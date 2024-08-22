<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\LessonImport;
use App\Imports\TopicImport;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\SubTopic;
use App\Models\Topic;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class LessonController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/lessons",
     * summary="Get lesson master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant lesson master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getLessons(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;

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

                $lessonList = Lesson::with('subject', 'subject.yeargroup', 'subject.academicyear')
                    ->where(function ($query) use ($search_text, $subjectArray) {

                        if ($search_text != null) {
                            $query->where('lesson_name', 'like', '%' . $search_text . '%');
                        }

                        //if (count($subjectArray) > 0) {
                        $query->whereIn('subject_id', $subjectArray);
                        //}

                    })
                    ->orderBy('lessons.subject_id', 'asc')
                    ->orderByRaw('CONVERT(lessons.lesson_number, SIGNED) asc')
                    ->orderBy('lessons.lesson_name', 'asc')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson Master list fetched successfully.', 'lesson_list' => $lessonList), 'error' => ''];
                return response()->json($reponse, 200);
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
     * path="/api/{subdomain}/create-lesson",
     * summary="Create lesson master data of tenant as per subdomain",
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
     *     description="add new lesson",
     *    @OA\JsonContent(
     *       required={"subject_id","lesson_number","lesson_name"},
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_name", type="string"),
     *       @OA\Property(property="lesson_number", type="string", default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant lesson master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createLesson(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $model = new Lesson;
                $model->subject_id = $request->subject_id;
                $model->lesson_name = $request->lesson_name;
                $model->lesson_number = $request->lesson_number;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->status = GlobalVars::ACTIVE_STATUS;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson added successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/create-lesson-teacher",
     * summary="Create lesson master data by teacher of tenant as per subdomain",
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
     *     description="add new lesson",
     *    @OA\JsonContent(
     *       required={"subject_id","lesson_number","lesson_name"},
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_name", type="string"),
     *       @OA\Property(property="lesson_number", type="string", default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant lesson master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createLessonTeacher(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }
                $model = new Lesson;
                $model->subject_id = $request->subject_id;
                $model->lesson_name = $request->lesson_name;
                $model->lesson_number = $request->lesson_number;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->status = GlobalVars::ACTIVE_STATUS;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson added successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/get-lesson-by-id",
     * summary="Get lesson details by lesson_id",
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
     *     description="Pass encrpted record lesson_id",
     *    @OA\JsonContent(
     *       required={"lesson_id"},
     *       @OA\Property(property="lesson_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Lesson details"),
     * @OA\Response(response="401", description="Lesson not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getLessonById(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $lesson_id = CommonHelper::decryptId($request->lesson_id);
                $lesson = Lesson::with('subject.yeargroup', 'subject.academicyear')->find($lesson_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Lesson details by id', 'details' => $lesson], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/update-lesson",
     * summary="Update lesson master data of tenant as per subdomain",
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
     *     description="update lesson",
     *    @OA\JsonContent(
     *       required={"lesson_id","subject_id","lesson_name"},
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_name", type="string"),
     *       @OA\Property(property="lesson_number", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant lesson master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateLesson(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $model = Lesson::find($request->lesson_id);
                // print_r($model);die;
                // $model->tenant_id = $user->tenant_id;
                $model->subject_id = $request->subject_id;
                $model->lesson_name = $request->lesson_name;
                $model->lesson_number = $request->lesson_number;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->status = $request->status;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson updated successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/update-lesson-teacher",
     * summary="Update lesson master data by teacher of tenant as per subdomain",
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
     *     description="update lesson",
     *    @OA\JsonContent(
     *       required={"lesson_id","subject_id","lesson_name"},
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_name", type="string"),
     *       @OA\Property(property="lesson_number", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant lesson master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateLessonTeacher(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }
                $model = Lesson::find($request->lesson_id);
                // print_r($model);die;
                // $model->tenant_id = $user->tenant_id;
                $model->subject_id = $request->subject_id;
                $model->lesson_name = $request->lesson_name;
                $model->lesson_number = $request->lesson_number;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->status = $request->status;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson updated successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/dropdown/get-subjectid-lessons",
     * summary="Get subject wise lesson list for dropdowns passing tenant token as per subdomain",
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
     *     description="provide subject_id",
     *    @OA\JsonContent(
     *       required={"subject_id"},
     *       @OA\Property(property="subject_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant lesson master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownSubjectIdLessons(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $subject_id = $request->subject_id;
                if ($subject_id != '' || $subject_id != null) {

                    $listing = Lesson::select('lessons.*')
                        ->where('subject_id', '=', $subject_id)
                        ->orderBy('lessons.subject_id', 'asc')
                        ->orderByRaw('CONVERT(lessons.lesson_number, SIGNED) asc')
                        ->orderBy('lessons.lesson_name', 'asc')
                        ->get();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subject wise lesson list fetched successfully.', 'listing' => $listing), 'error' => ''];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No year group id provided.']];
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
     * path="/api/{subdomain}/dropdown/get-lessonid-topics",
     * summary="Get Lesson wise topic list for dropdowns passing tenant token as per subdomain",
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
     *     description="provide lesson_id",
     *    @OA\JsonContent(
     *       required={"lesson_id"},
     *       @OA\Property(property="lesson_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant topic master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownLessonIdTopics(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $lesson_id = $request->lesson_id;
                if ($lesson_id != '' || $lesson_id != null) {

                    $listing = Topic::where('lesson_id', '=', $lesson_id)

                        ->orderBy('topics.topic', 'asc')
                        ->get();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson wise topic list fetched successfully.', 'listing' => $listing), 'error' => ''];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No year group id provided.']];
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
     * path="/api/{subdomain}/dropdown/get-topicid-subtopics",
     * summary="Get Topic wise sub-topic list for dropdowns passing tenant token as per subdomain",
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
     *     description="provide topic_id",
     *    @OA\JsonContent(
     *       required={"topic_id"},
     *       @OA\Property(property="topic_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant topic master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownTopicIdSubTopics(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $topic_id = $request->topic_id;
                if ($topic_id != '' || $topic_id != null) {

                    $listing = SubTopic::where('topic_id', '=', $topic_id)

                        ->orderBy('sub_topics.sub_topic', 'asc')
                        ->get();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Topic wise sub-topic list fetched successfully.', 'listing' => $listing), 'error' => ''];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No year group id provided.']];
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
     *     path="/api/{subdomain}/import-lessons",
     *     summary="Bulk import lessons",
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
     *                     property="import_file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload",
     *                     default=null
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Lesson bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importLesson(Request $request)
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

                    $data = \Excel::import(new LessonImport($request->subject_id, $user->user_id, $user->user_type), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk lessons processed successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/topics",
     * summary="Get topic master data on a lesson of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_lesson_id", type="int", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant topic master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTopics(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;
                $search_lesson_id = $request->search_lesson_id;

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

                $listing = Topic::with('lesson', 'subject', 'subject.yeargroup', 'subject.academicyear')
                    ->where(function ($query) use ($search_text, $subjectArray, $search_lesson_id) {

                        if ($search_text != null) {
                            $query->where('topic', 'like', '%' . $search_text . '%');
                        }
                        if ($search_lesson_id != null) {
                            $query->where('lesson_id', $search_lesson_id);
                        }

                        //if (count($subjectArray) > 0) {
                        $query->whereIn('subject_id', $subjectArray);
                        //}

                    })
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Topic Master list fetched successfully.', 'listing' => $listing), 'error' => ''];
                return response()->json($reponse, 200);
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
     * path="/api/{subdomain}/create-topic",
     * summary="Create topic of lesson master data of tenant as per subdomain",
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
     *     description="add new topic",
     *    @OA\JsonContent(
     *       required={"subject_id","lesson_id","topic"},
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="topic", type="string"),
     *       @OA\Property(property="sub_topics", type="array", description="List of sub_topic",
     *       @OA\Items(
     *          @OA\Property(
     *              property="sub_topic",
     *              description="sub_topic",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant lesson master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createTopic(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $model = new Topic;
                $model->subject_id = $request->subject_id;
                $model->lesson_id = $request->lesson_id;
                $model->topic = $request->topic;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->status = GlobalVars::ACTIVE_STATUS;
                $model->save();
                $sub_topics = $request->sub_topics ?? array();
                if (count($sub_topics) > 0) {
                    for ($i = 0; $i < count($sub_topics); $i++) {
                        if ($sub_topics[$i]['sub_topic'] != '') {
                            $modelSub = new SubTopic;
                            $modelSub->subject_id = $request->subject_id;
                            $modelSub->lesson_id = $request->lesson_id;
                            $modelSub->topic_id = $model->topic_id;
                            $modelSub->sub_topic = $sub_topics[$i]['sub_topic'];
                            $modelSub->created_by = $user->user_id;
                            $modelSub->creator_type = $user->user_type;
                            $modelSub->status = GlobalVars::ACTIVE_STATUS;
                            $modelSub->save();
                        }
                    }
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Topic added successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/get-topic-by-id",
     * summary="Get topic details by topic_id",
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
     *     description="Pass encrpted record topic_id",
     *    @OA\JsonContent(
     *       required={"topic_id"},
     *       @OA\Property(property="topic_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Topic details"),
     * @OA\Response(response="401", description="Topic not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTopicById(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $topic_id = CommonHelper::decryptId($request->topic_id);
                $details = Topic::with('sub_topics', 'subject.yeargroup', 'subject.academicyear')->find($topic_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Topic details by id', 'details' => $details], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/update-topic",
     * summary="Update topic of topic master data of tenant as per subdomain",
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
     *     description="update topic",
     *    @OA\JsonContent(
     *       required={"topic_id","subject_id","lesson_id","topic"},
     *       @OA\Property(property="topic_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="topic", type="string"),
     *       @OA\Property(property="sub_topics", type="array", description="List of sub_topic",
     *       @OA\Items(
     *          @OA\Property(
     *              property="sub_topic_id",
     *              description="sub_topic id",
     *              type="int",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="sub_topic",
     *              description="sub_topic",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant lesson master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateTopic(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $model = Topic::find($request->topic_id);
                $model->subject_id = $request->subject_id;
                $model->lesson_id = $request->lesson_id;
                $model->topic = $request->topic;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->status = $request->status;
                $model->save();
                $sub_topics = $request->sub_topics ?? array();
                if (count($sub_topics) > 0) {
                    for ($i = 0; $i < count($sub_topics); $i++) {
                        if ($sub_topics[$i]['sub_topic'] != '') {
                            if ($sub_topics[$i]['sub_topic_id'] != '') {
                                $modelSub = SubTopic::find($sub_topics[$i]['sub_topic_id']);
                                $modelSub->subject_id = $request->subject_id;
                                $modelSub->lesson_id = $request->lesson_id;
                                $modelSub->topic_id = $model->topic_id;
                                $modelSub->sub_topic = $sub_topics[$i]['sub_topic'];
                                $modelSub->created_by = $user->user_id;
                                $modelSub->creator_type = $user->user_type;
                                $modelSub->status = $request->status;
                                $modelSub->save();
                            } else {
                                $modelSub = new SubTopic;
                                $modelSub->subject_id = $request->subject_id;
                                $modelSub->lesson_id = $request->lesson_id;
                                $modelSub->topic_id = $model->topic_id;
                                $modelSub->sub_topic = $sub_topics[$i]['sub_topic'];
                                $modelSub->created_by = $user->user_id;
                                $modelSub->creator_type = $user->user_type;
                                $modelSub->status = $request->status;
                                $modelSub->save();
                            }
                        }
                    }
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Topic updated successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     *     path="/api/{subdomain}/import-topics",
     *     summary="Bulk import topics",
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
     *     @OA\Response(response="200", description="Topic bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importTopic(Request $request)
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

                    $data = \Excel::import(new TopicImport($request->subject_id, $request->lesson_id, $user->user_id, $user->user_type), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk topic processed successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/sub-topics",
     * summary="Get sub topic master data on a lesson of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_lesson_id", type="int", default=null),
     *       @OA\Property(property="search_topic_id", type="int", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant topic master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubTopics(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;
                $search_lesson_id = $request->search_lesson_id;
                $search_topic_id = $request->search_topic_id;

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

                $listing = SubTopic::with('topic', 'lesson', 'subject', 'subject.yeargroup', 'subject.academicyear')
                    ->where(function ($query) use ($search_text, $subjectArray, $search_lesson_id, $search_topic_id) {

                        if ($search_text != null) {
                            $query->where('sub_topic', 'like', '%' . $search_text . '%');
                        }
                        if ($search_lesson_id != null) {
                            $query->where('lesson_id', $search_lesson_id);
                        }
                        if ($search_topic_id != null) {
                            $query->where('topic_id', $search_topic_id);
                        }

                        //if (count($subjectArray) > 0) {
                        $query->whereIn('subject_id', $subjectArray);
                        //}

                    })
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Sub Topic Master list fetched successfully.', 'listing' => $listing), 'error' => ''];
                return response()->json($reponse, 200);
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

}
