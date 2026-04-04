<template>
    <Head :title="`Conversation: ${inquiry.product?.name}`" />
    <AuthenticatedLayout>
        <div class="max-w-[1600px] mx-auto space-y-8 p-4 lg:p-8">

            <div class="flex items-center gap-4">
                <Link :href="route('client.conversations')" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                        Conversation about <span class="text-indigo-600">{{ inquiry.product?.name }}</span>
                    </h1>
                    <p class="text-sm text-gray-500">SKU: {{ inquiry.product?.sku }} | Status: {{ formatStatus(inquiry.status) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col h-[calc(100vh-280px)]">
                    
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div v-for="msg in inquiry.messages" :key="msg.id" class="flex" :class="msg.sender_type === 'client' ? 'justify-end' : 'justify-start'">
                            <div :class="msg.sender_type === 'client' 
                                ? 'bg-indigo-600 text-white' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200'"
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

                <div class="space-y-6">
                    <div v-if="quotations.length === 0" class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center text-gray-500">
                        <FileText class="h-10 w-10 mx-auto mb-2 opacity-50" />
                        <p class="text-sm">No quotations yet.</p>
                        <p class="text-xs">ECO will send a quotation after discussing your requirements.</p>
                    </div>

                    <div v-for="quotation in quotations" :key="quotation.id" 
                        class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="p-4 border-b bg-indigo-50 dark:bg-indigo-900/20">
                            <div class="flex justify-between items-center">
                                <span class="font-mono text-sm font-bold">{{ quotation.quotation_number }}</span>
                                <span :class="quotation.status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : 
                                             quotation.status === 'rejected' ? 'bg-red-100 text-red-700' : 
                                             'bg-amber-100 text-amber-700'"
                                    class="px-2 py-0.5 rounded text-[9px] font-black uppercase">
                                    {{ quotation.status }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Valid until: {{ formatDate(quotation.valid_until) }}</p>
                        </div>
                        <div class="p-4 space-y-3">
                            <div v-for="item in quotation.items" :key="item.id" class="flex justify-between text-sm">
                                <span>{{ item.product_name }} x {{ item.quantity }}</span>
                                <span class="font-bold">₱{{ formatPrice(item.line_total) }}</span>
                            </div>
                            <div class="pt-2 border-t flex justify-between font-black text-lg">
                                <span>Total</span>
                                <span class="text-indigo-600">₱{{ formatPrice(quotation.grand_total) }}</span>
                            </div>
                            <div v-if="quotation.custom_notes" class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                {{ quotation.custom_notes }}
                            </div>
                            
                            <div v-if="quotation.status === 'sent'" class="flex gap-2 mt-4">
                                <button @click="openRejectModal(quotation)" class="flex-1 px-3 py-2 border border-red-300 text-red-600 rounded-lg text-xs font-bold hover:bg-red-50">
                                    Reject
                                </button>
                                <button @click="acceptQuotation(quotation)" class="flex-1 px-3 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700">
                                    Accept
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="rejectModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="rejectModal.show = false">
                <div class="bg-white dark:bg-gray-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-red-600 text-white flex justify-between items-center">
                        <h3 class="font-black text-lg">Reject Quotation</h3>
                        <button @click="rejectModal.show = false" class="p-1 hover:bg-white/20 rounded-lg"><X class="h-5 w-5" /></button>
                    </div>
                    <form @submit.prevent="submitReject" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-500 mb-1">Reason for rejection *</label>
                            <textarea v-model="rejectModal.reason" rows="3" required
                                class="w-full rounded-xl border-gray-200 p-3 text-sm focus:ring-red-500 dark:bg-gray-800 dark:border-gray-700"
                                placeholder="e.g., Price too high, delivery date not suitable..."></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="rejectModal.requestNew" id="requestNew" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <label for="requestNew" class="text-sm font-medium dark:text-gray-300">Request a new quotation (ECO will revise)</label>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl text-xs text-gray-600 dark:text-gray-400">
                            <p class="font-bold">Note:</p>
                            <p>If you request a new quotation, ECO will send an updated version. Otherwise, this inquiry will be marked as abandoned.</p>
                        </div>
                        <button type="submit" :disabled="rejectModal.submitting"
                            class="w-full py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition disabled:opacity-50">
                            {{ rejectModal.submitting ? 'Processing...' : 'Confirm Rejection' }}
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>

        <Transition name="toast">
            <div v-if="toast.show" class="fixed bottom-8 right-8 z-50 px-6 py-3 rounded-xl shadow-lg text-white font-bold text-sm"
                :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
                {{ toast.message }}
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, nextTick, onMounted, watch } from 'vue';
import { ArrowLeft, Calendar, Paperclip, Send, Loader2, FileText, X } from 'lucide-vue-next';

const props = defineProps({
    inquiry: Object,
    quotations: Array
});

const messagesContainer = ref(null);
const newMessage = ref('');
const sending = ref(false);
const isTyping = ref(false);
const fileInput = ref(null);
const toast = ref({ show: false, type: 'success', message: '' });

const rejectModal = ref({
    show: false,
    quotation: null,
    reason: '',
    requestNew: false,
    submitting: false
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const showToast = (type, message) => {
    toast.value = { show: true, type, message };
    setTimeout(() => { toast.value.show = false; }, 3000);
};

const sendMessage = () => {
    if (!newMessage.value.trim()) return;
    sending.value = true;
    // Direct URL to avoid Ziggy parameter issues
    const url = `/partner/conversations/${props.inquiry.id}/message`;
    router.post(url, {
        message: newMessage.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newMessage.value = '';
            scrollToBottom();
        },
        onError: (errors) => {
            showToast('error', errors.message || 'Failed to send message.');
        },
        onFinish: () => {
            sending.value = false;
        }
    });
};

const triggerFileUpload = () => {
    fileInput.value.click();
};

const uploadAttachment = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('attachment', file);
    formData.append('message', 'Sent an attachment');
    const url = `/partner/conversations/${props.inquiry.id}/message`;
    router.post(url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        preserveScroll: true,
        onSuccess: () => {
            scrollToBottom();
        },
        onError: (errors) => {
            showToast('error', errors.message || 'Failed to upload attachment.');
        }
    });
    fileInput.value.value = '';
};

const acceptQuotation = (quotation) => {
    if (!confirm('Accept this quotation? This will create a purchase order.')) return;
    const url = `/partner/quotations/${quotation.id}/accept`;
    router.post(url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showToast('success', 'Quotation accepted! Order has been created.');
            router.reload({ only: ['quotations'] });
        },
        onError: (errors) => {
            showToast('error', errors.message || 'Failed to accept quotation.');
        }
    });
};

