<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { 
  Save, 
  X
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

const toast = useToast();

const props = defineProps<{
  duplicateData?: any | null;
}>();

const emit = defineEmits<{
  filled: [];
}>();

// Form data
const formData = reactive({
  gr_number: '',
  date: '',
  citation: '',
  ponente: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: ''
});

// Form validation errors
const errors = reactive({
  gr_number: '',
  date: '',
  citation: '',
  ponente: '',
  reference: '',
  url: '',
  subject: '',
  pdf_path: ''
});

const isLoading = ref(false);
const perCuriam = ref(false);

watch(perCuriam, (val) => {
  if (val) {
    formData.ponente = 'Per Curiam';
  } else {
    formData.ponente = '';
  }
});

// Auto-fill from duplicate selection
watch(() => props.duplicateData, (val) => {
  if (!val) return;
  formData.citation = val.citation ?? '';
  formData.ponente = val.ponente ?? '';
  formData.reference = val.reference ?? '';
  formData.url = val.url ?? '';
  formData.pdf_availability = val.pdf_availability ?? false;
  formData.subject = val.subject ?? '';
  formData.pdf_path = val.pdf_path ?? '';
  toast.info('Record duplicated — fill in GR & date.');
  emit('filled');
});

// Validate form
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  
  // Validate GR Number
  if (!formData.gr_number.trim()) {
    errors.gr_number = 'GR Number is required';
    isValid = false;
  }
  
  // Validate Date
  if (!formData.date) {
    errors.date = 'Date is required';
    isValid = false;
  }
  
  return isValid;
};

// Clear form fields
const clearForm = () => {
  formData.gr_number = '';
  formData.date = '';
  formData.citation = '';
  formData.ponente = '';
  formData.reference = '';
  formData.url = '';
  formData.pdf_availability = false;
  formData.subject = '';
  formData.pdf_path = '';
  perCuriam.value = false;
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
};

// Submit form
const submitForm = async () => {
  if (!validateForm()) {
    toast.error('Please fix the validation errors');
    return;
  }
  
  isLoading.value = true;
  
  try {
    const response = await axios.post('/api/jurisprudence', formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.data.success) {
      toast.success('Jurisprudence record created successfully!');
      clearForm();
    } else {
      throw new Error(response.data.message || 'Failed to create record');
    }
    
  } catch (error: any) {
    console.error('Error creating jurisprudence:', error);
    
    if (error.response?.data?.errors) {
      // Handle validation errors from server
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if (errors[key] !== undefined) {
          errors[key] = validationErrors[key][0];
        }
      });
      toast.error('Please check the form for errors');
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to create jurisprudence record';
      toast.error(errorMessage);
    }
  } finally {
    isLoading.value = false;
  }
};

// Reset form (clear all fields and errors)
const resetForm = () => {
  clearForm();
  toast.info('Form has been reset');
};
</script>

