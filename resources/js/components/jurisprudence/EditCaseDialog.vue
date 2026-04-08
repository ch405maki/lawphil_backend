<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
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
import { Loader2, Save } from 'lucide-vue-next';

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

const subjectOptions = [
  'Political and Constitutional Law',
  'Labor Law and Social Legislation',
  'Civil Law',
  'Taxation Law',
  'Mercantile and Commercial Law',
  'Criminal Law',
  'Remedial Law',
  'Legal and Judicial Ethics',
  'Administrative Law',
  'Contract and Obligations',
  'Property and Real Estate Law',
  'Family Law',
  'Public and Private International Law',
  'Environmental and Natural Resources Law'
];

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
      pdf_availability: !!newData.pdf_availability,
      subject: newData.subject || '',
      pdf_path: newData.pdf_path || '',
    };
  }
}, { immediate: true });

const updateCase = async () => {
  errors.value = {};
  processing.value = true;

  try {
    const response = await axios.post(
      `/api/jurisprudence/${props.caseData.id}`,
      formData.value,
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
    <DialogContent class="max-w-3xl max-h-[95vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Case Information</DialogTitle>
        <DialogDescription>
          Modify the case details below to update the jurisprudence record.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-6 py-4">
        <div class="space-y-2">
          <Label for="citation">Citation / Case Title</Label>
          <Textarea
            id="citation"
            v-model="formData.citation"
            placeholder="e.g., People of the Philippines vs. Juan Dela Cruz"
            :disabled="processing"
            :class="{ 'border-destructive': errors.citation }"
            rows="2"
          />
          <p v-if="errors.citation" class="text-xs text-destructive">{{ errors.citation[0] }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="ponente">Ponente</Label>
            <Input
              id="ponente"
              v-model="formData.ponente"
              placeholder="e.g., Justice Dela Cruz"
              :disabled="processing"
              :class="{ 'border-destructive': errors.ponente }"
            />
            <p v-if="errors.ponente" class="text-xs text-destructive">{{ errors.ponente[0] }}</p>
          </div>
          <div class="space-y-2">
            <Label for="subject">Subject Area</Label>
            <select
              id="subject"
              v-model="formData.subject"
              :disabled="processing"
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              :class="{ 'border-destructive': errors.subject }"
            >
              <option value="" disabled>Select the law subject</option>
              <option v-for="option in subjectOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
            <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-2">
            <Label for="gr_number">
              GR Number <span class="text-destructive">*</span>
            </Label>
            <Input
              id="gr_number"
              v-model="formData.gr_number"
              placeholder="e.g., G.R. No. 123456"
              :disabled="processing"
              :class="{ 'border-destructive': errors.gr_number }"
            />
            <p v-if="errors.gr_number" class="text-xs text-destructive">{{ errors.gr_number[0] }}</p>
          </div>
          
          <div class="space-y-2">
            <Label for="date">
              Date <span class="text-destructive">*</span>
            </Label>
            <Input
              id="date"
              v-model="formData.date"
              type="date"
              :disabled="processing"
              :class="{ 'border-destructive': errors.date }"
            />
            <p v-if="errors.date" class="text-xs text-destructive">{{ errors.date[0] }}</p>
          </div>

          <div class="space-y-2">
            <Label for="reference">Reference / Volume</Label>
            <Input
              id="reference"
              v-model="formData.reference"
              placeholder="e.g., Vol 123, Page 45"
              :disabled="processing"
              :class="{ 'border-destructive': errors.reference }"
            />
            <p v-if="errors.reference" class="text-xs text-destructive">{{ errors.reference[0] }}</p>
          </div>
        </div>
        
        <div class="space-y-2">
            <Label for="url">Source URL</Label>
            <Input
                id="url"
                v-model="formData.url"
                placeholder="e.g., https://lawphil.net/..."
                :disabled="processing"
                :class="{ 'border-destructive': errors.url }"
            />
            <p v-if="errors.url" class="text-xs text-destructive">{{ errors.url[0] }}</p>
        </div>
          
        <div class="p-4 border rounded-lg bg-slate-50/50">
            <div class="flex items-start gap-4">
                <div class="flex items-center space-x-2 pt-2">
                    <Checkbox
                        id="pdf_availability"
                        v-model:checked="formData.pdf_availability"
                        :disabled="processing"
                    />
                    <Label for="pdf_availability" class="cursor-pointer font-medium whitespace-nowrap">
                        PDF Available
                    </Label>
                </div>
                
                <div class="flex-1 space-y-2">
                    <Input
                        id="pdf_path"
                        v-model="formData.pdf_path"
                        placeholder="Enter path (e.g., /storage/juris/case.pdf)"
                        :disabled="processing || !formData.pdf_availability"
                        :class="{ 'border-destructive': errors.pdf_path }"
                        class="bg-white"
                    />
                    <p v-if="errors.pdf_path" class="text-xs text-destructive">{{ errors.pdf_path[0] }}</p>
                </div>
            </div>
        </div>
      </div>

      <DialogFooter class="border-t pt-6">
        <div class="flex w-full justify-between items-center">
            <p class="text-xs text-muted-foreground italic">
                <span class="text-destructive">*</span> GR Number and Date are required.
            </p>
            <div class="flex gap-2">
                <Button variant="outline" @click="closeDialog" :disabled="processing">
                  Cancel
                </Button>
                <Button @click="updateCase" :disabled="processing" class="gap-2">
                  <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                  <Save v-else class="h-4 w-4" />
                  {{ processing ? 'Updating...' : 'Save Changes' }}
                </Button>
            </div>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>