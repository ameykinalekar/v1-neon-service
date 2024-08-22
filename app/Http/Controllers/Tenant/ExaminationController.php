<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Imports\QuizQuestionImport;
use App\Traits\GeneralMethods;
use App\Traits\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;

class ExaminationController extends Controller
{
    use GeneralMethods, QuestionBank;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/question/categories",
     * summary="Get question categories master data of tenant as per subdomain",
     * tags={"Tenant"},
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
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant question categories master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getQuestionCategories(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $listing = $this->qb_questionCategoriesPaginated($request);
                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Paginated list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/question/category/create",
     * summary="Create question category master data of tenant as per subdomain",
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
     *     description="add new question category",
     *    @OA\JsonContent(
     *       required={"category_name"},
     *       @OA\Property(property="category_name", type="string"),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant question category master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createQuestionCategory(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // dd($request->all());
                $model = $this->qb_createQuestionCategory($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Question Category added successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/question/category/get-by-id",
     * summary="Get question category details by question_category_id",
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
     *     description="Pass encrpted record question_category_id",
     *    @OA\JsonContent(
     *       required={"question_category_id"},
     *       @OA\Property(property="question_category_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="question category details"),
     * @OA\Response(response="401", description="question category not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getQuestionCategoryById(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $question_category_id = CommonHelper::decryptId($request->question_category_id);

                $details = $this->qb_getQuestionCategoryById($question_category_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Question category details by id', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/question/category/update",
     * summary="Update question category master data of tenant as per subdomain",
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
     *     description="update question category",
     *    @OA\JsonContent(
     *       required={"question_category_id","category_name"},
     *       @OA\Property(property="question_category_id", type="int"),
     *       @OA\Property(property="category_name", type="string"),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="update tenant question category master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateQuestionCategory(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $model = $this->qb_updateQuestionCategory($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Question Category updated successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/dropdown/question/categories",
     * summary="Get question category list for dropdowns passing tenant token as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant question category master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function dropdownQuestionCategoryArray()
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $arrayList = $this->qb_questionCategories();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Question Category Master list fetched successfully.', 'qc_array_list' => $arrayList), 'error' => ''];
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
     * path="/api/{subdomain}/question/create",
     * summary="Create question master data of tenant as per subdomain",
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
     *     description="add new question",
     *    @OA\JsonContent(
     *       required={"question_type","year_group_id","subject_id","question","level","created_by"},
     *       @OA\Property(property="question_type", type="string"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int", default=null),
     *       @OA\Property(property="question_category_id", type="int", default=null),
     *       @OA\Property(property="question", type="string"),
     *       @OA\Property(property="level", type="string", default="low"),
     *       @OA\Property(property="require_file_upload", type="int", default=null),
     *       @OA\Property(property="source", type="string", default="Q"),
     *       @OA\Property(property="created_by", type="int", default=null),
     *       @OA\Property(property="creator_type", type="string", default=null),
     *       @OA\Property(property="options", type="array", description="List of answer options",
     *       @OA\Items(
     *          @OA\Property(
     *              property="option_value",
     *              description="Answer text",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="is_correct",
     *              description="mark 1 if correct option",
     *              type="int",
     *              nullable="false"
     *          ),
     *       )
     *      )
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant question master"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createQuestion(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // dd($request->all());
                $validator = Validator::make($request->all(), [
                    'question_type' => 'required',
                    'year_group_id' => 'required',
                    'subject_id' => 'required',
                    'question' => 'required',
                    'level' => 'required',
                ]
                );

                //Send failed response if request is not valid
                if ($validator->fails()) {
                    $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => $validator->messages()->first(), 'data' => $validator->messages()]];
                    return response()->json($reponse, 401);
                }
                $requestFiltered = $request->only(
                    'question_type',
                    'year_group_id',
                    'subject_id',
                    'lesson_id',
                    'question_category_id',
                    'question',
                    'level',
                    'require_file_upload',
                    'source',
                    'created_by',
                    'creator_type',
                    'options'
                );
                $requestFiltered = new \Illuminate\Http\Request($requestFiltered);
                $model = $this->qb_createQuestion($requestFiltered);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Question added successfully.', 'question' => $model, 'options' => $model->options), 'error' => []];
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
     * path="/api/{subdomain}/examination/create",
     * summary="Create examination data of tenant (teacher) as per subdomain",
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
     *     description="add new examination",
     *    @OA\JsonContent(
     *       required={"examination_type","name"},
     *       @OA\Property(property="examination_type", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="year_group_id", type="int", default=null),
     *       @OA\Property(property="subject_id", type="int", default=null),
     *       @OA\Property(property="lesson_id", type="int", default=null),
     *       @OA\Property(property="homework", type="int", default=0),
     *    )
     * ),
     * @OA\Response(response="200", description="Examination added successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createExamination(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();

            $request->request->set('created_by', $user->user_id);
            $request->request->set('creator_type', $user->user_type . '-' . $user->role);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // dd($request->all());
                $model = $this->qb_createExam($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Examination added successfully.', 'examination' => $model), 'error' => []];

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
     * path="/api/{subdomain}/examination/create-with-questions",
     * summary="Create examination data of tenant (teacher) as per subdomain",
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
     *     description="add new examination",
     *    @OA\JsonContent(
     *       required={"examination_type","name"},
     *       @OA\Property(property="examination_type", type="string", default="A"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="homework", type="int",default=0),
     *       @OA\Property(property="examination_status", type="string", default=null),
     *       @OA\Property(property="questions", type="array", description="List of question options",
     *       @OA\Items(
     *          @OA\Property(
     *              property="question",
     *              description="Question text",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="question_type",
     *              description="question_type",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="level",
     *              description="level",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="require_file_upload",
     *              description="require consumer to upload file",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="point",
     *              description="question marks",
     *              type="int",
     *              nullable="false"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="Examination added successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */

    public function createExaminationWithQuestions(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();

            $request->request->set('created_by', $user->user_id);
            $request->request->set('creator_type', $user->user_type . '-' . $user->role);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // dd($request->all());
                $model = $this->qb_createExamWithQuestions($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Examination added/updated successfully.', 'examination' => $model), 'error' => []];

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
     * path="/api/{subdomain}/examination/create-with-questions-new",
     * summary="Create examination data of tenant (teacher) as per subdomain",
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
     *     description="add new examination",
     *    @OA\JsonContent(
     *       required={"examination_type","name"},
     *       @OA\Property(property="examination_type", type="string", default="A"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="homework", type="int", default=0),
     *       @OA\Property(property="examination_status", type="string", default=null),
     *       @OA\Property(property="questions", type="array", description="List of question options",
     *       @OA\Items(
     *          @OA\Property(
     *              property="question",
     *              description="Question text",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="question_type",
     *              description="question_type",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="level",
     *              description="level",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="require_file_upload",
     *              description="require consumer to upload file",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="point",
     *              description="question marks",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(property="subquestions", type="array", description="List of sub question options",
     *              @OA\Items(
     *                  @OA\Property(
     *                      property="question",
     *                      description="Question text",
     *                      type="string",
     *                      nullable="false"
     *                  ),
     *                  @OA\Property(
     *                      property="question_type",
     *                      description="question_type",
     *                      type="string",
     *                      nullable="false"
     *                  ),
     *                  @OA\Property(
     *                      property="level",
     *                      description="level",
     *                      type="string",
     *                      nullable="false"
     *                  ),
     *                  @OA\Property(
     *                      property="require_file_upload",
     *                      description="require consumer to upload file",
     *                      type="int",
     *                      nullable="false"
     *                  ),
     *                  @OA\Property(
     *                      property="point",
     *                      description="question marks",
     *                      type="int",
     *                      nullable="false"
     *                  ),
     *              )
     *          )
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="Examination added successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */

    public function createExaminationWithQuestionsNew(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();

            $request->request->set('created_by', $user->user_id);
            $request->request->set('creator_type', $user->user_type . '-' . $user->role);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                // dd($request->all());
                $model = $this->qb_createExamWithQuestionsNSubquestions($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Examination added/updated successfully.', 'examination' => $model), 'error' => []];

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
     * path="/api/{subdomain}/examination/creator-examinations",
     * summary="Get creator examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default="Q"),
     *     )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant question categories master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCreatorExaminations(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('created_by', $user->user_id);
            // $request->request->set('examination_type', 'Q');
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                // $d = $this->qb_getExaminationQuestionPageCount(3);
                // dd($d);
                $listing = $this->qb_creatorExaminationPaginated($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Paginated list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/question/create-or-update",
     * summary="Create or update exmination question data of tenant (teacher) as per subdomain",
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
     *     description="add new examination question",
     *    @OA\JsonContent(
     *       required={"examination_id","page_id"},
     *       @OA\Property(property="examination_id", type="int"),
     *       @OA\Property(property="examination_question_id", type="int", default=null),
     *       @OA\Property(property="point", type="int"),
     *       @OA\Property(property="time_inseconds", type="int"),
     *       @OA\Property(property="question_id", type="int", default=null),
     *       @OA\Property(property="page_id", type="string"),
     *       @OA\Property(property="question_type", type="string"),
     *       @OA\Property(property="year_group_id", type="int"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int", default=null),
     *       @OA\Property(property="question_category_id", type="int", default=null),
     *       @OA\Property(property="question", type="string"),
     *       @OA\Property(property="level", type="string", default="low"),
     *       @OA\Property(property="require_file_upload", type="int", default=null),
     *       @OA\Property(property="source", type="string", default="Q"),
     *       @OA\Property(property="options", type="array", description="List of answer options",
     *       @OA\Items(
     *          @OA\Property(
     *              property="option_value",
     *              description="Answer text",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="is_correct",
     *              description="mark 1 if correct option",
     *              type="int",
     *              nullable="false"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="Examination added successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createOrUpdateExaminationQuestion(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();

            $request->request->set('created_by', $user->user_id);
            $request->request->set('creator_type', $user->user_type . '-' . $user->role);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $model = $this->qb_createOrUpdateExaminationQuestion($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Examination question added/updated successfully.', 'examination_question' => $model), 'error' => []];

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
     *     path="/api/{subdomain}/examination/import-quiz-questions",
     *     summary="Bulk import quiz questions",
     *     tags={"Tenant"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="page_id", type="string"),
     *                 @OA\Property(property="examination_id", type="string"),
     *                 @OA\Property(property="year_group_id", type="string"),
     *                 @OA\Property(property="subject_id", type="string"),
     *                 @OA\Property(property="lesson_id", type="string"),
     *                 @OA\Property(property="topic_id", type="string"),
     *                 @OA\Property(property="sub_topic_id", type="string"),
     *                 @OA\Property(
     *                     property="import_file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload",
     *                     default=null
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="teacher bulk upload successfully"),
     *     @OA\Response(response="400", description="tenant not available"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function importQuizQuestions(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }

            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $file = $request->file('import_file');
                // dd($file);
                if ($file) {
                    $page_id = CommonHelper::decryptId($request->page_id);
                    $examination_id = CommonHelper::decryptId($request->examination_id);

                    $data = \Excel::import(new QuizQuestionImport($page_id, $examination_id, $request->year_group_id, $request->subject_id, $request->lesson_id, $request->topic_id, $request->sub_topic_id, $user->tenant_id, $user->user_id, $user->user_type), $file);

                    $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Bulk quiz question import processed successfully.'), 'error' => []];
                    return response()->json($reponse, 200);
                } else {
                    $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'No file provided for import.']];
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
     * path="/api/{subdomain}/examination/get-by-id",
     * summary="Get examination details by examination_id",
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
     *     description="Pass encrpted record examination_id",
     *    @OA\JsonContent(
     *       required={"examination_id"},
     *       @OA\Property(property="examination_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="examination details"),
     * @OA\Response(response="401", description="examination not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getExaminationById(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $examination_id = CommonHelper::decryptId($request->examination_id);
                // dd($examination_id);
                // $examination_id = $request->examination_id;

                $details = $this->qb_getExaminationById($examination_id);
                // dd($details);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Examination details by id', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/examination/question/get-by-examination_question_id",
     * summary="Get examination details by examination_question_id",
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
     *     description="Pass encrpted record examination_question_id",
     *    @OA\JsonContent(
     *       required={"examination_question_id"},
     *       @OA\Property(property="examination_question_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="examination question details"),
     * @OA\Response(response="401", description="examination question not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getExaminationQuestionById(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $examination_question_id = CommonHelper::decryptId($request->examination_question_id);
                // dd($examination_question_id);
                $details = $this->qb_getExaminationQuestionById($examination_question_id);
                // dd($details);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Examination question details by id', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/examination/remove-question-page",
     * summary="Remove examination question page by examination_id & page_id",
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
     *     description="Pass encrpted record examination_id",
     *    @OA\JsonContent(
     *       required={"examination_id","page_id"},
     *       @OA\Property(property="examination_id", type="int"),
     *       @OA\Property(property="page_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="examination details"),
     * @OA\Response(response="401", description="examination not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function removeExaminationQuestionByPageId(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $examination_id = CommonHelper::decryptId($request->examination_id);
                $page_id = CommonHelper::decryptId($request->page_id);
                // dd($examination_id);
                // $examination_id = $request->examination_id;

                $details = $this->qb_removeExaminationPage($examination_id, $page_id);
                // dd($details);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Examination details by id', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/examination/update-status",
     * summary="Update examination status by examination_id",
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
     *     description="Pass encrpted record examination_id",
     *    @OA\JsonContent(
     *       required={"examination_id","status"},
     *       @OA\Property(property="examination_id", type="int"),
     *       @OA\Property(property="status", type="string"),
     *    )
     * ),
     * @OA\Response(response="200", description="examination details"),
     * @OA\Response(response="401", description="examination not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateExaminationStatus(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $examination_id = CommonHelper::decryptId($request->examination_id);
                // dd($examination_id);
                // $examination_id = $request->examination_id;

                $details = $this->qb_updateStatusExamination($examination_id, $request->status);
                // dd($details);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Examination status updated', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/examination/update-hw",
     * summary="Update examination status by examination_id",
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
     *     description="Pass encrpted record examination_id",
     *    @OA\JsonContent(
     *       required={"examination_id","homework"},
     *       @OA\Property(property="examination_id", type="int"),
     *       @OA\Property(property="homework", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="examination details"),
     * @OA\Response(response="401", description="examination not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateExaminationHw(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $examination_id = CommonHelper::decryptId($request->examination_id);
                // dd($examination_id);
                // $examination_id = $request->examination_id;

                $details = $this->qb_updateHwExamination($examination_id, $request->homework);
                // dd($details);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Examination status updated', 'details' => $details], 'error' => []];
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
     * path="/api/{subdomain}/examination/consumer-examinations",
     * summary="Get consumer examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text","exam_type"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="examination_type", type="string", default="Q"),
     *       @OA\Property(property="search_text", type="string", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant exam list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getConsumerExaminations(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('consumed_by', $user->user_id);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_consumerExaminationPaginated($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Paginated list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/consumer-examination-questions",
     * summary="Get consumer examination questions of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"examination_id"},
     *       @OA\Property(property="examination_id", type="int"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant exam question list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getConsumerExaminationQuestions(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('consumed_by', $user->user_id);
            // dd($request->all());
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_getExaminationQuestions($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/consumer-examination-save",
     * summary="Consumer examination save of tenant (teacher) as per subdomain",
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
     *     description="save consumer examination data",
     *    @OA\JsonContent(
     *       required={"examination_id"},
     *       @OA\Property(property="examination_id", type="int"),
     *       @OA\Property(property="start_time", type="int", default=null),
     *       @OA\Property(property="end_time", type="int", default=null),
     *       @OA\Property(property="total_time_in_mins", type="int", default=0),
     *       @OA\Property(property="total_marks", type="int", default=0),
     *       @OA\Property(property="options", type="array", description="List of answer options",
     *       @OA\Items(
     *          @OA\Property(
     *              property="examination_question_id",
     *              description="question id",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="answer",
     *              description="answer",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="attachment_file",
     *              description="answer attachment",
     *              type="string",
     *              format="binary",
     *              nullable="true"
     *          ),
     *          @OA\Property(
     *              property="time_taken_inmins",
     *              description="time taken to answer the question",
     *              type="int",
     *              nullable="false"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="Examination added successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function saveConsumerExamination(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('consumed_by', $user->user_id);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_saveConsumerExam($request);

                // dd($listing);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'You have successfully completed your exam.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/creator-examinations-for-review",
     * summary="Get creator examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default="Q"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant question categories master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCreatorExaminationForReview(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('created_by', $user->user_id);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                // $d = $this->qb_getExaminationQuestionPageCount(3);
                // dd($d);
                $listing = $this->qb_creatorExaminationForReviewPaginated($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Paginated list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/get-examination-submission-info",
     * summary="Retrieve examination submission details by user_result_id of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"user_result_id"},
     *       @OA\Property(property="user_result_id", type="int"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant exam question list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getSubmittedExaminationInfo(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('consumed_by', $user->user_id);
            // dd($request->all());
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $details = $this->qb_getExaminationSubmissionDetails($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'details fetched successfully.', 'details' => $details), 'error' => ''];
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
     * path="/api/{subdomain}/examination/creator-examination-review-save",
     * summary="Save exmination review data of tenant (teacher) as per subdomain",
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
     *     description="save creator examination review data",
     *    @OA\JsonContent(
     *       required={"user_result_id"},
     *       @OA\Property(property="user_result_id", type="int"),
     *       @OA\Property(property="options", type="array", description="List of answer options",
     *       @OA\Items(
     *          @OA\Property(
     *              property="examination_question_id",
     *              description="question id",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="is_correct",
     *              description="answer correct or wrong",
     *              type="int",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="marks_given",
     *              description="marks obtained",
     *              type="string",
     *              nullable="false"
     *          ),
     *          @OA\Property(
     *              property="reviewer_comments",
     *              description="reviewer comment",
     *              type="string",
     *              nullable="false"
     *          ),
     *       )
     *      )
     *    )
     * ),
     * @OA\Response(response="200", description="Examination reviewed successfully"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function saveCreatorReviewExamination(Request $request)
    {
        // dd($request->all());
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('reviewer_user_id', $user->user_id);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $details = $this->qb_saveCreatorExamReview($request);

                // dd($details);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'You have successfully reviewed the exam.', 'details' => $details), 'error' => ''];
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
     * path="/api/{subdomain}/examination/creator-examinations-reviewed",
     * summary="Get creator reviewed examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default="Q"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant creator reviewed examination list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCreatorExaminationReviewed(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('created_by', $user->user_id);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                // $d = $this->qb_getExaminationQuestionPageCount(3);
                // dd($d);
                $listing = $this->qb_creatorExaminationReviewedPaginated($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Paginated list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/consumer-examinations-reviewed",
     * summary="Get consumer reviewed examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default="Q"),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant consumer reviewed examination list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getConsumerExaminationReviewed(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            $request->request->set('consumed_by', $user->user_id);
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_consumerExaminationReviewedPaginated($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Paginated list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/results",
     * summary="Get consumer reviewed examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant consumer reviewed examination list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getExaminationResults(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_examinationResults($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/parent/examination/results",
     * summary="Get parent consumer reviewed examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="search_academic_year_id", type="int", default=null),
     *       @OA\Property(property="search_year_group_id", type="int", default=null),
     *       @OA\Property(property="search_subject_id", type="int", default=null),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default=null),
     *    )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant parent consumer reviewed examination list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getParentExaminationResults(Request $request)
    {
        try {
            // $user = JWTAuth::parseToken()->authenticate();
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $request->request->set('parent_id', $user->user_id);
                $listing = $this->qb_parentExaminationResults($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/all-creator-examinations",
     * summary="Get creator examination data of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"page","search_text"},
     *       @OA\Property(property="page", type="int", default=1),
     *       @OA\Property(property="search_text", type="string", default=null),
     *       @OA\Property(property="examination_type", type="string", default="Q"),
     *     )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant question categories master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getCreatorAllExaminations(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            $request->request->set('created_by', $user->user_id);
            // $request->request->set('examination_type', 'Q');
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_allCreatorExaminations($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Exam list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
     * path="/api/{subdomain}/examination/user-result",
     * summary="Get examination result based on userid and examid of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="pass user_id & examination_id",
     *    @OA\JsonContent(
     *       required={"user_id","examination_id"},
     *       @OA\Property(property="user_id", type="int"),
     *       @OA\Property(property="examination_id", type="int"),
     *     )
     * ),
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant question categories master details list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getUserExaminationResult(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {

                $user = JWTAuth::parseToken()->authenticate();
            }
            // $request->request->set('examination_type', 'Q');
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = $this->qb_getUserExaminationResult($request);

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Exam list fetched successfully.', 'listing' => $listing), 'error' => ''];
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
