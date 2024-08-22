<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\YearGroupImport;
use App\Models\UserYearGroup;
use App\Models\YearGroup;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class YearGroupController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/year-groups",
     * summary="Get year group master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text","search_academic_year"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year", type="string", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant year group master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getYearGroups(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year = $request->search_academic_year;

                $yearGroup = YearGroup::join('academic_years', 'academic_years.academic_year_id', 'year_groups.academic_year_id', 'INNER')->select('year_groups.*', 'academic_years.academic_year')
                    ->where(function ($query) use ($search_text) {
                        if (isset($search_text) && $search_text != '') {
                            $query->where('year_groups.name', 'like', '%' . $search_text . '%');
                        }
                    })
                    ->where(function ($query) use ($search_academic_year) {
                        if (isset($search_academic_year) && $search_academic_year != '') {
                            $query->where('year_groups.academic_year_id', '=', $search_academic_year);
                        }
                    })
                    ->orderBy('year_groups.name', 'asc')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Year Group Master data fetched successfully.', 'year_groups' => $yearGroup), 'error' => ''];
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
     * path="/api/{subdomain}/create-year-group",
     * summary="Create year group master data of tenant as per subdomain",
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
     *     description="add new year group",
     *    @OA\JsonContent(
     *       required={"name","academic_year_id"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="academic_year_id", type="int"),
     *       @OA\Property(property="one_one", type="int", default=0),
     *       @OA\Property(property="group", type="int", default=0),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant year group master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createYearGroup(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingYearGroup = YearGroup::where('name', $request->name)->where('academic_year_id', $request->academic_year_id)->first();

                if (empty($existingYearGroup)) {
                    $model = new YearGroup;
                    $model->name = $request->name;
                    $model->academic_year_id = $request->academic_year_id;
                    $model->one_one = $request->one_one > 0 ? '1:1' : null;
                    $model->group = $request->group > 0 ? 'group' : null;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Year group added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Year group already exist.']];
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
     * path="/api/{subdomain}/get-year-group-by-id",
     * summary="Get year group details by id",
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
     *     description="Pass encrpted record year_group_id",
     *    @OA\JsonContent(
     *       required={"year_group_id"},
     *       @OA\Property(property="year_group_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Year group details"),
     * @OA\Response(response="401", description="Year group not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getYearGroupById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $year_group_id = CommonHelper::decryptId($request->year_group_id);
                $yearGroup = YearGroup::find($year_group_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Year group details by id', 'details' => $yearGroup], 'error' => []];
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
     * path="/api/{subdomain}/update-year-group",
     * summary="Update year group master data of tenant as per subdomain",
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
     *     description="update year group",
     *    @OA\JsonContent(
     *       required={"year_group_id","academic_year_id","name"},
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="academic_year_id", type="int"),
     *       @OA\Property(property="one_one", type="int", default=0),
     *       @OA\Property(property="group", type="int", default=0),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant year group master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateYearGroup(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingYearGroup = YearGroup::where('name', $request->name)->where('academic_year_id', $request->academic_year_id)->where('year_group_id', '!=', $request->year_group_id)->first();

                if (empty($existingYearGroup)) {
                    $model = YearGroup::find($request->year_group_id);
                    // $model->tenant_id = $user->tenant_id;
                    $model->name = $request->name;
                    $model->academic_year_id = $request->academic_year_id;
                    $model->one_one = $request->one_one > 0 ? '1:1' : null;
                    $model->group = $request->group > 0 ? 'group' : null;
                    $model->status = $request->status;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Year group updated successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Year group already exist.']];
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
     * path="/api/{subdomain}/dropdown/get-academicyearid-yeargroups",
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
     *     description="provide academic_year_id",
     *    @OA\JsonContent(
     *       required={"academic_year_id"},
     *       @OA\Property(property="academic_year_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="tenant academic year year group master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownAcademicYearIdYeargroups(Request $request)
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

                $academic_year_id = $request->academic_year_id;
                if ($academic_year_id != '' || $academic_year_id != null) {
                    $yearGroupList = array();
                    if ($user->user_type == GlobalVars::TENANT_USER_TYPE) {
                        $usrMappedYearGroups = UserYearGroup::where('user_year_groups.user_id', $user->user_id)->distinct('year_group_id')->pluck('year_group_id')->toArray();

                        $yearGroupList = YearGroup::select('year_groups.*')
                            ->where('year_groups.academic_year_id', '=', $academic_year_id)
                            ->whereIn('year_groups.year_group_id', $usrMappedYearGroups)
                            ->orderBy('year_groups.name', 'asc')
                            ->get();
                    } else {
                        $yearGroupList = YearGroup::select('year_groups.*')
                            ->where('academic_year_id', '=', $academic_year_id)
                            ->orderBy('year_groups.name', 'asc')
                            ->get();
                    }

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Academic year wise year group list fetched successfully.', 'yeargroup_list' => $yearGroupList), 'error' => ''];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No academic year provided.']];
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
     * path="/api/{subdomain}/dropdown/get-all-yeargroups",
     * summary="Get year group list for dropdowns passing tenant token as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),

     * @OA\Response(response="200", description="tenant academic year year group master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownAllYeargroups()
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // dd($user);
            // dd($this->tenantId . '==' . $user->tenant_id);

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $yearGroupList = array();
                if ($user->user_type == GlobalVars::TENANT_USER_TYPE) {
                    $usrMappedYearGroups = UserYearGroup::where('user_year_groups.user_id', $user->user_id)->distinct('year_group_id')->pluck('year_group_id')->toArray();

                    $yearGroupList = YearGroup::select('year_groups.*', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=year_groups.academic_year_id) as academic_year"), DB::raw("(select status from academic_years where academic_years.academic_year_id=year_groups.academic_year_id) as academic_year_status"))
                        ->whereIn('year_groups.year_group_id', $usrMappedYearGroups)
                        ->orderBy('year_groups.name', 'asc')
                        ->get();
                } else {
                    $yearGroupList = YearGroup::select('year_groups.*', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=year_groups.academic_year_id) as academic_year"), DB::raw("(select status from academic_years where academic_years.academic_year_id=year_groups.academic_year_id) as academic_year_status"))
                        ->orderBy('year_groups.name', 'asc')
                        ->get();
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Year group list fetched successfully.', 'yeargroup_list' => $yearGroupList), 'error' => ''];
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
     *     path="/api/{subdomain}/import-year-groups",
     *     summary="Bulk import year group",
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
     *                     description="academic year Id"
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
     *     @OA\Response(response="200", description="Year Group bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importYearGroup(Request $request)
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

                    $data = \Excel::import(new YearGroupImport($request->academic_year_id), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk Year groups import processed successfully.'), 'error' => []];
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
