<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import { useToast } from 'vue-toastification';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
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

const toast = useToast();

interface CaseData {
  id: number | null;
  gr_number: string;
  date: string;
  citation: string;
  ponente: string;
  reference: string;
  url: string;
  pdf_availability?: boolean;
  subject: string;
  pdf_path: string;
}

interface ValidationErrors {
  gr_number?: string[];
  date?: string[];
  citation?: string[];
  ponente?: string[];
  reference?: string[];
  url?: string[];
  pdf_availability?: string[];
  subject?: string[];
  pdf_path?: string[];
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
const perCuriam = ref(false);

watch(perCuriam, (val) => {
  if (val) {
    formData.value.ponente = 'Per Curiam';
  } else {
    formData.value.ponente = '';
  }
});

const formData = ref({
  gr_number: '',
  date: '',
  citation: '',
  ponente: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: '',
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
      pdf_availability: newData.pdf_availability || false,
      subject: newData.subject || '',
      pdf_path: newData.pdf_path || '',
    };
    perCuriam.value = newData.ponente === 'Per Curiam';
  }
}, { immediate: true });

const updateCase = async () => {
  errors.value = {};
  processing.value = true;

  try {
    const response = await axios.post(
      `/api/jurisprudence/${props.caseData.id}`,
      {
        gr_number: formData.value.gr_number,
        date: formData.value.date,
        citation: formData.value.citation,
        ponente: formData.value.ponente,
        reference: formData.value.reference,
        url: formData.value.url,
        pdf_availability: formData.value.pdf_availability,
        subject: formData.value.subject,
        pdf_path: formData.value.pdf_path,
      },
      {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      }
    );

    if (response.data.success) {
      toast.success('Case updated successfully!');
      emit('update:open', false);
      emit('saved');
    } else {
      throw new Error(response.data.message || 'Failed to update case');
    }
  } catch (error: any) {
    console.error('Error updating case:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
      toast.error('Please check the form for errors');
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to update case';
      toast.error(errorMessage);
    }
  } finally {
    processing.value = false;
  }
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
            <div class="flex items-center gap-2">
              <Label for="ponente" class="mb-0">Ponente</Label>
              <div class="flex items-center gap-1.5">
                <Checkbox id="per_curiam" v-model:checked="perCuriam" :disabled="processing" />
                <Label for="per_curiam" class="text-sm font-medium cursor-pointer mb-0">Per Curiam</Label>
              </div>
            </div>
            <Input
              id="ponente"
              v-model="formData.ponente"
              :disabled="processing || perCuriam"
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

        <!-- Subject Field -->
        <div class="space-y-2">
          <Label for="subject">Subject</Label>
          <Textarea 
            id="subject" 
            v-model="formData.subject" 
            :class="{'border-destructive': errors.subject}" 
            rows="2"
            placeholder="Enter the subject or topic of the case"
            :disabled="processing"
          />
          <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject[0] }}</p>
        </div>

        <!-- PDF Availability Checkbox -->
        <div class="flex items-start gap-4">
          <div class="flex items-center space-x-2 pt-2">
            <Checkbox
              id="pdf_availability"
              v-model:checked="formData.pdf_availability"
              :disabled="processing"
            />
            <Label for="pdf_availability" class="cursor-pointer whitespace-nowrap">
              PDF Available
            </Label>
          </div>
          
          <div class="flex-1">
            <Input 
              id="pdf_path" 
              v-model="formData.pdf_path" 
              type="text"
              placeholder="Enter PDF path or URL"
              :class="{'border-destructive': errors.pdf_path}" 
              :disabled="processing || !formData.pdf_availability"
              class="w-full"
            />
            <p v-if="errors.pdf_path" class="text-xs text-destructive mt-1">{{ errors.pdf_path[0] }}</p>
          </div>
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