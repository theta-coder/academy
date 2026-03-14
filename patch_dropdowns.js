import fs from 'fs';
import path from 'path';

const filesToPatch = [
    'StudentScholarships/Create.vue',
    'StudentScholarships/Edit.vue',
    'StudentFeeConcessions/Create.vue',
    'StudentFeeConcessions/Edit.vue',
    'StudentInstallmentAssignments/Create.vue',
    'StudentInstallmentAssignments/Edit.vue'
];

filesToPatch.forEach(relPath => {
    let filePath = path.join('c:/htdocs/htdocs/academy-management/resources/js/Pages', relPath);
    if (!fs.existsSync(filePath)) return;

    let content = fs.readFileSync(filePath, 'utf8');

    // Fix selectedStudentId
    content = content.replace(
        /<FormSelect id="selectedStudentId"[^>]+v-model="form\.selectedStudentId"[^>]+>/g,
        `<FormSelect id="selectedStudentId" label="Student" v-model="selectedStudentId" :options="students" label-key="student_name" required @change="onStudentChange" placeholder="Select Student" />`
    );

    // Fix student_enrollment_id
    content = content.replace(
        /<FormSelect id="student_enrollment_id"[^>]+v-model="form\.student_enrollment_id"[^>]+>/g,
        `<FormSelect id="student_enrollment_id" label="Enrollment" v-model="form.student_enrollment_id" :options="enrollmentOptions" label-key="label" :error="form.errors.student_enrollment_id" required :loading="loadingEnrollments" placeholder="Select Enrollment" />`
    );

    fs.writeFileSync(filePath, content);
    console.log(`Patched ${relPath}`);
});

const classSubjectFiles = [
    'ClassSubjects/Create.vue',
    'ClassSubjects/Edit.vue',
    'Exams/Create.vue',
    'Exams/Edit.vue',
    'ExamResults/Create.vue',
    'ExamResults/Edit.vue'
];
classSubjectFiles.forEach(relPath => {
    let filePath = path.join('c:/htdocs/htdocs/academy-management/resources/js/Pages', relPath);
    if (!fs.existsSync(filePath)) return;

    let content = fs.readFileSync(filePath, 'utf8');

    // Patch branch -> class cascading
    if (content.includes('onBranchChange')) {
        content = content.replace(
            /<FormSelect id="branch_id"[^>]+v-model="form\.branch_id"[^>]+>/g,
            `<FormSelect id="branch_id" label="Branch" v-model="form.branch_id" :options="branches" label-key="branch_name" :error="form.errors.branch_id" required @change="onBranchChange" />`
        );
        content = content.replace(
            /<FormSelect id="class_id"[^>]+v-model="form\.class_id"[^>]+>/g,
            `<FormSelect id="class_id" label="Class" v-model="form.class_id" :options="classOptions" label-key="class_name" :error="form.errors.class_id" required :loading="loadingClasses" @change="onClassChange" />`
        );
        content = content.replace(
            /<FormSelect id="exam_id"[^>]+v-model="form\.exam_id"[^>]+>/g,
            `<FormSelect id="exam_id" label="Exam" v-model="form.exam_id" :options="examOptions" label-key="exam_name" :error="form.errors.exam_id" required :loading="loadingExams" @change="onExamChange" />`
        );
    }

    fs.writeFileSync(filePath, content);
    console.log(`Patched ${relPath}`);
});

