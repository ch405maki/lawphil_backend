<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { 
  Upload, 
  Loader2, 
  CheckCircle, 
  XCircle, 
  AlertCircle,
  FileSpreadsheet,
  FolderOpen,
  X,
  Download
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
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

// Refs for file input
const fileInput = ref<HTMLInputElement | null>(null);

// Format file size
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Trigger file input
const triggerFileInput = () => {
  if (!isUploading.value && fileInput.value) {
    fileInput.value.click();
  }
};

// Handle file selection
const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    file.value = target.files[0];
    uploadResult.value = null;
    console.log('File selected:', file.value.name);
  } else {
    file.value = null;
  }
};

// Handle drop
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

// Upload file
const uploadFile = async () => {
  if (!file.value) {
    alert('Please select a file first');
    return;
  }
  
  console.log('Starting upload for:', file.value.name);
  isUploading.value = true;
  uploadProgress.value = 0;
  uploadResult.value = null;
  
  const formData = new FormData();
  formData.append('file', file.value);
  
  try {
    const response = await axios.post('/api/v1/proclamations/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }
      }
    });
    
    console.log('Upload response:', response.data);
    
    if (response.data && response.data.success) {
      uploadResult.value = {
        success: true,
        message: response.data.message,
        imported: response.data.data?.imported || 0,
        total_rows: response.data.data?.total_rows || 0,
        failed_count: response.data.data?.failed_count || 0,
        errors: response.data.data?.errors || []
      };
      
      // Emit success event
      emit('import-success', response.data.data);
      
      // Clear file input
      if (fileInput.value) {
        fileInput.value.value = '';
      }
      file.value = null;
      
    } else {
      throw new Error(response.data?.message || 'Unexpected response format');
    }
    
  } catch (error: any) {
    console.error('Upload failed:', error);
    
    let errorMessage = 'Upload failed';
    let detailedErrors: string[] = [];
    
    if (error.response) {
      if (error.response.data?.message) {
        errorMessage = error.response.data.message;
      }
      
      if (error.response.data?.errors) {
        detailedErrors = Array.isArray(error.response.data.errors) 
          ? error.response.data.errors 
          : [error.response.data.errors];
      } else if (error.response.data?.data?.errors) {
        detailedErrors = error.response.data.data.errors;
      }
    } else if (error.request) {
      errorMessage = 'No response from server. Please check your connection.';
      detailedErrors = ['Network error: Unable to reach the server'];
    } else {
      errorMessage = error.message;
      detailedErrors = [error.message];
    }
    
    uploadResult.value = {
      success: false,
      message: errorMessage,
      errors: detailedErrors
    };
    
    // Emit error event
    emit('import-error', { message: errorMessage, errors: detailedErrors });
    
  } finally {
    isUploading.value = false;
    uploadProgress.value = 0;
  }
};

