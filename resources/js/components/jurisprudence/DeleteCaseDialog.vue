<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import { toast } from 'vue-sonner';

// Shadcn UI Components
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

// Icons
import { Loader2 } from 'lucide-vue-next';

const props = defineProps<{
  open: boolean;
  caseId: number | null;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'deleted'): void;
}>();

const processing = ref(false);

interface ErrorResponse {
  message?: string;
}

const deleteCase = async () => {
  if (!props.caseId) return;
  
  processing.value = true;

  const promise = axios.delete(`/api/jurisprudence/${props.caseId}`, {
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
  });

  toast.promise(promise, {
    loading: 'Deleting case...',
    success: () => {
      emit('update:open', false);
      emit('deleted');
      return 'Case deleted successfully!';
    },
    error: (err: AxiosError<ErrorResponse>) => {
      return err.response?.data?.message || 'Failed to delete case';
    },
    finally: () => {
      processing.value = false;
    }
  });
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
          This action cannot be undone. This will permanently delete the case from the system.
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