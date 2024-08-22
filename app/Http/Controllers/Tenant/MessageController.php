<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageUser;
use App\Models\User;
use App\Models\UserYearGroup;
use App\Models\ViewStudent;
use App\Models\ViewTeacher;
use App\Traits\GeneralMethods;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;

class MessageController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/message/create",
     * summary="Create message data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="add new message",
     *    @OA\JsonContent(
     *       required={"message","created_for"},
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="created_for", type="string"),
     *       @OA\Property(property="subject", type="string", default=null),
     *       @OA\Property(property="users", type="array", description="List of users for message",
     *       @OA\Items(
     *          @OA\Property(
     *              property="user_id",
     *              description="user list",
     *              type="int",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant message created successfully."),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createMessage(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $created_for = $request->created_for ?? GlobalVars::MESSAGE_ALL;
                $messageUsers = array();
                if ($created_for == GlobalVars::MESSAGE_ALL_TEACHERS) {
                    $users = ViewTeacher::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($messageUsers, $ele);
                    }

                } elseif ($created_for == GlobalVars::MESSAGE_ALL_STUDENTS) {
                    $users = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($messageUsers, $ele);
                    }
                } elseif ($created_for == GlobalVars::MESSAGE_SPECIFIC_YEARGROUP) {
                    $users = UserYearGroup::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($messageUsers, $ele);
                    }
                } elseif ($created_for == GlobalVars::MESSAGE_ALL) {
                    $users = User::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($messageUsers, $ele);
                    }
                } else {

                    $users = $request->users ?? array();
                    if (count($users) > 0) {
                        foreach ($users as $usr) {
                            $ele = ['user_id' => $usr];
                            array_push($messageUsers, $ele);
                        }
                    }
                }
                // dd($messageUsers);
                if (count($messageUsers) > 0) {
                    $model = new Message;
                    $model->subject = $request->subject;
                    $model->message = $request->message;
                    $model->created_for = $created_for;
                    $model->created_by = $user->user_id;
                    $model->creator_type = $user->user_type;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                    if (count($messageUsers) > 0) {
                        for ($i = 0; $i < count($messageUsers); $i++) {
                            if ($messageUsers[$i]['user_id'] ?? null != '') {
                                $modelSub = new MessageUser;
                                $modelSub->user_id = $messageUsers[$i]['user_id'];
                                $modelSub->message_id = $model->message_id;
                                $modelSub->save();
                            }
                        }
                    }
                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Message sent successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Target audience for message not avaiable.']];
                    return response()->json($reponse, 400);
                }

            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/message/get-user-messages",
     * summary="Create message data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant message created successfully."),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getUserMessages(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $userMessageIds = MessageUser::where('user_id', $user->user_id)->distinct('message_id')->pluck('message_id')->toArray();

                $listing = Message::select('messages.*', DB::raw("(select is_read from message_users where message_users.message_id=messages.message_id and user_id=$user->user_id) as is_read"), DB::raw("(select read_at from message_users where message_users.message_id=messages.message_id and user_id=$user->user_id) as read_at"))
                    ->whereIn('messages.message_id', $userMessageIds)

                    ->orderBy('messages.created_at', 'desc')
                    ->get();

                // dd($listing);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User message list', 'listing' => $listing], 'error' => []];
                return response()->json($reponse, 200);

            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
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
     * path="/api/{subdomain}/message/get-user-message-details",
     * summary="Create message data of tenant as per subdomain",
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
     *     description="get message by id",
     *    @OA\JsonContent(
     *       required={"message_id"},
     *       @OA\Property(property="message_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant message created successfully."),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getUserMessagesDetails(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $message_id = CommonHelper::decryptId($request->message_id);
                if ($message_id == '') {
                    $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No message to fetch']];
                    return response()->json($reponse, 400);
                }
                $getMessageUser = MessageUser::where('user_id', '=', $user->user_id)->where('message_id', '=', $message_id)->first();
                if (!empty($getMessageUser)) {
                    if ($getMessageUser->is_read < 1) {
                        $model = MessageUser::find($getMessageUser->message_user_id);
                        $model->is_read = 1;
                        $model->read_at = now();
                        $model->save();
                    }
                }

                $userMessageIds = MessageUser::where('user_id', $user->user_id)->distinct('message_id')->pluck('message_id')->toArray();

                $details = Message::select('messages.*', DB::raw("(select is_read from message_users where message_users.message_id=messages.message_id and user_id=$user->user_id) as is_read"), DB::raw("(select read_at from message_users where message_users.message_id=messages.message_id and user_id=$user->user_id) as read_at"))
                    ->whereIn('messages.message_id', $userMessageIds)
                    ->where('messages.message_id', $message_id)
                    ->orderBy('messages.created_at', 'desc')
                    ->first();

                // dd($details);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'User message list', 'details' => $details], 'error' => []];
                return response()->json($reponse, 200);

            } else {
                $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'No such tenant exist']];
                return response()->json($reponse, 400);
            }
        } catch (\Exception $e) {
            // throw ($e);
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => $e->getMessage()]];
            return response()->json($reponse, 500);
        }

    }

}
