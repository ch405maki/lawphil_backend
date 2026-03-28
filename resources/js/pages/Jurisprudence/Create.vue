<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowUpRightIcon, TriangleAlert, Upload, Download, Loader2, CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ref } from 'vue';
import axios from 'axios';
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Jurisprudence',
        href: '#',
    },
    {
        title: 'Create',
        href: '#',
    },
];

// Component state
const file = ref<File | null>(null);
const isUploading = ref(false);
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

// Handle file selection
const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        file.value = target.files[0];
        uploadResult.value = null; // Clear previous results
        console.log('File selected:', file.value.name);
    } else {
        file.value = null;
    }
};

// Download template
const downloadTemplate = async () => {
    try {
        const response = await axios.get('/api/v1/jurisprudence/import/template', {
            responseType: 'blob'
        });
        
        // Create blob link to download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'jurisprudence_template.xlsx');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        
    } catch (error) {
        console.error('Template download failed:', error);
        alert('Failed to download template');
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
        const response = await axios.post('/api/v1/jurisprudence/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    console.log('Upload progress:', uploadProgress.value + '%');
                }
            }
        });
        
        console.log('Upload response:', response.data);
        
        // Check if response has the expected structure
        if (response.data && response.data.success) {
            uploadResult.value = {
                success: true,
                message: response.data.message,
                imported: response.data.data?.imported || 0,
                total_rows: response.data.data?.total_rows || 0,
                failed_count: response.data.data?.failed_count || 0,
                errors: response.data.data?.errors || []
            };
            
            // Clear file input - only set to empty string, not null
            if (fileInput.value) {
                fileInput.value.value = ''; // Clear the file input
            }
            file.value = null;
            
        } else {
            // Handle unexpected response structure
            throw new Error(response.data?.message || 'Unexpected response format');
        }
        
    } catch (error: any) {
        console.error('Upload failed:', error);
        
        let errorMessage = 'Upload failed';
        let detailedErrors: string[] = [];
        
        if (error.response) {
            // The request was made and the server responded with a status code
            console.log('Error response data:', error.response.data);
            console.log('Error status:', error.response.status);
            
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
            // The request was made but no response was received
            errorMessage = 'No response from server. Please check your connection.';
            detailedErrors = ['Network error: Unable to reach the server'];
        } else {
            // Something happened in setting up the request
            errorMessage = error.message;
            detailedErrors = [error.message];
        }
        
        uploadResult.value = {
            success: false,
            message: errorMessage,
            errors: detailedErrors
        };
    } finally {
        console.log('Upload finished, resetting state');
        isUploading.value = false;
        uploadProgress.value = 0;
    }
};

// Clear results
const clearResults = () => {
    uploadResult.value = null;
    file.value = null;
    if (fileInput.value) {
        fileInput.value.value = ''; // Clear the file input
    }
};
</script>

<template>
    <Head title="Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Upload Form -->
            <div class="bg-card rounded-lg border shadow-sm p-6">
                <h2 class="text-2xl font-bold mb-4">Import Jurisprudence from Excel</h2>
                
                <!-- Download Template Button -->
                <div class="mb-6">
                    <Button 
                        variant="outline" 
                        @click="downloadTemplate"
                        class="gap-2"
                    >
                        <Download class="h-4 w-4" />
                        Download Excel Template
                    </Button>
                    <p class="text-sm text-muted-foreground mt-2">
                        Download the template file to ensure correct format
                    </p>
                </div>
                
                <!-- File Upload Area -->
                <div class="space-y-4">
                    <div class="grid w-full max-w-sm items-center gap-1.5">
                        <Label for="file-upload">Excel File (xlsx, xls, csv)</Label>
                        <Input 
                            ref="fileInput"
                            id="file-upload"
                            type="file" 
                            accept=".xlsx,.xls,.csv"
                            @change="onFileChange"
                            :disabled="isUploading"
                        />
                        <p v-if="file" class="text-xs text-muted-foreground mt-1">
                            Selected: {{ file.name }}
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">
                            Max file size: 10MB
                        </p>
                    </div>
                    
                    <!-- Upload Button -->
                    <div class="flex gap-2">
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
                            v-if="uploadResult"
                            variant="outline"
                            @click="clearResults"
                            :disabled="isUploading"
                        >
                            Clear
                        </Button>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div v-if="isUploading && uploadProgress > 0" class="w-full max-w-sm">
                        <div class="bg-secondary h-2 rounded-full overflow-hidden">
                            <div 
                                class="bg-primary h-full transition-all duration-300"
                                :style="{ width: `${uploadProgress}%` }"
                            ></div>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">{{ uploadProgress }}% uploaded</p>
                    </div>
                </div>
                
                <!-- Success Alert -->
                <div v-if="uploadResult && uploadResult.success" class="mt-6">
                    <Alert variant="default" class="border-green-500 bg-green-50">
                        <CheckCircle class="h-4 w-4 text-green-600" />
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
                                    <div class="mt-2 max-h-60 overflow-y-auto bg-green-100 p-3 rounded">
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <li v-for="(error, index) in uploadResult.errors" :key="index" class="text-red-700">
                                                {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                </details>
                            </div>
                            
                            <!-- Success tip -->
                            <div class="mt-4 p-3 bg-green-100 rounded-md">
                                <p class="text-sm font-medium">✅ Import completed successfully!</p>
                                <p class="text-xs mt-1">You can now view the imported records in the jurisprudence list.</p>
                            </div>
                        </AlertDescription>
                    </Alert>
                </div>
                
                <!-- Error Alert -->
                <div v-if="uploadResult && !uploadResult.success" class="mt-6">
                    <Alert variant="destructive">
                        <XCircle class="h-4 w-4" />
                        <AlertTitle>Import Failed</AlertTitle>
                        <AlertDescription>
                            <p class="mb-2 font-semibold">{{ uploadResult.message }}</p>
                            
                            <div v-if="uploadResult.errors && uploadResult.errors.length > 0" class="mt-3">
                                <p class="text-sm font-medium mb-2">Error details:</p>
                                <div class="max-h-60 overflow-y-auto">
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
                                    <li>Check that all required fields (*) are filled</li>
                                    <li>Verify date format is YYYY-MM-DD</li>
                                    <li>Ensure file size is less than 10MB</li>
                                    <li>Download the template again if needed</li>
                                </ul>
                            </div>
                        </AlertDescription>
                    </Alert>
                </div>
            </div>
        </div>
    </AppLayout>
</template>