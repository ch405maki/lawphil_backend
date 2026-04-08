<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

// Shadcn UI Components
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

// Icons
import { Loader2 } from 'lucide-vue-next';

const toast = useToast();

const props = defineProps<{
  open: boolean;
  eoId: number | null; // Refactored mula caseId
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'deleted'): void;
}>();

const processing = ref(false);

const deleteEO = async () => {
  if (!props.eoId) return;
  
  processing.value = true;

  try {
    // API endpoint inadjust para sa Executive Orders
    const response = await axios.delete(`/api/executive-orders/${props.eoId}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });

    if (response.data.success) {
      toast.success('Executive Order deleted successfully!');
      emit('update:open', false);
      emit('deleted');
    } else {
      throw new Error(response.data.message || 'Failed to delete record');
    }
  } catch (error: any) {
    console.error('Error deleting EO:', error);
    const errorMessage = error.response?.data?.message || error.message || 'Failed to delete record';
    toast.error(errorMessage);
  } finally {
    processing.value = false;
  }
};

const closeDialog = () => {
  if (!processing.value) {
    emit('update:open', false);
  }
};
</script>

<template>
  <AlertDialog :open="open" @update:open="closeDialog">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete the Executive Order record from the system.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="processing">Cancel</AlertDialogCancel>
        <AlertDialogAction 
          @click="deleteEO" 
          class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
          :disabled="processing"
        >
          <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
          {{ processing ? 'Deleting...' : 'Delete' }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>