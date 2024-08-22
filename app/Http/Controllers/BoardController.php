<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Board;
use App\Traits\GeneralMethods;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BoardController extends Controller
{
    use GeneralMethods;

    /**
     * @OA\Post(
     * path="/api/get-boards",
     * summary="Get paginated board data",
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
     * @OA\Response(response="200", description="board list"),
     * @OA\Response(response="401", description="board not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getBoards(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $page = $request->page;
            $search_text = $request->search_text;

            if ($search_text != '') {
                $boards = Board::with('country')->where(function ($query) use ($search_text) {

                    $query->where('board_name', 'like', '%' . $search_text . '%')
                        ->orWhere('short_name', 'like', '%' . $search_text . '%');
                })->orderBy('board_name', 'asc')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            } else {
                $boards = Board::with('country')->orderBy('board_name', 'asc')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
            }
            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Paginated board list', 'boards' => $boards], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/create-board",
     * summary="Create board data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="add new board",
     *    @OA\JsonContent(
     *       required={"board_name","short_name"},
     *       @OA\Property(property="board_name", type="string"),
     *       @OA\Property(property="short_name", type="string"),
     *       @OA\Property(property="country_id", type="int", default=null),
     *       @OA\Property(property="description", type="string", default=null),
     *    )
     * ),
     * @OA\Response(response="200", description="board added"),
     * @OA\Response(response="401", description="board not added"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function createBoard(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $existingBoard = Board::where('board_name', $request->board_name)->first();
            if (empty($existingBoard)) {
                $model = new Board;
                $model->board_name = $request->board_name;
                $model->short_name = $request->short_name;
                $model->country_id = $request->country_id;
                $model->description = $request->description;
                $model->status = GlobalVars::ACTIVE_STATUS;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Board added successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Board already exist.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/get-board-by-id",
     * summary="Get board details by id",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=false,
     *     description="optional params",
     *    @OA\JsonContent(
     *       required={"board_id"},
     *       @OA\Property(property="board_id", type="int"),
     *    )
     * ),
     * @OA\Response(response="200", description="board details"),
     * @OA\Response(response="401", description="board not available"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function getBoardById(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $board_id = CommonHelper::decryptId($request->board_id);

            $boards = Board::find($board_id);

            $reponse = ['status' => true, 'statuscode' => '200', 'result' => ['message' => 'Board details by id', 'board_details' => $boards], 'error' => []];
            return response()->json($reponse, 200);

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }

    }

    /**
     * @OA\Post(
     * path="/api/update-board",
     * summary="Update board data",
     * tags={"Portal"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *     description="update board information",
     *    @OA\JsonContent(
     *       required={"board_id","board_name","short_name"},
     *       @OA\Property(property="board_id", type="int"),
     *       @OA\Property(property="board_name", type="string"),
     *       @OA\Property(property="short_name", type="string"),
     *       @OA\Property(property="country_id", type="int", default=null),
     *       @OA\Property(property="description", type="string", default=null),
     *       @OA\Property(property="status", type="string", default="Active"),
     *    )
     * ),
     * @OA\Response(response="200", description="board added"),
     * @OA\Response(response="401", description="board not added"),
     * @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function updateBoard(Request $request)
    {
        try {
            if (!$this->invalidatePortalUser()) {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Invalid user token.']];
                return response()->json($reponse, 400);
            }
            $existingBoard = Board::where('board_name', $request->board_name)->where('board_id', '!=', $request->board_id)->first();
            if (empty($existingBoard)) {
                $model = Board::find($request->board_id);
                $model->board_name = $request->board_name;
                $model->short_name = $request->short_name;
                $model->country_id = $request->country_id;
                $model->description = $request->description;
                $model->status = $request->status;
                $model->save();

                $reponse = ['status' => 'true', 'statuscode' => '200', 'result' => array('message' => 'Board updated successfully.'), 'error' => []];
                return response()->json($reponse, 200);
            } else {
                $reponse = ['status' => false, 'statuscode' => '400', 'result' => [], 'error' => ['message' => 'Board already exist.']];
                return response()->json($reponse, 400);
            }

        } catch (\Exception $e) {
            $reponse = ['status' => false, 'statuscode' => '500', 'result' => [], 'error' => ['message' => 'Something went wrong. Please try again later.', 'data' => ['exception' => $e->getMessage()]]];
            return response()->json($reponse, 500);
        }
    }
}
