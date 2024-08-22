<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\DepartmentImport;
use App\Models\Department;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/departments",
     * summary="Get department master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant department master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getDepartments(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                if ($search_text != '') {
                    $listing = Department::where('department_name', 'like', '%' . $search_text . '%')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
                } else {
                    $listing = Department::paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
                }
                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Department Master data fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/create-department",
     * summary="Create department master data of tenant as per subdomain",
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
     *     description="add new department",
     *    @OA\JsonContent(
     *       required={"department_name"},
     *       @OA\Property(property="department_name", type="string"),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant department master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createDepartment(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingDepartment = Department::where('department_name', $request->department_name)->first();
                if (empty($existingDepartment)) {
                    $model = new Department;
                    // $model->tenant_id = $user->tenant_id;
                    $model->department_name = $request->department_name;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Department added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Department already exist.']];
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
     * path="/api/{subdomain}/get-department-by-id",
     * summary="Get Department details by id",
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
     *     description="Pass encrpted record department_id",
     *    @OA\JsonContent(
     *       required={"department_id"},
     *       @OA\Property(property="department_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Depatment details"),
     * @OA\Response(response="401", description="Department not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getDepartmentById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $department_id = CommonHelper::decryptId($request->department_id);
                // $academic_year_id = $request->academic_year_id;
                // die("==" . $academic_year_id);
                $details = Department::find($department_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Department details by id', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/update-department",
     * summary="Update department master data of tenant as per subdomain",
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
     *     description="update department",
     *    @OA\JsonContent(
     *       required={"department_id","department_name"},
     *       @OA\Property(property="department_id", type="int"),
     *       @OA\Property(property="department_name", type="string"),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant department master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateDepartment(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $existingDepartment = Department::where('department_name', $request->department_name)->where('department_id', '!=', $request->department_id)->first();
                if (empty($existingDepartment)) {
                    $model = Department::find($request->department_id);
                    // $model->tenant_id = $user->tenant_id;
                    $model->department_name = $request->department_name;
                    $model->status = $request->status;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Department updated successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Department already exist.']];
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
     * path="/api/{subdomain}/dropdown/get-departments",
     * summary="Get department list for dropdowns passing tenant token as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant department master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdowndDepartments()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $dropdownList = Department::select('department_id', 'department_name')->where('status', '=', GlobalVars::ACTIVE_STATUS)->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Department Master list fetched successfully.', 'dropdown_list' => $dropdownList), 'error' => ''];
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
     *     path="/api/{subdomain}/import-departments",
     *     summary="Bulk import departments",
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
     *                     property="import_file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload",
     *                     default=null
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Department bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importDepartment(Request $request)
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

                    $data = \Excel::import(new DepartmentImport(), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk departments processed successfully.'), 'error' => []];
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
