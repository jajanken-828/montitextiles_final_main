<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Users, UserCheck, Eye, Edit, ShieldOff, ShieldCheck, History, X, Search, Building2,
    CheckCircle, XCircle, UserMinus, UserPlus, Calendar, Mail, Briefcase, MoreHorizontal,
    ArrowUpCircle, RotateCcw
} from 'lucide-vue-next';

const props = defineProps({
    employees: {
        type: Array,
        default: () => []
    }
});

// Toast notification
const showToast = ref(false);
const toastMessage = ref('');
const triggerToast = (msg) => {
    toastMessage.value = msg;
    showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 4000);
};

// Flash messages from server
const page = usePage();
if (page.props.flash?.message) {
    triggerToast(page.props.flash.message);
}
if (page.props.flash?.error) {
    triggerToast(page.props.flash.error);
}

// RANK HIERARCHY LOGIC
const getRank = (role, position) => {
    if (role === 'CEO') return 60;
    const pos = (position || '').toLowerCase();
    if (pos === 'secretary') return 50;
    if (pos === 'general_manager') return 40;
    if (pos === 'manager') return 30;
    if (pos === 'supervisor') return 20;
    if (pos === 'staff') return 10;
    return 0;
};

const currentUserRank = computed(() => {
    return getRank(page.props.auth.user.role, page.props.auth.user.position);
});

// Checks if the logged-in user outranks the target employee
const canManage = (emp) => {
    return currentUserRank.value > getRank(emp.role, emp.position);
};

// Filters
const searchQuery = ref('');
const activeDept = ref('ALL');
const showDeactivated = ref(false);

const departments = ['ALL', 'HRM', 'SCM', 'FIN', 'MAN', 'INV', 'ORD', 'WAR', 'CRM', 'ECO', 'PRO', 'PROJ', 'IT'];

// Check if employee is active
const isActive = (emp) => {
    return emp.is_active === 1 || emp.is_active === true || emp.status === 'Active';
};

// Filter employees
const filteredEmployees = computed(() => {
    let list = props.employees;
    list = list.filter(emp => isActive(emp) !== showDeactivated.value);
    if (activeDept.value !== 'ALL') {
        list = list.filter(emp => emp.role === activeDept.value);
    }
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(emp =>
            emp.name.toLowerCase().includes(q) ||
            emp.email.toLowerCase().includes(q) ||
            emp.role.toLowerCase().includes(q)
        );
    }
    return list;
});

// Separate managers and staff (Supervisors are grouped with staff)
const managers = computed(() => filteredEmployees.value.filter(emp => ['manager', 'general_manager', 'secretary'].includes(emp.position.toLowerCase())));
const staff = computed(() => filteredEmployees.value.filter(emp => !['manager', 'general_manager', 'secretary'].includes(emp.position.toLowerCase())));

// Modals
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isManageModalOpen = ref(false);
const isHistoryModalOpen = ref(false);
const isDeactivateModalOpen = ref(false);
const isActivateModalOpen = ref(false);
const selectedEmployee = ref(null);
const deactivationReason = ref('');

// Forms
const editForm = ref({
    id: null,
    name: '',
    email: '',
    role: '',
    position: '',
    is_active: true
});

const manageForm = ref({
    role: '',
    position: ''
});

const roleOptions = [
    { value: 'HRM', label: 'Human Resource' },
    { value: 'SCM', label: 'Supply Chain' },
    { value: 'FIN', label: 'Finance' },
    { value: 'MAN', label: 'Manufacturing' },
    { value: 'INV', label: 'Inventory' },
    { value: 'ORD', label: 'Order Processing' },
    { value: 'WAR', label: 'Warehouse' },
    { value: 'CRM', label: 'Customer Relationship' },
    { value: 'ECO', label: 'E-Commerce' },
    { value: 'PRO', label: 'Procurement' },
    { value: 'PROJ', label: 'Project Automation' },
    { value: 'IT', label: 'Information Technology' }
];

const positionOptions = [
    { value: 'staff', label: 'Staff' },
    { value: 'supervisor', label: 'Supervisor' },
    { value: 'manager', label: 'Manager' },
    { value: 'general_manager', label: 'General Manager' },
    { value: 'secretary', label: 'Secretary' }
];

// DYNAMIC PROMOTION BLOCK: Removes ranks that are >= the logged-in user's rank
const filteredPositionOptions = computed(() => {
    return positionOptions.filter(opt => getRank('', opt.value) < currentUserRank.value);
});

