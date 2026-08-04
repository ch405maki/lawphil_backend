<script setup lang="ts">
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

// Custom Components
import EditCaseDialog from '@/components/Executive/proclamation/EditCaseDialog.vue';
import DeleteCaseDialog from '@/components/Executive/proclamation/DeleteCaseDialog.vue';
import ExcelImportDialog from '@/components/Executive/proclamation/ExcelImportDialog.vue';

// Icons
import {
  Search,
  FileSpreadsheet,
  Plus,
  Trash2,
  SquarePen,
  SquareArrowOutUpRight,
  FileText,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Loader2,
  AlertCircle,
} from 'lucide-vue-next';
import { formatDate } from '@/lib/utils';

// Props
const props = defineProps<{
  refreshTrigger?: number;
}>();

// Emits
const emit = defineEmits<{
  (e: 'refresh'): void;
}>();

// Data state
const proc = ref<any>({
  data: [],
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

const search = ref('');
const showEditModal = ref(false);
const showDeleteDialog = ref(false);
const deleteId = ref<number | null>(null);
const loading = ref(false);
const searchLoading = ref(false);
const isSearching = ref(false);

const filterState = reactive({ 
  year: '', 
  sort: 'latest',
  rows: 10
});

const currentProc = ref({
  id: null,
  proc_number: '',
  date: '',
  citation: '',
  tenure: '',
  url: '',
  pdf_availability: false,
  description: '',
  pdf_path: ''
});

// Helper function to extract year from filename (e.g., "proc_1211_2026.html" -> "2026")
const getYearFromFilename = (filename: string): string => {
  if (!filename) return '';
  // Hinahanap nito ang 4-digit number bago ang .html o .pdf (hal. 2026)
  const match = filename.match(/_(\d{4})\.(html|pdf)/);
  return match ? match[1] : '';
};

const generatePdfUrl = (pdfPath: string | null, url: string | null) => {
  if (pdfPath && pdfPath.trim() !== '') {
    if (pdfPath.startsWith('http')) {
      return pdfPath;
    }
    const year = getYearFromFilename(pdfPath);
    const yearFolder = year ? `proc${year}/` : '';
    return `https://lawphil.net/executive/proc/${yearFolder}pdf/${pdfPath}`;
  }

  if (!url) return null;
  
  if (url.startsWith('http')) {
    const lastSlashIndex = url.lastIndexOf('/');
    const fileName = url.substring(lastSlashIndex + 1);
    const basePath = url.substring(0, lastSlashIndex);
    const pdfFileName = fileName.replace('.html', '.pdf');
    return `${basePath}/pdf/${pdfFileName}`;
  }
  
  const year = getYearFromFilename(url);
  const yearFolder = year ? `proc${year}/` : '';
  const pdfFileName = url.replace('.html', '.pdf');
  
  return `https://lawphil.net/executive/proc/${yearFolder}pdf/${pdfFileName}`;
};

// Helper function to generate HTML URL from relative path
const generateHtmlUrl = (url: string) => {
  if (!url) return null;
  
  if (url.startsWith('http')) {
    return url;
  }
  
  // Kung relative path lang (hal. "proc_1211_2026.html")
  const year = getYearFromFilename(url);
  const yearFolder = year ? `proc${year}/` : '';
  
  return `https://lawphil.net/executive/proc/${yearFolder}${url}`;
};

// Debounce function for search - with request cancellation
let searchTimeout: ReturnType<typeof setTimeout>;
let currentAbortController: AbortController | null = null;

const handleSearch = () => {
  isSearching.value = true;
  searchLoading.value = true;
  
  if (searchTimeout) clearTimeout(searchTimeout);
  
  if (currentAbortController) {
    currentAbortController.abort();
  }
  
  searchTimeout = setTimeout(() => {
    currentAbortController = new AbortController();
    fetchData(1, currentAbortController.signal);
  }, 500);
};

// Logic for generating numbered pagination
const pageNumbers = computed(() => {
  const total = proc.value.last_page;
  const current = proc.value.current_page;
  const delta = 2;
  const range = [];
  for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
    range.push(i);
  }
  if (current - delta > 2) range.unshift('...');
  range.unshift(1);
  if (current + delta < total - 1) range.push('...');
  if (total > 1) range.push(total);
  return range;
});

const fetchData = async (page = 1, signal?: AbortSignal) => {
  if (!signal) {
    loading.value = true;
  }
  
  try {
    const response = await axios.get('/api/v1/proclamations', {
      params: {
        search: search.value,
        page: page,
        year: filterState.year,
        sort: filterState.sort,
        rows: filterState.rows
      },
      signal: signal
    });
    // console.log("API Response Success"); // Pwede mo nang tanggalin yung pag-log ng buong response data para hindi bumigat ang console
    
    proc.value = response.data;
  } catch (error: any) {
    if (error.name !== 'AbortError' && error.code !== 'ERR_CANCELED') {
      console.error('Error fetching data:', error);
      toast.error('Failed to fetch proclamations data');
    }
  } finally {
    if (!signal) {
      loading.value = false;
    }
    searchLoading.value = false;
    isSearching.value = false;
  }
};

// Watch for filter changes (REFACTORED: Added checking to prevent infinite loops)
watch(
  [() => filterState.year, () => filterState.sort, () => filterState.rows], 
  (newVals, oldVals) => {
    // Kung walang totoong nagbago (halimbawa nag-re-render lang ang component), wag mag-fetch
    if (oldVals && newVals.join('|') === oldVals.join('|')) return;

    if (currentAbortController) {
      currentAbortController.abort();
    }
    currentAbortController = new AbortController();
    fetchData(1, currentAbortController.signal);
  }
);

// Watch for refresh trigger from parent (REFACTORED: Checked if trigger actually changed)
watch(() => props.refreshTrigger, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    fetchData(proc.value.current_page);
  }
});

