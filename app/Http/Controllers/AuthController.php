<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\PasswordReset;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Tenant\TenantPasswordReset;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Mail;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    use GeneralMethods;
    // public function register(Request $request)
    // {
    //     //Validate data
    //     $data = $request->only('name', 'email', 'password');
    //     $validator = Validator::make($data, [
    //         'name' => 'required|string',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required|string|min:6|max:50'
    //     ]);

    //     //Send failed response if request is not valid
    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->messages()], 200);
    //     }

    //     //Request is valid, create new user
    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password)
    //     ]);

    //     //User created, return success response
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'User created successfully',
    //         'data' => $user
    //     ], Response::HTTP_OK);
    // }

    /**
     * Handle an incoming authentication request.
     * @OA\Post(
     * path="/api/auth",
     * summary="Authenticate user and generate JWT token",
     * description="Returns user token based on credentials",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="text", example="abc123"),
     *    )
     * ),
     * @OA\Response(response="200", description="Login successful"),
     * @OA\Response(response="401", description="Invalid credentials"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentialsCoded = CommonHelper::encryptId(json_encode($credentials));

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required',
            'password' => 'required|string|min:2|max:50',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }

        //Request is validated
        //Check token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Login credentials are invalid.', 'data' => []]];
                $this->auditInvalidLogIn($credentials);
                return response()->json($reponse, 400);
            }
        } catch (JWTException $e) {
            // return $credentials;
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Could not create token.', 'data' => []]];
            return response()->json($reponse, 500);
        }

        //Token created, return with success response and jwt token
        $this->auditValidLogIn($credentials, $token);
        $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User token generated successfully.', 'token' => $token, 'credentials' => $credentialsCoded], 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * Handle an incoming authentication request.
     * @OA\Post(
     * path="/api/{subdomain}/auth",
     * summary="Authenticate tenant user and generate JWT token",
     * description="Returns user token based on credentials",
     * tags={"Authentication"},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *       @OA\Property(property="password", type="string", format="text", example="abc123"),
     *    )
     * ),
     * @OA\Response(response="200", description="Login successful"),
     * @OA\Response(response="401", description="Invalid credentials"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function authenticateTenant(Request $request)
    {

        $credentials = $request->only('email', 'password');
        $credentialsCoded = CommonHelper::encryptId(json_encode($credentials));

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required',
            'password' => 'required|string|min:2|max:50',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }

        //Request is validated
        //Check token
        try {
            // dd($token = JWTAuth::attempt($credentials));
            if (!$token = auth('tenant')->attempt($credentials)) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Login credentials are invalid.', 'data' => []]];
                $this->auditInvalidLogIn($credentials, 'tenant');
                return response()->json($reponse, 400);
            }
        } catch (JWTException $e) {
            // return $credentials;
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Could not create token.', 'data' => []]];
            return response()->json($reponse, 500);
        }

        //Token created, return with success response and jwt token

        $this->auditValidLogIn($credentials, $token, 'tenant');
        $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User token generated successfully.', 'token' => $token, 'credentials' => $credentialsCoded], 'error' => []];
        return response()->json($reponse, 200);
    }

    /**
     * @OA\Post(
     * path="/api/auth/logout",
     * summary="Check & destroy user JWT token",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass user token",
     *    @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Destroy User Token."),
     * @OA\Response(response="401", description="Invalid token"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
            return response()->json($reponse, 401);
        }

        //Request is validated, do logout
        try {
            $this->auditLogOut($request->token);
            // JWTAuth::invalidate($request->token);, $forceForever = false
            JWTAuth::manager()->invalidate(new \Tymon\JWTAuth\Token($request->token));
            // auth('tenant')->manager()->invalidate(new \Tymon\JWTAuth\Token($request->token));
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User has been logged out'], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be logged out', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/auth/validate",
     * summary="Get/check user JWT token",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass user token",
     *    @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Token validated and user information provided"),
     * @OA\Response(response="401", description="Invalid token"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function checkToken(Request $request)
    {
        try {
            $this->validate($request, [
                'token' => 'required',
            ]);

            $user = JWTAuth::parseToken()->authenticate();
            $user_profile = UserProfile::where('user_id', '=', $user->user_id)->first();
            $settings = Setting::where('tenant_id', '=', $user->tenant_id)->first();
            $tenant_info = [];
            if ($user->tenant_id > 0) {
                $tenant_info = Tenant::find($user->tenant_id);
            }
            $total_active_users_count = User::where('status', GlobalVars::ACTIVE_STATUS)->count();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Valid user', 'user' => $user, 'profile_info' => $user_profile, 'setting_info' => $settings, 'tenant_info' => $tenant_info, 'total_active_users_count' => $total_active_users_count], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be authenticated.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/auth/validate",
     * summary="Get/check tenant user JWT token",
     * tags={"Authentication"},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass user token",
     *    @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Token validated and user information provided"),
     * @OA\Response(response="401", description="Invalid token"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function checkTokenTenant(Request $request)
    {
        try {
            $this->validate($request, [
                'token' => 'required',
            ]);

            // dd($request->all());

            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken($request->token)->user();
            // $user = auth('tenant')->getPayload();
            // dd(UserProfile);
            $user_profile = TenantUserProfile::where('user_id', '=', $user->user_id)->first();
            $settings = Setting::where('tenant_id', '=', $user->tenant_id)->first();
            $tenant_info = [];
            if ($user->tenant_id > 0) {
                $tenant_info = Tenant::find($user->tenant_id);
            }
            $total_active_users_count = TenantUser::where('status', GlobalVars::ACTIVE_STATUS)->count();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Valid user', 'user' => $user, 'profile_info' => $user_profile, 'setting_info' => $settings, 'tenant_info' => $tenant_info, 'total_active_users_count' => $total_active_users_count], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be authenticated.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/forgot-password",
     * summary="Generate reset password link after verification",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass regsitered email",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Email reset password link to registered email"),
     * @OA\Response(response="401", description="Invalid email"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function forgotPassword(Request $request)
    {

        try {
            $credentials = $request->only('email');
            //valid credential
            $validator = Validator::make($credentials, [
                'email' => 'required',
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                return response()->json($reponse, 401);
            }
            $emailAddressExists = User::Where('email', $request->email)->first();
            // dd($emailAddressExists);
            if ($emailAddressExists) {
                // if ($emailAddressExists->user_type != 'A' || $emailAddressExists->user_type != 'TA') {
                //     $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Provided email is not valid.']];
                //     return response()->json($reponse, 400);
                // }
                $token = uniqid(mt_rand(), true);

                //save token for forgot password request
                $resetPwd = new PasswordReset();
                $resetPwd->email = $emailAddressExists->email;
                $resetPwd->token = $token;
                $resetPwd->usertype = $emailAddressExists->user_type;
                $resetPwd->save();
                $subdomain = null;
                $url = config('app.frontend_base_url') . '/reset-password/' . \Helpers::encryptId($emailAddressExists->email . '~' . $emailAddressExists->user_type . '_' . $token);

                if ($emailAddressExists->user_type == GlobalVars::TENANT_ADMIN_USER_TYPE) {
                    $subdomain = Tenant::where('tenant_id', $emailAddressExists->tenant_id)->value('subdomain');
                    $url = config('app.frontend_base_url') . '/' . $subdomain . '/reset-password/' . \Helpers::encryptId($emailAddressExists->email . '~' . $emailAddressExists->user_type . '_' . $token);
                }

                $uProfile = UserProfile::where('user_id', $emailAddressExists->user_id)->first();
                // $url = Route('reset_newpassword', \Helpers::encryptId($emailAddressExists->email . '_' . $token));
                $tenant_info = null;
                if ($emailAddressExists->tenant_id > 0) {
                    $tenant_info = Tenant::where('tenant_id', $emailAddressExists->tenant_id)->first();
                }
                $usettings = Setting::where('tenant_id', '=', $emailAddressExists->tenant_id)->pluck('mail_settings')->first();
                $mailData = array(
                    'email' => $emailAddressExists->email,
                    'url' => $url,
                    'tenant_info' => $tenant_info,
                    'emailHeaderSubject' => 'Reset Your Password',
                );
                CommonHelper::setMailConfig($usettings);
                Mail::send('emails.resetPassword', $mailData, function ($message)
                     use ($mailData, $uProfile) {
                        $message->from("info@neon-edu.com", "Neon Edu");
                        $message->to($mailData['email'], $uProfile->first_name)->subject($uProfile->short_name ?? \GlobalVars::ADMIN_NAME . ': ' . $mailData['emailHeaderSubject']);
                    });

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Reset information mail sent to your email id.', 'mailData' => $mailData], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Provided email is not valid.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            // throw $e;
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be authenticated.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/forgot-password",
     * summary="Generate reset password link after verification",
     * tags={"Authentication"},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass regsitered email",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Email reset password link to registered email"),
     * @OA\Response(response="401", description="Invalid email"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function forgotPasswordTenant(Request $request)
    {

        try {
            $credentials = $request->only('email');
            //valid credential
            $validator = Validator::make($credentials, [
                'email' => 'required',
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                return response()->json($reponse, 401);
            }
            $emailAddressExists = TenantUser::Where('email', $request->email)->first();
            // dd($emailAddressExists);
            if ($emailAddressExists) {

                $token = uniqid(mt_rand(), true);

                //save token for forgot password request
                $resetPwd = new TenantPasswordReset();
                $resetPwd->email = $emailAddressExists->email;
                $resetPwd->token = $token;
                $resetPwd->usertype = $emailAddressExists->user_type;
                $resetPwd->save();

                $subdomain = $this->getTenantSubdomain();

                $url = config('app.frontend_base_url') . '/' . $subdomain . '/reset-password/' . \Helpers::encryptId($emailAddressExists->email . '~' . $emailAddressExists->user_type . '_' . $token);

                $mailData = array(
                    'email' => $emailAddressExists->email,
                    'url' => $url,
                    'emailHeaderSubject' => 'Reset Your Password',
                );
                $usettings = Setting::where('tenant_id', '=', $emailAddressExists->tenant_id)->pluck('mail_settings')->first();
                CommonHelper::setMailConfig($usettings);
                Mail::send('emails.resetPassword', $mailData, function ($message)
                     use ($mailData) {
                        $message->from("info@neon-edu.com", "Neon Edu");
                        $message->to($mailData['email'], "Neon Edu")->subject(\GlobalVars::ADMIN_NAME . ': ' . $mailData['emailHeaderSubject']);
                    });

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Reset information mail sent to your email id.', 'mailData' => $mailData], 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Provided email is not valid.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be authenticated.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/reset-password",
     * summary="Set new password after token validation and send confirmation email.",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass reset token,password and confirm password",
     *    @OA\JsonContent(
     *       required={"token","password","confirm password"},
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="confirm_password", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Reset password"),
     * @OA\Response(response="401", description="token expire"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function resetPassword(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'password' => 'required|min:5',
                'confirm_password' => 'required|same:password',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                return response()->json($reponse, 401);
            }

            $decryptToken = \Helpers::decryptId($request->token); //decrypt the token
            $tokenArr = explode("_", $decryptToken);
            $emailArr = explode("~", $tokenArr[0]);
            $email = $emailArr[0];
            $utype = $emailArr[1];
            $token = $tokenArr[1];

            // dd($utype);

            $checkToken = PasswordReset::where('email', '=', $email)->where('token', '=', $token)->first();
            if (!empty($checkToken)) {
                $password = $request->password;
                $confPassword = $request->confirm_password;
                $objUser = User::where('email', $email)->first();

                if (!empty($objUser)) { //if user exist
                    if (!(Hash::check($request->input('password'), $objUser->password))) {
                        $objUser->password = $password;
                        $objUser->save();
                        $objReset = PasswordReset::where('token', $token)->delete();

                        $tenant_info = null;
                        if ($objUser->tenant_id > 0) {
                            $tenant_info = Tenant::where('tenant_id', $objUser->tenant_id)->first();
                        }
                        $uProfile = UserProfile::where('user_id', $objUser->user_id)->first();
                        $mailData = array(
                            'email' => $email,
                            'tenant_info' => $tenant_info,
                            'emailHeaderSubject' => 'Your Password Changed',
                        );
                        $usettings = Setting::where('tenant_id', '=', $objUser->tenant_id)->pluck('mail_settings')->first();
                        CommonHelper::setMailConfig($usettings);
                        Mail::send('emails.confirmationPassword', $mailData, function ($message)
                             use ($mailData, $uProfile) {
                                $message->from("info@neon-edu.com", "Neon Edu");
                                $message->to($mailData['email'], $uProfile->first_name)->subject($uProfile->short_name ?? \GlobalVars::ADMIN_NAME . ': ' . $mailData['emailHeaderSubject']);
                            });

                        $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Password changed successfully.'], 'error' => []];
                        return response()->json($reponse, 200);
                    } else {

                        $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'New password should not be same with the old password.']];
                        return response()->json($reponse, 400);
                    }
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Provided email is not valid.']];
                    return response()->json($reponse, 400);
                }
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Reset link expired.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be authenticated.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/reset-password",
     * summary="Set new password after token validation and send confirmation email.",
     * tags={"Authentication"},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *     description="Pass reset token,password and confirm password",
     *    @OA\JsonContent(
     *       required={"token","password","confirm password"},
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="confirm_password", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="Reset password"),
     * @OA\Response(response="401", description="token expire"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function resetPasswordTenant(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'password' => 'required|min:5',
                'confirm_password' => 'required|same:password',
            ]
            );

            //Send failed response if request is not valid
            if ($validator->fails()) {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Validation occured', 'data' => $validator->messages()]];
                return response()->json($reponse, 401);
            }

            $decryptToken = \Helpers::decryptId($request->token); //decrypt the token
            $tokenArr = explode("_", $decryptToken);
            $emailArr = explode("~", $tokenArr[0]);
            $email = $emailArr[0];
            $utype = $emailArr[1];
            $token = $tokenArr[1];

            $checkToken = TenantPasswordReset::where('email', '=', $email)->where('token', '=', $token)->first();
            if (!empty($checkToken)) {
                $password = $request->password;
                $confPassword = $request->confirm_password;
                $objUser = TenantUser::where('email', $email)->first();

                if (!empty($objUser)) { //if user exist
                    if (!(Hash::check($request->input('password'), $objUser->password))) {
                        $objUser->password = $password;
                        $objUser->save();
                        $objReset = TenantPasswordReset::where('token', $token)->delete();
                        $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Password changed successfully.'], 'error' => []];

                        $mailData = array(
                            'email' => $email,
                            'emailHeaderSubject' => 'Your Password Changed',
                        );
                        $usettings = Setting::where('tenant_id', '=', $objUser->tenant_id)->pluck('mail_settings')->first();
                        CommonHelper::setMailConfig($usettings);
                        Mail::send('emails.confirmationPassword', $mailData, function ($message)
                             use ($mailData) {
                                $message->from("info@neon-edu.com", "Neon Edu");
                                $message->to($mailData['email'], "Neon Edu")->subject(\GlobalVars::ADMIN_NAME . ': ' . $mailData['emailHeaderSubject']);
                            });
                        return response()->json($reponse, 200);
                    } else {

                        $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'New password should not be same with the old password.']];
                        return response()->json($reponse, 400);
                    }
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Provided email is not valid.']];
                    return response()->json($reponse, 400);
                }
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Reset link expired.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Sorry, user cannot be authenticated.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }
}
