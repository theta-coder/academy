<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model_type', 100)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('action', 50); // create, update, delete, login, logout
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
        });

        // 2. Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_id', 50)->nullable()->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('cnic', 20)->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('qualification', 200)->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('specialization', 200)->nullable();
            $table->date('joining_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->string('bank_account', 100)->nullable();
            $table->string('photo', 255)->nullable();
            $table->json('documents')->nullable();
            $table->enum('status', ['active', 'inactive', 'resigned', 'terminated'])->default('active');
            $table->timestamps();
        });

        // 3. Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->text('content');
            $table->string('image', 255)->nullable();
            $table->enum('target_audience', ['all', 'students', 'parents', 'teachers', 'staff'])->default('all');
            $table->unsignedBigInteger('target_class_id')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('show_on_dashboard')->default(true);
            $table->boolean('send_notification')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->boolean('send_email')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('target_class_id')->references('id')->on('classes')->nullOnDelete();
        });

        // 4. Assignments
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedInteger('total_marks')->default(0);
            $table->date('issue_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->boolean('late_submission_allowed')->default(false);
            $table->decimal('late_penalty_percent', 5, 2)->default(0);
            $table->enum('status', ['active', 'closed', 'draft'])->default('active');
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });

        // 5. Assignment Submissions
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->datetime('submission_date')->nullable();
            $table->json('submitted_files')->nullable();
            $table->text('content')->nullable();
            $table->decimal('obtained_marks', 8, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('is_late')->default(false);
            $table->decimal('penalty_applied', 8, 2)->default(0);
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('graded_at')->nullable();
            $table->enum('status', ['pending', 'submitted', 'late', 'graded'])->default('pending');
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });

        // 6. Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'leave', 'half_day'])->default('present');
            $table->string('leave_type', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'date']);
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });

        // 7. Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 200);
            $table->string('exam_code', 50)->nullable()->unique();
            $table->enum('exam_type', ['midterm', 'final', 'monthly', 'weekly', 'quiz', 'practical'])->default('midterm');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('result_date')->nullable();
            $table->unsignedInteger('total_marks')->default(100);
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });

        // 8. Exam Subjects
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('exam_date')->nullable();
            $table->time('exam_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->unsignedSmallInteger('theory_marks')->default(80);
            $table->unsignedSmallInteger('practical_marks')->default(20);
            $table->unsignedSmallInteger('pass_marks')->default(33);
            $table->timestamps();
            $table->unique(['exam_id', 'subject_id']);
        });

        // 9. Exam Results
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('theory_marks', 8, 2)->default(0);
            $table->decimal('practical_marks', 8, 2)->default(0);
            $table->decimal('obtained_marks', 8, 2)->default(0);
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->string('grade', 5)->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();
            $table->unsignedSmallInteger('rank_in_class')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_absent')->default(false);
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id', 'subject_id']);
        });

        // 10. Fees (umbrella)
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason', 255)->nullable();
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'waived'])->default('pending');
            $table->boolean('is_waived')->default(false);
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('waiver_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });

        // 11. Grade Scales
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 50);
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->string('grade', 5);
            $table->decimal('grade_point', 4, 2)->default(0);
            $table->string('remarks', 200)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 12. Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 255)->nullable();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('parent_message_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->datetime('read_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_deleted_by_sender')->default(false);
            $table->boolean('is_deleted_by_receiver')->default(false);
            $table->timestamps();
            $table->foreign('parent_message_id')->references('id')->on('messages')->nullOnDelete();
        });

        // 13. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 50)->nullable(); // fee, exam, attendance, announcement
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('action_url', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->datetime('read_at')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->timestamps();
        });

        // 14. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 100)->nullable();
            $table->string('key', 150);
            $table->text('value')->nullable();
            $table->string('data_type', 20)->default('string'); // string, number, boolean, json
            $table->string('description', 255)->nullable();
            $table->boolean('is_editable')->default(true);
            $table->timestamps();
            $table->unique(['branch_id', 'key']);
        });

        // 15. Timetables
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->unsignedTinyInteger('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_no', 20)->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->boolean('is_break')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
            $table->unique(['branch_id', 'class_id', 'section_id', 'day_of_week', 'period_number'], 'timetable_unique_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('grade_scales');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_subjects');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('activity_logs');
    }
};
