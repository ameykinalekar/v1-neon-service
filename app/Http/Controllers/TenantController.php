<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Jobs\CreateTenantJob;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserTrustee;
use App\Models\ViewSchool;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/tenant/exist",
     * summary="Check subdomain already exist",
     * tags={"Common"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass subdomain",
     *    @OA\JsonContent(
     *       required={"subdomain"},
     *       @OA\Property(property="subdomain", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Subdomain available"),
     * @OA\Response(response="401", description="Subdomain not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function isTenantDomainExist(Request $request)
    {
        try {
            $reqSubDomain = $request->only('subdomain');
            $tenant = Tenant::where('subdomain', '=', $reqSubDomain)->first();
            if (!empty($tenant)) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Sub domain already exist.']];
                return response()->json($reponse, 401);
            } else {
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Sub domain is available.'], 'error' => []];
                return response()->json($reponse, 200);
            }
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/get-schools",
     * summary="Get paginated schools data",
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
     * @OA\Response(response="200", description="school list"),
     * @OA\Response(response="401", description="school not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSaTenants(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $page = $request->page;
            $search_text = $request->search_text;
            $search_country_id = $request->search_country_id;

            $schools = ViewSchool::where(function ($query) use ($search_text, $search_country_id) {
                if ($search_text != '') {

                    $query->where('first_name', 'like', '%' . $search_text . '%')
                        ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                        ->orWhere('last_name', 'like', '%' . $search_text . '%')
                        ->orWhere('email', 'like', '%' . $search_text . '%')
                        ->orWhere('phone', 'like', '%' . $search_text . '%');
                }
                if ($search_country_id != '') {
                    $query->where('country_id', $search_country_id);
                }
            })->orderBy('first_name', 'asc')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Paginated school list', 'schools' => $schools], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/create-school",
     * summary="Create school data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="add new school",
     *    @OA\JsonContent(
     *       required={"school_name","short_name","subdomain","email","password"},
     *       @OA\Property(property="school_name", type="string"),
     *       @OA\Property(property="short_name", type="string"),
     *       @OA\Property(property="subdomain", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="country_id", type="int", default=null),
     *       @OA\Property(property="trustee_id", type="int", default=null),
     *       @OA\Property(property="logo", type="string", default=null),
     *       @OA\Property(property="background_image", type="string", default=null),
     *       @OA\Property(property="customer_name", type="string", default=null),
     *       @OA\Property(property="company_address", type="string", default=null),
     *       @OA\Property(property="contact_persons", type="array", description="List of contact persons",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of contact person",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of contact person",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="contact person email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="contact person phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="technical_poc", type="array", description="List of technical point of contacts",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of technical_poc",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of technical_poc",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="technical_poc email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="technical_poc phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="customer_service_contact", type="array", description="List of customer service contacts",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of customer_service",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of customer_service",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="customer_service email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="customer_service phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="billing_contact", type="array", description="List of billing contacts",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of billing_contact",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of billing_contact",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="billing_contact email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="billing_contact phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="school added"),
     * @OA\Response(response="401", description="school not added"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createSchool(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                DB::rollback();
                return response()->json($reponse, 400);
            }

            $validator = Validator::make($request->all(), [
                'school_name' => 'required',
                'short_name' => 'required',
                'subdomain' => 'required|unique:tenants,subdomain',
                'email' => 'required',
                'password' => 'required|min:5',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => $validator->messages()->first(), 'data' => $validator->messages()]];
                return response()->json($reponse, 401);
            }
            //validate existence of email id in any user type
            $ulist = User::where('email', '=', $request->email)->first();
            $subdomain = trim(strtolower($request->subdomain));
            $uDomain = Tenant::where('subdomain', '=', $subdomain)->first();

            if (empty($ulist) && empty($uDomain)) {
                $tenantData['subdomain'] = $subdomain;
                $tenantData['country_id'] = $request->country_id ?? '';
                $tenantData['logo'] = '';
                $tenantData['background_image'] = '';
                $tenantDatabase = "tenant_" . $subdomain;
                $dbpss = CommonHelper::encryptId('Welcome1@');
                $tenantData['dbuser'] = $tenantDatabase;
                $tenantData['dbname'] = $tenantDatabase;
                $tenantData['dbpassword'] = $dbpss;

                $tenantData['created_at'] = now();

                // dd($tenantData);
                // $modelTenant = new Tenant;
                // $modelTenant->subdomain = $subdomain;

                if (isset($request->logo) && $request->logo != '') {
                    //image without mime information
                    $imageDataWithoutMime_logo = explode('base64,', $request->logo);

                    $file_logo = $request->logo;
                    if (isset($imageDataWithoutMime_logo[1])) {
                        $file_logo = base64_decode($imageDataWithoutMime_logo[1]);
                    }
                    if ($file_logo) {

                        $folderPath_logo = \GlobalVars::PORTAL_LOGO_PATH;
                        $extension_logo = 'png';
                        if (isset($imageDataWithoutMime_logo[1])) {
                            $extension_logo = explode('/', mime_content_type($request->logo))[1];
                        }
                        // dd($extension);

                        $image_base64_1_logo = $file_logo;
                        $file_logo1 = $folderPath_logo . uniqid() . '.' . $extension_logo;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_logo1, $image_base64_1_logo);

                        // $modelTenant->logo = $file_logo1;
                        $tenantData['logo'] = $file_logo1;
                    }
                }

                if (isset($request->background_image) && $request->background_image != '') {
                    //image without mime information
                    $imageDataWithoutMime_bg = explode('base64,', $request->background_image);

                    $file_bg = $request->background_image;
                    if (isset($imageDataWithoutMime_bg[1])) {
                        $file_bg = base64_decode($imageDataWithoutMime_bg[1]);
                    }
                    if ($file_bg) {

                        $folderPath_bg = \GlobalVars::PORTAL_BG_PATH;
                        $extension_bg = 'png';
                        if (isset($imageDataWithoutMime_bg[1])) {
                            $extension_bg = explode('/', mime_content_type($request->background_image))[1];
                        }
                        // dd($extension);

                        $image_base64_1_bg = $file_bg;
                        $file_bg1 = $folderPath_bg . uniqid() . '.' . $extension_bg;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_bg1, $image_base64_1_bg);

                        // $modelTenant->background_image = $file_bg1;
                        $tenantData['background_image'] = $file_bg1;
                    }
                }

                // $modelTenant->save();

                // Create the tenant in the master database
                // $tenant = Tenant::create($tenantData);
                $tenant = DB::table('tenants')->insert($tenantData);
                $tenant_id = DB::getPdo()->lastInsertId();
                $tenant = Tenant::find($tenant_id);
                // dd($tenant);
                $userData = [
                    'tenant_id' => $tenant_id,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'user_type' => GlobalVars::TENANT_ADMIN_USER_TYPE,
                    'phone' => $request->phone,
                    'status' => GlobalVars::ACTIVE_STATUS,
                    'created_at' => now(),
                ];

                // $modelUser = new User;
                // $modelUser->tenant_id = $tenant->tenant_id;
                // $modelUser->email = $request->email;
                // $modelUser->password = $request->password;
                // $modelUser->user_type = GlobalVars::TENANT_ADMIN_USER_TYPE;
                // $modelUser->phone = $request->phone;
                // $modelUser->status = GlobalVars::ACTIVE_STATUS;
                // $modelUser->save();

                // $modelUser = User::create($userData);
                $modelUser = DB::table('users')->insert($userData);
                $user_id = DB::getPdo()->lastInsertId();

                $contact_persons = $request->contact_persons ?? array();
                $customer_service_contact = $request->customer_service_contact ?? array();
                $technical_poc = $request->technical_poc ?? array();
                $billing_contact = $request->billing_contact ?? array();

                $contact_persons = json_encode($contact_persons);
                $customer_service_contact = json_encode($customer_service_contact);
                $technical_poc = json_encode($technical_poc);
                $billing_contact = json_encode($billing_contact);

                $userProfileData = [
                    'user_id' => $user_id,
                    'first_name' => $request->school_name,
                    'short_name' => $request->short_name,
                    'address' => $request->address,
                    'customer_name' => $request->customer_name ?? '',
                    'company_address' => $request->company_address ?? '',
                    'contact_persons' => $contact_persons,
                    'customer_service_contact' => $customer_service_contact,
                    'technical_poc' => $technical_poc,
                    'billing_contact' => $billing_contact,
                    'created_at' => now(),
                ];
                // dd($userProfileData);
                // $modelUserProfile = UserProfile::create($userProfileData);
                $modelUserProfile = DB::table('user_profiles')->insert($userProfileData);

                // $modelUserProfile = new UserProfile;
                // $modelUserProfile->user_id = $modelUser->user_id;
                // $modelUserProfile->first_name = $request->school_name;
                // $modelUserProfile->short_name = $request->short_name;
                // $modelUserProfile->address = $request->address;
                // $modelUserProfile->save();

                if ($request->trustee_id != "") {
                    DB::table('user_trustees')->where('tenant_user_id', '=', $user_id)->where('trustee_user_id', '=', $request->trustee_id)->delete();
                    // UserTrustee::where('tenant_user_id', '=', $modelUser->id)->where('trustee_user_id', '=', $request->trustee_id)->delete();

                    $userTrusteeData = [
                        'tenant_user_id' => $user_id,
                        'trustee_user_id' => $request->trustee_id,
                        'created_at' => now(),
                    ];
                    // dd($userTrusteeData);
                    // $modelUserTrustee = UserTrustee::create($userTrusteeData);
                    $modelUserTrustee = DB::table('user_trustees')->insert($userTrusteeData);

                    // $modelUserTrustee = new UserTrustee;
                    // $modelUserTrustee->tenant_user_id = $modelUser->user_id;
                    // $modelUserTrustee->trustee_user_id = $request->trustee_id;
                    // $modelUserTrustee->save();
                }
                $jobres = CreateTenantJob::dispatch($tenant);

                // dd($jobres);
                //Commit transaction
                DB::commit();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'School added successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                if (!empty($uDomain)) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Subdomain already registered.']];
                    DB::rollback();
                    return response()->json($reponse, 400);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Email already registered.']];
                    DB::rollback();
                    return response()->json($reponse, 400);
                }

            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];

            //Rollback transaction
            DB::rollback();
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/get-school-by-id",
     * summary="Get school details by id",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"user_id"},
     *       @OA\Property(property="user_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="school details"),
     * @OA\Response(response="401", description="school not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSchoolById(Request $request)
    {
        // dd($request->all());
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $user_id = CommonHelper::decryptId($request->user_id);
            // $id = $request->id;
            // dd($id);

            $schoolInfo = ViewSchool::join('user_trustees', 'user_trustees.tenant_user_id', '=', 'schools.user_id', 'LEFT')->where('user_id', '=', $user_id)->select('schools.*', 'user_trustees.trustee_user_id as trustee_id')->first();

            // dd($schoolInfo);

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'School details by user_id', 'school_details' => $schoolInfo], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/update-school",
     * summary="Update school data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="update school information",
     *    @OA\JsonContent(
     *       required={"user_id","school_name","short_name"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="school_name", type="string"),
     *       @OA\Property(property="short_name", type="string"),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="country_id", type="int", default=null),
     *       @OA\Property(property="trustee_id", type="int", default=null),
     *       @OA\Property(property="logo", type="string", default=null),
     *       @OA\Property(property="background_image", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *       @OA\Property(property="customer_name", type="string", default=null),
     *       @OA\Property(property="company_address", type="string", default=null),
     *       @OA\Property(property="contact_persons", type="array", description="List of contact persons",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of contact person",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of contact person",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="contact person email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="contact person phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="technical_poc", type="array", description="List of technical point of contacts",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of technical_poc",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of technical_poc",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="technical_poc email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="technical_poc phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="customer_service_contact", type="array", description="List of customer service contacts",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of customer_service",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of customer_service",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="customer_service email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="customer_service phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="billing_contact", type="array", description="List of billing contacts",
     *       @OA\Items(
     *          @OA\Property(
     *              property="salutation",
     *              description="salutation of billing_contact",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="name",
     *              description="name of billing_contact",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="email",
     *              description="billing_contact email",
     *              type="string",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="phone",
     *              description="billing_contact phone",
     *              type="string",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *     )
     * ),
     * @OA\Response(response="200", description="school updated"),
     * @OA\Response(response="401", description="school not updated"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateSchool(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'school_name' => 'required',
                'short_name' => 'required',
                'status' => 'required',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                return response()->json($reponse, 401);
            }
            $modelU = User::find($request->user_id);
            $modelU->phone = $request->phone;
            $modelU->status = $request->status;
            $modelU->save();

            $modelTenant = Tenant::find($modelU->tenant_id);
            if (isset($request->logo) && $request->logo != '') {
                //image without mime information
                $imageDataWithoutMime_logo = explode('base64,', $request->logo);

                $file_logo = $request->logo;
                if (isset($imageDataWithoutMime_logo[1])) {
                    $file_logo = base64_decode($imageDataWithoutMime_logo[1]);
                }
                if ($file_logo) {

                    $folderPath_logo = \GlobalVars::PORTAL_LOGO_PATH;
                    $extension_logo = 'png';
                    if (isset($imageDataWithoutMime_logo[1])) {
                        $extension_logo = explode('/', mime_content_type($request->logo))[1];
                    }
                    // dd($extension);

                    $image_base64_1_logo = $file_logo;
                    $file_logo1 = $folderPath_logo . uniqid() . '.' . $extension_logo;
                    // $file1 = uniqid() . '.' . $extension;

                    Storage::disk('public')->put($file_logo1, $image_base64_1_logo);

                    $modelTenant->logo = $file_logo1;
                }
            }

            if (isset($request->background_image) && $request->background_image != '') {
                //image without mime information
                $imageDataWithoutMime_bg = explode('base64,', $request->background_image);

                $file_bg = $request->background_image;
                if (isset($imageDataWithoutMime_bg[1])) {
                    $file_bg = base64_decode($imageDataWithoutMime_bg[1]);
                }
                if ($file_bg) {

                    $folderPath_bg = \GlobalVars::PORTAL_BG_PATH;
                    $extension_bg = 'png';
                    if (isset($imageDataWithoutMime_bg[1])) {
                        $extension_bg = explode('/', mime_content_type($request->background_image))[1];
                    }
                    // dd($extension);

                    $image_base64_1_bg = $file_bg;
                    $file_bg1 = $folderPath_bg . uniqid() . '.' . $extension_bg;
                    // $file1 = uniqid() . '.' . $extension;

                    Storage::disk('public')->put($file_bg1, $image_base64_1_bg);

                    $modelTenant->background_image = $file_bg1;
                }
            }
            $modelTenant->country_id = $request->country_id ?? '';

            $modelTenant->save();

            $contact_persons = $request->contact_persons ?? array();
            $customer_service_contact = $request->customer_service_contact ?? array();
            $technical_poc = $request->technical_poc ?? array();
            $billing_contact = $request->billing_contact ?? array();

            $contact_persons = json_encode($contact_persons);
            $customer_service_contact = json_encode($customer_service_contact);
            $technical_poc = json_encode($technical_poc);
            $billing_contact = json_encode($billing_contact);

            $getProfileId = UserProfile::where('user_id', '=', $request->user_id)->pluck('user_profile_id');

            $modelUserProfile = UserProfile::find($getProfileId[0]);
            $modelUserProfile->user_id = $modelU->user_id;
            $modelUserProfile->first_name = $request->school_name;
            $modelUserProfile->short_name = $request->short_name;
            $modelUserProfile->address = $request->address;
            $modelUserProfile->customer_name = $request->customer_name ?? '';
            $modelUserProfile->company_address = $request->company_address ?? '';
            $modelUserProfile->contact_persons = $contact_persons;
            $modelUserProfile->customer_service_contact = $customer_service_contact;
            $modelUserProfile->technical_poc = $technical_poc;
            $modelUserProfile->billing_contact = $billing_contact;

            $modelUserProfile->save();

            if ($request->trustee_id != "") {
                UserTrustee::where('tenant_user_id', '=', $modelU->user_id)->where('trustee_user_id', '=', $request->trustee_id)->delete();
                $modelUserTrustee = new UserTrustee;
                $modelUserTrustee->tenant_user_id = $modelU->user_id;
                $modelUserTrustee->trustee_user_id = $request->trustee_id;
                $modelUserTrustee->save();
            }

            //Commit transaction
            DB::commit();

            $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'School updated successfully.'), 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            //Rollback transaction
            DB::rollback();
            return response()->json($reponse, 500);
        }
    }

}
