<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { 
  Save, 
  RotateCcw
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

const toast = useToast();

// Form data - Refactored for EO
const formData = reactive({
  eo_number: '',
  date: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: ''
});

// Form validation errors
const errors = reactive({
  eo_number: '',
  date: '',
  reference: '',
  url: '',
  subject: '',
  pdf_path: ''
});

const isLoading = ref(false);

// Validate form
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    (errors as any)[key] = '';
  });
  
  // Validate EO Number
  if (!formData.eo_number.trim()) {
    errors.eo_number = 'E.O. Number is required';
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
  formData.eo_number = '';
  formData.date = '';
  formData.reference = '';
  formData.url = '';
  formData.pdf_availability = false;
  formData.subject = '';
  formData.pdf_path = '';
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    (errors as any)[key] = '';
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
    // URL adjusted for EO
    const response = await axios.post('/api/executive-orders', formData);
    
    if (response.data.success) {
      toast.success('Executive Order record created successfully!');
      clearForm();
    }
  } catch (error: any) {
    console.error('Error creating EO:', error);
    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if ((errors as any)[key] !== undefined) {
          (errors as any)[key] = validationErrors[key][0];
        }
      });
      toast.error('Please check the form for errors');
    } else {
      toast.error('Failed to create executive order record');
    }
  } finally {
    isLoading.value = false;
  }
};

const resetForm = () => {
  clearForm();
  toast.info('Form has been reset');
};
</script>

<template>
  <div class="w-full">
    <form @submit.prevent="submitForm" class="space-y-5">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5 text-left">
        <div class="space-y-1.5">
          <Label for="eo_number" class="text-[13px] font-medium text-gray-700">
            E.O. Number <span class="text-red-500">*</span>
          </Label>
          <Input
            id="eo_number"
            v-model="formData.eo_number"
            placeholder="e.g., E.O. No. 01"
            :disabled="isLoading"
            class="h-10 border-gray-200 shadow-none rounded-md"
            :class="{ 'border-red-500': errors.eo_number }"
          />
          <p v-if="errors.eo_number" class="text-[11px] text-red-500 mt-1">{{ errors.eo_number }}</p>
        </div>
        
        <div class="space-y-1.5">
          <Label for="date" class="text-[13px] font-medium text-gray-700">
            Date <span class="text-red-500">*</span>
          </Label>
          <Input
            id="date"
            v-model="formData.date"
            type="date"
            :disabled="isLoading"
            class="h-10 border-gray-200 shadow-none rounded-md"
            :class="{ 'border-red-500': errors.date }"
          />
          <p v-if="errors.date" class="text-[11px] text-red-500 mt-1">{{ errors.date }}</p>
        </div>

        <div class="space-y-1.5">
          <Label for="reference" class="text-[13px] font-medium text-gray-700">Reference</Label>
          <Input
            id="reference"
            v-model="formData.reference"
            placeholder="e.g., Volume 123, Page 456"
            :disabled="isLoading"
            class="h-10 border-gray-200 shadow-none rounded-md"
          />
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
        <div class="space-y-1.5 text-left">
            <Label for="url" class="text-[13px] font-medium text-gray-700">URL</Label>
            <Input
                id="url"
                v-model="formData.url"
                placeholder="Enter URL"
                :disabled="isLoading"
                class="h-10 border-gray-200 shadow-none rounded-md"
            />
        </div>
      </div>
      
      <div class="space-y-1.5 text-left">
        <Label for="subject" class="text-[13px] font-medium text-gray-700">Subject</Label>
        <Textarea
          id="subject"
          v-model="formData.subject"
          placeholder="Enter the subject or topic of the executive order"
          :disabled="isLoading"
          rows="4"
          class="border-gray-200 shadow-none rounded-md resize-none text-[14px]"
        />
        <p v-if="errors.subject" class="text-[11px] text-red-500 mt-1">{{ errors.subject }}</p>
      </div>
      
      <div class="flex items-center gap-4 pt-2">
          <div class="flex items-center space-x-2 min-w-fit">
              <Checkbox
                  id="pdf_availability"
                  :checked="formData.pdf_availability"
                  @update:checked="formData.pdf_availability = $event"
                  :disabled="isLoading"
              />
              <Label for="pdf_availability" class="cursor-pointer text-[13.5px] font-medium text-gray-700">
                  PDF Available
              </Label>
          </div>
          
          <Input
              id="pdf_path"
              v-model="formData.pdf_path"
              placeholder="PDF path (e.g., /uploads/pdfs/eo.pdf)"
              :disabled="isLoading || !formData.pdf_availability"
              class="flex-1 h-10 border-gray-200 shadow-none rounded-md"
          />
      </div>
      
      <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-gray-100 mt-10">
        <p class="text-[13px] text-gray-400 italic">
            <span class="text-red-500 font-bold">*</span> Required fields.
        </p>
        <div class="flex gap-2">
            <Button 
                type="submit" 
                :disabled="isLoading"
                class="bg-[#121212] hover:bg-black text-white px-6 h-10 rounded-md font-bold flex items-center gap-2 shadow-none border-none"
                >
                <Save class="h-4 w-4" />
                {{ isLoading ? 'Creating...' : 'Create Record' }}
            </Button>
                
            <Button 
                type="button" 
                variant="outline" 
                @click="resetForm"
                :disabled="isLoading"
                class="h-10 px-6 rounded-md font-bold border-gray-300 text-gray-600 shadow-none"
                >
                <RotateCcw class="h-4 w-4 mr-1" />
                Reset
            </Button>
        </div>
      </div>
    </form>
  </div>
</template>