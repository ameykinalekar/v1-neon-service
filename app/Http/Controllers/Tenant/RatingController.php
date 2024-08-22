<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Lesson;
use App\Models\ViewTeacher;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use JWTAuth;
use DB;
use App\Helpers\CommonHelper;

class RatingController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/{subdomain}/rating/create",
     * summary="Create rating data of tenant as per subdomain",
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
     *     description="add new rating",
     *    @OA\JsonContent(
     *       required={"creator_rating","content_rating"},
     *       @OA\Property(property="creator_id", type="int"),
     *       @OA\Property(property="creator_rating", type="int"),
     *       @OA\Property(property="content_rating", type="int"),
     *       @OA\Property(property="creator_remarks", type="string"),
     *       @OA\Property(property="content_remarks", type="string"),
     *       @OA\Property(property="subject_id", type="int"),
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="academic_year_id", type="int",default=null),
     *       @OA\Property(property="year_group_id", type="int",default=null),
     *    )
     * ),

     * @OA\Response(response="200", description="create tenant task"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createRating(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {
                $lesson_id= $request->lesson_id ?? 0;
                $existingRating = Rating::where('rating_created_by', $user->user_id)
                ->where('lesson_id', $lesson_id)
                ->first();
                if(!empty($existingRating)){
                    $model = Rating::find($existingRating->rating_id);
                    $model->creator_rating = $request->creator_rating ?? 0;
                    $model->content_rating = $request->content_rating ?? 0;
                    $model->creator_remarks = $request->creator_remarks ?? '';
                    $model->content_remarks = $request->content_remarks ?? '';
                    $model->academic_year_id = $request->academic_year_id ?? 0;
                    $model->year_group_id = $request->year_group_id ?? 0;
                    $model->subject_id = $request->subject_id ?? 0;
                    $model->lesson_id = $request->lesson_id ?? 0;
                    $model->creator_id = $request->creator_id ?? 0;
                    $model->rating_created_by = $user->user_id;
                    $model->rating_creator_type = $user->user_type;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();
                }else{
                    $model = new Rating;
                    $model->creator_rating = $request->creator_rating ?? 0;
                    $model->content_rating = $request->content_rating ?? 0;
                    $model->creator_remarks = $request->creator_remarks ?? '';
                    $model->content_remarks = $request->content_remarks ?? '';
                    $model->academic_year_id = $request->academic_year_id ?? 0;
                    $model->year_group_id = $request->year_group_id ?? 0;
                    $model->subject_id = $request->subject_id ?? 0;
                    $model->lesson_id = $request->lesson_id ?? 0;
                    $model->creator_id = $request->creator_id ?? 0;
                    $model->rating_created_by = $user->user_id;
                    $model->rating_creator_type = $user->user_type;
                    $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();
                }

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Rating saved successfully.'), 'error' => []];
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
     * path="/api/{subdomain}/rating/fetch-by-lesson",
     * summary="Get existing rating of tenant as per subdomain",
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
     *     description="required",
     *    @OA\JsonContent(
     *       required={"lesson_id"},
     *       @OA\Property(property="lesson_id", type="int"),
     *       @OA\Property(property="teacher_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getExistingRatingByLesson(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $lesson_id=$request->lesson_id;
                $teacher_id=$request->teacher_id;

                $details = Rating::where('rating_created_by', $user->user_id)
                    ->where('lesson_id', $lesson_id)
                    ->first();
                // dd($listing);

                $lessonInfo=Lesson::with('subject')->find($lesson_id);
                $teacherInfo=ViewTeacher::find($teacher_id);

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'All Attendance listing', 'rating' => $details,'lesson_info'=>$lessonInfo,'teacher_info'=>$teacherInfo], 'error' => []];
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
     * path="/api/{subdomain}/rating/consumer-by-lesson",
     * summary="Get consumer existing rating of tenant as per subdomain",
     * tags={"Tenant"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *         name="subdomain",
     *         in="path",
     *         description="tenant subdomain",
     *         required=true,
     *      ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getConsumerRatingByLesson(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $listing = Rating::where('creator_id', $user->user_id)
                ->where('status',GlobalVars::ACTIVE_STATUS)
                ->with('lesson')
                    ->select('lesson_id', DB::raw('SUM(content_rating)/count(*) as avg_content_rating'), DB::raw('SUM(content_rating_outof)/count(*) as avg_content_rating_outof'), DB::raw('SUM(creator_rating)/count(*) as avg_creator_rating'), DB::raw('SUM(creator_rating_outof)/count(*) as avg_creator_rating_outof'))
                    ->groupBy('lesson_id')
                    ->get();

                // dd($listing);
               
                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'All rating listing', 'listing' => $listing], 'error' => []];

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
     * path="/api/{subdomain}/rating/consumer-by-lesson-id",
     * summary="Get existing rating of tenant as per subdomain",
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
     *     description="required",
     *    @OA\JsonContent(
     *       required={"lesson_id"},
     *       @OA\Property(property="lesson_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="tenant attendance data list"),
     * @OA\Response(response="400", description="tenant not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getConsumerRatingByLessonId(Request $request)
    {
        try {
            $user = auth('tenant')->setToken(request()->bearerToken())->user();
            // print_r(empty($user));
            if ($user == null) {
                $user = JWTAuth::parseToken()->authenticate();
            }
            if ($this->tenantId != "" && $this->tenantId == $user->tenant_id) {

                $lesson_id=CommonHelper::decryptId($request->lesson_id);

                $listing = Rating::where('creator_id', $user->user_id)
                    ->where('lesson_id', $lesson_id)
                    ->where('status',GlobalVars::ACTIVE_STATUS)
                    ->with('student','lesson')
                    ->get();
                // dd($listing);

               

                $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'All Attendance listing', 'listing' => $listing,], 'error' => []];
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