<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    students: Array,
    academicYears: Array,
});

const form = useForm({
    student_enrollment_id: '',
    from_academic_year_id: '',
    to_academic_year_id: '',
    original_outstanding: '',
    breakup: {},
    recovered_amount: '',
    remaining_amount: '',
    carry_forward_date: '',
    status: 'unpaid',
    notes: '',
});

const enrollments = ref([]);

const fetchEnrollments = async () => {
    if (!form.student_id) {
        enrollments.value = [];
        return;
    }
    try {
        const response = await fetch(`/api/previous-year-balances/enrollments-by-student/${form.student_id}`);
        enrollments.value = await response.json();
    } catch (error) {
        console.error('Error fetching enrollments:', error);
    }
};

const submit = () => {
    form.post(route('previous-year-balances.store'));
};
</script>

<template>
    <Head title="Carry Forward Balance" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Carry Forward Previous Year Balance</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Student -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Student *</label>
                                <select v-model="form.student_id" @change="fetchEnrollments"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Student</option>
                                    <option v-for="student in students" :key="student.id" :value="student.id">
                                        {{ student.student_name }} ({{ student.admission_no }})
                                    </option>
                                </select>
                            </div>

                            <!-- Enrollment -->
                            <div v-if="enrollments.length > 0">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Enrollment *</label>
                                <select v-model="form.student_enrollment_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Enrollment</option>
                                    <option v-for="enrollment in enrollments" :key="enrollment.id"
                                        :value="enrollment.id">
                                        {{ enrollment.class_name }} - {{ enrollment.section_name }} ({{
                                            enrollment.academic_year }})
                                    </option>
                                </select>
                                <span v-if="form.errors.student_enrollment_id" class="text-red-500 text-sm">
                                    {{ form.errors.student_enrollment_id }}
                                </span>
                            </div>

                            <!-- Academic Years -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">From Academic Year *</label>
                                    <select v-model="form.from_academic_year_id" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Year</option>
                                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                            {{ year.year_name }}
                                        </option>
                                    </select>
                                    <span v-if="form.errors.from_academic_year_id" class="text-red-500 text-sm">
                                        {{ form.errors.from_academic_year_id }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">To Academic Year *</label>
                                    <select v-model="form.to_academic_year_id" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Year</option>
                                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                            {{ year.year_name }}
                                        </option>
                                    </select>
                                    <span v-if="form.errors.to_academic_year_id" class="text-red-500 text-sm">
                                        {{ form.errors.to_academic_year_id }}
                                    </span>
                                </div>
                            </div>

                            <!-- Amount Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Original Outstanding (Rs.) *</label>
                                    <input v-model.number="form.original_outstanding" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span v-if="form.errors.original_outstanding" class="text-red-500 text-sm">
                                        {{ form.errors.original_outstanding }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Recovered Amount (Rs.)</label>
                                    <input v-model.number="form.recovered_amount" type="number" step="0.01"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Remaining Amount (Rs.) *</label>
                                    <input v-model.number="form.remaining_amount" type="number" step="0.01" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span v-if="form.errors.remaining_amount" class="text-red-500 text-sm">
                                        {{ form.errors.remaining_amount }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                                <select v-model="form.status" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                    <option value="cleared">Cleared</option>
                                </select>
                                <span v-if="form.errors.status" class="text-red-500 text-sm">
                                    {{ form.errors.status }}
                                </span>
                            </div>

                            <!-- Carry Forward Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Carry Forward Date *</label>
                                <input v-model="form.carry_forward_date" type="date" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <span v-if="form.errors.carry_forward_date" class="text-red-500 text-sm">
                                    {{ form.errors.carry_forward_date }}
                                </span>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea v-model="form.notes" rows="3"
                                    placeholder="Breakup details or additional information..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-4 pt-4">
                                <Link :href="route('previous-year-balances.index')"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                                    Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50">
                                    Carry Forward Balance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
