<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\OfsteadFinanceImport;
use App\Models\OfsteadFinance;
use App\Models\SubIndicator;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class OfsteadController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     *     path="/api/{subdomain}/ofstead/import-finance",
     *     summary="Bulk import ofstead finance",
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
     *     @OA\Response(response="200", description="Ofstead Finance bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importOfsteadFinance(Request $request)
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

                    $data = \Excel::import(new OfsteadFinanceImport($request->academic_year_id), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk ofstead finance processed successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/ofstead/finance",
     * summary="Get ofstead finance list",
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
     * @OA\Response(response="200", description="ofstead finance list"),
     * @OA\Response(response="401", description="ofstead finance list not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getOfsteadFinance(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $page = $request->page;
                $search_text = $request->search_text;

                $listing = OfsteadFinance::select('year', DB::raw('max(created_at) as created_at'), DB::raw('max(updated_at) as updated_at'), DB::raw('count(*) as count_subindicator'))
                    ->groupBy('year')
                    ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Ofstead finance listing', 'listing' => $listing], 'error' => []];
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
     * path="/api/{subdomain}/ofstead/delete-finance",
     * summary="Delete ofstead finance by year",
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
     *     description="Pass encrpted record year",
     *    @OA\JsonContent(
     *       required={"year"},
     *       @OA\Property(property="year", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Delete ofstead finance"),
     * @OA\Response(response="401", description="ofstead finance not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function deleteOfsteadFinanceByYear(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $year = CommonHelper::decryptId($request->year);
                $ofsteadFinance = OfsteadFinance::where('year', '=', $year)->delete();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Ofstead finance of ' . $year . ' deleted successfully.'], 'error' => []];
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
     * path="/api/{subdomain}/ofstead/get-finance-year",
     * summary="Get ofstead finance by year",
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
     *     description="Pass encrpted record year",
     *    @OA\JsonContent(
     *       required={"year"},
     *       @OA\Property(property="year", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="list ofstead finance by request year"),
     * @OA\Response(response="401", description="ofstead finance not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getOfsteadFinanceByYear(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $year = CommonHelper::decryptId($request->year);
                $listing = OfsteadFinance::with('sub_indicator')->where('year', '=', $year)->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Ofstead finance of ' . $year . ' fetched successfully.', 'listing' => $listing], 'error' => []];
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
     * path="/api/{subdomain}/ofstead/get-finance-expenditure",
     * summary="Get ofstead finance expenditure",
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
     *     description="Pass ofsted_show_value",
     *    @OA\JsonContent(
     *       required={"ofsted_show_value","ofsted_show_group"},
     *       @OA\Property(property="ofsted_show_group", type="string",default="total_expenditure"),
     *       @OA\Property(property="ofsted_show_value", type="string",default="absolute_total"),
     *    )
     * ),
     * @OA\Response(response="200", description="list ofstead finance by request year"),
     * @OA\Response(response="401", description="ofstead finance not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getOfsteadFinanceExpenditure(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // $year = CommonHelper::decryptId($request->year);
                $ofstedYr = OfsteadFinance::orderBy('year', 'asc')->distinct('year')->pluck('year');

                // dd($ofstedYr);

                $listing = array();
                $xval = '';
                $yval = '';

                $subIndicatiorIdGroup = SubIndicator::where('excel_column_identifier', $request->ofsted_show_group)->pluck('sub_indicator_id');

                $subIndicatiorIdValue = '';
                if ($request->ofsted_show_value != 'absolute_total') {
                    $subIndicatiorIdValue = SubIndicator::where('excel_column_identifier', $request->ofsted_show_value)->pluck('sub_indicator_id');
                }

                foreach ($ofstedYr as $year) {
                    $xval = $year;
                    $ofstedShowGroup = OfsteadFinance::where('year', $year)->where('sub_indicator_id', $subIndicatiorIdGroup)->first();

                    // dd($subIndicatiorIdGroup);

                    if ($subIndicatiorIdValue != '') {
                        // dd('sub');
                        $ofstedShowValue = OfsteadFinance::where('year', $year)->where('sub_indicator_id', $subIndicatiorIdValue)->first();
                        $yval = round((($ofstedShowGroup->value / $ofstedShowValue->value)), 0);
                    } else {
                        $yval = round(($ofstedShowGroup->value / 1000000), 2);
                    }

                    $ele = array(
                        'xvalue' => $xval,
                        'yvalue' => $yval,
                    );
                    array_push($listing, $ele);
                }

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Ofstead finance exenditure fetched successfully.', 'listing' => $listing], 'error' => []];
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
}
