<script setup lang="ts">
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
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
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

// Custom Components
import EditCaseDialog from '@/components/jurisprudence/EditCaseDialog.vue';
import DeleteCaseDialog from '@/components/jurisprudence/DeleteCaseDialog.vue';

// Icons
import {
  Search,
  Trash2,
  SquarePen,
  SquareArrowOutUpRight,
  FileText,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Loader2,
  AlertCircle,
  Trash,
  X
} from 'lucide-vue-next';

// Props
const props = defineProps<{
  refreshTrigger?: number;
}>();

// Emits
const emit = defineEmits<{
  (e: 'refresh'): void;
}>();

// Data state
const cases = ref<any>({
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

// Multiple deletion state
const selectedIds = ref<number[]>([]);
const showBulkDeleteDialog = ref(false);
const bulkDeleting = ref(false);

const filterState = reactive({ 
    year: '', 
    sort: 'latest',
    rows: 10
});

const currentCase = ref({
    id: null,
    gr_number: '',
    date: '',
    citation: '',
    ponente: '',
    reference: '',
    url: '',
    pdf_availability: false,
    pdf_path: ''
});

// Helper functions
const generatePdfUrl = (url: string) => {
  if (!url) return null;
  if (url.startsWith('http')) {
    const lastSlashIndex = url.lastIndexOf('/');
    const fileName = url.substring(lastSlashIndex + 1);
    const basePath = url.substring(0, lastSlashIndex);
    const pdfFileName = fileName.replace('.html', '.pdf');
    return `${basePath}/pdf/${pdfFileName}`;
  }
  const pathParts = url.split('/');
  const fileName = pathParts.pop();
  const basePath = pathParts.join('/');
  const pdfFileName = fileName?.replace('.html', '.pdf');
  return `https://lawphil.net/judjuris/${basePath}/pdf/${pdfFileName}`;
};

const generateHtmlUrl = (url: string) => {
  if (!url) return null;
  return url.startsWith('http') ? url : `https://lawphil.net/judjuris/${url}`;
};

// Computed properties for multiple deletion
const isAllSelectedOnPage = computed(() => {
  return cases.value.data?.length > 0 && 
         cases.value.data.every((item: any) => selectedIds.value.includes(item.id));
});

const isSomeSelected = computed(() => {
  return selectedIds.value.length > 0 && !isAllSelectedOnPage.value;
});

let searchTimeout: ReturnType<typeof setTimeout>;
let currentAbortController: AbortController | null = null;

const handleSearch = () => {
  isSearching.value = true;
  searchLoading.value = true;
  if (searchTimeout) clearTimeout(searchTimeout);
  if (currentAbortController) currentAbortController.abort();
  
  searchTimeout = setTimeout(() => {
    currentAbortController = new AbortController();
    fetchData(1, currentAbortController.signal);
  }, 500);
};

const pageNumbers = computed(() => {
    const total = cases.value.last_page;
    const current = cases.value.current_page;
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
  if (!signal) loading.value = true;
  try {
    const response = await axios.get('/api/jurisprudence', {
      params: {
        search: search.value,
        page: page,
        year: filterState.year,
        sort: filterState.sort,
        rows: filterState.rows
      },
      signal: signal
    });
    cases.value = response.data;
  } catch (error: any) {
    if (error.name !== 'AbortError' && error.code !== 'ERR_CANCELED') {
      console.error('Error fetching data:', error);
      toast.error('Failed to fetch jurisprudence data');
    }
  } finally {
    if (!signal) loading.value = false;
    searchLoading.value = false;
    isSearching.value = false;
  }
};

watch([() => filterState.year, () => filterState.sort, () => filterState.rows], () => {
  selectedIds.value = [];
  if (currentAbortController) currentAbortController.abort();
  currentAbortController = new AbortController();
  fetchData(1, currentAbortController.signal);
});

watch(() => props.refreshTrigger, () => {
  fetchData(cases.value.current_page);
});

const openEdit = (item: any) => {
    currentCase.value = { ...item };
    showEditModal.value = true;
};

const confirmDelete = (id: number) => {
    deleteId.value = id;
    showDeleteDialog.value = true;
};

const toggleSelectAllOnPage = () => {
  if (isAllSelectedOnPage.value) {
    selectedIds.value = selectedIds.value.filter(id => 
      !cases.value.data.some((item: any) => item.id === id)
    );
  } else {
    const pageIds = cases.value.data.map((item: any) => item.id);
    selectedIds.value = [...new Set([...selectedIds.value, ...pageIds])];
  }
};

const toggleSelect = (id: number) => {
  const index = selectedIds.value.indexOf(id);
  index > -1 ? selectedIds.value.splice(index, 1) : selectedIds.value.push(id);
};

const bulkDelete = async () => {
  bulkDeleting.value = true;
  try {
    const response = await axios.post('/api/jurisprudence/bulk-delete', {
      ids: selectedIds.value,
      select_all: false
    }, {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });

    if (response.data.success) {
      toast.success(`Successfully deleted ${response.data.deleted_count} record(s)`);
      showBulkDeleteDialog.value = false;
      selectedIds.value = [];
      fetchData(cases.value.current_page);
      emit('refresh');
    }
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to delete records');
  } finally {
    bulkDeleting.value = false;
  }
};

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

const refreshData = () => {
    selectedIds.value = [];
    fetchData(cases.value.current_page);
    emit('refresh');
};

const resetFilters = () => {
  Object.assign(filterState, {year: '', sort: 'latest', rows: 10});
  search.value = '';
  selectedIds.value = [];
  if (currentAbortController) currentAbortController.abort();
  fetchData(1);
};

onMounted(() => fetchData());

defineExpose({ refreshData });
</script>

<template>
  <div>
    <Card>
      <CardContent class="p-0">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4">
          <div class="flex items-center gap-2">
            <Button 
              v-if="selectedIds.length > 0"
              variant="destructive" 
              size="sm"
              @click="showBulkDeleteDialog = true"
              class="gap-2"
            >
              <Trash class="h-4 w-4" />
              Delete Selected ({{ selectedIds.length }})
            </Button>
            <Button 
              v-if="selectedIds.length > 0"
              variant="ghost" 
              size="sm"
              @click="() => { selectedIds = []; }"
              class="gap-2"
            >
              <X class="h-4 w-4" />
              Clear
            </Button>
          </div>
          
          <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-[320px]">
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                <Search v-if="!searchLoading" class="h-4 w-4 text-muted-foreground" />
                <Loader2 v-else class="h-4 w-4 animate-spin text-primary" />
              </div>
              <Input 
                v-model="search" 
                @input="handleSearch"
                placeholder="Search G.R. No. or Case Title..." 
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
                <SelectItem value="az">A-Z (Title)</SelectItem>
                <SelectItem value="za">Z-A (Title)</SelectItem>
              </SelectContent>
            </Select>
            
            <Input v-model="filterState.year" type="number" placeholder="Year" class="w-24" :disabled="searchLoading" />
            
            <Button variant="ghost" @click="resetFilters" class="gap-1" :disabled="searchLoading">
              <RefreshCw class="h-4 w-4" />
              <span class="hidden sm:inline">Reset</span>
            </Button>
          </div>
        </div>
        
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-12">
                <Checkbox 
                  :checked="isAllSelectedOnPage"
                  :indeterminate="isSomeSelected"
                  @click="toggleSelectAllOnPage"
                  :disabled="loading || cases.data?.length === 0"
                />
              </TableHead>
              <TableHead class="w-[15%]">G.R. & Date</TableHead>
              <TableHead class="w-[40%]">Case Title</TableHead>
              <TableHead class="w-[15%] text-center">URL</TableHead>
              <TableHead class="w-[10%] text-center">PDF</TableHead>
              <TableHead class="w-[20%] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loading && cases.data?.length === 0">
              <TableRow v-for="i in 5" :key="i">
                <TableCell><Skeleton class="h-4 w-4" /></TableCell>
                <TableCell><Skeleton class="h-5 w-24" /></TableCell>
                <TableCell><Skeleton class="h-5 w-64" /></TableCell>
                <TableCell><Skeleton class="h-5 w-12 mx-auto" /></TableCell>
                <TableCell><Skeleton class="h-8 w-8 rounded-full mx-auto" /></TableCell>
                <TableCell><Skeleton class="h-8 w-16 ml-auto" /></TableCell>
              </TableRow>
            </template>
            
            <TableRow v-for="item in cases.data" :key="item.id" class="group">
              <TableCell>
                <Checkbox 
                  :checked="selectedIds.includes(item.id)"
                  @click="toggleSelect(item.id)"
                  :disabled="loading"
                />
              </TableCell>
              <TableCell>
                <div class="font-semibold">{{ item.gr_number }}</div>
                <div class="text-xs text-muted-foreground mt-1">{{ formatDate(item.date) }}</div>
              </TableCell>
              <TableCell>
                <div class="font-medium line-clamp-2 group-hover:text-primary transition-colors">
                  {{ item.citation }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                  Ponente: {{ item.ponente || 'N/A' }} • Vol: {{ item.reference }}
                </div>
              </TableCell>
              <TableCell class="text-center">
                <a v-if="item.url" :href="generateHtmlUrl(item.url)" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline text-sm">
                  Link <SquareArrowOutUpRight class="h-3 w-3" />
                </a>
                <span v-else class="text-muted-foreground">—</span>
              </TableCell>
              <TableCell class="text-center">
                <a v-if="item.pdf_availability && item.url" :href="generatePdfUrl(item.url)" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all">
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
            <TableRow v-if="cases.data?.length === 0 && !loading">
              <TableCell colspan="6" class="text-center py-8">
                <div class="flex flex-col items-center gap-2">
                  <AlertCircle class="h-8 w-8 text-muted-foreground" />
                  <p class="text-muted-foreground">No cases found</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="border-t px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <p class="text-sm text-muted-foreground">
              Showing {{ cases.from }} to {{ cases.to }} of {{ cases.total }} entries
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
            <Button variant="outline" size="sm" @click="fetchData(cases.current_page - 1)" :disabled="cases.current_page === 1 || loading">
              <ChevronLeft class="h-4 w-4" />
            </Button>

            <template v-for="(page, index) in pageNumbers" :key="index">
              <Button 
                v-if="page !== '...'" 
                variant="outline"
                size="sm"
                @click="fetchData(page)" 
                :class="cases.current_page === page ? 'bg-primary text-primary-foreground' : ''"
                :disabled="loading"
              >
                {{ page }}
              </Button>
              <span v-else class="px-2 text-muted-foreground">...</span>
            </template>

            <Button variant="outline" size="sm" @click="fetchData(cases.current_page + 1)" :disabled="cases.current_page === cases.last_page || loading">
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <EditCaseDialog v-model:open="showEditModal" :case-data="currentCase" @saved="refreshData" />
    <DeleteCaseDialog v-model:open="showDeleteDialog" :case-id="deleteId" @deleted="refreshData" />

    <AlertDialog v-model:open="showBulkDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete 
            <strong>{{ selectedIds.length }} record(s)</strong> from the system.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="bulkDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="bulkDelete" class="bg-destructive text-destructive-foreground hover:bg-destructive/90" :disabled="bulkDeleting">
            <Loader2 v-if="bulkDeleting" class="h-4 w-4 mr-2 animate-spin" />
            {{ bulkDeleting ? 'Deleting...' : 'Confirm Delete' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>