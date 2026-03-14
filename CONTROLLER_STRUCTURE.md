# Controller Structure

### AcademicYearController.php

- index
- create
- edit
- store
- update
- destroy
- activate
- current
- dropdown

### AcademyPaymentAccountController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### ActivityLogController.php

- index
- show

### AnnouncementController.php

- index
- create
- edit
- store
- update
- destroy

### AssignmentController.php

- index
- create
- edit
- store
- update
- destroy

### AssignmentSubmissionController.php

- index
- create
- edit
- store
- update
- destroy

### AttendanceController.php

- index
- create
- store
- show
- edit
- update
- destroy
- getStudentsBySection
- report

### Branchclasscontroller.php

- index
- create
- edit
- store
- update
- destroy

### BranchController.php

- index
- create
- edit
- branchClasses
- store
- update
- destroy
- dropdown
- getClasses

### ChequeTrackingController.php

- index
- create
- store
- show
- edit
- update
- destroy

### ClassController.php

- index
- create
- edit
- store
- update
- destroy
- show
- dropdown
- reorder

### ClassSectionController.php

- index
- create
- edit
- store
- update
- destroy
- getBranches
- getClassesByBranch
- getAvailableSections
- getSectionsByBranchClass
- toggleStatus
- updateCapacity
- capacityStats

### ClassSectionSubjectController.php

- index
- create
- edit
- store
- update
- destroy
- getBranches
- getClassesByBranch
- getClassSectionsByBranchClass
- getSubjects
- getSubjectGroups
- getAssignedSubjects
- subjectStats
- bulkDelete

### ClassSubjectController.php

- index
- create
- edit
- groupSubjects
- store
- update
- destroy
- getClassSections
- getSectionSubjects

### ExamController.php

- index
- create
- store
- show
- edit
- update
- destroy
- dropdown

### ExamResultController.php

- index
- create
- store
- show
- edit
- update
- destroy
- reportCard
- getStudentsByExam

### ExamSubjectController.php

- index
- create
- store
- show
- edit
- update
- destroy
- getByExam

### FeeAdvanceAdjustmentController.php

- index
- create
- edit
- store
- update
- destroy

### FeeCollectionSummaryController.php

- index
- create
- edit
- store
- update
- destroy

### FeeConcessionTypeController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### FeeFineRuleController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### FeePaymentController.php

- __construct
- index
- create
- edit
- show
- store
- update
- destroy
- dropdown

### FeeRefundController.php

- index
- create
- edit
- store
- update
- destroy

### FeeStructureController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### FeeTypeController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### FeeVoucherController.php

- __construct
- index
- generateMonthlyVouchers
- recalculate
- create
- edit
- store
- update
- destroy

### FeeVoucherEditHistoryController.php

- index
- create
- store
- show
- edit
- update
- destroy

### FeeVoucherFineController.php

- index
- create
- edit
- store
- update
- destroy

### FeeWaiverController.php

- index
- create
- edit
- store
- update
- destroy

### GradeScaleController.php

- index
- create
- store
- show
- edit
- update
- destroy
- getGrade
- dropdown

### InstallmentPlanController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### InstallmentScheduleController.php

- index
- create
- edit
- store
- update
- destroy

### MessageController.php

- index
- create
- show
- store
- destroy
- toggleStar
- markAsRead

### NotificationController.php

- index
- create
- edit
- store
- update
- destroy
- markAsRead
- markAllAsRead

### OnlinePaymentProofController.php

- index
- create
- edit
- store
- update
- destroy

### ParentController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### PermissionController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### ProfileController.php

- edit
- update
- destroy

### RoleController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### ScholarshipController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### SectionController.php

- index
- create
- store
- show
- edit
- update
- destroy
- dropdown

### SettingController.php

- index
- create
- edit
- store
- update
- destroy

### SiblingDiscountRuleController.php

- index
- create
- edit
- store
- update
- destroy

### StudentAdvanceBalanceController.php

- index
- create
- edit
- store
- update
- destroy

### StudentController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### StudentEnrollmentController.php

- index
- create
- edit
- classesByBranch
- sectionsByBranchClass
- store
- update
- destroy

### StudentFeeConcessionController.php

- index
- create
- edit
- enrollmentsByStudent
- store
- update
- destroy

### StudentInstallmentAssignmentController.php

- index
- create
- edit
- enrollmentsByStudent
- store
- update
- destroy

### StudentLedgerController.php

- index
- create
- edit
- store
- update
- destroy

### StudentScholarshipController.php

- index
- create
- edit
- enrollmentsByStudent
- store
- update
- destroy

### SubjectController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### SubjectGroupController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### TeacherController.php

- index
- create
- store
- show
- edit
- update
- destroy
- dropdown

### TimetableController.php

- index
- create
- edit
- store
- update
- destroy

### UserController.php

- index
- create
- edit
- store
- update
- destroy
- dropdown

### VoucherDiscountBreakdownController.php

- index
- show

### AuthenticatedSessionController.php

- create
- store
- destroy

### ConfirmablePasswordController.php

- show
- store

### EmailVerificationNotificationController.php

- store

### EmailVerificationPromptController.php

- __invoke

### NewPasswordController.php

- create
- store

### PasswordController.php

- update

### PasswordResetLinkController.php

- create
- store

### RegisteredUserController.php

- create
- store

### VerifyEmailController.php

- __invoke
