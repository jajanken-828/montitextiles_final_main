<template>
    <Head :title="`Inquiry: ${inquiry.client?.company_name} - ${inquiry.product?.name}`" />
    <AuthenticatedLayout>
        <div class="max-w-[1600px] mx-auto space-y-8 p-4 lg:p-8">

            <!-- Header with back button -->
            <div class="flex items-center gap-4">
                <Link :href="route('eco.inquiries')" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                            Conversation with <span class="text-indigo-600">{{ inquiry.client?.company_name }}</span>
                        </h1>
                        <span :class="statusBadge(inquiry.status)" class="px-3 py-1 rounded-full text-[9px] font-black uppercase">
                            {{ inquiry.status }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">Product: {{ inquiry.product?.name }} (SKU: {{ inquiry.product?.sku }})</p>
                </div>
                <div class="ml-auto">
                    <button @click="checkCredit" class="flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 rounded-xl text-[10px] font-black uppercase hover:bg-amber-100 transition">
                        <Shield class="h-4 w-4" /> Credit Check
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main conversation area -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col h-[calc(100vh-250px)]">
                    <!-- Messages -->
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div v-for="msg in inquiry.messages" :key="msg.id" class="flex" :class="msg.sender_type === 'client' ? 'justify-start' : 'justify-end'">
                            <div :class="msg.sender_type === 'client' 
                                ? 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200' 
                                : 'bg-indigo-600 text-white'"
                                class="max-w-[70%] rounded-2xl px-4 py-2 shadow-sm">
                                <p class="text-sm">{{ msg.message }}</p>
                                <div v-if="msg.meeting_data" class="mt-2 text-xs border-t border-white/20 pt-1">
                                    <Calendar class="inline h-3 w-3 mr-1" />
                                    Meeting: {{ msg.meeting_data.type }} at {{ msg.meeting_data.location }} on {{ formatDateTime(msg.meeting_data.scheduled_at) }}
                                </div>
                                <div v-if="msg.attachment" class="mt-1">
                                    <a :href="msg.attachment" target="_blank" class="text-xs underline flex items-center gap-1">
                                        <Paperclip class="h-3 w-3" /> Attachment
                                    </a>
                                </div>
                                <p class="text-[10px] opacity-70 mt-1 text-right">{{ formatTime(msg.created_at) }}</p>
                            </div>
                        </div>
                        <div v-if="isTyping" class="flex justify-start">
                            <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl px-4 py-2 text-gray-500 text-sm">
                                ECO is typing...
                            </div>
                        </div>
                    </div>

                    <!-- Message input -->
                    <div class="border-t border-gray-100 dark:border-gray-800 p-4">
                        <form @submit.prevent="sendMessage" class="flex gap-3">
                            <input v-model="newMessage" type="text" placeholder="Type your message..." 
                                class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <button type="button" @click="triggerFileUpload" class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200">
                                <Paperclip class="h-5 w-5 text-gray-500" />
                            </button>
                            <input ref="fileInput" type="file" class="hidden" @change="uploadAttachment">
                            <button type="submit" :disabled="sending" class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition">
                                <Send v-if="!sending" class="h-5 w-5" />
                                <Loader2 v-else class="h-5 w-5 animate-spin" />
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar actions -->
                <div class="space-y-6">
                    <!-- Set Meeting Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
                        <h3 class="text-sm font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                            <Calendar class="h-4 w-4 text-indigo-600" /> Schedule Meeting
                        </h3>
                        <form @submit.prevent="scheduleMeeting">
                            <div class="space-y-3">
                                <input v-model="meetingData.scheduled_at" type="datetime-local" required
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 p-2 text-sm">
                                <input v-model="meetingData.location" type="text" placeholder="Location (e.g., Zoom, Office)" required
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 p-2 text-sm">
                                <select v-model="meetingData.type" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 p-2 text-sm">
                                    <option value="onsite">On-site</option>
                                    <option value="video">Video Call</option>
                                    <option value="phone">Phone Call</option>
                                </select>
                                <button type="submit" :disabled="scheduling" class="w-full py-2 bg-indigo-100 text-indigo-700 rounded-xl font-bold text-sm hover:bg-indigo-200 transition">
                                    {{ scheduling ? 'Scheduling...' : 'Send Meeting Invite' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Issue Quotation Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
                        <h3 class="text-sm font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                            <FileText class="h-4 w-4 text-indigo-600" /> Issue Quotation
                        </h3>
                        <button @click="openQuotationModal" class="w-full py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition">
                            Create New Quotation
                        </button>
                    </div>

                    <!-- Previously sent quotations -->
                    <div v-if="quotations.length > 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
                        <h3 class="text-sm font-black uppercase tracking-widest mb-3">Sent Quotations</h3>
                        <div class="space-y-2">
                            <div v-for="q in quotations" :key="q.id" class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                                <div class="flex justify-between items-center">
                                    <span class="font-mono text-xs">{{ q.quotation_number }}</span>
                                    <span :class="q.status === 'accepted' ? 'text-emerald-600' : q.status === 'rejected' ? 'text-red-600' : 'text-amber-600'" class="text-[9px] font-black uppercase">
                                        {{ q.status }}
                                    </span>
                                </div>
                                <p class="text-sm font-black mt-1">₱{{ formatPrice(q.grand_total) }}</p>
                                <p class="text-[10px] text-gray-500">Valid until: {{ formatDate(q.valid_until) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quotation Modal -->
        <Teleport to="body">
            <div v-if="showQuotationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showQuotationModal = false">
                <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="px-6 py-4 bg-indigo-600 text-white flex justify-between items-center">
                        <h3 class="font-black text-lg">Issue Quotation</h3>
                        <button @click="showQuotationModal = false" class="p-1 hover:bg-white/20 rounded-lg"><X class="h-5 w-5" /></button>
                    </div>
                    <div class="p-6 overflow-y-auto">
                        <form @submit.prevent="submitQuotation" class="space-y-4">
                            <div v-for="(item, idx) in quotationItems" :key="idx" class="border p-3 rounded-xl space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-bold text-sm">Item {{ idx+1 }}</span>
                                    <button type="button" @click="removeItem(idx)" v-if="quotationItems.length > 1" class="text-red-500 text-xs">Remove</button>
                                </div>
                                <select v-model="item.product_id" required class="w-full rounded-lg border p-2 text-sm">
                                    <option value="">Select product</option>
                                    <option v-for="p in allProducts" :key="p.id" :value="p.id">{{ p.name }} (₱{{ formatPrice(p.selling_price) }})</option>
                                </select>
                                <input v-model.number="item.quantity" type="number" placeholder="Quantity" required class="w-full rounded-lg border p-2 text-sm">
                                <input v-model.number="item.unit_price" type="number" step="0.01" placeholder="Unit Price" required class="w-full rounded-lg border p-2 text-sm">
                                <input v-model="item.technical_specs" placeholder="Technical specs (optional)" class="w-full rounded-lg border p-2 text-sm">
                            </div>
                            <button type="button" @click="addItem" class="text-sm text-indigo-600 font-bold">+ Add another item</button>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div><label class="block text-xs font-bold">Delivery Date *</label><input v-model="quotationForm.delivery_date" type="date" required class="w-full rounded-lg border p-2"></div>
                                <div><label class="block text-xs font-bold">Payment Mode *</label><input v-model="quotationForm.payment_mode" required class="w-full rounded-lg border p-2" placeholder="e.g., Bank Transfer"></div>
                                <div class="col-span-2"><label class="block text-xs font-bold">Payment Terms *</label><input v-model="quotationForm.payment_terms" required class="w-full rounded-lg border p-2" placeholder="e.g., 30% DP, 70% before shipment"></div>
                                <div class="col-span-2"><label class="block text-xs font-bold">Notes / Remarks</label><textarea v-model="quotationForm.notes" rows="2" class="w-full rounded-lg border p-2"></textarea></div>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <p class="font-bold">Subtotal: ₱{{ formatPrice(computedSubtotal) }}</p>
                                <p class="font-black text-indigo-600 text-lg mt-1">Grand Total: ₱{{ formatPrice(computedSubtotal) }}</p>
                                <p class="text-xs text-gray-500">* Tax & shipping can be added later in SCM.</p>
                            </div>
                            
                            <button type="submit" :disabled="submitting" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">
                                {{ submitting ? 'Sending...' : 'Send Quotation to Client' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import { ArrowLeft, Shield, Calendar, Paperclip, Send, Loader2, FileText, X } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    inquiry: Object,
    quotations: Array,
    allProducts: Array   // from inventory for dropdown
});

const messagesContainer = ref(null);
const newMessage = ref('');
const sending = ref(false);
const scheduling = ref(false);
const isTyping = ref(false);
const showQuotationModal = ref(false);
const submitting = ref(false);
const fileInput = ref(null);

// Meeting form
const meetingData = ref({
    scheduled_at: '',
    location: '',
    type: 'video'
});

// Quotation form
const quotationItems = ref([{ product_id: '', quantity: 1, unit_price: 0, technical_specs: '' }]);
const quotationForm = ref({
    delivery_date: '',
    payment_mode: '',
    payment_terms: '',
    notes: ''
});

const computedSubtotal = computed(() => {
    return quotationItems.value.reduce((sum, item) => {
        return sum + (item.quantity * item.unit_price);
    }, 0);
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;
    sending.value = true;
    try {
        await router.post(route('eco.inquiry.message', props.inquiry.id), { message: newMessage.value });
        newMessage.value = '';
        scrollToBottom();
    } finally {
        sending.value = false;
    }
};

const triggerFileUpload = () => {
    fileInput.value.click();
};

const uploadAttachment = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('attachment', file);
    formData.append('message', 'Sent an attachment');
    await router.post(route('eco.inquiry.message', props.inquiry.id), formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    });
    fileInput.value.value = '';
    scrollToBottom();
};

const scheduleMeeting = async () => {
    scheduling.value = true;
    await router.post(route('eco.inquiry.meeting', props.inquiry.id), meetingData.value);
    meetingData.value = { scheduled_at: '', location: '', type: 'video' };
    scheduling.value = false;
    scrollToBottom();
};

const checkCredit = async () => {
    try {
        const res = await axios.get(route('eco.credit.check', props.inquiry.client_id));
        alert(`Credit Status: ${res.data.is_good_payer ? 'Good Payer' : 'High Risk'}\nOutstanding Balance: ₱${res.data.outstanding}`);
    } catch (error) {
        console.error('Credit check failed', error);
        alert('Could not fetch credit information.');
    }
};

const openQuotationModal = () => {
    showQuotationModal.value = true;
};

const addItem = () => {
    quotationItems.value.push({ product_id: '', quantity: 1, unit_price: 0, technical_specs: '' });
};

const removeItem = (idx) => {
    quotationItems.value.splice(idx, 1);
};

const submitQuotation = async () => {
    for (let item of quotationItems.value) {
        if (!item.product_id || item.quantity <= 0 || item.unit_price <= 0) {
            alert('Please fill all product fields correctly.');
            return;
        }
    }
    if (!quotationForm.value.delivery_date || !quotationForm.value.payment_mode || !quotationForm.value.payment_terms) {
        alert('Please fill delivery date, payment mode, and payment terms.');
        return;
    }
    submitting.value = true;
    await router.post(route('eco.inquiry.quotation', props.inquiry.id), {
        items: quotationItems.value,
        delivery_date: quotationForm.value.delivery_date,
        payment_mode: quotationForm.value.payment_mode,
        payment_terms: quotationForm.value.payment_terms,
        notes: quotationForm.value.notes
    });
    submitting.value = false;
    showQuotationModal.value = false;
    quotationItems.value = [{ product_id: '', quantity: 1, unit_price: 0, technical_specs: '' }];
    quotationForm.value = { delivery_date: '', payment_mode: '', payment_terms: '', notes: '' };
    scrollToBottom();
};

const formatPrice = (val) => Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2 });
const formatDate = (date) => new Date(date).toLocaleDateString('en-PH');
const formatDateTime = (date) => new Date(date).toLocaleString('en-PH');
const formatTime = (date) => new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

const statusBadge = (status) => {
    const map = {
        open: 'bg-blue-100 text-blue-700',
        quotation_sent: 'bg-indigo-100 text-indigo-700',
        converted: 'bg-emerald-100 text-emerald-700'
    };
    return map[status] || 'bg-gray-100 text-gray-600';
};

watch(() => props.inquiry.messages, () => {
    scrollToBottom();
}, { deep: true });

onMounted(() => {
    scrollToBottom();
    // Debug: log products to console
    console.log('Available products:', props.allProducts);
});
</script>