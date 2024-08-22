<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Library;
use App\Models\LibraryContentType;
use App\Models\Subject;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;

class LibraryController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/library/supported-content-types",
     * summary="Get library supported content type list",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="library supported content type list"),
     * @OA\Response(response="401", description="library supported content type list not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getLibrarySupportedContentTypes()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = LibraryContentType::get();

                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Library supported content type listing', 'listing' => $listing], 'error' => []];
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
     *     path="/api/{subdomain}/upload-to-library",
     *     summary="Upload item to library",
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
     *                     property="lesson_id",
     *                     type="int",
     *                     description="Lesson Id"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="Title for content file",
     *                     default=null
     *                 ),
     *                 @OA\Property(
     *                     property="content_type",
     *                     type="string",
     *                     description="Content type note/ppt/url/video/mindmap/assessment/solution"
     *                 ),
     *                 @OA\Property(
     *                     property="content_file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload",
     *                     default=null
     *                 ),
     *                 @OA\Property(
     *                     property="content_url",
     *                     type="string",
     *                     description="Content link",
     *                     default=null
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="New library item created successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function uploadToLibrary(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $request->validate([
                    'lesson_id' => 'required',
                    'content_type' => 'required|string',
                    'title' => 'nullable|string|max:255',
                    'content_file' => 'nullable|max:2048',
                    'content_url' => 'nullable|string|max:255',
                ]);

                // Store lesson details in the database or perform other business logic
                $model = new Library;
                $model->lesson_id = $request->lesson_id;
                $model->created_by = $user->user_id;
                $model->content_type = $request->content_type;
                $model->title = $request->title ?? '';
                $model->content_url = $request->content_url ?? '';
                $model->status = GlobalVars::ACTIVE_STATUS;

                if ($request->hasFile('content_file')) {
                    $path = $request->file('content_file')->store(GlobalVars::USER_LIBRARY_PATH . $this->tenantId . '/' . $request->lesson_id, 'public');
                    $model->content_file = $path ?? '';
                }
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'New library item created successfully.'), 'error' => []];
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
     *     path="/api/{subdomain}/update-library-item",
     *     summary="Update item of library",
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
     *                     property="library_id",
     *                     type="int",
     *                     description="Library Id"
     *                 ),
     *                 @OA\Property(
     *                     property="lesson_id",
     *                     type="int",
     *                     description="Lesson Id"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="Title for content file",
     *                     default=null
     *                 ),
     *                 @OA\Property(
     *                     property="created_by",
     *                     type="int",
     *                     description="Creator user_id"
     *                 ),
     *                 @OA\Property(
     *                     property="content_type",
     *                     type="string",
     *                     description="Content type note/ppt/url/video/mindmap/assessment/solution"
     *                 ),
     *                 @OA\Property(
     *                     property="content_file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload",
     *                     default=null
     *                 ),
     *                 @OA\Property(
     *                     property="content_url",
     *                     type="string",
     *                     description="Content link",
     *                     default=null
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     description="status",
     *                     default="Active"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Library item updated successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateLibraryItem(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $request->validate([
                    'library_id' => 'required',
                    'lesson_id' => 'required',
                    'content_type' => 'required|string',
                    'title' => 'nullable|string|max:255',
                    'content_file' => 'nullable|max:2048',
                    'content_url' => 'nullable|string|max:255',
                ]);

                // Store lesson details in the database or perform other business logic
                $model = Library::find($request->library_id);
                $model->lesson_id = $request->lesson_id;
                // $model->created_by = $user->user_id;
                $model->content_type = $request->content_type;
                $model->title = $request->title ?? '';
                $model->content_url = $request->content_url ?? '';
                $model->status = $request->status ?? '';

                if ($request->hasFile('content_file')) {
                    if ($model->content_file != '') {
                        $arrFile = explode('.', $model->content_file);
                        if (isset($arrFile[1])) {
                            Storage::disk('public')->delete($model->content_file);
                        }
                    }
                    $path = $request->file('content_file')->store(GlobalVars::USER_LIBRARY_PATH . $this->tenantId, 'public');
                    $model->content_file = $path ?? '';
                }
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Library item updated successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/library/get-subjectid-lessons",
     * summary="Get library subject wise lesson list passing tenant token as per subdomain",
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
     *       @OA\Property(property="status", type="string",default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant lesson master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function librarySubjectIdLessons(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $subject_id = $request->subject_id;
                $status = $request->status ?? '';
                if ($subject_id != '' || $subject_id != null) {
                    $subject_id = CommonHelper::decryptId($request->subject_id);
                    $subject = Subject::with('yeargroup', 'academicyear')->find($subject_id);
                    if ($status != '') {
                        $listing = Lesson::select(
                            'lessons.*',
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_NOTE . '" and libraries.status="' . $status . '"),0) as total_notes'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_MINDMAP . '" and libraries.status="' . $status . '"),0) as total_mindmaps'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_VIDEO . '" and libraries.status="' . $status . '"),0) as total_videos'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_PPT . '" and libraries.status="' . $status . '"),0) as total_ppts'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_URL . '" and libraries.status="' . $status . '"),0) as total_urls'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_ASSESSMENT . '" and libraries.status="' . $status . '"),0) as total_assessments'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_ASSESSMENT_SOL . '" and libraries.status="' . $status . '"),0) as total_assessment_solutions')
                        )
                            ->where('subject_id', '=', $subject_id)
                            ->get();
                    } else {
                        $listing = Lesson::select(
                            'lessons.*',
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_NOTE . '"),0) as total_notes'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_MINDMAP . '"),0) as total_mindmaps'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_VIDEO . '"),0) as total_videos'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_PPT . '"),0) as total_ppts'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_URL . '"),0) as total_urls'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_ASSESSMENT . '"),0) as total_assessments'),
                            DB::raw('COALESCE((SELECT count(*) FROM libraries where libraries.lesson_id=lessons.lesson_id and libraries.content_type="' . GlobalVars::LIBRARY_CT_ASSESSMENT_SOL . '"),0) as total_assessment_solutions')
                        )
                            ->where('subject_id', '=', $subject_id)
                            ->get();
                    }

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Library Subject wise lesson list fetched successfully.', 'listing' => $listing, 'subject_info' => $subject), 'error' => ''];
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
     * path="/api/{subdomain}/library/get-content-by-lessonntype",
     * summary="Get library lesson and content type wise content list passing tenant token as per subdomain",
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
     *     description="provide lesson_id and content_type",
     *    @OA\JsonContent(
     *       required={"lesson_id","content_type"},
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="content_type", type="string"),
     *       @OA\Property(property="status", type="string",default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant library content list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function libraryLessonIdContentByType(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $lesson_id = $request->lesson_id;
                $content_type = $request->content_type;
                $status = $request->status ?? '';
                if ($lesson_id != '' || $lesson_id != null) {
                    $lesson_id = CommonHelper::decryptId($request->lesson_id);
                    $lesson_info = Lesson::with('subject', 'subject.yeargroup', 'subject.academicyear')->find($lesson_id);

                    $listing = Library::where('libraries.lesson_id', '=', $lesson_id)
                        ->where('libraries.content_type', '=', $content_type)
                        ->where(function ($query) use ($status) {

                            if ($status != '') {
                                $query->where('libraries.status', $status);
                            }

                        })
                        ->get();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Library Lesson and content type wise content list fetched successfully.', 'listing' => $listing, 'lesson_info' => $lesson_info), 'error' => ''];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No lesson id provided.']];
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
     *     path="/api/{subdomain}/get-library-item",
     *     summary="get item of library",
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
     *                     property="library_id",
     *                     type="int",
     *                     description="Library Id"
     *                 ),
     *
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Library item fetched successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getLibraryItem(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $request->validate([
                    'library_id' => 'required',

                ]);
                $library_id = $request->library_id ?? '';
                $library_id = CommonHelper::decryptId($library_id);
                // Store lesson details in the database or perform other business logic
                $details = Library::find($library_id);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Library item fetched successfully.', 'details' => $details), 'error' => []];
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
