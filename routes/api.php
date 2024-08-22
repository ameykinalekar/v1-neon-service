<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Tenant\AcademicYearController;
use App\Http\Controllers\Tenant\AttendanceController;
use App\Http\Controllers\Tenant\BatchTypeController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Http\Controllers\Tenant\ExaminationController;
use App\Http\Controllers\Tenant\GradeController;
use App\Http\Controllers\Tenant\LessonController;
use App\Http\Controllers\Tenant\LibraryController;
use App\Http\Controllers\Tenant\MessageController;
use App\Http\Controllers\Tenant\OfsteadController;
use App\Http\Controllers\Tenant\ParentController;
use App\Http\Controllers\Tenant\RatingController;
use App\Http\Controllers\Tenant\StudentController;
use App\Http\Controllers\Tenant\StudyGroupController;
use App\Http\Controllers\Tenant\SubjectController;
use App\Http\Controllers\Tenant\TargetController;
use App\Http\Controllers\Tenant\TaskController;
use App\Http\Controllers\Tenant\TeacherAssistantController;
use App\Http\Controllers\Tenant\TeacherController;
use App\Http\Controllers\Tenant\YearGroupController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Authentication API
Route::post('auth', [AuthController::class, 'authenticate']);
Route::post('auth/validate', [AuthController::class, 'checkToken']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::post('email/test', [UserController::class, 'testEmail']);

//Open API
Route::post('tenant/exist', [TenantController::class, 'isTenantDomainExist']);
Route::post('email/exist', [UserController::class, 'isEmailExist']);

//Route::group(['middleware' => ['checkHeader']], function () {
Route::post('dropdown/portal-tenants', [CommonController::class, 'getPortalTenantList']);
Route::post('dropdown/portal-question-types', [CommonController::class, 'getQuestionTypeList']);
Route::post('get-tenant-config', [CommonController::class, 'geTenantConfig']);
Route::post('get-tenant-config-by-email', [CommonController::class, 'geTenantConfigByEmail']);
Route::post('dropdown/portal-question-levels', [CommonController::class, 'getQuestionLevelList']);
//});

//application dropdown purpose apis start
Route::post('dropdown/boards', [CommonController::class, 'getBoardList']);
Route::post('dropdown/countries', [CountryController::class, 'getDropdownCountries']);
Route::post('dropdown/genders', [CommonController::class, 'getGenderList']);
Route::post('get-modules', [CommonController::class, 'getModuleList']);
Route::post('get-currencies', [CommonController::class, 'getCurrencyList']);
Route::post('dropdown/salutations', [CommonController::class, 'getSalutationList']);
Route::post('dropdown/library-content-types', [CommonController::class, 'getLibraryContentTypeList']);
Route::post('dropdown/examination-status', [CommonController::class, 'getExaminationStatusList']);

//Portal Admin API
Route::group(['prefix' => '/', 'middleware' => ['jwt.verify']], function () {
    // Your portaladmin specific routes go here

    Route::post('user/update', [UserController::class, 'update_profile']);
    Route::post('user/change-password', [UserController::class, 'change_password']);

    //Boards
    Route::post('get-boards', [BoardController::class, 'getBoards']);
    Route::post('create-board', [BoardController::class, 'createBoard']);
    Route::post('get-board-by-id', [BoardController::class, 'getBoardById']);
    Route::post('update-board', [BoardController::class, 'updateBoard']);

    //Country
    Route::post('get-countries', [CountryController::class, 'getCountries']);
    Route::post('get-country-by-id', [CountryController::class, 'getCountryById']);
    Route::post('update-country', [CountryController::class, 'updateCountry']);

    //Trustee
    Route::post('get-trustees', [UserController::class, 'getTrustees']);
    Route::post('create-trustee', [UserController::class, 'createTrustee']);
    Route::post('get-trustee-by-id', [UserController::class, 'getTrusteeById']);
    Route::post('update-trustee', [UserController::class, 'updateTrustee']);

    Route::post('get-trustee-schools', [UserController::class, 'getTrusteeSchools']);

    //application dropdown purpose apis start
    Route::post('dropdown/trustees', [CommonController::class, 'getTrusteeList']);

    //application dropdown purpose apis end

    //Tenant
    Route::post('get-schools', [TenantController::class, 'getSaTenants']);
    Route::post('create-school', [TenantController::class, 'createSchool']);
    Route::post('get-school-by-id', [TenantController::class, 'getSchoolById']);
    Route::post('update-school', [TenantController::class, 'updateSchool']);

    //subscription plan master
    Route::post('get-subscription-plans', [SubscriptionPlanController::class, 'getSubscriptionPlans']);
    Route::post('create-subscription-plan', [SubscriptionPlanController::class, 'createSubscriptionPlan']);
    Route::post('get-subscription-plan-by-id', [SubscriptionPlanController::class, 'getSubscriptionPlanById']);
    Route::post('update-subscription-plan', [SubscriptionPlanController::class, 'updateSubscriptionPlan']);

    Route::post('get-plans-to-subscribe', [SubscriptionPlanController::class, 'getPlansToSubscribe']);
    Route::post('subscribe-plan', [SubscriptionPlanController::class, 'subscribePlan']);
    Route::post('get-subscribed-plans', [SubscriptionPlanController::class, 'getSubscribedPlans']);

    //settings
    Route::post('pa/settings', [SettingController::class, 'getPaSettingList']);
    Route::post('pa/set-settings', [SettingController::class, 'createOrUpdatePortalAdminSettings']);

    Route::post('view-invitation', [CommonController::class, 'getInvitationInfo']);
    Route::post('invitation-response', [CommonController::class, 'updateInvitationResponse']);
});

//Tenant API
Route::group(['prefix' => '/{any}/', 'middleware' => ['tenant']], function () {
    Route::post('auth', [AuthController::class, 'authenticateTenant']);
    Route::post('auth/validate', [AuthController::class, 'checkTokenTenant']);
    Route::post('forgot-password', [AuthController::class, 'forgotPasswordTenant']);
    Route::post('reset-password', [AuthController::class, 'resetPasswordTenant']);
    Route::post('email/exist', [UserController::class, 'isTenantEmailExist']);
    Route::post('nino/exist', [UserController::class, 'isTenantNIExist']);
});

Route::group(['prefix' => '/{any}/', 'middleware' => ['jwt.verify', 'tenant']], function () {
    // Your tenant specific routes go here

    Route::post('user/update', [UserController::class, 'tenant_update_profile']);
    Route::post('user/change-password', [UserController::class, 'tenant_change_password']);

    //TA home
    Route::post('/home', [UserController::class, 'getTaHomeSummary']);

    //TA settings
    Route::post('/settings', [SettingController::class, 'getTaSettingList']);
    Route::post('/set-settings', [SettingController::class, 'createOrUpdateTenantAdminSettings']);

    //TA Academic year master
    Route::post('/academic-years', [AcademicYearController::class, 'getAcademicYears']);
    Route::post('/create-academic-year', [AcademicYearController::class, 'createAcademicYear']);
    Route::post('/get-academic-year-by-id', [AcademicYearController::class, 'getAcademicYearById']);
    Route::post('/update-academic-year', [AcademicYearController::class, 'updateAcademicYear']);
    //Get dropdown academic years
    Route::post('/dropdown/get-academic-years', [AcademicYearController::class, 'dropdowdAcademicYears']);

    //TA Year group master
    Route::post('/year-groups', [YearGroupController::class, 'getYearGroups']);
    Route::post('/create-year-group', [YearGroupController::class, 'createYearGroup']);
    Route::post('/get-year-group-by-id', [YearGroupController::class, 'getYearGroupById']);
    Route::post('/update-year-group', [YearGroupController::class, 'updateYearGroup']);
    Route::post('/import-year-groups', [YearGroupController::class, 'importYearGroup']);

    //TA Grade master
    Route::post('/grades', [GradeController::class, 'getGrades']);
    Route::post('/create-grade', [GradeController::class, 'createGrade']);
    Route::post('/get-grade-by-id', [GradeController::class, 'getGradeById']);
    Route::post('/update-grade', [GradeController::class, 'updateGrade']);
    Route::post('/import-grades', [GradeController::class, 'importGrade']);

    //TA Get dropdown yeargroups as per academic_year_id provided
    Route::post('/dropdown/get-academicyearid-yeargroups', [YearGroupController::class, 'dropdownAcademicYearIdYeargroups']);
    Route::post('/dropdown/get-all-yeargroups', [YearGroupController::class, 'dropdownAllYeargroups']);

    //TA Get dropdown batch type
    Route::post('/dropdown/get-batch-types', [BatchTypeController::class, 'dropdowdBatchTypes']);

    //TA Subject master
    Route::post('/subjects', [SubjectController::class, 'getSubjects']);
    Route::post('/create-subject', [SubjectController::class, 'createSubject']);
    Route::post('/get-subject-by-id', [SubjectController::class, 'getSubjectById']);
    Route::post('/update-subject', [SubjectController::class, 'updateSubject']);
    Route::post('/import-subjects', [SubjectController::class, 'importSubject']);

    //TA Get dropdown subjects as per yeargroups provided
    Route::post('/dropdown/get-yeargroup-subjects', [SubjectController::class, 'dropdownYeargroupSubjects']);
    //TA Get dropdown all subjects
    Route::post('/dropdown/get-all-subjects', [SubjectController::class, 'dropdownSubjects']);

    //TA Lesson master
    Route::post('/lessons', [LessonController::class, 'getLessons']);
    Route::post('/create-lesson', [LessonController::class, 'createLesson']);
    Route::post('/create-lesson-teacher', [LessonController::class, 'createLessonTeacher']);
    Route::post('/get-lesson-by-id', [LessonController::class, 'getLessonById']);
    Route::post('/update-lesson', [LessonController::class, 'updateLesson']);
    Route::post('/update-lesson-teacher', [LessonController::class, 'updateLessonTeacher']);
    Route::post('/import-lessons', [LessonController::class, 'importLesson']);

    Route::post('/dropdown/get-subjectid-lessons', [LessonController::class, 'dropdownSubjectIdLessons']);

    //TA Topic master
    Route::post('/topics', [LessonController::class, 'getTopics']);
    Route::post('/create-topic', [LessonController::class, 'createTopic']);
    Route::post('/get-topic-by-id', [LessonController::class, 'getTopicById']);
    Route::post('/update-topic', [LessonController::class, 'updateTopic']);
    Route::post('/import-topics', [LessonController::class, 'importTopic']);

    //TA Sub Topic master
    Route::post('/sub-topics', [LessonController::class, 'getSubTopics']);

    Route::post('/dropdown/get-lessonid-topics', [LessonController::class, 'dropdownLessonIdTopics']);
    Route::post('/dropdown/get-topicid-subtopics', [LessonController::class, 'dropdownTopicIdSubTopics']);

    //TA Student
    Route::post('students', [StudentController::class, 'getStudents']);
    Route::post('create-student', [StudentController::class, 'createStudent']);
    Route::post('get-student-by-id', [StudentController::class, 'getStudentById']);
    Route::post('update-student', [StudentController::class, 'updateStudent']);
    Route::post('initial-subject-grades', [StudentController::class, 'mapStudentSubjectGrades']);

    Route::post('get-user-subjects', [CommonController::class, 'getSubjectsByUserId']);
    Route::post('get-students-by-subjectid', [StudentController::class, 'getStudentListBySubjectId']);
    Route::post('student/update-cover-image', [StudentController::class, 'updateStudentCoverImage']);
    Route::post('student/update-profile-image', [StudentController::class, 'updateStudentProfileImage']);
    Route::post('/import-students', [StudentController::class, 'importStudent']);

    //TA Teacher
    Route::post('teachers', [TeacherController::class, 'getTeachers']);
    Route::post('create-teacher', [TeacherController::class, 'createTeacher']);
    Route::post('get-teacher-by-id', [TeacherController::class, 'getTeacherById']);
    Route::post('update-teacher', [TeacherController::class, 'updateTeacher']);
    Route::post('/import-teachers', [TeacherController::class, 'importTeacher']);

    //TA Department master
    Route::post('/departments', [DepartmentController::class, 'getDepartments']);
    Route::post('/create-department', [DepartmentController::class, 'createDepartment']);
    Route::post('/get-department-by-id', [DepartmentController::class, 'getDepartmentById']);
    Route::post('/update-department', [DepartmentController::class, 'updateDepartment']);
    //TA Get dropdown departments
    Route::post('/dropdown/get-departments', [DepartmentController::class, 'dropdowndDepartments']);
    Route::post('/import-departments', [DepartmentController::class, 'importDepartment']);

    //TA Teacher Assistant
    Route::post('teacher-assistants', [TeacherAssistantController::class, 'getTeacherAssistants']);
    Route::post('create-teacher-assistant', [TeacherAssistantController::class, 'createTeacherAssistant']);
    Route::post('get-teacher-assistant-by-id', [TeacherAssistantController::class, 'getTeacherAssistantById']);
    Route::post('update-teacher-assistant', [TeacherAssistantController::class, 'updateTeacherAssistant']);

    //TA Employee
    Route::post('employees', [EmployeeController::class, 'getEmployees']);
    Route::post('create-employee', [EmployeeController::class, 'createEmployee']);
    Route::post('get-employee-by-id', [EmployeeController::class, 'getEmployeeById']);
    Route::post('update-employee', [EmployeeController::class, 'updateEmployee']);

    //TA Parent
    Route::post('parents', [ParentController::class, 'getParents']);
    Route::post('create-parent', [ParentController::class, 'createParent']);
    Route::post('get-parent-by-id', [ParentController::class, 'getParentById']);
    Route::post('update-parent', [ParentController::class, 'updateParent']);

    Route::post('examination/results', [ExaminationController::class, 'getExaminationResults']);

    //Message
    Route::post('message/create', [MessageController::class, 'createMessage']);
    Route::post('message/get-user-messages', [MessageController::class, 'getUserMessages']);
    Route::post('message/get-user-message-details', [MessageController::class, 'getUserMessagesDetails']);

    //Schedule task
    Route::post('task/create', [TaskController::class, 'createTask']);
    Route::post('task/update', [TaskController::class, 'updateTask']);
    Route::post('task/delete', [TaskController::class, 'deleteTask']);
    Route::post('task/get/async', [TaskController::class, 'getTaskAsync']);
    Route::post('task/get-by-id', [TaskController::class, 'getTaskById']);
    Route::post('task/get/homework-created', [TaskController::class, 'getTaskHwCreated']);
    Route::post('task/get/consumer/homework', [TaskController::class, 'getTaskConsumerHomework']);
    Route::post('task/get/consumers', [TaskController::class, 'getTaskConsumers']);

    Route::post('all-active-students', [StudentController::class, 'getAllActiveStudents']);
    Route::post('all-active-teachers', [TeacherController::class, 'getAllActiveTeachers']);

    //Rating & review
    Route::post('rating/create', [RatingController::class, 'createRating']);
    Route::post('rating/fetch-by-lesson', [RatingController::class, 'getExistingRatingByLesson']);
    Route::post('rating/consumer-by-lesson', [RatingController::class, 'getConsumerRatingByLesson']);
    Route::post('rating/consumer-by-lesson-id', [RatingController::class, 'getConsumerRatingByLessonId']);

    //TA Ofstead
    Route::post('/ofstead/import-finance', [OfsteadController::class, 'importOfsteadFinance']);
    Route::post('/ofstead/finance', [OfsteadController::class, 'getOfsteadFinance']);
    Route::post('/ofstead/delete-finance', [OfsteadController::class, 'deleteOfsteadFinanceByYear']);
    Route::post('/ofstead/get-finance-year', [OfsteadController::class, 'getOfsteadFinanceByYear']);

    Route::post('/ofstead/get-finance-expenditure', [OfsteadController::class, 'getOfsteadFinanceExpenditure']);

    //TU Student
    Route::post('my-courses', [StudentController::class, 'getStudentMyCourses']);
    //Route::post('teacher/subjects', [TeacherController::class, 'getTeacherSubjects']);
    Route::post('/student/get-subjectid-lessons', [StudentController::class, 'studentSubjectIdLessons']);
    Route::post('/student/get-profile-completion', [StudentController::class, 'getStudentProfileCompletion']);

    Route::post('examination/consumer-examinations', [ExaminationController::class, 'getConsumerExaminations']);
    Route::post('examination/consumer-examination-questions', [ExaminationController::class, 'getConsumerExaminationQuestions']);

    Route::post('examination/consumer-examination-save', [ExaminationController::class, 'saveConsumerExamination']);
    Route::post('examination/consumer-examinations-reviewed', [ExaminationController::class, 'getConsumerExaminationReviewed']);

    Route::post('study-groups', [StudyGroupController::class, 'getStudyGroups']);
    Route::post('studygroup/create', [StudyGroupController::class, 'createStudyGroup']);
    Route::post('/get-studygroup-by-id', [StudyGroupController::class, 'getStudyGroupById']);
    Route::post('studygroup/update', [StudyGroupController::class, 'updateStudyGroup']);
    Route::post('studygroup/join', [StudyGroupController::class, 'joinStudyGroup']);
    Route::post('studygroup/view', [StudyGroupController::class, 'viewStudyGroup']);
    Route::post('studygroup/add-content', [StudyGroupController::class, 'addContentStudyGroup']);
    Route::post('studygroup/invite', [StudyGroupController::class, 'inviteToStudyGroup']);

    Route::post('user/attendances', [AttendanceController::class, 'getUserAttendances']);
    Route::post('user/all-attendances-for-rating', [AttendanceController::class, 'getUserAllAttendancesLessonsForRating']);

    //common to all tenant user
    Route::post('library/supported-content-types', [LibraryController::class, 'getLibrarySupportedContentTypes']);

    //TU Teacher
    Route::post('question/categories', [ExaminationController::class, 'getQuestionCategories']);
    Route::post('question/category/create', [ExaminationController::class, 'createQuestionCategory']);
    Route::post('question/category/get-by-id', [ExaminationController::class, 'getQuestionCategoryById']);
    Route::post('question/category/update', [ExaminationController::class, 'updateQuestionCategory']);

    Route::post('/dropdown/question/categories', [ExaminationController::class, 'dropdownQuestionCategoryArray']);

    Route::post('question/create', [ExaminationController::class, 'createQuestion']);
    Route::post('examination/create', [ExaminationController::class, 'createExamination']);
    Route::post('examination/create-with-questions', [ExaminationController::class, 'createExaminationWithQuestions']);
    Route::post('examination/create-with-questions-new', [ExaminationController::class, 'createExaminationWithQuestionsNew']);
    Route::post('examination/creator-examinations', [ExaminationController::class, 'getCreatorExaminations']);

    Route::post('examination/all-creator-examinations', [ExaminationController::class, 'getCreatorAllExaminations']);

    Route::post('examination/question/create-or-update', [ExaminationController::class, 'createOrUpdateExaminationQuestion']);
    Route::post('examination/get-by-id', [ExaminationController::class, 'getExaminationById']);
    Route::post('examination/question/get-by-examination_question_id', [ExaminationController::class, 'getExaminationQuestionById']);
    Route::post('examination/remove-question-page', [ExaminationController::class, 'removeExaminationQuestionByPageId']);
    Route::post('examination/update-status', [ExaminationController::class, 'updateExaminationStatus']);
    Route::post('examination/update-hw', [ExaminationController::class, 'updateExaminationHw']);

    Route::post('/examination/import-quiz-questions', [ExaminationController::class, 'importQuizQuestions']);

    Route::post('/examination/user-result', [ExaminationController::class, 'getUserExaminationResult']);

    Route::post('teacher/subjects', [TeacherController::class, 'getTeacherSubjects']);
    Route::post('teacher/lessons', [TeacherController::class, 'getTeacherLessons']);
    Route::post('teacher/all-lessons', [TeacherController::class, 'getTeacherAllLessons']);
    Route::post('teacher/students', [TeacherController::class, 'getTeacherStudents']);
    Route::post('teacher/students-by-yrgpid', [TeacherController::class, 'getTeacherStudentsByYrGpId']);
    Route::post('teacher/students-by-subjectid', [TeacherController::class, 'getTeacherStudentsBySubjectId']);
    Route::post('teacher/teacher-assistants', [TeacherController::class, 'getTeacherTa']);

    Route::post('teacher/course-status', [TeacherController::class, 'getTeacherCourseStatus']);
    Route::post('student/course-status', [StudentController::class, 'getCourseStatus']);

    Route::post('/teacher/get-lessonid-skillmap', [TeacherController::class, 'getLessonIdSkillMap']);
    Route::post('/teacher/get-student-lessonid-skillmap', [TeacherController::class, 'getStudentLessonIdSkillMap']);
    Route::post('/student/get-lessonid-skillmap', [StudentController::class, 'getLessonIdSkillMap']);

    Route::post('examination/creator-examinations-for-review', [ExaminationController::class, 'getCreatorExaminationForReview']);

    Route::post('examination/get-examination-submission-info', [ExaminationController::class, 'getSubmittedExaminationInfo']);

    Route::post('examination/creator-examination-review-save', [ExaminationController::class, 'saveCreatorReviewExamination']);

    Route::post('examination/creator-examinations-reviewed', [ExaminationController::class, 'getCreatorExaminationReviewed']);

    Route::post('upload-to-library', [LibraryController::class, 'uploadToLibrary']);
    Route::post('update-library-item', [LibraryController::class, 'updateLibraryItem']);
    Route::post('get-library-item', [LibraryController::class, 'getLibraryItem']);
    Route::post('/library/get-subjectid-lessons', [LibraryController::class, 'librarySubjectIdLessons']);
    Route::post('/library/get-content-by-lessonntype', [LibraryController::class, 'libraryLessonIdContentByType']);

    //TUT Attendance
    Route::post('attendance/create-or-update', [AttendanceController::class, 'createOrUpdateAttendance']);
    Route::post('attendances', [AttendanceController::class, 'getAttendances']);
    Route::post('attendance/delete', [AttendanceController::class, 'deleteAttendance']);
    Route::post('attendance/view-list', [AttendanceController::class, 'viewAttendanceList']);
    Route::post('/import-attendance', [AttendanceController::class, 'importAttendance']);

    Route::post('teacher/attendance-graph-data', [AttendanceController::class, 'getAttendanceGraphDataTeacher']);
    Route::post('get-pupil-count', [StudentController::class, 'getPupilCount']);

    //P Parent
    Route::post('generate-child-link', [ParentController::class, 'generateChildLink']);
    Route::post('validate-child', [ParentController::class, 'validateLinkChild']);
    Route::post('parent/children', [ParentController::class, 'getChildren']);
    Route::post('parent/examination/results', [ExaminationController::class, 'getParentExaminationResults']);
    Route::post('parent/students', [ParentController::class, 'getStudents']);
    Route::post('parent/attendances', [AttendanceController::class, 'getParentUserAttendances']);

    //Student target
    Route::post('/targets', [TargetController::class, 'getTargets']);
    Route::post('/create-target', [TargetController::class, 'createTarget']);
    Route::post('/get-target-by-id', [TargetController::class, 'getTargetById']);
    Route::post('/update-target', [TargetController::class, 'updateTarget']);
    Route::post('/consumer/targets', [TargetController::class, 'getConsumerTargets']);
});

// \Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
//     echo '<pre>';
//     var_dump($query->sql);
//     var_dump($query->bindings);
//     var_dump($query->time);
//     echo '</pre>';
// });
return false;
