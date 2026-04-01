<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, DollarSign, Factory, TrendingUp, AlertTriangle, Building2, Calendar, Zap, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    materialRequestsTrend: Object,
    supplierPerformance: Array,
    urgencyCounts: Object,
});

// Helper for greeting
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
};

// Max value for trend chart scaling
const maxTrendValue = computed(() => Math.max(...props.materialRequestsTrend.values, 1));
const totalRequests = computed(() => props.stats.pendingMaterialRequests || 1);
</script>

<template>

    <Head title="Supply chain Management" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6 sm:space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        Supply Chain Management <span class="text-blue-600">Dashboard</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ getGreeting() }}, Monti Team</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <Calendar class="w-4 h-4" />
                    <span>{{ new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                        }}</span>
                </div>
            </div>

            <!-- Stats Cards (Enhanced) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Material
                                Requests</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                stats.pendingMaterialRequests }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl group-hover:bg-blue-100 transition">
                            <ClipboardList class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        <TrendingUp class="w-3 h-3" /> {{ stats.pendingMaterialRequests > 0 ? '+12%' : 'No pending' }}
                    </div>
                </div>

                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Invoices</p>
                            <p class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-1">{{
                                stats.pendingInvoices }}</p>
                        </div>
                        <div
                            class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl group-hover:bg-amber-100 transition">
                            <DollarSign class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-amber-600 dark:text-amber-400">{{ stats.pendingInvoices > 0 ?
                        'Overduealerts' : 'All paid' }}</div>
                </div>

                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Orders Ready for
                                Manufacturing</p>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{
                                stats.readyOrders }}</p>
                        </div>
                        <div
                            class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl group-hover:bg-emerald-100 transition">
                            <Factory class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-emerald-600 dark:text-emerald-400">Ready for production</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Material Requests Trend (Enhanced) -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <TrendingUp class="w-4 h-4 text-blue-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Material Requests Trend</h3>
                    </div>
                    <div class="h-48 flex items-end gap-2">
                        <div v-for="(value, idx) in materialRequestsTrend.values" :key="idx"
                            class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-gradient-to-t from-blue-500 to-blue-600 rounded-t-lg transition-all duration-500"
                                :style="{ height: `${(value / maxTrendValue) * 100}%`, minHeight: '4px' }"></div>
                            <span class="text-[10px] sm:text-xs mt-2 text-gray-500">{{ materialRequestsTrend.months[idx]
                                }}</span>
                            <span class="text-[9px] font-bold text-gray-600 dark:text-gray-400">{{ value }}</span>
                        </div>
                    </div>
                </div>

                <!-- Urgency Distribution (Enhanced) -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <AlertTriangle class="w-4 h-4 text-red-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Requests by Urgency</h3>
                    </div>
                    <div class="space-y-4">
                        <div v-for="(count, label, color) in { High: urgencyCounts.High, Medium: urgencyCounts.Medium, Low: urgencyCounts.Low }"
                            :key="label">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium">{{ label }}</span>
                                <span>{{ count }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="{
                                    'bg-red-500': label === 'High',
                                    'bg-yellow-500': label === 'Medium',
                                    'bg-green-500': label === 'Low'
                                }" :style="{ width: `${(count / totalRequests) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Performance Table (Enhanced) -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                <div class="flex items-center gap-2 mb-4">
                    <Building2 class="w-4 h-4 text-purple-500" />
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Top Suppliers (by spend)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 rounded-t-xl">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Count
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Spend
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="supplier in supplierPerformance" :key="supplier.supplier_name"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                <td class="px-4 py-3 font-medium">{{ supplier.supplier_name }}</td>
                                <td class="px-4 py-3">{{ supplier.po_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold">₱{{
                                    supplier.total_amount.toLocaleString() }}</td>
                            </tr>
                            <tr v-if="supplierPerformance.length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">No supplier data available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity (Placeholder) -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <Calendar class="w-4 h-4 text-gray-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Recent Activity</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="p-1.5 bg-blue-50 rounded-lg">
                                <ClipboardList class="w-3 h-3 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Material request forwarded to PRO
                                </p>
                                <p class="text-xs text-gray-400">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="p-1.5 bg-green-50 rounded-lg">
                                <Factory class="w-3 h-3 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Order #PO-2026-012 sent to
                                    manufacturing</p>
                                <p class="text-xs text-gray-400">Yesterday</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="p-1.5 bg-amber-50 rounded-lg">
                                <DollarSign class="w-3 h-3 text-amber-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Invoice INV-2026-045 due tomorrow
                                </p>
                                <p class="text-xs text-gray-400">2 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <Zap class="w-4 h-4 text-amber-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Quick Actions</h3>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('scm.manager.operations')"
                            class="flex-1 min-w-[120px] text-center px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition">
                            Manage Material Requests
                        </Link>
                        <Link :href="route('scm.manager.payments')"
                            class="flex-1 min-w-[120px] text-center px-4 py-2 bg-amber-600 text-white rounded-xl text-sm font-bold hover:bg-amber-700 transition">
                            Review Invoices
                        </Link>
                        <Link :href="route('scm.manager.vendor')"
                            class="flex-1 min-w-[120px] text-center px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-bold hover:bg-purple-700 transition">
                            Vendor Management
                        </Link>
                        <Link :href="route('scm.manager.sales-orders')"
                            class="flex-1 min-w-[120px] text-center px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition">
                            Sales Orders
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>