<template>
  <form @submit.prevent="submitForm" class="space-y-4">
    <!-- GR Number, Date Row and Ref -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <Label for="gr_number">
          GR <span class="text-red-500">*</span>
        </Label>
        <Input
          id="gr_number"
          v-model="formData.gr_number"
          placeholder="e.g., G.R. No. 123456"
          :disabled="isLoading"
          :class="{ 'border-red-500': errors.gr_number }"
        />
        <p v-if="errors.gr_number" class="text-sm text-red-500">{{ errors.gr_number }}</p>
      </div>
      
      <div>
        <Label for="date">
          Date <span class="text-red-500">*</span>
        </Label>
        <Input
          id="date"
          v-model="formData.date"
          type="date"
          :disabled="isLoading"
          :class="{ 'border-red-500': errors.date }"
        />
        <p v-if="errors.date" class="text-sm text-red-500">{{ errors.date }}</p>
      </div>

      <div>
        <Label for="reference">Reference</Label>
        <Input
          id="reference"
          v-model="formData.reference"
          placeholder="e.g., Volume 123, Page 456"
          :disabled="isLoading"
          :class="{ 'border-red-500': errors.reference }"
        />
        <p v-if="errors.reference" class="text-sm text-red-500">{{ errors.reference }}</p>
      </div>
    </div>
    
    <!-- Url and Ponente Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
          <Label for="url">URL</Label>
          <Input
              id="url"
              v-model="formData.url"
              type="text"
              placeholder="Enter URL or Rel. Path"
              :disabled="isLoading"
              :class="{ 'border-red-500': errors.url }"
          />
          <p v-if="errors.url" class="text-sm text-red-500">{{ errors.url }}</p>
      </div>
      <div>
        <div class="flex items-center gap-2 mb-1.5">
          <Label for="ponente" class="mb-0">Ponente</Label>
          <div class="flex items-center gap-1.5">
            <Checkbox id="per_curiam" v-model:checked="perCuriam" :disabled="isLoading" />
            <Label for="per_curiam" class="text-sm font-medium cursor-pointer mb-0">Per Curiam</Label>
          </div>
        </div>
        <Input
          id="ponente"
          v-model="formData.ponente"
          placeholder="e.g., Justice Dela Cruz"
          :disabled="isLoading || perCuriam"
          :class="{ 'border-red-500': errors.ponente }"
        />
        <p v-if="errors.ponente" class="text-sm text-red-500">{{ errors.ponente }}</p>
      </div>
    </div>
      
    <!-- Citation -->
    <div class="grid grid-cols-1 gap-4">
      <div>
        <Label for="citation">Citation</Label>
        <Textarea
          id="citation"
          v-model="formData.citation"
          placeholder="e.g., 123 SCRA 456"
          :disabled="isLoading"
          :class="{ 'border-red-500': errors.citation }"
        />
        <p v-if="errors.citation" class="text-sm text-red-500">{{ errors.citation }}</p>
      </div>
    </div>
    
    <!-- Subject -->
    <div>
      <Label for="subject">Subject</Label>
      <Textarea
        id="subject"
        v-model="formData.subject"
        placeholder="Enter the subject or topic of the case"
        :disabled="isLoading"
        rows="3"
        :class="{ 'border-red-500': errors.subject }"
      />
      <p v-if="errors.subject" class="text-sm text-red-500">{{ errors.subject }}</p>
    </div>
    
    <!-- PDF Availability Section - Inline with wider input -->
    <div class="space-y-2">
        <div class="flex items-start gap-4">
            <div class="flex items-center space-x-2 pt-2">
                <Checkbox
                    id="pdf_availability"
                    v-model:checked="formData.pdf_availability"
                    :disabled="isLoading"
                />
                <Label for="pdf_availability" class="cursor-pointer">
                    PDF Available
                </Label>
            </div>
            
            <div class="flex-1">
                <Input
                    id="pdf_path"
                    v-model="formData.pdf_path"
                    type="text"
                    placeholder="Enter PDF path or URL (e.g., /uploads/pdfs/case.pdf)"
                    :disabled="isLoading || !formData.pdf_availability"
                    :class="{ 'border-red-500': errors.pdf_path }"
                    class="w-full"
                />
                <p v-if="errors.pdf_path" class="text-sm text-red-500 mt-1">{{ errors.pdf_path }}</p>
                <p v-if="formData.pdf_availability" class="text-xs text-muted-foreground mt-1">
                    You can enter a relative path or full URL to the PDF file
                </p>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="flex gap-2 justify-between border-t pt-6 mt-6">
      <p class="text-sm text-muted-foreground">
          <span class="text-red-500">*</span> Required fields. All other fields are optional and will be stored as NULL if empty.
      </p>
      <div class="flex gap-2">
          <Button 
              type="submit" 
              :disabled="isLoading"
              class="gap-2"
              >
              <Save class="h-4 w-4" />
              {{ isLoading ? 'Creating...' : 'Create Record' }}
          </Button>
              
          <Button 
              type="button" 
              variant="outline" 
              @click="resetForm"
              :disabled="isLoading"
              class="gap-2"
              >
              <X class="h-4 w-4" />
              Reset
          </Button>
      </div>
    </div>
  </form>
</template>
