<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { Head } from '@inertiajs/vue3';

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
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';

// Custom Components
import EditCaseDialog from '@/components/jurisprudence/EditCaseDialog.vue';
import DeleteCaseDialog from '@/components/jurisprudence/DeleteCaseDialog.vue';
import ExcelImportDialog from '@/components/jurisprudence/ExcelImportDialog.vue';

// Icons
import {
  Search,
  FileSpreadsheet,
  Plus,
  Trash2,
  Pencil,
  ExternalLink,
  FileText,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Loader2,
  AlertCircle
} from 'lucide-vue-next';

const breadcrumbs = [ 
  { title: "Dashboard", href: "/dashboard" }, 
  { title: "Jurisprudence", href: "#" }
];

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

// Debounce function for search
let searchTimeout: ReturnType<typeof setTimeout>;
const handleSearch = () => {
  searchLoading.value = true;
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchData(1);
  }, 500);
};

// Logic for generating numbered pagination
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

const fetchData = async (page = 1) => {
  loading.value = true;
  
  try {
    const response = await axios.get('/api/jurisprudence', {
      params: {
        search: search.value,
        page: page,
        year: filterState.year,
        sort: filterState.sort,
        rows: filterState.rows
      }
    });
    
    cases.value = response.data;
  } catch (error) {
    console.error('Error fetching data:', error);
    toast.error('Failed to fetch jurisprudence data');
  } finally {
    loading.value = false;
    searchLoading.value = false;
  }
};

// Handle successful import
const handleImportSuccess = (data: any) => {
    toast.success(`Import successful! ${data.imported} records imported.`);
    fetchData(); // Refresh the data after import
};

// Handle import error
const handleImportError = (error: any) => {
    toast.error(error.message || 'Import failed. Please check your file format.');
};

// Watch for filter changes
watch([() => filterState.year, () => filterState.sort, () => filterState.rows], () => fetchData(1));

const openEdit = (item: any) => {
    currentCase.value = { ...item };
    showEditModal.value = true;
};

const confirmDelete = (id: number) => {
    deleteId.value = id;
    showDeleteDialog.value = true;
};

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';

const refreshData = () => {
    fetchData(cases.value.current_page);
};

const goToCreate = () => {
  window.location.href = '/jurisprudence/create';
};

const resetFilters = () => {
  Object.assign(filterState, {year: '', sort: 'latest', rows: 10});
  search.value = '';
  fetchData(1);
};

// Initial data fetch
onMounted(() => {
  fetchData();
});
</script>

<template>
    <Head title="Jurisprudence" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-background p-4">
            
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Jurisprudence Bank</h1>
                        <p class="text-muted-foreground">Legal Archives Management System</p>
                    </div>
                    <div class="flex gap-2">
                        <ExcelImportDialog 
                            trigger-text="Import Excel/CSV"
                            trigger-variant="outline"
                            :trigger-icon="FileSpreadsheet"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                        <Button @click="goToCreate" class="gap-2">
                            <Plus class="h-4 w-4" />
                            Add New Case
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <Card>
                <CardContent class="p-0">
                    <!-- Filters -->
                    <div class="flex flex-wrap items-center justify-end gap-3 p-4">
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
                                :disabled="loading"
                            />
                        </div>
                        
                        <Select v-model="filterState.sort" :disabled="loading">
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
                        
                        <Input 
                            v-model="filterState.year" 
                            type="number" 
                            placeholder="Year" 
                            class="w-24"
                            :disabled="loading"
                        />
                        
                        <Button 
                            variant="ghost" 
                            @click="resetFilters"
                            class="gap-1"
                            :disabled="loading"
                        >
                            <RefreshCw class="h-4 w-4" />
                            <span class="hidden sm:inline">Reset</span>
                        </Button>
                    </div>
                    
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[15%]">G.R. & Date</TableHead>
                                <TableHead class="w-[40%]">Case Title</TableHead>
                                <TableHead class="w-[15%] text-center">Reference</TableHead>
                                <TableHead class="w-[10%] text-center">PDF</TableHead>
                                <TableHead class="w-[20%] text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <!-- Skeleton loading rows -->
                            <template v-if="loading && cases.data?.length === 0">
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
                            
                            <!-- Actual data rows -->
                            <TableRow v-for="item in cases.data" :key="item.id" class="group">
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
                                    <a v-if="item.url" :href="item.url" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline text-sm">
                                        Link <ExternalLink class="h-3 w-3" />
                                    </a>
                                    <span v-else class="text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <a v-if="item.pdf_availability" :href="item.pdf_path" target="_blank" 
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all">
                                        <FileText class="h-4 w-4" />
                                    </a>
                                    <span v-else class="text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="sm" @click="openEdit(item)" :disabled="loading">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="confirmDelete(item.id)" :disabled="loading">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="cases.data?.length === 0 && !loading">
                                <TableCell colspan="5" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-2">
                                        <AlertCircle class="h-8 w-8 text-muted-foreground" />
                                        <p class="text-muted-foreground">No cases found</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
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
                            <Button 
                                variant="outline" 
                                size="sm"
                                @click="fetchData(cases.current_page - 1)" 
                                :disabled="cases.current_page === 1 || loading"
                            >
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

                            <Button 
                                variant="outline" 
                                size="sm"
                                @click="fetchData(cases.current_page + 1)" 
                                :disabled="cases.current_page === cases.last_page || loading"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Edit Dialog Component -->
        <EditCaseDialog 
            v-model:open="showEditModal"
            :case-data="currentCase"
            @saved="refreshData"
        />

        <!-- Delete Dialog Component -->
        <DeleteCaseDialog
            v-model:open="showDeleteDialog"
            :case-id="deleteId"
            @deleted="refreshData"
        />
    </AppLayout>
</template>