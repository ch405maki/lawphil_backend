<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { 
  Upload, 
  Loader2, 
  FileSpreadsheet,
  X
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

// Props
const props = defineProps<{
  triggerText?: string;
  triggerVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
  triggerIcon?: any;
}>();

// Emits
const emit = defineEmits<{
  (e: 'import-success', data: any): void;
  (e: 'import-error', error: any): void;
}>();

// Administrative Order Configuration
const config = {
  title: 'Administrative Orders',
  endpoint: '/api/v1/administrative/import',
  requiredHeaders: [
    'ao_number', 
    'date', 
    'description', 
    'subject', 
    'url', 
    'pdf_availability', 
    'pdf_path'
  ],
};

// Component state
const open = ref(false);
const file = ref<File | null>(null);
const isUploading = ref(false);
const isDragOver = ref(false);
const uploadProgress = ref(0);
const uploadResult = ref<{
  success: boolean;
  message: string;
  imported?: number;
  total_rows?: number;
  failed_count?: number;
  errors?: any[];
} | null>(null);

const fileInput = ref<HTMLInputElement | null>(null);

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const triggerFileInput = () => {
  if (!isUploading.value && fileInput.value) {
    fileInput.value.click();
  }
};

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    file.value = target.files[0];
    uploadResult.value = null;
  }
};

const handleDrop = (e: DragEvent) => {
  isDragOver.value = false;
  const files = e.dataTransfer?.files;
  if (files && files.length > 0) {
    const droppedFile = files[0];
    const validExtensions = ['.xlsx', '.xls', '.csv'];
    const fileExt = '.' + droppedFile.name.split('.').pop()?.toLowerCase();
    
    if (validExtensions.includes(fileExt)) {
      file.value = droppedFile;
      uploadResult.value = null;
    }
  }
};

const uploadFile = async () => {
  if (!file.value) return;
  
  isUploading.value = true;
  uploadProgress.value = 0;
  uploadResult.value = null;
  
  const formData = new FormData();
  formData.append('file', file.value);
  
  try {
    const response = await axios.post(config.endpoint, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json',
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }
      }
    });
    
    if (response.data?.success) {
      uploadResult.value = {
        success: true,
        message: response.data.message,
        imported: response.data.data?.imported || 0,
        total_rows: response.data.data?.total_rows || 0,
        failed_count: response.data.data?.failed_count || 0,
        errors: response.data.data?.errors || []
      };
      emit('import-success', response.data.data);
      file.value = null;
    }
  } catch (error: any) {
    const errorMessage = error.response?.data?.message || error.message || 'Upload failed';
    const detailedErrors = error.response?.data?.errors || [errorMessage];
    
    uploadResult.value = {
      success: false,
      message: errorMessage,
      errors: Array.isArray(detailedErrors) ? detailedErrors : [detailedErrors]
    };
    emit('import-error', { message: errorMessage, errors: detailedErrors });
  } finally {
    isUploading.value = false;
  }
};

const clearResults = () => {
  uploadResult.value = null;
  file.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const closeDialog = () => {
  open.value = false;
  setTimeout(() => clearResults(), 300);
};
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button :variant="triggerVariant || 'default'" class="gap-2">
        <component :is="triggerIcon || Upload" class="h-4 w-4" />
        {{ triggerText || `Import Administrative Orders` }}
      </Button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Import Administrative Orders</DialogTitle>
        <DialogDescription>
          Upload an Excel or CSV file to import administrative order records.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-6 py-4">
        <div v-if="!uploadResult">
          <div class="space-y-4">
            <div class="grid w-full items-center gap-1.5">
              <Label>Excel/CSV File</Label>
              
              <div 
                class="relative border-2 border-dashed rounded-lg p-8 transition-all duration-200"
                :class="[
                  isDragOver ? 'border-primary bg-primary/5 scale-[1.02]' : 'border-muted-foreground/25 hover:border-primary/50 hover:bg-muted/30',
                  isUploading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                ]"
                @dragover.prevent="isDragOver = true"
                @dragleave.prevent="isDragOver = false"
                @drop.prevent="handleDrop"
                @click="triggerFileInput"
              >
                <input 
                  ref="fileInput"
                  type="file" 
                  accept=".xlsx,.xls,.csv"
                  @change="onFileChange"
                  :disabled="isUploading"
                  class="hidden"
                />
                
                <div class="flex flex-col items-center justify-center text-center space-y-3">
                  <div class="p-2 rounded-lg bg-primary/10 text-primary">
                    <FileSpreadsheet :class="isDragOver ? 'animate-bounce' : ''" />
                  </div>
                  
                  <div class="space-y-1">
                    <p class="text-sm font-medium">
                      {{ file ? file.name : 'Drag and drop your Excel file here' }}
                    </p>
                    <p v-if="file" class="text-xs text-primary font-medium mt-2">
                      Selected: {{ file.name }} ({{ formatFileSize(file.size) }})
                    </p>
                    <p v-else class="text-xs text-muted-foreground">or click to browse</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end gap-2">
              <Button @click="uploadFile" :disabled="!file || isUploading" class="gap-2">
                <Loader2 v-if="isUploading" class="h-4 w-4 animate-spin" />
                <Upload v-else class="h-4 w-4" />
                {{ isUploading ? `Uploading... ${uploadProgress}%` : 'Upload File' }}
              </Button>
              <Button v-if="file" variant="outline" @click="clearResults" :disabled="isUploading">
                Clear
              </Button>
            </div>
          </div>
        </div>
        
        <div v-else>
          <div v-if="uploadResult.success">
            <Alert class="border-green-500 bg-green-50">
              <AlertTitle class="text-green-800 font-semibold">Import Successful!</AlertTitle>
              <AlertDescription class="text-green-700">
                <p>{{ uploadResult.message }}</p>
                <div class="mt-3 flex gap-4 text-sm font-medium">
                   <span>Total Rows: {{ uploadResult.total_rows }}</span>
                   <span class="text-green-600">Successfully Imported: {{ uploadResult.imported }}</span>
                   <span v-if="uploadResult.failed_count" class="text-red-600">Failed: {{ uploadResult.failed_count }}</span>
                </div>
              </AlertDescription>
            </Alert>
          </div>
          
          <div v-else>
            <Alert variant="destructive">
              <AlertTitle>Import Failed</AlertTitle>
              <AlertDescription>
                <p class="font-semibold">{{ uploadResult.message }}</p>
                <div class="mt-4 p-3 bg-red-100 rounded-md text-red-900">
                  <p class="text-xs font-bold uppercase mb-2">Required Column Headers (Exact Order):</p>
                  <div class="flex flex-wrap gap-1">
                    <span v-for="head in config.requiredHeaders" :key="head" class="bg-white px-2 py-0.5 rounded border border-red-200 text-[10px] font-mono">
                      {{ head }}
                    </span>
                  </div>
                  <ul class="list-disc list-inside text-xs mt-3 space-y-1">
                    <li>Required fields: <strong>ao_number</strong> and <strong>date</strong></li>
                    <li>Date format: <strong>YYYY-MM-DD</strong> (e.g. 2026-04-07)</li>
                    <li>PDF Availability: <strong>Yes</strong> or <strong>No</strong></li>
                  </ul>
                </div>
              </AlertDescription>
            </Alert>
          </div>

          <div class="flex gap-2 justify-end mt-6">
            <Button @click="clearResults" variant="outline">{{ uploadResult.success ? 'Import Another' : 'Try Again' }}</Button>
            <Button @click="closeDialog">Close</Button>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>