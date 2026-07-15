<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRightIcon, TriangleAlert, Upload, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import ExcelImportDialog from '@/components/batas-pambansa/ExcelImportDialog.vue';
import CreateBatasPambansaForm from '@/components/batas-pambansa/CreateBatasPambansaForm.vue';
import Info from '@/components/batas-pambansa/Info.vue';
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
        title: 'Batas Pambansa',
        href: '/batas-pambansa',
    },
    {
        title: 'Create',
        href: '/batas-pambansa/create',
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
        const response = await axios.get('/api/v1/batas-pambansa/import/template', {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'batas_pambansa_template.xlsx');
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
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Register New Batas Pambansa</h1>
                        <p class="text-muted-foreground">
                            Register a new batas pambansa record manually.
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
            </div>
            <div>
                <CreateBatasPambansaForm />
            </div>
        </div>
    </AppLayout>
</template>
