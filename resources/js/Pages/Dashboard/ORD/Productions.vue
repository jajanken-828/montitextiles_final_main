<template>
    <AuthenticatedLayout>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Production Tracking</h1>
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="prod in productions" :key="prod.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ prod.po_number }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ prod.client_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <div class="w-32 bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-600 h-2 rounded-full" :style="{ width: prod.progress + '%' }"></div>
                </div>
                <span class="text-xs">{{ prod.completed_quantity }}/{{ prod.total_quantity }}</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="statusClass(prod.status)" class="px-2 py-1 text-xs rounded-full">
                {{ prod.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ prod.created_at }}</td>
          </tr>
          <tr v-if="productions.length === 0">
            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No active production orders.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({
  productions: Array
})

const statusClass = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}
</script>