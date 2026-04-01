<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Calendar, Clock, DollarSign, ShoppingBag, FileText, X,
    CheckCircle, XCircle, Package, ChevronLeft, ChevronRight,
    Activity, ArrowRight, Bell, Layers, Zap, TrendingUp, AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ total_orders: 0, total_quotations_sent: 0, pending_settlements: 0 })
    },
    calendarEvents: {
        type: Array,
        default: () => []
    },
});

// ── Calendar Helpers ─────────────────────────────────────────────
const today = new Date();
const currentYear = ref(today.getFullYear());
const currentMonth = ref(today.getMonth()); // 0-indexed

const daysInMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value, 1).getDay();
});

const calendarDays = computed(() => {
    const days = [];
    const totalDays = daysInMonth.value;
    const startDay = firstDayOfMonth.value;

    // Fill leading empty cells
    for (let i = 0; i < startDay; i++) {
        days.push({ date: null, events: [] });
    }

    // Fill actual days
    for (let d = 1; d <= totalDays; d++) {
        const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const events = props.calendarEvents?.filter(e => e.date === dateStr) || [];
        days.push({ date: d, fullDate: dateStr, events });
    }

    return days;
});

const goPrevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const goNextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// ── Modal for Event Details ─────────────────────────────────────
const showModal = ref(false);
const selectedEvents = ref([]);
const isProcessing = ref(false);

const openDay = (day) => {
    if (day.events && day.events.length) {
        selectedEvents.value = day.events;
        showModal.value = true;
    }
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => { selectedEvents.value = []; }, 300); // Wait for transition
};

// Actions
const handleAccept = (quotationId) => {
    isProcessing.value = true;
    router.post(route('client.quotations.accept', quotationId), {}, {
        preserveScroll: true,
        onFinish: () => { isProcessing.value = false; closeModal(); }
    });
};

const handleReject = (quotationId) => {
    if (confirm('Are you sure you want to reject this quotation?')) {
        isProcessing.value = true;
        router.post(route('client.quotations.reject', quotationId), {}, {
            preserveScroll: true,
            onFinish: () => { isProcessing.value = false; closeModal(); }
        });
    }
};

const handleApproveOrder = (orderId) => {
    isProcessing.value = true;
    router.post(route('client.orders.accept', orderId), {}, {
        preserveScroll: true,
        onFinish: () => { isProcessing.value = false; closeModal(); }
    });
};

// Formatters
const formatCurrency = (val) => '₱' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });

// Enhanced Event styling
const getEventStyles = (type) => {
    const map = {
        quotation_sent: {
            bg: 'bg-blue-50 dark:bg-blue-900/30',
            text: 'text-blue-700 dark:text-blue-400',
            border: 'border-blue-200 dark:border-blue-800',
            iconBg: 'bg-blue-500',
            icon: FileText
        },
        quotation_ready: {
            bg: 'bg-emerald-50 dark:bg-emerald-900/30',
            text: 'text-emerald-700 dark:text-emerald-400',
            border: 'border-emerald-200 dark:border-emerald-800',
            iconBg: 'bg-emerald-500',
            icon: CheckCircle
        },
        order_ready: {
            bg: 'bg-indigo-50 dark:bg-indigo-900/30',
            text: 'text-indigo-700 dark:text-indigo-400',
            border: 'border-indigo-200 dark:border-indigo-800',
            iconBg: 'bg-indigo-500',
            icon: Package
        },
        payment_due: {
            bg: 'bg-rose-50 dark:bg-rose-900/30',
            text: 'text-rose-700 dark:text-rose-400',
            border: 'border-rose-200 dark:border-rose-800',
            iconBg: 'bg-rose-500',
            icon: DollarSign
        }
    };
    return map[type] || {
        bg: 'bg-gray-50 dark:bg-gray-800',
        text: 'text-gray-700 dark:text-gray-300',
        border: 'border-gray-200 dark:border-gray-700',
        iconBg: 'bg-gray-500',
        icon: Bell
    };
};

const isToday = (dateNum) => {
    return dateNum === new Date().getDate() &&
        currentMonth.value === new Date().getMonth() &&
        currentYear.value === new Date().getFullYear();
};
</script>

