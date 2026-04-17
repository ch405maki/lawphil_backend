<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { 
  Upload, 
  Loader2, 
  FileSpreadsheet,
  X,
  AlertCircle
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
  } else {
    file.value = null;
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
    } else {
      alert('Please upload a valid Excel file (.xlsx, .xls, .csv)');
    }
  }
};

// Main Upload Logic for Executive Orders
const uploadFile = async () => {
  if (!file.value) {
    alert('Please select a file first');
    return;
  }
  
  isUploading.value = true;
  uploadProgress.value = 0;
  uploadResult.value = null;
  
  const formData = new FormData();
  formData.append('file', file.value);
  
  try {
    // PINALITAN ANG ENDPOINT: /api/executive-orders/import
    const response = await axios.post('/api/executive-orders/import', formData, {
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
    
    if (response.data && response.data.success) {
      uploadResult.value = {
        success: true,
        message: response.data.message,
        imported: response.data.data?.imported || 0,
        total_rows: response.data.data?.total_rows || 0,
        failed_count: response.data.data?.failed_count || 0,
        errors: response.data.data?.errors || []
      };
      
      emit('import-success', response.data.data);
      if (fileInput.value) fileInput.value.value = '';
      file.value = null;
    } else {
      throw new Error(response.data?.message || 'Unexpected response format');
    }
    
  } catch (error: any) {
    console.error('EO Upload failed:', error);
    let errorMessage = error.response?.data?.message || 'Upload failed';
    let detailedErrors = error.response?.data?.errors || [error.message];
    
    uploadResult.value = {
      success: false,
      message: errorMessage,
      errors: Array.isArray(detailedErrors) ? detailedErrors : [detailedErrors]
    };
    emit('import-error', { message: errorMessage, errors: detailedErrors });
  } finally {
    isUploading.value = false;
    uploadProgress.value = 0;
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
        <component :is="triggerIcon || FileSpreadsheet" class="h-4 w-4" />
        {{ triggerText || 'Import Excel' }}
      </Button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Import Executive Orders from Excel</DialogTitle>
        <DialogDescription>
          Upload an Excel file to import executive order records to the bank.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-6 py-4">
        <div v-if="!uploadResult">
          <div class="space-y-4">
            <div class="grid w-full items-center gap-1.5">
              <Label>Upload Excel File</Label>
              <div 
                class="relative border-2 border-dashed rounded-lg p-8 transition-all duration-200 cursor-pointer"
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
                  id="eo-file-upload"
                  type="file" 
                  accept=".xlsx,.xls,.csv"
                  @change="onFileChange"
                  :disabled="isUploading"
                  class="hidden"
                />
                
                <div class="flex flex-col items-center justify-center text-center space-y-3">
                  <div class="p-2 rounded-lg bg-primary/10 transition-all duration-300" :class="isDragOver ? 'bg-primary/20 scale-110' : ''">
                    <FileSpreadsheet :class="isDragOver ? 'animate-bounce' : ''" />
                  </div>
                  
                  <div class="space-y-1">
                    <p class="text-sm font-medium">
                      {{ file ? file.name : 'Drag and drop your EO Excel file here' }}
                    </p>
                    <p class="text-xs text-muted-foreground">or click to browse</p>
                    <p v-if="file" class="text-xs text-primary font-medium mt-2">
                      Selected: {{ file.name }} ({{ formatFileSize(file.size) }})
                    </p>
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
              <Button v-if="file" variant="outline" @click="clearResults" :disabled="isUploading" class="gap-2">
                <X class="h-4 w-4" /> Clear
              </Button>
            </div>
            
            <div v-if="isUploading && uploadProgress > 0" class="w-full">
              <div class="bg-secondary h-2 rounded-full overflow-hidden">
                <div class="bg-primary h-full transition-all duration-300" :style="{ width: `${uploadProgress}%` }"></div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else>
          <div v-if="uploadResult.success">
            <Alert variant="default" class="border-green-500 bg-green-50">
              <AlertTitle class="text-green-800 font-semibold">Import Successful!</AlertTitle>
              <AlertDescription class="text-green-700">
                <p class="mb-2">{{ uploadResult.message }}</p>
                <div class="mt-3 flex items-center gap-4 text-sm font-medium">
                  <span>Imported: <span class="text-green-600">{{ uploadResult.imported }}</span></span>
                  <span>Total: {{ uploadResult.total_rows }}</span>
                  <span v-if="uploadResult.failed_count">Failed: <span class="text-red-600">{{ uploadResult.failed_count }}</span></span>
                </div>

                <div v-if="uploadResult.errors?.length" class="mt-4">
                  <details class="cursor-pointer">
                    <summary class="text-xs font-bold text-green-800 uppercase">View Row Errors</summary>
                    <div class="mt-2 max-h-40 overflow-y-auto bg-green-100 p-2 rounded text-xs">
                      <ul class="list-disc list-inside space-y-1">
                        <li v-for="(error, index) in uploadResult.errors" :key="index" class="text-red-700">{{ error }}</li>
                      </ul>
                    </div>
                  </details>
                </div>
              </AlertDescription>
            </Alert>
          </div>
          
          <div v-else>
            <Alert variant="destructive">
              <AlertTitle>Import Failed</AlertTitle>
              <AlertDescription>
                <p class="mb-2 font-semibold">{{ uploadResult.message }}</p>
                <div class="mt-4 p-3 bg-red-100 rounded-md">
                  <p class="text-sm font-medium">EO Troubleshooting:</p>
                  <ul class="list-disc list-inside text-xs mt-1 space-y-1">
                    <li>Check if columns 'eo_number' and 'date' are present</li>
                    <li>Date must be in YYYY-MM-DD format</li>
                    <li>Ensure 'eo_number' is not empty</li>
                  </ul>
                </div>
              </AlertDescription>
            </Alert>
          </div>

          <div class="flex gap-2 justify-end mt-6">
            <Button @click="clearResults" variant="outline" class="gap-2">
              <Upload class="h-4 w-4" /> {{ uploadResult.success ? 'Import Another' : 'Try Again' }}
            </Button>
            <Button @click="closeDialog">Close</Button>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>