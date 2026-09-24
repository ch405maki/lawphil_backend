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

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';

// Icons
import { Copy, Loader2 } from 'lucide-vue-next';

const toast = useToast();

interface ProcData {
    id: number | null;
    execord_number: string;
    date: string;
    citation: string;
    tenure: string;
    url: string;
    pdf_availability?: boolean;
    description: string;
    pdf_path: string;
}

interface ValidationErrors {
    execord_number?: string[];
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
    execordData?: ProcData;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const processing = ref(false);
const errors = ref<ValidationErrors>({});
const duplicating = ref(false);
const duplicateOpen = ref(false);
const editingId = ref<number | null>(null);
const isDuplicateEdit = ref(false);

const formData = ref({
    execord_number: '',
    date: '',
    citation: '',
    tenure: '',
    url: '',
    pdf_availability: false,
    description: '',
    pdf_path: '',
});

// Watch for execordData changes to populate form
watch(
    () => props.execordData,
    (newData) => {
        if (newData && newData.id) {
            formData.value = {
                execord_number: newData.execord_number || '',
                date: newData.date ? new Date(newData.date).toISOString().split('T')[0] : '',
                citation: newData.citation || '',
                tenure: newData.tenure || '',
                url: newData.url || '',
                pdf_availability: newData.pdf_availability || false,
                description: newData.description || '',
                pdf_path: newData.pdf_path || '',
            };
            editingId.value = newData.id;
            isDuplicateEdit.value = false;
        }
    },
    { immediate: true },
);

const updateCase = async () => {
    if (!props.execordData || !props.execordData.id) {
        toast.error('Error: No executive order data selected.');
        return;
    }
    errors.value = {};
    processing.value = true;

    try {
        const response = await axios.post(
            `/api/v1/execord/${editingId.value ?? props.execordData.id}`,
            {
                execord_number: formData.value.execord_number,
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
            toast.success('Executive Order updated successfully!');
            emit('update:open', false);
            emit('saved');
        } else {
            throw new Error(response.data.message || 'Failed to update executive order');
        }
    } catch (error: any) {
        console.error('Error updating executive order:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to update executive order';
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

const duplicateAsNew = async () => {
    errors.value = {};
    duplicating.value = true;

    try {
        const response = await axios.post(
            '/api/v1/execord',
            {
                execord_number: formData.value.execord_number,
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
            const created = response.data.data;
            toast.success('Record duplicated — now editing the new record!');
            duplicateOpen.value = false;
            formData.value = {
                execord_number: created.execord_number || '',
                date: created.date ? new Date(created.date).toISOString().split('T')[0] : '',
                citation: created.citation || '',
                tenure: created.tenure || '',
                url: created.url || '',
                pdf_availability: created.pdf_availability || false,
                description: created.description || '',
                pdf_path: created.pdf_path || '',
            };
            editingId.value = created.id ?? null;
            isDuplicateEdit.value = true;
            emit('saved');
        } else {
            throw new Error(response.data.message || 'Failed to duplicate record');
        }
    } catch (error: any) {
        console.error('Error duplicating record:', error);

        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to duplicate record';
            toast.error(errorMessage);
        }
    } finally {
        duplicating.value = false;
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="closeDialog">
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <div class="flex items-start justify-between gap-2 pr-6">
                    <DialogTitle>
                        {{ isDuplicateEdit ? 'Editing Duplicated Record' : 'Edit Executive Order Information' }}
                    </DialogTitle>
                    <Badge v-if="isDuplicateEdit" variant="outline" class="shrink-0 border-primary/50 text-primary"> Duplicate </Badge>
                </div>
                <DialogDescription>
                    <template v-if="isDuplicateEdit">
                        You are editing the newly created duplicate
                        <span v-if="formData.execord_number" class="font-medium text-foreground">(E.O. No. {{ formData.execord_number }})</span>.
                        Saving changes updates this new record — the original is left untouched.
                    </template>
                    <template v-else> Update the executive order details below. </template>
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="execord_number">Executive Order Number</Label>
                        <Input
                            id="execord_number"
                            v-model="formData.execord_number"
                            :class="{ 'border-destructive': errors.execord_number }"
                            :disabled="processing"
                        />
                        <p v-if="errors.execord_number" class="text-xs text-destructive">{{ errors.execord_number[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="date">Date</Label>
                        <Input id="date" type="date" v-model="formData.date" :class="{ 'border-destructive': errors.date }" :disabled="processing" />
                        <p v-if="errors.date" class="text-xs text-destructive">{{ errors.date[0] }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="formData.description"
                        :class="{ 'border-destructive': errors.description }"
                        rows="3"
                        placeholder="Enter the description of the proclamation"
                        :disabled="processing"
                    />
                    <p v-if="errors.description" class="text-xs text-destructive">{{ errors.description[0] }}</p>
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
                    <Label for="citation">Other Keyword</Label>
                    <Textarea
                        id="citation"
                        v-model="formData.citation"
                        :class="{ 'border-destructive': errors.citation }"
                        rows="2"
                        :disabled="processing"
                    />
                    <p v-if="errors.citation" class="text-xs text-destructive">{{ errors.citation[0] }}</p>
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
                <Button variant="outline" @click="closeDialog" :disabled="processing || duplicating"> Cancel </Button>
                <Button variant="outline" @click="duplicateOpen = true" :disabled="processing || duplicating || isDuplicateEdit" class="gap-2">
                    <Copy class="h-4 w-4" />
                    Duplicate
                </Button>
                <Button @click="updateCase" :disabled="processing || duplicating">
                    <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Updating...' : 'Save Changes' }}
                </Button>
            </DialogFooter>
        </DialogContent>
        <AlertDialog :open="duplicateOpen" @update:open="duplicateOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Duplicate as new record?</AlertDialogTitle>
                    <AlertDialogDescription>
                        A new record will be created with the current details. Your edits will not be applied to the original record.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="duplicating">Cancel</AlertDialogCancel>
                    <AlertDialogAction @click.prevent="duplicateAsNew" :disabled="duplicating">
                        <Loader2 v-if="duplicating" class="mr-2 h-4 w-4 animate-spin" />
                        {{ duplicating ? 'Duplicating...' : 'Duplicate' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </Dialog>
</template>
