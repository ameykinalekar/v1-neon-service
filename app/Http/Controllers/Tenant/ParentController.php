<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\UserSibling;
use App\Models\UserSubject;
use App\Models\ViewParent;
use App\Models\ViewStudent;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Mail;

class ParentController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/parents",
     * summary="Get parent master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant parent list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getParents(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page;
                $search_text = $request->search_text;

                $itemList = ViewParent::where(function ($query) use ($search_text) {
                    if ($search_text != null) {
                        $query->where('first_name', 'like', '%' . $search_text . '%')
                            ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                            ->orWhere('last_name', 'like', '%' . $search_text . '%')
                            ->orWhere('email', 'like', '%' . $search_text . '%')
                            ->orWhere('phone', 'like', '%' . $search_text . '%')
                            ->orWhere('code', 'like', '%' . $search_text . '%')
                            ->orWhere('address', 'like', '%' . $search_text . '%');
                    }
                })->orderBy('first_name', 'asc')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Parent list fetched successfully.', 'item_list' => $itemList), 'error' => ''];
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
     * path="/api/{subdomain}/create-parent",
     * summary="Create parent master data of tenant as per subdomain",
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
     *     description="add new parent",
     *    @OA\JsonContent(
     *       required={"first_name","email","password","phone","gender"},
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant parent master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createParent(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                //validate existence of email id in any user type
                $ulist = TenantUser::where('email', '=', $request->email)->first();
                $parent_code = CommonHelper::parentCode();

                if (empty($ulist)) {
                    $modelU = new TenantUser;
                    $modelU->email = $request->email;
                    $modelU->tenant_id = $user->tenant_id;
                    $modelU->password = $request->password;
                    $modelU->user_type = GlobalVars::TENANT_PARENT_USER_TYPE;
                    $modelU->role = GlobalVars::TENANT_PARENT_ROLE;
                    $modelU->phone = $request->phone;
                    $modelU->code = $parent_code;
                    $modelU->status = GlobalVars::ACTIVE_STATUS;
                    if (isset($request->profile_image) && $request->profile_image != '') {
                        //image without mime information
                        $imageDataWithoutMime = explode('base64,', $request->profile_image);
                        $file = $request->profile_image;
                        if (isset($imageDataWithoutMime[1])) {
                            $file = base64_decode($imageDataWithoutMime[1]);
                        }
                        if ($file) {

                            $folderPath1 = \GlobalVars::USER_PIC_PATH . $user->tenant_id . '/';
                            $extension = 'png';
                            if (isset($imageDataWithoutMime[1])) {
                                $extension = explode('/', mime_content_type($request->profile_image))[1];
                            }
                            // dd($extension);

                            $image_base64_1 = $file;
                            $file1 = $folderPath1 . uniqid() . '.' . $extension;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file1, $image_base64_1);

                            $modelU->user_logo = $file1;
                        }
                    }
                    $modelU->save();

                    $modelUP = new TenantUserProfile;
                    $modelUP->user_id = $modelU->user_id;
                    $modelUP->first_name = $request->first_name;
                    $modelUP->last_name = $request->last_name;
                    $modelUP->address = $request->address;
                    $modelUP->gender = $request->gender;

                    $modelUP->save();

                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Email already registered.']];
                    //Rollback transaction
                    DB::rollback();
                    return response()->json($reponse, 400);
                }
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                //Rollback transaction
                DB::rollback();
                return response()->json($reponse, 400);
            }
        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            //Rollback transaction
            DB::rollback();
            return response()->json($reponse, 500);
        }
        //Commit transaction
        DB::commit();

        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Parent added successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/get-parent-by-id",
     * summary="Get parent details by id",
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
     *     description="Pass encrpted record parent user_id",
     *    @OA\JsonContent(
     *       required={"user_id"},
     *       @OA\Property(property="user_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="Parent details"),
     * @OA\Response(response="401", description="Parent not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getParentById(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $user_id = CommonHelper::decryptId($request->user_id);
                $resDetails = ViewParent::find($user_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Parent details by id', 'details' => $resDetails], 'error' => []];
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
     * path="/api/{subdomain}/update-parent",
     * summary="Update parent master data of tenant as per subdomain",
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
     *     description="update parent",
     *    @OA\JsonContent(
     *       required={"user_id","first_name","email","phone","gender"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="gender", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant parent master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateParent(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $modelU = TenantUser::find($request->user_id);
                // print_r($modelU);
                $modelU->phone = $request->phone;
                $modelU->status = $request->status;
                if (isset($request->profile_image) && $request->profile_image != '') {
                    //image without mime information
                    $imageDataWithoutMime = explode('base64,', $request->profile_image);
                    $file = $request->profile_image;
                    if (isset($imageDataWithoutMime[1])) {
                        $file = base64_decode($imageDataWithoutMime[1]);
                    }
                    if ($file) {

                        $folderPath1 = \GlobalVars::USER_PIC_PATH;
                        $extension = 'png';
                        if (isset($imageDataWithoutMime[1])) {
                            $extension = explode('/', mime_content_type($request->profile_image))[1];
                        }
                        // dd($extension);

                        $image_base64_1 = $file;
                        $file1 = $folderPath1 . uniqid() . '.' . $extension;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file1, $image_base64_1);
                        if ($modelU->user_logo != '') {
                            $arrFile = explode('.', $modelU->user_logo);
                            if (isset($arrFile[1])) {
                                Storage::disk('public')->delete($modelU->user_logo);
                            }
                        }

                        $modelU->user_logo = $file1;
                    }
                }
                $modelU->save();

                $getProfileId = TenantUserProfile::where('user_id', '=', $request->user_id)->pluck('user_profile_id');
                // dd($getProfileId);

                $modelUP = TenantUserProfile::find($getProfileId[0]);
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $request->first_name;
                $modelUP->last_name = $request->last_name;
                $modelUP->address = $request->address;
                $modelUP->gender = $request->gender;

                $modelUP->save();

            } else {
                //Rollback transaction
                DB::rollback();
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            // throw ($e);
            //Rollback transaction
            DB::rollback();
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }
        //Commit transaction
        DB::commit();
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Parent updated successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/generate-child-link",
     * summary="Generate Link child data of tenant as per subdomain",
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
     *     description="Link child parent",
     *    @OA\JsonContent(
     *       required={"child_email","child_code"},
     *       @OA\Property(property="child_email", type="string"),
     *       @OA\Property(property="child_code", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Generate Link parent & child"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function generateChildLink(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $getStudent = ViewStudent::where('email', '=', $request->child_email)->where('code', '=', $request->child_code)->first();

                // dd($getStudent);

                if (empty($getStudent)) {
                    //Rollback transaction
                    DB::rollback();
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such student information exist.']];
                    return response()->json($reponse, 400);
                } else {
                    $parent_user_id = $user->user_id;
                    $sibling_user_id = $getStudent->user_id;

                    //check if child already linked
                    $checkSibling = UserSibling::where('parent_user_id', '=', $parent_user_id)->where('sibling_user_id', '=', $sibling_user_id)->first();

                    if (!empty($checkSibling)) {
                        //child liked but need to be verified if linking is complete
                        if ($checkSibling->token == null && $checkSibling->status == GlobalVars::ACTIVE_STATUS) {
                            //Rollback transaction
                            DB::rollback();
                            $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Student already linked.']];
                            return response()->json($reponse, 400);
                        } else {
                            //Rollback transaction
                            DB::rollback();
                            $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Please complete the previous process.']];
                            return response()->json($reponse, 400);
                        }
                    } else {
                        $tenant_info = [];
                        if ($user->tenant_id > 0) {
                            $tenant_info = Tenant::find($user->tenant_id);
                        }

                        $otp = mt_rand(1000, 9999);
                        $token = $parent_user_id . '^' . $request->child_email . '~' . $sibling_user_id . '#' . $otp;
                        $token = \Helpers::encryptId($token);

                        //insert into db and send otp email
                        $model = new UserSibling;
                        $model->parent_user_id = $parent_user_id;
                        $model->sibling_user_id = $sibling_user_id;
                        $model->token = $token;

                        //send otp email
                        $usettings = Setting::where('tenant_id', '=', $user->tenant_id)->pluck('mail_settings')->first();
                        $uProfile = TenantUserProfile::where('user_id', $parent_user_id)->first();
                        $to_name = $uProfile->first_name ?? '' . ' ' . $uProfile->last_name ?? '';

                        $childName = $getStudent->first_name ?? '' . ' ' . $getStudent->last_name ?? '';
                        $messageBody = '<p>Thank you for initiating student link process for the following student information:<p><strong>Student Information</strong><p>Student Name: ' . $childName . '</p><p>Student Code: ' . $request->child_code . '</p><p>Student Email: ' . $request->child_email . '</p></p><p>Please provide this OTP to complete the process. OTP: <b>' . $otp . '</b></p>';

                        $mailData = array(
                            'email' => $user->email,
                            'name' => $to_name,
                            'mailbody' => $messageBody,
                            'tenant_info' => $tenant_info,
                            'emailHeaderSubject' => 'OTP to link your student',
                        );
                        // dd($usettings);
                        CommonHelper::setMailConfig($usettings);
                        $sender = CommonHelper::decryptId($usettings);
                        $sender = json_decode($sender);
                        // dd($sender->smtp_username);

                        Mail::send('emails.common', $mailData, function ($message)
                             use ($mailData, $sender) {
                                // $message->from($sender->smtp_username, $mailData['tenant_info']->subdomain);
                                $message->to($mailData['email'], $mailData['name'])->subject($mailData['emailHeaderSubject']);
                            });

                        $model->save();
                    }
                }

            } else {
                //Rollback transaction
                DB::rollback();
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            // throw ($e);
            //Rollback transaction
            DB::rollback();
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }
        //Commit transaction
        DB::commit();
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'An OTP has been sent to your registered email to complete. \n Please provide that OTP on validate OTP step.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/validate-child",
     * summary="Validate child data of tenant as per subdomain",
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
     *     description="Validate Link child parent",
     *    @OA\JsonContent(
     *       required={"child_email","child_code","otp"},
     *       @OA\Property(property="child_email", type="string"),
     *       @OA\Property(property="child_code", type="string"),
     *       @OA\Property(property="otp", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Validate Link parent & child"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function validateLinkChild(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $getStudent = ViewStudent::where('email', '=', $request->child_email)->where('code', '=', $request->child_code)->first();

                // dd($getStudent);

                if (empty($getStudent)) {
                    //Rollback transaction
                    DB::rollback();
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such student information exist.']];
                    return response()->json($reponse, 400);
                } else {
                    $parent_user_id = $user->user_id;
                    $sibling_user_id = $getStudent->user_id;

                    //check if child already linked
                    $checkSibling = UserSibling::where('parent_user_id', '=', $parent_user_id)->where('sibling_user_id', '=', $sibling_user_id)->whereNotNull('token')->first();

                    if (!empty($checkSibling)) {
                        //child liked but need to be verified if linking is complete
                        $tenant_info = [];
                        if ($user->tenant_id > 0) {
                            $tenant_info = Tenant::find($user->tenant_id);
                        }

                        $otp = $request->otp;
                        $tokenInDb = $checkSibling->token;
                        $token = $parent_user_id . '^' . $request->child_email . '~' . $sibling_user_id . '#' . $otp;
                        $token = \Helpers::encryptId($token);

                        if ($token == $tokenInDb) {
                            //update into db and send otp email
                            $model = UserSibling::find($checkSibling->user_sibling_id);
                            $model->parent_user_id = $parent_user_id;
                            $model->sibling_user_id = $sibling_user_id;
                            $model->token = null;
                            $model->status = GlobalVars::ACTIVE_STATUS;

                            //send link confirmation email
                            $usettings = Setting::where('tenant_id', '=', $user->tenant_id)->pluck('mail_settings')->first();
                            $uProfile = TenantUserProfile::where('user_id', $parent_user_id)->first();
                            $to_name = $uProfile->first_name ?? '' . ' ' . $uProfile->last_name ?? '';

                            $childName = $getStudent->first_name ?? '' . ' ' . $getStudent->last_name ?? '';
                            $messageBody = '<p>Your student linking is successful.<p><strong>Student Information</strong><p>Student Name: ' . $childName . '</p><p>Student Code: ' . $request->child_code . '</p><p>Student Email: ' . $request->child_email . '</p></p>';

                            $mailData = array(
                                'email' => $user->email,
                                'name' => $to_name,
                                'mailbody' => $messageBody,
                                'tenant_info' => $tenant_info,
                                'emailHeaderSubject' => 'Student successfully linked.',
                            );
                            // dd($usettings);
                            CommonHelper::setMailConfig($usettings);
                            $sender = CommonHelper::decryptId($usettings);
                            $sender = json_decode($sender);
                            // dd($sender->smtp_username);

                            Mail::send('emails.common', $mailData, function ($message)
                                 use ($mailData, $sender) {
                                    // $message->from($sender->smtp_username, $mailData['tenant_info']->subdomain);
                                    $message->to($mailData['email'], $mailData['name'])->subject($mailData['emailHeaderSubject']);
                                });

                            $model->save();
                        } else {
                            //Rollback transaction
                            DB::rollback();
                            $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid OTP']];
                            return response()->json($reponse, 400);
                        }

                    } else {
                        //Rollback transaction
                        DB::rollback();
                        $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No active student linking process found.']];
                        return response()->json($reponse, 400);
                    }
                }

            } else {
                //Rollback transaction
                DB::rollback();
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            // throw ($e);
            //Rollback transaction
            DB::rollback();
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }
        //Commit transaction
        DB::commit();
        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Student linked successfully.'), 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/parent/children",
     * summary="Get parent children listing data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant parent list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getChildren(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $page = $request->page;
                $search_text = $request->search_text;
                $search_academic_year_id = $request->search_academic_year_id;
                $search_year_group_id = $request->search_year_group_id;
                $search_subject_id = $request->search_subject_id;

                $subjectArray = Subject::where(function ($query) use ($search_subject_id, $search_academic_year_id, $search_year_group_id) {
                    if ($search_academic_year_id != null) {
                        $query->where('academic_year_id', $search_academic_year_id);
                    }
                    if ($search_year_group_id != null) {
                        $query->where('year_group_id', $search_year_group_id);
                    }
                    if ($search_subject_id != null) {
                        $query->where('subject_id', $search_subject_id);
                    }
                })
                    ->distinct('subject_id')
                    ->pluck('subject_id')
                    ->toArray();

                $subjectUsers = UserSubject::whereIn('subject_id', $subjectArray)
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();

                $itemList = ViewStudent::join('user_siblings', 'user_siblings.sibling_user_id', 'students.user_id')
                    ->select('students.*', 'user_siblings.status as childstatus', 'user_siblings.token')
                    ->where('user_siblings.parent_user_id', $user->user_id)
                    ->where(function ($query) use ($search_text, $subjectUsers, $search_subject_id, $search_academic_year_id, $search_year_group_id) {
                        if ($search_text != null) {
                            $query->where('first_name', 'like', '%' . $search_text . '%')
                                ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                                ->orWhere('last_name', 'like', '%' . $search_text . '%')
                                ->orWhere('email', 'like', '%' . $search_text . '%')
                                ->orWhere('phone', 'like', '%' . $search_text . '%')
                                ->orWhere('code', 'like', '%' . $search_text . '%')
                                ->orWhere('address', 'like', '%' . $search_text . '%');
                        }
                        if ($search_academic_year_id != null || $search_year_group_id != null || $search_subject_id != null) {
                            $query->whereIn('user_id', $subjectUsers);
                        }
                    })->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Children list fetched successfully.', 'item_list' => $itemList), 'error' => ''];
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
     * path="/api/{subdomain}/parent/students",
     * summary="Get parent student listing data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant parent student list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudents()
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $itemList = ViewStudent::join('user_siblings', 'user_siblings.sibling_user_id', 'students.user_id')
                    ->select('students.*', 'user_siblings.status as childstatus', 'user_siblings.token')
                    ->where('user_siblings.parent_user_id', $user->user_id)
                    ->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Children list fetched successfully.', 'item_list' => $itemList), 'error' => ''];
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
