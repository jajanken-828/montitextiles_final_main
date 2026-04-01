<script setup>
import Sidebar from './Sidebar.vue'
import MobileSidebar from './MobileSidebar.vue'
import { usePage, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

const page = usePage()

// User data from Inertia shared props
const user = computed(() => page.props.auth.user)

// Current URL path
const currentPath = computed(() => page.url)

/**
 * Determine the current module from the URL 
 */
const currentModule = computed(() => {
    const match = currentPath.value.match(/\/dashboard\/([^\/]+)/)
    return match ? match[1] : null
})

// Helper to check if a link is active for UI styling
const isActive = (href) => {
    return currentPath.value === href || currentPath.value.startsWith(href + '/')
}

/**
 * Safe Route Helper
 * Prevents Ziggy from throwing an uncaught error if a route is missing.
 * Returns '#' if the route doesn't exist.
 */
const safeRoute = (name) => {
    try {
        return route(name)
    } catch (e) {
        console.warn(`Ziggy route "${name}" is missing in web.php`);
        return '#'
    }
}

/**
 * Unified Module Registry
 * Updated to match the exact names found in your web.php file.
 */
const allModuleMenus = {
    hrm: [
        { label: 'Dashboard', href: safeRoute('hrm.dashboard'), id: 'dashboard' },
        { label: 'Employees', href: safeRoute('hrm.employees.index'), id: 'employee' },
        { label: 'Applications', href: safeRoute('hrm.applications.index'), id: 'application' },
        { label: 'Interviews', href: safeRoute('hrm.interview.index'), id: 'interview' },
        { label: 'Trainees', href: safeRoute('hrm.trainee.index'), id: 'trainee' },
        { label: 'Onboarding', href: safeRoute('hrm.onboarding.index'), id: 'onboarding' },
        { label: 'Archive', href: safeRoute('hrm.applications.rejected'), id: 'reject' },
        { label: 'Payroll', href: safeRoute('hrm.payroll'), id: 'payroll' },
        { label: 'Analytics', href: safeRoute('hrm.analytics'), id: 'analytics' },
        { label: 'Access Control', href: safeRoute('hrm.access.index'), id: 'access' },
    ],
    scm: [
        { label: 'Dashboard', href: safeRoute('scm.manager.dashboard'), id: 'dashboard' },
        { label: 'Operations', href: safeRoute('scm.manager.operations'), id: 'operations' },
        { label: 'Sales Orders', href: safeRoute('scm.manager.sales-orders'), id: 'sales' },
        { label: 'Vendors', href: safeRoute('scm.manager.vendor'), id: 'vendor' },
        { label: 'Payments', href: safeRoute('scm.manager.payments'), id: 'payments' },
        { label: 'Staff Assignment', href: safeRoute('scm.manager.assignment'), id: 'assignment' },
        { label: 'Close Module', href: safeRoute('scm.manager.close'), id: 'close' },
    ],
    crm: [
        { label: 'Dashboard', href: safeRoute('crm.dashboard'), id: 'dashboard' },
        { label: 'Leads & Deals', href: safeRoute('crm.lead'), id: 'lead' },
        { label: 'Customer Profiles', href: safeRoute('crm.customerprofile'), id: 'profile' },
        { label: 'Approvals', href: safeRoute('crm.approval.queue'), id: 'approval' },
        { label: 'Oversight', href: safeRoute('crm.oversight'), id: 'oversight' },
        { label: 'Strategy', href: safeRoute('crm.strategy'), id: 'strategy' },
    ],
    man: [
        { label: 'Dashboard', href: safeRoute('man.manager.dashboard'), id: 'dashboard' },
        { label: 'Production Orders', href: safeRoute('man.manager.production'), id: 'production' },
        { label: 'Rejected Items', href: safeRoute('man.manager.rejected'), id: 'reject' },
        // These will resolve to '#' until you add them to the MAN group in web.php
        { label: 'Interviews', href: safeRoute('man.interview.index'), id: 'interview' },
        { label: 'Trainees', href: safeRoute('man.trainee.index'), id: 'trainee' },
        { label: 'Access Control', href: safeRoute('man.access.index'), id: 'access' },
    ],
    inv: [
        { label: 'Dashboard', href: safeRoute('inv.manager.dashboard'), id: 'dashboard' },
        { label: 'Inventory', href: safeRoute('inv.manager.inventory'), id: 'inventory' },
        { label: 'Planning', href: safeRoute('inv.manager.production-planning'), id: 'planning' },
        { label: 'Materials', href: safeRoute('inv.manager.material'), id: 'material' },
        { label: 'Products', href: safeRoute('inv.manager.product'), id: 'product' },
    ],
    pro: [
        { label: 'Dashboard', href: safeRoute('pro.manager.dashboard'), id: 'dashboard' },
        { label: 'Quotations', href: safeRoute('pro.manager.supplier-quotations'), id: 'quotation' },
        { label: 'Requests', href: safeRoute('pro.manager.material-requests'), id: 'request' },
        { label: 'Receipts', href: safeRoute('pro.manager.receipt'), id: 'receipt' },
    ],
    eco: [
        { label: 'Dashboard', href: safeRoute('eco.manager.dashboard'), id: 'dashboard' },
        { label: 'Store', href: safeRoute('eco.manager.store'), id: 'store' },
        { label: 'Orders', href: safeRoute('eco.manager.orders'), id: 'orders' },
        { label: 'Quotations', href: safeRoute('eco.manager.quotations'), id: 'quotations' },
        { label: 'Credit', href: safeRoute('eco.manager.credit'), id: 'credit' },
        { label: 'Book', href: safeRoute('eco.manager.book'), id: 'book' },
    ],
}

/**
 * Hierarchy & RBAC Filtering Logic
 */
const submenuItems = computed(() => {
    const module = currentModule.value
    if (!module || !allModuleMenus[module]) return []

    const userRole = user.value?.role?.toUpperCase()
    const userPosition = user.value?.position?.toLowerCase()

    // 1. CEO / Secretary / GM - Full Access
    if (['CEO', 'SECRETARY', 'GENERAL MANAGER'].includes(userRole)) {
        return allModuleMenus[module]
    }

    // 2. Module Manager - Full Module Access
    if (userPosition === 'manager') {
        return allModuleMenus[module]
    }

    // 3. Staff / Supervisor - Granular Permission filter
    const moduleKey = module.toUpperCase()
    const allowedIds = user.value?.permissions?.[moduleKey] || []

    return allModuleMenus[module].filter(item => {
        return item.id === 'dashboard' || allowedIds.includes(item.id)
    })
})

const showTopNav = computed(() => submenuItems.value.length > 0)
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 transition-colors duration-300">
        <Sidebar />

        <div
            class="md:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
            <div class="px-4 py-4 flex items-center justify-between h-16">
                <div class="flex items-center">
                    <MobileSidebar />
                    <div class="ml-4">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">
                            Monti <span class="text-blue-600">Textile</span>
                        </h1>
                    </div>
                </div>
                <div
                    class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                    {{ user?.name?.charAt(0) }}
                </div>
            </div>
        </div>

        <div class="md:pl-64 flex flex-col flex-1">
            <div v-if="showTopNav"
                class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-gray-200/50 dark:border-zinc-800/50 sticky top-0 z-30 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center space-x-1 overflow-x-auto py-2 scrollbar-hide">
                        <Link v-for="item in submenuItems" :key="item.id" :href="item.href" :class="[
                            isActive(item.href)
                                ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400 -mb-[1px]'
                                : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800/50',
                            'px-4 py-2 rounded-t-lg text-sm font-bold transition-all duration-200 whitespace-nowrap'
                        ]">
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
            </div>

            <main class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="animate-in fade-in duration-500">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>