<script setup>
import { usePage, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import {
    LayoutDashboard,
    BarChart3,
    Package,
    LogOut,
    ChevronRight,
    CreditCard,
    UserPlus,
    Spool,
    ClipboardList,
    ChartNoAxesCombined,
    ShoppingBasket,
    HandCoins,
    FileUser,
    DoorOpen,
    BicepsFlexed,
    Truck,
    Wallet,
    Factory,
    Book,
    Boxes,
    ShoppingCart,
    Warehouse,
    Globe,
    Clock,
    CalendarDays,
    History,
    Users,
    UserPen,
    Settings,
    Receipt,
    HelpCircle,
    ShieldCheck,
    Building2,
    RefreshCw,
    ClipboardCheck,
    FileText,
    Send,
    ShoppingBag,
    User,
    TrendingUp,
    XCircle,
    Eye,
    Award,
    Archive,
    CalendarCheck,
    UserX,
    AlertCircle,
    UserCog,
    MessageSquare,
    Navigation,
    MapPin,
    Briefcase,
    Plus,
    ArrowLeft,
    Paperclip,
    Loader2,
    Info,
    Phone,
    Mail,
    Calendar,
    Tag,
    Weight,
    Ruler,
    Layers,
    ArrowRight,
    Zap,
    Activity,
    DollarSign,
    Users as UsersIcon
} from 'lucide-vue-next'

const page = usePage()

const user = computed(() => page.props.auth.user)
const client = computed(() => page.props.auth.client)
const supplier = computed(() => page.props.auth.supplier || (page.props.auth.user?.business_name ? page.props.auth.user : null))
const currentUrl = computed(() => page.url)

// ─── PERSISTENCE HELPERS ───────────────────────────────────────────────────────
const STORAGE_PREFIX = 'sidebar_'

const getStored = (key, fallback = false) => {
    try {
        const raw = sessionStorage.getItem(STORAGE_PREFIX + key)
        return raw !== null ? JSON.parse(raw) : fallback
    } catch {
        return fallback
    }
}

const setStored = (key, value) => {
    try {
        sessionStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(value))
    } catch { }
}

// ─── DROPDOWN STATES ──────────────────────────────────────────────────────────
const isWorkforceSubOpen = ref(getStored('workforce'))
const isHrmOpen = ref(getStored('hrm'))
const isCrmOpen = ref(getStored('crm'))
const isEcoOpen = ref(getStored('eco'))
const isScmOpen = ref(getStored('scm'))
const isWarehouseOpen = ref(getStored('warehouse'))
const isInventoryOpen = ref(getStored('inventory'))
const isProOpen = ref(getStored('pro'))
const isManOpen = ref(getStored('man'))
const isOrdOpen = ref(getStored('ord'))

const toggleWorkforceSub = () => { isWorkforceSubOpen.value = !isWorkforceSubOpen.value; setStored('workforce', isWorkforceSubOpen.value) }
const toggleHrm = () => { isHrmOpen.value = !isHrmOpen.value; setStored('hrm', isHrmOpen.value) }
const toggleCrm = () => { isCrmOpen.value = !isCrmOpen.value; setStored('crm', isCrmOpen.value) }
const toggleEco = () => { isEcoOpen.value = !isEcoOpen.value; setStored('eco', isEcoOpen.value) }
const toggleScm = () => { isScmOpen.value = !isScmOpen.value; setStored('scm', isScmOpen.value) }
const toggleWarehouse = () => { isWarehouseOpen.value = !isWarehouseOpen.value; setStored('warehouse', isWarehouseOpen.value) }
const toggleInventory = () => { isInventoryOpen.value = !isInventoryOpen.value; setStored('inventory', isInventoryOpen.value) }
const togglePro = () => { isProOpen.value = !isProOpen.value; setStored('pro', isProOpen.value) }
const toggleMan = () => { isManOpen.value = !isManOpen.value; setStored('man', isManOpen.value) }
const toggleOrd = () => { isOrdOpen.value = !isOrdOpen.value; setStored('ord', isOrdOpen.value) }

