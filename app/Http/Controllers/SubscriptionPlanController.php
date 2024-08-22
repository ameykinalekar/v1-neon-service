<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SubscriptionPlanController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/get-subscription-plans",
     * summary="Get paginated subscription plan data",
     * tags={"Portal"},
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
     * @OA\Response(response="200", description="subscription plan list"),
     * @OA\Response(response="401", description="subscription plan not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubscriptionPlans(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $page = $request->page;
            $search_text = $request->search_text;

            if ($search_text != '') {
                $plans = SubscriptionPlan::where(function ($query) use ($search_text) {

                    $query->where('plan_name', 'like', '%' . $search_text . '%')
                        ->orWhere('description', 'like', '%' . $search_text . '%');
                })->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            } else {
                $plans = SubscriptionPlan::paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            }
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Paginated board list', 'plans' => $plans], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/create-subscription-plan",
     * summary="Create subscription plan data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="add new subscription plan",
     *    @OA\JsonContent(
     *       required={"plan_name","base_price","tax_percentage","validity_indays"},
     *       @OA\Property(property="plan_name", type="string"),
     *       @OA\Property(property="base_price", type="double"),
     *       @OA\Property(property="tax_percentage", type="double"),
     *       @OA\Property(property="validity_indays", type="int"),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="users_allowed", type="int", default=1),
     *       @OA\Property(property="features_available", type="string", default=null,format="Json string"),
     *    )
     * ),
     * @OA\Response(response="200", description="subscription plan added"),
     * @OA\Response(response="401", description="subscription plan not added"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createSubscriptionPlan(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                DB::rollback();
                return response()->json($reponse, 400);
            }
            $requestData = [
                "plan_name" => $request->plan_name ?? '',
                "base_price" => $request->base_price ?? '',
                "tax_percentage" => $request->tax_percentage ?? '',
                "validity_indays" => $request->validity_indays ?? '',
                "users_allowed" => $request->users_allowed ?? '',
                "features_available" => $request->features_available ?? '',

            ];

            $validator = Validator::make($requestData, [
                'plan_name' => 'required',
                'base_price' => 'required',
                'tax_percentage' => 'required',
                'validity_indays' => 'required',
                'users_allowed' => 'required',
                'features_available' => 'required',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                DB::rollback();
                return response()->json($reponse, 401);
            }

            $modelSP = new SubscriptionPlan;
            $modelSP->plan_name = $request->plan_name;
            $modelSP->base_price = $request->base_price;
            $modelSP->tax_percentage = $request->tax_percentage;
            $modelSP->validity_indays = $request->validity_indays;
            $modelSP->users_allowed = $request->users_allowed;
            $modelSP->features_available = $request->features_available;
            $modelSP->description = $request->description;
            $modelSP->status = GlobalVars::ACTIVE_STATUS;
            $modelSP->save();

            DB::commit();
            $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subscription plan added successfully.'), 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            DB::rollback();
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/get-subscription-plan-by-id",
     * summary="Get subscription plan by id",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"subscription_plan_id"},
     *       @OA\Property(property="subscription_plan_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="subscription plan details"),
     * @OA\Response(response="401", description="subscription plan not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubscriptionPlanById(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $subscription_plan_id = CommonHelper::decryptId($request->subscription_plan_id);
            // $id = $request->id;

            $planInfo = SubscriptionPlan::where('subscription_plan_id', '=', $subscription_plan_id)->first();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'subscription plan details by id', 'plan_details' => $planInfo], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/update-subscription-plan",
     * summary="Update subscription plan data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="update subscription plan",
     *    @OA\JsonContent(
     *       required={"subscription_plan_id","plan_name","base_price","tax_percentage","validity_indays"},
     *       @OA\Property(property="subscription_plan_id", type="int"),
     *       @OA\Property(property="plan_name", type="string"),
     *       @OA\Property(property="base_price", type="double"),
     *       @OA\Property(property="tax_percentage", type="double"),
     *       @OA\Property(property="validity_indays", type="int"),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="users_allowed", type="int", default=1),
     *       @OA\Property(property="features_available", type="string", default=null,format="Json string"),
     *    )
     * ),
     * @OA\Response(response="200", description="subscription plan updated"),
     * @OA\Response(response="401", description="subscription plan not updated"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateSubscriptionPlan(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                DB::rollback();
                return response()->json($reponse, 400);
            }

            $requestData = [
                "subscription_plan_id" => $request->subscription_plan_id ?? '',
                "plan_name" => $request->plan_name ?? '',
                "base_price" => $request->base_price ?? '',
                "tax_percentage" => $request->tax_percentage ?? '',
                "validity_indays" => $request->validity_indays ?? '',
                "users_allowed" => $request->users_allowed ?? '',
                "features_available" => $request->features_available ?? '',
            ];

            $validator = Validator::make($requestData, [
                'subscription_plan_id' => 'required',
                'plan_name' => 'required',
                'base_price' => 'required',
                'tax_percentage' => 'required',
                'validity_indays' => 'required',
                'users_allowed' => 'required',
                'features_available' => 'required',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                DB::rollback();
                return response()->json($reponse, 401);
            }

            $modelSP = SubscriptionPlan::find($request->subscription_plan_id);
            $modelSP->plan_name = $request->plan_name;
            $modelSP->base_price = $request->base_price;
            $modelSP->tax_percentage = $request->tax_percentage;
            $modelSP->validity_indays = $request->validity_indays;
            $modelSP->users_allowed = $request->users_allowed;
            $modelSP->features_available = $request->features_available;
            $modelSP->description = $request->description;
            $modelSP->status = $request->status;
            $modelSP->save();

            DB::commit();

            $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subscription plan updated successfully.'), 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            //Rollback transaction
            DB::rollback();
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/get-plans-to-subscribe",
     * summary="Get all plans available for subscription",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response="200", description="subscription plan list"),
     * @OA\Response(response="401", description="subscription plan not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getPlansToSubscribe()
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $plans = SubscriptionPlan::where('status', GlobalVars::ACTIVE_STATUS)->get();
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Plan list', 'plans' => $plans], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/subscribe-plan",
     * summary="create tenant subscription",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="create tenant subscription request body",
     *    @OA\JsonContent(
     *       required={"subscription_plan_id","user_id","tenant_id","start_date"},
     *       @OA\Property(property="subscription_plan_id", type="int"),
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="tenant_id", type="int"),
     *       @OA\Property(property="start_date", type="date"),
     *    )
     * ),
     * @OA\Response(response="200", description="Plan susbscribed successfully"),
     * @OA\Response(response="401", description="Unable to subscribe"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function subscribePlan(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                DB::rollback();
                return response()->json($reponse, 400);
            }

            $requestData = [
                "subscription_plan_id" => $request->subscription_plan_id ?? '',
                "user_id" => $request->user_id ?? '',
                "tenant_id" => $request->tenant_id ?? '',
                "start_date" => $request->start_date ?? '',
            ];

            $validator = Validator::make($requestData, [
                'subscription_plan_id' => 'required',
                'user_id' => 'required',
                'tenant_id' => 'required',
                'start_date' => 'required',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                DB::rollback();
                return response()->json($reponse, 401);
            }
            $planInfo = SubscriptionPlan::where('subscription_plan_id', '=', $request->subscription_plan_id)->first();
            $price = $planInfo->base_price;
            $tax = $planInfo->tax_percentage;
            $tax_amount = round((($price * $tax) / 100), 2);
            $final_price = $price + $tax_amount;

            $end_date = date('Y-m-d', strtotime($request->start_date . ' + ' . $planInfo->validity_indays . ' days'));

            $existingPlans = TenantSubscription::where('tenant_id', $request->tenant_id)->update(['status' => GlobalVars::INACTIVE_STATUS, 'updated_at' => now()]);

            $modelTS = new TenantSubscription;
            $modelTS->subscription_plan_id = $request->subscription_plan_id;
            $modelTS->tenant_id = $request->tenant_id;
            $modelTS->base_price = $planInfo->base_price;
            $modelTS->tax_percentage = $planInfo->tax_percentage;
            $modelTS->tax_amount = $tax_amount;
            $modelTS->net_payable = $final_price;

            $modelTS->start_date = $request->start_date;
            $modelTS->end_date = $end_date;
            $modelTS->status = GlobalVars::ACTIVE_STATUS;
            $modelTS->save();

            $tenant_subscription_id = $modelTS->tenant_subscription_id;

            $tenant = Tenant::find($request->tenant_id);
            $tenant->tenant_subscription_id = $tenant_subscription_id;
            $tenant->subscription_expiry_date = $end_date;
            $tenant->users_allowed = $planInfo->users_allowed;
            $tenant->features_available = $planInfo->features_available;
            $tenant->save();

            DB::commit();

            $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Subscription plan subscribed successfully.'), 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            //Rollback transaction
            DB::rollback();
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/get-subscribed-plans",
     * summary="Get all plans available for subscription",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="create tenant subscription request body",
     *    @OA\JsonContent(
     *       required={"tenant_id"},
     *       @OA\Property(property="tenant_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="subscription plan list"),
     * @OA\Response(response="401", description="subscription plan not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubscribedPlans(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $tenant_id = CommonHelper::decryptId($request->tenant_id);
            $plans = TenantSubscription::join('subscription_plans', 'subscription_plans.subscription_plan_id', '=', 'tenant_subscriptions.subscription_plan_id')
                ->where('tenant_subscriptions.tenant_id', $tenant_id)
                ->select('tenant_subscriptions.*', 'subscription_plans.plan_name')
                ->get();
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Plan list', 'plans' => $plans], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

}
