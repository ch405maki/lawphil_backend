<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Upload, Download, Copy, Search, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import ExcelImportDialog from '@/components/jurisprudence/ExcelImportDialog.vue';
import CreateJurisprudenceForm from '@/components/jurisprudence/CreateJurisprudenceForm.vue';
import Info from '@/components/jurisprudence/Info.vue';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Jurisprudence', href: '/jurisprudence' },
    { title: 'Create', href: '/Jurisprudence/Create' },
];

const dialogOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const searching = ref(false);
const showResults = ref(false);
const duplicateData = ref<any>(null);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const searchCases = async (query: string) => {
  if (!query.trim()) {
    searchResults.value = [];
    showResults.value = false;
    return;
  }
  searching.value = true;
  try {
    const { data } = await axios.get('/api/public/jurisprudence', {
      params: { search: query, rows: 10 }
    });
    searchResults.value = data.data ?? [];
    showResults.value = true;
  } catch {
    searchResults.value = [];
  } finally {
    searching.value = false;
  }
};

watch(searchQuery, (val) => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => searchCases(val), 300);
});

const selectResult = (item: any) => {
  duplicateData.value = { ...item };
  searchQuery.value = '';
  showResults.value = false;
  dialogOpen.value = false;
};

const clearDuplicate = () => {
  duplicateData.value = null;
  searchQuery.value = '';
};

// Handle successful import
const handleImportSuccess = (data: any) => {
    console.log('Import successful:', data);
};

const handleImportError = (error: any) => {
    console.error('Import error:', error);
};

const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/v1/jurisprudence/import/template', {
            responseType: 'blob'
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'jurisprudence_template.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Template download failed:', error);
        alert('Failed to download template');
    }
};
</script>

<template>
    <Head title="Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Create Jurisprudence Record</h1>
                        <p class="text-muted-foreground">
                            Create a new jurisprudence record manually.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mt-2">
                        <Dialog v-model:open="dialogOpen">
                            <TooltipProvider>
                                <Tooltip>
                                <TooltipTrigger as-child>
                                    <DialogTrigger as-child>
                                        <Button variant="outline" size="icon">
                                            <Copy />
                                        </Button>
                                    </DialogTrigger>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Duplicate from existing</p>
                                </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                            <DialogContent class="sm:max-w-lg">
                                <DialogHeader>
                                    <DialogTitle>Duplicate from existing record</DialogTitle>
                                    <DialogDescription>
                                        Search by GR number or citation, then select a record to auto-fill the form.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="flex items-center gap-2 rounded-md border border-input bg-background px-3 ring-offset-background focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
                                    <Search class="h-4 w-4 shrink-0 text-muted-foreground" />
                                    <Input
                                        v-model="searchQuery"
                                        placeholder="Search GR number or citation..."
                                        @focus="showResults = searchResults.length > 0"
                                        @blur="setTimeout(() => showResults = false, 300)"
                                        class="border-0 px-0 focus-visible:ring-0 focus-visible:ring-offset-0"
                                    />
                                    <Loader2 v-if="searching" class="h-4 w-4 shrink-0 animate-spin text-muted-foreground" />
                                </div>

                                    <div v-if="showResults && searchResults.length > 0" class="mt-2 max-h-60 overflow-y-auto border rounded-md bg-popover shadow-sm">
                                        <button
                                            v-for="item in searchResults"
                                            :key="item.id"
                                            type="button"
                                            @mousedown.prevent="selectResult(item)"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-accent transition-colors border-b last:border-b-0"
                                        >
                                            <span class="font-medium">{{ item.gr_number }}</span>
                                            <span v-if="item.citation" class="text-muted-foreground ml-2">— {{ item.citation }}</span>
                                            <br v-if="item.ponente" />
                                            <span v-if="item.ponente" class="text-xs text-muted-foreground">{{ item.ponente }}</span>
                                        </button>
                                    </div>
                            </DialogContent>
                        </Dialog>

                        <Info />
                        <TooltipProvider>
                            <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="outline" size="icon" @click="downloadTemplate">
                                    <Download />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Download Template</p>
                            </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <ExcelImportDialog 
                            trigger-text="Import Excel"
                            :trigger-icon="Upload"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                    </div>
                </div>

                <!-- Duplicate indicator -->
                <span v-if="duplicateData" class="flex items-center gap-1.5 text-xs text-green-600">
                    <span>✓</span>
                    <span>Record loaded — fill in GR &amp; date</span>
                    <button type="button" @click="clearDuplicate" class="underline hover:text-green-800 ml-1">Clear</button>
                </span>
            </div>

            <!-- Manual Creation Form -->
            <div>
                <CreateJurisprudenceForm :duplicate-data="duplicateData" @filled="clearDuplicate" />
            </div>
        </div>
    </AppLayout>
</template>
