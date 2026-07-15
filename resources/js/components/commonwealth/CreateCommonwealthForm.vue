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

const formData = reactive({
  ca_number: '',
  date: '',
  citation: '',
  tenure: '',
  url: '',
  pdf_availability: false,
  description: '',
  pdf_path: ''
});

const errors = reactive({
  ca_number: '',
  date: '',
  citation: '',
  tenure: '',
  url: '',
  description: '',
  pdf_path: ''
});

const isLoading = ref(false);

const validateForm = () => {
  let isValid = true;

  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = '';
  });

  if (!formData.ca_number.trim()) {
    errors.ca_number = 'C.A. Number is required';
    isValid = false;
  }

  if (!formData.date) {
    errors.date = 'Date is required';
    isValid = false;
  }

  return isValid;
};

const clearForm = () => {
  formData.ca_number = '';
  formData.date = '';
  formData.citation = '';
  formData.tenure = '';
  formData.url = '';
  formData.pdf_availability = false;
  formData.description = '';
  formData.pdf_path = '';

  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = '';
  });
};

const submitForm = async () => {
  if (!validateForm()) {
    toast.error('Please fix the validation errors');
    return;
  }

  isLoading.value = true;

  try {
    const response = await axios.post('/api/v1/commonwealth', formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (response.data.success) {
      toast.success('Commonwealth record created successfully!');
      clearForm();
    } else {
      throw new Error(response.data.message || 'Failed to create record');
    }

  } catch (error: any) {
    console.error('Error creating commonwealth:', error);

    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      Object.keys(validationErrors).forEach(key => {
        if (key in errors) {
          errors[key as keyof typeof errors] = validationErrors[key][0];
        }
      });
      toast.error('Please check the form for errors');
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to create commonwealth record';
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
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label for="ca_number">
            C.A. Number <span class="text-red-500">*</span>
          </Label>
          <Input
            id="ca_number"
            v-model="formData.ca_number"
            placeholder="e.g., C.A. No. 123"
            :disabled="isLoading"
            :class="{ 'border-red-500': errors.ca_number }"
          />
          <p v-if="errors.ca_number" class="text-sm text-red-500">{{ errors.ca_number }}</p>
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
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="formData.description"
            placeholder="Enter description here..."
            :disabled="isLoading"
            rows="3"
            :class="{ 'border-red-500': errors.description }"
          />
          <p v-if="errors.description" class="text-sm text-red-500">{{ errors.description }}</p>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label for="citation">Citation</Label>
          <Input
            id="citation"
            v-model="formData.citation"
            type="text"
            placeholder="e.g., 123 SCRA 456"
            :disabled="isLoading"
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
