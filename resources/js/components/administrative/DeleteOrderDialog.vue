<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

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

const props = defineProps<{
  open: boolean;
  orderId: number | null;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'deleted'): void;
}>();

const processing = ref(false);

const deleteOrder = async () => {
  if (!props.orderId) return;
  
  processing.value = true;

  try {
    // UPDATED ENDPOINT: /api/v1/administrative
    const response = await axios.delete(`/api/v1/administrative/${props.orderId}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });

    if (response.data.success) {
      toast.success('Administrative Order deleted successfully!');
      emit('update:open', false);
      emit('deleted');
    } else {
      throw new Error(response.data.message || 'Failed to delete order');
    }
  } catch (error: any) {
    console.error('Error deleting order:', error);
    const errorMessage = error.response?.data?.message || error.message || 'Failed to delete order';
    toast.error(errorMessage);
  } finally {
    processing.value = false;
  }
};

const closeDialog = (state: boolean) => {
  if (!processing.value) {
    emit('update:open', state);
  }
};
</script>

<template>
  <AlertDialog :open="open" @update:open="closeDialog">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete the administrative order from the system.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="processing">Cancel</AlertDialogCancel>
        <AlertDialogAction 
          @click.prevent="deleteOrder" 
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