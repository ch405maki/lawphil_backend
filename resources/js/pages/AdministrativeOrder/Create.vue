<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRightIcon, TriangleAlert, Upload, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import ExcelImportDialog from '@/components/administrative/ExcelImportDialog.vue';
import CreateAdministrativeOrderForm from '@/components/administrative/CreateAdministrativeOrderForm.vue';
import Info from '@/components/administrative/Info.vue';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Administrative Orders',
        href: route('administrative.index'),
    },
    {
        title: 'Create',
        href: '#',
    },
];

const handleImportSuccess = (data: any) => {
    console.log('Import successful:', data);
};

const handleImportError = (error: any) => {
    console.error('Import error:', error);
};

const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/v1/administrative/import/template', {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'administrative_order_template.xlsx');
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
    <Head title="Create Administrative Order" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Create Administrative Order</h1>
                        <p class="text-muted-foreground">
                            Register a new executive issuance to the archives.
                        </p>
                    </div>
                    
                    <div class="flex gap-3 mt-2">
                        <Info />
                        <TooltipProvider>
                            <Tooltip>
                            <TooltipTrigger as-child>
                                <Button 
                                    variant="outline" 
                                    size="icon"
                                    @click="downloadTemplate"
                                >
                                    <Download class="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Download Template</p>
                            </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <ExcelImportDialog 
                            trigger-text="Import Orders"
                            :trigger-icon="Upload"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                    </div>
                </div>
            </div>
            
            <div class="bg-card rounded-lg border p-6 shadow-sm">
                <CreateAdministrativeOrderForm />
            </div>
        </div>
    </AppLayout>
</template>