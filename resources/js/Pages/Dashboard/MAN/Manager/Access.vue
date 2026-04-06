<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Users, ShieldCheck, Plus, X } from 'lucide-vue-next';

const props = defineProps({
    staff: Array,
    allRoles: Array,
});

const expandUser = ref(null);

const toggleSupervisor = (user) => {
    router.post(route('man.access.assign-supervisor'), {
        user_id: user.id,
        is_supervisor: !user.is_manufacturing_supervisor,
    }, { preserveScroll: true });
};

const saveRoles = (user) => {
    router.post(route('man.access.update-roles'), {
        user_id: user.id,
        roles: user.assigned_roles || [],
    }, { preserveScroll: true });
};

const formatRoleLabel = (role) => {
    return role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Manufacturing Access Control</h1>

            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Staff</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Current Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Supervisor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase">Assigned Supervisor Roles</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="user in staff" :key="user.id">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ formatRoleLabel(user.manufacturing_role) || 'Not set' }}
                            </td>
                            <td class="px-6 py-4">
                                <button @click="toggleSupervisor(user)"
                                    :class="user.is_manufacturing_supervisor ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                    class="px-3 py-1 rounded-full text-sm font-medium">
                                    {{ user.is_manufacturing_supervisor ? 'Yes' : 'No' }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="user.is_manufacturing_supervisor" class="flex flex-wrap gap-2">
                                    <span v-for="role in user.supervisor_roles" :key="role.id"
                                        class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                        {{ formatRoleLabel(role.manufacturing_role) }}
                                    </span>
                                    <button @click="expandUser = expandUser === user.id ? null : user.id"
                                        class="text-blue-600 text-xs">
                                        Manage
                                    </button>
                                </div>
                                <span v-else class="text-gray-400 text-sm">—</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <ShieldCheck class="w-5 h-5 text-gray-400" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal / inline editor for assigning supervisor roles -->
            <div v-if="expandUser" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
                @click.self="expandUser = null">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Assign Supervisor Roles</h3>
                        <button @click="expandUser = null"><X class="w-5 h-5" /></button>
                    </div>
                    <p class="mb-4">User: <strong>{{ expandUser.name }}</strong></p>
                    <div class="space-y-2 mb-6">
                        <label v-for="role in allRoles" :key="role" class="flex items-center gap-2">
                            <input type="checkbox" :value="role" v-model="expandUser.assigned_roles" />
                            {{ formatRoleLabel(role) }}
                        </label>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="expandUser = null" class="px-4 py-2 border rounded-lg">Cancel</button>
                        <button @click="saveRoles(expandUser)" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Save Roles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>