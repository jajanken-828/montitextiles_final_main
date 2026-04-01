<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ShoppingBag, Package, TrendingUp, Calendar, BarChart3, PieChart, Clock, CheckCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    pipeline: Object,
});

// Placeholder data for charts (you can replace with real data from backend)
const salesTrend = {
    months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    values: [45000, 52000, 48000, 61000, 59000, 72000],
};

const orderStatuses = [
    { label: 'Credit Review', value: props.pipeline.credit_review, color: 'bg-orange-500' },
    { label: 'Tier Assignment', value: props.pipeline.tier_assignment, color: 'bg-blue-500' },
    { label: 'Pending Client Approval', value: props.pipeline.pending_client_approval, color: 'bg-purple-500' },
    { label: 'Approved', value: props.pipeline.approved, color: 'bg-green-500' },
];

const totalOrders = computed(() => orderStatuses.reduce((sum, s) => sum + s.value, 0));

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
};

const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
};
</script>

<template>

    <Head title="ECO Manager Dashboard" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6 sm:space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        E‑Commerce Management <span class="text-indigo-600">Dashboard</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ getGreeting() }}, Monti Team</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <Calendar class="w-4 h-4" />
                    <span>{{ new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                    }}</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Today's Sales</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                formatCurrency(stats.todaySales) }}</p>
                        </div>
                        <div
                            class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl group-hover:bg-green-100 transition">
                            <ShoppingBag class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                        <TrendingUp class="w-3 h-3" /> +12% from yesterday
                    </div>
                </div>

                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Products</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                stats.activeProducts }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl group-hover:bg-blue-100 transition">
                            <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-blue-600 dark:text-blue-400">+5 this month</div>
                </div>

                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Monthly Revenue</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                formatCurrency(stats.monthlyRevenue) }}</p>
                        </div>
                        <div
                            class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl group-hover:bg-purple-100 transition">
                            <TrendingUp class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-purple-600 dark:text-purple-400">Target ₱1.2M</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sales Trend Chart -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                            <BarChart3 class="w-4 h-4 text-indigo-500" /> Sales Trend (Last 6 Months)
                        </h3>
                        <span class="text-xs text-gray-400">₱</span>
                    </div>
                    <div class="h-48 flex items-end gap-2">
                        <div v-for="(value, idx) in salesTrend.values" :key="idx"
                            class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-indigo-500 rounded-t-lg transition-all duration-500"
                                :style="{ height: `${(value / Math.max(...salesTrend.values)) * 100}%`, minHeight: '4px' }">
                            </div>
                            <span class="text-[10px] sm:text-xs mt-2 text-gray-500">{{ salesTrend.months[idx] }}</span>
                            <span class="text-[9px] font-bold text-gray-600 dark:text-gray-400">{{
                                value.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Order Status Breakdown -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <PieChart class="w-4 h-4 text-emerald-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Order Pipeline Status</h3>
                    </div>
                    <div class="space-y-3">
                        <div v-for="status in orderStatuses" :key="status.label" class="relative">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium">{{ status.label }}</span>
                                <span>{{ status.value }} ({{ ((status.value / totalOrders) * 100).toFixed(1) }}%)</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="status.color"
                                    :style="{ width: `${(status.value / totalOrders) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-between text-xs text-gray-500">
                        <span>Total Orders: {{ totalOrders }}</span>
                        <span>Approval Rate: {{((orderStatuses.find(s => s.label === 'Approved').value / totalOrders) *
                            100).toFixed(1)}}%</span>
                    </div>
                </div>
            </div>

            <!-- Pipeline Quick View -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-4">Department Pipeline</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-3 bg-orange-50 dark:bg-orange-900/10 rounded-xl text-center">
                        <p class="text-xs text-orange-600 dark:text-orange-400">Credit Review</p>
                        <p class="text-2xl font-black text-orange-600 dark:text-orange-400">{{ pipeline.credit_review }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/10 rounded-xl text-center">
                        <p class="text-xs text-blue-600 dark:text-blue-400">Tier Assignment</p>
                        <p class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ pipeline.tier_assignment }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/10 rounded-xl text-center">
                        <p class="text-xs text-purple-600 dark:text-purple-400">Pending Client Approval</p>
                        <p class="text-2xl font-black text-purple-600 dark:text-purple-400">{{
                            pipeline.pending_client_approval }}</p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-xl text-center">
                        <p class="text-xs text-green-600 dark:text-green-400">Approved</p>
                        <p class="text-2xl font-black text-green-600 dark:text-green-400">{{ pipeline.approved }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity (Placeholder) -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 flex items-center gap-2 mb-4">
                    <Clock class="w-4 h-4 text-gray-500" /> Recent Activity
                </h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3 text-sm">
                        <CheckCircle class="w-4 h-4 text-green-500 mt-0.5" />
                        <div>
                            <p class="text-gray-700 dark:text-gray-300">Order #PO-2026-0012 approved</p>
                            <p class="text-xs text-gray-400">2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 text-sm">
                        <ShoppingBag class="w-4 h-4 text-blue-500 mt-0.5" />
                        <div>
                            <p class="text-gray-700 dark:text-gray-300">New product added: Classic Polo Shirt</p>
                            <p class="text-xs text-gray-400">5 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 text-sm">
                        <XCircle class="w-4 h-4 text-red-500 mt-0.5" />
                        <div>
                            <p class="text-gray-700 dark:text-gray-300">Credit review rejected for client #C-245</p>
                            <p class="text-xs text-gray-400">Yesterday</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <button class="text-xs text-indigo-600 dark:text-indigo-400 font-medium hover:underline">View all
                        activity</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>