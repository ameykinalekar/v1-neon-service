<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\Topic;
use App\Models\UserSibling;
use App\Models\UserSubject;
use App\Models\UserYearGroup;
use App\Models\ViewStudent;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/students",
     * summary="Get student master data of tenant as per subdomain",
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
     * @OA\Response(response="200", description="tenant student details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudents(Request $request)
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

                $listing = ViewStudent::where(function ($query) use ($search_text, $subjectUsers, $search_subject_id, $search_academic_year_id, $search_year_group_id) {
                    if ($search_text != null) {
                        $query->where('first_name', 'like', '%' . $search_text . '%')
                            ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                            ->orWhere('last_name', 'like', '%' . $search_text . '%')
                            ->orWhere('email', 'like', '%' . $search_text . '%')
                            ->orWhere('parent_email', 'like', '%' . $search_text . '%')
                            ->orWhere('phone', 'like', '%' . $search_text . '%')
                            ->orWhere('parent_phone', 'like', '%' . $search_text . '%')
                            ->orWhere('code', 'like', '%' . $search_text . '%')
                            ->orWhere('address', 'like', '%' . $search_text . '%');
                    }
                    if ($search_academic_year_id != null || $search_year_group_id != null || $search_subject_id != null) {
                        $query->whereIn('user_id', $subjectUsers);
                    }
                })
                    ->orderBy('first_name', 'asc')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'student data fetched successfully.', 'students' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/create-student",
     * summary="Create student master data of tenant as per subdomain",
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
     *     description="add new student",
     *    @OA\JsonContent(
     *       required={"first_name","email","password","phone","gender","batch_type_id","parent_name","parent_phone","parent_email"},
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="batch_type_id", type="int"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="parent_name", type="string"),
     *       @OA\Property(property="parent_phone", type="string"),
     *       @OA\Property(property="parent_email", type="string"),
     *       @OA\Property(property="have_sensupport_healthcare_plan", type="string", default="N"),
     *       @OA\Property(property="first_lang_not_eng", type="string", default="N"),
     *       @OA\Property(property="freeschool_eligible", type="string", default="N"),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *       @OA\Property(property="subject_id", type="string",format="1,2,3"),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant student master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createStudent(Request $request)
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
                    $student_code = CommonHelper::studentCode();

                    $modelU = new TenantUser;
                    $modelU->email = $request->email;
                    $modelU->tenant_id = $user->tenant_id;
                    $modelU->password = $request->password;
                    $modelU->user_type = GlobalVars::TENANT_USER_TYPE;
                    $modelU->role = GlobalVars::TENANT_STUDENT_ROLE;
                    $modelU->phone = $request->phone;
                    $modelU->code = $student_code;
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
                    $modelUP->batch_type_id = $request->batch_type_id;
                    $modelUP->parent_name = $request->parent_name;
                    $modelUP->parent_phone = $request->parent_phone;
                    $modelUP->parent_email = $request->parent_email;
                    $modelUP->have_sensupport_healthcare_plan = $request->have_sensupport_healthcare_plan ?? 'N';
                    $modelUP->first_lang_not_eng = $request->first_lang_not_eng ?? 'N';
                    $modelUP->freeschool_eligible = $request->freeschool_eligible ?? 'N';
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
                    //automatic parent creation

                    if ($modelUP->parent_email != '') {

                        $ulistParent = TenantUser::where('email', '=', $modelUP->parent_email)->first();

                        if (empty($ulistParent)) {
                            //create parent & link subling
                            $parent_code = CommonHelper::parentCode();
                            $modelUParent = new TenantUser;
                            $modelUParent->email = $modelUP->parent_email;
                            $modelUParent->tenant_id = $user->tenant_id;
                            $modelUParent->password = $request->password;
                            $modelUParent->user_type = GlobalVars::TENANT_PARENT_USER_TYPE;
                            $modelUParent->role = GlobalVars::TENANT_PARENT_ROLE;
                            $modelUParent->phone = $modelUP->parent_phone;
                            $modelUParent->code = $parent_code;
                            $modelUParent->status = GlobalVars::ACTIVE_STATUS;
                            $modelUParent->save();

                            $modelUPParent = new TenantUserProfile;
                            $modelUPParent->user_id = $modelUParent->user_id;
                            $modelUPParent->first_name = $modelUP->parent_name;

                            $modelUPParent->save();

                            $parent_user_id = $modelUParent->user_id;
                            $sibling_user_id = $modelU->user_id;

                            $chkSiblingExist = UserSibling::where('parent_user_id', '=', $parent_user_id)->where('sibling_user_id', '=', $sibling_user_id)->whereNull('token')->first();
                            if (empty($chkSiblingExist)) {
                                //need to map sibling
                                $model = new UserSibling;
                                $model->parent_user_id = $parent_user_id;
                                $model->sibling_user_id = $sibling_user_id;
                                $model->token = null;
                                $model->status = GlobalVars::ACTIVE_STATUS;
                                $model->save();
                            }

                        } else {
                            //map subling only
                            $parent_user_id = $ulistParent->user_id;
                            $sibling_user_id = $modelU->user_id;

                            $chkSiblingExist = UserSibling::where('parent_user_id', '=', $parent_user_id)->where('sibling_user_id', '=', $sibling_user_id)->whereNull('token')->first();
                            if (empty($chkSiblingExist)) {
                                //need to map sibling
                                $model = new UserSibling;
                                $model->parent_user_id = $parent_user_id;
                                $model->sibling_user_id = $sibling_user_id;
                                $model->token = null;
                                $model->status = GlobalVars::ACTIVE_STATUS;
                                $model->save();
                            }

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

        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student added successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/{subdomain}/import-students",
     *     summary="Bulk import students",
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
     *                 @OA\Property(property="batch_type_id", type="int"),
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
     *     @OA\Response(response="200", description="student bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importStudent(Request $request)
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

                    $data = \Excel::import(new StudentImport($request->subject_id ?? '', $user->tenant_id, $request->batch_type_id ?? ''), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk student import processed successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/get-student-by-id",
     * summary="Get student details by id",
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
     *     description="Pass encrpted record student_id",
     *    @OA\JsonContent(
     *       required={"student_id"},
     *       @OA\Property(property="student_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Student details"),
     * @OA\Response(response="401", description="Student not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudentById(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $student_id = CommonHelper::decryptId($request->student_id);
                // $academic_year_id = $request->academic_year_id;
                // die("==" . $academic_year_id);
                $resDetails = ViewStudent::find($student_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Student details by id', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/update-student",
     * summary="Update student master data of tenant as per subdomain",
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
     *     description="update student",
     *    @OA\JsonContent(
     *       required={"student_id","first_name","phone","gender","batch_type_id","parent_name","parent_phone","parent_email"},
     *       @OA\Property(property="student_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="batch_type_id", type="int"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="parent_name", type="string"),
     *       @OA\Property(property="parent_phone", type="string"),
     *       @OA\Property(property="parent_email", type="string"),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *       @OA\Property(property="subject_id", type="string",format="1,2,3"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant student master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateStudent(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->student_id);
                // print_r($modelU);
                if ($modelU->code == null) {
                    $student_code = CommonHelper::studentCode();
                    $modelU->code = $student_code;
                }
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

                $getProfileId = TenantUserProfile::where('user_id', '=', $request->student_id)->pluck('user_profile_id');
                // dd($getProfileId);

                $modelUP = TenantUserProfile::find($getProfileId[0]);
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $request->first_name;
                $modelUP->last_name = $request->last_name;
                $modelUP->address = $request->address;
                $modelUP->gender = $request->gender;
                $modelUP->batch_type_id = $request->batch_type_id;
                $modelUP->parent_name = $request->parent_name;
                $modelUP->parent_phone = $request->parent_phone;
                $modelUP->parent_email = $request->parent_email;
                $modelUP->have_sensupport_healthcare_plan = $request->have_sensupport_healthcare_plan ?? 'N';
                $modelUP->first_lang_not_eng = $request->first_lang_not_eng ?? 'N';
                $modelUP->freeschool_eligible = $request->freeschool_eligible ?? 'N';
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
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/my-courses",
     * summary="Get student subjects",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="Student subject details"),
     * @OA\Response(response="401", description="Student subject details not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudentMyCourses()
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_STUDENT_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }
                $student_id = $user->user_id;

                $resDetails = UserSubject::join('subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                    ->select('user_subjects.user_id', 'subjects.*', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"))
                    ->where('user_subjects.user_id', $student_id)
                    ->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Student details', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/student/get-subjectid-lessons",
     * summary="Get student subject wise lesson list passing tenant token as per subdomain",
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
    public function studentSubjectIdLessons(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $subject_id = $request->subject_id;
                if ($subject_id != '' || $subject_id != null) {
                    $subject_id = CommonHelper::decryptId($request->subject_id);
                    $subject = Subject::with('yeargroup', 'academicyear')->find($subject_id);
                    $listing = Lesson::select('lessons.*', DB::raw('COALESCE((SELECT ROUND(((user_results.marks_obtained/user_results.total_marks)*100),2) FROM user_results Inner Join examinations ON examinations.examination_id = user_results.examination_id where user_results.is_reviewed="Y" and examinations.lesson_id=lessons.lesson_id and examinations.subject_id=lessons.subject_id order by user_results.created_at desc , ((user_results.marks_obtained/user_results.total_marks)*100) desc limit 1),0) as percentage'), DB::raw('(SELECT user_results.grade FROM user_results Inner Join examinations ON examinations.examination_id = user_results.examination_id where user_results.is_reviewed="Y" and examinations.lesson_id=lessons.lesson_id and examinations.subject_id=lessons.subject_id order by user_results.created_at desc , ((user_results.marks_obtained/user_results.total_marks)*100) desc limit 1) as grade'))
                        ->with('subject', 'subject.yeargroup', 'subject.academicyear')
                        ->where('subject_id', '=', $subject_id)
                        ->get();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student Subject wise lesson list fetched successfully.', 'listing' => $listing, 'subject_info' => $subject), 'error' => ''];
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
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/initial-subject-grades",
     * summary="Map initial subject grades of student master data of tenant as per subdomain",
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
     *     description="map subject grades",
     *    @OA\JsonContent(
     *       required={"user_id"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="mapping", type="array", description="subject and corresponding grades",
     *       @OA\Items(
     *          @OA\Property(
     *              property="subject_id",
     *              description="subject id",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="grade_id",
     *              description="initial grade",
     *              type="int",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant student subject-grade mapping"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function mapStudentSubjectGrades(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->user_id);
                // dd($modelU);
                if (!empty($modelU)) {
                    $mapping = $request->mapping ?? array();
                    if (count($mapping) > 0) {
                        for ($i = 0; $i < count($mapping); $i++) {
                            $checkUserSubject = UserSubject::where('user_id', $request->user_id)->where('subject_id', $mapping[$i]['subject_id'])->first();

                            if (!empty($checkUserSubject)) {
                                $checkUserSubject->grade_id = $mapping[$i]['grade_id'];
                                $checkUserSubject->save();
                            }
                        }
                    }
                } else {
                    //Rollback transaction
                    DB::rollback();
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such student exist']];
                    return response()->json($reponse, 400);
                }

            } else {
                //Rollback transaction
                DB::rollback();
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student subject mapping updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/get-students-by-subjectid",
     * summary="Get student list by subject id",
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
     *       @OA\Property(property="enroll_date", type="date",default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="Student list"),
     * @OA\Response(response="401", description="Student not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudentListBySubjectId(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // $subject_id = CommonHelper::decryptId($request->subject_id);
                $subject_id = $request->subject_id;
                $enroll_date = $request->enroll_date ?? null;

                $userArray = UserSubject::where('user_subjects.subject_id', $subject_id)
                    ->where(function ($query) use ($enroll_date) {

                        if ($enroll_date != null) {
                            $query->where('created_at', '<=', $enroll_date);
                        }

                    })
                    ->pluck('user_id')->toArray();

                $listing = ViewStudent::whereIn('user_id', $userArray)->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Student listing by subject_id', 'listing' => $listing], 'error' => []];
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
     * path="/api/{subdomain}/student/get-profile-completion",
     * summary="Get student profile completion percentage",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="Student profile completion"),
     * @OA\Response(response="401", description="Student not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudentProfileCompletion()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            // if ($user == null) {
            //     $user = JWTAuth::parseToken()->authenticate();
            // }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // $subject_id = CommonHelper::decryptId($request->subject_id);

                $student = ViewStudent::where('user_id', $user->user_id)->first();
                $completion = 0;
                if ($student->first_name != '') {
                    $completion++;
                }
                if ($student->email != '') {
                    $completion++;
                }
                if ($student->phone != '') {
                    $completion++;
                }
                // if ($student->subject_ids != '') {
                //     $completion++;
                // }
                if ($student->user_logo != '') {
                    $completion++;
                }
                if ($student->cover_picture != '') {
                    $completion++;
                }
                if ($student->parent_name != '') {
                    $completion++;
                }
                if ($student->parent_phone != '') {
                    $completion++;
                }
                if ($student->parent_email != '') {
                    $completion++;
                }
                if ($student->address != '') {
                    $completion++;
                }
                if ($student->gender != '') {
                    $completion++;
                }
                $total_checks = 10;
                $completion_p = round((($completion / $total_checks) * 100), 0);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Student profile completion percentage', 'completion' => $completion_p], 'error' => []];
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
     * path="/api/{subdomain}/student/update-cover-image",
     * summary="Update student cover image data of tenant as per subdomain",
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
     *     description="update student",
     *    @OA\JsonContent(
     *       required={"student_id","cover_picture"},
     *       @OA\Property(property="student_id", type="int"),
     *       @OA\Property(property="cover_picture", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant student master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateStudentCoverImage(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $getProfileId = TenantUserProfile::where('user_id', '=', $request->student_id)->pluck('user_profile_id');
                // dd($getProfileId);

                $modelUP = TenantUserProfile::find($getProfileId[0]);
                if (isset($request->cover_picture) && $request->cover_picture != '') {
                    //image without mime information
                    $imageDataWithoutMime = explode('base64,', $request->cover_picture);
                    $file = $request->cover_picture;
                    if (isset($imageDataWithoutMime[1])) {
                        $file = base64_decode($imageDataWithoutMime[1]);
                    }
                    if ($file) {

                        $folderPath1 = \GlobalVars::USER_PIC_PATH . $user->tenant_id . '/';
                        $extension = 'png';
                        if (isset($imageDataWithoutMime[1])) {
                            $extension = explode('/', mime_content_type($request->cover_picture))[1];
                        }
                        // dd($extension);

                        $image_base64_1 = $file;
                        $file1 = $folderPath1 . uniqid() . '.' . $extension;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file1, $image_base64_1);
                        if ($modelUP->cover_picture != '') {
                            $arrFile = explode('.', $modelUP->cover_picture);
                            if (isset($arrFile[1])) {
                                Storage::disk('public')->delete($modelUP->cover_picture);
                            }
                        }

                        $modelUP->cover_picture = $file1;
                    }
                }

                $modelUP->save();

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
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student cover image updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/student/update-profile-image",
     * summary="Update student profile image data of tenant as per subdomain",
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
     *     description="update student",
     *    @OA\JsonContent(
     *       required={"student_id","profile_image"},
     *       @OA\Property(property="student_id", type="int"),
     *       @OA\Property(property="profile_image", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant student master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateStudentProfileImage(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->student_id);

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
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student cover image updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/student/get-lessonid-skillmap",
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

                    $listing = Topic::join('sub_topics', 'sub_topics.topic_id', 'topics.topic_id', 'INNER')
                        ->select('topics.topic', 'topics.topic_id', 'sub_topics.*', DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.tc =  '1' AND user_results.user_id =  $user->user_id) as tc"), DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.ms =  '1' AND user_results.user_id =  $user->user_id) as ms"), DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.ps =  '1' AND user_results.user_id =  $user->user_id) as ps"), DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examination_questions.sub_topic_id =  sub_topics.sub_topic_id AND examination_questions.at =  '1' AND user_results.user_id =  $user->user_id) as at"))
                        ->withCount('sub_topics')
                        ->where('topics.lesson_id', '=', $lesson_id)
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
     * path="/api/{subdomain}/student/course-status",
     * summary="Get student course status",
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
    public function getCourseStatus()
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                if ($user->user_type != GlobalVars::TENANT_USER_TYPE || $user->role != GlobalVars::TENANT_STUDENT_ROLE) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'You are not authorised.']];
                    return response()->json($reponse, 400);
                }
                $student_id = $user->user_id;

                $subjectDetails = UserSubject::join('subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                    ->select('user_subjects.user_id', 'subjects.*', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"))
                    ->where('user_subjects.user_id', $student_id)
                    ->get();

                //deduce lessons on each subject
                $listing = array();
                foreach ($subjectDetails as $record) {
                    $lessonDetails = Lesson::join('subjects', 'subjects.subject_id', 'lessons.subject_id', 'INNER')
                        ->join('user_subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                        ->select('lessons.*', DB::raw("(SELECT round(COALESCE((Sum(((user_result_inputs.marks_given/examination_questions.point)*100))/count(*)),0),2) as avgp FROM examination_questions Inner Join examinations ON examination_questions.examination_id = examinations.examination_id Inner Join user_result_inputs ON examination_questions.examination_question_id = user_result_inputs.examination_question_id Inner Join user_results ON user_results.user_result_id = user_result_inputs.user_result_id WHERE examinations.lesson_id = lessons.lesson_id  AND user_results.user_id =  $user->user_id) as result"), DB::raw("(select count(*) from attendances where attendances.lesson_id=lessons.lesson_id and attendances.user_id= $student_id  and is_present=1) as attendancecnt"))
                        ->where('user_subjects.user_id', $student_id)
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
     * path="/api/{subdomain}/all-active-students",
     * summary="Get active student master data of tenant as per subdomain",
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
    public function getAllActiveStudents()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Active students data fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/get-pupil-count",
     * summary="Get pupil graph data (teacher) of tenant as per subdomain",
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
     *     description="optional parameters",
     *    @OA\JsonContent(
     *       @OA\Property(property="have_sensupport", type="string",default=null),
     *       @OA\Property(property="free_meal", type="string",default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant pupil data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getPupilCount(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $have_sensupport = $request->have_sensupport ?? null;
                $free_meal = $request->free_meal ?? null;

                // dd($have_sensupport);

                $total_pupil = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)
                    ->where(function ($query) use ($have_sensupport, $free_meal) {
                        if (isset($have_sensupport) && ($have_sensupport != '' || $have_sensupport != 'N')) {
                            $query->where('have_sensupport_healthcare_plan', 'Y');
                        }
                        if (isset($free_meal) && ($free_meal != '' || $free_meal != 'N')) {
                            $query->where('freeschool_eligible', 'Y');
                        }
                    })
                    ->count();
                $total_male_pupil = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)
                    ->where('gender', 'Male')
                    ->where(function ($query) use ($have_sensupport, $free_meal) {
                        if (isset($have_sensupport) && ($have_sensupport != '' || $have_sensupport != 'N')) {
                            $query->where('have_sensupport_healthcare_plan', 'Y');
                        }
                        if (isset($free_meal) && ($free_meal != '' || $free_meal != 'N')) {
                            $query->where('freeschool_eligible', 'Y');
                        }
                    })
                    ->count();
                $total_female_pupil = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)
                    ->where('gender', 'Female')
                    ->where(function ($query) use ($have_sensupport, $free_meal) {
                        if (isset($have_sensupport) && ($have_sensupport != '' || $have_sensupport != 'N')) {
                            $query->where('have_sensupport_healthcare_plan', 'Y');
                        }
                        if (isset($free_meal) && ($free_meal != '' || $free_meal != 'N')) {
                            $query->where('freeschool_eligible', 'Y');
                        }
                    })
                    ->count();
                $total_other_pupil = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)
                    ->where('gender', 'Other')
                    ->where(function ($query) use ($have_sensupport, $free_meal) {
                        if (isset($have_sensupport) && ($have_sensupport != '' || $have_sensupport != 'N')) {
                            $query->where('have_sensupport_healthcare_plan', 'Y');
                        }
                        if (isset($free_meal) && ($free_meal != '' || $free_meal != 'N')) {
                            $query->where('freeschool_eligible', 'Y');
                        }
                    })
                    ->count();
                //have_sensupport_healthcare_plan
                //freeschool_eligible
                // dd($listing);
                $listing = [
                    'total_pupil' => $total_pupil,
                    'total_male_pupil' => $total_male_pupil,
                    'total_female_pupil' => $total_female_pupil,
                    'total_other_pupil' => $total_other_pupil,
                ];

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Pupil listing', 'listing' => $listing], 'error' => []];
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
