<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\ExternalUser;
use App\Models\StudyGroup;
use App\Models\StudyGroupContent;
use App\Models\StudyGroupMember;
use App\Models\Tenant;
use App\Models\UserExternalStudyGroup;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Mail;
use \Illuminate\Support\Str;

class StudyGroupController extends Controller
{
    use GeneralMethods;
    /**
     * @OA\Post(
     * path="/api/{subdomain}/study-groups",
     * summary="Get study group master data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="view", type="string", default="all"),
     *       @OA\Property(property="status", type="string", default="all"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant study group master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudyGroups(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // dd($user);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $search_text = $request->search_text;
                $view = $request->view ?? null;
                $status = $request->status ?? null;

                $sgid = StudyGroupMember::where('member_user_id', $user->user_id)->distinct('study_group_id')->pluck('study_group_id')->toArray();

                if ($view == 'external') {
                    $myExternalGroups = UserExternalStudyGroup::where('user_id', $user->user_id)->get();
                    $listing = array();
                    foreach ($myExternalGroups as $group) {
                        $groupInfo = CommonHelper::decryptId($group->group_info);
                        $groupInfo = json_decode($groupInfo);
                        // dd($groupInfo);
                        array_push($listing, $groupInfo);
                    }

                } else {

                    $listing = StudyGroup::with('creator', 'creator.profile')->where(function ($query) use ($search_text, $view, $status, $user) {
                        if ($view != null && $view == 'my') {
                            $query->where('created_by', $user->user_id);
                        }
                        if ($search_text != null) {
                            $query->where('name', 'like', '%' . $search_text . '%');
                        }
                        if ($status != null) {
                            if ($status != 'all') {
                                $query->where('status', $status);
                            }
                        }
                    })->get();

                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'study group Master data fetched successfully.', 'listing' => $listing, 'studygroupsin' => $sgid), 'error' => ''];
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
     * path="/api/{subdomain}/studygroup/create",
     * summary="Create study group master data of tenant as per subdomain",
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
     *     description="add new study group",
     *    @OA\JsonContent(
     *       required={"name"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="group_image", type="string", default=null),
     *    )
     * ),


     * @OA\Response(response="200", description="create tenant study group master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createStudyGroup(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $existingStudyGroup = StudyGroup::Where('name', $request->name)->first();
                if (empty($existingStudyGroup)) {
                    $model = new StudyGroup;
                    $model->name = $request->name;
                    $model->description = $request->description ?? '';
                    $model->created_by = $user->user_id ?? '';
                    if (isset($request->group_image) && $request->group_image != '') {

                        $folderPath_group_image = \GlobalVars::USER_SG_PATH;

                        //image without mime information
                        $imageDataWithoutMime_group_image = explode('base64,', $request->group_image);

                        $file_group_image = $request->group_image;
                        if (isset($imageDataWithoutMime_group_image[1])) {
                            $file_group_image = base64_decode($imageDataWithoutMime_group_image[1]);
                        }
                        if ($file_group_image) {

                            $extension_group_image = 'png';
                            if (isset($imageDataWithoutMime_group_image[1])) {
                                $extension_group_image = explode('/', mime_content_type($request->group_image))[1];
                            }
                            // dd($extension);

                            $image_base64_1_group_image = $file_group_image;
                            $file_group_image1 = $folderPath_group_image . uniqid() . '.' . $extension_group_image;
                            // $file1 = uniqid() . '.' . $extension;

                            Storage::disk('public')->put($file_group_image1, $image_base64_1_group_image);

                            $model->group_image = $file_group_image1;
                        }
                    }
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $study_group_id = $model->study_group_id;

                    $modelSGM = new StudyGroupMember;
                    $modelSGM->study_group_id = $study_group_id;
                    $modelSGM->member_user_id = $user->user_id ?? '';

                    $modelSGM->status = GlobalVars::ACTIVE_STATUS;
                    $modelSGM->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'study group added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'study group name already exist.']];
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
     * path="/api/{subdomain}/get-studygroup-by-id",
     * summary="Get study group details by id",
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
     *     description="Pass encrpted record study_group_id",
     *    @OA\JsonContent(
     *       required={"study_group_id"},
     *       @OA\Property(property="study_group_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="study group details"),
     * @OA\Response(response="401", description="study group not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getStudyGroupById(Request $request)
    {
        try {
            // dd(CommonHelper::encryptId("1"));
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $study_group_id = CommonHelper::decryptId($request->study_group_id);
                $studyGroup = StudyGroup::find($study_group_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'study group details by id', 'details' => $studyGroup], 'error' => []];
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
     * path="/api/{subdomain}/studygroup/update",
     * summary="Update study group master data of tenant as per subdomain",
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
     *     description="update study group",
     *    @OA\JsonContent(
     *       required={"study_group_id","name"},
     *       @OA\Property(property="study_group_id", type="int"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="group_image", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant study group master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateStudyGroup(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $existingStudyGroup = StudyGroup::Where('name', $request->name)->where('study_group_id', '!=', $request->study_group_id)->first();

                if (empty($existingStudyGroup)) {
                    $existingStudyGroup = StudyGroup::where('study_group_id', $request->study_group_id)->first();
                    if (!empty($existingStudyGroup)) {
                        $model = StudyGroup::find($request->study_group_id);
                        $model->name = $request->name;
                        $model->description = $request->description ?? '';
                        // $model->created_by = $user->user_id ?? '';
                        if (isset($request->group_image) && $request->group_image != '') {

                            $folderPath_group_image = \GlobalVars::USER_SG_PATH;

                            //image without mime information
                            $imageDataWithoutMime_group_image = explode('base64,', $request->group_image);

                            $file_group_image = $request->group_image;
                            if (isset($imageDataWithoutMime_group_image[1])) {
                                $file_group_image = base64_decode($imageDataWithoutMime_group_image[1]);
                            }
                            if ($file_group_image) {

                                $extension_group_image = 'png';
                                if (isset($imageDataWithoutMime_group_image[1])) {
                                    $extension_group_image = explode('/', mime_content_type($request->group_image))[1];
                                }
                                // dd($extension);

                                $image_base64_1_group_image = $file_group_image;
                                $file_group_image1 = $folderPath_group_image . uniqid() . '.' . $extension_group_image;
                                // $file1 = uniqid() . '.' . $extension;

                                Storage::disk('public')->put($file_group_image1, $image_base64_1_group_image);
                                if ($model->group_image != '') {
                                    $arrFile = explode('.', $model->group_image);
                                    if (isset($arrFile[1])) {
                                        Storage::disk('public')->delete($model->group_image);
                                    }
                                }
                                $model->group_image = $file_group_image1;
                            }
                        }
                        $model->status = $request->status;
                        $model->save();

                        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'study group updated successfully.'), 'error' => []];
                        return response()->json($reponse, 200);
                    } else {
                        $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'study group  doesnot exist.']];
                        return response()->json($reponse, 400);
                    }
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'study group name already exist.']];
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
     * path="/api/{subdomain}/studygroup/join",
     * summary="Join study group",
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
     *     description="Pass encrpted record study_group_id",
     *    @OA\JsonContent(
     *       required={"study_group_id"},
     *       @OA\Property(property="study_group_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="study group details"),
     * @OA\Response(response="401", description="study group not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function joinStudyGroup(Request $request)
    {
        try {
            // dd(CommonHelper::encryptId("1"));
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $study_group_id = CommonHelper::decryptId($request->study_group_id);
                $studyGroup = StudyGroup::find($study_group_id);

                if (!empty($studyGroup)) {
                    $model = new StudyGroupMember;
                    $model->study_group_id = $study_group_id;
                    $model->member_user_id = $user->user_id ?? '';

                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Study group joined successfully.', 'details' => $studyGroup], 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Unable to join. No such stydy group exist']];
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
     * path="/api/{subdomain}/studygroup/view",
     * summary="View study group details by id",
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
     *     description="Pass encrpted record study_group_id",
     *    @OA\JsonContent(
     *       required={"study_group_id"},
     *       @OA\Property(property="study_group_id", type="int"),
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="study group details"),
     * @OA\Response(response="401", description="study group not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function viewStudyGroup(Request $request)
    {
        try {
            // dd(CommonHelper::encryptId("1"));
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $page = $request->page ?? '1';
                $search_text = $request->search_text ?? null;
                $study_group_id = CommonHelper::decryptId($request->study_group_id);
                $studyGroup = StudyGroup::with(['creator', 'creator.profile', 'internal_members', 'external_members'])->find($study_group_id);

                $content_list = StudyGroupContent::where('study_group_id', $study_group_id)->with('contentowner', 'contentowner.memberinfo', 'contentowner.memberinfo.profile')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'study group details by id', 'details' => $studyGroup, 'content_list' => $content_list], 'error' => []];
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
     * path="/api/{subdomain}/studygroup/add-content",
     * summary="Add study group content data of tenant as per subdomain",
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
     *     description="add new study group content",
     *    @OA\JsonContent(
     *       required={"study_group_id","content"},
     *       @OA\Property(property="study_group_id", type="int"),
     *       @OA\Property(property="content", type="string"),
     *    )
     * ),


     * @OA\Response(response="200", description="created tenant study group content"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function addContentStudyGroup(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $study_group_id = CommonHelper::decryptId($request->study_group_id);
                $study_group_member = StudyGroupMember::where('member_user_id', $user->user_id)->where('study_group_id', $study_group_id)->first();
                if (!empty($study_group_member)) {
                    $model = new StudyGroupContent;
                    $model->study_group_id = $study_group_id;
                    $model->study_group_member_id = $study_group_member->study_group_member_id;
                    $model->content = $request->content ?? '';

                    $model->save();

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'study group content added successfully.'), 'error' => []];
                    return response()->json($reponse, 200);

                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such study group member exist']];
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
     * path="/api/{subdomain}/studygroup/invite",
     * summary="Invite to study group of tenant as per subdomain",
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
     *     description="Invite to study group",
     *    @OA\JsonContent(
     *       required={"study_group_id","name","email"},
     *       @OA\Property(property="study_group_id", type="int"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *    )
     * ),


     * @OA\Response(response="200", description="Invitation sent successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function inviteToStudyGroup(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            // dd($user);
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $validator = Validator::make($request->all(), [
                    'study_group_id' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                ]
                );

                //Send failed response if request is not valid
                if ($validator->fails()) {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => $validator->messages()->first(), 'data' => $validator->messages()]];
                    return response()->json($reponse, 400);
                }

                $tenant_info = [];
                if ($user->tenant_id > 0) {
                    $tenant_info = Tenant::find($user->tenant_id);
                }

                $study_group_id = CommonHelper::decryptId($request->study_group_id);
                //check existance of study group
                $studyGroup = StudyGroup::find($study_group_id);

                if (!empty($studyGroup)) {
                    //check for previous invitation
                    $previousInvitation = ExternalUser::where('entity_id', $request->study_group_id)
                        ->where('invited_for', GlobalVars::INVITATION_SG)
                        ->where('name', 'like', '%' . $request->name . '%')
                        ->where('email', 'like', '%' . $request->email . '%')
                        ->first();

                    if (empty($previousInvitation)) {
                        //create group info and encrypt

                        $groupInfo = [
                            'study_group_id' => $request->study_group_id,
                            'name' => $studyGroup->name,
                            'description' => $studyGroup->description,
                            'group_image' => $studyGroup->group_image,
                            'subdomain' => CommonHelper::encryptId($tenant_info->subdomain ?? ''),
                            'tenant_id' => CommonHelper::encryptId($user->tenant_id ?? ''),
                        ];

                        $groupInfo = json_encode($groupInfo);
                        $invite_token = CommonHelper::encryptId($groupInfo);
                        $token = uniqid(mt_rand(), true);

                        $token = $request->email . '^' . ($tenant_info->subdomain ?? '') . '_' . $token;

                        $token = \Helpers::encryptId($token);
                        // dd($token);
                        //create invitation and send email to invitee
                        $model = new ExternalUser;
                        $model->email = $request->email;
                        $model->name = $request->name;
                        $model->invited_for = GlobalVars::INVITATION_SG;
                        $model->entity_id = $request->study_group_id ?? '';
                        $model->invited_by = $user->user_id ?? '';
                        $model->invite_token = $invite_token;
                        $model->token = $token;
                        $model->status = 'Invitation';

                        $model->save();

                        //send invitation email
                        $senderName = $user->profile->first_name ?? '' . ' ' . $user->profile->last_name ?? '';
                        $messageBody = 'You have been invited by <b>' . $senderName . '</b>, to join the study group namely, <b>' . $studyGroup->name . '</b>.<br>Click on <b>View Invitation</b> button below, to take necessary action of your choice.';

                        // dd($message);

                        $url = config('app.frontend_base_url') . '/invitation/' . $token;

                        // dd($url);
                        $mailData = array(
                            'email' => $request->email,
                            'name' => $request->name,
                            'url' => $url,
                            'mailbody' => $messageBody,
                            'tenant_info' => $tenant_info,
                            'emailHeaderSubject' => 'Study group invitation',
                        );

                        Mail::send('emails.invitation', $mailData, function ($message)
                             use ($mailData) {
                                $message->from("info@neon-edu.com", "Neon Edu");
                                $message->to($mailData['email'], 'Hello World')->subject($mailData['emailHeaderSubject']);
                            });

                        $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Invitation sent successfully.'), 'error' => []];
                        return response()->json($reponse, 200);
                    } else {
                        if ($previousInvitation->status == GlobalVars::ACTIVE_STATUS) {
                            $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invitee is already a member of the concerned group.']];
                            return response()->json($reponse, 400);
                        } else {
                            $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invitation already sent.']];
                            return response()->json($reponse, 400);
                        }
                    }

                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No such study group exist']];
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
