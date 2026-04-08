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
  // Dinagdag natin ito para makausap ang Parent kung gusto mag-edit
  (e: 'edit', item: any): void; 
}>();

// Data state
const orders = ref<any>({
  data: [],
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

const search = ref('');
const loading = ref(false);
const searchLoading = ref(false);
const isSearching = ref(false);

// Deletion State
const showDeleteDialog = ref(false);
const deleteId = ref<number | null>(null);
const isDeleting = ref(false);

// Multiple deletion state
const selectedIds = ref<number[]>([]);
const selectAllAcrossPages = ref(false);
const totalFiltered = ref(0);
const showBulkDeleteDialog = ref(false);
const bulkDeleting = ref(false);

const filterState = reactive({ 
    year: '', 
    sort: 'latest',
    rows: 10
});

// Helper functions para sa URL (EO specific)
const generateHtmlUrl = (url: string | null | undefined): string => {
  if (!url) return '#';
  if (url.startsWith('http')) return url;
  return `https://lawphil.net/executive/execord/${url}`;
};

const generatePdfUrl = (item: any): string => {
  if (item.pdf_path) return item.pdf_path;
  if (!item.url) return '#';
  
  const url = item.url;
  const lastSlashIndex = url.lastIndexOf('/');
  const fileName = url.substring(lastSlashIndex + 1);
  const basePath = url.substring(0, lastSlashIndex);
  const pdfFileName = fileName.replace('.html', '.pdf');
  
  return `${basePath}/pdf/${pdfFileName}`;
};

// Computed properties for multiple deletion
const isAllSelectedOnPage = computed(() => {
  return orders.value.data?.length > 0 && selectedIds.value.length === orders.value.data?.length;
});

const isSomeSelected = computed(() => {
  return selectedIds.value.length > 0 && selectedIds.value.length < orders.value.data?.length;
});

const selectedCountText = computed(() => {
  if (selectAllAcrossPages.value) {
    return `All ${totalFiltered.value} records`;
  }
  return `${selectedIds.value.length} record(s)`;
});

// Debounce function for search
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

// Pagination Logic
const pageNumbers = computed(() => {
    const total = orders.value.last_page;
    const current = orders.value.current_page;
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

// Fetch Data (Tumuturo na sa Executive Orders API)
const fetchData = async (page = 1, signal?: AbortSignal) => {
  if (!signal) loading.value = true;
  
  try {
    const response = await axios.get('/api/executive-orders', {
      params: {
        search: search.value,
        page: page,
        year: filterState.year,
        sort: filterState.sort,
        rows: filterState.rows
      },
      signal: signal
    });
    
    orders.value = response.data;
    totalFiltered.value = response.data.total;
    
    if (!selectAllAcrossPages.value) {
      selectedIds.value = [];
    }
  } catch (error: any) {
    if (error.name !== 'AbortError' && error.code !== 'ERR_CANCELED') {
      console.error('Error fetching data:', error);
      toast.error('Failed to fetch executive orders data');
    }
  } finally {
    if (!signal) loading.value = false;
    searchLoading.value = false;
    isSearching.value = false;
  }
};

// Watchers
watch([() => filterState.year, () => filterState.sort, () => filterState.rows], () => {
  selectAllAcrossPages.value = false;
  selectedIds.value = [];
  if (currentAbortController) currentAbortController.abort();
  currentAbortController = new AbortController();
  fetchData(1, currentAbortController.signal);
});

watch(() => props.refreshTrigger, () => {
  fetchData(orders.value.current_page);
});

// Action Handlers
const openEdit = (item: any) => {
    emit('edit', item);
};

const confirmDelete = (id: number) => {
    deleteId.value = id;
    showDeleteDialog.value = true;
};

// Selection Handlers
const toggleSelectAllOnPage = () => {
  if (isAllSelectedOnPage.value) {
    selectedIds.value = selectedIds.value.filter(id => 
      !orders.value.data.some((item: any) => item.id === id)
    );
  } else {
    const pageIds = orders.value.data.map((item: any) => item.id);
    const newIds = [...new Set([...selectedIds.value, ...pageIds])];
    selectedIds.value = newIds;
  }
  selectAllAcrossPages.value = false;
};

const toggleSelectAllAcrossPages = () => {
  selectAllAcrossPages.value = !selectAllAcrossPages.value;
  selectedIds.value = [];
};

const toggleSelect = (id: number) => {
  if (selectAllAcrossPages.value) selectAllAcrossPages.value = false;
  const index = selectedIds.value.indexOf(id);
  if (index > -1) {
    selectedIds.value.splice(index, 1);
  } else {
    selectedIds.value.push(id);
  }
};

// Bulk Delete Logic
const bulkDelete = async () => {
  bulkDeleting.value = true;
  try {
    let response;
    const payload = selectAllAcrossPages.value 
      ? { select_all: true, filters: { search: search.value, year: filterState.year, sort: filterState.sort } }
      : { ids: selectedIds.value, select_all: false };

    response = await axios.post('/api/executive-orders/bulk-delete', payload);

    if (response.data.success) {
      toast.success(`Successfully deleted ${response.data.deleted_count} record(s)`);
      showBulkDeleteDialog.value = false;
      selectAllAcrossPages.value = false;
      selectedIds.value = [];
      fetchData(orders.value.current_page);
      emit('refresh');
    } else {
      throw new Error(response.data.message || 'Failed to delete records');
    }
  } catch (error: any) {
    console.error('Error bulk deleting:', error);
    toast.error(error.response?.data?.message || 'Failed to delete records');
  } finally {
    bulkDeleting.value = false;
  }
};

// Single Delete Logic (Simplified)
const deleteRecord = async () => {
    isDeleting.value = true;
    try {
        const response = await axios.delete(`/api/executive-orders/${deleteId.value}`);
        if (response.data.success) {
            toast.success('Record deleted successfully');
            showDeleteDialog.value = false;
            fetchData(orders.value.current_page);
            emit('refresh');
        }
    } catch (error: any) {
        toast.error('Failed to delete record');
    } finally {
        isDeleting.value = false;
    }
};

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

const refreshData = () => {
    selectAllAcrossPages.value = false;
    selectedIds.value = [];
    fetchData(orders.value.current_page);
    emit('refresh');
};

const resetFilters = () => {
  Object.assign(filterState, {year: '', sort: 'latest', rows: 10});
  search.value = '';
  selectAllAcrossPages.value = false;
  selectedIds.value = [];
  if (currentAbortController) currentAbortController.abort();
  currentAbortController = new AbortController();
  fetchData(1, currentAbortController.signal);
};

onMounted(() => {
  fetchData();
});

defineExpose({ refreshData });
</script>

<template>
  <div>
    <Card>
      <CardContent class="p-0">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4">
          <div class="flex items-center gap-2">
            <Button 
              v-if="selectedIds.length > 0 || selectAllAcrossPages"
              variant="destructive" 
              size="sm"
              @click="showBulkDeleteDialog = true"
              class="gap-2"
            >
              <Trash class="h-4 w-4" /> Delete Selected ({{ selectedCountText }})
            </Button>
            <Button 
              v-if="selectedIds.length > 0 || selectAllAcrossPages"
              variant="ghost" 
              size="sm"
              @click="() => { selectedIds = []; selectAllAcrossPages = false; }"
              class="gap-2"
            >
              <X class="h-4 w-4" /> Clear
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
                placeholder="Search E.O. Number or Subject..." 
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
                <SelectItem value="az">A-Z (Subject)</SelectItem>
                <SelectItem value="za">Z-A (Subject)</SelectItem>
              </SelectContent>
            </Select>
            
            <Input 
              v-model="filterState.year" 
              type="number" 
              placeholder="Year" 
              class="w-24"
              :disabled="searchLoading"
            />
            
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
                <div class="flex flex-col gap-1">
                  <Checkbox 
                    :checked="isAllSelectedOnPage && !selectAllAcrossPages"
                    :indeterminate="isSomeSelected && !selectAllAcrossPages"
                    @click="toggleSelectAllOnPage"
                    :disabled="loading || orders.data?.length === 0"
                  />
                  <span class="text-xs font-normal">Page</span>
                </div>
              </TableHead>
              <TableHead class="w-12">
                <div class="flex flex-col gap-1">
                  <Checkbox 
                    :checked="selectAllAcrossPages"
                    @click="toggleSelectAllAcrossPages"
                    :disabled="loading || totalFiltered === 0"
                  />
                  <span class="text-xs font-normal">All</span>
                </div>
              </TableHead>
              <TableHead class="w-[20%]">E.O. Number & Date</TableHead>
              <TableHead class="w-[35%]">Subject / Description</TableHead>
              <TableHead class="w-[15%]">Reference</TableHead>
              <TableHead class="w-[10%] text-center">URL</TableHead>
              <TableHead class="w-[10%] text-center">PDF</TableHead>
              <TableHead class="w-[10%] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          
          <TableBody>
            <template v-if="loading && orders.data?.length === 0">
              <TableRow v-for="i in 5" :key="i">
                <TableCell colspan="8"><Skeleton class="h-8 w-full" /></TableCell>
              </TableRow>
            </template>
            
            <TableRow v-for="item in orders.data" :key="item.id" class="group">
              <TableCell>
                <Checkbox 
                  :checked="selectedIds.includes(item.id)"
                  @click="toggleSelect(item.id)"
                  :disabled="loading || selectAllAcrossPages"
                />
              </TableCell>
              <TableCell></TableCell>
              <TableCell>
                <div class="font-semibold">{{ item.eo_number }}</div>
                <div class="text-xs text-muted-foreground mt-1">{{ formatDate(item.date) }}</div>
              </TableCell>
              <TableCell>
                <div class="font-medium line-clamp-2 group-hover:text-primary transition-colors">
                  {{ item.subject }}
                </div>
              </TableCell>
              <TableCell>
                <div class="text-sm">{{ item.reference || 'N/A' }}</div>
              </TableCell>
              <TableCell class="text-center">
                <a v-if="item.url" :href="generateHtmlUrl(item.url)" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline text-sm">
                  Link <SquareArrowOutUpRight class="h-3 w-3" />
                </a>
                <span v-else class="text-muted-foreground">—</span>
              </TableCell>
              <TableCell class="text-center">
                <a v-if="item.pdf_availability || item.url" :href="generatePdfUrl(item)" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all">
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
            <TableRow v-if="orders.data?.length === 0 && !loading">
              <TableCell colspan="8" class="text-center py-8">
                <div class="flex flex-col items-center gap-2">
                  <AlertCircle class="h-8 w-8 text-muted-foreground" />
                  <p class="text-muted-foreground">No executive orders found</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="border-t px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <p class="text-sm text-muted-foreground">Showing {{ orders.from || 0 }} to {{ orders.to || 0 }} of {{ orders.total }} entries</p>
            <div class="flex items-center gap-2">
              <span class="text-sm text-muted-foreground">Show</span>
              <Select v-model="filterState.rows" :disabled="loading">
                <SelectTrigger class="w-[70px]"><SelectValue /></SelectTrigger>
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
            <Button variant="outline" size="sm" @click="fetchData(orders.current_page - 1)" :disabled="orders.current_page === 1 || loading">
              <ChevronLeft class="h-4 w-4" />
            </Button>
            <template v-for="(page, index) in pageNumbers" :key="index">
              <Button v-if="page !== '...'" variant="outline" size="sm" @click="fetchData(page)" :class="orders.current_page === page ? 'bg-primary text-primary-foreground' : ''" :disabled="loading">
                {{ page }}
              </Button>
              <span v-else class="px-2 text-muted-foreground">...</span>
            </template>
            <Button variant="outline" size="sm" @click="fetchData(orders.current_page + 1)" :disabled="orders.current_page === orders.last_page || loading">
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <AlertDialog v-model:open="showDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Record</AlertDialogTitle>
          <AlertDialogDescription>Are you sure you want to delete this record? This action cannot be undone.</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="deleteRecord" class="bg-destructive" :disabled="isDeleting">
            <Loader2 v-if="isDeleting" class="h-4 w-4 mr-2 animate-spin" /> Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog v-model:open="showBulkDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete <strong>{{ selectedCountText }}</strong> from the system.
            <div v-if="selectAllAcrossPages" class="mt-2 p-2 bg-yellow-50 rounded text-yellow-800 text-sm">
              ⚠️ Warning: This will delete ALL {{ totalFiltered }} records matching your current filters.
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="bulkDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="bulkDelete" class="bg-destructive" :disabled="bulkDeleting">
            <Loader2 v-if="bulkDeleting" class="h-4 w-4 mr-2 animate-spin" /> Delete All
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>