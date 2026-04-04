<template>
    <Head title="Push to Modules - ECO" />
    <AuthenticatedLayout>
        <div class="max-w-[1600px] mx-auto space-y-10 p-4 lg:p-10">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em]">
                        <Send class="h-3.5 w-3.5" />
                        Order Dispatch
                    </div>
                    <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter uppercase">
                        Push <span class="text-indigo-600">Center</span>
                    </h1>
                    <p class="text-sm font-medium text-gray-500 italic">
                        Forward approved sales orders to Supply Chain or Order Management.
                    </p>
                </div>
                <button @click="refreshData" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <RefreshCw class="h-5 w-5 text-gray-500" />
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-3 px-6 font-black uppercase text-sm border-b-2 transition">
                    Pending Push ({{ pendingOrders.length }})
                </button>
                <button @click="activeTab = 'pushed'"
                    :class="activeTab === 'pushed' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-3 px-6 font-black uppercase text-sm border-b-2 transition">
                    Already Pushed ({{ pushedOrders.length }})
                </button>
            </div>

            <!-- Pending Orders Table -->
            <div v-if="activeTab === 'pending'" class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-gray-800/30 text-[10px] font-black uppercase text-gray-400 tracking-[0.15em]">
                            <tr>
                                <th class="px-8 py-5">PO Number</th>
                                <th class="px-8 py-5">Client</th>
                                <th class="px-8 py-5 text-right">Total Amount</th>
                                <th class="px-8 py-5 text-center">Tier</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr v-for="order in pendingOrders" :key="order.id" class="group hover:bg-gray-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <span class="font-mono text-sm font-black text-gray-900 dark:text-white">{{ order.po_number }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                            <Building2 class="h-4 w-4 text-indigo-600" />
                                        </div>
                                        <span class="text-sm font-bold">{{ order.client?.company_name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right font-black text-gray-900">₱{{ formatCurrency(order.total_amount) }}</td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-[9px] font-black uppercase">{{ order.tier_level || 'Normal' }}</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex gap-2 justify-center">
                                        <button @click="pushToModule(order, 'scm')" :disabled="pushing[order.id]"
                                            class="flex items-center gap-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase hover:bg-blue-700 transition disabled:opacity-50">
                                            <Truck v-if="!pushing[order.id]" class="h-3 w-3" />
                                            <Loader2 v-else class="h-3 w-3 animate-spin" />
                                            Push to SCM
                                        </button>
                                        <button @click="pushToModule(order, 'order_mgmt')" :disabled="pushing[order.id]"
                                            class="flex items-center gap-1 px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase hover:bg-emerald-700 transition disabled:opacity-50">
                                            <Layers v-if="!pushing[order.id]" class="h-3 w-3" />
                                            <Loader2 v-else class="h-3 w-3 animate-spin" />
                                            Push to Order Mgmt
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pendingOrders.length === 0">
                                <td colspan="5" class="px-8 py-20 text-center text-gray-400 uppercase font-black italic">
                                    No pending sales orders to push.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Already Pushed Orders Table -->
            <div v-if="activeTab === 'pushed'" class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-gray-800/30 text-[10px] font-black uppercase text-gray-400 tracking-[0.15em]">
                            <tr>
                                <th class="px-8 py-5">PO Number</th>
                                <th class="px-8 py-5">Client</th>
                                <th class="px-8 py-5 text-right">Total Amount</th>
                                <th class="px-8 py-5 text-center">Pushed To</th>
                                <th class="px-8 py-5 text-center">Pushed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr v-for="order in pushedOrders" :key="order.id" class="group hover:bg-gray-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <span class="font-mono text-sm font-black text-gray-900 dark:text-white">{{ order.po_number }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-sm font-bold">{{ order.client?.company_name }}</span>
                                </td>
                                <td class="px-8 py-6 text-right font-black text-gray-900">₱{{ formatCurrency(order.total_amount) }}</td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-[9px] font-black uppercase">{{ order.pushed_to || 'SCM / Order Mgmt' }}</span>
                                </td>
                                <td class="px-8 py-6 text-center text-xs text-gray-500">{{ formatDate(order.pushed_at) }}</td>
                            </tr>
                            <tr v-if="pushedOrders.length === 0">
                                <td colspan="5" class="px-8 py-20 text-center text-gray-400 uppercase font-black italic">
                                    No orders have been pushed yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Success/Error Toast (optional) -->
            <Transition name="toast">
                <div v-if="toast.show" class="fixed bottom-8 right-8 z-50 px-6 py-3 rounded-xl shadow-lg text-white font-bold text-sm"
                    :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
                    {{ toast.message }}
                </div>
            </Transition>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Send, RefreshCw, Building2, Truck, Layers, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    salesOrders: {
        type: Array,
        default: () => []
    },
    pushedOrders: {
        type: Array,
        default: () => []
    }
});

const activeTab = ref('pending');
const pushing = ref({});
const toast = ref({ show: false, type: 'success', message: '' });

const pendingOrders = computed(() => props.salesOrders);
// Already pushed orders are passed as prop (from controller)

const formatCurrency = (val) => Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2 });
const formatDate = (date) => date ? new Date(date).toLocaleString('en-PH') : '—';

const showToast = (type, message) => {
    toast.value = { show: true, type, message };
    setTimeout(() => { toast.value.show = false; }, 3000);
};

const pushToModule = async (order, module) => {
    pushing.value[order.id] = true;
    try {
        let routeName = module === 'scm' ? 'eco.push.scm' : 'eco.push.ordermgmt';
        await router.post(route(routeName, order.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                showToast('success', `Order ${order.po_number} pushed to ${module === 'scm' ? 'SCM' : 'Order Management'}.`);
                // Optionally reload data
                refreshData();
            },
            onError: (errors) => {
                showToast('error', Object.values(errors)[0] || 'Push failed.');
            }
        });
    } catch (e) {
        showToast('error', 'An error occurred.');
    } finally {
        pushing.value[order.id] = false;
    }
};

const refreshData = () => {
    router.reload({ only: ['salesOrders', 'pushedOrders'] });
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>