const openRejectModal = (quotation) => {
    rejectModal.value = {
        show: true,
        quotation: quotation,
        reason: '',
        requestNew: false,
        submitting: false
    };
};

const submitReject = () => {
    if (!rejectModal.value.quotation) return;
    
    if (!rejectModal.value.reason.trim()) {
        showToast('error', 'Please provide a reason for rejection.');
        return;
    }

    rejectModal.value.submitting = true;
    
    const url = `/partner/quotations/${rejectModal.value.quotation.id}/reject`;
    
    router.post(url, {
        reason: rejectModal.value.reason,
        request_new: rejectModal.value.requestNew
    }, {
        preserveScroll: true,
        onSuccess: () => {
            rejectModal.value.show = false;
            showToast('success', rejectModal.value.requestNew ? 'Request for new quotation sent.' : 'Quotation rejected.');
            router.reload({ only: ['quotations'] });
        },
        onError: (errors) => {
            const errorMsg = errors.error || errors.message || Object.values(errors)[0] || 'Failed to reject quotation.';
            showToast('error', errorMsg);
        },
        onFinish: () => {
            rejectModal.value.submitting = false;
        }
    });
};

const formatPrice = (val) => Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2 });
const formatDate = (date) => new Date(date).toLocaleDateString('en-PH');
const formatDateTime = (date) => new Date(date).toLocaleString('en-PH');
const formatTime = (date) => new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
const formatStatus = (status) => {
    const map = { open: 'Open', quotation_sent: 'Quotation Sent', converted: 'Converted', abandoned: 'Abandoned' };
    return map[status] || status;
};

watch(() => props.inquiry.messages, () => scrollToBottom(), { deep: true });
onMounted(() => scrollToBottom());
</script>

<style scoped>
.toast-enter-active, .toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from, .toast-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>