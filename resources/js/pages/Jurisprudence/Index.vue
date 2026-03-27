<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted, reactive, watch } from 'vue';
import axios from 'axios';

const cases = ref({ data: [] });
const search = ref('');
const rows = ref(10);
const loading = ref(false);

const filters = reactive({ year: '', volume: '' });
const showEditModal = ref(false);
const pdfFile = ref(null); 

const currentCase = reactive({ 
    id: null, 
    gr_number: '', 
    date: '', 
    citation: '', 
    ponente: '', 
    reference: '',
    url: '',
    pdf_availability: false,
    pdf_path: '' 
});

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get(route('api.jurisprudence.index'), { 
            params: { search: search.value, rows: rows.value, page, ...filters } 
        });
        cases.value = res.data;
    } catch (e) {
        console.error("Fetch Error:", e.response?.data || e.message);
    } finally {
        loading.value = false;
    }
};

watch([() => filters.year, () => filters.volume], () => fetchData(1));

const openEdit = (item) => { 
    Object.assign(currentCase, item); 
    if (item.date) currentCase.date = item.date.split('T')[0];
    pdfFile.value = null; 
    showEditModal.value = true; 
};

const handlePdfChange = (e) => {
    pdfFile.value = e.target.files[0];
};

const updateCase = async () => {
    try {
        let data = new FormData();
        data.append('_method', 'PUT'); 
        data.append('gr_number', currentCase.gr_number);
        data.append('date', currentCase.date);
        data.append('citation', currentCase.citation);
        data.append('ponente', currentCase.ponente || '');
        data.append('reference', currentCase.reference || '');
        data.append('url', currentCase.url || '');
        
        if (pdfFile.value) {
            data.append('pdf_file', pdfFile.value);
        }

        await axios.post(route('api.jurisprudence.update', currentCase.id), data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        showEditModal.value = false;
        fetchData(cases.value.current_page);
    } catch (e) { 
        alert("Update failed. Ensure the Date is selected and fields are valid."); 
    }
};

const deleteCase = async (id) => {
    if (!confirm("Are you sure?")) return;
    await axios.delete(route('jurisprudence.destroy', id));
    fetchData(cases.value.current_page);
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
onMounted(fetchData);
</script>

<template>
    <AppLayout title="Jurisprudence">
        <div class="min-h-screen bg-slate-50 text-slate-800 font-sans">
            
            <div class="bg-white border-b border-slate-200 py-6 px-8 sticky top-0 z-10">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Jurisprudence Bank</h1>
                        <p class="text-sm text-slate-500">Legal Archives Management System</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="cursor-pointer px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                            Import Excel/CSV
                            <input type="file" @change="importExcel" class="hidden" />
                        </label>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-md transition-all active:scale-95">
                            + Add New Case
                        </button>
                    </div>
                </div>
            </div>

            <main class="max-w-7xl mx-auto px-8 py-8">
                
                <div class="bg-white p-4 rounded-t-xl border border-slate-200 flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[300px] relative">
                        <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                        <input v-model="search" @input="fetchData(1)" placeholder="Search G.R. No. or Case Title..." 
                               class="w-full bg-slate-50 border-slate-200 rounded-lg py-2 pl-10 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="filters.year" type="number" placeholder="Year" class="w-24 border-slate-200 bg-slate-50 rounded-lg py-2 text-sm p-2" />
                        <button @click="Object.assign(filters, {year: '', volume: ''})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 px-2">Reset</button>
                    </div>
                </div>

                <div class="bg-white border-x border-b border-slate-200 rounded-b-xl shadow-sm overflow-hidden">
                    <table class="w-full text-left text-sm table-fixed">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 w-[15%]">G.R. & Date</th>
                                <th class="px-6 py-4 w-[40%]">Case Title</th>
                                <th class="px-6 py-4 w-[15%]">Reference URL</th>
                                <th class="px-6 py-4 w-[10%] text-center">PDF</th>
                                <th class="px-6 py-4 w-[20%] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in cases.data" :key="item.id" class="hover:bg-indigo-50/30 transition-colors">
                                <td class="px-6 py-5 align-top">
                                    <div class="font-bold text-slate-900 whitespace-nowrap">{{ item.gr_number }}</div>
                                    <div class="text-[11px] text-slate-400 mt-1 font-medium">{{ formatDate(item.date) }}</div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <div class="line-clamp-2 font-semibold text-slate-700 leading-relaxed" :title="item.citation">
                                        {{ item.citation }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1.5 uppercase tracking-tighter italic">
                                        Ponente: {{ item.ponente || 'N/A' }} • Vol: {{ item.reference }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <a v-if="item.url" :href="item.url" target="_blank" class="text-indigo-600 hover:underline text-xs truncate block font-medium">
                                        External Link ↗
                                    </a>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-6 py-5 align-top text-center">
                                    <a v-if="item.pdf_availability" :href="item.pdf_path" target="_blank" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Open PDF">
                                        <span class="text-xs font-bold">PDF</span>
                                    </a>
                                    <span v-else class="text-slate-200">—</span>
                                </td>
                                <td class="px-6 py-5 align-top text-right">
                                    <div class="flex justify-end gap-4">
                                        <button @click="openEdit(item)" class="text-indigo-600 hover:text-indigo-900 font-bold">Edit</button>
                                        <button @click="deleteCase(item.id)" class="text-slate-300 hover:text-red-500 transition-colors">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="loading" class="p-12 text-center text-slate-400 italic bg-white">Syncing database...</div>
                </div>
            </main>
        </div>

        <div v-if="showEditModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden border border-white/20">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 flex-shrink-0">
                    <h3 class="text-lg font-bold text-slate-800">Edit Case Information</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                
                <div class="p-8 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">G.R. Number</label>
                            <input v-model="currentCase.gr_number" class="w-full border-slate-200 rounded-lg text-sm p-2 focus:ring-2 focus:ring-indigo-500/20 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date</label>
                            <input type="date" v-model="currentCase.date" class="w-full border-slate-200 rounded-lg text-sm p-2" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Case Citation / Title</label>
                        <textarea v-model="currentCase.citation" class="w-full border-slate-200 rounded-lg text-sm p-2" rows="3"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ponente</label>
                            <input v-model="currentCase.ponente" class="w-full border-slate-200 rounded-lg text-sm p-2" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Volume</label>
                            <input v-model="currentCase.reference" class="w-full border-slate-200 rounded-lg text-sm p-2" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reference URL</label>
                        <input v-model="currentCase.url" class="w-full border-slate-200 rounded-lg text-sm p-2" />
                    </div>

                    <div class="mt-4 p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                        <label class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest block mb-2">Attached PDF Document</label>
                        <div class="flex items-center gap-3">
                            <input type="file" @change="handlePdfChange" accept=".pdf" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700" />
                        </div>
                        <p v-if="currentCase.pdf_availability" class="text-[9px] text-indigo-400 mt-2 font-medium italic">Current file is already stored in archives.</p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button @click="updateCase" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">Save Changes</button>
                        <button @click="showEditModal = false" class="px-6 py-3 text-slate-500 text-sm font-semibold hover:bg-slate-50 rounded-xl transition">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>