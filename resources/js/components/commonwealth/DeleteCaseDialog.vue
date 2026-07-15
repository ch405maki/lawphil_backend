<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

import { Button } from '@/components/ui/button';
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

import { Loader2 } from 'lucide-vue-next';

const toast = useToast();

const props = defineProps<{
  open: boolean;
  raId: number | null;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'deleted'): void;
}>();

const processing = ref(false);

const deleteCase = async () => {
  if (!props.raId) return;

  processing.value = true;

  try {
    const response = await axios.delete(`/api/v1/commonwealth/${props.raId}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });

    if (response.data.success) {
      toast.success('Commonwealth deleted successfully!');
      emit('update:open', false);
      emit('deleted');
    } else {
      throw new Error(response.data.message || 'Failed to delete commonwealth');
    }
  } catch (error: any) {
    console.error('Error deleting commonwealth:', error);
    const errorMessage = error.response?.data?.message || error.message || 'Failed to delete commonwealth';
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
          This action cannot be undone. This will permanently delete the commonwealth act from the system.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="processing">Cancel</AlertDialogCancel>
        <AlertDialogAction
          @click="deleteCase"
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
