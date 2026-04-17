<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Save, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

const toast = useToast();

const formData = reactive({
  mo_number: '',
  date: '',
  citation: '',
  signatory: '',
  reference: '',
  url: '',
  pdf_availability: false,
  subject: '',
  pdf_path: ''
});

const errors = reactive({
  mo_number: '',
  date: '',
  citation: '',
  signatory: '',
  reference: '',
  url: '',
  subject: '',
  pdf_path: ''
});

const isLoading = ref(false);

const validateForm = () => {
  let isValid = true;
  Object.keys(errors).forEach(key => { errors[key as keyof typeof errors] = ''; });
  
  if (!formData.mo_number.trim()) {
    errors.mo_number = 'M.O. Number is required';
    isValid = false;
  }
  if (!formData.date) {
    errors.date = 'Date is required';
    isValid = false;
  }
  
  return isValid;
};

const clearForm = () => {
  formData.mo_number = '';
  formData.date = '';
  formData.citation = '';
  formData.signatory = '';
  formData.reference = '';
  formData.url = '';
  formData.pdf_availability = false;
  formData.subject = '';
  formData.pdf_path = '';
  
  Object.keys(errors).forEach(key => { errors[key as keyof typeof errors] = ''; });
};

const submitForm = async () => {
  if (!validateForm()) {
    toast.error('Please fix the validation errors');
    return;
  }
  
  isLoading.value = true;
  
  try {
    const response = await axios.post('/api/memorandum-orders', formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    if (response.data.success) {
      toast.success('Memorandum Order created successfully!');
      clearForm();
    } else {
      throw new Error(response.data.message || 'Failed to create record');
    }
    
  } catch (error: any) {
    console.error('Error creating record:', error);
    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if (errors[key as keyof typeof errors] !== undefined) {
          errors[key as keyof typeof errors] = validationErrors[key][0];
        }
      });
      toast.error('Please check the form for errors');
    } else {
      toast.error(error.response?.data?.message || error.message || 'Failed to create record');
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
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <Label for="mo_number">M.O. Number <span class="text-red-500">*</span></Label>
          <Input id="mo_number" v-model="formData.mo_number" placeholder="e.g., M.O. No. 123" :disabled="isLoading" :class="{ 'border-red-500': errors.mo_number }" />
          <p v-if="errors.mo_number" class="text-sm text-red-500">{{ errors.mo_number }}</p>
        </div>
        
        <div>
          <Label for="date">Date <span class="text-red-500">*</span></Label>
          <Input id="date" v-model="formData.date" type="date" :disabled="isLoading" :class="{ 'border-red-500': errors.date }" />
          <p v-if="errors.date" class="text-sm text-red-500">{{ errors.date }}</p>
        </div>

        <div>
          <Label for="reference">Reference</Label>
          <Input id="reference" v-model="formData.reference" placeholder="e.g., Volume 123" :disabled="isLoading" :class="{ 'border-red-500': errors.reference }" />
          <p v-if="errors.reference" class="text-sm text-red-500">{{ errors.reference }}</p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <Label for="url">URL</Label>
            <Input id="url" v-model="formData.url" type="text" placeholder="Enter URL or Rel. Path" :disabled="isLoading" :class="{ 'border-red-500': errors.url }" />
            <p v-if="errors.url" class="text-sm text-red-500">{{ errors.url }}</p>
        </div>
        <div>
          <Label for="signatory">Signatory</Label>
          <Input id="signatory" v-model="formData.signatory" placeholder="e.g., Pres. Ferdinand Marcos Jr." :disabled="isLoading" :class="{ 'border-red-500': errors.signatory }" />
          <p v-if="errors.signatory" class="text-sm text-red-500">{{ errors.signatory }}</p>
        </div>
      </div>
        
      <div class="grid grid-cols-1 gap-4">
        <div>
          <Label for="citation">Citation / Title</Label>
          <Textarea id="citation" v-model="formData.citation" placeholder="Enter the order title or citation" :disabled="isLoading" :class="{ 'border-red-500': errors.citation }" />
          <p v-if="errors.citation" class="text-sm text-red-500">{{ errors.citation }}</p>
        </div>
      </div>
      
      <div>
        <Label for="subject">Subject</Label>
        <Textarea id="subject" v-model="formData.subject" placeholder="Enter the subject or topic" :disabled="isLoading" rows="3" :class="{ 'border-red-500': errors.subject }" />
        <p v-if="errors.subject" class="text-sm text-red-500">{{ errors.subject }}</p>
      </div>
      
      <div class="space-y-2">
          <div class="flex items-start gap-4">
              <div class="flex items-center space-x-2 pt-2">
                  <Checkbox id="pdf_availability" v-model:checked="formData.pdf_availability" :disabled="isLoading" />
                  <Label for="pdf_availability" class="cursor-pointer">PDF Available</Label>
              </div>
              
              <div class="flex-1">
                  <Input id="pdf_path" v-model="formData.pdf_path" type="text" placeholder="Enter PDF path or URL (e.g., /uploads/pdfs/order.pdf)" :disabled="isLoading || !formData.pdf_availability" :class="{ 'border-red-500': errors.pdf_path }" class="w-full" />
                  <p v-if="errors.pdf_path" class="text-sm text-red-500 mt-1">{{ errors.pdf_path }}</p>
                  <p v-if="formData.pdf_availability" class="text-xs text-muted-foreground mt-1">
                      You can enter a relative path or full URL to the PDF file
                  </p>
              </div>
          </div>
      </div>
      
      <div class="flex flex-col md:flex-row gap-4 justify-between border-t pt-6 mt-6">
        <p class="text-sm text-muted-foreground order-2 md:order-1">
            <span class="text-red-500">*</span> Required fields. All other fields are optional.
        </p>
        <div class="flex gap-2 order-1 md:order-2 justify-end">
            <Button type="button" variant="outline" @click="resetForm" :disabled="isLoading" class="gap-2">
                <X class="h-4 w-4" /> Reset
            </Button>
            <Button type="submit" :disabled="isLoading" class="gap-2">
                <Save class="h-4 w-4" /> {{ isLoading ? 'Creating...' : 'Create Record' }}
            </Button>
        </div>
      </div>
    </form>
  </div>
</template>