// ─── SCROLL POSITION PERSISTENCE ──────────────────────────────────────────────
const sidebarScrollRef = ref(null)

const onSidebarScroll = () => {
    if (sidebarScrollRef.value) {
        setStored('scrollTop', sidebarScrollRef.value.scrollTop)
    }
}

onMounted(() => {
    if (sidebarScrollRef.value) {
        const savedScrollTop = getStored('scrollTop', 0)
        sidebarScrollRef.value.scrollTop = savedScrollTop
        sidebarScrollRef.value.addEventListener('scroll', onSidebarScroll, { passive: true })
    }
})

onBeforeUnmount(() => {
    if (sidebarScrollRef.value) {
        sidebarScrollRef.value.removeEventListener('scroll', onSidebarScroll)
    }
})

// ─── AUTH & PERMISSIONS ───────────────────────────────────────────────────────
const showLogoutModal = ref(false)

const hasHrmPermission = (pageName) => {
    const perms = user.value?.permissions?.HRM
    return perms ? perms.includes(pageName) : false
}

const hasCrmPermission = (pageName) => {
    const perms = user.value?.crmPagePermissions
    return perms ? perms.includes(pageName) : false
}

const hasWorkforceAccess = computed(() => {
    if (user.value?.role === 'CEO') return true
    if (user.value?.position === 'secretary') return true
    const perms = user.value?.workforce_permissions
    return perms && perms.length > 0
})

// Warehouse module access
const hasWarehouseAccess = computed(() => {
    if (user.value?.role === 'CEO') return true
    if (user.value?.position === 'secretary') return true
    if (user.value?.position === 'general_manager') return true
    return user.value?.has_warehouse_access === true
})

// Inventory module access
const hasInventoryAccess = computed(() => {
    if (user.value?.role === 'CEO') return true
    if (user.value?.position === 'secretary') return true
    if (user.value?.position === 'general_manager') return true
    return user.value?.has_inventory_access === true
})

// Order Management module access
const hasOrdAccess = computed(() => {
    if (user.value?.role === 'CEO') return true
    return user.value?.has_ord_access === true
})

const isEmployeePortal = computed(() => currentUrl.value.startsWith('/dashboard/employee-ui'))
const isClient = computed(() => !!client.value)
const isSupplier = computed(() => !!supplier.value || currentUrl.value.startsWith('/supplier'))

