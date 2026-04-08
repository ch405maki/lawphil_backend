<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner'; // Inadjust ko sa sonner para consistent sa index mo

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

interface EOData {
  id: number | null;
  eo_number: string;
  date: string;
  reference: string;
  url: string;
  pdf_availability?: boolean;
  subject: string;
  pdf_path: string;
}

interface ValidationErrors {
  eo_number?: string[];
  date?: string[];
  reference?: string[];
  url?: string[];
  subject?: string[];
  pdf_path?: string[];
}

const props = defineProps<{
  open: boolean;
  eoData: EOData;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});

const formData = ref({
  eo_number: '',
  date: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: '',
});

// Watch for eoData changes to populate form
watch(() => props.eoData, (newData) => {
  if (newData && newData.id) {
    formData.value = {
      eo_number: newData.eo_number || '',
      date: newData.date ? new Date(newData.date).toISOString().split('T')[0] : '',
      reference: newData.reference || '',
      url: newData.url || '',
      pdf_availability: !!newData.pdf_availability,
      subject: newData.subject || '',
      pdf_path: newData.pdf_path || '',
    };
  }
}, { immediate: true });

const updateEO = async () => {
  errors.value = {};
  processing.value = true;

  try {
    // API endpoint adjusted to match our EO route
    const response = await axios.post(`/api/executive-orders/${props.eoData.id}`, {
      ...formData.value,
      _method: 'POST' // O 'PUT' depende sa route setup mo, pero post-with-id is common
    });

    if (response.data.success) {
      toast.success('Executive Order updated successfully!');
      emit('update:open', false);
      emit('saved');
    }
  } catch (error: any) {
    console.error('Error updating EO:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
      toast.error('Please check the form for errors');
    } else {
      toast.error('Failed to update executive order');
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
        <DialogTitle>Edit Executive Order</DialogTitle>
        <DialogDescription>
          Update the issuance details below.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-4 py-4 text-left">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="eo_number">E.O. Number</Label>
            <Input 
              id="eo_number" 
              v-model="formData.eo_number" 
              :class="{'border-destructive': errors.eo_number}" 
              :disabled="processing"
            />
            <p v-if="errors.eo_number" class="text-xs text-destructive">{{ errors.eo_number[0] }}</p>
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
          <Label for="reference">Reference</Label>
          <Input 
            id="reference" 
            v-model="formData.reference" 
            :disabled="processing"
            placeholder="e.g. Vol 108"
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

        <div class="space-y-2">
          <Label for="subject">Subject</Label>
          <Textarea 
            id="subject" 
            v-model="formData.subject" 
            :class="{'border-destructive': errors.subject}" 
            rows="4"
            placeholder="Enter the subject or topic"
            :disabled="processing"
          />
          <p v-if="errors.subject" class="text-xs text-destructive">{{ errors.subject[0] }}</p>
        </div>

        <div class="flex items-center gap-4 pt-2">
          <div class="flex items-center space-x-2 min-w-fit">
            <Checkbox
              id="pdf_availability"
              :checked="formData.pdf_availability"
              @update:checked="formData.pdf_availability = $event"
              :disabled="processing"
            />
            <Label for="pdf_availability" class="cursor-pointer whitespace-nowrap">
              PDF Available
            </Label>
          </div>
          
          <Input 
            id="pdf_path" 
            v-model="formData.pdf_path" 
            placeholder="Enter PDF path"
            :class="{'border-destructive': errors.pdf_path}" 
            :disabled="processing || !formData.pdf_availability"
            class="flex-1"
          />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="closeDialog" :disabled="processing">
          Cancel
        </Button>
        <Button @click="updateEO" :disabled="processing" class="bg-[#121212] hover:bg-black">
          <Loader2 v-if="processing" class="h-4 w-4 mr-2 animate-spin" />
          {{ processing ? 'Updating...' : 'Save Changes' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>