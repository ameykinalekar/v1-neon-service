<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\TeacherImport;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\Topic;
use App\Models\UserSubject;
use App\Models\UserYearGroup;
use App\Models\ViewStudent;
use App\Models\ViewTeacher;
use App\Models\ViewTeacherAssistant;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/teachers",
     * summary="Get teacher master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant teacher details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeachers(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
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

                $subjectUsers = UserSubject::whereIn('subject_id', $subjectArray)
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();

                $listing = ViewTeacher::where(function ($query) use ($search_text, $subjectUsers, $search_subject_id, $search_academic_year_id, $search_year_group_id) {
                    if ($search_text != null) {
                        $query->where('first_name', 'like', '%' . $search_text . '%')
                            ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                            ->orWhere('last_name', 'like', '%' . $search_text . '%')
                            ->orWhere('email', 'like', '%' . $search_text . '%')
                            ->orWhere('parent_email', 'like', '%' . $search_text . '%')
                            ->orWhere('phone', 'like', '%' . $search_text . '%')
                            ->orWhere('parent_phone', 'like', '%' . $search_text . '%')
                            ->orWhere('address', 'like', '%' . $search_text . '%');
                    }
                    if ($search_academic_year_id != null || $search_year_group_id != null || $search_subject_id != null) {
                        $query->whereIn('user_id', $subjectUsers);
                    }
                })->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'teacher data fetched successfully.', 'teacher_list' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/create-teacher",
     * summary="Create teacher master data of tenant as per subdomain",
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
     *     description="add new teacher",
     *    @OA\JsonContent(
     *       required={"first_name","email","password","phone","gender"},
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="department_id", type="string"),
     *       @OA\Property(property="ni_number", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="about", type="string", default=null),
     *       @OA\Property(property="end_date_id", type="date", default=null),
     *       @OA\Property(property="end_date_dbs", type="date", default=null),
     *       @OA\Property(property="id_file", type="string", default=null),
     *       @OA\Property(property="dbs_certificate_file", type="string", default=null),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *       @OA\Property(property="subject_id", type="string",format="1,2,3"),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant teacher master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createTeacher(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                //validate existence of email id in any user type
                $ulist = TenantUser::where('email', '=', $request->email)->first();

                if (empty($ulist)) {
                    $modelU = new TenantUser;
                    $modelU->email = $request->email;
                    $modelU->tenant_id = $user->tenant_id;
                    $modelU->password = $request->password;
                    $modelU->user_type = GlobalVars::TENANT_USER_TYPE;
                    $modelU->role = GlobalVars::TENANT_TEACHER_ROLE;
                    $modelU->phone = $request->phone;
                    $modelU->status = GlobalVars::ACTIVE_STATUS;
                    if (isset($request->profile_image) && $request->profile_image != '') {
                        //image without mime information
                        $imageDataWithoutMime = explode('base64,', $request->profile_image);
                        $file = $request->profile_image;
                        if (isset($imageDataWithoutMime[1])) {
                            $file = base64_decode($imageDataWithoutMime[1]);
                        }
                        if ($file) {

                            $folderPath1 = \GlobalVars::USER_PIC_PATH . $user->tenant_id . '/';
                            $extension = 'png';
                            if (isset($imageDataWithoutMime[1])) {
                                $extension = explode('/', mime_content_type($request->profile_image))[1];
                            }
                            // dd($extension);

                            $image_base64_1 = $file;
                            $file1 = $folderPath1 . uniqid() . '.' . $extension;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file1, $image_base64_1);

                            $modelU->user_logo = $file1;
                        }
                    }
                    $modelU->save();

                    $modelUP = new TenantUserProfile;
                    $modelUP->user_id = $modelU->user_id;
                    $modelUP->first_name = $request->first_name;
                    $modelUP->last_name = $request->last_name;
                    $modelUP->address = $request->address;
                    $modelUP->gender = $request->gender;
                    $modelUP->ni_number = $request->ni_number;
                    $modelUP->department_id = $request->department_id;
                    $modelUP->about = $request->about;
                    $modelUP->end_date_id = $request->end_date_id;
                    $modelUP->end_date_dbs = $request->end_date_dbs;
                    if (isset($request->id_file) && $request->id_file != '') {
                        //image without mime information
                        $imageDataWithoutMime = explode('base64,', $request->id_file);
                        $file = $request->id_file;
                        if (isset($imageDataWithoutMime[1])) {
                            $file = base64_decode($imageDataWithoutMime[1]);
                        }
                        if ($file) {

                            $folderPath1 = \GlobalVars::USER_IDFILE_PATH . $user->tenant_id . '/';
                            $extension = 'png';
                            if (isset($imageDataWithoutMime[1])) {
                                $extension = explode('/', mime_content_type($request->id_file))[1];
                            }
                            // dd($extension);

                            $image_base64_1 = $file;
                            $file1 = $folderPath1 . uniqid() . '.' . $extension;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file1, $image_base64_1);

                            $modelUP->id_file = $file1;
                        }
                    }
                    if (isset($request->dbs_certificate_file) && $request->dbs_certificate_file != '') {
                        //image without mime information
                        $imageDataWithoutMime = explode('base64,', $request->dbs_certificate_file);
                        $file = $request->dbs_certificate_file;
                        if (isset($imageDataWithoutMime[1])) {
                            $file = base64_decode($imageDataWithoutMime[1]);
                        }
                        if ($file) {

                            $folderPath1 = \GlobalVars::USER_DBSFILE_PATH . $user->tenant_id . '/';
                            $extension = 'png';
                            if (isset($imageDataWithoutMime[1])) {
                                $extension = explode('/', mime_content_type($request->dbs_certificate_file))[1];
                            }
                            // dd($extension);

                            $image_base64_1 = $file;
                            $file1 = $folderPath1 . uniqid() . '.' . $extension;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file1, $image_base64_1);

                            $modelUP->dbs_certificate_file = $file1;
                        }
                    }

                    $modelUP->save();

                    if ($request->subject_id != "") {
                        UserSubject::where('user_id', '=', $modelU->user_id)->delete();
                        UserYearGroup::where('user_id', '=', $modelU->user_id)->delete();

                        $subjectArr = explode(',', $request->subject_id);
                        $yearGroupArr = array();

                        foreach ($subjectArr as $subjectId) {
                            $yearGroupId = Subject::where('subject_id', $subjectId)->value('year_group_id');
                            array_push($yearGroupArr, $yearGroupId);
                            $modelUserSubject = new UserSubject;
                            $modelUserSubject->user_id = $modelU->user_id;
                            $modelUserSubject->subject_id = $subjectId;
                            $modelUserSubject->status = GlobalVars::ACTIVE_STATUS;
                            $modelUserSubject->save();
                        }
                        $yearGroupArr = array_unique($yearGroupArr);
                        foreach ($yearGroupArr as $yearGroupId) {
                            $modelUserYearGroup = new UserYearGroup;
                            $modelUserYearGroup->user_id = $modelU->user_id;
                            $modelUserYearGroup->year_group_id = $yearGroupId;
                            $modelUserYearGroup->status = GlobalVars::ACTIVE_STATUS;
                            $modelUserYearGroup->save();
                        }
                    }

                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Email already registered.']];
                    //Rollback transaction
                    DB::rollback();
                    return response()->json($reponse, 400);
                }
            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                //Rollback transaction
                DB::rollback();
                return response()->json($reponse, 400);
            }
        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            //Rollback transaction
            DB::rollback();
            return response()->json($reponse, 500);
        }
        //Commit transaction
        DB::commit();

        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Teacher added successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/{subdomain}/import-teachers",
     *     summary="Bulk import teachers",
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
     *                 @OA\Property(property="subject_id", type="string",format="1,2,3"),
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
     *     @OA\Response(response="200", description="teacher bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importTeacher(Request $request)
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

                    $data = \Excel::import(new TeacherImport($request->subject_id, $user->tenant_id), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk teacher import processed successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/get-teacher-by-id",
     * summary="Get teacher details by id",
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
     *     description="Pass encrpted record teacher_id",
     *    @OA\JsonContent(
     *       required={"teacher_id"},
     *       @OA\Property(property="teacher_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Student details"),
     * @OA\Response(response="401", description="Student not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $teacher_id = CommonHelper::decryptId($request->teacher_id);
                $resDetails = ViewTeacher::find($teacher_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher details by id', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/update-teacher",
     * summary="Update teacher master data of tenant as per subdomain",
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
     *     description="update teacher",
     *    @OA\JsonContent(
     *       required={"teacher_id","first_name","email","password","phone","gender"},
     *       @OA\Property(property="teacher_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="department_id", type="string"),
     *       @OA\Property(property="ni_number", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="about", type="string", default=null),
     *       @OA\Property(property="end_date_id", type="date", default=null),
     *       @OA\Property(property="end_date_dbs", type="date", default=null),
     *       @OA\Property(property="id_file", type="string", default=null),
     *       @OA\Property(property="dbs_certificate_file", type="string", default=null),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *       @OA\Property(property="subject_id", type="string",format="1,2,3"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant teacher master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateTeacher(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->teacher_id);
                // print_r($modelU);
                $modelU->phone = $request->phone;
                $modelU->status = $request->status;
                if (isset($request->profile_image) && $request->profile_image != '') {
                    //image without mime information
                    $imageDataWithoutMime = explode('base64,', $request->profile_image);
                    $file = $request->profile_image;
                    if (isset($imageDataWithoutMime[1])) {
                        $file = base64_decode($imageDataWithoutMime[1]);
                    }
                    if ($file) {

                        $folderPath1 = \GlobalVars::USER_PIC_PATH;
                        $extension = 'png';
                        if (isset($imageDataWithoutMime[1])) {
                            $extension = explode('/', mime_content_type($request->profile_image))[1];
                        }
                        // dd($extension);

                        $image_base64_1 = $file;
                        $file1 = $folderPath1 . uniqid() . '.' . $extension;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file1, $image_base64_1);
                        if ($modelU->user_logo != '') {
                            $arrFile = explode('.', $modelU->user_logo);
                            if (isset($arrFile[1])) {
                                Storage::disk('public')->delete($modelU->user_logo);
                            }
                        }

                        $modelU->user_logo = $file1;
                    }
                }
                $modelU->save();

                $getProfileId = TenantUserProfile::where('user_id', '=', $request->teacher_id)->pluck('user_profile_id');
                // dd($getProfileId);

                $modelUP = TenantUserProfile::find($getProfileId[0]);
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $request->first_name;
                $modelUP->last_name = $request->last_name;
                $modelUP->address = $request->address;
                $modelUP->gender = $request->gender;
                $modelUP->ni_number = $request->ni_number;
                $modelUP->department_id = $request->department_id;
                $modelUP->about = $request->about;
                $modelUP->end_date_id = $request->end_date_id;
                $modelUP->end_date_dbs = $request->end_date_dbs;
                if (isset($request->id_file) && $request->id_file != '') {
                    // dd('id file');
                    //image without mime information
                    $imageDataWithoutMime = explode('base64,', $request->id_file);
                    $file = $request->id_file;
                    if (isset($imageDataWithoutMime[1])) {
                        $file = base64_decode($imageDataWithoutMime[1]);
                    }
                    if ($file) {

                        $folderPath1 = \GlobalVars::USER_IDFILE_PATH . $user->tenant_id . '/';
                        $extension = 'png';
                        if (isset($imageDataWithoutMime[1])) {
                            $extension = explode('/', mime_content_type($request->id_file))[1];
                        }
                        // dd($extension);

                        $image_base64_1 = $file;
                        $file1 = $folderPath1 . uniqid() . '.' . $extension;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file1, $image_base64_1);

                        $modelUP->id_file = $file1;
                    }
                }
                if (isset($request->dbs_certificate_file) && $request->dbs_certificate_file != '') {
                    // dd('dbs file');
                    //image without mime information
                    $imageDataWithoutMime = explode('base64,', $request->dbs_certificate_file);
                    $file = $request->dbs_certificate_file;
                    if (isset($imageDataWithoutMime[1])) {
                        $file = base64_decode($imageDataWithoutMime[1]);
                    }
                    if ($file) {

                        $folderPath1 = \GlobalVars::USER_DBSFILE_PATH . $user->tenant_id . '/';
                        $extension = 'png';
                        if (isset($imageDataWithoutMime[1])) {
                            $extension = explode('/', mime_content_type($request->dbs_certificate_file))[1];
                        }
                        // dd($extension);

                        $image_base64_1 = $file;
                        $file1 = $folderPath1 . uniqid() . '.' . $extension;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file1, $image_base64_1);

                        $modelUP->dbs_certificate_file = $file1;
                    }
                }

                $modelUP->save();

                if ($request->subject_id != "") {
                    UserSubject::where('user_id', '=', $modelU->user_id)->delete();
                    UserYearGroup::where('user_id', '=', $modelU->user_id)->delete();

                    $subjectArr = explode(',', $request->subject_id);
                    $yearGroupArr = array();

                    foreach ($subjectArr as $subjectId) {
                        $yearGroupId = Subject::where('subject_id', $subjectId)->value('year_group_id');
                        array_push($yearGroupArr, $yearGroupId);
                        $modelUserSubject = new UserSubject;
                        $modelUserSubject->user_id = $modelU->user_id;
                        $modelUserSubject->subject_id = $subjectId;
                        $modelUserSubject->status = GlobalVars::ACTIVE_STATUS;
                        $modelUserSubject->save();
                    }
                    $yearGroupArr = array_unique($yearGroupArr);
                    foreach ($yearGroupArr as $yearGroupId) {
                        $modelUserYearGroup = new UserYearGroup;
                        $modelUserYearGroup->user_id = $modelU->user_id;
                        $modelUserYearGroup->year_group_id = $yearGroupId;
                        $modelUserYearGroup->status = GlobalVars::ACTIVE_STATUS;
                        $modelUserYearGroup->save();
                    }
                }

            } else {
                //Rollback transaction
                DB::rollback();
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            // throw ($e);
            //Rollback transaction
            DB::rollback();
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }
        //Commit transaction
        DB::commit();
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Teacher updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/teacher/subjects",
     * summary="Get teacher subjects",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher subject details"),
     * @OA\Response(response="401", description="teacher subject details not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherSubjects()
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }
                $teacher_id = $user->user_id;

                $resDetails = UserSubject::join('subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                    ->select('user_subjects.user_id', 'subjects.*', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"))
                    ->where('user_subjects.user_id', $teacher_id)
                    ->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher subject details', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/teacher/lessons",
     * summary="Get teacher lessons",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="provide subject_id",
     *    @OA\JsonContent(
     *       required={"subject_id"},
     *       @OA\Property(property="subject_id", type="int"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher lessons details"),
     * @OA\Response(response="401", description="teacher lessons details not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherLessons(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }

                $teacher_id = $user->user_id;
                $page = $request->page;
                $search_text = $request->search_text;
                $subject_id = $request->subject_id;

                if ($subject_id != '' || $subject_id != null) {
                    $subject_id = CommonHelper::decryptId($request->subject_id);

                    $resDetails = Lesson::join('subjects', 'subjects.subject_id', 'lessons.subject_id', 'INNER')
                        ->join('user_subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                        ->select('lessons.*', 'subjects.subject_name', 'subjects.board_id', 'subjects.subject_image', 'subjects.academic_year_id', 'subjects.year_group_id', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"), DB::raw("(select count(*) from examinations where examinations.examination_type='Q' and (examinations.lesson_id=lessons.lesson_id or (examinations.lesson_id=null and examinations.subject_id=subjects.subject_id))) as quizcnt"), DB::raw("(select count(*) from examinations where examinations.examination_type='A' and (examinations.lesson_id=lessons.lesson_id or (examinations.lesson_id=null and examinations.subject_id=subjects.subject_id))) as assesmentcnt"))
                        ->where('user_subjects.user_id', $teacher_id)
                        ->where('subjects.subject_id', $subject_id)
                        ->where(function ($query) use ($search_text) {
                            if ($search_text != null) {
                                $query->where('subjects.subject_name', 'like', '%' . $search_text . '%')
                                    ->orWhere('lessons.lesson_number', 'like', '%' . $search_text . '%')
                                    ->orWhere('lessons.lesson_name', 'like', '%' . $search_text . '%')
                                    ->orWhere(DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id)"), 'like', '%' . $search_text . '%');
                            }
                        })
                        ->orderBy('lessons.subject_id', 'asc')
                        ->orderByRaw('CONVERT(lessons.lesson_number, SIGNED) asc')
                        ->orderBy('lessons.lesson_name', 'asc')
                        ->get();

                    $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher lesson details', 'details' => $resDetails], 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No subject provided.']];
                    return response()->json($reponse, 400);
                }

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
     * path="/api/{subdomain}/teacher/course-status",
     * summary="Get teacher course status",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher course status"),
     * @OA\Response(response="401", description="teacher course status not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherCourseStatus()
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }
                $teacher_id = $user->user_id;

                $subjectDetails = UserSubject::join('subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                    ->select('user_subjects.user_id', 'subjects.*', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"))
                    ->where('user_subjects.user_id', $teacher_id)
                    ->get();

                //deduce lessons on each subject
                $listing = array();
                foreach ($subjectDetails as $record) {
                    $lessonDetails = Lesson::join('subjects', 'subjects.subject_id', 'lessons.subject_id', 'INNER')
                        ->join('user_subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                        ->select('lessons.*', DB::raw("(select count(*) from examinations where examinations.examination_type='Q' and (examinations.lesson_id=lessons.lesson_id or (examinations.lesson_id=null and examinations.subject_id=subjects.subject_id))) as quizcnt"), DB::raw("(select count(*) from examinations where examinations.examination_type='A' and (examinations.lesson_id=lessons.lesson_id or (examinations.lesson_id=null and examinations.subject_id=subjects.subject_id))) as assesmentcnt"), DB::raw("(select count(*) from attendances where attendances.lesson_id=lessons.lesson_id) as attendancecnt"))
                        ->where('user_subjects.user_id', $teacher_id)
                        ->where('subjects.subject_id', $record->subject_id)
                        ->get();

                    $ele = [
                        'subject_id' => $record->subject_id,
                        'subject_name' => $record->subject_name,
                        'year_group_id' => $record->year_group_id,
                        'yeargroup' => $record->yeargroup,
                        'academic_year_id' => $record->academic_year_id,
                        'academic_year' => $record->academic_year,
                        'lessons' => $lessonDetails,
                    ];
                    array_push($listing, $ele);
                }

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher course status details', 'listing' => $listing], 'error' => []];
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
     * path="/api/{subdomain}/teacher/students",
     * summary="Get teacher students",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher lessons details"),
     * @OA\Response(response="401", description="teacher lessons details not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherStudents(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }

                $teacher_id = $user->user_id;
                $page = $request->page;
                $search_text = $request->search_text;

                $tutSubjects = UserSubject::where('user_subjects.user_id', $teacher_id)->distinct('subject_id')->pluck('subject_id')->toArray();

                // print_r($tutSubjects);die;

                $tusUserIds = UserSubject::whereIn('user_subjects.subject_id', $tutSubjects)->distinct('user_id')->pluck('user_id')->toArray();

                // print_r($tusUserIds);die;

                $resDetails = ViewStudent::whereIn('user_id', $tusUserIds)
                    ->where(function ($query) use ($search_text) {
                        if ($search_text != null) {
                            $query->where('first_name', 'like', '%' . $search_text . '%')
                                ->orWhere('last_name', 'like', '%' . $search_text . '%')
                                ->orWhere('code', 'like', '%' . $search_text . '%')
                                ->orWhere('email', 'like', '%' . $search_text . '%');
                        }
                    })
                    ->where('status', GlobalVars::ACTIVE_STATUS)
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher student details', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/teacher/students-by-yrgpid",
     * summary="Get yeargroup teacher students",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass encrpted record year_group_id",
     *    @OA\JsonContent(
     *       required={"year_group_id"},
     *       @OA\Property(property="year_group_id", type="int"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher yeargroup student list"),
     * @OA\Response(response="401", description="teacher yeargroup student list not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherStudentsByYrGpId(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }

                $teacher_id = $user->user_id;
                $year_group_id = $request->year_group_id;

                $tutSubjects = UserSubject::where('user_subjects.user_id', $teacher_id)->distinct('subject_id')->pluck('subject_id')->toArray();

                // print_r($tutSubjects);die;

                $tusUserIds = UserSubject::whereIn('user_subjects.subject_id', $tutSubjects)->distinct('user_id')->pluck('user_id')->toArray();

                $tusYrGpUserIds = UserYearGroup::where('user_year_groups.year_group_id', $year_group_id)->distinct('user_id')->pluck('user_id')->toArray();

                // print_r($tusUserIds);die;

                $resDetails = ViewStudent::whereIn('user_id', $tusUserIds)
                    ->whereIn('user_id', $tusYrGpUserIds)
                    ->where('status', GlobalVars::ACTIVE_STATUS)
                    ->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher student details', 'list' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/teacher/students-by-subjectid",
     * summary="Get subject teacher students",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass record subject_id",
     *    @OA\JsonContent(
     *       required={"subject_id"},
     *       @OA\Property(property="subject_id", type="int"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher subject student list"),
     * @OA\Response(response="401", description="teacher subject student list not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherStudentsBySubjectId(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }

                $teacher_id = $user->user_id;
                $subject_id = $request->subject_id;

                // $tutSubjects = UserSubject::where('user_subjects.user_id', $teacher_id)->distinct('subject_id')->pluck('subject_id')->toArray();

                // print_r($tutSubjects);die;

                $tusUserIds = UserSubject::where('user_subjects.subject_id', $subject_id)->distinct('user_id')->pluck('user_id')->toArray();

                // $tusYrGpUserIds = UserYearGroup::where('user_year_groups.year_group_id', $year_group_id)->distinct('user_id')->pluck('user_id')->toArray();

                // print_r($tusUserIds);die;

                $resDetails = ViewStudent::whereIn('user_id', $tusUserIds)
                    ->where('status', GlobalVars::ACTIVE_STATUS)
                    ->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher student details', 'list' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/teacher/teacher-assistants",
     * summary="Get teacher's teacher assistant list",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher's teacher assistant list "),
     * @OA\Response(response="401", description="teacher's teacher assistant list not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherTa(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }

                $teacher_id = $user->user_id;
                $page = $request->page;
                $search_text = $request->search_text;

                $tutSubjects = UserSubject::where('user_subjects.user_id', $teacher_id)->distinct('subject_id')->pluck('subject_id')->toArray();

                // print_r($tutSubjects);die;

                $tusUserIds = UserSubject::whereIn('user_subjects.subject_id', $tutSubjects)->distinct('user_id')->pluck('user_id')->toArray();

                // print_r($tusUserIds);die;

                $resDetails = ViewTeacherAssistant::whereIn('user_id', $tusUserIds)
                    ->where(function ($query) use ($search_text) {
                        if ($search_text != null) {
                            $query->where('first_name', 'like', '%' . $search_text . '%')
                                ->orWhere('last_name', 'like', '%' . $search_text . '%')
                                ->orWhere('code', 'like', '%' . $search_text . '%')
                                ->orWhere('email', 'like', '%' . $search_text . '%');
                        }
                    })
                    ->where('status', GlobalVars::ACTIVE_STATUS)
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher assistant list', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/teacher/all-lessons",
     * summary="Get teacher all lessons",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="teacher lessons details"),
     * @OA\Response(response="401", description="teacher lessons details not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherAllLessons(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_TEACHER_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }

                $teacher_id = $user->user_id;
                $page = $request->page;
                $search_text = $request->search_text;

                $resDetails = Lesson::join('subjects', 'subjects.subject_id', 'lessons.subject_id', 'INNER')
                    ->join('user_subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                    ->select('lessons.*', 'subjects.subject_name', 'subjects.board_id', 'subjects.subject_image', 'subjects.academic_year_id', 'subjects.year_group_id', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"), DB::raw("(select count(*) from examinations where examinations.examination_type='Q' and (examinations.lesson_id=lessons.lesson_id or (examinations.lesson_id=null and examinations.subject_id=subjects.subject_id))) as quizcnt"), DB::raw("(select count(*) from examinations where examinations.examination_type='A' and (examinations.lesson_id=lessons.lesson_id or (examinations.lesson_id=null and examinations.subject_id=subjects.subject_id))) as assesmentcnt"))
                    ->where('user_subjects.user_id', $teacher_id)
                    ->where(function ($query) use ($search_text) {
                        if ($search_text != null) {
                            $query->where('subjects.subject_name', 'like', '%' . $search_text . '%')
                                ->orWhere('lessons.lesson_number', 'like', '%' . $search_text . '%')
                                ->orWhere('lessons.lesson_name', 'like', '%' . $search_text . '%')
                                ->orWhere(DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id)"), 'like', '%' . $search_text . '%');
                        }
                    })
                    ->orderBy('lessons.subject_id', 'asc')
                    ->orderByRaw('CONVERT(lessons.lesson_number, SIGNED) asc')
                    ->orderBy('lessons.lesson_name', 'asc')
                    ->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher lesson details', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/teacher/get-lessonid-skillmap",
     * summary="Get Lesson wise skillmap list for teacher passing tenant token as per subdomain",
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

     * @OA\Response(response="200", description="tenant teacher skillmap list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getLessonIdSkillMap(Request $request)
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
                        ->with('sub_topics')
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
     * path="/api/{subdomain}/teacher/get-student-lessonid-skillmap",
     * summary="Get Lesson wise skillmap list for student passing tenant token as per subdomain",
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
     *       required={"lesson_id","user_id"},
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="user_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant teacher skillmap list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudentLessonIdSkillMap(Request $request)
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
                $user_id = $request->user_id;
                if (($lesson_id != '' || $lesson_id != null) && $user_id != null) {

                    $listing = Topic::join('sub_topics', 'sub_topics.topic_id', 'topics.topic_id', 'INNER')
                        ->select('topics.topic', 'topics.topic_id', 'sub_topics.*', DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.tc =  '1' AND user_results.user_id =  $user_id) as tc"), DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.ms =  '1' AND user_results.user_id =  $user_id) as ms"), DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.ps =  '1' AND user_results.user_id =  $user_id) as ps"), DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.at =  '1' AND user_results.user_id =  $user_id) as at"))
                        ->withCount('sub_topics')
                        ->where('topics.lesson_id', '=', $lesson_id)
                        ->orderBy('topics.topic', 'asc')
                        ->get();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Lesson wise topic list fetched successfully.', 'listing' => $listing), 'error' => ''];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No information provided.']];
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
     * path="/api/{subdomain}/all-active-teachers",
     * summary="Get active teacher master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant teacher details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getAllActiveTeachers()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = ViewTeacher::where('status', GlobalVars::ACTIVE_STATUS)->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Active teachers data fetched successfully.', 'listing' => $listing), 'error' => ''];
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
