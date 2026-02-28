<template>
  <AppLayout>
    <div class="min-h-screen flex flex-col">
      <div class="flex-1 px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8">

        <!-- Page Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Student</h1>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">Update student information</p>
            </div>
            <Button
              @click="$inertia.visit(route('students.index'))"
              variant="secondary"
              class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to List
            </Button>
          </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

              <!-- Section: Basic Info -->
              <div class="col-span-2">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 pb-2 mb-1">
                  Basic Information
                </h2>
              </div>

              <!-- Student Name -->
              <div>
                <label for="student_name" class="block text-sm font-medium text-gray-700 mb-2">
                  Student Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="student_name"
                  v-model="form.student_name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.student_name }"
                  placeholder="Enter student full name"
                  required
                />
                <p v-if="form.errors.student_name" class="mt-1 text-sm text-red-600">{{ form.errors.student_name }}</p>
              </div>

              <!-- Admission No -->
              <div>
                <label for="admission_no" class="block text-sm font-medium text-gray-700 mb-2">
                  Admission No
                </label>
                <input
                  id="admission_no"
                  v-model="form.admission_no"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.admission_no }"
                  placeholder="e.g., ADM-2024-001"
                />
                <p v-if="form.errors.admission_no" class="mt-1 text-sm text-red-600">{{ form.errors.admission_no }}</p>
              </div>

              <!-- Parent -->
              <div>
                <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Parent / Guardian <span class="text-red-500">*</span>
                </label>
                <select
                  id="parent_id"
                  v-model="form.parent_id"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.parent_id }"
                  required
                >
                  <option value="">Select a parent</option>
                  <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                    {{ parent.father_name }} {{ parent.father_phone ? '— ' + parent.father_phone : '' }}
                  </option>
                </select>
                <p v-if="form.errors.parent_id" class="mt-1 text-sm text-red-600">{{ form.errors.parent_id }}</p>
              </div>

              <!-- Date of Birth -->
              <div>
                <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                  Date of Birth <span class="text-red-500">*</span>
                </label>
                <input
                  id="date_of_birth"
                  v-model="form.date_of_birth"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.date_of_birth }"
                  required
                />
                <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ form.errors.date_of_birth }}</p>
              </div>

              <!-- Gender -->
              <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                  Gender <span class="text-red-500">*</span>
                </label>
                <select
                  id="gender"
                  v-model="form.gender"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.gender }"
                  required
                >
                  <option value="">Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
                <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.gender }}</p>
              </div>

              <!-- Student Type -->
              <div>
                <label for="student_type" class="block text-sm font-medium text-gray-700 mb-2">
                  Student Type <span class="text-red-500">*</span>
                </label>
                <select
                  id="student_type"
                  v-model="form.student_type"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.student_type }"
                  required
                >
                  <option value="">Select type</option>
                  <option value="school">School</option>
                  <option value="academy">Academy</option>
                  <option value="both">Both</option>
                </select>
                <p v-if="form.errors.student_type" class="mt-1 text-sm text-red-600">{{ form.errors.student_type }}</p>
              </div>

              <!-- Age Display -->
              <div v-if="age !== null" class="col-span-2">
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                  <p class="text-sm text-blue-800">
                    <span class="font-semibold">Age:</span> {{ age }} years old
                  </p>
                </div>
              </div>

              <!-- Section: Additional Info -->
              <div class="col-span-2 mt-2">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 pb-2 mb-1">
                  Additional Information
                </h2>
              </div>

              <!-- WhatsApp Number -->
              <div>
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">
                  WhatsApp Number
                </label>
                <input
                  id="whatsapp_number"
                  v-model="form.whatsapp_number"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.whatsapp_number }"
                  placeholder="e.g., +92 300 1234567"
                />
                <p v-if="form.errors.whatsapp_number" class="mt-1 text-sm text-red-600">{{ form.errors.whatsapp_number }}</p>
              </div>

              <!-- B-Form No -->
              <div>
                <label for="b_form_no" class="block text-sm font-medium text-gray-700 mb-2">
                  B-Form No
                </label>
                <input
                  id="b_form_no"
                  v-model="form.b_form_no"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.b_form_no }"
                  placeholder="e.g., 12345-1234567-1"
                />
                <p v-if="form.errors.b_form_no" class="mt-1 text-sm text-red-600">{{ form.errors.b_form_no }}</p>
              </div>

              <!-- Blood Group -->
              <div>
                <label for="blood_group" class="block text-sm font-medium text-gray-700 mb-2">
                  Blood Group
                </label>
                <select
                  id="blood_group"
                  v-model="form.blood_group"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.blood_group }"
                >
                  <option value="">Select blood group</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                </select>
                <p v-if="form.errors.blood_group" class="mt-1 text-sm text-red-600">{{ form.errors.blood_group }}</p>
              </div>

              <!-- Religion -->
              <div>
                <label for="religion" class="block text-sm font-medium text-gray-700 mb-2">
                  Religion
                </label>
                <input
                  id="religion"
                  v-model="form.religion"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.religion }"
                  placeholder="e.g., Islam"
                />
                <p v-if="form.errors.religion" class="mt-1 text-sm text-red-600">{{ form.errors.religion }}</p>
              </div>

              <!-- Previous School -->
              <div class="col-span-2">
                <label for="previous_school" class="block text-sm font-medium text-gray-700 mb-2">
                  Previous School
                </label>
                <input
                  id="previous_school"
                  v-model="form.previous_school"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.previous_school }"
                  placeholder="Enter previous school name"
                />
                <p v-if="form.errors.previous_school" class="mt-1 text-sm text-red-600">{{ form.errors.previous_school }}</p>
              </div>

              <!-- Medical Condition -->
              <div class="col-span-2">
                <label for="medical_condition" class="block text-sm font-medium text-gray-700 mb-2">
                  Medical Condition
                </label>
                <textarea
                  id="medical_condition"
                  v-model="form.medical_condition"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-500': form.errors.medical_condition }"
                  placeholder="Describe any medical conditions or allergies..."
                ></textarea>
                <p v-if="form.errors.medical_condition" class="mt-1 text-sm text-red-600">{{ form.errors.medical_condition }}</p>
              </div>

              <!-- Section: Settings -->
              <div class="col-span-2 mt-2">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 pb-2 mb-1">
                  Settings
                </h2>
              </div>

              <!-- Is Hafiz -->
              <div>
                <div class="flex items-center">
                  <input
                    id="is_hafiz"
                    v-model="form.is_hafiz"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="is_hafiz" class="ml-2 block text-sm text-gray-900">
                    Is Hafiz (Memorized Quran)
                  </label>
                </div>
              </div>

              <!-- Is Active -->
              <div>
                <div class="flex items-center">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Set as Active Student
                  </label>
                </div>
              </div>

            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 sm:gap-4">
              <Button
                type="button"
                variant="secondary"
                @click="$inertia.visit(route('students.index'))"
                class="w-full sm:w-auto shadow-sm hover:shadow-md transition-all text-sm"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant="primary"
                :loading="form.processing"
                class="w-full sm:w-auto shadow-lg hover:shadow-xl transition-all text-sm"
              >
                <span v-if="!form.processing">Update Student</span>
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
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Button from '@/Components/Common/Button.vue'

