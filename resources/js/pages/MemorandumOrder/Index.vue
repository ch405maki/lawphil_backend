<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

// Custom Components - Pinalitan natin ang folder at component name!
import ExcelImportDialog from '@/components/jurisprudence/ExcelImportDialog.vue'; // Pwede mong i-reuse ito kung generic siya!
import MemorandumOrderTable from '@/components/memorandum-orders/MemorandumOrderTable.vue';

// Icons
import { FileSpreadsheet, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const breadcrumbs = [ 
  { title: "Dashboard", href: "/dashboard" }, 
  { title: "Memorandum Orders", href: "#" }
];

// Refresh trigger
const refreshTrigger = ref(0);

// Handle successful import
const handleImportSuccess = (data: any) => {
    toast.success(`Import successful! ${data.imported} records imported.`);
    refreshTable();
};

// Handle import error
const handleImportError = (error: any) => {
    toast.error(error.message || 'Import failed. Please check your file format.');
};

// Refresh table
const refreshTable = () => {
    refreshTrigger.value++;
};

const goToCreate = () => {
    window.location.href = '/memorandum-orders/create';
};
</script>

<template>
    <Head title="Memorandum Orders" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-background p-4">
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Memorandum Orders</h1>
                        <p class="text-muted-foreground">Legal Archives Management System</p>
                    </div>
                    <div class="flex gap-2">
                        <ExcelImportDialog 
                            title="Memorandum Orders"
                            import-url="/api/v1/memorandum-orders/import"
                            trigger-text="Import Excel/CSV"
                            trigger-variant="outline"
                            :trigger-icon="FileSpreadsheet"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                        <Button @click="goToCreate" class="gap-2">
                            <Plus class="h-4 w-4" />
                            Add New Order
                        </Button>
                    </div>
                </div>
            </div>

            <MemorandumOrderTable :refresh-trigger="refreshTrigger" @refresh="refreshTable" />
        </div>
    </AppLayout>
</template>