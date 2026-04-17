<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Upload, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import axios from 'axios';


import Info from '@/components/jurisprudence/Info.vue';
import ExcelImportDialog from '@/components/jurisprudence/ExcelImportDialog.vue';


import CreateExecutiveOrderForm from '@/components/executive-orders/CreateExecutiveOrderForm.vue';

import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Executive Orders',
        href: '/executive-orders',
    },
    {
        title: 'Create',
        href: '#',
    },
];

// Handle successful import
const handleImportSuccess = (data: any) => {
    console.log('Import successful:', data);
};

// Handle import error
const handleImportError = (error: any) => {
    console.error('Import error:', error);
};

// Download template logic
const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/v1/executive-orders/import/template', {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'executive_order_template.xlsx');
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
    <Head title="Create Executive Order" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Create Executive Order</h1>
                        <p class="text-muted-foreground">
                            Create a new executive order record manually.
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
                            trigger-text="Import Excel"
                            :trigger-icon="Upload"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg border border-gray-100 p-0 shadow-sm">
                <CreateExecutiveOrderForm />
            </div>
        </div>
    </AppLayout>
</template>