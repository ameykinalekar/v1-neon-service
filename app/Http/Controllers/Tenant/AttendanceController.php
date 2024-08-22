<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\AttendanceImport;
use App\Models\Attendance;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\UserSibling;
use App\Models\UserSubject;
use App\Models\ViewStudent;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/attendances",
     * summary="Get attendance data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_date_from", type="date", default=null),
     *       @OA\Property(property="search_date_to", type="date", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getAttendances(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;
                $search_lesson_id = $request->search_lesson_id;
                $search_date_from = $request->search_date_from ?? null;
                $search_date_to = $request->search_date_to ?? null;

                $subjectArray = Subject::where(function ($query) use ($search_subject_id, $search_academic_year_id, $search_year_group_id) {
                    if ($search_academic_year_id != null) {
                        $query->where('academic_year_id', $search_academic_year_id);
                    }
                    if ($search_year_group_id != null) {
                        $query->where('year_group_id', $search_year_group_id);
                    }
                    if ($search_subject_id != null) {
                        $query->where('subject_id', $search_subject_id);
                    }
                })
                    ->distinct('subject_id')
                    ->pluck('subject_id')
                    ->toArray();

                $listing = Attendance::select('attendance_date', 'subject_id', 'lesson_id', DB::raw('max(created_at) as created_at'), DB::raw('max(updated_at) as updated_at'), DB::raw('COALESCE((SELECT count(*) FROM attendances ia where ia.lesson_id=attendances.lesson_id and ia.attendance_date=attendances.attendance_date and ia.subject_id=attendances.subject_id and ia.is_present=1),0) as total_present'), DB::raw('COALESCE((SELECT count(*) FROM attendances ia where ia.lesson_id=attendances.lesson_id and ia.attendance_date=attendances.attendance_date and ia.subject_id=attendances.subject_id and ia.is_present=0),0) as total_absent'), DB::raw('COALESCE((SELECT count(students.user_id) FROM students inner join user_subjects on students.user_id=user_subjects.user_id where user_subjects.subject_id=attendances.subject_id and date(user_subjects.created_at)<=attendances.attendance_date),0) as total_enrolled'))
                    ->with('lesson', 'lesson.subject', 'lesson.subject.yeargroup', 'lesson.subject.academicyear')
                    ->whereIn('subject_id', $subjectArray)
                    ->where(function ($query) use ($search_date_from, $search_date_to, $search_lesson_id) {
                        if ($search_lesson_id != null) {
                            $query->where('lesson_id', $search_lesson_id);
                        }
                        if ($search_date_from != null) {
                            $query->where('attendance_date', '>=', $search_date_from);
                        }
                        if ($search_date_to != null) {
                            $query->where('attendance_date', '<=', $search_date_to);
                        }
                    })
                    ->groupBy('attendance_date', 'subject_id', 'lesson_id')
                    ->orderBy(DB::raw('date(attendance_date)'), 'asc')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Attendance listing', 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/attendance/create-or-update",
     * summary="create-or-update attendance data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="add new attendances",
     *    @OA\JsonContent(
     *       required={"attendance_date","subject_id","lesson_id"},
     *       @OA\Property(property="attendance_date", type="date"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="user_details", type="array", description="List of attendance users",
     *       @OA\Items(
     *          @OA\Property(
     *              property="user_id",
     *              description="student user id",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="is_present",
     *              description="mark 1 if present else 0",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="remarks",
     *              description="attendance remarks",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant attendance data"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createOrUpdateAttendance(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $userDetails = $request->user_details ?? array();
                if (count($userDetails) > 0) {
                    for ($i = 0; $i < count($userDetails); $i++) {
                        $existingAttendance = Attendance::where('attendance_date', $request->attendance_date)
                            ->where('subject_id', $request->subject_id)
                            ->where('lesson_id', $request->lesson_id)
                            ->where('user_id', $userDetails[$i]['user_id'])
                            ->first();
                        // dd($existingAttendance);
                        if (empty($existingAttendance)) {
                            $model = new Attendance;
                            $model->attendance_date = $request->attendance_date ?? null;
                            $model->subject_id = $request->subject_id ?? '';
                            $model->lesson_id = $request->lesson_id ?? '';
                            $model->created_by = $user->user_id ?? '';
                            $model->user_id = $userDetails[$i]['user_id'] ?? '';
                            $model->is_present = $userDetails[$i]['is_present'] ?? 0;
                            $model->remarks = $userDetails[$i]['remarks'] ?? '';
                            // dd($model);
                            // $model->status = GlobalVars::ACTIVE_STATUS;
                            $model->save();
                        } else {
                            $model = Attendance::find($existingAttendance->attendance_id);
                            $model->attendance_date = $request->attendance_date ?? null;
                            $model->subject_id = $request->subject_id ?? '';
                            $model->lesson_id = $request->lesson_id ?? '';
                            $model->created_by = $user->user_id ?? '';
                            $model->user_id = $userDetails[$i]['user_id'] ?? '';
                            $model->is_present = $userDetails[$i]['is_present'] ?? 0;
                            $model->remarks = $userDetails[$i]['remarks'] ?? '';
                            // $model->status = GlobalVars::ACTIVE_STATUS;
                            $model->save();

                        }
                    }
                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Attendance submitted successfully.'), 'error' => []];
                    return response()->json($reponse, 200);

                }

            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }
        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/{subdomain}/import-attendance",
     *     summary="Bulk import attendance",
     *     tags={"Tenant"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="subject_id",
     *                     type="int",
     *                     description="subject Id"
     *                 ),
     *                 @OA\Property(
     *                     property="lesson_id",
     *                     type="int",
     *                     description="lesson Id"
     *                 ),
     *                 @OA\Property(
     *                     property="import_file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload",
     *                     default=null
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Attendance bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importAttendance(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $file = $request->file('import_file');
                // dd($file);
                if ($file) {

                    $data = \Excel::import(new AttendanceImport($request->subject_id, $request->lesson_id, $user->user_id), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk attendance processed successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No file provided for import.']];
                    return response()->json($reponse, 400);
                }

            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }
        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/attendance/view-list",
     * summary="View attendance by date & lesson",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass encrpted record year",
     *    @OA\JsonContent(
     *       required={"attendance_date","lesson_id"},
     *       @OA\Property(property="attendance_date", type="date"),
     *       @OA\Property(property="lesson_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Delete attendance"),
     * @OA\Response(response="401", description="Attendance not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function viewAttendanceList(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $attendance_date = CommonHelper::decryptId($request->attendance_date);
                $lesson_id = CommonHelper::decryptId($request->lesson_id);

                $lesson_info = Lesson::find($lesson_id);

                $userArray = UserSubject::where('user_subjects.subject_id', $lesson_info->subject_id)
                    ->where(function ($query) use ($attendance_date) {
                        if ($attendance_date != null) {
                            $query->where('created_at', '<=', $attendance_date);
                        }
                    })
                    ->pluck('user_id')->toArray();

                // dd($userArray);

                $listing = ViewStudent::leftJoin("attendances", function ($join) use ($attendance_date, $lesson_id) {
                    $join->on("attendances.user_id", "=", "students.user_id")
                        ->where("attendances.attendance_date", "=", $attendance_date)
                        ->where("attendances.lesson_id", "=", $lesson_id);
                })
                    ->select('students.*', 'attendances.attendance_date', 'attendances.subject_id', 'attendances.lesson_id', 'attendances.is_present', 'attendances.remarks', DB::raw('(SELECT subject_name FROM subjects where subjects.subject_id=attendances.subject_id) as subject_name'), DB::raw('(SELECT lesson_name FROM lessons where lessons.lesson_id=attendances.lesson_id) as lesson_name'), DB::raw('(SELECT year_groups.name FROM subjects inner join year_groups on year_groups.year_group_id=subjects.year_group_id where subjects.subject_id=attendances.subject_id) as yeargroup_name'), DB::raw('(SELECT academic_years.academic_year FROM subjects inner join academic_years on academic_years.academic_year_id=subjects.academic_year_id where subjects.subject_id=attendances.subject_id) as academic_year'))

                    ->whereIn('students.user_id', $userArray)

                    ->get();
                // $listing = Attendance::with('user', 'lesson', 'lesson.subject', 'lesson.subject.yeargroup', 'lesson.subject.academicyear')->where('attendance_date', '=', $attendance_date)->where('lesson_id', '=', $lesson_id)->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'View listing Attendance of ' . $attendance_date, 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/attendance/delete",
     * summary="Delete attendance by date & lesson",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass encrpted record year",
     *    @OA\JsonContent(
     *       required={"attendance_date","lesson_id"},
     *       @OA\Property(property="attendance_date", type="date"),
     *       @OA\Property(property="lesson_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Delete attendance"),
     * @OA\Response(response="401", description="Attendance not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function deleteAttendance(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $attendance_date = CommonHelper::decryptId($request->attendance_date);
                $lesson_id = CommonHelper::decryptId($request->lesson_id);

                $attendance = Attendance::where('attendance_date', '=', $attendance_date)->where('lesson_id', '=', $lesson_id)->delete();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Attendance of ' . $attendance_date . ' deleted successfully.'], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }
    /**
     * @OA\Post(
     * path="/api/{subdomain}/user/attendances",
     * summary="Get attendance data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_date_from", type="date", default=null),
     *       @OA\Property(property="search_date_to", type="date", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getUserAttendances(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;
                $search_lesson_id = $request->search_lesson_id;
                $search_date_from = $request->search_date_from ?? null;
                $search_date_to = $request->search_date_to ?? null;

                $subjectArray = Subject::where(function ($query) use ($search_subject_id, $search_academic_year_id, $search_year_group_id) {
                    if ($search_academic_year_id != null) {
                        $query->where('academic_year_id', $search_academic_year_id);
                    }
                    if ($search_year_group_id != null) {
                        $query->where('year_group_id', $search_year_group_id);
                    }
                    if ($search_subject_id != null) {
                        $query->where('subject_id', $search_subject_id);
                    }
                })
                    ->distinct('subject_id')
                    ->pluck('subject_id')
                    ->toArray();

                $listing = Attendance::with('lesson', 'lesson.subject', 'lesson.subject.yeargroup', 'lesson.subject.academicyear')
                    ->where('user_id', $user->user_id)
                    ->whereIn('subject_id', $subjectArray)
                    ->where(function ($query) use ($search_date_from, $search_date_to, $search_lesson_id) {
                        if ($search_lesson_id != null) {
                            $query->where('lesson_id', $search_lesson_id);
                        }
                        if ($search_date_from != null) {
                            $query->where('attendance_date', '>=', $search_date_from);
                        }
                        if ($search_date_to != null) {
                            $query->where('attendance_date', '<=', $search_date_to);
                        }
                    })
                // ->groupBy('attendance_date', 'subject_id', 'lesson_id')
                    ->orderBy(DB::raw('date(attendance_date)'), 'asc')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Attendance listing', 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }
    /**
     * @OA\Post(
     * path="/api/{subdomain}/user/all-attendances-for-rating",
     * summary="Get all attendance data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getUserAllAttendancesLessonsForRating(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = Attendance::with('lesson', 'creator')
                    ->where('user_id', $user->user_id)
                    ->where('is_present', 1)
                    ->distinct('lesson_id', 'created_by')
                    ->select('lesson_id', 'created_by')
                    ->get();
                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'All Attendance listing', 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/parent/attendances",
     * summary="Get for parent attendance data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       @OA\Property(property="search_student", type="int", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_date_from", type="date", default=null),
     *       @OA\Property(property="search_date_to", type="date", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getParentUserAttendances(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $parentStudentUserIds = UserSibling::where('parent_user_id', '=', $user->user_id)
                    ->distinct('sibling_user_id')
                    ->pluck('sibling_user_id')
                    ->toArray();

                $search_student = $request->search_student;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;
                $search_lesson_id = $request->search_lesson_id;
                $search_date_from = $request->search_date_from ?? null;
                $search_date_to = $request->search_date_to ?? null;

                $subjectArray = Subject::where(function ($query) use ($search_subject_id, $search_academic_year_id, $search_year_group_id) {
                    if ($search_academic_year_id != null) {
                        $query->where('academic_year_id', $search_academic_year_id);
                    }
                    if ($search_year_group_id != null) {
                        $query->where('year_group_id', $search_year_group_id);
                    }
                    if ($search_subject_id != null) {
                        $query->where('subject_id', $search_subject_id);
                    }
                })
                    ->distinct('subject_id')
                    ->pluck('subject_id')
                    ->toArray();

                // dd($subjectArray);

                $listing = Attendance::with('user', 'lesson', 'lesson.subject', 'lesson.subject.yeargroup', 'lesson.subject.academicyear')
                    ->whereIn('subject_id', $subjectArray)
                    ->whereIn('user_id', $parentStudentUserIds)
                    ->where(function ($query) use ($search_date_from, $search_date_to, $search_lesson_id, $search_student) {
                        if ($search_lesson_id != null) {
                            $query->where('lesson_id', $search_lesson_id);
                        }
                        if ($search_date_from != null) {
                            $query->where('attendance_date', '>=', $search_date_from);
                        }
                        if ($search_date_to != null) {
                            $query->where('attendance_date', '<=', $search_date_to);
                        }
                        if ($search_student != null) {
                            $query->where('user_id', $search_student);
                        }
                    })
                    ->orderBy(DB::raw('date(attendance_date)'), 'asc')
                    ->orderBy('user_id', 'asc')
                    ->get();

                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Attendance listing', 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/teacher/attendance-graph-data",
     * summary="Get attendance graph data (teacher) of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getAttendanceGraphDataTeacher(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                if ($user->user_type == 'TA') {
                    $listing = Attendance::select(
                        'attendance_date',
                        DB::raw('COALESCE((SELECT count(*) FROM attendances ia where ia.is_present=1 and ia.attendance_date=attendances.attendance_date),0) as total_present'),
                        DB::raw('COALESCE((SELECT count(*) FROM attendances ia where  ia.is_present=0 and ia.attendance_date=attendances.attendance_date),0) as total_absent'),
                        DB::raw('COALESCE((SELECT count(*) FROM attendances ia where ia.attendance_date=attendances.attendance_date),0) as total_enrollment')
                    )
                    // ->where('created_by', $user->user_id)
                        ->groupBy('attendance_date')
                        ->orderBy(DB::raw('date(attendance_date)'), 'asc')
                        ->get();
                } else {
                    $listing = Attendance::select(
                        'attendance_date',
                        DB::raw('COALESCE((SELECT count(*) FROM attendances ia where ia.is_present=1 and ia.attendance_date=attendances.attendance_date),0) as total_present'),
                        DB::raw('COALESCE((SELECT count(*) FROM attendances ia where  ia.is_present=0 and ia.attendance_date=attendances.attendance_date),0) as total_absent'),
                        DB::raw('COALESCE((SELECT count(*) FROM attendances ia where ia.attendance_date=attendances.attendance_date),0) as total_enrollment')
                    )
                        ->where('created_by', $user->user_id)
                        ->groupBy('attendance_date')
                        ->orderBy(DB::raw('date(attendance_date)'), 'asc')
                        ->get();
                }
                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Attendance listing', 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

}
