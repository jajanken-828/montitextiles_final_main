<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserCheck, Briefcase, Users, Calendar, CheckCircle, XCircle, Search,
    TrendingUp, ArrowUp, ArrowDown, PieChart, BarChart3, Clock, MessageSquare,
    Phone, Mail, MoreHorizontal, PlusCircle, Zap
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    businessPartners: Array,
    leads: Array,
    pendingRegistrations: Array,
    upcomingInterviews: Array,
    pendingApprovals: Array,
    userRole: String,
    userPosition: String,
});

// ─────────────────────────────────────────────
// Computed for charts
// ─────────────────────────────────────────────
const conversionRate = computed(() => {
    if (!props.stats.totalLeads) return 0;
    return ((props.stats.leadsWon / props.stats.totalLeads) * 100).toFixed(1);
});

const leadStatusCounts = computed(() => {
    const counts = { Inquiry: 0, Negotiation: 0, 'Approval Sent': 0, 'Closed-Won': 0, Lost: 0 };
    props.leads.forEach(lead => {
        if (counts[lead.status] !== undefined) counts[lead.status]++;
    });
    return counts;
});

const maxStatusCount = computed(() => Math.max(...Object.values(leadStatusCounts.value)));

// Tab state
const activeTab = ref('partners');

// ─────────────────────────────────────────────
// Actions (unchanged)
// ─────────────────────────────────────────────
const approveClient = (clientId) => {
    router.patch(route('clients.status.update', clientId), { status: 'active' });
};
const rejectClient = (clientId) => {
    router.patch(route('clients.status.update', clientId), { status: 'rejected' });
};
const togglePartnerStatus = (client) => {
    const newStatus = client.status === 'active' ? 'suspended' : 'active';
    router.patch(route('clients.status.update', client.id), { status: newStatus });
};
const terminateLead = (leadId) => {
    if (confirm('Are you sure you want to terminate this lead? It will be archived.')) {
        router.patch(route('crm.lead.status', leadId), { status: 'Archived' });
    }
};
const formatDateTime = (date) => {
    return new Date(date).toLocaleString();
};

// Helper for greeting
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
};
</script>

