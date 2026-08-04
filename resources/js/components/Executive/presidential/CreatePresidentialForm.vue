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
  pd_number: '',
  date: '',
  citation: '',
  subject: '',
  tenure: '',
  reference: '',
  url: '',
  pdf_availability: false,
  pdf_path: ''
});

// Form validation errors
const errors = reactive({
  pd_number: '',
  date: '',
  citation: '',
  subject: '',
  tenure: '',
  reference: '',
  url: '',
  pdf_path: ''
});

const isLoading = ref(false);

// Validate form
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = '';
  });
  
  // Validate Presidential Decrees Number
  if (!formData.pd_number.trim()) {
    errors.pd_number = 'Presidential Decrees Number is required';
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
  formData.pd_number = '';
  formData.date = '';
  formData.citation = '';
  formData.subject = '';
  formData.tenure = '';
  formData.reference = '';
  formData.url = '';
  formData.pdf_availability = false;
  formData.pdf_path = '';
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = '';
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
    const response = await axios.post('/api/v1/presidential', formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.data.success) {
      toast.success('Presidential Decree record registered successfully!');
      clearForm();
    } else {
      throw new Error(response.data.message || 'Failed to register record');
    }
    
  } catch (error: any) {
    console.error('Error registering presidential decree:', error);
    
    if (error.response?.data?.errors) {
      // Handle validation errors from server
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if (key in errors) {
          errors[key as keyof typeof errors] = validationErrors[key][0];
        }
      });
      toast.error('Please check the form for errors');
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to register new presidential decree';
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
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <Label for="pd_number">
            P.D. Number <span class="text-red-500">*</span>
          </Label>
          <Input
            id="pd_number"
            v-model="formData.pd_number"
            placeholder="e.g., Presidential Decrees No. 123456"
            :disabled="isLoading"
            :class="{ 'border-red-500': errors.pd_number }"
          />
          <p v-if="errors.pd_number" class="text-sm text-red-500">{{ errors.pd_number }}</p>
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
          <Label for="tenure">Tenure</Label>
          <Input
            id="tenure"
            v-model="formData.tenure"
            placeholder="e.g., Justice Dela Cruz"
            :disabled="isLoading"
            :class="{ 'border-red-500': errors.tenure }"
          />
          <p v-if="errors.tenure" class="text-sm text-red-500">{{ errors.tenure }}</p>
        </div>
      </div>
        
      <div class="grid grid-cols-1 gap-4">
        <div>
          <Label for="citation">Description</Label>
          <Textarea
            id="citation"
            v-model="formData.citation"
            placeholder="Enter description here..."
            :disabled="isLoading"
            rows="3"
            :class="{ 'border-red-500': errors.citation }"
          />
          <p v-if="errors.citation" class="text-sm text-red-500">{{ errors.citation }}</p>
        </div>
      </div>
      
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
                {{ isLoading ? 'Registering...' : 'Register Record' }}
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