<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner'; // In-align ko sa sonner toast mo para consistent

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

interface MCData {
  id: number | null;
  mc_number: string;
  date: string;
  reference: string;
  url: string;
  pdf_availability?: boolean;
  subject: string;
  pdf_path: string;
}

interface ValidationErrors {
  mc_number?: string[];
  date?: string[];
  reference?: string[];
  url?: string[];
  subject?: string[];
  pdf_path?: string[];
}

const props = defineProps<{
  open: boolean;
  mcData: MCData;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});

const formData = ref({
  mc_number: '',
  date: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: '',
});

// Watch for mcData changes to populate form
watch(() => props.mcData, (newData) => {
  if (newData && newData.id) {
    formData.value = {
      mc_number: newData.mc_number || '',
      date: newData.date ? new Date(newData.date).toISOString().split('T')[0] : '',
      reference: newData.reference || '',
      url: newData.url || '',
      pdf_availability: !!newData.pdf_availability,
      subject: newData.subject || '',
      pdf_path: newData.pdf_path || '',
    };
  }
}, { immediate: true });

const updateMC = async () => {
  errors.value = {};
  processing.value = true;

  try {
    const response = await axios.post(
      `/api/memorandum-circulars/${props.mcData.id}`,
      {
        mc_number: formData.value.mc_number,
        date: formData.value.date,
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
      toast.success('M.C. updated successfully!');
      emit('update:open', false);
      emit('saved');
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
      toast.error('Please check the form for errors');
    } else {
      toast.error(error.response?.data?.message || 'Failed to update record');
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
        <DialogTitle>Edit Memorandum Circular</DialogTitle>
        <DialogDescription>
          Update the M.C. details below.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-4 py-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="mc_number">M.C. Number</Label>
            <Input 
              id="mc_number" 
              v-model="formData.mc_number" 
              :class="{'border-destructive': errors.mc_number}" 
              :disabled="processing"
            />
            <p v-if="errors.mc_number" class="text-xs text-destructive">{{ errors.mc_number[0] }}</p>
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
          <Label for="subject">Subject / Description</Label>
          <Textarea 
            id="subject" 
            v-model="formData.subject" 
            :class="{'border-destructive': errors.subject}" 
            rows="3"
            :disabled="processing"
          />
          <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="reference">Reference</Label>
            <Input 
              id="reference" 
              v-model="formData.reference" 
              :disabled="processing"
            />
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
        <Button @click="updateMC" :disabled="processing">
          <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
          {{ processing ? 'Updating...' : 'Save Changes' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>