<template>
    <AuthenticatedLayout title="CRM Dashboard">

        <Head title="CRM Dashboard" />

        <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6 sm:space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        Customer Relationship Management <span class="text-blue-600">Dashboard</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ getGreeting() }}, {{
                        $page.props.auth.user?.name }}</p>
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
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Leads</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                stats.totalLeads }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl group-hover:bg-blue-100 transition">
                            <Briefcase class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        <TrendingUp class="w-3 h-3" /> +12% from last month
                    </div>
                </div>

                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Leads Won</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                stats.leadsWon }}</p>
                        </div>
                        <div
                            class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl group-hover:bg-green-100 transition">
                            <UserCheck class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-green-600 dark:text-green-400">{{ conversionRate }}% conversion rate
                    </div>
                </div>

                <div
                    class="group bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Business Partners</p>
                            <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">{{
                                stats.totalPartners }}</p>
                        </div>
                        <div
                            class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl group-hover:bg-purple-100 transition">
                            <Users class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-purple-600 dark:text-purple-400">Active since Q1 2026</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Lead Conversion Progress -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <PieChart class="w-4 h-4 text-blue-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Lead Conversion Rate</h3>
                    </div>
                    <div class="relative pt-1">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>Conversion</span>
                            <span>{{ conversionRate }}%</span>
                        </div>
                        <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-1000"
                                :style="{ width: `${conversionRate}%` }"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span>Leads: {{ stats.totalLeads }}</span>
                            <span>Won: {{ stats.leadsWon }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lead Status Distribution (Bar Chart) -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2 mb-4">
                        <BarChart3 class="w-4 h-4 text-emerald-500" />
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Lead Status Distribution</h3>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(count, status) in leadStatusCounts" :key="status">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium">{{ status }}</span>
                                <span>{{ count }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="{
                                    'bg-blue-500': status === 'Inquiry',
                                    'bg-yellow-500': status === 'Negotiation',
                                    'bg-purple-500': status === 'Approval Sent',
                                    'bg-green-500': status === 'Closed-Won',
                                    'bg-red-500': status === 'Lost',
                                }" :style="{ width: `${(count / (maxStatusCount || 1)) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs (Horizontal scroll on mobile) -->
            <div class="border-b border-gray-200 dark:border-zinc-800 overflow-x-auto no-scrollbar">
                <nav class="flex space-x-4 sm:space-x-6 min-w-max">
                    <button @click="activeTab = 'partners'"
                        :class="activeTab === 'partners' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm uppercase tracking-wider transition-colors">
                        Business Partners
                    </button>
                    <button @click="activeTab = 'leads'"
                        :class="activeTab === 'leads' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm uppercase tracking-wider transition-colors">
                        Leads
                    </button>
                    <button @click="activeTab = 'pending'"
                        :class="activeTab === 'pending' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm uppercase tracking-wider transition-colors">
                        Pending Registrations
                    </button>
                    <button @click="activeTab = 'calendar'"
                        :class="activeTab === 'calendar' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm uppercase tracking-wider flex items-center gap-1">
                        <Calendar class="w-4 h-4" /> Calendar
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <!-- Business Partners Tab -->
            <div v-if="activeTab === 'partners'">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div
                        class="p-4 border-b border-gray-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h3 class="text-sm font-black uppercase">Official Business Partners</h3>
                        <div class="relative w-full sm:w-64">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input type="text" placeholder="Search by name or email..."
                                class="pl-9 pr-4 py-2 w-full border border-gray-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                <tr v-for="partner in businessPartners" :key="partner.id"
                                    class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ partner.company_name
                                            }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-500 dark:text-gray-400">{{ partner.contact_person }}<br>{{
                                            partner.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="partner.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ partner.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button @click="togglePartnerStatus(partner)"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                                            {{ partner.status === 'active' ? 'Suspend' : 'Reactivate' }}
                                        </button>
                                        <Link :href="route('crm.customerprofile', partner.id)"
                                            class="ml-4 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white text-sm">
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="businessPartners.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No business partners
                                        found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Leads Tab -->
            <div v-if="activeTab === 'leads'">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center">
                        <h3 class="text-sm font-black uppercase">All Leads</h3>
                        <Link :href="route('crm.lead')"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                            Manage Pipeline
                            <ArrowUp class="w-3 h-3 rotate-45" />
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                <tr v-for="lead in leads" :key="lead.id"
                                    class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ lead.company_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-500 dark:text-gray-400">{{ lead.contact_person }}<br>{{
                                            lead.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': lead.status === 'Inquiry',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': lead.status === 'Negotiation',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': lead.status === 'Approval Sent',
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': lead.status === 'Closed-Won',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': lead.status === 'Lost',
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ lead.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button @click="terminateLead(lead.id)"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                                            Terminate
                                        </button>
                                        <Link :href="route('crm.lead')"
                                            class="ml-4 text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="leads.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No leads found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Registrations Tab -->
            <div v-if="activeTab === 'pending'">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="text-sm font-black uppercase">Pending Business Registrations</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TIN</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                <tr v-for="reg in pendingRegistrations" :key="reg.id"
                                    class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ reg.company_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-500 dark:text-gray-400">{{ reg.contact_person }}<br>{{
                                            reg.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-gray-500 dark:text-gray-400">{{ reg.tin_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button @click="approveClient(reg.id)"
                                            class="text-green-600 hover:text-green-800 dark:text-green-400 text-sm font-medium mr-4">Approve</button>
                                        <button @click="rejectClient(reg.id)"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">Reject</button>
                                    </td>
                                </tr>
                                <tr v-if="pendingRegistrations.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No pending
                                        registrations.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Calendar Tab -->
            <div v-if="activeTab === 'calendar'">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-5">
                    <h3 class="text-sm font-black uppercase mb-4 flex items-center gap-2">
                        <Calendar class="w-4 h-4" /> Upcoming Interviews
                    </h3>
                    <div class="space-y-4">
                        <div v-for="interview in upcomingInterviews" :key="interview.id"
                            class="border-b border-gray-100 dark:border-zinc-800 pb-4 last:border-0 last:pb-0">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{
                                        interview.lead.company_name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{
                                        formatDateTime(interview.scheduled_at) }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500">Location: {{ interview.location
                                        || 'TBD' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500">Notes: {{ interview.notes ||
                                        'Nonotes' }}</p>
                                </div>
                                <Link :href="route('crm.lead')"
                                    class="text-blue-600 hover:text-blue-700 text-sm font-medium self-start">View Lead
                                </Link>
                            </div>
                        </div>
                        <div v-if="upcomingInterviews.length === 0"
                            class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No upcoming interviews scheduled.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Queue (only for managers) -->
            <div v-if="userPosition === 'manager' && pendingApprovals.length > 0"
                class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-2xl p-4 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase text-amber-800 dark:text-amber-400">Pending Approvals</h3>
                    <p class="text-xs text-amber-700 dark:text-amber-500">{{ pendingApprovals.length }} items awaiting
                        your review</p>
                </div>
                <Link :href="route('crm.approval.queue')"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                    Review Queue
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Hide scrollbar for horizontal tab scrolling */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>