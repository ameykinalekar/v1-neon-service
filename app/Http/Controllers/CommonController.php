<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Board;
use App\Models\QuestionLevel;
use App\Models\QuestionType;
use App\Models\Salutation;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserSubject;
use App\Models\ViewTrustee;
use App\Traits\GeneralMethods;
use Config;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class CommonController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/dropdown/trustees",
     * summary="Get trustee list for dropdowns",
     * description="Get trustee list for dropdowns passing portal admin token",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response="200", description="trustee list"),
     * @OA\Response(response="401", description="trustee not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTrusteeList(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $trusteeList = ViewTrustee::select('user_id as id', 'first_name as trustee_name')->orderBy('first_name')->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Trustee List', 'trustees' => $trusteeList], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/portal-tenants",
     * summary="Get portal tenant list for dropdowns",
     * description="Get portal tenant list for dropdowns passing API key",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="X_NEON",
     *         in="header",
     *         description="API access",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=false,
     *     description="Pass country id",
     *    @OA\JsonContent(
     *       @OA\Property(property="country_id", type="string",default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="portal tenant list"),
     * @OA\Response(response="401", description="portal tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getPortalTenantList(Request $request)
    {
        try {

            $country_id = $request->country_id ?? '';

            $user = User::select('users.user_id', 'tenants.subdomain', 'user_profiles.first_name', 'user_profiles.middle_name', 'user_profiles.last_name', 'user_profiles.short_name')
                ->join('tenants', 'tenants.tenant_id', 'users.tenant_id')
                ->join('user_profiles', 'user_profiles.user_id', 'users.user_id')
                ->where('users.user_type', GlobalVars::TENANT_ADMIN_USER_TYPE)
                ->where(function ($query) use ($country_id) {
                    if ($country_id != null) {
                        $query->where('tenants.country_id', $country_id);
                    }
                })
                ->orderBy('user_profiles.first_name', 'asc')
                ->get();

            $tenantList = $user;
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Tenant List', 'tenants' => $tenantList], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/get-tenant-config",
     * summary="Get portal tenant theming settings",
     * description="Get portal tenant theming settings passing API key",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="X_NEON",
     *         in="header",
     *         description="API access",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass subdomain",
     *    @OA\JsonContent(
     *       required={"subdomain"},
     *       @OA\Property(property="subdomain", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="portal tenant list"),
     * @OA\Response(response="401", description="portal tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function geTenantConfig(Request $request)
    {
        try {

            $tenant_info = Tenant::where('tenants.subdomain', $request->subdomain)->first();
            $setting_info = [];
            $tenant_short_name = null;
            if ($tenant_info->tenant_id > 0) {
                $setting_info = Setting::where('settings.tenant_id', $tenant_info->tenant_id)->first();
                $tenant_short_name = User::join('user_profiles', 'user_profiles.user_id', 'users.user_id')->where('tenant_id', $tenant_info->tenant_id)->where('users.user_type', GlobalVars::TENANT_ADMIN_USER_TYPE)->value('short_name');
            }
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Tenant config', 'tenant_info' => $tenant_info, 'setting_info' => $setting_info, 'tenant_short_name' => $tenant_short_name], 'error' => []];

            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }
    /**
     * @OA\Post(
     * path="/api/get-tenant-config-by-email",
     * summary="Get portal tenant theming settings",
     * description="Get portal tenant theming settings passing API key",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="X_NEON",
     *         in="header",
     *         description="API access",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass email",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="portal tenant list"),
     * @OA\Response(response="401", description="portal tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function geTenantConfigByEmail(Request $request)
    {
        try {
            $emailInfo = User::join('user_profiles', 'user_profiles.user_id', 'users.user_id')->where('users.email', $request->email)->where('users.user_type', GlobalVars::TENANT_ADMIN_USER_TYPE)->first();

            $tenant_info = Tenant::where('tenants.tenant_id', $emailInfo->tenant_id)->first();
            $setting_info = [];
            $tenant_short_name = null;
            if ($tenant_info->tenant_id > 0) {
                $setting_info = Setting::where('settings.tenant_id', $tenant_info->tenant_id)->first();
                $tenant_short_name = User::join('user_profiles', 'user_profiles.user_id', 'users.user_id')->where('tenant_id', $tenant_info->tenant_id)->where('users.user_type', GlobalVars::TENANT_ADMIN_USER_TYPE)->value('short_name');
            }
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Tenant config', 'tenant_info' => $tenant_info, 'setting_info' => $setting_info, 'tenant_short_name' => $tenant_short_name], 'error' => []];

            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/boards",
     * summary="Get board list for dropdowns",
     * description="Get board list for dropdowns passing token",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="X_NEON",
     *         in="header",
     *         description="API access",
     *         required=false,
     *      ),
     * @OA\Response(response="200", description="board list"),
     * @OA\Response(response="401", description="board not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getBoardList(Request $request)
    {
        try {

            $user = auth('tenant')->setToken(request()->bearerToken())->user();

            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // print_r($user);
            $country_id = 0;
            // if ($user['tenant_id'] > 0) {
            //     $tenant = Tenant::find($user['tenant_id']);
            //     // dd($tenant->country_id);
            //     $country_id = $tenant->country_id ?? 0;
            // }

            $boardList = Board::where(function ($query) use ($country_id) {
                if ($country_id > 0) {
                    //$query->where('country_id', $country_id);
                }

            })->orderBy('board_name', 'asc')->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Board List', 'boards' => $boardList], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/genders",
     * summary="Get gender list for dropdowns",
     * description="Get gender list for dropdowns",
     * tags={"Common"},
     * @OA\Response(response="200", description="gender list"),
     * @OA\Response(response="401", description="gender not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getGenderList()
    {
        try {

            $genderList = \GlobalVars::GENDERS;

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Gender List', 'genders' => $genderList], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/get-modules",
     * summary="Get module list for subscription plan",
     * description="Get module list for subscription plan",
     * tags={"Common"},
     * @OA\Response(response="200", description="module list"),
     * @OA\Response(response="401", description="module not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getModuleList()
    {
        try {

            $list = \GlobalVars::SUBSCRIPTION_MODULES;

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Module List', 'list' => $list], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/get-currencies",
     * summary="Get currency list",
     * description="Get currency list",
     * tags={"Common"},
     * @OA\Response(response="200", description="currency list"),
     * @OA\Response(response="401", description="currency not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCurrencyList()
    {
        try {

            $list = \GlobalVars::CURRENCIES;

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Currency List', 'list' => $list], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/library-content-types",
     * summary="Get library content type list for dropdowns",
     * description="Get library content type list for dropdowns",
     * tags={"Common"},
     * @OA\Response(response="200", description="library content type list"),
     * @OA\Response(response="401", description="library content type not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getLibraryContentTypeList()
    {
        try {

            $listing = \GlobalVars::LIBRARY_CONTENT_TYPES;

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Library Content Type List', 'listing' => $listing], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/portal-question-types",
     * summary="Get portal supported question type list for dropdowns",
     * description="Get portal tenant list for dropdowns passing API key",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="X_NEON",
     *         in="header",
     *         description="API access",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="portal supported question type list"),
     * @OA\Response(response="401", description="portal supported question type not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getQuestionTypeList(Request $request)
    {
        try {

            $listing = QuestionType::where('question_types.status', GlobalVars::ACTIVE_STATUS)->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Portal question type List', 'question_types' => $listing], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/portal-question-levels",
     * summary="Get portal supported question level list for dropdowns",
     * description="Get portal question level list for dropdowns passing API key",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="X_NEON",
     *         in="header",
     *         description="API access",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="portal supported question level list"),
     * @OA\Response(response="401", description="portal supported question level not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getQuestionLevelList(Request $request)
    {
        try {

            $listing = QuestionLevel::where('status', GlobalVars::ACTIVE_STATUS)->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Portal question level List', 'question_levels' => $listing], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/dropdown/examination-status",
     * summary="Get examination status list for dropdowns",
     * description="Get examination status list for dropdowns",
     * tags={"Common"},
     * @OA\Response(response="200", description="examination status list"),
     * @OA\Response(response="401", description="examination status not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getExaminationStatusList()
    {
        try {

            $examinationStatusList = \GlobalVars::EXAMINATION_STATUS;

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Examination status List', 'exam_status' => $examinationStatusList], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/view-invitation",
     * summary="View invitation",
     * description="View invitation details by passing bearer token",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass token",
     *    @OA\JsonContent(
     *       required={"token","session_subdomain","session_email"},
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="session_subdomain", type="string"),
     *       @OA\Property(property="session_email", type="string"),
     *       @OA\Property(property="session_userid", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Get invitation token details"),
     * @OA\Response(response="401", description="token not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getInvitationInfo(Request $request)
    {
        try {

            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd(empty($user));
            if ($user) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($user == null) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $decryptedToken = CommonHelper::decryptId($request->token);
            $decryptedTokenArr = explode('_', $decryptedToken);

            $tokenEmail = '';
            $tokenSubdomain = '';

            if (is_array($decryptedTokenArr)) {
                $decryptedTokenArrSub = explode('^', $decryptedTokenArr[0]);
                if (is_array($decryptedTokenArrSub)) {
                    $tokenEmail = $decryptedTokenArrSub[0];
                    $tokenSubdomain = $decryptedTokenArrSub[1];
                }
            }

            if ($request->session_email != $tokenEmail) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'This invitation is not for you.']];
                return response()->json($reponse, 400);
            }

            $subdomain = $tokenSubdomain;
            $tenantDatabase = '';
            $tenant_info = Tenant::where('tenants.subdomain', $subdomain)->first();
            $dbpss = '';
            $dbuser = '';
            if (!empty($tenant_info)) {
                $dbpss = CommonHelper::decryptId($tenant_info->dbpassword);
                $tenantDatabase = $tenant_info->dbname;
                $dbuser = $tenant_info->dbuser;
            }

            // dd($tenantDatabase);

            Config::set('database.connections.tenantdb.database', $tenantDatabase);
            Config::set('database.connections.tenantdb.username', $dbuser);
            Config::set('database.connections.tenantdb.password', $dbpss);

            DB::reconnect('tenantdb');

            // Continue processing the request
            $tokenInfo = DB::connection('tenantdb')->table('external_users')->where('token', $request->token)->where('email', $tokenEmail)->first();
            // dd($tokenInfo);

            if (!empty($tokenInfo) && $tokenInfo->status == 'Invitation') {
                $invitee = [
                    'email' => $tokenInfo->email,
                    'name' => $tokenInfo->name,
                ];
                $inviteToken = $tokenInfo->invite_token;
                $groupInfo = CommonHelper::decryptId($inviteToken);
                $groupInfo = json_decode($groupInfo);
                $invitor = DB::connection('tenantdb')
                    ->table('users')
                    ->join('user_profiles', 'user_profiles.user_id', '=', 'users.user_id')
                    ->where('users.user_id', $tokenInfo->invited_by)
                    ->first();
                // dd($invitor);

                // Reset the database connection
                DB::disconnect('tenantdb');

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'invitation info', 'details' => $groupInfo, 'invitor' => $invitor, 'invitee' => $invitee], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                // Reset the database connection
                DB::disconnect('tenantdb');
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid invite token.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/invitation-response",
     * summary="Save invitation response",
     * description="Response invitation by passing bearer token",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass token",
     *    @OA\JsonContent(
     *       required={"token","status"},
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="session_subdomain", type="string"),
     *       @OA\Property(property="session_email", type="string"),
     *       @OA\Property(property="session_userid", type="int"),
     *       @OA\Property(property="status", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Response invitation token"),
     * @OA\Response(response="401", description="token not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateInvitationResponse(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($user == null) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $decryptedToken = CommonHelper::decryptId($request->token);
            $decryptedTokenArr = explode('_', $decryptedToken);

            $tokenEmail = '';
            $tokenSubdomain = '';

            if (is_array($decryptedTokenArr)) {
                $decryptedTokenArrSub = explode('^', $decryptedTokenArr[0]);
                if (is_array($decryptedTokenArrSub)) {
                    $tokenEmail = $decryptedTokenArrSub[0];
                    $tokenSubdomain = $decryptedTokenArrSub[1];
                }
            }
            if ($request->session_email != $tokenEmail) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'This invitation is not for you.']];
                return response()->json($reponse, 400);
            }

            $subdomain = $tokenSubdomain;
            $tenantDatabase = '';
            $tenant_info = Tenant::where('tenants.subdomain', $subdomain)->first();
            $dbpss = '';
            $dbuser = '';
            if (!empty($tenant_info)) {
                $dbpss = CommonHelper::decryptId($tenant_info->dbpassword);
                $tenantDatabase = $tenant_info->dbname;
                $dbuser = $tenant_info->dbuser;
            }

            // dd($tenantDatabase);

            Config::set('database.connections.tenantdb.database', $tenantDatabase);
            Config::set('database.connections.tenantdb.username', $dbuser);
            Config::set('database.connections.tenantdb.password', $dbpss);

            DB::reconnect('tenantdb');

            // Continue processing the request
            $tokenInfo = DB::connection('tenantdb')->table('external_users')->where('token', $request->token)->where('email', $tokenEmail)->first();
            // dd($tokenInfo);

            if (!empty($tokenInfo)) {
                $status = $request->status;
                //update invitation based on invitefor
                $model = DB::connection('tenantdb')->table('external_users')
                    ->where('external_user_id', $tokenInfo->external_user_id)
                    ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]); //'token' => null,

                $study_group_id = CommonHelper::decryptId($tokenInfo->entity_id);
                $modelSGM = DB::connection('tenantdb')->table('study_group_members')
                    ->insert(['study_group_id' => $study_group_id, 'member_user_id' => $tokenInfo->external_user_id, 'is_external_member' => 'Y', 'status' => GlobalVars::ACTIVE_STATUS, 'created_at' => date('Y-m-d H:i:s')]); //'token' => null,

                // Reset the database connection
                DB::disconnect('tenantdb');
                if ($status == GlobalVars::ACTIVE_STATUS) {
                    //local db update
                    $subdomain = $request->session_subdomain;
                    $tenantDatabase = '';
                    $tenant_info = Tenant::where('tenants.subdomain', $subdomain)->first();
                    $dbpss = '';
                    $dbuser = '';
                    if (!empty($tenant_info)) {
                        $dbpss = CommonHelper::decryptId($tenant_info->dbpassword);
                        $tenantDatabase = $tenant_info->dbname;
                        $dbuser = $tenant_info->dbuser;
                    }

                    // dd($tenantDatabase);

                    Config::set('database.connections.tenantdb.database', $tenantDatabase);
                    Config::set('database.connections.tenantdb.username', $dbuser);
                    Config::set('database.connections.tenantdb.password', $dbpss);

                    DB::reconnect('tenantdb');

                    $model = DB::connection('tenantdb')->table('user_external_study_groups')
                        ->insert(['user_id' => $request->session_userid, 'group_info' => $tokenInfo->invite_token, 'created_at' => date('Y-m-d H:i:s')]);

                    // Reset the database connection
                    DB::disconnect('tenantdb');
                }
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'invitation response successful'], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                // Reset the database connection
                DB::disconnect('tenantdb');
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid invite token.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/get-user-subjects",
     * summary="Get user mapped subjects",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass encrpted record user_id",
     *    @OA\JsonContent(
     *       required={"user_id"},
     *       @OA\Property(property="user_id", type="int"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="User subject details"),
     * @OA\Response(response="401", description="User subject details not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubjectsByUserId(Request $request)
    {
        try {
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // if ($user) {
            $user = JWTAuth::parseToken()->authenticate();
            // }
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                // $student_id = $user->user_id;
                $user_id = $request->user_id;

                $resDetails = UserSubject::join('subjects', 'subjects.subject_id', 'user_subjects.subject_id', 'INNER')
                    ->select('user_subjects.*', 'subjects.subject_name', DB::raw("(select academic_year from academic_years where academic_years.academic_year_id=subjects.academic_year_id) as academic_year"), DB::raw("(select name from year_groups where year_groups.year_group_id=subjects.year_group_id) as yeargroup"))
                    ->where('user_subjects.user_id', $user_id)
                    ->orderBy('subjects.subject_name', 'asc')
                    ->get();

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Subject details', 'details' => $resDetails], 'error' => []];
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
     * path="/api/dropdown/salutations",
     * summary="Get salutation list for dropdowns",
     * description="Get salutation list for dropdowns",
     * tags={"Common"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass status",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string",default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="salutation list"),
     * @OA\Response(response="401", description="salutation not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSalutationList(Request $request)
    {
        try {
            $status = $request->status ?? GlobalVars::ACTIVE_STATUS;
            $listing = Salutation::where('status', $status)->get();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Salutation List', 'listing' => $listing], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }
}
