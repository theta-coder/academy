<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div><h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Refund</h1><p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Process a fee refund</p></div>
            <Button @click="$inertia.visit(route('fee-refunds.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="student_enrollment_id" class="block text-sm font-medium text-gray-700 mb-2">Student Enrollment <span class="text-red-500">*</span></label>
                <select id="student_enrollment_id" v-model="form.student_enrollment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required><option value="">Select Enrollment</option></select>
                <p v-if="form.errors.student_enrollment_id" class="mt-1 text-sm text-red-600">{{ form.errors.student_enrollment_id }}</p>
              </div>
              <div>
                <label for="payment_id" class="block text-sm font-medium text-gray-700 mb-2">Payment <span class="text-red-500">*</span></label>
                <select id="payment_id" v-model="form.payment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required><option value="">Select Payment</option></select>
                <p v-if="form.errors.payment_id" class="mt-1 text-sm text-red-600">{{ form.errors.payment_id }}</p>
              </div>
              <div>
                <label for="refund_amount" class="block text-sm font-medium text-gray-700 mb-2">Refund Amount <span class="text-red-500">*</span></label>
                <input id="refund_amount" v-model="form.refund_amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="refund_date" class="block text-sm font-medium text-gray-700 mb-2">Refund Date <span class="text-red-500">*</span></label>
                <input id="refund_date" v-model="form.refund_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="refund_method" class="block text-sm font-medium text-gray-700 mb-2">Refund Method</label>
                <select id="refund_method" v-model="form.refund_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option>
                </select>
              </div>
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option>
                </select>
              </div>
              <div class="col-span-2">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason <span class="text-red-500">*</span></label>
                <textarea id="reason" v-model="form.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required></textarea>
              </div>
              <div class="col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-refunds.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Create Refund</span><span v-else>Creating...</span>
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
const form = useForm({ student_enrollment_id: '', payment_id: '', refund_amount: '', refund_date: '', reason: '', refund_method: 'cash', bank_details: '', status: 'pending', notes: '' })
const submit = () => { form.post(route('fee-refunds.store'), { preserveScroll: true }) }
</script>
