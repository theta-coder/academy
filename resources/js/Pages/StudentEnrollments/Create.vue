<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Enrollment</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Enroll a student into a class</p>
            </div>
            <Button @click="$inertia.visit(route('student-enrollments.index'))" variant="secondary" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Back to List
            </Button>
          </div>
        </div>

        <!-- Create Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- Student -->
              <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student <span class="text-red-500">*</span></label>
                <select id="student_id" v-model="form.student_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.student_id }" required>
                  <option value="">Select Student</option>
                  <option v-for="s in students" :key="s.id" :value="s.id">{{ s.student_name }} ({{ s.admission_no }})</option>
                </select>
                <p v-if="form.errors.student_id" class="mt-1 text-sm text-red-600">{{ form.errors.student_id }}</p>
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

              <!-- ========== CASCADING DROPDOWNS ========== -->

              <!-- 1. Branch -->
              <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
                <select id="branch_id" v-model="form.branch_id" @change="onBranchChange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.branch_id }" required>
                  <option value="">Select Branch</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.branch_name }}</option>
                </select>
                <p v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</p>
              </div>

              <!-- 2. Class (loaded after branch selection) -->
              <div>
                <label for="branch_class_id" class="block text-sm font-medium text-gray-700 mb-2">Class <span class="text-red-500">*</span></label>
                <select id="branch_class_id" v-model="selectedBranchClassId" @change="onClassChange" :disabled="!form.branch_id || loadingClasses" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed" required>
                  <option value="">{{ loadingClasses ? 'Loading classes...' : 'Select Class' }}</option>
                  <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.class_name }}</option>
                </select>
              </div>

              <!-- 3. Section (loaded after class selection) -->
              <div>
                <label for="class_section_id" class="block text-sm font-medium text-gray-700 mb-2">Section <span class="text-red-500">*</span></label>
                <select id="class_section_id" v-model="form.class_section_id" :disabled="!selectedBranchClassId || loadingSections" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed" :class="{ 'border-red-500': form.errors.class_section_id }" required>
                  <option value="">{{ loadingSections ? 'Loading sections...' : 'Select Section' }}</option>
                  <option v-for="s in sectionOptions" :key="s.id" :value="s.id">{{ s.section_name }}</option>
                </select>
                <p v-if="form.errors.class_section_id" class="mt-1 text-sm text-red-600">{{ form.errors.class_section_id }}</p>
              </div>

              <!-- Roll Number -->
              <div>
                <label for="roll_number" class="block text-sm font-medium text-gray-700 mb-2">Roll Number</label>
                <input id="roll_number" v-model="form.roll_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., 101" />
              </div>

              <!-- Admission Date -->
              <div>
                <label for="admission_date" class="block text-sm font-medium text-gray-700 mb-2">Admission Date <span class="text-red-500">*</span></label>
                <input id="admission_date" v-model="form.admission_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :class="{ 'border-red-500': form.errors.admission_date }" required />
                <p v-if="form.errors.admission_date" class="mt-1 text-sm text-red-600">{{ form.errors.admission_date }}</p>
              </div>

              <!-- Enrollment Type -->
              <div>
                <label for="enrollment_type" class="block text-sm font-medium text-gray-700 mb-2">Enrollment Type</label>
                <select id="enrollment_type" v-model="form.enrollment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="school">School</option>
                  <option value="academy">Academy</option>
                  <option value="both">Both</option>
                </select>
              </div>

              <!-- Sibling Order -->
              <div>
                <label for="sibling_order" class="block text-sm font-medium text-gray-700 mb-2">Sibling Order</label>
                <input id="sibling_order" v-model="form.sibling_order" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., 1" />
              </div>

              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="active">Active</option>
                  <option value="left">Left</option>
                  <option value="graduated">Graduated</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>

              <!-- Remarks -->
              <div class="col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea id="remarks" v-model="form.remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Additional notes..."></textarea>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button type="button" variant="secondary" @click="$inertia.visit(route('student-enrollments.index'))" class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm">Cancel</Button>
              <Button type="submit" variant="primary" :loading="form.processing" class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm">
                <span v-if="!form.processing">Create Enrollment</span>
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
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'
import axios from 'axios'

const props = defineProps({
  students: Array,
  academicYears: Array,
  branches: Array,
})

// Cascading dropdown state
const classOptions = ref([])
const sectionOptions = ref([])
const loadingClasses = ref(false)
const loadingSections = ref(false)
const selectedBranchClassId = ref('')

const form = useForm({
  student_id: '',
  academic_year_id: '',
  branch_id: '',
  class_section_id: '',
  roll_number: '',
  admission_date: '',
  enrollment_type: 'school',
  sibling_order: '',
  status: 'active',
  remarks: '',
})

// When branch changes → load classes for that branch
const onBranchChange = async () => {
  // Reset dependent fields
  selectedBranchClassId.value = ''
  form.class_section_id = ''
  classOptions.value = []
  sectionOptions.value = []

  if (!form.branch_id) return

  loadingClasses.value = true
  try {
    const res = await axios.get(route('student-enrollments.classes-by-branch', form.branch_id))
    classOptions.value = res.data
  } catch (e) {
    console.error('Error loading classes:', e)
  } finally {
    loadingClasses.value = false
  }
}

// When class changes → load sections for that branch_class
const onClassChange = async () => {
  // Reset dependent field
  form.class_section_id = ''
  sectionOptions.value = []

  if (!selectedBranchClassId.value) return

  loadingSections.value = true
  try {
    const res = await axios.get(route('student-enrollments.sections-by-branch-class', selectedBranchClassId.value))
    sectionOptions.value = res.data
  } catch (e) {
    console.error('Error loading sections:', e)
  } finally {
    loadingSections.value = false
  }
}

const submit = () => {
  form.post(route('student-enrollments.store'), {
    preserveScroll: true,
  })
}
</script>
