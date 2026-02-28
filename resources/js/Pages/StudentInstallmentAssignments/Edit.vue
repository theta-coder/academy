<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Installment Assignment</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update installment assignment details</p>
            </div>
            <Button @click="$inertia.visit(route('student-installment-assignments.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- Student (cascading) -->
              <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student <span class="text-red-500">*</span></label>
                <select id="student_id" v-model="selectedStudentId" @change="onStudentChange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Student</option>
                  <option v-for="s in students" :key="s.id" :value="s.id">{{ s.student_name }} ({{ s.admission_no }})</option>
                </select>
              </div>

              <!-- Enrollment (cascading) -->
              <div>
                <label for="student_enrollment_id" class="block text-sm font-medium text-gray-700 mb-2">Enrollment <span class="text-red-500">*</span></label>
                <select id="student_enrollment_id" v-model="form.student_enrollment_id" :disabled="!selectedStudentId || loadingEnrollments" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed" :class="{ 'border-red-500': form.errors.student_enrollment_id }" required>
                  <option value="">{{ loadingEnrollments ? 'Loading enrollments...' : 'Select Enrollment' }}</option>
                  <option v-for="e in enrollmentOptions" :key="e.id" :value="e.id">
                    {{ e.class_name }} – {{ e.section_name }} ({{ e.academic_year }})
                  </option>
                </select>
                <p v-if="form.errors.student_enrollment_id" class="mt-1 text-sm text-red-600">{{ form.errors.student_enrollment_id }}</p>
              </div>

              <!-- Installment Plan -->
              <div>
                <label for="installment_plan_id" class="block text-sm font-medium text-gray-700 mb-2">Installment Plan <span class="text-red-500">*</span></label>
                <select id="installment_plan_id" v-model="form.installment_plan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.installment_plan_id }" required>
                  <option value="">Select Plan</option>
                  <option v-for="p in installmentPlans" :key="p.id" :value="p.id">{{ p.plan_name }} ({{ p.total_installments }} installments)</option>
                </select>
                <p v-if="form.errors.installment_plan_id" class="mt-1 text-sm text-red-600">{{ form.errors.installment_plan_id }}</p>
              </div>

              <!-- Fee Voucher -->
              <div>
                <label for="fee_voucher_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Fee Voucher
                  <span class="ml-1 text-xs text-gray-400 font-normal">(optional)</span>
                </label>
                <select id="fee_voucher_id" v-model="form.fee_voucher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="">Select Voucher (optional)</option>
                  <option v-for="v in feeVouchers" :key="v.id" :value="v.id">{{ v.voucher_no }} — Rs {{ Number(v.net_amount).toLocaleString() }}</option>
                </select>
              </div>

              <!-- Total Amount -->
              <div>
                <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">Total Amount <span class="text-red-500">*</span></label>
                <input id="total_amount" v-model="form.total_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.total_amount }" placeholder="0.00" required />
                <p v-if="form.errors.total_amount" class="mt-1 text-sm text-red-600">{{ form.errors.total_amount }}</p>
              </div>

              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="active">Active</option>
                  <option value="completed">Completed</option>
                  <option value="defaulted">Defaulted</option>
                </select>
              </div>

              <!-- Notes -->
              <div class="col-span-1 md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Additional notes..."></textarea>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('student-installment-assignments.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Assignment</span>
                <span v-else>Updating...</span>
              </Button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import axios from 'axios'

const props = defineProps({
  assignment: { type: Object, required: true },
  students: Array,
  installmentPlans: Array,
  feeVouchers: { type: Array, default: () => [] },
  initialEnrollments: { type: Array, default: () => [] },
})

// Cascading dropdown state — pre-loaded from controller
const selectedStudentId = ref(props.assignment.student_id ?? '')
const enrollmentOptions = ref(props.initialEnrollments)
const loadingEnrollments = ref(false)

const form = useForm({
  student_enrollment_id: props.assignment.student_enrollment_id,
  installment_plan_id: props.assignment.installment_plan_id,
  fee_voucher_id: props.assignment.fee_voucher_id || '',
  total_amount: props.assignment.total_amount,
  status: props.assignment.status,
  notes: '',
})

// When student changes → load their enrollments
const onStudentChange = async () => {
  form.student_enrollment_id = ''
  enrollmentOptions.value = []
  if (!selectedStudentId.value) return

  loadingEnrollments.value = true
  try {
    const res = await axios.get(route('student-installment-assignments.enrollments-by-student', selectedStudentId.value))
    enrollmentOptions.value = res.data
  } catch (e) { console.error('Error loading enrollments:', e) }
  finally { loadingEnrollments.value = false }
}

const submit = () => {
  form.put(route('student-installment-assignments.update', props.assignment.id), { preserveScroll: true })
}
</script>
