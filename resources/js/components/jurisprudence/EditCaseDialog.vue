<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import { toast } from 'vue-sonner';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

// Icons
import { Loader2 } from 'lucide-vue-next';

interface CaseData {
  id: number | null;
  gr_number: string;
  date: string;
  citation: string;
  ponente: string;
  reference: string;
  url: string;
}

interface ValidationErrors {
  gr_number?: string[];
  date?: string[];
  citation?: string[];
  ponente?: string[];
  reference?: string[];
  url?: string[];
}

interface ErrorResponse {
  message?: string;
  errors?: ValidationErrors;
}

const props = defineProps<{
  open: boolean;
  caseData: CaseData;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});

const formData = ref({
  gr_number: '',
  date: '',
  citation: '',
  ponente: '',
  reference: '',
  url: '',
});

// Watch for caseData changes to populate form
watch(() => props.caseData, (newData) => {
  if (newData && newData.id) {
    formData.value = {
      gr_number: newData.gr_number || '',
      date: newData.date ? new Date(newData.date).toISOString().split('T')[0] : '',
      citation: newData.citation || '',
      ponente: newData.ponente || '',
      reference: newData.reference || '',
      url: newData.url || '',
    };
  }
}, { immediate: true });

const updateCase = async () => {
  errors.value = {};
  processing.value = true;

  const promise = axios.post(
    `/api/jurisprudence/${props.caseData.id}`,
    {
      gr_number: formData.value.gr_number,
      date: formData.value.date,
      citation: formData.value.citation,
      ponente: formData.value.ponente,
      reference: formData.value.reference,
      url: formData.value.url,
    },
    {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    }
  );

  toast.promise(promise, {
    loading: 'Updating case...',
    success: () => {
      emit('update:open', false);
      emit('saved');
      return 'Case updated successfully!';
    },
    error: (err: AxiosError<ErrorResponse>) => {
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors;
        return 'Please check the form for errors';
      }
      return err.response?.data?.message || 'Failed to update case';
    },
    finally: () => {
      processing.value = false;
    }
  });
};

const closeDialog = () => {
  if (!processing.value) {
    emit('update:open', false);
    errors.value = {};
  }
};
</script>

<template>
  <Dialog :open="open" @update:open="closeDialog">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Case Information</DialogTitle>
        <DialogDescription>
          Update the case details below.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-4 py-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="gr_number">G.R. Number</Label>
            <Input 
              id="gr_number" 
              v-model="formData.gr_number" 
              :class="{'border-destructive': errors.gr_number}" 
              :disabled="processing"
            />
            <p v-if="errors.gr_number" class="text-xs text-destructive">{{ errors.gr_number[0] }}</p>
          </div>
          <div class="space-y-2">
            <Label for="date">Date</Label>
            <Input 
              id="date" 
              type="date" 
              v-model="formData.date" 
              :class="{'border-destructive': errors.date}" 
              :disabled="processing"
            />
            <p v-if="errors.date" class="text-xs text-destructive">{{ errors.date[0] }}</p>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="citation">Case Citation / Title</Label>
          <Textarea 
            id="citation" 
            v-model="formData.citation" 
            :class="{'border-destructive': errors.citation}" 
            rows="3"
            :disabled="processing"
          />
          <p v-if="errors.citation" class="text-xs text-destructive">{{ errors.citation[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="ponente">Ponente</Label>
            <Input 
              id="ponente" 
              v-model="formData.ponente" 
              :disabled="processing"
            />
          </div>
          <div class="space-y-2">
            <Label for="reference">Reference</Label>
            <Input 
              id="reference" 
              v-model="formData.reference" 
              :disabled="processing"
            />
          </div>
        </div>

        <div class="space-y-2">
          <Label for="url">Reference URL</Label>
          <Input 
            id="url" 
            v-model="formData.url" 
            placeholder="https://..." 
            :disabled="processing"
          />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="closeDialog" :disabled="processing">
          Cancel
        </Button>
        <Button @click="updateCase" :disabled="processing">
          <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
          {{ processing ? 'Updating...' : 'Save Changes' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>