const openEdit = (item: any) => {
  currentProc.value = { ...item };
  showEditModal.value = true;
};

const confirmDelete = (id: number) => {
  deleteId.value = id;
  showDeleteDialog.value = true;
};

const refreshData = () => {
  fetchData(proc.value.current_page);
  emit('refresh');
};

const resetFilters = () => {
  Object.assign(filterState, {year: '', sort: 'latest', rows: 10});
  search.value = '';
  if (currentAbortController) {
    currentAbortController.abort();
  }
  currentAbortController = new AbortController();
  loading.value = true;
  proc.value.data = [];
  fetchData(1, currentAbortController.signal);
};

// Initial data fetch
onMounted(() => {
  // Pwede mo na i-comment o tanggalin ito kung sigurado ka na
  // console.log("Component Mounted!");
  fetchData();
});

// Expose methods for parent component
defineExpose({
  refreshData
});
</script>

<template>
  <div>
    <Card>
      <CardContent class="p-0">
        <div class="flex flex-wrap items-center justify-end gap-3 p-4">
          
          <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-[320px]">
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                <Search v-if="!searchLoading" class="h-4 w-4 text-muted-foreground" />
                <Loader2 v-else class="h-4 w-4 animate-spin text-primary" />
              </div>
              <Input 
                v-model="search" 
                @input="handleSearch"
                placeholder="Search Proclamation No. or Description..." 
                class="pl-9"
              />
            </div>
            
            <Select v-model="filterState.sort" :disabled="searchLoading">
              <SelectTrigger class="w-[130px]">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="latest">Newest First</SelectItem>
                <SelectItem value="oldest">Oldest First</SelectItem>
                <SelectItem value="az">A-Z (Description)</SelectItem>
                <SelectItem value="za">Z-A (Description)</SelectItem>
              </SelectContent>
            </Select>
            
            <Input 
              v-model="filterState.year" 
              type="number" 
              placeholder="Year" 
              class="w-24"
              :disabled="searchLoading"
            />
            
            <Button 
              variant="ghost" 
              @click="resetFilters"
              class="gap-1"
              :disabled="searchLoading"
            >
              <RefreshCw class="h-4 w-4" />
              <span class="hidden sm:inline">Reset</span>
            </Button>
          </div>
        </div>
        
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[15%]">Proclamation No. & Date</TableHead>
              <TableHead class="w-[40%]">Description</TableHead>
              <TableHead class="w-[15%] text-center">URL</TableHead>
              <TableHead class="w-[10%] text-center">PDF</TableHead>
              <TableHead class="w-[20%] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loading && proc.data?.length === 0">
              <TableRow v-for="i in 5" :key="i">
                <TableCell>
                  <div class="space-y-2">
                    <Skeleton class="h-5 w-24" />
                    <Skeleton class="h-3 w-16" />
                  </div>
                </TableCell>
                <TableCell>
                  <div class="space-y-2">
                    <Skeleton class="h-5 w-64" />
                    <Skeleton class="h-3 w-48" />
                  </div>
                </TableCell>
                <TableCell class="text-center">
                  <Skeleton class="h-5 w-12 mx-auto" />
                </TableCell>
                <TableCell class="text-center">
                  <Skeleton class="h-8 w-8 rounded-full mx-auto" />
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Skeleton class="h-8 w-8 rounded-md" />
                    <Skeleton class="h-8 w-8 rounded-md" />
                  </div>
                </TableCell>
              </TableRow>
            </template>
            
            <TableRow v-for="item in proc.data" :key="item.id" class="group">
              <TableCell>
                <div class="font-semibold">{{ item.proc_number }}</div>
                <div class="text-xs text-muted-foreground mt-1">{{ formatDate(item.date) }}</div>
              </TableCell>
              <TableCell>
                <div class="font-medium line-clamp-2 group-hover:text-primary transition-colors">
                  {{ item.description }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                  Tenure: {{ item.tenure || 'N/A' }}
                </div>
              </TableCell>
              <TableCell class="text-center">
                <a 
                  v-if="item.url" 
                  :href="generateHtmlUrl(item.url)" 
                  target="_blank" 
                  class="inline-flex items-center gap-1 text-primary hover:underline text-sm"
                >
                  Link <SquareArrowOutUpRight class="h-3 w-3" />
                </a>
                <span v-else class="text-muted-foreground">—</span>
              </TableCell>
              <TableCell class="text-center">
                <a 
                  v-if="item.pdf_availability && (item.pdf_path || item.url)" 
                  :href="generatePdfUrl(item.pdf_path, item.url)" 
                  target="_blank" 
                  class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                >
                  <FileText class="h-4 w-4" />
                </a>
                <span v-else class="text-muted-foreground">—</span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="icon" @click="openEdit(item)" :disabled="loading">
                    <SquarePen class="h-4 w-4" />
                  </Button>
                  <Button variant="ghost" size="icon" @click="confirmDelete(item.id)" :disabled="loading">
                    <Trash2 class="h-4 w-4 text-destructive" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="proc.data?.length === 0 && !loading">
              <TableCell colspan="5" class="text-center py-8">
                <div class="flex flex-col items-center gap-2">
                  <AlertCircle class="h-8 w-8 text-muted-foreground" />
                  <p class="text-muted-foreground">No proclamations found</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="border-t px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <p class="text-sm text-muted-foreground">
              Showing {{ proc.from }} to {{ proc.to }} of {{ proc.total }} entries
            </p>
            <div class="flex items-center gap-2">
              <span class="text-sm text-muted-foreground">Show</span>
              <Select v-model="filterState.rows" :disabled="loading">
                <SelectTrigger class="w-[70px]">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem :value="10">10</SelectItem>
                  <SelectItem :value="25">25</SelectItem>
                  <SelectItem :value="50">50</SelectItem>
                  <SelectItem :value="100">100</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="flex items-center gap-1">
            <Button 
              variant="outline" 
              size="sm"
              @click="fetchData(proc.current_page - 1)" 
              :disabled="proc.current_page === 1 || loading"
            >
              <ChevronLeft class="h-4 w-4" />
            </Button>

            <template v-for="(page, index) in pageNumbers" :key="index">
              <Button 
                v-if="page !== '...'" 
                variant="outline"
                size="sm"
                @click="fetchData(page)" 
                :class="proc.current_page === page ? 'bg-primary text-primary-foreground' : ''"
                :disabled="loading"
              >
                {{ page }}
              </Button>
              <span v-else class="px-2 text-muted-foreground">...</span>
            </template>

            <Button 
              variant="outline" 
              size="sm"
              @click="fetchData(proc.current_page + 1)" 
              :disabled="proc.current_page === proc.last_page || loading"
            >
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <EditCaseDialog 
      v-if="showEditModal"
      v-model:open="showEditModal"
      :proc-data="currentProc"
      @saved="refreshData"
    />

    <DeleteCaseDialog
      v-if="showDeleteDialog"
      v-model:open="showDeleteDialog"
      :proc-id="deleteId"
      @deleted="refreshData"
    />


  </div>
</template>