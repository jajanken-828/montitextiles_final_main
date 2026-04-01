<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Users, ArrowRight, CheckCircle2, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    staff: Array,
});

const searchQuery = ref('');
const updating = ref(false);
const updatingId = ref(null);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const filteredStaff = computed(() => {
    if (!searchQuery.value) return props.staff;
    const query = searchQuery.value.toLowerCase();
    return props.staff.filter(s =>
        s.name.toLowerCase().includes(query) ||
        s.email.toLowerCase().includes(query)
    );
});

const assignRole = (userId, newRole) => {
    if (!newRole) return;
    updating.value = true;
    updatingId.value = userId;

    router.post(route('scm.manager.update-staff-role', userId), { role: newRole }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage(`Staff role updated successfully.`, 'success');
            // Refresh the list
            router.reload({ only: ['staff'] });
        },
        onError: () => {
            showToastMessage('Failed to update role. Please try again.', 'error');
        },
        onFinish: () => {
            updating.value = false;
            updatingId.value = null;
        }
    });
};

const showToastMessage = (msg, type) => {
    toastMessage.value = msg;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 3000);
};
</script>

<template>

    <Head title="SCM Staff Assignment" />
    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 max-w-7xl mx-auto">
            <!-- Toast Notification -->
            <Transition name="toast">
                <div v-if="showToast"
                    class="fixed top-6 right-6 z-50 flex items-center gap-3 px-6 py-4 bg-slate-900 text-white rounded-2xl shadow-2xl border border-white/10">
                    <component :is="toastType === 'success' ? CheckCircle2 : AlertCircle" class="h-5 w-5" />
                    <p class="text-sm font-bold uppercase tracking-tight">{{ toastMessage }}</p>
                </div>
            </Transition>

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                        Staff <span class="text-blue-600">Assignment</span>
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                        Reassign SCM staff to other departments (INV, MAN, PRO).
                    </p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md mb-6">
                <input v-model="searchQuery" type="text" placeholder="Search by name or email..."
                    class="w-full px-4 py-2 pl-10 border border-gray-200 dark:border-zinc-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800" />
                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Staff List -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-zinc-800/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Current Role</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Assign To</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                            <tr v-for="user in filteredStaff" :key="user.id"
                                class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <select @change="assignRole(user.id, $event.target.value)"
                                            :disabled="updating && updatingId === user.id"
                                            class="border border-gray-200 dark:border-zinc-700 rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select Department</option>
                                            <option value="INV">Inventory (INV)</option>
                                            <option value="MAN">Manufacturing (MAN)</option>
                                            <option value="PRO">Procurement (PRO)</option>
                                            <option value="SCM">Keep as SCM</option>
                                        </select>
                                        <span v-if="updating && updatingId === user.id"
                                            class="text-xs text-gray-400">Updating...</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredStaff.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No SCM staff found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.toast-enter-from,
.toast-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}
</style>