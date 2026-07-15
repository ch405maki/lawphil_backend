<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import ExcelImportDialog from '@/components/acts/ExcelImportDialog.vue';
import ActsTable from '@/components/acts/ActsTable.vue';
import { FileSpreadsheet, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Acts", href: "#" }
];

const props = defineProps({
    acts: Object,
});

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
    window.location.href = '/acts/create';
};
</script>

<template>
    <Head title="Acts" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-background p-4">
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Acts</h1>
                        <p class="text-muted-foreground">Legal Archives Management System</p>
                    </div>
                    <div class="flex gap-2">
                        <ExcelImportDialog v-if="can('acts', 'create')"
                            trigger-text="Import Excel/CSV"
                            trigger-variant="outline"
                            :trigger-icon="FileSpreadsheet"
                            @import-success="handleImportSuccess"
                            @import-error="handleImportError"
                        />
                        <Button v-if="can('acts', 'create')" @click="goToCreate" class="gap-2">
                            <Plus class="h-4 w-4" />
                            Add New
                        </Button>
                    </div>
                </div>
            </div>
            <ActsTable :refresh-trigger="refreshTrigger" @refresh="refreshTable" />
        </div>
    </AppLayout>
</template>
