<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

// Shadcn UI Components
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

// Icons
import { Loader2 } from 'lucide-vue-next';

const toast = useToast();

interface RaData {
    id: number | null;
    ra_number: string;
    date: string;
    citation: string;
    tenure: string;
    url: string;
    pdf_availability?: boolean;
    description: string;
    pdf_path: string;
}

interface ValidationErrors {
    ra_number?: string[];
    date?: string[];
    citation?: string[];
    tenure?: string[];
    url?: string[];
    pdf_availability?: string[];
    description?: string[];
    pdf_path?: string[];
}

const props = defineProps<{
    open: boolean;
    raData?: RaData;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});

const formData = ref({
    ra_number: '',
    date: '',
    citation: '',
    tenure: '',
    url: '',
    pdf_availability: false,
    description: '',
    pdf_path: '',
});

// Watch for raData changes to populate form
watch(
    () => props.raData,
    (newData) => {
        if (newData && newData.id) {
            formData.value = {
                ra_number: newData.ra_number || '',
                date: newData.date ? new Date(newData.date).toISOString().split('T')[0] : '',
                citation: newData.citation || '',
                tenure: newData.tenure || '',
                url: newData.url || '',
                pdf_availability: newData.pdf_availability || false,
                description: newData.description || '',
                pdf_path: newData.pdf_path || '',
            };
        }
    },
    { immediate: true },
);

const updateCase = async () => {
    if (!props.raData || !props.raData.id) {
        toast.error('Error: No republic act data selected.');
        return;
    }
    errors.value = {};
    processing.value = true;

    try {
        const response = await axios.post(
            `/api/v1/republic/${props.raData.id}`,
            {
                ra_number: formData.value.ra_number,
                date: formData.value.date,
                citation: formData.value.citation,
                tenure: formData.value.tenure,
                url: formData.value.url,
                pdf_availability: formData.value.pdf_availability,
                description: formData.value.description,
                pdf_path: formData.value.pdf_path,
            },
            {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
            },
        );

        if (response.data.success) {
            toast.success('Republic act updated successfully!');
            emit('update:open', false);
            emit('saved');
        } else {
            throw new Error(response.data.message || 'Failed to update republic act');
        }
    } catch (error: any) {
        console.error('Error updating republic act:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to update republic act';
            toast.error(errorMessage);
        }
    } finally {
        processing.value = false;
    }
};

const closeDialog = () => {
    if (!processing.value) {
        emit('update:open', false);
        errors.value = {};
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Edit Republic Act Information</DialogTitle>
                <DialogDescription> Update the republic act details below. </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="ra_number">Republic Act Number</Label>
                        <Input
                            id="ra_number"
                            v-model="formData.ra_number"
                            :class="{ 'border-destructive': errors.ra_number }"
                            :disabled="processing"
                        />
                        <p v-if="errors.ra_number" class="text-xs text-destructive">{{ errors.ra_number[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="date">Date</Label>
                        <Input id="date" type="date" v-model="formData.date" :class="{ 'border-destructive': errors.date }" :disabled="processing" />
                        <p v-if="errors.date" class="text-xs text-destructive">{{ errors.date[0] }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="citation">Citation / Title</Label>
                    <Textarea
                        id="citation"
                        v-model="formData.citation"
                        :class="{ 'border-destructive': errors.citation }"
                        rows="2"
                        :disabled="processing"
                    />
                    <p v-if="errors.citation" class="text-xs text-destructive">{{ errors.citation[0] }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="tenure">Tenure</Label>
                        <Input id="tenure" v-model="formData.tenure" :disabled="processing" />
                    </div>
                    <div class="space-y-2">
                        <Label for="url">Reference URL</Label>
                        <Input id="url" v-model="formData.url" placeholder="https://..." :disabled="processing" />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="formData.description"
                        :class="{ 'border-destructive': errors.description }"
                        rows="3"
                        placeholder="Enter the description of the republic act"
                        :disabled="processing"
                    />
                    <p v-if="errors.description" class="text-xs text-destructive">{{ errors.description[0] }}</p>
                </div>

                <div class="flex items-start gap-4">
                    <div class="flex items-center space-x-2 pt-2">
                        <Checkbox id="pdf_availability" v-model:checked="formData.pdf_availability" :disabled="processing" />
                        <Label for="pdf_availability" class="cursor-pointer whitespace-nowrap"> PDF Available </Label>
                    </div>

                    <div class="flex-1">
                        <Input
                            id="pdf_path"
                            v-model="formData.pdf_path"
                            type="text"
                            placeholder="Enter PDF path or URL"
                            :class="{ 'border-destructive': errors.pdf_path }"
                            :disabled="processing || !formData.pdf_availability"
                            class="w-full"
                        />
                        <p v-if="errors.pdf_path" class="mt-1 text-xs text-destructive">{{ errors.pdf_path[0] }}</p>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="closeDialog" :disabled="processing"> Cancel </Button>
                <Button @click="updateCase" :disabled="processing">
                    <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Updating...' : 'Save Changes' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>