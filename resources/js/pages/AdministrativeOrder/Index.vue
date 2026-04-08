<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

// Custom Components
import ExcelImportDialog from '@/components/administrative/ExcelImportDialog.vue';
import JurisprudenceTable from '@/components/administrative/AdministrativeOrderTable.vue';

// Icons
import { FileSpreadsheet, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const breadcrumbs = [ 
  { title: "Dashboard", href: route('dashboard') }, 
  { title: "Administrative Orders", href: "#" }
];

const refreshTrigger = ref(0);

const handleImportSuccess = (data: any) => {
    toast.success(`Import successful! ${data.imported} records imported.`);
    refreshTable();
};

const handleImportError = (error: any) => {
    toast.error(error.message || 'Import failed. Please check your file format.');
};

const refreshTable = () => {
    refreshTrigger.value++;
};

const goToCreate = () => {
    window.location.href = route('administrative.create');
};
</script>

<template>
    <Head title="Administrative Orders" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-background p-4">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-blue-900">Administrative Orders</h1>
                        <p class="text-muted-foreground">Executive Issuances Management</p>
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
                            Add New Order
                        </Button>
                    </div>
                </div>
            </div>

            <div class="rounded-md border bg-white shadow-sm">
                <JurisprudenceTable 
                    :refresh-trigger="refreshTrigger" 
                    @refresh="refreshTable" 
                />
            </div>
        </div>
    </AppLayout>
</template>