<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\SubjectImport;
use App\Models\Board;
use App\Models\Subject;
use App\Models\UserSubject;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class SubjectController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/subjects",
     * summary="Get subject master data of tenant as per subdomain",
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
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant academic year master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubjects(Request $request)
    {

        try {
            // dd(request()->bearerToken());
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_board_id = $request->search_board_id;

                $subjectList = Subject::join('academic_years', 'academic_years.academic_year_id', 'subjects.academic_year_id', 'INNER')
                    ->join('year_groups', 'year_groups.year_group_id', 'subjects.year_group_id', 'INNER')
                    ->select('subjects.*', 'academic_years.academic_year', 'year_groups.name')
                    ->where(function ($query) use ($search_academic_year_id, $search_year_group_id, $search_text, $search_board_id) {
                        if ($search_academic_year_id != null) {
                            $query->where('subjects.academic_year_id', $search_academic_year_id);
                        }
                        if ($search_year_group_id != null) {
                            $query->where('subjects.year_group_id', $search_year_group_id);
                        }
                        if ($search_board_id != null) {
                            $query->where('subjects.board_id', $search_board_id);
                        }
                        if ($search_text != null) {
                            $query->where('subjects.subject_name', 'like', '%' . $search_text . '%');
                        }
                    })
                    ->orderBy('subjects.subject_name', 'asc')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $boards = Board::pluck('board_name', 'board_id');
                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subject Master list fetched successfully.', 'subject_list' => $subjectList, 'boards' => $boards), 'error' => ''];
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
     * path="/api/{subdomain}/create-subject",
     * summary="Create subject master data of tenant as per subdomain",
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
     *     description="add new subject",
     *    @OA\JsonContent(
     *       required={"academic_year_id","board_id","year_group_id","subject_name"},
     *       @OA\Property(property="academic_year_id", type="int"),
     *       @OA\Property(property="board_id", type="int"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="subject_name", type="string"),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="subject_image", type="string", default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant subject master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createSubject(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $existingSubject = Subject::where('subject_name', $request->subject_name)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('board_id', $request->board_id)
                    ->where('year_group_id', $request->year_group_id)
                    ->first();

                if (empty($existingSubject)) {
                    $model = new Subject;
                    $model->academic_year_id = $request->academic_year_id;
                    $model->board_id = $request->board_id;
                    $model->year_group_id = $request->year_group_id;
                    $model->subject_name = $request->subject_name;
                    $model->description = $request->description;
                    if (isset($request->subject_image) && $request->subject_image != '') {

                        $folderPath_subject_image = \GlobalVars::PORTAL_SUBJECT_PATH;

                        //image without mime information
                        $imageDataWithoutMime_subject_image = explode('base64,', $request->subject_image);

                        $file_subject_image = $request->subject_image;
                        if (isset($imageDataWithoutMime_subject_image[1])) {
                            $file_subject_image = base64_decode($imageDataWithoutMime_subject_image[1]);
                        }
                        if ($file_subject_image) {

                            $extension_subject_image = 'png';
                            if (isset($imageDataWithoutMime_subject_image[1])) {
                                $extension_subject_image = explode('/', mime_content_type($request->subject_image))[1];
                            }
                            // dd($extension);

                            $image_base64_1_subject_image = $file_subject_image;
                            $file_subject_image1 = $folderPath_subject_image . uniqid() . '.' . $extension_subject_image;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file_subject_image1, $image_base64_1_subject_image);

                            $model->subject_image = $file_subject_image1;
                        }
                    }
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subject added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Subject already exist.']];
                    return response()->json($reponse, 400);
                }
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
     * path="/api/{subdomain}/get-subject-by-id",
     * summary="Get subject details by subject_id",
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
     *     description="Pass encrpted record subject_id",
     *    @OA\JsonContent(
     *       required={"subject_id"},
     *       @OA\Property(property="subject_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Subject details"),
     * @OA\Response(response="401", description="Subject not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubjectById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $subject_id = CommonHelper::decryptId($request->subject_id);
                $subject = Subject::find($subject_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Subject details by id', 'details' => $subject], 'error' => []];
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
     * path="/api/{subdomain}/update-subject",
     * summary="Update subject master data of tenant as per subdomain",
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
     *     description="update subject",
     *    @OA\JsonContent(
     *       required={"subject_id","academic_year_id","board_id","year_group_id","subject_name"},
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="academic_year_id", type="int"),
     *       @OA\Property(property="board_id", type="int"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="subject_name", type="string"),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="subject_image", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant academic year master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateSubject(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $existingSubject = Subject::where('subject_name', $request->subject_name)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('board_id', $request->board_id)
                    ->where('year_group_id', $request->year_group_id)
                    ->where('subject_id', '!=', $request->subject_id)
                    ->first();

                if (empty($existingSubject)) {
                    $model = Subject::find($request->subject_id);
                    $model->academic_year_id = $request->academic_year_id;
                    $model->board_id = $request->board_id;
                    $model->year_group_id = $request->year_group_id;
                    $model->subject_name = $request->subject_name;
                    $model->description = $request->description;
                    if (isset($request->subject_image) && $request->subject_image != '') {

                        $folderPath_subject_image = \GlobalVars::PORTAL_SUBJECT_PATH;

                        //image without mime information
                        $imageDataWithoutMime_subject_image = explode('base64,', $request->subject_image);

                        $file_subject_image = $request->subject_image;
                        if (isset($imageDataWithoutMime_subject_image[1])) {
                            $file_subject_image = base64_decode($imageDataWithoutMime_subject_image[1]);
                        }
                        if ($file_subject_image) {

                            $extension_subject_image = 'png';
                            if (isset($imageDataWithoutMime_subject_image[1])) {
                                $extension_subject_image = explode('/', mime_content_type($request->subject_image))[1];
                            }
                            // dd($extension);

                            $image_base64_1_subject_image = $file_subject_image;
                            $file_subject_image1 = $folderPath_subject_image . uniqid() . '.' . $extension_subject_image;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file_subject_image1, $image_base64_1_subject_image);
                            if ($model->subject_image != '') {
                                $arrFile = explode('.', $model->subject_image);
                                if (isset($arrFile[1])) {
                                    Storage::disk('public')->delete($model->subject_image);
                                }
                            }
                            $model->subject_image = $file_subject_image1;
                        }
                    }
                    $model->status = $request->status;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subject updated successfully.'), 'error' => []];
                    return response()->json($reponse, 200);

                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Subject already exist.']];
                    return response()->json($reponse, 400);
                }
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
     * path="/api/{subdomain}/dropdown/get-yeargroup-subjects",
     * summary="Get year group wise subject list for dropdowns passing tenant token as per subdomain",
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
     *     description="provide comma separate year_group_id",
     *    @OA\JsonContent(
     *       required={"year_group_id"},
     *       @OA\Property(property="year_group_id", type="string",format="1,2,3"),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant year group subject master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownYeargroupSubjects(Request $request)
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

                $year_group_id = $request->year_group_id;
                if ($year_group_id != '' || $year_group_id != null) {
                    $arrYearGroupIds = explode(',', $year_group_id);
                    $subjectList = array();
                    if ($user->user_type == GlobalVars::TENANT_USER_TYPE) {
                        $usrMappedSubjects = UserSubject::where('user_subjects.user_id', $user->user_id)->distinct('subject_id')->pluck('subject_id')->toArray();

                        $subjectList = Subject::join('academic_years', 'academic_years.academic_year_id', 'subjects.academic_year_id', 'INNER')
                            ->join('year_groups', 'year_groups.year_group_id', 'subjects.year_group_id', 'INNER')
                            ->select('subjects.*', 'academic_years.academic_year', 'year_groups.name')
                            ->whereIn('subjects.year_group_id', $arrYearGroupIds)
                            ->whereIn('subjects.subject_id', $usrMappedSubjects)
                            ->orderBy('subjects.subject_name', 'asc')
                            ->get();
                    } else {
                        $subjectList = Subject::join('academic_years', 'academic_years.academic_year_id', 'subjects.academic_year_id', 'INNER')
                            ->join('year_groups', 'year_groups.year_group_id', 'subjects.year_group_id', 'INNER')
                            ->select('subjects.*', 'academic_years.academic_year', 'year_groups.name')
                            ->whereIn('subjects.year_group_id', $arrYearGroupIds)
                            ->orderBy('subjects.subject_name', 'asc')
                            ->get();
                    }

                    $boards = Board::pluck('short_name', 'board_id');
                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Year group wise subject list fetched successfully.', 'subject_list' => $subjectList, 'boards' => $boards), 'error' => ''];
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
     * path="/api/{subdomain}/dropdown/get-all-subjects",
     * summary="Get all subject list for dropdowns passing tenant token as per subdomain",
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
     *     description="add new lesson",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant all subject master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownSubjects(Request $request)
    {
        // dd($request->all());
        $status = $request->status ?? '';
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $subjectList = array();
                if ($user->user_type == GlobalVars::TENANT_USER_TYPE) {

                    $usrMappedSubjects = UserSubject::where('user_subjects.user_id', $user->user_id)->distinct('subject_id')->pluck('subject_id')->toArray();

                    $subjectList = Subject::join('academic_years', 'academic_years.academic_year_id', 'subjects.academic_year_id', 'INNER')
                        ->join('year_groups', 'year_groups.year_group_id', 'subjects.year_group_id', 'INNER')
                        ->select('subjects.*', 'academic_years.academic_year', 'year_groups.name')
                        ->whereIn('subjects.subject_id', $usrMappedSubjects)
                        ->where(function ($query) use ($status) {

                            if ($status != '') {
                                $query->where('subjects.status', $status);
                            }

                        })
                        ->orderBy('subjects.subject_name', 'asc')
                        ->get();
                } else {
                    $subjectList = Subject::join('academic_years', 'academic_years.academic_year_id', 'subjects.academic_year_id', 'INNER')
                        ->join('year_groups', 'year_groups.year_group_id', 'subjects.year_group_id', 'INNER')
                        ->select('subjects.*', 'academic_years.academic_year', 'year_groups.name')
                        ->where(function ($query) use ($status) {

                            if ($status != '') {
                                $query->where('subjects.status', $status);
                            }

                        })
                        ->orderBy('subjects.subject_name', 'asc')
                        ->get();
                }

                $boards = Board::pluck('board_name', 'board_id');
                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subject list fetched successfully.', 'subject_list' => $subjectList, 'boards' => $boards), 'error' => ''];
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
     *     path="/api/{subdomain}/import-subjects",
     *     summary="Bulk import subjects",
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
     *                     property="academic_year_id",
     *                     type="int",
     *                     description="academic_year Id"
     *                 ),
     *                 @OA\Property(
     *                     property="year_group_id",
     *                     type="int",
     *                     description="year_group Id"
     *                 ),
     *                 @OA\Property(
     *                     property="board_id",
     *                     type="int",
     *                     description="board Id"
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
     *     @OA\Response(response="200", description="Subject bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importSubject(Request $request)
    {
        // dd($request->all());
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
                    $data = \Excel::import(new SubjectImport($request->academic_year_id, $request->year_group_id, $request->board_id), $file);
                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk subjects import processed successfully.'), 'error' => []];
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
}
