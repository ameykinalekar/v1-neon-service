<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAllocation;
use App\Models\TaskExamination;
use App\Models\UserYearGroup;
use App\Models\ViewStudent;
use App\Models\ViewTeacher;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;

class TaskController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/task/create",
     * summary="Create task data of tenant as per subdomain",
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
     *     description="add new task",
     *    @OA\JsonContent(
     *       required={"start_date","end_date","task_type"},
     *       @OA\Property(property="start_date", type="date"),
     *       @OA\Property(property="end_date", type="date"),
     *       @OA\Property(property="task_type", type="string"),
     *       @OA\Property(property="created_for", type="string"),
     *       @OA\Property(property="task", type="string",default=null),
     *       @OA\Property(property="description", type="string",default=null),
     *       @OA\Property(property="users", type="array", description="List of users",
     *       @OA\Items(
     *          @OA\Property(
     *              property="user_id",
     *              description="user_id",
     *              type="int",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="examinations", type="array", description="List of examination",
     *       @OA\Items(
     *          @OA\Property(
     *              property="examination_id",
     *              description="examination_id",
     *              type="int",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant task"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createTask(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $created_for = $request->created_for ?? GlobalVars::MESSAGE_ALL;
                $taskUsers = array();
                if ($created_for == GlobalVars::MESSAGE_ALL_TEACHERS) {
                    $users = ViewTeacher::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }

                } elseif ($created_for == GlobalVars::MESSAGE_ALL_STUDENTS) {
                    $users = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }
                } elseif ($created_for == GlobalVars::MESSAGE_SPECIFIC_YEARGROUP) {
                    $users = UserYearGroup::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }
                } elseif ($created_for == GlobalVars::MESSAGE_ALL) {
                    $users = User::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }
                } else {
                    $users = $request->users ?? array();
                    if (count($users) > 0) {
                        foreach ($users as $usr) {
                            $ele = ['user_id' => $usr];
                            array_push($taskUsers, $ele);
                        }
                    }
                }

                // dd($taskUsers);
                $taskExaminations = array();
                $exams = $request->examinations ?? array();
                if (count($exams) > 0) {
                    foreach ($exams as $ex) {
                        $ele = ['examination_id' => $ex];
                        array_push($taskExaminations, $ele);
                    }
                }

                $model = new Task;
                $model->start_date = $request->start_date;
                $model->end_date = $request->end_date;
                $model->task_type = $request->task_type;
                $model->created_for = $created_for;
                $model->task = $request->task;
                $model->description = $request->description;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->save();

                if (count($taskUsers) > 0) {
                    for ($i = 0; $i < count($taskUsers); $i++) {
                        if ($taskUsers[$i]['user_id'] ?? null != '') {
                            $modelA = new TaskAllocation;
                            $modelA->task_id = $model->task_id;
                            $modelA->user_id = $taskUsers[$i]['user_id'];
                            $modelA->save();
                        }
                    }
                }

                if (count($taskExaminations) > 0) {
                    for ($i = 0; $i < count($taskExaminations); $i++) {
                        if ($taskExaminations[$i]['examination_id'] ?? null != '') {
                            $modelE = new TaskExamination;
                            $modelE->task_id = $model->task_id;
                            $modelE->examination_id = $taskExaminations[$i]['examination_id'];
                            $modelE->save();
                        }
                    }
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task scheduled successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/task/update",
     * summary="Update task data of tenant as per subdomain",
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
     *     description="update task",
     *    @OA\JsonContent(
     *       required={"task_id","start_date","end_date","task_type"},
     *       @OA\Property(property="task_id", type="int"),
     *       @OA\Property(property="start_date", type="date"),
     *       @OA\Property(property="end_date", type="date"),
     *       @OA\Property(property="task_type", type="string"),
     *       @OA\Property(property="created_for", type="string"),
     *       @OA\Property(property="task", type="string",default=null),
     *       @OA\Property(property="description", type="string",default=null),
     *       @OA\Property(property="users", type="array", description="List of users",
     *       @OA\Items(
     *          @OA\Property(
     *              property="user_id",
     *              description="user_id",
     *              type="int",
     *              nullable="true"
     *          ),
     *       )
     *      ),
     *       @OA\Property(property="examinations", type="array", description="List of examination",
     *       @OA\Items(
     *          @OA\Property(
     *              property="examination_id",
     *              description="examination_id",
     *              type="int",
     *              nullable="true"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant task"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateTask(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $created_for = $request->created_for ?? GlobalVars::MESSAGE_ALL;
                $taskUsers = array();
                if ($created_for == GlobalVars::MESSAGE_ALL_TEACHERS) {
                    $users = ViewTeacher::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }

                } elseif ($created_for == GlobalVars::MESSAGE_ALL_STUDENTS) {
                    $users = ViewStudent::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }
                } elseif ($created_for == GlobalVars::MESSAGE_SPECIFIC_YEARGROUP) {
                    $users = UserYearGroup::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }
                } elseif ($created_for == GlobalVars::MESSAGE_ALL) {
                    $users = User::where('status', GlobalVars::ACTIVE_STATUS)->pluck('user_id');

                    foreach ($users as $usr) {
                        $ele = ['user_id' => $usr];
                        array_push($taskUsers, $ele);
                    }
                } else {
                    $users = $request->users ?? array();
                    if (count($users) > 0) {
                        foreach ($users as $usr) {
                            $ele = ['user_id' => $usr];
                            array_push($taskUsers, $ele);
                        }
                    }
                }

                // dd($taskUsers);
                $taskExaminations = array();
                $exams = $request->examinations ?? array();
                if (count($exams) > 0) {
                    foreach ($exams as $ex) {
                        $ele = ['examination_id' => $ex];
                        array_push($taskExaminations, $ele);
                    }
                }

                $model = Task::find($request->task_id);
                $model->start_date = $request->start_date;
                $model->end_date = $request->end_date;
                $model->task_type = $request->task_type;
                $model->created_for = $created_for;
                $model->task = $request->task;
                $model->description = $request->description;
                $model->created_by = $user->user_id;
                $model->creator_type = $user->user_type;
                $model->save();

                if (count($taskUsers) > 0) {
                    TaskAllocation::where('task_id', $model->task_id)->delete();
                    for ($i = 0; $i < count($taskUsers); $i++) {
                        if ($taskUsers[$i]['user_id'] ?? null != '') {
                            $modelA = new TaskAllocation;
                            $modelA->task_id = $model->task_id;
                            $modelA->user_id = $taskUsers[$i]['user_id'];
                            $modelA->save();
                        }
                    }
                }

                if (count($taskExaminations) > 0) {
                    TaskExamination::where('task_id', $model->task_id)->delete();
                    for ($i = 0; $i < count($taskExaminations); $i++) {
                        if ($taskExaminations[$i]['examination_id'] ?? null != '') {
                            $modelE = new TaskExamination;
                            $modelE->task_id = $model->task_id;
                            $modelE->examination_id = $taskExaminations[$i]['examination_id'];
                            $modelE->save();
                        }
                    }
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task updated successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/task/delete",
     * summary="Delete task data of tenant as per subdomain",
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
     *     description="delete task",
     *    @OA\JsonContent(
     *       required={"task_id"},
     *       @OA\Property(property="task_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="delete tenant task"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function deleteTask(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $task_id = CommonHelper::decryptId($request->task_id);

                TaskAllocation::where('task_id', $task_id)->delete();
                TaskExamination::where('task_id', $task_id)->delete();
                Task::where('task_id', $task_id)->delete();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task deleted successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/task/get/async",
     * summary="Get task data of tenant as per subdomain",
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
     *     description="get task",
     *    @OA\JsonContent(
     *       required={"start_date","end_date"},
     *       @OA\Property(property="start_date", type="date"),
     *       @OA\Property(property="end_date", type="date"),
     *    )
     * ),

     * @OA\Response(response="200", description="Get tenant task"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaskAsync(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $userTask = TaskAllocation::where('user_id', $user->user_id)->pluck('task_id')->toArray();

                $listing = Task::where('start_date', '>=', $request->start_date)
                    ->where('end_date', '<=', $request->end_date)
                    ->whereIn('task_id', $userTask)
                    ->orWhere('created_by', $user->user_id)
                    ->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/task/get-by-id",
     * summary="Get task data of tenant as per subdomain",
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
     *     description="get task details",
     *    @OA\JsonContent(
     *       required={"task_id"},
     *       @OA\Property(property="task_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="Get tenant task details"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaskById(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $userTask = TaskAllocation::where('user_id', $user->user_id)->pluck('task_id')->toArray();

                $details = Task::with('exams', 'exams.details', 'allocations')
                    ->where('task_id', $request->task_id)
                // ->whereIn('task_id', $userTask)
                    ->first();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task list fetched successfully.', 'details' => $details), 'error' => ''];
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
     * path="/api/{subdomain}/task/get/homework-created",
     * summary="Get task data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="Get tenant task"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaskHwCreated(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $userTask = TaskExamination::join('examinations', 'examinations.examination_id', 'task_examinations.examination_id', 'INNER')->where('examinations.homework', 1)->pluck('task_id')->toArray();

                $listing = Task::with('exams', 'exams.details')
                    ->whereIn('task_id', $userTask)
                    ->where('created_by', $user->user_id)
                    ->where('task_type', 'H')
                    ->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/task/get/consumers",
     * summary="Get task data of tenant as per subdomain",
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
     *     description="get task details",
     *    @OA\JsonContent(
     *       required={"task_id"},
     *       @OA\Property(property="task_id", type="int"),
     *    )
     * ),

     * @OA\Response(response="200", description="Get tenant task details"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaskConsumers(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                // $userTask = TaskAllocation::where('user_id', $user->user_id)->pluck('task_id')->toArray();

                $details = Task::with('exams', 'exams.details', 'allocations', 'allocations.students')
                    ->where('task_id', $request->task_id)
                // ->whereIn('task_id', $userTask)
                    ->first();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task list fetched successfully.', 'details' => $details), 'error' => ''];
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
     * path="/api/{subdomain}/task/get/consumer/homework",
     * summary="Get homework task data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),

     * @OA\Response(response="200", description="Get tenant task details"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getTaskConsumerHomework(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $userTask = TaskAllocation::where('user_id', $user->user_id)->pluck('task_id')->toArray();

                $listing = Task::with('exams', 'exams.details')
                    ->whereIn('task_id', $userTask)
                    ->where('task_type', 'H')
                    ->get();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Task list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
