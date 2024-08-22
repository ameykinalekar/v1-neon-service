<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Setting;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserTrustee;
use App\Models\ViewSchool;
use App\Models\ViewStudent;
use App\Models\ViewTeacher;
use App\Models\ViewTrustee;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Mail;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/email/test",
     * summary="Test email smtp",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response="200", description="success"),
     * @OA\Response(response="401", description="failure"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function testEmail()
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            $usettings = Setting::where('tenant_id', '=', $user->tenant_id)->pluck('mail_settings')->first();
            // $usettings = CommonHelper::decryptId($usettings);
            // $usettings = json_decode($usettings);
            //Set mail configuration
            CommonHelper::setMailConfig($usettings);

            $mailData = ['name' => $user->email];

            // dd($mailData);

            Mail::send('emails.testmail', $mailData, function ($message) use ($mailData) {
                $message->to('subhasish@qolarisdata.com', 'SMTP test mail')
                    ->subject('Laravel Basic Testing Mail');
                $message->from('xyz@gmail.com', $mailData['name']);
            });

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Success.'], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/email/exist",
     * summary="Check email already exist",
     * tags={"Common"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass email",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="email available"),
     * @OA\Response(response="401", description="email not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function isEmailExist(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            // if (empty($user)) {
            //     $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
            //     return response()->json($reponse, 400);
            // }

            $reqEmail = $request->only('email');
            $ulist = User::where('email', '=', $reqEmail)->first();
            if (!empty($ulist)) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Email already exist.']];
                return response()->json($reponse, 401);
            } else {
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Email is available.'], 'error' => []];
                return response()->json($reponse, 200);
            }
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/email/exist",
     * summary="Check email already exist",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
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
     * @OA\Response(response="200", description="email available"),
     * @OA\Response(response="401", description="email not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function isTenantEmailExist(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            // if (empty($user)) {
            //     $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
            //     return response()->json($reponse, 400);
            // }

            $reqEmail = $request->only('email');
            $ulist = TenantUser::where('email', '=', $reqEmail)->first();
            if (!empty($ulist)) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Email already exist.']];
                return response()->json($reponse, 401);
            } else {
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Email is available.'], 'error' => []];
                return response()->json($reponse, 200);
            }
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/nino/exist",
     * summary="Check NI number already exist",
     * tags={"Common"},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass ni_number",
     *    @OA\JsonContent(
     *       required={"ni_number"},
     *       @OA\Property(property="ni_number", type="string"),
     *       @OA\Property(property="user_id", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="ni_number available"),
     * @OA\Response(response="401", description="ni_number not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function isTenantNIExist(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            // if (empty($user)) {
            //     $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
            //     return response()->json($reponse, 400);
            // }

            $usrId = $request->only('user_id') ?? null;
            $reqEmail = $request->only('ni_number');
            $ulist = TenantUserProfile::where('ni_number', '=', $reqEmail)
                ->where(function ($query) use ($usrId) {
                    if ($usrId != null) {
                        $query->where('user_id', '!=', $usrId);
                    }
                })
                ->first();
            if (!empty($ulist)) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'NI number already exist.']];
                return response()->json($reponse, 401);
            } else {
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'NI number is available.'], 'error' => []];
                return response()->json($reponse, 200);
            }
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/user/update",
     * summary="Update user profile",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass profile fields",
     *    @OA\JsonContent(
     *       required={"user_id","first_name"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="middle_name", type="string", default=null),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="Profile updated."),
     * @OA\Response(response="400", description="Profile not updated"),
     * @OA\Response(response="401", description="validation error"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function update_profile(Request $request)
    {
        // dd($request->logo);
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }
        try {
            $user_id = $request->user_id;
            $user_list = User::find($user_id);
            if (!empty($user_list)) {

                $user_list->phone = $request->phone ?? $user_list->phone;

                $profile_list = UserProfile::where('user_id', '=', $user_id)->first();
                // dd($profile_list);

                $first_name = $request->first_name ?? $profile_list->first_name;
                $middle_name = $request->middle_name ?? $profile_list->middle_name;
                $last_name = $request->last_name ?? $profile_list->last_name;
                $address = $request->address ?? $profile_list->address;

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

                        $user_list->user_logo = $file1;
                    }
                }
                $user_list->save();

                if (!empty($profile_list)) {
                    UserProfile::where('user_id', '=', $user_id)->update([
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'last_name' => $last_name,
                        'address' => $address,
                    ]);
                }
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User profile updated successfully.'], 'error' => []];
                return response()->json($reponse, 200);

            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'User Information not found.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }
    /**
     * @OA\Post(
     * path="/api/{subdomain}/user/update",
     * summary="Update tenant user profile",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass profile fields",
     *    @OA\JsonContent(
     *       required={"user_id","first_name"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="first_name", type="string"),
     *       @OA\Property(property="middle_name", type="string", default=null),
     *       @OA\Property(property="last_name", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="profile_image", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="Profile updated."),
     * @OA\Response(response="400", description="Profile not updated"),
     * @OA\Response(response="401", description="validation error"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function tenant_update_profile(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }
        try {
            $user_id = $request->user_id;
            $user_list = TenantUser::find($user_id);
            if (!empty($user_list)) {

                $user_list->phone = $request->phone ?? $user_list->phone;

                $profile_list = TenantUserProfile::where('user_id', '=', $user_id)->first();
                // dd($profile_list);

                $first_name = $request->first_name ?? $profile_list->first_name;
                $middle_name = $request->middle_name ?? $profile_list->middle_name;
                $last_name = $request->last_name ?? $profile_list->last_name;
                $address = $request->address ?? $profile_list->address;

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

                        $user_list->user_logo = $file1;
                    }
                }
                $user_list->save();

                if (!empty($profile_list)) {
                    TenantUserProfile::where('user_id', '=', $user_id)->update([
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'last_name' => $last_name,
                        'address' => $address,
                    ]);
                }
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User profile updated successfully.'], 'error' => []];
                return response()->json($reponse, 200);

            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'User Information not found.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/user/change-password",
     * summary="Change user password",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass current, new & confirm passwords",
     *    @OA\JsonContent(
     *       required={"current_password","new_password","confirm_password"},
     *       @OA\Property(property="current_password", type="string"),
     *       @OA\Property(property="new_password", type="string"),
     *       @OA\Property(property="confirm_password", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Password updated."),
     * @OA\Response(response="400", description="Password not updated"),
     * @OA\Response(response="401", description="validation error"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }
        try {

            // $user = JWTAuth::parseToken()->authenticate();
            // $user_profile = UserProfile::where('user_id', '=', $user->id)->first();
            // dd($user_profile);
            $auth = JWTAuth::parseToken()->authenticate();
            $credentials = ['email' => $auth->email, 'password' => $request->new_password];
            $credentialsCoded = CommonHelper::encryptId(json_encode($credentials));

            // The passwords matches
            if (!Hash::check($request->current_password, $auth->password)) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Current Password is Invalid']];
                return response()->json($reponse, 400);
            }

            // Current password and new password same
            if (strcmp($request->current_password, $request->new_password) == 0) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'New Password cannot be same as your current password.']];
                return response()->json($reponse, 400);
            }

            // dd($credentials);

            $user = User::find($auth->user_id);
            $user->password = $request->new_password;
            $user->save();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Password changed successfully.', 'credentials' => $credentialsCoded], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/user/change-password",
     * summary="Change user password",
     * tags={"Common"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass current, new & confirm passwords",
     *    @OA\JsonContent(
     *       required={"current_password","new_password","confirm_password"},
     *       @OA\Property(property="current_password", type="string"),
     *       @OA\Property(property="new_password", type="string"),
     *       @OA\Property(property="confirm_password", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Password updated."),
     * @OA\Response(response="400", description="Password not updated"),
     * @OA\Response(response="401", description="validation error"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function tenant_change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }
        try {
            $token = request()->bearerToken();
            $auth = auth('tenant')->setToken($token)->user();
            $credentials = ['email' => $auth->email, 'password' => $request->new_password];
            $credentialsCoded = CommonHelper::encryptId(json_encode($credentials));

            // The passwords matches
            if (!Hash::check($request->current_password, $auth->password)) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Current Password is Invalid']];
                return response()->json($reponse, 400);
            }

            // Current password and new password same
            if (strcmp($request->current_password, $request->new_password) == 0) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'New Password cannot be same as your current password.']];
                return response()->json($reponse, 400);
            }

            // dd($credentials);

            $user = TenantUser::find($auth->user_id);
            $user->password = $request->new_password;
            $user->save();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Password changed successfully.', 'credentials' => $credentialsCoded], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/get-trustees",
     * summary="Get paginated trustee data",
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
     * @OA\Response(response="200", description="trustee list"),
     * @OA\Response(response="401", description="trustee not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTrustees(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $page = $request->page;
            $search_text = $request->search_text;

            if ($search_text != '') {
                $trustees = ViewTrustee::where(function ($query) use ($search_text) {

                    $query->where('first_name', 'like', '%' . $search_text . '%')
                        ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                        ->orWhere('last_name', 'like', '%' . $search_text . '%')
                        ->orWhere('email', 'like', '%' . $search_text . '%')
                        ->orWhere('phone', 'like', '%' . $search_text . '%');
                })->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            } else {
                $trustees = ViewTrustee::paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            }
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Paginated trustee list', 'trustees' => $trustees], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/create-trustee",
     * summary="Create trustee data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="add new trustee",
     *    @OA\JsonContent(
     *       required={"trustee_name","email","password"},
     *       @OA\Property(property="trustee_name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="trustee added"),
     * @OA\Response(response="401", description="trustee not added"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createTrustee(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                DB::rollback();
                return response()->json($reponse, 400);
            }

            //validate existence of email id in any user type
            $ulist = User::where('email', '=', $request->email)->first();

            if (empty($ulist)) {
                $modelU = new User;
                $modelU->email = $request->email;
                $modelU->password = $request->password;
                $modelU->user_type = GlobalVars::TRUSTEE_USER_TYPE;
                $modelU->phone = $request->phone;
                $modelU->status = GlobalVars::ACTIVE_STATUS;
                $modelU->save();

                $modelUP = new UserProfile;
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $request->trustee_name;
                $modelUP->address = $request->address;
                $modelUP->save();

                //Commit transaction
                DB::commit();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Trustee added successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Email already registered.']];
                DB::rollback();
                return response()->json($reponse, 400);
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
     * path="/api/get-trustee-by-id",
     * summary="Get trustee details by id",
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
     * @OA\Response(response="200", description="trustee details"),
     * @OA\Response(response="401", description="trustee not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTrusteeById(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $user_id = CommonHelper::decryptId($request->user_id);
            // $id = $request->id;

            $trustee = ViewTrustee::where('user_id', '=', $user_id)->first();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Trustee details by id', 'trustee_details' => $trustee], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/update-trustee",
     * summary="Update trustee data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="update trustee information",
     *    @OA\JsonContent(
     *       required={"user_id","trustee_name"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="trustee_name", type="string"),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="trustee updated"),
     * @OA\Response(response="401", description="trustee not updated"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateTrustee(Request $request)
    {
        //Begin database transaction
        DB::beginTransaction();
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $modelU = User::find($request->user_id);
            $modelU->phone = $request->phone;
            $modelU->status = $request->status;
            $modelU->save();

            $getProfileId = UserProfile::where('user_id', '=', $request->user_id)->pluck('user_profile_id');

            $modelUP = UserProfile::find($getProfileId[0]);
            $modelUP->user_id = $modelU->user_id;
            $modelUP->first_name = $request->trustee_name;
            $modelUP->address = $request->address;
            $modelUP->save();

            //Commit transaction
            DB::commit();

            $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Trustee updated successfully.'), 'error' => []];
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
     * path="/api/get-trustee-schools",
     * summary="Get paginated trustee school list",
     * tags={"Trustee"},
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
     * @OA\Response(response="200", description="trustee list"),
     * @OA\Response(response="401", description="trustee not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTrusteeSchools(Request $request)
    {
        try {
            if (!$this->invalidateTrusteeUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $page = $request->page;
            $search_text = $request->search_text;
            $user = JWTAuth::parseToken()->authenticate();
            $trusteeTenantIds = UserTrustee::where('trustee_user_id', $user->user_id)
                ->distinct('tenant_user_id')
                ->pluck('tenant_user_id')
                ->toArray();

            // print_r($trusteeTenantIds);

            $listing = ViewSchool::whereIn('user_id', $trusteeTenantIds)->where(function ($query) use ($search_text) {
                if ($search_text != '') {
                    $query->where('first_name', 'like', '%' . $search_text . '%')
                        ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                        ->orWhere('last_name', 'like', '%' . $search_text . '%')
                        ->orWhere('email', 'like', '%' . $search_text . '%')
                        ->orWhere('phone', 'like', '%' . $search_text . '%');
                }
            })->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Paginated trustee school list', 'listing' => $listing], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/home",
     * summary="Get Tenant admin home page summary",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="Tenant admin home page summary"),
     * @OA\Response(response="401", description="Tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaHomeSummary()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $studentCount = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)->count();
                $teacherCount = ViewTeacher::where('status', GlobalVars::ACTIVE_STATUS)->count();

                $details = array(
                    'student_count' => $studentCount,
                    'teacher_count' => $teacherCount,
                );

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'summary data fetched successfully.', 'details' => $details), 'error' => ''];
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
}
