<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Fee Concession</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update student fee concession details</p>
            </div>
            <Button @click="$inertia.visit(route('student-fee-concessions.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- 1. Student -->
              <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student <span class="text-red-500">*</span></label>
                <select id="student_id" v-model="selectedStudentId" @change="onStudentChange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="">Select Student</option>
                  <option v-for="s in students" :key="s.id" :value="s.id">{{ s.student_name }} ({{ s.admission_no }})</option>
                </select>
              </div>

              <!-- 2. Enrollment (cascading) -->
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

              <!-- Concession Type -->
              <div>
                <label for="concession_type_id" class="block text-sm font-medium text-gray-700 mb-2">Concession Type <span class="text-red-500">*</span></label>
                <select id="concession_type_id" v-model="form.concession_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.concession_type_id }" required>
                  <option value="">Select Concession Type</option>
                  <option v-for="ct in concessionTypes" :key="ct.id" :value="ct.id">{{ ct.concession_name }}</option>
                </select>
                <p v-if="form.errors.concession_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.concession_type_id }}</p>
              </div>

              <!-- Fee Type -->
              <div>
                <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Fee Type
                  <span class="ml-1 text-xs text-gray-400 font-normal">(leave blank to apply to all fees)</span>
                </label>
                <select id="fee_type_id" v-model="form.fee_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="">All Fee Types</option>
                  <option v-for="ft in feeTypes" :key="ft.id" :value="ft.id">{{ ft.fee_name }}</option>
                </select>
              </div>

              <!-- Discount Type -->
              <div>
                <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type <span class="text-red-500">*</span></label>
                <select id="discount_type" v-model="form.discount_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                  <option value="percentage">Percentage (%)</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
              </div>

              <!-- Discount Value -->
              <div>
                <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-2">
                  Discount Value <span class="text-red-500">*</span>
                  <span class="ml-1 text-xs text-gray-400 font-normal">{{ form.discount_type === 'percentage' ? '(0–100%)' : '(amount)' }}</span>
                </label>
                <div class="relative mt-1">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 text-sm pointer-events-none">
                    {{ form.discount_type === 'percentage' ? '%' : '₹' }}
                  </span>
                  <input id="discount_value" v-model.number="form.discount_value" type="number" min="0" :max="form.discount_type === 'percentage' ? 100 : undefined" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-8" :class="{ 'border-red-500': form.errors.discount_value }" placeholder="0.00" required />
                </div>
                <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
              </div>

              <!-- Start Date -->
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <!-- End Date -->
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                  End Date
                  <span class="ml-1 text-xs text-gray-400 font-normal">(leave blank for ongoing)</span>
                </label>
                <input id="end_date" v-model="form.end_date" type="date" :min="form.start_date || undefined" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <!-- Is Active -->
              <div class="flex items-center gap-3 pt-2">
                <label class="inline-flex items-center cursor-pointer">
                  <input id="is_active" v-model="form.is_active" type="checkbox" class="sr-only peer" />
                  <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                  <span class="ms-3 text-sm font-medium text-gray-700">Active Concession</span>
                </label>
              </div>

              <!-- Remarks -->
              <div class="col-span-1 md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea id="remarks" v-model="form.remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Additional notes about this concession..."></textarea>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('student-fee-concessions.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Concession</span>
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
  concession: { type: Object, required: true },
  students: Array,
  feeTypes: Array,
  concessionTypes: Array,
  initialEnrollments: { type: Array, default: () => [] },
})

// Cascading dropdown state — pre-loaded from controller
const selectedStudentId = ref(props.concession.student_id ?? '')
const enrollmentOptions = ref(props.initialEnrollments)
const loadingEnrollments = ref(false)

const form = useForm({
  student_enrollment_id: props.concession.student_enrollment_id,
  fee_type_id: props.concession.fee_type_id || '',
  concession_type_id: props.concession.concession_type_id,
  discount_type: props.concession.discount_type,
  discount_value: props.concession.discount_value,
  start_date: props.concession.start_date || '',
  end_date: props.concession.end_date || '',
  remarks: props.concession.remarks || '',
  is_active: props.concession.is_active,
})

// When student changes → load their enrollments
const onStudentChange = async () => {
  form.student_enrollment_id = ''
  enrollmentOptions.value = []
  if (!selectedStudentId.value) return

  loadingEnrollments.value = true
  try {
    const res = await axios.get(route('student-fee-concessions.enrollments-by-student', selectedStudentId.value))
    enrollmentOptions.value = res.data
  } catch (e) { console.error('Error loading enrollments:', e) }
  finally { loadingEnrollments.value = false }
}

const submit = () => {
  form.put(route('student-fee-concessions.update', props.concession.id), {
    preserveScroll: true,
  })
}
</script>
