<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Filter, Download, Plus, ChevronDown, ArrowDownRight, ArrowUpRight,
    Settings2, ArrowUpRightSquare, Clock, CheckCircle2, AlertTriangle,
    X, Wallet, TrendingUp, Users, Factory, Package, ShoppingCart, DollarSign,Activity
} from 'lucide-vue-next';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalOrders: 1248,
            totalRevenue: 12135000,
            activeEmployees: 42,
            pendingLeads: 18
        })
    },
});

// Demo data mapped to the new aesthetic
const revenueTrend = computed(() => ({
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    values: [40, 75, 45, 90, 60, 50, 85], // Percentages for the bar chart
}));

// Activity metrics
const activityStats = ref({
    receipts: { value: '120.560,00', trend: '+11.5%', isUp: true },
    contributions: { value: '37.272,00', trend: '+4.5%', isUp: true },
    owes: { value: '9.230,00', trend: '-20.5%', isUp: false },
});

// Transaction History (Replacing Recent Activity with the table format)
const transactions = ref([
    { id: '#1298970', name: 'Guy Hawkins', role: 'Supplier', status: 'Completed', date: 'May 29, 2024', amount: '-$15.30', isNegative: true, avatar: 'GH' },
    { id: '#1848230', name: 'Robert Fox', role: 'Logistics', status: 'Pending', date: 'May 11, 2024', amount: '-$23.00', isNegative: true, avatar: 'RF' },
    { id: '#2948211', name: 'Jacob Jones', role: 'Client', status: 'Completed', date: 'May 09, 2024', amount: '+$140.00', isNegative: false, avatar: 'JJ' },
    { id: '#3029122', name: 'Courtney Henry', role: 'Contractor', status: 'Completed', date: 'May 02, 2024', amount: '-$45.00', isNegative: true, avatar: 'CH' },
]);

// Department Modules
const modules = ref([
    { title: 'Human Resource', icon: Users, stats: { 'Total': '42', 'Open': '3', 'Attendance': '96%' }, color: 'text-blue-500', bg: 'bg-blue-50' },
    { title: 'Supply Chain', icon: ShoppingCart, stats: { 'Suppliers': '24', 'Pending': '8', 'On-time': '94%' }, color: 'text-emerald-500', bg: 'bg-emerald-50' },
    { title: 'Manufacturing', icon: Factory, stats: { 'Orders': '12', 'Running': '7/10', 'Defect': '2.5%' }, color: 'text-amber-500', bg: 'bg-amber-50' },
    { title: 'Inventory', icon: Package, stats: { 'SKUs': '245', 'Alerts': '6', 'Value': '₱2.4M' }, color: 'text-purple-500', bg: 'bg-purple-50' },
]);

const showBanner = ref(true);
</script>

