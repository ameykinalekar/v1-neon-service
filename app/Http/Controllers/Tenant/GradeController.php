<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\GradeImport;
use App\Models\Grade;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class GradeController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/grades",
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
    public function getGrades(Request $request)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $page = $request->page ?? 1;
                $search_text = $request->search_text ?? '';

                $listing = Grade::where(function ($query) use ($search_text) {
                    if (isset($search_text) && $search_text != '') {
                        $query->where('grades.grade', 'like', '%' . $search_text . '%');
                    }
                })
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Grade Master data fetched successfully.', 'listing' => $listing), 'error' => []];

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
     * path="/api/{subdomain}/create-grade",
     * summary="Create grade master data of tenant as per subdomain",
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
     *     description="add new grade",
     *    @OA\JsonContent(
     *       required={"grade","min_value","max_value","effective_date"},
     *       @OA\Property(property="board_id", type="int", default=1),
     *       @OA\Property(property="grade", type="string"),
     *       @OA\Property(property="min_value", type="int", default=0),
     *       @OA\Property(property="max_value", type="int", default=0),
     *       @OA\Property(property="effective_date", type="date"),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant grade master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createGrade(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingGrade = Grade::where('grade', $request->grade)->where('min_value', $request->min_value)->where('max_value', $request->max_value)->where('effective_date', $request->effective_date)->where('board_id', $request->board_id)->first();

                if (empty($existingGrade)) {
                    $model = new Grade;
                    $model->grade = $request->grade;
                    $model->board_id = $request->board_id;
                    $model->min_value = $request->min_value;
                    $model->max_value = $request->max_value;
                    $model->effective_date = $request->effective_date;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Grade added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Grade already exist.']];
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
     * path="/api/{subdomain}/get-grade-by-id",
     * summary="Get grade details by id",
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
     *     description="Pass encrpted record grade_id",
     *    @OA\JsonContent(
     *       required={"grade_id"},
     *       @OA\Property(property="grade_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Grade details"),
     * @OA\Response(response="400", description="Grade not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getGradeById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $grade_id = CommonHelper::decryptId($request->grade_id);
                $details = Grade::find($grade_id);

                if (!empty($details)) {
                    $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Grade details by id', 'details' => $details], 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/update-grade",
     * summary="Update grade master data of tenant as per subdomain",
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
     *     description="update grade",
     *    @OA\JsonContent(
     *       required={"grade_id","grade","min_value","max_value","effective_date"},
     *       @OA\Property(property="grade_id", type="int"),
     *       @OA\Property(property="board_id", type="int",default=1),
     *       @OA\Property(property="grade", type="string"),
     *       @OA\Property(property="min_value", type="int", default=0),
     *       @OA\Property(property="max_value", type="int", default=0),
     *       @OA\Property(property="effective_date", type="date"),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant grade master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateGrade(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingGrade = Grade::where('grade', $request->grade)->where('min_value', $request->min_value)->where('max_value', $request->max_value)->where('effective_date', $request->effective_date)->where('board_id', $request->board_id)->first();

                if (empty($existingGrade) || $existingGrade->grade_id == $request->grade_id) {
                    $model = Grade::find($request->grade_id);
                    $model->grade = $request->grade;
                    $model->board_id = $request->board_id;
                    $model->min_value = $request->min_value;
                    $model->max_value = $request->max_value;
                    $model->effective_date = $request->effective_date;
                    $model->status = $request->status;
                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Grade updated successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Grade already exist.']];
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
     *     path="/api/{subdomain}/import-grades",
     *     summary="Bulk import grades",
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
     *     @OA\Response(response="200", description="Grade bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importGrade(Request $request)
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

                    $data = \Excel::import(new GradeImport($request->board_id), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk grades processed successfully.'), 'error' => []];
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
