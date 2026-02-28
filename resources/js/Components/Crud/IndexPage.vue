<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ title }}</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">{{ description }}</p>
            </div>
            <Button v-if="createRoute" @click="$inertia.visit(createRoute)" variant="primary" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              {{ createLabel || 'Add New' }}
            </Button>
          </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="showSuccess && $page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              {{ $page.props.flash.success }}
            </div>
            <button @click="showSuccess = false" class="text-green-700 hover:text-green-900"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
          </div>
        </div>
        <div v-if="showError && $page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
              {{ $page.props.flash.error }}
            </div>
            <button @click="showError = false" class="text-red-700 hover:text-red-900"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <slot name="filters" />
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-white rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50 gap-3">
            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
              <span class="text-xs sm:text-sm text-gray-700">Show</span>
              <select :value="perPage" @change="$emit('change-per-page', $event.target.value)" class="px-3 sm:px-6 py-1.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
              </select>
              <span class="text-xs sm:text-sm text-gray-700">entries</span>
            </div>
            <div class="w-full sm:w-64">
              <div class="relative">
                <input :value="tableSearch" @input="$emit('table-search', $event.target.value)" type="text" placeholder="Search in table..." class="w-full pl-9 sm:pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm" />
                <svg class="absolute left-2.5 sm:left-3 top-2.5 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table :id="tableId" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-indigo-50 to-blue-50">
                <tr><slot name="table-columns" /></tr>
              </thead>
              <tbody class="bg-white text-center divide-y divide-gray-100"></tbody>
            </table>
          </div>
          <div class="flex flex-col sm:flex-row items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 bg-gray-50 gap-3 sm:gap-4">
            <div class="text-xs sm:text-sm text-gray-600" :id="tableId + '-info-wrap'"></div>
            <div :id="tableId + '-pagination-wrap'"></div>
          </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3 sm:space-y-4">
          <div v-if="mobileLoading" class="flex items-center justify-center py-12 bg-white rounded-lg shadow"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div></div>
          <div v-else-if="mobileItems.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
            <slot name="empty-icon">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </slot>
            <p class="mt-2 text-sm font-medium text-gray-500">{{ emptyMessage || 'No records found' }}</p>
          </div>
          <template v-else>
            <div v-for="(item, index) in mobileItems" :key="item.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
              <div class="p-4">
                <slot name="mobile-card" :item="item" :index="mobileOffset + index + 1" />
              </div>
            </div>
          </template>
        </div>

        <!-- Mobile Pagination -->
        <div v-if="!mobileLoading && mobileItems.length > 0" class="md:hidden mt-4 bg-white rounded-lg shadow p-3">
          <div class="flex items-center justify-between">
            <button @click="$emit('prev-page')" :disabled="mobileCurrentPage === 1 || mobileLoading" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed bg-white hover:bg-gray-50 transition-colors flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>Previous
            </button>
            <div class="text-center"><div class="text-sm font-medium text-gray-900">Page {{ mobileCurrentPage }} of {{ mobileTotalPages }}</div><div class="text-xs text-gray-500 mt-0.5">{{ mobileTotal }} total</div></div>
            <button @click="$emit('next-page')" :disabled="mobileCurrentPage === mobileTotalPages || mobileLoading" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed bg-white hover:bg-gray-50 transition-colors flex items-center gap-1">
              Next<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </div>

      <slot name="modals" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'

const props = defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  createRoute: { type: String, default: '' },
  createLabel: { type: String, default: 'Add New' },
  tableId: { type: String, default: 'data-table' },
  perPage: { type: [Number, String], default: 10 },
  tableSearch: { type: String, default: '' },
  mobileItems: { type: Array, default: () => [] },
  mobileLoading: { type: Boolean, default: true },
  mobileCurrentPage: { type: Number, default: 1 },
  mobileTotalPages: { type: Number, default: 1 },
  mobileTotal: { type: Number, default: 0 },
  mobileOffset: { type: Number, default: 0 },
  emptyMessage: { type: String, default: 'No records found' },
})

defineEmits(['change-per-page', 'table-search', 'prev-page', 'next-page'])

const showSuccess = ref(true)
const showError = ref(true)
const page = usePage()

watch(() => page.props.flash, (f) => {
  if (f.success) { showSuccess.value = true; setTimeout(() => { showSuccess.value = false }, 10000) }
  if (f.error) { showError.value = true; setTimeout(() => { showError.value = false }, 10000) }
}, { deep: true, immediate: true })
</script>

<style scoped>
:deep(.dataTables_info) { font-size: 0.875rem; color: #4b5563; font-weight: 500; }
:deep(.dataTables_paginate) { display: flex; justify-content: flex-end; gap: 0.25rem; flex-wrap: wrap; }
:deep(.paginate_button) { padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 500; border: 1px solid #d1d5db; border-radius: 0.5rem; background: white; color: #374151; cursor: pointer; transition: all 0.2s; }
:deep(.paginate_button:hover:not(.disabled)) { background: #f3f4f6; border-color: #9ca3af; }
:deep(.paginate_button.current) { background: #2563eb; color: white; border-color: #2563eb; }
:deep(.paginate_button.current:hover) { background: #1d4ed8; border-color: #1d4ed8; }
:deep(.paginate_button.disabled) { opacity: 0.5; cursor: not-allowed; background: #f9fafb; }
:deep(#data-table_info), :deep(#data-table_paginate) { display: none; }
:deep(#data-table tbody td) { padding: 0.5rem 0.75rem; font-size: 0.875rem; }
@media (min-width: 640px) { :deep(#data-table tbody td) { padding: 0.75rem 1.5rem; } }
</style>