// Helper functions
const getInitials = (name) => name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
const getAvatarColor = (id) => {
    const colors = ['bg-blue-50 text-blue-600', 'bg-violet-50 text-violet-600', 'bg-emerald-50 text-emerald-600', 'bg-orange-50 text-orange-600', 'bg-pink-50 text-pink-600'];
    return colors[id % colors.length];
};
const formatDate = (date) => date ? new Date(date).toLocaleDateString() : 'N/A';

// Modal actions
const openViewModal = (emp) => {
    selectedEmployee.value = emp;
    isViewModalOpen.value = true;
};
const closeViewModal = () => {
    isViewModalOpen.value = false;
    selectedEmployee.value = null;
};

const openEditModal = (emp) => {
    editForm.value = {
        id: emp.id,
        name: emp.name,
        email: emp.email,
        role: emp.role,
        position: emp.position,
        is_active: emp.is_active
    };
    isEditModalOpen.value = true;
};
const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.value = { id: null, name: '', email: '', role: '', position: '', is_active: true };
};

const updateEmployee = () => {
    router.patch(route('hrm.employees.update', editForm.value.id), {
        name: editForm.value.name,
        email: editForm.value.email,
        role: editForm.value.role,
        position: editForm.value.position,
        is_active: editForm.value.is_active
    }, {
        preserveScroll: true,
        onSuccess: () => {
            triggerToast('Employee updated successfully.');
            closeEditModal();
        },
        onError: (errors) => {
            triggerToast(Object.values(errors)[0] || 'Update failed.');
        }
    });
};

const openManageModal = (emp) => {
    selectedEmployee.value = emp;
    manageForm.value = {
        role: emp.role,
        position: emp.position
    };
    isManageModalOpen.value = true;
};
const closeManageModal = () => {
    isManageModalOpen.value = false;
    selectedEmployee.value = null;
};

const updateRolePosition = () => {
    if (!selectedEmployee.value) return;
    router.patch(route('hrm.employees.update-role-position', selectedEmployee.value.id), {
        role: manageForm.value.role,
        position: manageForm.value.position
    }, {
        preserveScroll: true,
        onSuccess: () => {
            triggerToast('Role/position updated successfully.');
            closeManageModal();
        },
        onError: (errors) => {
            triggerToast(Object.values(errors)[0] || 'Update failed.');
        }
    });
};

const openHistoryModal = (emp) => {
    selectedEmployee.value = emp;
    isHistoryModalOpen.value = true;
};
const closeHistoryModal = () => {
    isHistoryModalOpen.value = false;
    selectedEmployee.value = null;
};

const openDeactivateModal = (emp) => {
    selectedEmployee.value = emp;
    deactivationReason.value = '';
    isDeactivateModalOpen.value = true;
};
const closeDeactivateModal = () => {
    isDeactivateModalOpen.value = false;
    selectedEmployee.value = null;
    deactivationReason.value = '';
};

const confirmDeactivate = () => {
    if (!deactivationReason.value.trim()) {
        triggerToast('Please provide a reason for deactivation.');
        return;
    }
    router.delete(route('hrm.employees.toggle-status', selectedEmployee.value.id), {
        data: { reason: deactivationReason.value },
        preserveScroll: true,
        onSuccess: () => {
            triggerToast(`Employee ${selectedEmployee.value.name} deactivated.`);
            closeDeactivateModal();
        },
        onError: (errors) => {
            triggerToast(Object.values(errors)[0] || 'Deactivation failed.');
        }
    });
};

const openActivateModal = (emp) => {
    selectedEmployee.value = emp;
    deactivationReason.value = '';
    isActivateModalOpen.value = true;
};
const closeActivateModal = () => {
    isActivateModalOpen.value = false;
    selectedEmployee.value = null;
    deactivationReason.value = '';
};

const confirmActivate = () => {
    if (!deactivationReason.value.trim()) {
        triggerToast('Please provide a reason for reactivation.');
        return;
    }
    router.delete(route('hrm.employees.toggle-status', selectedEmployee.value.id), {
        data: { reason: deactivationReason.value },
        preserveScroll: true,
        onSuccess: () => {
            triggerToast(`Employee ${selectedEmployee.value.name} reactivated.`);
            closeActivateModal();
        },
        onError: (errors) => {
            triggerToast(Object.values(errors)[0] || 'Reactivation failed.');
        }
    });
};
</script>