// ─── NAV ITEMS ────────────────────────────────────────────────────────────────
const navItems = computed(() => {
    if (isSupplier.value) {
        return [
            { label: 'Vendor Hub', href: route('supplier.dashboard'), icon: LayoutDashboard },
            { label: 'Purchase Orders', href: route('supplier.orders'), icon: ShoppingCart },
        ]
    }

    if (isClient.value) {
        return [
            { label: 'Dashboard', href: route('client.dashboard'), icon: LayoutDashboard },
            { label: 'Products', href: route('client.products'), icon: ShoppingBag },
            { label: 'Conversations', href: route('client.conversations'), icon: MessageSquare },
            { label: 'Orders', href: route('client.orders'), icon: ShoppingCart },
            { label: 'Invoices', href: route('client.invoices'), icon: Receipt },
            { label: 'Receiving', href: route('client.receiving'), icon: Truck },
            { label: 'Profile', href: route('client.profile.edit'), icon: User },
            { label: 'Support', href: route('client.support'), icon: HelpCircle },
        ]
    }

    if (isEmployeePortal.value) {
        return [
            { label: 'Employee Dashboard', href: route('employee.ui.dashboard'), icon: Clock },
            { label: 'My Attendance', href: route('employee.ui.clock'), icon: CalendarDays },
            { label: 'Leave Request', href: route('employee.ui.leave'), icon: History },
            { label: 'Payslip', href: route('employee.ui.payslip'), icon: HandCoins },
        ]
    }

    const items = [{ label: 'Main Dashboard', href: route('dashboard'), icon: LayoutDashboard }]
    const userRole = user.value?.role?.toUpperCase()
    const userPosition = user.value?.position?.toLowerCase()

    // --- Workforce Management ---
    if (hasWorkforceAccess.value) {
        const workforceChildren = [
            { label: 'Dashboard', href: route('workforce.dashboard'), icon: LayoutDashboard },
            { label: 'Scheduler', href: route('workforce.scheduler'), icon: CalendarCheck },
            { label: 'Leave Requests', href: route('workforce.leave'), icon: FileText },
            { label: 'Absence Tracking', href: route('workforce.absent'), icon: UserX },
        ];
        if (userRole === 'CEO') {
            workforceChildren.push({ label: 'Access Control', href: route('workforce.access'), icon: UserCog });
        }
        items.push({
            label: 'Workforce Management',
            icon: CalendarDays,
            isDropdown: true,
            isOpen: isWorkforceSubOpen.value,
            toggle: toggleWorkforceSub,
            children: workforceChildren
        });
    }

    // --- HRM Logic ---
    if (userRole === 'HRM' || userRole === 'CEO') {
        const hrmChildren = [
            { label: 'Dashboard', href: route('hrm.dashboard'), icon: LayoutDashboard },
            { label: 'Employees', href: route('hrm.employees.index'), icon: Users },
            { label: 'Applications', href: route('hrm.applications.index'), icon: FileText },
            { label: 'Interviews', href: route('hrm.interview.index'), icon: Eye },
            { label: 'Trainees', href: route('hrm.trainee.index'), icon: Award },
            { label: 'Onboarding', href: route('hrm.onboarding.index'), icon: UserPlus },
            { label: 'Archive', href: route('hrm.applications.rejected'), icon: Archive },
            { label: 'Payroll', href: route('hrm.payroll'), icon: HandCoins },
            { label: 'Analytics', href: route('hrm.analytics'), icon: ChartNoAxesCombined },
            { label: 'Access Control', href: route('hrm.access.index'), icon: ShieldCheck },
        ];
        if (userRole === 'CEO') {
            items.push({ label: 'Human Resource', icon: Users, isDropdown: true, isOpen: isHrmOpen.value, toggle: toggleHrm, children: hrmChildren });
        } else {
            items.push(...hrmChildren.filter(c => userPosition === 'manager' || hasHrmPermission(c.label.toLowerCase())));
        }
    }

    // --- CRM Logic ---
    if (userRole === 'CRM' || userRole === 'CEO') {
        const allCrmPages = [
            { label: 'Dashboard', href: route('crm.dashboard'), icon: LayoutDashboard, id: 'dashboard' },
            { label: 'Leads', href: route('crm.lead'), icon: FileUser, id: 'lead' },
            { label: 'Interviews', href: route('crm.interview.index'), icon: Eye, id: 'interview' },
            { label: 'Trainees', href: route('crm.trainee.index'), icon: Award, id: 'trainee' },
            { label: 'Approvals', href: route('crm.approval.index'), icon: ClipboardCheck, id: 'approval' },
            { label: 'Customer Profiles', href: route('crm.customerprofile.index'), icon: Users, id: 'customerprofile' },
            { label: 'Investigation', href: route('crm.investigation.index'), icon: AlertCircle, id: 'investigation' },
            { label: 'Access Control', href: route('crm.access.index'), icon: ShieldCheck, id: 'access' },
        ];

        if (userRole === 'CEO') {
            items.push({
                label: 'Customer Relationship',
                icon: UserPen,
                isDropdown: true,
                isOpen: isCrmOpen.value,
                toggle: toggleCrm,
                children: allCrmPages
            });
        } else {
            const visiblePages = allCrmPages.filter(page => {
                if (userPosition === 'manager') return true;
                return hasCrmPermission(page.id);
            });
            items.push(...visiblePages);
        }
    }

    // --- ECO Logic ---
    if (userRole === 'ECO' || userRole === 'CEO') {
        const ecoChildren = [
            { label: 'Dashboard', href: route('eco.dashboard'), icon: LayoutDashboard },
            { label: 'Store', href: route('eco.store'), icon: ShoppingBag },
            { label: 'Inquiries', href: route('eco.inquiries'), icon: MessageSquare },
            { label: 'Suppliers', href: route('eco.suppliers'), icon: UsersIcon },
            { label: 'Credit', href: route('eco.credit'), icon: CreditCard },
            { label: 'Push Center', href: route('eco.push'), icon: Send },
            { label: 'Access Control', href: route('eco.access'), icon: ShieldCheck },
        ];
        if (userRole === 'CEO') {
            items.push({ label: 'E-Commerce', icon: ShoppingBag, isDropdown: true, isOpen: isEcoOpen.value, toggle: toggleEco, children: ecoChildren });
        } else {
            items.push(...ecoChildren);
        }
    }

    // --- SCM Logic (Restructured) ---
    if (userRole === 'SCM' || userRole === 'CEO') {
        const scmChildren = [
            { label: 'Sales Orders', href: route('scm.sales-orders'), icon: ShoppingCart },
            { label: 'Procurement Orders', href: route('scm.procurement-orders'), icon: ClipboardList },
            { label: 'Vendors', href: route('scm.vendors'), icon: Building2 },
        ];
        // Only CEO sees Access Control page
        if (userRole === 'CEO') {
            scmChildren.push({ label: 'Access Control', href: route('scm.access.index'), icon: ShieldCheck });
        }
        items.push({
            label: 'Supply Chain',
            icon: Truck,
            isDropdown: true,
            isOpen: isScmOpen.value,
            toggle: toggleScm,
            children: scmChildren
        });
    }

    // --- Warehouse Management Module ---
    if (hasWarehouseAccess.value) {
        const warehouseChildren = [
            { label: 'All Warehouses', href: route('warehouse.index'), icon: Warehouse },
            { label: 'Receiving', href: route('warehouse.receiving'), icon: Truck },
            { label: 'Packages', href: route('warehouse.packages'), icon: Package },
            { label: 'Rejects', href: route('warehouse.rejects'), icon: XCircle },
        ];
        if (userRole === 'CEO') {
            warehouseChildren.push({ label: 'Access Control', href: route('warehouse.access'), icon: ShieldCheck });
        }
        items.push({
            label: 'Warehouse',
            icon: Warehouse,
            isDropdown: true,
            isOpen: isWarehouseOpen.value,
            toggle: toggleWarehouse,
            children: warehouseChildren
        });
    }

    // --- Inventory Management Module ---
    if (hasInventoryAccess.value) {
        const inventoryChildren = [
            { label: 'Dashboard', href: route('inv.dashboard'), icon: LayoutDashboard },
            { label: 'Materials', href: route('inv.materials'), icon: Spool },
            { label: 'Products', href: route('inv.products'), icon: Package },
            { label: 'Bill of Materials', href: route('inv.bom'), icon: Layers },
            { label: 'Stock Checker', href: route('inv.checker'), icon: AlertCircle },
        ];
        if (userRole === 'CEO') {
            inventoryChildren.push({ label: 'Access Control', href: route('inv.access'), icon: ShieldCheck });
        }
        items.push({
            label: 'Inventory',
            icon: Boxes,
            isDropdown: true,
            isOpen: isInventoryOpen.value,
            toggle: toggleInventory,
            children: inventoryChildren
        });
    }

    // --- PRO Logic ---
    if (userRole === 'PRO' || userRole === 'CEO') {
        const proChildren = [
            { label: 'Dashboard', href: route('pro.manager.dashboard'), icon: LayoutDashboard },
            { label: 'Quotations', href: route('pro.manager.supplier-quotations'), icon: FileText },
            { label: 'Requests', href: route('pro.manager.material-requests'), icon: ClipboardList },
            { label: 'Receipts', href: route('pro.manager.receipt'), icon: Send },
        ];
        if (userRole === 'CEO') {
            items.push({ label: 'Procurement', icon: ShoppingCart, isDropdown: true, isOpen: isProOpen.value, toggle: togglePro, children: proChildren });
        } else {
            items.push(...proChildren);
        }
    }

    // --- MAN Logic ---
    if (userRole === 'MAN' || userRole === 'CEO') {
        const manChildren = [
            { label: 'Dashboard', href: route('man.manager.dashboard'), icon: Factory },
            { label: 'Production Orders', href: route('man.manager.production'), icon: ClipboardList },
            { label: 'Rejected Items', href: route('man.manager.rejected'), icon: XCircle },
            { label: 'Interviews', href: route('man.interview.index'), icon: Eye },
            { label: 'Trainees', href: route('man.trainee.index'), icon: Award },
            { label: 'Access Control', href: route('man.access.index'), icon: ShieldCheck },
        ];
        if (userRole === 'CEO') {
            items.push({ label: 'Manufacturing', icon: Factory, isDropdown: true, isOpen: isManOpen.value, toggle: toggleMan, children: manChildren });
        } else {
            items.push(...manChildren);
        }
    }

    // --- Order Management (ORD) Logic ---
    if (hasOrdAccess.value) {
        const ordChildren = [
            { label: 'Orders', href: route('ord.orders'), icon: ClipboardList },
            { label: 'Productions', href: route('ord.productions'), icon: Factory },
            { label: 'Delivery', href: route('ord.delivery'), icon: Truck },
        ];
        if (userRole === 'CEO') {
            ordChildren.push({ label: 'Access Control', href: route('ord.access.index'), icon: ShieldCheck });
        }
        items.push({
            label: 'Order Management',
            icon: ClipboardCheck,
            isDropdown: true,
            isOpen: isOrdOpen.value,
            toggle: toggleOrd,
            children: ordChildren
        });
    }

    return items
})

