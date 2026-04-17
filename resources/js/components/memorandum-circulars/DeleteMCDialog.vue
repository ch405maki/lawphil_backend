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
  mcId: number | null;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'deleted'): void;
}>();

const processing = ref(false);

const deleteMC = async () => {
  if (!props.mcId) return;
  
  processing.value = true;

  try {
    // Tinitiyak na ang URL ay tugma sa route sa web.php
    const response = await axios.delete(`/api/memorandum-circulars/${props.mcId}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });

    if (response.data.success) {
      toast.success('Memorandum Circular deleted successfully!');
      emit('update:open', false);
      emit('deleted');
    } else {
      throw new Error(response.data.message || 'Failed to delete record');
    }
  } catch (error: any) {
    console.error('Error deleting MC:', error);
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
        <AlertDialogTitle>Are you sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete the memorandum circular from the system.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="processing">Cancel</AlertDialogCancel>
        <AlertDialogAction 
          @click="deleteMC" 
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