<template>
    <AuthenticatedLayout>

        <Head title="CEO Dashboard" />

        <div class="min-h-screen bg-[#F6F6F8] p-4 sm:p-8 font-sans text-[#1A1A1A]">
            <div class="max-w-[1400px] mx-auto space-y-8">

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">
                            Welcome back, Monti Leadership!
                        </h1>
                        <p class="text-gray-500 mt-2 font-medium">Control your investment, income, and expenses.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            class="flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 rounded-full font-semibold shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-gray-100 hover:bg-gray-50 transition">
                            <Filter class="w-4 h-4" /> Filters
                        </button>
                        <button
                            class="flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 rounded-full font-semibold shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-gray-100 hover:bg-gray-50 transition">
                            <Download class="w-4 h-4" /> Exports
                        </button>
                        <button
                            class="flex items-center gap-2 px-5 py-2.5 bg-[#F05A28] text-white rounded-full font-semibold shadow-md hover:bg-[#d94e22] transition transform hover:scale-105">
                            <Plus class="w-4 h-4" /> Add card
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

                    <div class="xl:col-span-4 flex flex-col gap-6">

                        <div
                            class="bg-white rounded-[2rem] p-7 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50">
                            <div class="flex justify-between items-center mb-8">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Summary</h2>
                                    <p class="text-sm text-gray-400 font-medium">Track your performance.</p>
                                </div>
                                <button
                                    class="flex items-center gap-1 text-sm font-semibold px-4 py-2 border border-gray-100 rounded-full hover:bg-gray-50">
                                    Weekly
                                    <ChevronDown class="w-4 h-4 text-gray-400" />
                                </button>
                            </div>

                            <div class="flex gap-4 mb-10">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-1">
                                        <div
                                            class="w-6 h-6 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                            <ArrowDownRight class="w-3 h-3 text-gray-600" />
                                        </div>
                                        Total income
                                    </div>
                                    <div class="text-2xl font-bold tracking-tight">12.135,00</div>
                                </div>
                                <div class="w-px bg-gray-100"></div>
                                <div class="flex-1 pl-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-1">
                                        <div
                                            class="w-6 h-6 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                            <ArrowUpRight class="w-3 h-3 text-gray-600" />
                                        </div>
                                        Total paid
                                    </div>
                                    <div class="text-2xl font-bold tracking-tight">8.873,00</div>
                                </div>
                            </div>

                            <div class="h-40 flex items-end justify-between gap-3 mt-4">
                                <div v-for="(val, index) in revenueTrend.values" :key="index"
                                    class="w-full relative group flex flex-col justify-end h-full">
                                    <div class="w-full rounded-t-xl rounded-b-sm transition-all duration-500"
                                        :class="index === 2 || index === 3 || index === 4 ? 'bg-[#F05A28]' : 'bg-[#EFECE5]'"
                                        :style="{ height: `${val}%` }">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div
                                class="bg-white rounded-[2rem] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50">
                                <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-4">
                                    <Clock class="w-4 h-4" /> Weekly average
                                </div>
                                <div class="flex items-center gap-1 text-sm font-bold text-gray-900 mb-1">
                                    <ArrowDownRight class="w-4 h-4 text-gray-900" /> -11.5%
                                </div>
                                <div class="text-2xl font-bold">25.460,00</div>
                            </div>
                            <div
                                class="bg-white rounded-[2rem] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50">
                                <div class="flex items-center gap-2 text-sm text-gray-500 font-medium mb-4">
                                    <Activity class="w-4 h-4" /> Annual average
                                </div>
                                <div class="flex items-center gap-1 text-sm font-bold text-[#F05A28] mb-1">
                                    <ArrowUpRight class="w-4 h-4" /> +11.5%
                                </div>
                                <div class="text-2xl font-bold">25.460,00</div>
                            </div>
                        </div>

                        <div v-if="showBanner"
                            class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50 flex justify-between items-center mt-2">
                            <h3 class="font-bold text-lg text-gray-900 leading-tight max-w-[200px]">
                                How is your business management going?
                            </h3>
                            <button @click="showBanner = false"
                                class="w-10 h-10 rounded-full bg-gray-50 hover:bg-gray-100 flex items-center justify-center transition">
                                <X class="w-5 h-5 text-gray-500" />
                            </button>
                        </div>
                    </div>

                    <div class="xl:col-span-8 flex flex-col gap-8">

                        <div>
                            <div class="flex justify-between items-center mb-4 px-2">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Activity</h2>
                                    <p class="text-sm text-gray-400 font-medium">Track your activity.</p>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100 hover:bg-gray-50">
                                        <Settings2 class="w-4 h-4 text-gray-600" />
                                    </button>
                                    <button
                                        class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100 hover:bg-gray-50">
                                        <ArrowUpRightSquare class="w-4 h-4 text-gray-600" />
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div
                                    class="bg-white rounded-[2rem] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50 flex flex-col relative overflow-hidden group">
                                    <div class="flex items-center gap-3 mb-6 relative z-10">
                                        <div class="p-2 bg-gray-50 rounded-xl">
                                            <Wallet class="w-5 h-5 text-gray-700" />
                                        </div>
                                        <span class="font-semibold text-gray-900">Receipts</span>
                                    </div>
                                    <div class="relative z-10">
                                        <div class="text-[#F05A28] text-sm font-bold flex items-center gap-1 mb-1">
                                            <ArrowUpRight class="w-4 h-4" /> {{ activityStats.receipts.trend }}
                                        </div>
                                        <div class="text-3xl font-bold tracking-tight">{{ activityStats.receipts.value
                                            }}</div>
                                    </div>
                                    <div
                                        class="absolute bottom-0 left-0 w-full h-24 opacity-80 group-hover:opacity-100 transition duration-500">
                                        <svg viewBox="0 0 200 60" preserveAspectRatio="none" class="w-full h-full">
                                            <path
                                                d="M0,40 Q10,30 20,45 T40,35 T60,50 T80,20 T100,45 T120,25 T140,50 T160,15 T180,35 T200,20"
                                                fill="none" stroke="#F05A28" stroke-width="2.5"
                                                stroke-linecap="round" />
                                            <path
                                                d="M0,40 Q10,30 20,45 T40,35 T60,50 T80,20 T100,45 T120,25 T140,50 T160,15 T180,35 T200,20 L200,60 L0,60 Z"
                                                fill="url(#gradOrange)" opacity="0.15" />
                                            <defs>
                                                <linearGradient id="gradOrange" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#F05A28" />
                                                    <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    class="bg-white rounded-[2rem] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50 flex flex-col relative overflow-hidden group">
                                    <div class="flex items-center gap-3 mb-6 relative z-10">
                                        <div class="p-2 bg-gray-50 rounded-xl">
                                            <TrendingUp class="w-5 h-5 text-gray-700" />
                                        </div>
                                        <span class="font-semibold text-gray-900">Contributions</span>
                                    </div>
                                    <div class="relative z-10">
                                        <div class="text-[#F05A28] text-sm font-bold flex items-center gap-1 mb-1">
                                            <ArrowUpRight class="w-4 h-4" /> {{ activityStats.contributions.trend }}
                                        </div>
                                        <div class="text-3xl font-bold tracking-tight">{{
                                            activityStats.contributions.value }}</div>
                                    </div>
                                    <div
                                        class="absolute bottom-0 left-0 w-full h-24 opacity-80 group-hover:opacity-100 transition duration-500">
                                        <svg viewBox="0 0 200 60" preserveAspectRatio="none" class="w-full h-full">
                                            <path d="M0,50 Q15,20 30,40 T60,25 T90,45 T120,30 T150,55 T180,25 T200,40"
                                                fill="none" stroke="#F05A28" stroke-width="2.5"
                                                stroke-linecap="round" />
                                            <path
                                                d="M0,50 Q15,20 30,40 T60,25 T90,45 T120,30 T150,55 T180,25 T200,40 L200,60 L0,60 Z"
                                                fill="url(#gradOrange2)" opacity="0.15" />
                                            <defs>
                                                <linearGradient id="gradOrange2" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#F05A28" />
                                                    <stop offset="100%" stop-color="#ffffff" stop-opacity="0" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    class="bg-[#3E2A1E] text-white rounded-[2rem] p-6 shadow-xl flex flex-col relative overflow-hidden group">
                                    <div class="flex items-center gap-3 mb-6 relative z-10">
                                        <div class="p-2 bg-white/10 rounded-xl">
                                            <DollarSign class="w-5 h-5 text-white" />
                                        </div>
                                        <span class="font-semibold text-white">Owes</span>
                                    </div>
                                    <div class="relative z-10">
                                        <div class="text-white/60 text-sm font-medium flex items-center gap-1 mb-1">
                                            <ArrowDownRight class="w-4 h-4" /> {{ activityStats.owes.trend }}
                                        </div>
                                        <div class="text-3xl font-bold tracking-tight">{{ activityStats.owes.value }}
                                        </div>
                                    </div>
                                    <div
                                        class="absolute bottom-0 left-0 w-full h-24 opacity-90 group-hover:opacity-100 transition duration-500">
                                        <svg viewBox="0 0 200 60" preserveAspectRatio="none" class="w-full h-full">
                                            <path d="M0,35 Q20,15 40,45 T80,25 T120,50 T160,30 T200,45" fill="none"
                                                stroke="#F05A28" stroke-width="2.5" stroke-linecap="round" />
                                            <path d="M0,35 Q20,15 40,45 T80,25 T120,50 T160,30 T200,45 L200,60 L0,60 Z"
                                                fill="url(#gradDark)" opacity="0.2" />
                                            <defs>
                                                <linearGradient id="gradDark" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#F05A28" />
                                                    <stop offset="100%" stop-color="#3E2A1E" stop-opacity="0" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-[2rem] p-7 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-gray-50 flex-1">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Transactions history</h2>
                                    <p class="text-sm text-gray-400 font-medium">Track your history.</p>
                                </div>
                                <button
                                    class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100 hover:bg-gray-50">
                                    <Settings2 class="w-4 h-4 text-gray-600" />
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                                            <th class="pb-4 font-semibold px-2">Name</th>
                                            <th class="pb-4 font-semibold px-2">ID</th>
                                            <th class="pb-4 font-semibold px-2">Status</th>
                                            <th class="pb-4 font-semibold px-2">Date</th>
                                            <th class="pb-4 font-semibold px-2 text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr v-for="tx in transactions" :key="tx.id"
                                            class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                            <td class="py-4 px-2">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-600 text-sm overflow-hidden border border-gray-200">
                                                        {{ tx.avatar }}
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-gray-900">{{ tx.name }}</div>
                                                        <div class="text-xs text-gray-500">{{ tx.role }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-2 font-medium text-gray-900">{{ tx.id }}</td>
                                            <td class="py-4 px-2">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                                    :class="tx.status === 'Completed' ? 'bg-green-50 text-green-600' : 'bg-orange-50 text-orange-600'">
                                                    <span class="w-1.5 h-1.5 rounded-full"
                                                        :class="tx.status === 'Completed' ? 'bg-green-600' : 'bg-orange-600'"></span>
                                                    {{ tx.status }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-2 font-medium text-gray-900">{{ tx.date }}</td>
                                            <td class="py-4 px-2 text-right font-bold"
                                                :class="tx.isNegative ? 'text-[#F05A28]' : 'text-gray-900'">
                                                {{ tx.amount }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pt-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 px-2">Department Overview</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="(mod, idx) in modules" :key="idx"
                            class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-gray-50 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3 mb-5">
                                <div :class="[mod.bg, mod.color, 'p-3 rounded-2xl']">
                                    <component :is="mod.icon" class="w-5 h-5" />
                                </div>
                                <h3 class="font-bold text-gray-900">{{ mod.title }}</h3>
                            </div>
                            <div class="space-y-3">
                                <div v-for="(val, key) in mod.stats" :key="key"
                                    class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 font-medium">{{ key }}</span>
                                    <span class="font-bold text-gray-900">{{ val }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Importing an elegant sans-serif font to match the modern look */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.font-sans {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* Subtle custom scrollbar for tables if needed */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>