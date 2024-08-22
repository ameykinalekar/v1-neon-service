<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CountryController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/get-countries",
     * summary="Get list of country data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response="200", description="country list"),
     * @OA\Response(response="401", description="country not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCountries(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $listing = Country::orderBy('status', 'asc')->orderBy('name', 'asc')->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Country list', 'listing' => $listing], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/get-country-by-id",
     * summary="Get country details by id",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"country_id"},
     *       @OA\Property(property="country_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="country details"),
     * @OA\Response(response="401", description="country not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCountryById(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $country_id = CommonHelper::decryptId($request->country_id);

            $country = Country::find($country_id);

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Country details by id', 'details' => $country], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/update-country",
     * summary="Update country data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="update country information",
     *    @OA\JsonContent(
     *       required={"country_id","name","code"},
     *       @OA\Property(property="country_id", type="int"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="code", type="string"),
     *       @OA\Property(property="currency_code", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="country updated successfully."),
     * @OA\Response(response="401", description="country not added"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateCountry(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $existingCountry = Country::where('name', $request->name)->where('country_id', '!=', $request->country_id)->first();
            if (empty($existingCountry)) {
                $model = Country::find($request->country_id);
                $model->name = $request->name;
                $model->code = $request->code;
                $model->currency_code = $request->currency_code;
                $model->status = $request->status;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Country updated successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Country already exist.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/dropdown/countries",
     * summary="Get list of active countries",
     * tags={"Common"},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"status"},
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="country list"),
     * @OA\Response(response="401", description="country not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getDropdownCountries(Request $request)
    {
        try {

            $listing = Country::where('status', $request->status ?? GlobalVars::ACTIVE_STATUS)->orderBy('status', 'asc')->orderBy('name', 'asc')->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Country list', 'listing' => $listing], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }
}
