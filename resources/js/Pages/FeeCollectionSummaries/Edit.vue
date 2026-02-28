<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Collection Summary</h1><p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update fee collection summary</p></div>
            <Button @click="$inertia.visit(route('fee-collection-summaries.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
                <select v-model="form.branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Branch</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.branch_name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year <span class="text-red-500">*</span></label>
                <select v-model="form.academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Year</option>
                  <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.year_name }}</option>
                </select>
              </div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Month</label><select v-model="form.month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><option value="">Select</option><option v-for="m in 12" :key="m" :value="m">{{ m }}</option></select></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Year</label><input v-model="form.year" type="number" min="2020" max="2040" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Total Students</label><input v-model="form.total_students" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Total Billed</label><input v-model="form.total_billed" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Total Discount</label><input v-model="form.total_discount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Total Fine</label><input v-model="form.total_fine" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Total Collected</label><input v-model="form.total_collected" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
              <div><label class="block text-sm font-medium text-gray-700 mb-2">Total Pending</label><input v-model="form.total_pending" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" /></div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-collection-summaries.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Summary</span><span v-else>Updating...</span>
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
const props = defineProps({ summary: { type: Object, required: true }, branches: Array, academicYears: Array })
const form = useForm({ branch_id: props.summary.branch_id, academic_year_id: props.summary.academic_year_id, month: props.summary.month, year: props.summary.year, total_students: props.summary.total_students, total_billed: props.summary.total_billed, total_discount: props.summary.total_discount, total_fine: props.summary.total_fine, total_collected: props.summary.total_collected, total_pending: props.summary.total_pending })
const submit = () => { form.put(route('fee-collection-summaries.update', props.summary.id), { preserveScroll: true }) }
</script>
