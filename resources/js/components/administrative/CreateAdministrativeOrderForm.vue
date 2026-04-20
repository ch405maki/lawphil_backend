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
import { Checkbox } from '@/components/ui/checkbox';

const toast = useToast();

const subjectOptions = [
  'General Administration and Personnel',
  'Fiscal and Budgetary Matters',
  'Public Procurement and Asset Management',
  'Regulatory Policy and Licensing',
  'Institutional Organization and Mandates',
  'Public Health and Safety',
  'Environmental and Natural Resources',
  'Information and Communications Technology',
  'Emergency and Contingency Directives',
  'Inter-Agency Coordination'
];

const formData = reactive({
  ao_number: '', 
  date: '',
  description: '',
  subject: '',
  url: '',
  pdf_availability: false,
  pdf_path: ''
});

const errors = reactive({
  ao_number: '',
  date: '',
  description: '',
  subject: '',
  url: '',
  pdf_path: ''
});

const isLoading = ref(false);

const validateForm = () => {
  let isValid = true;
  
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  
  if (!formData.ao_number.trim()) {
    errors.ao_number = 'AO Number is required';
    isValid = false;
  }
  
  if (!formData.date) {
    errors.date = 'Date is required';
    isValid = false;
  }

  if (!formData.description.trim()) {
    errors.description = 'Description is required';
    isValid = false;
  }
  
  return isValid;
};

const clearForm = () => {
  formData.ao_number = '';
  formData.date = '';
  formData.description = '';
  formData.subject = '';
  formData.url = '';
  formData.pdf_availability = false;
  formData.pdf_path = '';
  
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
};

const submitForm = async () => {
  if (!validateForm()) {
    toast.error('Please fix the validation errors');
    return;
  }
  
  isLoading.value = true;
  
  try {
    const response = await axios.post('/api/v1/administrative', formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.data.success) {
      toast.success('Administrative Order created successfully!');
      clearForm();
    } else {
      throw new Error(response.data.message || 'Failed to create record');
    }
    
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if (errors[key] !== undefined) {
          errors[key] = validationErrors[key][0];
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

const resetForm = () => {
  clearForm();
  toast.info('Form has been reset');
};
</script>

<template>
  <div>
    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-1 w-full">
        <div>
          <Label for="description">
            Description / Title <span class="text-red-500">*</span>
          </Label>
          <Input
            id="description"
            v-model="formData.description"
            placeholder="e.g., AUTHORIZING THE GRANT OF GRATUITY PAY..."
            :disabled="isLoading"
            :class="{ 'border-red-500': errors.description }"
            class="w-full"
          />
          <p v-if="errors.description" class="text-sm text-red-500 mt-1">{{ errors.description }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label for="ao_number">
            Administrative Order No. <span class="text-red-500">*</span>
          </Label>
          <Input
            id="ao_number"
            v-model="formData.ao_number"
            placeholder="e.g., A.O. No. 01"
            :disabled="isLoading"
            :class="{ 'border-red-500': errors.ao_number }"
          />
          <p v-if="errors.ao_number" class="text-sm text-red-500 mt-1">{{ errors.ao_number }}</p>
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
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <Label for="url">Source URL</Label>
            <Input
                id="url"
                v-model="formData.url"
                type="text"
                placeholder="Official Gazette Link"
                :disabled="isLoading"
                :class="{ 'border-red-500': errors.url }"
            />
            <p v-if="errors.url" class="text-sm text-red-500 mt-1">{{ errors.url }}</p>
        </div>
        <div>
          <Label for="subject">Subject Area</Label>
          <select
            id="subject"
            v-model="formData.subject"
            :disabled="isLoading"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            :class="{ 'border-red-500': errors.subject }"
          >
            <option value="" disabled>Select category</option>
            <option v-for="option in subjectOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
          <p v-if="errors.subject" class="text-sm text-red-500 mt-1">{{ errors.subject }}</p>
        </div>
      </div>
      
      <div class="space-y-2 pt-2">
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
                      placeholder="e.g., /storage/ao/41.pdf"
                      :disabled="isLoading || !formData.pdf_availability"
                      :class="{ 'border-red-500': errors.pdf_path }"
                      class="w-full"
                  />
                  <p v-if="errors.pdf_path" class="text-sm text-red-500 mt-1">{{ errors.pdf_path }}</p>
              </div>
          </div>
      </div>
      
      <div class="flex gap-2 justify-between border-t pt-6 mt-6">
        <p class="text-xs text-muted-foreground italic">
            <span class="text-red-500">*</span> Required fields.
        </p>
        <div class="flex gap-2">
            <Button 
                type="submit" 
                :disabled="isLoading"
                class="gap-2"
                >
                <Save class="h-4 w-4" />
                {{ isLoading ? 'Saving...' : 'Save Order' }}
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