// Clear results and show upload area again
const clearResults = () => {
  uploadResult.value = null;
  file.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Close dialog and reset
const closeDialog = () => {
  open.value = false;
  setTimeout(() => {
    clearResults();
  }, 300);
};
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button :variant="triggerVariant || 'default'" class="gap-2">
        <component :is="triggerIcon || Upload" class="h-4 w-4" />
        {{ triggerText || 'Import Excel' }}
      </Button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Import Proclamation from Excel</DialogTitle>
        <DialogDescription>
          Upload an Excel file to import proclamation records.
        </DialogDescription>
      </DialogHeader>
      
      <div class="space-y-6 py-4">
        <!-- Conditional Content: Show Upload Area or Results -->
        <div v-if="!uploadResult">
          <!-- File Upload Area with Dropzone -->
          <div class="space-y-4">
            <div class="grid w-full items-center gap-1.5">
              <Label>Upload Excel File</Label>
              
              <!-- Custom File Upload Dropzone -->
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
                  id="file-upload"
                  type="file" 
                  accept=".xlsx,.xls,.csv"
                  @change="onFileChange"
                  :disabled="isUploading"
                  class="hidden"
                />
                
                <div class="flex flex-col items-center justify-center text-center space-y-3">
                  <!-- Icon with animation -->
                  <div 
                    class="p-2 rounded-lg bg-primary/10 transition-all duration-300"
                    :class="isDragOver ? 'bg-primary/20 scale-110' : ''"
                  >
                    <FileSpreadsheet 
                      :class="isDragOver ? 'animate-bounce' : ''"
                    />
                  </div>
                  
                  <!-- Text content -->
                  <div class="space-y-1">
                    <p class="text-sm font-medium">
                      {{ file ? file.name : 'Drag and drop your Excel file here' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                      or click to browse
                    </p>
                    <p v-if="file" class="text-xs text-primary font-medium mt-2">
                      Selected: {{ file.name }} ({{ formatFileSize(file.size) }})
                    </p>
                    <p class="text-xs text-muted-foreground mt-2">
                      Supports .xlsx, .xls, .csv • Max file size: 10MB
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Upload Button and Clear -->
            <div class="flex justify-end gap-2">
              <Button 
                @click="uploadFile" 
                :disabled="!file || isUploading"
                class="gap-2"
              >
                <Loader2 v-if="isUploading" class="h-4 w-4 animate-spin" />
                <Upload v-else class="h-4 w-4" />
                {{ isUploading ? `Uploading... ${uploadProgress}%` : 'Upload File' }}
              </Button>
              
              <Button 
                v-if="file"
                variant="outline"
                @click="clearResults"
                :disabled="isUploading"
                class="gap-2"
              >
                <X class="h-4 w-4" />
                Clear
              </Button>
            </div>
            
            <!-- Progress Bar -->
            <div v-if="isUploading && uploadProgress > 0" class="w-full">
              <div class="bg-secondary h-2 rounded-full overflow-hidden">
                <div 
                  class="bg-primary h-full transition-all duration-300"
                  :style="{ width: `${uploadProgress}%` }"
                ></div>
              </div>
              <p class="text-sm text-muted-foreground mt-1">{{ uploadProgress }}% uploaded</p>
            </div>
          </div>
        </div>
        
        <!-- Results Display (Replaces Upload Area) -->
        <div v-else>
          <!-- Success Alert -->
          <div v-if="uploadResult.success">
            <Alert variant="default" class="border-green-500 bg-green-50">
              <AlertTitle class="text-green-800 font-semibold">Import Successful!</AlertTitle>
              <AlertDescription class="text-green-700">
                <p class="mb-2">{{ uploadResult.message }}</p>
                
                <div class="mt-3 space-y-2">
                  <div class="flex items-center gap-3 text-sm">
                    <div class="flex items-center gap-2">
                      <span class="font-medium">Total rows:</span>
                      <span>{{ uploadResult.total_rows || 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="font-medium">Imported:</span>
                      <span class="text-green-600 font-semibold">{{ uploadResult.imported || 0 }}</span>
                    </div>
                    <div v-if="uploadResult.failed_count && uploadResult.failed_count > 0" class="flex items-center gap-2">
                      <span class="font-medium">Failed:</span>
                      <span class="text-red-600 font-semibold">{{ uploadResult.failed_count }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Show errors if any -->
                <div v-if="uploadResult.errors && uploadResult.errors.length > 0" class="mt-4">
                  <details class="cursor-pointer">
                    <summary class="text-sm font-medium text-green-800 hover:text-green-900">
                      View {{ uploadResult.errors.length }} error(s) (click to expand)
                    </summary>
                    <div class="mt-2 max-h-40 overflow-y-auto bg-green-100 p-3 rounded">
                      <ul class="list-disc list-inside space-y-1 text-sm">
                        <li v-for="(error, index) in uploadResult.errors" :key="index" class="text-red-700">
                          {{ error }}
                        </li>
                      </ul>
                    </div>
                  </details>
                </div>
              </AlertDescription>
            </Alert>
          </div>
          
          <!-- Error Alert -->
          <div v-else>
            <Alert variant="destructive">
              <AlertTitle>Import Failed</AlertTitle>
              <AlertDescription>
                <p class="mb-2 font-semibold">{{ uploadResult.message }}</p>
                
                <div v-if="uploadResult.errors && uploadResult.errors.length > 0" class="mt-3">
                  <p class="text-sm font-medium mb-2">Error details:</p>
                  <div class="max-h-40 overflow-y-auto">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                      <li v-for="(error, index) in uploadResult.errors" :key="index">
                        {{ error }}
                      </li>
                    </ul>
                  </div>
                </div>
                
                <div class="mt-4 p-3 bg-red-100 rounded-md">
                  <p class="text-sm font-medium">Troubleshooting tips:</p>
                  <ul class="list-disc list-inside text-sm mt-1 space-y-1">
                    <li>Make sure your Excel file follows the template format</li>
                    <li>Check that Proclamation Number and Date are filled</li>
                    <li>Verify date format is YYYY-MM-DD</li>
                    <li>Ensure file size is less than 10MB</li>
                    <li>Download the template again if needed</li>
                  </ul>
                </div>
              </AlertDescription>
            </Alert>
          </div>
        </div>
        
        <!-- Action Buttons for Results (Always at bottom of results) -->
        <div v-if="uploadResult" class="flex gap-2 justify-end">
          <Button @click="clearResults" variant="outline" class="gap-2">
            <Upload class="h-4 w-4" />
            {{ uploadResult.success ? 'Import Another File' : 'Try Again' }}
          </Button>
          <Button @click="closeDialog" variant="default">
            Close
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>