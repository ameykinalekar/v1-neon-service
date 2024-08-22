<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Tenant;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JWTAuth;

class SettingController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/pa/settings",
     * summary="Get portal admin settings",
     * description="Gey portal admin settings passing portal admin token",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response="200", description="settings list"),
     * @OA\Response(response="401", description="settings not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getPaSettingList()
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }

            $settingList = Setting::where('tenant_id', '=', '0')->first();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Setting List', 'settings' => $settingList], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/pa/set-settings",
     * summary="Create/Update portal admin settings",
     * description="Create/Update portal admin settings passing portal admin token",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="update setting information",
     *    @OA\JsonContent(
     *       @OA\Property(property="setting_id", type="int", default=0),
     *       @OA\Property(property="tenant_id", type="int", default=0),
     *       @OA\Property(property="system_title", type="string", default=null),
     *       @OA\Property(property="system_email", type="string", default=null),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="footer_text", type="string", default=null),
     *       @OA\Property(property="footer_link", type="string", default=null),
     *       @OA\Property(property="mail_settings", type="string", default=null),
     *       @OA\Property(property="favicon", type="int", default=null),
     *       @OA\Property(property="main_logo", type="string", default=null),
     *     )
     * ),
     * @OA\Response(response="200", description="PA setting updated/created"),
     * @OA\Response(response="401", description="PA setting not updated/created"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createOrUpdatePortalAdminSettings(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $user = JWTAuth::parseToken()->authenticate();

            if (isset($request->setting_id) && $request->setting_id != null) {
                //update settings
                $model = Setting::find($request->setting_id);
                $model->tenant_id = $user->tenant_id;
                $model->system_title = $request->system_title;
                $model->system_email = $request->system_email;
                $model->phone = $request->phone;
                $model->address = $request->address;
                $model->footer_text = $request->footer_text;
                $model->footer_link = $request->footer_link;
                $model->mail_settings = $request->mail_settings;

                if (isset($request->favicon) && $request->favicon != '') {

                    $folderPath_favicon = \GlobalVars::PORTAL_FAVICON_PATH;

                    //image without mime information
                    $imageDataWithoutMime_favicon = explode('base64,', $request->favicon);

                    $file_favicon = $request->favicon;
                    if (isset($imageDataWithoutMime_favicon[1])) {
                        $file_favicon = base64_decode($imageDataWithoutMime_favicon[1]);
                    }
                    if ($file_favicon) {

                        $extension_favicon = 'png';
                        if (isset($imageDataWithoutMime_favicon[1])) {
                            $extension_favicon = explode('/', mime_content_type($request->favicon))[1];
                        }
                        // dd($extension);

                        $image_base64_1_favicon = $file_favicon;
                        $file_favicon1 = $folderPath_favicon . uniqid() . '.' . $extension_favicon;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_favicon1, $image_base64_1_favicon);

                        if ($model->favicon != '') {
                            $arrFile = explode('.', $model->favicon);
                            if (isset($arrFile[1])) {
                                Storage::disk('public')->delete($model->favicon);
                            }
                        }

                        $model->favicon = $file_favicon1;
                    }
                }

                if (isset($request->main_logo) && $request->main_logo != '') {

                    $folderPath_main_logo = \GlobalVars::PORTAL_FAVICON_PATH;

                    //image without mime information
                    $imageDataWithoutMime_main_logo = explode('base64,', $request->main_logo);

                    $file_main_logo = $request->main_logo;
                    if (isset($imageDataWithoutMime_main_logo[1])) {
                        $file_main_logo = base64_decode($imageDataWithoutMime_main_logo[1]);
                    }
                    if ($file_main_logo) {

                        $extension_main_logo = 'png';
                        if (isset($imageDataWithoutMime_main_logo[1])) {
                            $extension_main_logo = explode('/', mime_content_type($request->main_logo))[1];
                        }
                        // dd($extension);

                        $image_base64_1_main_logo = $file_main_logo;
                        $file_main_logo1 = $folderPath_main_logo . uniqid() . '.' . $extension_main_logo;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_main_logo1, $image_base64_1_main_logo);

                        if ($model->main_logo != '') {
                            $arrFile = explode('.', $model->main_logo);
                            if (isset($arrFile[1])) {
                                Storage::disk('public')->delete($model->main_logo);
                            }
                        }

                        $model->main_logo = $file_main_logo1;
                    }
                }

                $model->save();
            } else {
                //create settings
                $model = new Setting;
                $model->tenant_id = $user->tenant_id;
                $model->system_title = $request->system_title;
                $model->system_email = $request->system_email;
                $model->phone = $request->phone;
                $model->address = $request->address;
                $model->footer_text = $request->footer_text;
                $model->footer_link = $request->footer_link;

                if (isset($request->favicon) && $request->favicon != '') {

                    $folderPath_favicon = \GlobalVars::PORTAL_FAVICON_PATH;

                    //image without mime information
                    $imageDataWithoutMime_favicon = explode('base64,', $request->favicon);

                    $file_favicon = $request->favicon;
                    if (isset($imageDataWithoutMime_favicon[1])) {
                        $file_favicon = base64_decode($imageDataWithoutMime_favicon[1]);
                    }
                    if ($file_favicon) {

                        $extension_favicon = 'png';
                        if (isset($imageDataWithoutMime_favicon[1])) {
                            $extension_favicon = explode('/', mime_content_type($request->favicon))[1];
                        }
                        // dd($extension);

                        $image_base64_1_favicon = $file_favicon;
                        $file_favicon1 = $folderPath_favicon . uniqid() . '.' . $extension_favicon;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_favicon1, $image_base64_1_favicon);

                        $model->favicon = $file_favicon1;
                    }
                }

                if (isset($request->main_logo) && $request->main_logo != '') {

                    $folderPath_main_logo = \GlobalVars::PORTAL_FAVICON_PATH;

                    //image without mime information
                    $imageDataWithoutMime_main_logo = explode('base64,', $request->main_logo);

                    $file_main_logo = $request->main_logo;
                    if (isset($imageDataWithoutMime_main_logo[1])) {
                        $file_main_logo = base64_decode($imageDataWithoutMime_main_logo[1]);
                    }
                    if ($file_main_logo) {

                        $extension_main_logo = 'png';
                        if (isset($imageDataWithoutMime_main_logo[1])) {
                            $extension_main_logo = explode('/', mime_content_type($request->main_logo))[1];
                        }
                        // dd($extension);

                        $image_base64_1_main_logo = $file_main_logo;
                        $file_main_logo1 = $folderPath_main_logo . uniqid() . '.' . $extension_main_logo;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_main_logo1, $image_base64_1_main_logo);

                        $model->main_logo = $file_main_logo1;
                    }
                }

                $model->save();
            }

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'System Settings updated.', 'settings' => $model], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/settings",
     * summary="Get tenant admin settings",
     * description="Get tenant admin settings passing user token",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="settings list"),
     * @OA\Response(response="401", description="settings not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaSettingList()
    {
        try {
            if (!$this->invalidateTenantUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $user = JWTAuth::parseToken()->authenticate();
            $settingList = Setting::where('tenant_id', '=', $user->tenant_id)->first();
            $tenantInfo = Tenant::find($user->tenant_id);

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Setting List', 'settings' => $settingList, 'tenantInfo' => $tenantInfo], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/{subdomain}/set-settings",
     * summary="Create/Update tenant admin settings",
     * description="Create/Update tenant admin settings passing portal admin token",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),

     * @OA\RequestBody(
     *    required=true,
     *    description="update setting information",
     *    @OA\JsonContent(
     *       @OA\Property(property="setting_id", type="int", default=0),
     *       @OA\Property(property="tenant_id", type="int", default=0),
     *       @OA\Property(property="system_title", type="string", default=null),
     *       @OA\Property(property="system_email", type="string", default=null),
     *       @OA\Property(property="phone", type="string", default=null),
     *       @OA\Property(property="address", type="string", default=null),
     *       @OA\Property(property="footer_text", type="string", default=null),
     *       @OA\Property(property="footer_link", type="string", default=null),
     *       @OA\Property(property="mail_settings", type="string", default=null),
     *       @OA\Property(property="favicon", type="int", default=null),
     *       @OA\Property(property="logo", type="string", default=null),
     *       @OA\Property(property="background_image", type="string", default=null),
     *     )
     * ),
     * @OA\Response(response="200", description="Setting updated/created"),
     * @OA\Response(response="401", description="Setting not updated/created"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createOrUpdateTenantAdminSettings(Request $request)
    {
        try {
            if (!$this->invalidateTenantUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $user = JWTAuth::parseToken()->authenticate();

            if (isset($request->setting_id) && $request->setting_id != null) {
                //update settings
                $model = Setting::find($request->setting_id);
                $model->tenant_id = $user->tenant_id;
                $model->system_title = $request->system_title;
                $model->system_email = $request->system_email;
                $model->phone = $request->phone;
                $model->address = $request->address;
                $model->footer_text = $request->footer_text;
                $model->footer_link = $request->footer_link;
                $model->mail_settings = $request->mail_settings;

                if (isset($request->favicon) && $request->favicon != '') {

                    $folderPath_favicon = \GlobalVars::PORTAL_FAVICON_PATH;

                    //image without mime information
                    $imageDataWithoutMime_favicon = explode('base64,', $request->favicon);

                    $file_favicon = $request->favicon;
                    if (isset($imageDataWithoutMime_favicon[1])) {
                        $file_favicon = base64_decode($imageDataWithoutMime_favicon[1]);
                    }
                    if ($file_favicon) {

                        $extension_favicon = 'png';
                        if (isset($imageDataWithoutMime_favicon[1])) {
                            $extension_favicon = explode('/', mime_content_type($request->favicon))[1];
                        }
                        // dd($extension);

                        $image_base64_1_favicon = $file_favicon;
                        $file_favicon1 = $folderPath_favicon . uniqid() . '.' . $extension_favicon;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_favicon1, $image_base64_1_favicon);

                        if ($model->favicon != '') {
                            $arrFile = explode('.', $model->favicon);
                            if (isset($arrFile[1])) {
                                Storage::disk('public')->delete($model->favicon);
                            }
                        }

                        $model->favicon = $file_favicon1;
                    }
                }

                $model->save();

            } else {
                //create settings
                $model = new Setting;
                $model->tenant_id = $user->tenant_id;
                $model->system_title = $request->system_title;
                $model->system_email = $request->system_email;
                $model->phone = $request->phone;
                $model->address = $request->address;
                $model->footer_text = $request->footer_text;
                $model->footer_link = $request->footer_link;

                if (isset($request->favicon) && $request->favicon != '') {

                    $folderPath_favicon = \GlobalVars::PORTAL_FAVICON_PATH;

                    //image without mime information
                    $imageDataWithoutMime_favicon = explode('base64,', $request->favicon);

                    $file_favicon = $request->favicon;
                    if (isset($imageDataWithoutMime_favicon[1])) {
                        $file_favicon = base64_decode($imageDataWithoutMime_favicon[1]);
                    }
                    if ($file_favicon) {

                        $extension_favicon = 'png';
                        if (isset($imageDataWithoutMime_favicon[1])) {
                            $extension_favicon = explode('/', mime_content_type($request->favicon))[1];
                        }
                        // dd($extension);

                        $image_base64_1_favicon = $file_favicon;
                        $file_favicon1 = $folderPath_favicon . uniqid() . '.' . $extension_favicon;
                        // $file1 = uniqid() . '.' . $extension;

                        Storage::disk('public')->put($file_favicon1, $image_base64_1_favicon);

                        $model->favicon = $file_favicon1;
                    }
                }

                $model->save();
            }

            $tenantModel = Tenant::find($user->tenant_id);
            $tenantModel->theme_color = $request->theme_color ?? '';

            if (isset($request->logo) && $request->logo != '') {

                $folderPath_logo = \GlobalVars::PORTAL_FAVICON_PATH;

                //image without mime information
                $imageDataWithoutMime_logo = explode('base64,', $request->logo);

                $file_logo = $request->logo;
                if (isset($imageDataWithoutMime_logo[1])) {
                    $file_logo = base64_decode($imageDataWithoutMime_logo[1]);
                }
                if ($file_logo) {

                    $extension_logo = 'png';
                    if (isset($imageDataWithoutMime_logo[1])) {
                        $extension_logo = explode('/', mime_content_type($request->logo))[1];
                    }
                    // dd($extension);

                    $image_base64_1_logo = $file_logo;
                    $file_logo1 = $folderPath_logo . uniqid() . '.' . $extension_logo;
                    // $file1 = uniqid() . '.' . $extension;

                    Storage::disk('public')->put($file_logo1, $image_base64_1_logo);
                    if ($tenantModel->logo != '') {
                        $arrFile = explode('.', $tenantModel->logo);
                        if (isset($arrFile[1])) {
                            Storage::disk('public')->delete($tenantModel->logo);
                        }
                    }
                    $tenantModel->logo = $file_logo1;
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

                    if ($tenantModel->background_image != '') {
                        $arrFile = explode('.', $tenantModel->background_image);
                        if (isset($arrFile[1])) {
                            Storage::disk('public')->delete($tenantModel->background_image);
                        }
                    }
                    $tenantModel->background_image = $file_bg1;
                }
            }
            $tenantModel->save();

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'System Settings updated.', 'settings' => $model], 'error' => []];
            return response()->json($reponse, 200);
        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

}
