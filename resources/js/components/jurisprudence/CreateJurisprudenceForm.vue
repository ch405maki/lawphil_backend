<script setup lang="ts">
import { ref, reactive } from 'vue';
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

// Form data
const formData = reactive({
  gr_number: '',
  date: '',
  citation: '',
  ponente: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: ''
});

// Form validation errors
const errors = reactive({
  gr_number: '',
  date: '',
  citation: '',
  ponente: '',
  reference: '',
  url: '',
  subject: ''
});

const isLoading = ref(false);

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
  <div>
    <form @submit.prevent="submitForm" class="space-y-4">
      <!-- GR Number, Date Row and Ref -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <Label for="gr_number">
            GR Number <span class="text-red-500">*</span>
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
          <Label for="ponente">Ponente</Label>
          <Input
            id="ponente"
            v-model="formData.ponente"
            placeholder="e.g., Justice Dela Cruz"
            :disabled="isLoading"
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
      
      <!-- PDF Availability Checkbox -->
      <div class="flex items-center space-x-2">
        <Checkbox
          id="pdf_availability"
          v-model:checked="formData.pdf_availability"
          :disabled="isLoading"
        />
        <Label for="pdf_availability" class="cursor-pointer">
          PDF Available
        </Label>
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
  </div>
</template>