<template>

    <Head title="Employee Management" />

    <AuthenticatedLayout>
        <Transition name="toast">
            <div v-if="showToast"
                class="fixed top-6 right-6 z-[100] flex items-center gap-3 px-6 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl shadow-2xl border border-white/10">
                <CheckCircle class="h-5 w-5 text-emerald-400 dark:text-emerald-600" />
                <p class="text-sm font-bold uppercase tracking-tight">{{ toastMessage }}</p>
            </div>
        </Transition>

        <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6 sm:space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        Employee <span class="text-blue-600">Directory</span>
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                        {{ showDeactivated ? 'Viewing deactivated accounts' : 'Viewing active personnel' }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <input v-model="searchQuery" type="text" placeholder="Search by name, email, role..."
                            class="pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-slate-800 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-blue-600 text-sm w-64" />
                    </div>
                    <button @click="showDeactivated = !showDeactivated"
                        :class="['flex items-center gap-2 px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all',
                            showDeactivated ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400']">
                        <UserMinus v-if="!showDeactivated" class="h-4 w-4" />
                        <UserCheck v-else class="h-4 w-4" />
                        {{ showDeactivated ? 'View Active' : 'Deactivated' }}
                    </button>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 p-2.5 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 flex overflow-x-auto gap-3 no-scrollbar">
                <button v-for="dept in departments" :key="dept" @click="activeDept = dept"
                    :class="['px-8 py-3 rounded-2xl text-xs font-black transition-all uppercase tracking-widest whitespace-nowrap',
                        activeDept === dept ? 'bg-blue-600 text-white shadow-xl translate-y-[-2px]' : 'text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700']">
                    {{ dept }}
                </button>
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between border-b-2 border-slate-100 dark:border-slate-700 pb-3">
                    <h2 class="text-lg font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-widest">
                        Higher Management ({{ managers.length }})
                    </h2>
                </div>
                <div v-if="managers.length === 0"
                    class="text-center py-24 bg-slate-50 dark:bg-slate-900/30 rounded-[2.5rem] border-2 border-dashed border-slate-200 dark:border-slate-700 text-slate-400 text-sm italic">
                    No management found.
                </div>
                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="emp in managers" :key="emp.id"
                        class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between hover:border-blue-400 hover:shadow-xl transition-all group">
                        <div class="flex items-center gap-5">
                            <div class="h-16 w-16 flex-shrink-0 relative">
                                <img v-if="emp.profile_photo_url" :src="emp.profile_photo_url"
                                    class="h-full w-full rounded-2xl object-cover border-2 border-white shadow-md" />
                                <div v-else
                                    :class="['h-full w-full rounded-2xl flex items-center justify-center font-black text-2xl shadow-md', getAvatarColor(emp.id)]">
                                    {{ getInitials(emp.name) }}
                                </div>
                                <div v-if="!isActive(emp)"
                                    class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 border-2 border-white rounded-full">
                                </div>
                            </div>
                            <div>
                                <h4
                                    class="font-bold text-gray-900 dark:text-white text-lg leading-tight group-hover:text-blue-600 transition-colors">
                                    {{ emp.name }}
                                </h4>
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">
                                    {{ emp.role }} • {{ emp.position.replace('_', ' ') }}
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter',
                                            isActive(emp) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                                        {{ isActive(emp) ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openViewModal(emp)"
                                class="p-2 rounded-xl bg-slate-50 dark:bg-slate-700/50 text-slate-500 hover:text-blue-600 transition-colors"
                                title="View Details">
                                <Eye class="h-4 w-4" />
                            </button>
                            <template v-if="canManage(emp)">
                                <button @click="openHistoryModal(emp)"
                                    class="p-2 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                    title="Audit Logs">
                                    <History class="h-4 w-4" />
                                </button>
                                <button @click="openEditModal(emp)"
                                    class="p-2 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors"
                                    title="Edit Details">
                                    <Edit class="h-4 w-4" />
                                </button>
                                <button @click="openManageModal(emp)"
                                    class="p-2 rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors"
                                    title="Manage Role/Position">
                                    <Briefcase class="h-4 w-4" />
                                </button>
                                <button v-if="isActive(emp)" @click="openDeactivateModal(emp)"
                                    class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                                    title="Deactivate">
                                    <ShieldOff class="h-4 w-4" />
                                </button>
                                <button v-else @click="openActivateModal(emp)"
                                    class="p-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                    title="Reactivate">
                                    <ShieldCheck class="h-4 w-4" />
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 mt-8">
                <div class="flex items-center justify-between border-b-2 border-slate-100 dark:border-slate-700 pb-3">
                    <h2 class="text-lg font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-widest">
                        Staff & Supervisors ({{ staff.length }})
                    </h2>
                </div>
                <div v-if="staff.length === 0"
                    class="text-center py-24 bg-slate-50 dark:bg-slate-900/30 rounded-[2.5rem] border-2 border-dashed border-slate-200 dark:border-slate-700 text-slate-400 text-sm italic">
                    No staff found.
                </div>
                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="emp in staff" :key="emp.id"
                        class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between hover:border-blue-400 hover:shadow-xl transition-all group">
                        <div class="flex items-center gap-5">
                            <div class="h-16 w-16 flex-shrink-0 relative">
                                <img v-if="emp.profile_photo_url" :src="emp.profile_photo_url"
                                    class="h-full w-full rounded-2xl object-cover border-2 border-white shadow-md" />
                                <div v-else
                                    :class="['h-full w-full rounded-2xl flex items-center justify-center font-black text-2xl shadow-md', getAvatarColor(emp.id)]">
                                    {{ getInitials(emp.name) }}
                                </div>
                                <div v-if="!isActive(emp)"
                                    class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 border-2 border-white rounded-full">
                                </div>
                            </div>
                            <div>
                                <h4
                                    class="font-bold text-gray-900 dark:text-white text-lg leading-tight group-hover:text-blue-600 transition-colors">
                                    {{ emp.name }}
                                </h4>
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">
                                    {{ emp.role }} • {{ emp.position }}
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter',
                                            isActive(emp) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                                        {{ isActive(emp) ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openViewModal(emp)"
                                class="p-2 rounded-xl bg-slate-50 dark:bg-slate-700/50 text-slate-500 hover:text-blue-600 transition-colors"
                                title="View Details">
                                <Eye class="h-4 w-4" />
                            </button>
                            <template v-if="canManage(emp)">
                                <button @click="openHistoryModal(emp)"
                                    class="p-2 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                    title="Audit Logs">
                                    <History class="h-4 w-4" />
                                </button>
                                <button @click="openEditModal(emp)"
                                    class="p-2 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors"
                                    title="Edit Details">
                                    <Edit class="h-4 w-4" />
                                </button>
                                <button @click="openManageModal(emp)"
                                    class="p-2 rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors"
                                    title="Manage Role/Position">
                                    <Briefcase class="h-4 w-4" />
                                </button>
                                <button v-if="isActive(emp)" @click="openDeactivateModal(emp)"
                                    class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                                    title="Deactivate">
                                    <ShieldOff class="h-4 w-4" />
                                </button>
                                <button v-else @click="openActivateModal(emp)"
                                    class="p-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                    title="Reactivate">
                                    <ShieldCheck class="h-4 w-4" />
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isViewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            @click.self="closeViewModal">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden border border-white/20">
                <div class="bg-blue-600 p-8 text-white relative">
                    <button @click="closeViewModal"
                        class="absolute top-6 right-6 text-white/70 hover:text-white text-3xl leading-none">&times;</button>
                    <div class="flex items-center gap-6">
                        <div
                            class="h-24 w-24 flex-shrink-0 rounded-full overflow-hidden border-4 border-white/30 shadow-xl">
                            <img v-if="selectedEmployee?.profile_photo_url" :src="selectedEmployee.profile_photo_url"
                                class="h-full w-full object-cover" />
                            <div v-else
                                class="h-full w-full bg-white/20 flex items-center justify-center text-4xl font-black">
                                {{ getInitials(selectedEmployee?.name) }}
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black">{{ selectedEmployee?.name }}</h2>
                            <p class="text-blue-200 text-sm font-bold uppercase tracking-widest mt-1">
                                {{ selectedEmployee?.role }} • {{ selectedEmployee?.position?.replace('_', ' ') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Email</p>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300 break-all">{{
                                selectedEmployee?.email }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Employee ID</p>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{
                                selectedEmployee?.employee_id || 'N/A' }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Join Date</p>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{
                                formatDate(selectedEmployee?.join_date) }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Status</p>
                            <span
                                :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-black uppercase', isActive(selectedEmployee) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                                {{ isActive(selectedEmployee) ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <button @click="closeViewModal"
                        class="w-full py-4 bg-slate-900 dark:bg-white dark:text-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 dark:hover:bg-blue-600 transition-all">Close</button>
                </div>
            </div>
        </div>

        <div v-if="isEditModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            @click.self="closeEditModal">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden border border-white/20">
                <div class="bg-amber-600 p-8 text-white">
                    <h2 class="text-xl font-black uppercase">Edit Employee</h2>
                    <p class="text-amber-200 text-xs mt-1">Update employee details</p>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Full
                                Name</label>
                            <input v-model="editForm.name" type="text"
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-amber-500" />
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Email</label>
                            <input v-model="editForm.email" type="email"
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-amber-500" />
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Department
                                (Role)</label>
                            <select v-model="editForm.role"
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-amber-500">
                                <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">{{ opt.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Position</label>
                            <select v-model="editForm.position"
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-amber-500">
                                <option v-for="opt in filteredPositionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="editForm.is_active" class="rounded border-slate-300" />
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeEditModal"
                            class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase">Cancel</button>
                        <button @click="updateEmployee"
                            class="flex-1 py-4 bg-amber-600 text-white rounded-2xl text-xs font-black uppercase shadow-lg hover:bg-amber-700 transition-all">Save
                            Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isManageModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            @click.self="closeManageModal">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden border border-white/20">
                <div class="bg-purple-600 p-8 text-white">
                    <h2 class="text-xl font-black uppercase">Manage Role & Position</h2>
                    <p class="text-purple-200 text-xs mt-1">Update department and position for {{ selectedEmployee?.name
                        }}</p>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Department
                            (Role)</label>
                        <select v-model="manageForm.role"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-purple-500">
                            <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">{{ opt.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Position</label>
                        <select v-model="manageForm.position"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-purple-500">
                            <option v-for="opt in filteredPositionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeManageModal"
                            class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase">Cancel</button>
                        <button @click="updateRolePosition"
                            class="flex-1 py-4 bg-purple-600 text-white rounded-2xl text-xs font-black uppercase shadow-lg hover:bg-purple-700 transition-all">Update</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isHistoryModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            @click.self="closeHistoryModal">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl max-w-2xl w-full overflow-hidden border border-white/20">
                <div class="bg-indigo-600 p-8 text-white flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-black uppercase">Audit Logs</h2>
                        <p class="text-indigo-200 text-sm">{{ selectedEmployee?.name }}</p>
                    </div>
                    <button @click="closeHistoryModal"
                        class="text-white/70 hover:text-white text-3xl leading-none">&times;</button>
                </div>
                <div class="p-8 max-h-[500px] overflow-y-auto">
                    <div v-if="!selectedEmployee?.audit_logs || selectedEmployee.audit_logs.length === 0"
                        class="text-center py-10">
                        <History class="h-12 w-12 text-slate-300 mx-auto mb-2" />
                        <p class="text-sm text-slate-500">No audit logs found for this employee.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="log in selectedEmployee.audit_logs" :key="log.id"
                            class="p-4 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span
                                        :class="['px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest',
                                            log.action === 'deactivate' ? 'bg-red-100 text-red-700' :
                                                log.action === 'reactivate' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700']">
                                        {{ log.action }}
                                    </span>
                                    <p class="mt-2 text-sm font-medium text-slate-800 dark:text-slate-200">{{ log.reason
                                        }}</p>
                                    <p class="text-[10px] text-slate-400 mt-2">{{ new
                                        Date(log.created_at).toLocaleString() }}</p>
                                </div>
                                <div class="text-xs text-slate-400">Admin ID: {{ log.admin_id }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isDeactivateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            @click.self="closeDeactivateModal">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden border border-white/20">
                <div class="bg-red-600 p-8 text-white">
                    <h2 class="text-xl font-black uppercase">Deactivate Account</h2>
                    <p class="text-red-200 text-xs mt-1">Suspend access for {{ selectedEmployee?.name }}</p>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Reason
                            for Deactivation</label>
                        <textarea v-model="deactivationReason" rows="3"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-red-500"
                            placeholder="e.g., End of contract, policy violation..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeDeactivateModal"
                            class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase">Cancel</button>
                        <button @click="confirmDeactivate" :disabled="!deactivationReason.trim()"
                            class="flex-1 py-4 bg-red-600 text-white rounded-2xl text-xs font-black uppercase shadow-lg hover:bg-red-700 transition-all disabled:opacity-50">Confirm
                            Deactivation</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isActivateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            @click.self="closeActivateModal">
            <div
                class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-2xl max-w-md w-full overflow-hidden border border-white/20">
                <div class="bg-emerald-600 p-8 text-white">
                    <h2 class="text-xl font-black uppercase">Reactivate Account</h2>
                    <p class="text-emerald-200 text-xs mt-1">Restore access for {{ selectedEmployee?.name }}</p>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Reason
                            for Reactivation</label>
                        <textarea v-model="deactivationReason" rows="3"
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border-none ring-1 ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-emerald-500"
                            placeholder="e.g., Cleared for return, contract renewed..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeActivateModal"
                            class="flex-1 py-4 text-slate-500 font-bold text-xs uppercase">Cancel</button>
                        <button @click="confirmActivate" :disabled="!deactivationReason.trim()"
                            class="flex-1 py-4 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase shadow-lg hover:bg-emerald-700 transition-all disabled:opacity-50">Confirm
                            Reactivation</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}
</style>