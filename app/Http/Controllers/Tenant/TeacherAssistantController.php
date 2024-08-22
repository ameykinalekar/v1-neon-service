<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\UserSubject;
use App\Models\UserYearGroup;
use App\Models\ViewTeacherAssistant;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;

class TeacherAssistantController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/teacher-assistants",
     * summary="Get teacher assistant master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant teacher assistant details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherAssistants(Request $request)
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

                $listing = ViewTeacherAssistant::whereIn('user_id', $subjectUsers)
                    ->where(function ($query) use ($search_text) {
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
                    })->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Teacher assistant data fetched successfully.', 'teacher_assistant_list' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/create-teacher-assistant",
     * summary="Create teacher assistant master data of tenant as per subdomain",
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
     *     description="add new teacher assistant",
     *    @OA\JsonContent(
     *       required={"first_name","email","password","phone","gender"},
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="phone", type="string"),
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

     * @OA\Response(response="200", description="create tenant teacher assistant master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createTeacherAssistant(Request $request)
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
                    $modelU->role = GlobalVars::TENANT_TEACHER_ASSISTANT_ROLE;
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

        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Teacher assistant added successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/get-teacher-assistant-by-id",
     * summary="Get teacher assistant details by id",
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
     *     description="Pass encrpted record teacher_assistant_id",
     *    @OA\JsonContent(
     *       required={"teacher_assistant_id"},
     *       @OA\Property(property="teacher_assistant_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Teacher assistant details"),
     * @OA\Response(response="401", description="Teacher assistant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTeacherAssistantById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $teacher_assistant_id = CommonHelper::decryptId($request->teacher_assistant_id);
                $resDetails = ViewTeacherAssistant::find($teacher_assistant_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Teacher assistant details by id', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/update-teacher-assistant",
     * summary="Update teacher assistant master data of tenant as per subdomain",
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
     *     description="update teacher assistant",
     *    @OA\JsonContent(
     *       required={"teacher_assistant_id","first_name","email","password","phone","gender"},
     *       @OA\Property(property="teacher_assistant_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
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
     * @OA\Response(response="200", description="update tenant teacher assistant master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateTeacherAssistant(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->teacher_assistant_id);
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

                $getProfileId = TenantUserProfile::where('user_id', '=', $request->teacher_assistant_id)->pluck('user_profile_id');
                // dd($getProfileId);

                $modelUP = TenantUserProfile::find($getProfileId[0]);
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $request->first_name;
                $modelUP->last_name = $request->last_name;
                $modelUP->address = $request->address;
                $modelUP->gender = $request->gender;
                $modelUP->ni_number = $request->ni_number;
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
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Teacher assistant updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

}
