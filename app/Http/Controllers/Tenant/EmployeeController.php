<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\ViewEmployee;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;

class EmployeeController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/employees",
     * summary="Get employee master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant employee list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getEmployees(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                if ($search_text != '') {
                    $itemList = ViewEmployee::where('first_name', 'like', '%' . $search_text . '%')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
                } else {
                    $itemList = ViewEmployee::paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
                }
                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Employee list fetched successfully.', 'item_list' => $itemList), 'error' => ''];
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
     * path="/api/{subdomain}/create-employee",
     * summary="Create employee master data of tenant as per subdomain",
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
     *     description="add new employee",
     *    @OA\JsonContent(
     *       required={"first_name","email","password","phone","gender","department_id"},
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="department_id", type="string"),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant employee master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createEmployee(Request $request)
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
                    $modelU->role = GlobalVars::TENANT_EMPLOYEE_ROLE;
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
                    $modelUP->department_id = $request->department_id;

                    $modelUP->save();

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

        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Employee added successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/get-employee-by-id",
     * summary="Get employee details by id",
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
     *     description="Pass encrpted record employee_id",
     *    @OA\JsonContent(
     *       required={"employee_id"},
     *       @OA\Property(property="employee_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Employee details"),
     * @OA\Response(response="401", description="Employee not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getEmployeeById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $employee_id = CommonHelper::decryptId($request->employee_id);
                $resDetails = ViewEmployee::find($employee_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Employee details by id', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/update-employee",
     * summary="Update employee master data of tenant as per subdomain",
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
     *     description="update employee",
     *    @OA\JsonContent(
     *       required={"employee_id","first_name","email","password","phone","gender","department_id"},
     *       @OA\Property(property="employee_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="department_id", type="string"),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant employee master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateEmployee(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->employee_id);
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

                $getProfileId = TenantUserProfile::where('user_id', '=', $request->employee_id)->pluck('user_profile_id');
                // dd($getProfileId);

                $modelUP = TenantUserProfile::find($getProfileId[0]);
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $request->first_name;
                $modelUP->last_name = $request->last_name;
                $modelUP->address = $request->address;
                $modelUP->gender = $request->gender;
                $modelUP->department_id = $request->department_id;

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
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Employee updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

}