// ─── HELPERS ──────────────────────────────────────────────────────────────────
const isActive = (href) => {
    if (href === '#') return false
    return currentUrl.value === href || currentUrl.value.startsWith(href + '/')
}

const displayName = computed(() => {
    if (isSupplier.value) return supplier.value?.representative_name
    if (isClient.value) return client.value?.company_name
    return user.value?.name
})
const displayInitial = computed(() => displayName.value?.charAt(0) ?? '?')
const userPhotoUrl = computed(() => {
    if (user.value?.profile_photo_path) return `/storage/${user.value.profile_photo_path}`
    if (supplier.value?.profile_photo_path) return `/storage/${supplier.value.profile_photo_path}`
    if (client.value?.profile_photo_path) return `/storage/${client.value.profile_photo_path}`
    return null
})
const displayDepartment = computed(() => isSupplier.value ? 'Supplier' : (isClient.value ? client.value?.business_type : user.value?.role))
const displayPosition = computed(() => isSupplier.value ? (supplier.value?.business_name ?? 'Vendor') : (isClient.value ? 'Partner' : user.value?.position))
const sidebarLabel = computed(() => isSupplier.value ? 'Vendor' : (isClient.value ? 'Partner' : (isEmployeePortal.value ? 'Employee' : 'System')))
const logoutRoute = computed(() => isClient.value ? route('client.logout') : (isSupplier.value ? route('supplier.logout') : route('logout')))
</script>

