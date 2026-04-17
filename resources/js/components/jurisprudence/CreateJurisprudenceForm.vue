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

const validateForm = () => {
  let isValid = true;
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  
  if (!formData.gr_number.trim()) {
    errors.gr_number = 'GR Number is required';
    isValid = false;
  }
  
  if (!formData.date) {
    errors.date = 'Date is required';
    isValid = false;
  }
  
  return isValid;
};

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
    if (error.response?.data?.errors) {
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

const resetForm = () => {
  clearForm();
  toast.info('Form has been reset');
};
</script>

<template>
  <div>
    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="space-y-2">
        <Label for="citation">Citation / Case Title</Label>
        <Textarea
          id="citation"
          v-model="formData.citation"
          placeholder="e.g., People of the Philippines vs. Juan Dela Cruz"
          :disabled="isLoading"
          :class="{ 'border-red-500': errors.citation }"
          rows="2"
        />
        <p v-if="errors.citation" class="text-sm text-red-500">{{ errors.citation }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        <div>
          <Label for="subject">Subject Area</Label>
          <select
            id="subject"
            v-model="formData.subject"
            :disabled="isLoading"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            :class="{ 'border-red-500': errors.subject }"
          >
            <option value="" disabled>Select the law subject or category</option>
            <option v-for="option in subjectOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
          <p v-if="errors.subject" class="text-sm text-red-500">{{ errors.subject }}</p>
        </div>
      </div>

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
          <Label for="reference">Reference / Volume</Label>
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
      
      <div class="space-y-2">
          <Label for="url">Source URL</Label>
          <Input
              id="url"
              v-model="formData.url"
              type="text"
              placeholder="e.g., https://lawphil.net/judjuris/..."
              :disabled="isLoading"
              :class="{ 'border-red-500': errors.url }"
          />
          <p v-if="errors.url" class="text-sm text-red-500">{{ errors.url }}</p>
      </div>
        
      <div class="p-4 border rounded-lg bg-slate-50/50">
          <div class="flex items-start gap-4">
              <div class="flex items-center space-x-2 pt-2">
                  <Checkbox
                      id="pdf_availability"
                      v-model:checked="formData.pdf_availability"
                      :disabled="isLoading"
                  />
                  <Label for="pdf_availability" class="cursor-pointer font-medium">
                      PDF Available
                  </Label>
              </div>
              
              <div class="flex-1 space-y-2">
                  <Input
                      id="pdf_path"
                      v-model="formData.pdf_path"
                      type="text"
                      placeholder="Enter path (e.g., /storage/juris/case_123.pdf)"
                      :disabled="isLoading || !formData.pdf_availability"
                      :class="{ 'border-red-500': errors.pdf_path }"
                      class="w-full bg-white"
                  />
                  <p v-if="errors.pdf_path" class="text-sm text-red-500 mt-1">{{ errors.pdf_path }}</p>
                  <p v-if="formData.pdf_availability" class="text-xs text-muted-foreground mt-1">
                      Provide the relative path or direct link to the document.
                  </p>
              </div>
          </div>
      </div>
      
      <div class="flex flex-col md:flex-row gap-4 justify-between border-t pt-6">
        <p class="text-xs text-muted-foreground italic self-center">
            <span class="text-red-500">*</span> Required fields: GR Number and Date.
        </p>
        <div class="flex gap-2">
            <Button 
                type="submit" 
                :disabled="isLoading"
                class="gap-2 px-6"
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