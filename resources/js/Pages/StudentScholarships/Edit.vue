<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Scholarship</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update student scholarship details</p>
            </div>
            <Button @click="$inertia.visit(route('student-scholarships.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
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

              <!-- Scholarship -->
              <div>
                <label for="scholarship_id" class="block text-sm font-medium text-gray-700 mb-2">Scholarship <span class="text-red-500">*</span></label>
                <select id="scholarship_id" v-model="form.scholarship_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.scholarship_id }" required>
                  <option value="">Select Scholarship</option>
                  <option v-for="s in scholarships" :key="s.id" :value="s.id">{{ s.scholarship_name }}</option>
                </select>
                <p v-if="form.errors.scholarship_id" class="mt-1 text-sm text-red-600">{{ form.errors.scholarship_id }}</p>
              </div>

              <!-- Academic Year -->
              <div>
                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Year <span class="text-red-500">*</span></label>
                <select id="academic_year_id" v-model="form.academic_year_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.academic_year_id }" required>
                  <option value="">Select Year</option>
                  <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.year_name }}</option>
                </select>
                <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600">{{ form.errors.academic_year_id }}</p>
              </div>

              <!-- Awarded On -->
              <div>
                <label for="awarded_on" class="block text-sm font-medium text-gray-700 mb-2">Awarded On</label>
                <input id="awarded_on" v-model="form.awarded_on" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <!-- Valid From -->
              <div>
                <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-2">Valid From</label>
                <input id="valid_from" v-model="form.valid_from" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <!-- Valid To -->
              <div>
                <label for="valid_to" class="block text-sm font-medium text-gray-700 mb-2">
                  Valid To
                  <span class="ml-1 text-xs text-gray-400 font-normal">(leave blank for ongoing)</span>
                </label>
                <input id="valid_to" v-model="form.valid_to" type="date" :min="form.valid_from || undefined" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <!-- Position Achieved -->
              <div>
                <label for="position_achieved" class="block text-sm font-medium text-gray-700 mb-2">Position Achieved</label>
                <input id="position_achieved" v-model="form.position_achieved" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., 1st" />
              </div>

              <!-- Marks Percentage -->
              <div>
                <label for="marks_percentage" class="block text-sm font-medium text-gray-700 mb-2">Marks Percentage</label>
                <input id="marks_percentage" v-model="form.marks_percentage" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., 95.50" />
              </div>

              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="active">Active</option>
                  <option value="revoked">Revoked</option>
                  <option value="expired">Expired</option>
                </select>
              </div>

              <!-- Revoke Reason -->
              <div class="col-span-1 md:col-span-2">
                <label for="revoke_reason" class="block text-sm font-medium text-gray-700 mb-2">Revoke Reason</label>
                <textarea id="revoke_reason" v-model="form.revoke_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Reason for revoking..."></textarea>
              </div>

              <!-- Notes -->
              <div class="col-span-1 md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Additional notes..."></textarea>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('student-scholarships.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Update Scholarship</span>
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
  studentScholarship: { type: Object, required: true },
  students: Array,
  scholarships: Array,
  academicYears: Array,
  initialEnrollments: { type: Array, default: () => [] },
})

// Cascading dropdown state — pre-loaded from controller
const selectedStudentId = ref(props.studentScholarship.student_id ?? '')
const enrollmentOptions = ref(props.initialEnrollments)
const loadingEnrollments = ref(false)

const form = useForm({
  student_enrollment_id: props.studentScholarship.student_enrollment_id,
  scholarship_id: props.studentScholarship.scholarship_id,
  academic_year_id: props.studentScholarship.academic_year_id,
  awarded_on: props.studentScholarship.awarded_on || '',
  valid_from: props.studentScholarship.valid_from || '',
  valid_to: props.studentScholarship.valid_to || '',
  position_achieved: props.studentScholarship.position_achieved || '',
  marks_percentage: props.studentScholarship.marks_percentage || '',
  status: props.studentScholarship.status,
  revoke_reason: props.studentScholarship.revoke_reason || '',
  notes: props.studentScholarship.notes || '',
})

// When student changes → load their enrollments
const onStudentChange = async () => {
  form.student_enrollment_id = ''
  enrollmentOptions.value = []
  if (!selectedStudentId.value) return

  loadingEnrollments.value = true
  try {
    const res = await axios.get(route('student-scholarships.enrollments-by-student', selectedStudentId.value))
    enrollmentOptions.value = res.data
  } catch (e) { console.error('Error loading enrollments:', e) }
  finally { loadingEnrollments.value = false }
}

const submit = () => {
  form.put(route('student-scholarships.update', props.studentScholarship.id), { preserveScroll: true })
}
</script>
