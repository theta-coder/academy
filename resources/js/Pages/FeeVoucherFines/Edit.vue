<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Voucher Fine</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update voucher fine details</p>
            </div>
            <Button @click="$inertia.visit(route('fee-voucher-fines.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="voucher_id" class="block text-sm font-medium text-gray-700 mb-2">Voucher <span class="text-red-500">*</span></label>
                <input type="text" :value="'Voucher #' + fine.voucher_id" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm" disabled />
              </div>
              <div>
                <label for="fine_rule_id" class="block text-sm font-medium text-gray-700 mb-2">Fine Rule</label>
                <select id="fine_rule_id" v-model="form.fine_rule_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="">Manual Fine</option>
                  <option v-for="r in fineRules" :key="r.id" :value="r.id">
                    {{ r.description || `${r.fine_type === 'fixed' ? 'Rs. ' + r.fine_value : r.fine_value + '%'} (After ${r.days_after_due} days)` }}
                  </option>
                </select>
              </div>
              <div>
                <label for="days_overdue" class="block text-sm font-medium text-gray-700 mb-2">Days Overdue <span class="text-red-500">*</span></label>
                <input id="days_overdue" v-model="form.days_overdue" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="fine_type" class="block text-sm font-medium text-gray-700 mb-2">Fine Type <span class="text-red-500">*</span></label>
                <select id="fine_type" v-model="form.fine_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="fixed">Fixed</option><option value="percentage">Percentage</option>
                </select>
              </div>
              <div>
                <label for="fine_value" class="block text-sm font-medium text-gray-700 mb-2">Fine Value <span class="text-red-500">*</span></label>
                <input id="fine_value" v-model="form.fine_value" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="calculated_amount" class="block text-sm font-medium text-gray-700 mb-2">Calculated Amount <span class="text-red-500">*</span></label>
                <input id="calculated_amount" v-model="form.calculated_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="applied_on" class="block text-sm font-medium text-gray-700 mb-2">Applied On</label>
                <input id="applied_on" v-model="form.applied_on" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div class="flex items-center">
                <input id="is_waived" v-model="form.is_waived" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label for="is_waived" class="ml-2 block text-sm text-gray-700">Waived</label>
              </div>
              <div class="col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-voucher-fines.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Fine</span><span v-else>Updating...</span>
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
const props = defineProps({
  fine: { type: Object, required: true },
  fineRules: { type: Array, default: () => [] }
})
const form = useForm({ voucher_id: props.fine.voucher_id, fine_rule_id: props.fine.fine_rule_id ?? '', days_overdue: props.fine.days_overdue, fine_type: props.fine.fine_type, fine_value: props.fine.fine_value, calculated_amount: props.fine.calculated_amount, applied_on: props.fine.applied_on ?? '', is_waived: props.fine.is_waived ?? false, notes: props.fine.notes ?? '' })
const submit = () => { form.put(route('fee-voucher-fines.update', props.fine.id), { preserveScroll: true }) }
</script>
