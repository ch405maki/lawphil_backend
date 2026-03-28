<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRightIcon, TriangleAlert, Upload, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import ExcelImportDialog from '@/components/jurisprudence/ExcelImportDialog.vue';
import Info from '@/components/jurisprudence/Info.vue';
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Jurisprudence',
        href: '#',
    },
    {
        title: 'Create',
        href: '#',
    },
];

// Handle successful import
const handleImportSuccess = (data: any) => {
    console.log('Import successful:', data);
    // You can add additional logic here, like refreshing a list
};

// Handle import error
const handleImportError = (error: any) => {
    console.error('Import error:', error);
    // You can add additional error handling here
};

// Download template
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
                        <h1 class="text-2xl font-bold tracking-tight">Import Jurisprudence</h1>
                        <p class="text-muted-foreground">
                            Bulk import jurisprudence records from Excel files. Download the template to get started.
                        </p>
                    </div>
                    
                    <!-- Info Component -->
                    <Info />
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3 mt-2">
                    <ExcelImportDialog 
                        trigger-text="Import Excel"
                        trigger-variant="default"
                        :trigger-icon="Upload"
                        @import-success="handleImportSuccess"
                        @import-error="handleImportError"
                    />
                    
                    <Button 
                        variant="outline" 
                        @click="downloadTemplate"
                        class="gap-2"
                    >
                        <Download class="h-4 w-4" />
                        Download Template
                    </Button>
                </div>
            </div>
            
            <!-- Empty State for Other Features -->
            <Empty>
                <EmptyHeader>
                    <EmptyMedia variant="icon">
                        <TriangleAlert />
                    </EmptyMedia>

                    <EmptyTitle>Additional Features Coming Soon</EmptyTitle>

                    <EmptyDescription>
                        More import options and batch operations are currently under development.
                    </EmptyDescription>
                </EmptyHeader>

                <EmptyContent>
                    <div class="flex gap-2">
                        <Button disabled>Coming Soon</Button>
                        <Button variant="outline" disabled>
                            Under Development
                        </Button>
                    </div>
                </EmptyContent>

                <Button variant="link" as-child class="text-muted-foreground" size="sm">
                    <a href="#">
                        View Development Updates <ArrowUpRightIcon />
                    </a>
                </Button>
            </Empty>
        </div>
    </AppLayout>
</template>