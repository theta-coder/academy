<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Record Fee Payment</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Record a new fee payment transaction</p>
            </div>
            <Button @click="$inertia.visit(route('fee-payments.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>

        <!-- Create Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- Voucher -->
              <div>
                <label for="voucher_id" class="block text-sm font-medium text-gray-700 mb-2">Voucher <span class="text-red-500">*</span></label>
                <select id="voucher_id" v-model="form.voucher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.voucher_id }" required>
                  <option value="">Select Voucher</option>
                  <option v-for="v in vouchers" :key="v.id" :value="v.id">{{ v.voucher_no }} - Rs. {{ Number(v.net_amount).toLocaleString() }} ({{ v.status }})</option>
                </select>
                <p v-if="form.errors.voucher_id" class="mt-1 text-sm text-red-600">{{ form.errors.voucher_id }}</p>
              </div>

              <!-- Student Enrollment -->
              <div>
                <label for="student_enrollment_id" class="block text-sm font-medium text-gray-700 mb-2">Student Enrollment <span class="text-red-500">*</span></label>
                <select id="student_enrollment_id" v-model="form.student_enrollment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.student_enrollment_id }" required>
                  <option value="">Select Enrollment</option>
                  <option v-for="e in enrollments" :key="e.id" :value="e.id">{{ e.student?.student_name || e.student?.first_name }} ({{ e.student?.admission_no }})</option>
                </select>
                <p v-if="form.errors.student_enrollment_id" class="mt-1 text-sm text-red-600">{{ form.errors.student_enrollment_id }}</p>
              </div>

              <!-- Paid Amount -->
              <div>
                <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">Paid Amount (Rs.) <span class="text-red-500">*</span></label>
                <input id="paid_amount" v-model="form.paid_amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.paid_amount }" required />
                <p v-if="form.errors.paid_amount" class="mt-1 text-sm text-red-600">{{ form.errors.paid_amount }}</p>
              </div>

              <!-- Payment Date -->
              <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                <input id="payment_date" v-model="form.payment_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.payment_date }" required />
                <p v-if="form.errors.payment_date" class="mt-1 text-sm text-red-600">{{ form.errors.payment_date }}</p>
              </div>

              <!-- Payment Method -->
              <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method <span class="text-red-500">*</span></label>
                <select id="payment_method" v-model="form.payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cheque">Cheque</option>
                  <option value="online">Online</option>
                </select>
              </div>

              <!-- Bank Name -->
              <div>
                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                <input id="bank_name" v-model="form.bank_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., HBL, UBL" />
              </div>

              <!-- Transaction Ref -->
              <div>
                <label for="transaction_ref" class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference</label>
                <input id="transaction_ref" v-model="form.transaction_ref" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Transaction ID / Cheque No" />
              </div>

              <!-- Is Advance -->
              <div class="flex items-center">
                <input id="is_advance" v-model="form.is_advance" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label for="is_advance" class="ml-2 block text-sm text-gray-700">Advance Payment</label>
              </div>

              <!-- Notes -->
              <div class="col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Additional notes..."></textarea>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-payments.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Record Payment</span>
                <span v-else>Recording...</span>
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
  vouchers: { type: Array, default: () => [] },
  enrollments: { type: Array, default: () => [] },
})

const form = useForm({
  voucher_id: '',
  student_enrollment_id: '',
  paid_amount: '',
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: 'cash',
  bank_name: '',
  transaction_ref: '',
  is_advance: false,
  notes: '',
})

const submit = () => {
  form.post(route('fee-payments.store'), {
    preserveScroll: true,
  })
}
</script>