<template>

    <Head title="Client Dashboard - Monti Textile" />
    <AuthenticatedLayout>

        <div class="min-h-screen bg-[#F8F9FA] dark:bg-gray-950 pb-20">

            <div
                class="relative bg-gradient-to-br from-slate-900 to-indigo-950 overflow-hidden text-white pt-10 pb-20 px-4 sm:px-6 lg:px-8 shadow-inner">
                <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
                    <div
                        class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob">
                    </div>
                    <div
                        class="absolute top-48 -left-24 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000">
                    </div>
                    <div
                        class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPHBhdGggZD0iTTAgMEw4IDhaTTggMEwwIDhaIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjA1Ii8+Cjwvc3ZnPg==')] opacity-30">
                    </div>
                </div>

                <div
                    class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-[10px] font-black tracking-widest uppercase text-indigo-200 mb-4">
                            <Layers class="w-3.5 h-3.5" /> Client Portal
                        </div>
                        <h1 class="text-4xl sm:text-5xl font-black tracking-tight leading-tight">
                            Welcome back, <br />
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300">Partner.</span>
                        </h1>
                        <p class="text-indigo-100/80 mt-2 font-medium max-w-lg">Track your textile orders, review
                            pending quotations, and manage upcoming settlements all in one place.</p>
                    </div>

                    <div class="hidden md:block">
                        <div
                            class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-3xl text-center shadow-2xl">
                            <p class="text-xs font-bold text-indigo-200 uppercase tracking-widest mb-1">System Status
                            </p>
                            <div class="flex items-center gap-2 justify-center text-emerald-400 font-bold">
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                All Systems Operational
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 space-y-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="group bg-white dark:bg-gray-900 rounded-[2rem] p-6 shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800 relative overflow-hidden transition-all hover:-translate-y-1">
                        <div
                            class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity group-hover:scale-110 duration-500">
                            <ShoppingBag class="w-24 h-24 text-indigo-500" />
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div
                                class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 mb-6 group-hover:scale-110 transition-transform">
                                <Package class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">
                                    Active Orders</p>
                                <div class="flex items-end gap-3">
                                    <h3 class="text-4xl font-black text-gray-900 dark:text-white leading-none">{{
                                        stats.total_orders }}</h3>
                                    <span
                                        class="flex items-center text-xs font-bold text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg mb-1">
                                        <TrendingUp class="w-3 h-3 mr-1" /> Volume
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group bg-white dark:bg-gray-900 rounded-[2rem] p-6 shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800 relative overflow-hidden transition-all hover:-translate-y-1">
                        <div
                            class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity group-hover:scale-110 duration-500">
                            <FileText class="w-24 h-24 text-cyan-500" />
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div
                                class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-900/50 flex items-center justify-center border border-cyan-100 dark:border-cyan-800 mb-6 group-hover:scale-110 transition-transform">
                                <FileText class="h-6 w-6 text-cyan-600 dark:text-cyan-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">
                                    RFQs Processed</p>
                                <div class="flex items-end gap-3">
                                    <h3 class="text-4xl font-black text-gray-900 dark:text-white leading-none">{{
                                        stats.total_quotations_sent }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group bg-gradient-to-br from-rose-500 to-orange-500 rounded-[2rem] p-6 shadow-xl shadow-rose-500/20 relative overflow-hidden transition-all hover:-translate-y-1 text-white">
                        <div
                            class="absolute top-0 right-0 p-6 opacity-20 group-hover:opacity-30 transition-opacity group-hover:scale-110 duration-500">
                            <DollarSign class="w-24 h-24" />
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div
                                class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center border border-white/30 backdrop-blur-md mb-6 group-hover:scale-110 transition-transform">
                                <Activity class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <p class="text-xs font-black text-rose-100 uppercase tracking-widest mb-1">Pending
                                    Settlements</p>
                                <div class="flex items-end gap-3">
                                    <h3 class="text-4xl font-black leading-none">{{ stats.pending_settlements }}</h3>
                                    <span v-if="stats.pending_settlements > 0"
                                        class="flex items-center text-[10px] font-bold bg-white/20 backdrop-blur-md px-2 py-1 rounded-lg mb-1">
                                        Action Required
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <div
                        class="xl:col-span-2 bg-white dark:bg-gray-900 rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                            <div>
                                <h2
                                    class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                                    <span
                                        class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                                        <Calendar class="h-5 w-5" />
                                    </span>
                                    Logistics Schedule
                                </h2>
                                <p class="text-sm text-gray-500 font-medium mt-1">Track manufacturing phases and
                                    delivery drops.</p>
                            </div>

                            <div
                                class="flex items-center bg-gray-50 dark:bg-gray-800 p-1 rounded-2xl border border-gray-200 dark:border-gray-700">
                                <button @click="goPrevMonth"
                                    class="p-2.5 rounded-xl hover:bg-white dark:hover:bg-gray-700 hover:shadow-sm text-gray-600 transition-all">
                                    <ChevronLeft class="h-4 w-4" />
                                </button>
                                <span
                                    class="px-6 py-1 font-bold text-sm tracking-wide text-gray-900 dark:text-white min-w-[140px] text-center uppercase">
                                    {{ monthNames[currentMonth] }} {{ currentYear }}
                                </span>
                                <button @click="goNextMonth"
                                    class="p-2.5 rounded-xl hover:bg-white dark:hover:bg-gray-700 hover:shadow-sm text-gray-600 transition-all">
                                    <ChevronRight class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 gap-2 text-center mb-3">
                            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day"
                                class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                {{ day }}
                            </div>
                        </div>

                        <div class="grid grid-cols-7 gap-2 md:gap-3">
                            <div v-for="(day, idx) in calendarDays" :key="idx" @click="openDay(day)"
                                class="relative min-h-[100px] md:min-h-[120px] p-2 md:p-3 rounded-2xl border transition-all duration-300 group flex flex-col"
                                :class="[
                                    !day.date ? 'border-transparent bg-transparent cursor-default' : 'border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20 cursor-pointer hover:bg-white dark:hover:bg-gray-800 hover:shadow-lg hover:border-indigo-200 dark:hover:border-indigo-800',
                                    isToday(day.date) ? 'ring-2 ring-indigo-500 bg-indigo-50/30 dark:bg-indigo-900/10' : ''
                                ]">

                                <span v-if="day.date"
                                    class="text-sm font-bold flex items-center justify-center w-7 h-7 rounded-full mb-2"
                                    :class="isToday(day.date) ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 group-hover:text-indigo-600'">
                                    {{ day.date }}
                                </span>

                                <div class="mt-auto space-y-1.5 w-full custom-scrollbar overflow-y-auto max-h-[60px]">
                                    <div v-for="event in day.events.slice(0, 3)" :key="event.id"
                                        class="text-[10px] font-bold px-2 py-1.5 rounded-lg border truncate transition-all shadow-sm"
                                        :class="[getEventStyles(event.type).bg, getEventStyles(event.type).text, getEventStyles(event.type).border]">
                                        {{ event.title }}
                                    </div>
                                    <div v-if="day.events.length > 3"
                                        class="text-[10px] font-black text-gray-400 text-center uppercase tracking-wider">
                                        +{{ day.events.length - 3 }} More
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-900 rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h2 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <Zap class="h-5 w-5 text-amber-500" /> Action Items
                                </h2>
                                <p class="text-xs text-gray-500 font-medium mt-1 uppercase tracking-wider">Upcoming
                                    Deadlines</p>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
                            <div v-if="calendarEvents.length === 0"
                                class="h-full flex flex-col items-center justify-center text-gray-400 space-y-4 py-10">
                                <div
                                    class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center border border-gray-100 dark:border-gray-700">
                                    <CheckCircle class="w-8 h-8 text-gray-300" />
                                </div>
                                <p class="text-sm font-bold text-center">You're all caught up!<br /><span
                                        class="font-normal text-xs">No pending actions required.</span></p>
                            </div>

                            <div v-else
                                class="relative border-l-2 border-gray-100 dark:border-gray-800 ml-3 space-y-8 pb-4">
                                <div v-for="(event, i) in calendarEvents.slice(0, 6)" :key="event.id"
                                    @click="openDay({ events: [event] })" class="relative pl-6 cursor-pointer group">

                                    <div class="absolute -left-[11px] top-1 w-5 h-5 rounded-full border-4 border-white dark:border-gray-900 transition-transform group-hover:scale-125"
                                        :class="getEventStyles(event.type).iconBg"></div>

                                    <div
                                        class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 group-hover:border-indigo-300 dark:group-hover:border-indigo-700 transition-all group-hover:shadow-md">
                                        <div class="flex justify-between items-start mb-1">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white line-clamp-1">{{
                                                event.title }}</p>
                                            <span
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-wider whitespace-nowrap ml-2">
                                                {{ formatDate(event.date).split(',')[0] }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-medium" :class="getEventStyles(event.type).text">
                                            {{ event.type.replace('_', ' ').toUpperCase() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            class="mt-6 w-full py-3 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                            View Full History
                            <ArrowRight class="w-4 h-4" />
                        </button>
                    </div>

                </div>
            </div>

            <Teleport to="body">
                <Transition name="fade">
                    <div v-if="showModal"
                        class="fixed inset-0 z-[150] flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-sm"
                        @click.self="closeModal">
                        <div
                            class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[2rem] shadow-2xl overflow-hidden max-h-[90vh] flex flex-col border border-gray-200 dark:border-gray-800 transform transition-all">

                            <div
                                class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-center z-10 sticky top-0">
                                <div>
                                    <h2
                                        class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                                        <Calendar class="h-6 w-6 text-indigo-500" />
                                        {{ selectedEvents[0] ? formatDate(selectedEvents[0].date) : 'Event Details' }}
                                    </h2>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mt-1">Daily Log
                                        Overview</p>
                                </div>
                                <button @click="closeModal"
                                    class="p-2.5 bg-white dark:bg-gray-800 hover:bg-rose-50 text-gray-500 hover:text-rose-600 rounded-xl transition-colors border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <div
                                class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar bg-white dark:bg-gray-900">
                                <div v-for="event in selectedEvents" :key="event.id"
                                    class="border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">

                                    <div class="px-5 py-4 flex items-center gap-3 border-b border-gray-100 dark:border-gray-800"
                                        :class="getEventStyles(event.type).bg">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm"
                                            :class="getEventStyles(event.type).iconBg">
                                            <component :is="getEventStyles(event.type).icon"
                                                class="w-4 h-4 text-white" />
                                        </div>
                                        <h3 class="font-black text-lg text-gray-900 dark:text-white">{{ event.title }}
                                        </h3>
                                    </div>

                                    <div class="p-5">
                                        <div class="grid grid-cols-2 gap-4 mb-5">
                                            <template
                                                v-if="event.type === 'quotation_ready' || event.type === 'quotation_sent'">
                                                <div>
                                                    <p
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                                        Ref Number</p>
                                                    <p class="font-mono text-sm font-medium dark:text-gray-300">{{
                                                        event.details?.quotation_number || 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                                        Valid Until</p>
                                                    <p class="text-sm font-medium dark:text-gray-300">{{
                                                        event.details?.valid_until ?
                                                        formatDate(event.details.valid_until) : 'N/A' }}</p>
                                                </div>
                                                <div
                                                    class="col-span-2 bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 flex justify-between items-center border border-gray-100 dark:border-gray-700">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 uppercase tracking-wider">Estimated
                                                        Total</span>
                                                    <span
                                                        class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{
                                                        formatCurrency(event.details?.grand_total || 0) }}</span>
                                                </div>
                                            </template>

                                            <template v-if="event.type === 'order_ready'">
                                                <div>
                                                    <p
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                                        PO Number</p>
                                                    <p class="font-mono text-sm font-medium dark:text-gray-300">{{
                                                        event.details?.po_number || 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                                        Service Tier</p>
                                                    <p class="text-sm font-medium dark:text-gray-300">
                                                        <span
                                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">{{
                                                            event.details?.tier_level || 'Standard' }}</span>
                                                    </p>
                                                </div>
                                                <div
                                                    class="col-span-2 bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 flex justify-between items-center border border-gray-100 dark:border-gray-700">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 uppercase tracking-wider">Order
                                                        Value</span>
                                                    <span
                                                        class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{
                                                        formatCurrency(event.details?.total_amount || 0) }}</span>
                                                </div>
                                            </template>

                                            <template v-if="event.type === 'payment_due'">
                                                <div class="col-span-2">
                                                    <p
                                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                                        Related PO Number</p>
                                                    <p class="font-mono text-sm font-medium dark:text-gray-300">{{
                                                        event.details?.po_number || 'N/A' }}</p>
                                                </div>
                                                <div
                                                    class="col-span-2 bg-rose-50 dark:bg-rose-900/20 rounded-xl p-4 flex justify-between items-center border border-rose-100 dark:border-rose-800/50">
                                                    <span
                                                        class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Amount
                                                        Due</span>
                                                    <span class="text-xl font-black text-rose-700 dark:text-rose-300">{{
                                                        formatCurrency(event.details?.total_amount || 0) }}</span>
                                                </div>
                                            </template>
                                        </div>

                                        <div v-if="event.type === 'quotation_ready'"
                                            class="flex gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <button @click="handleReject(event.id)" :disabled="isProcessing"
                                                class="flex-1 px-4 py-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all text-sm disabled:opacity-50 flex items-center justify-center gap-2">
                                                <XCircle class="w-4 h-4" /> Reject Terms
                                            </button>
                                            <button @click="handleAccept(event.id)" :disabled="isProcessing"
                                                class="flex-1 px-4 py-3 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all text-sm disabled:opacity-50 flex items-center justify-center gap-2">
                                                <CheckCircle class="w-4 h-4" /> Approve Quotation
                                            </button>
                                        </div>

                                        <div v-if="event.type === 'order_ready'"
                                            class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                            <button @click="handleApproveOrder(event.id)" :disabled="isProcessing"
                                                class="w-full px-4 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all text-sm disabled:opacity-50 flex items-center justify-center gap-2">
                                                <Package class="w-4 h-4" /> Authorize Production
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Typography */
.font-black {
    font-weight: 900;
}

/* Custom Scrollbars */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
    border-radius: 20px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #475569;
}

/* Animations for Header shapes */
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }

    33% {
        transform: translate(30px, -50px) scale(1.1);
    }

    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }

    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

/* Modal Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}
</style>