// Props — student data + parents list passed from controller
const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  parents: {
    type: Array,
    default: () => []
  }
})

// Form — pre-populated with existing student data
const form = useForm({
  admission_no:      props.student.admission_no      ?? '',
  parent_id:         props.student.parent_id         ?? '',
  student_name:      props.student.student_name      ?? '',
  date_of_birth:     props.student.date_of_birth     ?? '',
  gender:            props.student.gender             ?? '',
  photo:             props.student.photo              ?? '',
  whatsapp_number:   props.student.whatsapp_number   ?? '',
  b_form_no:         props.student.b_form_no         ?? '',
  blood_group:       props.student.blood_group        ?? '',
  religion:          props.student.religion           ?? '',
  is_hafiz:          props.student.is_hafiz           ?? false,
  student_type:      props.student.student_type      ?? '',
  previous_school:   props.student.previous_school   ?? '',
  medical_condition: props.student.medical_condition ?? '',
  is_active:         props.student.is_active          ?? true,
})

// Calculate age from date_of_birth
const age = computed(() => {
  if (!form.date_of_birth) return null
  const today = new Date()
  const dob = new Date(form.date_of_birth)
  let years = today.getFullYear() - dob.getFullYear()
  const m = today.getMonth() - dob.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) years--
  return years >= 0 ? years : null
})

// Form submit
const submit = () => {
  form.put(route('students.update', props.student.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by controller
    }
  })
}
</script>