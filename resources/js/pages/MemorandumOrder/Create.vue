<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRightIcon, TriangleAlert, Upload, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import ExcelImportDialog from '@/components/jurisprudence/ExcelImportDialog.vue'; // Ginamit natin yung dynamic dialog
import CreateMemorandumOrderForm from '@/components/memorandum-orders/CreateMemorandumOrderForm.vue'; // Bagong form
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
    { title: 'Memorandum Orders', href: '/memorandum-orders' },
    { title: 'Create', href: '#' },
];

const handleImportSuccess = (data: any) => { console.log('Import successful:', data); };
const handleImportError = (error: any) => { console.error('Import error:', error); };

// Note: Pansamantalang naka-disable muna ang template download kung wala pa kayong template file para sa M.O. sa backend.
const downloadTemplate = async () => {
    alert('Template download for Memorandum Orders will be available soon.');
};
</script>

<template>
    <Head title="Create Memorandum Order" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Create Memorandum Order</h1>
                        <p class="text-muted-foreground">
                            Create a new memorandum order record manually.
                        </p>
                    </div>
                    
                    <div class="flex gap-3 mt-2">
                        <Info />
                        <TooltipProvider>
                            <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="outline" size="icon" @click="downloadTemplate">
                                    <Download />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>Download Template</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        
                        <ExcelImportDialog 
                            title="Memorandum Orders"
                            import-url="/api/v1/memorandum-orders/import"
                            trigger-text="Import Excel"
                            :trigger-icon="Upload"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                    </div>
                </div>
            </div>
            
            <div>
                <CreateMemorandumOrderForm />
            </div>
        </div>
    </AppLayout>
</template>