<template>
    <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 z-40 transition-all duration-300 h-screen">
        <div
            class="flex flex-col h-full bg-white/70 dark:bg-gray-950/70 backdrop-blur-xl border-r border-gray-200/40 dark:border-gray-800/40 shadow-2xl">

            <div class="flex items-center h-20 flex-shrink-0 px-4 pt-2">
                <div class="relative w-full">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 dark:from-blue-400/10 dark:to-indigo-400/10 rounded-2xl blur-xl">
                    </div>
                    <div
                        class="relative flex items-center gap-2.5 p-2 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-100/50 dark:border-gray-800/50 w-full">
                        <div :class="isSupplier ? 'bg-emerald-600 shadow-emerald-500/30' : 'bg-blue-600 shadow-blue-500/30'"
                            class="h-9 w-9 flex-shrink-0 rounded-xl flex items-center justify-center shadow-lg">
                            <img src="/images/applogo.png" alt="Logo"
                                class="h-6 w-6 object-contain brightness-0 invert" />
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <h2
                                class="text-[13px] font-black text-gray-900 dark:text-white leading-tight tracking-tight uppercase">
                                Monti <span :class="isSupplier ? 'text-emerald-600' : 'text-blue-600'">Textile</span>
                            </h2>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest truncate">{{
                                sidebarLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div ref="sidebarScrollRef" class="flex-1 overflow-y-auto px-3 py-4 custom-scrollbar">
                <div class="mb-3 px-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.15em]">Main Menu</p>
                </div>
                <nav class="space-y-1">
                    <template v-for="item in navItems" :key="item.label">

                        <div v-if="item.isDropdown" class="space-y-1">
                            <button @click="item.toggle"
                                :class="[item.isOpen ? 'text-blue-600 bg-white/60 dark:bg-gray-900/60 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-900/40']"
                                class="group w-full flex items-center justify-between px-3 py-2.5 text-[13px] font-bold rounded-xl transition-all duration-300 backdrop-blur-sm">
                                <div class="flex items-center">
                                    <div :class="[item.isOpen ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600' : 'text-gray-400']"
                                        class="p-1.5 rounded-lg mr-2.5 transition-colors duration-300">
                                        <component :is="item.icon" class="h-4.5 w-4.5" />
                                    </div>
                                    <span class="truncate tracking-tight">{{ item.label }}</span>
                                </div>
                                <ChevronRight
                                    :class="['h-3.5 w-3.5 transition-transform duration-300', item.isOpen ? 'rotate-90' : 'text-gray-400']" />
                            </button>

                            <div v-show="item.isOpen" class="pl-10 space-y-1 mt-1 transition-all">
                                <Link v-for="subItem in item.children" :key="subItem.label" :href="subItem.href"
                                    preserve-scroll preserve-state
                                    :class="[isActive(subItem.href) ? 'text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white']"
                                    class="flex items-center py-2 text-[12px] font-bold transition-colors">
                                    <component :is="subItem.icon" class="h-3.5 w-3.5 mr-2.5" />
                                    {{ subItem.label }}
                                </Link>
                            </div>
                        </div>

                        <Link v-else :href="item.href" preserve-scroll preserve-state
                            :class="[isActive(item.href) ? (isSupplier ? 'bg-emerald-50/80 dark:bg-emerald-900/30 text-emerald-600 shadow-sm ring-1 ring-emerald-500/20' : 'bg-blue-50/80 dark:bg-blue-900/30 text-blue-600 shadow-sm ring-1 ring-blue-500/20') : 'text-gray-500 dark:text-gray-400 hover:bg-white/40 dark:hover:bg-gray-900/40 hover:text-gray-900 dark:hover:text-white']"
                            class="group relative flex items-center justify-between px-3 py-2.5 text-[13px] font-bold rounded-xl transition-all duration-300 backdrop-blur-sm">
                            <div v-if="isActive(item.href)" :class="isSupplier ? 'bg-emerald-500' : 'bg-blue-500'"
                                class="absolute left-0 top-1/4 bottom-1/4 w-0.5 rounded-r-full"></div>
                            <div class="flex items-center relative z-10">
                                <div :class="[isActive(item.href) ? (isSupplier ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-600') : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300']"
                                    class="p-1.5 rounded-lg transition-colors duration-300 mr-2.5">
                                    <component :is="item.icon" class="h-4.5 w-4.5 flex-shrink-0" />
                                </div>
                                <span class="truncate tracking-tight">{{ item.label }}</span>
                            </div>
                            <ChevronRight v-if="isActive(item.href)"
                                :class="isSupplier ? 'text-emerald-600/40' : 'text-blue-600/40'" class="h-3.5 w-3.5" />
                        </Link>

                    </template>
                </nav>
            </div>

            <div class="p-3 mt-auto flex-shrink-0 border-t border-gray-100/20 dark:border-gray-800/50">
                <div class="relative group">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 dark:from-blue-400/10 dark:to-indigo-400/10 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div
                        class="relative bg-white/60 dark:bg-gray-900/60 backdrop-blur-md rounded-2xl p-2.5 border border-gray-100/50 dark:border-gray-800/50 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center gap-2.5">
                            <div class="relative">
                                <img v-if="userPhotoUrl" :src="userPhotoUrl"
                                    class="h-9 w-9 rounded-xl object-cover shadow-lg"
                                    :class="isSupplier ? 'shadow-emerald-500/30' : 'shadow-blue-500/30'" />
                                <div v-else
                                    :class="isSupplier ? 'from-emerald-600 to-teal-700 shadow-emerald-500/30' : 'from-blue-600 to-indigo-700 shadow-blue-500/30'"
                                    class="h-9 w-9 rounded-xl bg-gradient-to-br flex items-center justify-center text-white text-xs font-black shadow-lg uppercase">
                                    {{ displayInitial }}</div>
                                <div
                                    class="absolute -bottom-0.5 -right-0.5 h-3 w-3 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full">
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-[11px] font-black text-gray-900 dark:text-white truncate uppercase tracking-tighter">
                                    {{ displayName }}</p>
                                <div class="flex items-center gap-1 mb-0.5">
                                    <Building2 class="h-2.5 w-2.5 text-gray-400" />
                                    <span :class="isSupplier ? 'text-emerald-600' : 'text-blue-600'"
                                        class="text-[8px] font-black uppercase truncate">{{ displayDepartment }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <ShieldCheck :class="isSupplier ? 'text-emerald-500' : 'text-blue-500'"
                                        class="h-2.5 w-2.5" />
                                    <span class="text-[8px] font-black text-gray-400 uppercase truncate">{{
                                        displayPosition
                                        }}</span>
                                </div>
                            </div>
                            <button @click="showLogoutModal = true"
                                class="p-2 rounded-xl bg-gray-100/80 dark:bg-gray-800/80 text-gray-400 hover:text-red-500 hover:bg-red-50/80 dark:hover:bg-red-900/20 transition-all duration-300 backdrop-blur-sm">
                                <LogOut class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <transition name="modal-fade">
                <div v-if="showLogoutModal"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
                    @click.self="showLogoutModal = false">
                    <div
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 w-full max-w-sm p-6 flex flex-col items-center text-center transform transition-all duration-300 scale-100">
                        <div
                            class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                            <LogOut class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Sign Out</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 px-2">Are you sure you want to sign out
                            of your
                            account?</p>
                        <div class="flex gap-3 w-full">
                            <button @click="showLogoutModal = false"
                                class="flex-1 py-3 text-sm font-bold rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">Cancel</button>
                            <Link :href="logoutRoute" method="post" as="button"
                                class="flex-1 py-3 text-sm font-bold rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-lg shadow-red-500/20">
                                Confirm Sign Out</Link>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>
    </aside>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.2);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.4);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-active .bg-white,
.modal-fade-leave-active .bg-white {
    transition: transform 0.2s ease;
}

.modal-fade-enter-from .bg-white,
.modal-fade-leave-to .bg-white {
    transform: scale(0.95) translateY(10px);
}
</style>