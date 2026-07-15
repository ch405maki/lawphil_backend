<script setup lang="ts">
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

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

import EditCaseDialog from '@/components/acts/EditCaseDialog.vue';
import DeleteCaseDialog from '@/components/acts/DeleteCaseDialog.vue';

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
} from 'lucide-vue-next';

const props = defineProps<{
  refreshTrigger?: number;
}>();

const emit = defineEmits<{
  (e: 'refresh'): void;
}>();

const data = ref<any>({
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

const currentItem = ref({
  id: null,
  act_number: '',
  date: '',
  citation: '',
  tenure: '',
  url: '',
  pdf_availability: false,
  description: '',
  pdf_path: ''
});

const getYearFromFilename = (filename: string): string => {
  if (!filename) return '';
  const match = filename.match(/_(\d{4})\.(html|pdf)/);
  return match ? match[1] : '';
};

const generatePdfUrl = (url: string) => {
  if (!url) return null;
  if (url.startsWith('http')) {
    const lastSlashIndex = url.lastIndexOf('/');
    const fileName = url.substring(lastSlashIndex + 1);
    const basePath = url.substring(0, lastSlashIndex);
    const pdfFileName = fileName.replace('.html', '.pdf');
    return `${basePath}/pdf/${pdfFileName}`;
  }
  const year = getYearFromFilename(url);
  const yearFolder = year ? `act${year}/` : '';
  const pdfFileName = url.replace('.html', '.pdf');
  return `https://lawphil.net/statutes/acts/${yearFolder}pdf/${pdfFileName}`;
};

const generateHtmlUrl = (url: string) => {
  if (!url) return null;
  if (url.startsWith('http')) {
    return url;
  }
  const year = getYearFromFilename(url);
  const yearFolder = year ? `act${year}/` : '';
  return `https://lawphil.net/statutes/acts/${yearFolder}${url}`;
};

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

const pageNumbers = computed(() => {
  const total = data.value.last_page;
  const current = data.value.current_page;
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
    const response = await axios.get('/api/v1/acts', {
      params: {
        search: search.value,
        page: page,
        year: filterState.year,
        sort: filterState.sort,
        rows: filterState.rows
      },
      signal: signal
    });
    data.value = response.data;
  } catch (error: any) {
    if (error.name !== 'AbortError' && error.code !== 'ERR_CANCELED') {
      console.error('Error fetching data:', error);
      toast.error('Failed to fetch acts data');
    }
  } finally {
    if (!signal) {
      loading.value = false;
    }
    searchLoading.value = false;
    isSearching.value = false;
  }
};

watch(
  [() => filterState.year, () => filterState.sort, () => filterState.rows],
  (newVals, oldVals) => {
    if (oldVals && newVals.join('|') === oldVals.join('|')) return;
    if (currentAbortController) {
      currentAbortController.abort();
    }
    currentAbortController = new AbortController();
    fetchData(1, currentAbortController.signal);
  }
);

watch(() => props.refreshTrigger, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    fetchData(data.value.current_page);
  }
});

const openEdit = (item: any) => {
  currentItem.value = { ...item };
  showEditModal.value = true;
};

const confirmDelete = (id: number) => {
  deleteId.value = id;
  showDeleteDialog.value = true;
};

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

const refreshData = () => {
  fetchData(data.value.current_page);
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
  data.value.data = [];
  fetchData(1, currentAbortController.signal);
};

onMounted(() => {
  fetchData();
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
                placeholder="Search Act No. or Description..."
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
              <TableHead class="w-[15%]">Act & Date</TableHead>
              <TableHead class="w-[40%]">Description</TableHead>
              <TableHead class="w-[15%] text-center">URL</TableHead>
              <TableHead class="w-[10%] text-center">PDF</TableHead>
              <TableHead class="w-[20%] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="loading && data.data?.length === 0">
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

            <TableRow v-for="item in data.data" :key="item.id" class="group">
              <TableCell>
                <div class="font-semibold">{{ item.act_number }}</div>
                <div class="text-xs text-muted-foreground mt-1">{{ formatDate(item.date) }}</div>
              </TableCell>
              <TableCell>
                <div class="font-medium line-clamp-2 group-hover:text-primary transition-colors">
                  {{ item.description }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                  Citation: {{ item.citation || 'N/A' }} &bull; Tenure: {{ item.tenure || 'N/A' }}
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
                <span v-else class="text-muted-foreground">&mdash;</span>
              </TableCell>
              <TableCell class="text-center">
                <a
                  v-if="item.pdf_availability && item.url"
                  :href="generatePdfUrl(item.url)"
                  target="_blank"
                  class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
                >
                  <FileText class="h-4 w-4" />
                </a>
                <span v-else class="text-muted-foreground">&mdash;</span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button v-if="can('acts', 'update')" variant="ghost" size="icon" @click="openEdit(item)" :disabled="loading">
                    <SquarePen class="h-4 w-4" />
                  </Button>
                  <Button v-if="can('acts', 'delete')" variant="ghost" size="icon" @click="confirmDelete(item.id)" :disabled="loading">
                    <Trash2 class="h-4 w-4 text-destructive" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="data.data?.length === 0 && !loading">
              <TableCell colspan="5" class="text-center py-8">
                <div class="flex flex-col items-center gap-2">
                  <AlertCircle class="h-8 w-8 text-muted-foreground" />
                  <p class="text-muted-foreground">No acts found</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="border-t px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <p class="text-sm text-muted-foreground">
              Showing {{ data.from }} to {{ data.to }} of {{ data.total }} entries
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
              @click="fetchData(data.current_page - 1)"
              :disabled="data.current_page === 1 || loading"
            >
              <ChevronLeft class="h-4 w-4" />
            </Button>

            <template v-for="(page, index) in pageNumbers" :key="index">
              <Button
                v-if="page !== '...'"
                variant="outline"
                size="sm"
                @click="fetchData(page)"
                :class="data.current_page === page ? 'bg-primary text-primary-foreground' : ''"
                :disabled="loading"
              >
                {{ page }}
              </Button>
              <span v-else class="px-2 text-muted-foreground">...</span>
            </template>

            <Button
              variant="outline"
              size="sm"
              @click="fetchData(data.current_page + 1)"
              :disabled="data.current_page === data.last_page || loading"
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
      :ra-data="currentItem"
      @saved="refreshData"
    />

    <DeleteCaseDialog
      v-if="showDeleteDialog"
      v-model:open="showDeleteDialog"
      :ra-id="deleteId"
      @deleted="refreshData"
    />
  </div>
</template>
