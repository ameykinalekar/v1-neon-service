<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\TargetDetail;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;

class TargetController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/targets",
     * summary="Get grade list for dropdowns passing tenant token as per subdomain",
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
     * @OA\Response(response="200", description="tenant grade master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTargets(Request $request)
    {
        try {

            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $page = $request->page ?? 1;
                $search_text = $request->search_text ?? '';

                $listing = Target::with('yeargroup', 'student')
                    ->where(function ($query) use ($search_text) {
                        // if (isset($search_text) && $search_text != '') {
                        //     $query->where('grades.grade', 'like', '%' . $search_text . '%');
                        // }
                    })
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Target Master data fetched successfully.', 'listing' => $listing), 'error' => []];

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
     * path="/api/{subdomain}/create-target",
     * summary="Create target data of tenant as per subdomain",
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
     *     description="add new target",
     *    @OA\JsonContent(
     *       required={"user_id","set_date","year_group_id"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="set_date", type="date"),
     *       @OA\Property(property="targets", type="array", description="List of targets",
     *       @OA\Items(
     *          @OA\Property(
     *              property="subject_id",
     *              description="subject_id",
     *              type="int",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="target",
     *              description="target",
     *              type="int",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="target_date",
     *              description="target_date",
     *              type="date",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant target master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createTarget(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingTarget = Target::where('set_date', $request->set_date)->where('user_id', $request->user_id)->first();

                if (empty($existingGrade)) {
                    $model = new Target;
                    $model->set_date = $request->set_date;
                    $model->year_group_id = $request->year_group_id;
                    $model->user_id = $request->user_id;
                    $model->created_by = $user->user_id;
                    $model->creator_type = $user->user_type;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();
                    $targets = $request->targets ?? array();
                    if (count($targets) > 0) {
                        for ($i = 0; $i < count($targets); $i++) {
                            if ($targets[$i]['target_date'] ?? null != '' && $targets[$i]['target'] != '') {
                                $modelSub = new TargetDetail;
                                $modelSub->subject_id = $targets[$i]['subject_id'];
                                $modelSub->target = $targets[$i]['target'];
                                $modelSub->target_date = $targets[$i]['target_date'];
                                $modelSub->target_id = $model->target_id;
                                $modelSub->save();
                            }
                        }
                    }
                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Target added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Target already exist.']];
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
     * path="/api/{subdomain}/get-target-by-id",
     * summary="Get target details by id",
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
     *     description="Pass encrpted record target_id",
     *    @OA\JsonContent(
     *       required={"target_id"},
     *       @OA\Property(property="target_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Target details"),
     * @OA\Response(response="400", description="Target not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTargetById(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // $target_id = $request->target_id;
                $target_id = CommonHelper::decryptId($request->target_id);
                $details = Target::with('details')->find($target_id);

                if (!empty($details)) {
                    $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Target details by id', 'details' => $details], 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No target exist']];
                    return response()->json($reponse, 400);
                }

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
     * path="/api/{subdomain}/update-target",
     * summary="Update target master data of tenant as per subdomain",
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
     *     description="update target",
     *    @OA\JsonContent(
     *       required={"target_id","user_id","set_date","year_group_id"},
     *       @OA\Property(property="target_id", type="int"),
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="set_date", type="date"),
     *       @OA\Property(property="status", type="string", default="Active"),
     *       @OA\Property(property="targets", type="array", description="List of targets",
     *       @OA\Items(
     *          @OA\Property(
     *              property="subject_id",
     *              description="subject_id",
     *              type="int",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="target",
     *              description="target",
     *              type="int",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="target_date",
     *              description="target_date",
     *              type="date",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant target"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateTarget(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingTarget = Target::where('set_date', $request->set_date)->where('user_id', $request->user_id)->where('year_group_id', $request->year_group_id)->where('target_id', '!=', $request->target_id)->first();
                // print_r($existingTarget);
                if (empty($existingTarget)) {
                    $model = Target::find($request->target_id);
                    $model->set_date = $request->set_date;
                    $model->user_id = $request->user_id;
                    $model->created_by = $user->user_id;
                    $model->creator_type = $user->user_type;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $targets = $request->targets ?? array();
                    if (count($targets) > 0) {
                        TargetDetail::where('target_id', $request->target_id)->delete();
                        for ($i = 0; $i < count($targets); $i++) {
                            if ($targets[$i]['target_date'] ?? null != '' && $targets[$i]['target'] != '') {
                                $modelSub = new TargetDetail;
                                $modelSub->subject_id = $targets[$i]['subject_id'] ?? '';
                                $modelSub->target = $targets[$i]['target'] ?? null;
                                $modelSub->target_date = $targets[$i]['target_date'] ?? null;
                                $modelSub->target_id = $model->target_id;
                                $modelSub->save();
                            }
                        }
                    }

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Target updated successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Target does not exist.']];
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
     * path="/api/{subdomain}/consumer/targets",
     * summary="Get consumer targer list  passing tenant token as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},

     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant grade master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getConsumerTargets()
    {
        try {

            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = Target::join('target_details', 'target_details.target_id', 'targets.target_id', 'INNER')
                    ->select('target_details.*', 'targets.set_date', DB::raw("(select subject_name from subjects where subjects.subject_id=target_details.subject_id) as subject_name"), DB::raw("(select name from year_groups where year_groups.year_group_id=targets.year_group_id) as yeargroup"))
                    ->where('targets.user_id', $user->user_id)
                    ->where('targets.status', GlobalVars::ACTIVE_STATUS)
                    ->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Consumer Target data fetched successfully.', 'listing' => $listing), 'error' => []];

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

}
