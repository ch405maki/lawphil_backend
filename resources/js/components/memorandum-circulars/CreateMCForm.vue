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

// Form data (MC Specific - Pinalitan ang gr_number ng mc_number)
const formData = reactive({
  mc_number: '',
  date: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: ''
});

// Form validation errors
const errors = reactive({
  mc_number: '',
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
  
  // Validate MC Number
  if (!formData.mc_number.trim()) {
    errors.mc_number = 'M.C. Number is required';
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
  formData.mc_number = '';
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
    const response = await axios.post('/api/memorandum-circulars', formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.data.success) {
      toast.success('Memorandum Circular created successfully!');
      clearForm();
    } else {
      throw new Error(response.data.message || 'Failed to create record');
    }
    
  } catch (error: any) {
    console.error('Error creating MC:', error);
    
    if (error.response?.data?.errors) {
      // Handle validation errors from server
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if ((errors as any)[key] !== undefined) {
          (errors as any)[key] = validationErrors[key][0];
        }
      });
      toast.error('Please check the form for errors');
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to create record';
      toast.error(errorMessage);
    }
  } finally {
    isLoading.value = false;
  }
};

// Reset form
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
          <Label for="mc_number">
            M.C. Number <span class="text-red-500">*</span>
          </Label>
          <Input
            id="mc_number"
            v-model="formData.mc_number"
            placeholder="e.g., M.C. No. 01"
            :disabled="isLoading"
            :class="{ 'border-red-500': errors.mc_number }"
          />
          <p v-if="errors.mc_number" class="text-sm text-red-500 mt-1">{{ errors.mc_number }}</p>
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
          <p v-if="errors.date" class="text-sm text-red-500 mt-1">{{ errors.date }}</p>
        </div>

        <div>
          <Label for="reference">Reference (Series)</Label>
          <Input
            id="reference"
            v-model="formData.reference"
            placeholder="e.g., Series of 2026"
            :disabled="isLoading"
          />
        </div>
      </div>
      
      <div class="grid grid-cols-1 gap-4">
        <div>
            <Label for="url">Reference URL</Label>
            <Input
                id="url"
                v-model="formData.url"
                type="text"
                placeholder="Enter URL or Relative Path"
                :disabled="isLoading"
            />
        </div>
      </div>
        
      <div>
        <Label for="subject">Subject / Description</Label>
        <Textarea
          id="subject"
          v-model="formData.subject"
          placeholder="Enter the subject or topic of the circular"
          :disabled="isLoading"
          rows="4"
          :class="{ 'border-red-500': errors.subject }"
        />
        <p v-if="errors.subject" class="text-sm text-red-500 mt-1">{{ errors.subject }}</p>
      </div>
      
      <div class="space-y-2">
          <div class="flex items-start gap-4">
              <div class="flex items-center space-x-2 pt-2">
                  <Checkbox
                      id="pdf_availability"
                      v-model:checked="formData.pdf_availability"
                      :disabled="isLoading"
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
                      placeholder="Enter PDF path or URL (e.g., /uploads/pdfs/mc-01.pdf)"
                      :disabled="isLoading || !formData.pdf_availability"
                      class="w-full"
                  />
                  <p v-if="formData.pdf_availability" class="text-xs text-muted-foreground mt-1">
                      You can enter a relative path or full URL to the PDF file
                  </p>
              </div>
          </div>
      </div>
      
      <div class="flex gap-2 justify-between border-t pt-6 mt-6">
        <p class="text-sm text-muted-foreground">
            <span class="text-red-500">*</span> Required fields.
        </p>
        <div class="flex gap-2">
            <Button 
                type="submit" 
                :disabled="isLoading"
                class="gap-2 bg-[#121212] hover:bg-black text-white"
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