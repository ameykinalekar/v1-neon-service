<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\UserYearGroup;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;

class AcademicYearController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/academic-years",
     * summary="Get academic year master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
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
    public function getAcademicYears(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;
                if ($search_text != '') {
                    $academicYear = AcademicYear::where('academic_year', 'like', '%' . $search_text . '%')->orderBy('academic_years.academic_year', 'asc')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
                } else {
                    $academicYear = AcademicYear::orderBy('academic_years.academic_year', 'asc')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
                }
                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Academic Year Master data fetched successfully.', 'academic_years' => $academicYear), 'error' => ''];
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
     * path="/api/{subdomain}/create-academic-year",
     * summary="Create academic year master data of tenant as per subdomain",
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
     *     description="add new academic year",
     *    @OA\JsonContent(
     *       required={"academic_year","start_year","end_year"},
     *       @OA\Property(property="academic_year", type="string"),
     *       @OA\Property(property="start_year", type="string"),
     *       @OA\Property(property="end_year", type="string"),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant academic year master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createAcademicYear(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                AcademicYear::query()->update(['status' => GlobalVars::INACTIVE_STATUS]);
                $existingAcademicYear = AcademicYear::Where('academic_year', $request->academic_year)->first();
                if (empty($existingAcademicYear)) {
                    $model = new AcademicYear;
                    // $model->tenant_id = $user->tenant_id;
                    $model->academic_year = $request->academic_year;
                    $model->start_year = $request->start_year;
                    $model->end_year = $request->end_year;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Academic year added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Academic year already exist.']];
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
     * path="/api/{subdomain}/get-academic-year-by-id",
     * summary="Get Academic year details by id",
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
     *     description="Pass encrpted record academic_year_id",
     *    @OA\JsonContent(
     *       required={"academic_year_id"},
     *       @OA\Property(property="academic_year_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Academic year details"),
     * @OA\Response(response="401", description="Academic year not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getAcademicYearById(Request $request)
    {
        try {
            // dd(CommonHelper::encryptId("1"));
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $academic_year_id = CommonHelper::decryptId($request->academic_year_id);
                // $academic_year_id = $request->academic_year_id;
                // die("==" . $academic_year_id);
                $academicYear = AcademicYear::find($academic_year_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Academic year details by id', 'details' => $academicYear], 'error' => []];
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
     * path="/api/{subdomain}/update-academic-year",
     * summary="Update academic year master data of tenant as per subdomain",
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
     *     description="update academic year",
     *    @OA\JsonContent(
     *       required={"academic_year_id","academic_year"},
     *       @OA\Property(property="academic_year_id", type="int"),
     *       @OA\Property(property="academic_year", type="string"),
     *       @OA\Property(property="start_year", type="string"),
     *       @OA\Property(property="end_year", type="string"),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant academic year master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateAcademicYear(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $existingAcademicYear = AcademicYear::Where('academic_year', $request->academic_year)->where('academic_year_id', '!=', $request->academic_year_id)->first();

                if (empty($existingAcademicYear)) {
                    if ($request->status == GlobalVars::ACTIVE_STATUS) {
                        AcademicYear::query()->update(['status' => GlobalVars::INACTIVE_STATUS]);
                        // DB::table('academic_years')->update(['status' => GlobalVars::INACTIVE_STATUS]);
                    }
                    $model = AcademicYear::find($request->academic_year_id);
                    // $model->tenant_id = $user->tenant_id;
                    $model->academic_year = $request->academic_year;
                    $model->start_year = $request->start_year;
                    $model->end_year = $request->end_year;
                    $model->status = $request->status;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Academic year updated successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Academic year already exist.']];
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
     * path="/api/{subdomain}/dropdown/get-academic-years",
     * summary="Get academic year list for dropdowns passing tenant token as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant batch type master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdowdAcademicYears()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $academicYearList = array();
                if ($user->user_type == GlobalVars::TENANT_USER_TYPE) {

                    $usrMappedAcademicYears = UserYearGroup::join('year_groups', 'year_groups.year_group_id', 'user_year_groups.year_group_id', 'INNER')
                        ->where('user_year_groups.user_id', $user->user_id)
                        ->distinct('year_groups.academic_year_id')
                        ->pluck('year_groups.academic_year_id')
                        ->toArray();

                    $academicYearList = AcademicYear::whereIn('academic_years.academic_year_id', $usrMappedAcademicYears)->orderBy('academic_years.academic_year', 'asc')->get();
                } else {
                    $academicYearList = AcademicYear::orderBy('academic_years.academic_year', 'asc')->get();
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Academic year Master list fetched successfully.', 'academic_year_list' => $academicYearList), 'error' => ''];
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
