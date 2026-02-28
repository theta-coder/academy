<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Fee Voucher</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Generate a new fee voucher for a student</p>
            </div>
            <Button @click="$inertia.visit(route('fee-vouchers.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="student_enrollment_id" class="block text-sm font-medium text-gray-700 mb-2">Student Enrollment <span class="text-red-500">*</span></label>
                <select id="student_enrollment_id" v-model="form.student_enrollment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.student_enrollment_id }" required>
                  <option value="">Select Enrollment</option>
                  <option v-for="e in enrollments" :key="e.id" :value="e.id">{{ e.student?.first_name }} - {{ e.student?.admission_no }}</option>
                </select>
                <p v-if="form.errors.student_enrollment_id" class="mt-1 text-sm text-red-600">{{ form.errors.student_enrollment_id }}</p>
              </div>
              <div>
                <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">Fee Type <span class="text-red-500">*</span></label>
                <select id="fee_type_id" v-model="form.fee_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.fee_type_id }" required>
                  <option value="">Select Fee Type</option>
                  <option v-for="f in feeTypes" :key="f.id" :value="f.id">{{ f.fee_name }}</option>
                </select>
                <p v-if="form.errors.fee_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.fee_type_id }}</p>
              </div>
              <div>
                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Year <span class="text-red-500">*</span></label>
                <select id="academic_year_id" v-model="form.academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Year</option>
                  <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.year_name }}</option>
                </select>
                <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600">{{ form.errors.academic_year_id }}</p>
              </div>
              <div>
                <label for="voucher_no" class="block text-sm font-medium text-gray-700 mb-2">Voucher No</label>
                <input id="voucher_no" v-model="form.voucher_no" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Auto-generated if blank" />
              </div>
              <div>
                <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month <span class="text-red-500">*</span></label>
                <select id="month" v-model="form.month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Month</option>
                  <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year <span class="text-red-500">*</span></label>
                <input id="year" v-model="form.year" type="number" min="2020" max="2040" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="generated_for" class="block text-sm font-medium text-gray-700 mb-2">Generated For</label>
                <input id="generated_for" v-model="form.generated_for" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. Tuition Fee" />
              </div>
              <div>
                <label for="original_amount" class="block text-sm font-medium text-gray-700 mb-2">Original Amount <span class="text-red-500">*</span></label>
                <input id="original_amount" v-model="form.original_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Amount</label>
                <input id="discount_amount" v-model="form.discount_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="fine_amount" class="block text-sm font-medium text-gray-700 mb-2">Fine Amount</label>
                <input id="fine_amount" v-model="form.fine_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="net_amount" class="block text-sm font-medium text-gray-700 mb-2">Net Amount <span class="text-red-500">*</span></label>
                <input id="net_amount" v-model="form.net_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                <input id="paid_amount" v-model="form.paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="remaining_amount" class="block text-sm font-medium text-gray-700 mb-2">Remaining Amount</label>
                <input id="remaining_amount" v-model="form.remaining_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date <span class="text-red-500">*</span></label>
                <input id="due_date" v-model="form.due_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
              </div>
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="pending">Pending</option>
                  <option value="partial">Partial</option>
                  <option value="paid">Paid</option>
                  <option value="waived">Waived</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div class="col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Additional notes..."></textarea>
              </div>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('fee-vouchers.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Create Voucher</span>
                <span v-else>Creating...</span>
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
const props = defineProps({ enrollments: Array, feeTypes: Array, academicYears: Array })
const form = useForm({
  student_enrollment_id: '', fee_type_id: '', academic_year_id: '', voucher_no: '',
  month: '', year: new Date().getFullYear(), generated_for: '', original_amount: '', discount_amount: 0,
  fine_amount: 0, net_amount: '', paid_amount: 0, remaining_amount: 0, due_date: '', status: 'pending', notes: '',
})
const submit = () => { form.post(route('fee-vouchers.store'), { preserveScroll: true }) }
</script>
