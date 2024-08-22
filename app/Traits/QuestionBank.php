<?php

namespace App\Traits;

use App\Helpers\CommonHelper;
use App\Models\Examination;
use App\Models\ExaminationQuestion;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\TaskAllocation;
use App\Models\TaskExamination;
use App\Models\UserResult;
use App\Models\UserResultInput;
use App\Models\UserSibling;
use App\Models\UserSubject;
use App\Models\ViewStudent;
use DB;
use GlobalVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Models\Audit;

trait QuestionBank
{

    public function qb_questionCategoriesPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;

        if ($search_text != '') {
            $listing = QuestionCategory::where('category_name', 'like', '%' . $search_text . '%')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
        } else {
            $listing = QuestionCategory::paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
        }

        return $listing;
    }

    public function qb_createQuestionCategory(Request $request)
    {
        // dd($request->all());
        $model = new QuestionCategory;
        $model->category_name = $request->category_name;
        $model->status = GlobalVars::ACTIVE_STATUS;
        $model->save();

        return $model;
    }

    public function qb_getQuestionCategoryById($question_category_id)
    {
        $details = QuestionCategory::find($question_category_id);
        return $details;
    }

    public function qb_updateQuestionCategory(Request $request)
    {
        // dd($request->all());
        $model = QuestionCategory::find($request->question_category_id);
        $model->category_name = $request->category_name;
        $model->status = $request->status ?? GlobalVars::ACTIVE_STATUS;
        $model->save();

        return $model;
    }

    public function qb_questionCategories()
    {
        $listArray = [];
        $listing = QuestionCategory::where('status', GlobalVars::ACTIVE_STATUS)->get();
        foreach ($listing as $record) {
            $listArray[$record->question_category_id] = $record->category_name;
        }

        return $listArray;
    }

    public function qb_questionsPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;

        if ($search_text != '') {
            $listing = Question::where('question', 'like', '%' . $search_text . '%')->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
        } else {
            $listing = Question::paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
        }

        return $listing;
    }

    public function qb_createQuestion(Request $request)
    {
        // dd($request->all());
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {
            $model = new Question;
            $model->question_type = $request->question_type;
            $model->year_group_id = $request->year_group_id;
            $model->subject_id = $request->subject_id;
            $model->lesson_id = $request->lesson_id ?? null;
            $model->question_category_id = $request->question_category_id ?? null;
            $model->question = $request->question;
            $model->level = $request->level;
            $model->require_file_upload = $request->require_file_upload ?? 0;
            $model->source = $request->source ?? 'Q';
            $model->created_by = $request->created_by;
            $model->creator_type = $request->creator_type;
            $model->status = GlobalVars::ACTIVE_STATUS;
            $model->save();

            $question_id = $model->question_id;

            $questionOptions = $request->options;
            if (count($questionOptions) > 0) {
                for ($i = 0; $i < count($questionOptions); $i++) {

                    $modelOption = new QuestionOption;
                    $modelOption->question_id = $question_id;
                    $modelOption->option_value = $questionOptions[$i]['option_value'];
                    $modelOption->is_correct = $questionOptions[$i]['is_correct'];

                    $modelOption->save();
                }

            }
            $response = Question::find($question_id);
            // dd($response->options);
            DB::commit();
        } catch (\Exception $e) {
            // throw ($e);
            DB::rollback();

        }
        return $response;
    }

    public function qb_createExam(Request $request)
    {
        // dd($request->all());
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {
            $model = new Examination;
            $model->examination_type = $request->examination_type;
            $model->name = $request->name;
            $model->year_group_id = $request->year_group_id ?? null;
            $model->subject_id = $request->subject_id ?? null;
            $model->lesson_id = $request->lesson_id ?? null;
            $model->created_by = $request->created_by ?? null;
            $model->creator_type = $request->creator_type ?? null;
            $model->homework = $request->homework ?? 0;
            $model->status = GlobalVars::EXAM_DRAFT_STATUS;
            $model->save();

            $examination_id = $model->examination_id;
            $response = Examination::find($examination_id);
            // dd($response->options);
            DB::commit();
        } catch (\Exception $e) {
            // throw ($e);
            DB::rollback();

        }
        return $response;
    }
    public function qb_createExamWithQuestions(Request $request)
    {
        // dd($request->all());
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {
            $examination_id = $request->examination_id ?? null;
            if ($examination_id != null) {
                $model = Examination::find($examination_id);
                $model->examination_type = $request->examination_type;
                $model->name = $request->name;
                $model->year_group_id = $request->year_group_id ?? null;
                $model->subject_id = $request->subject_id ?? null;
                $model->lesson_id = $request->lesson_id ?? null;
                $model->created_by = $request->created_by ?? null;
                $model->creator_type = $request->creator_type ?? null;
                $model->homework = $request->homework ?? 0;
                $model->status = $request->examination_status ?? GlobalVars::EXAM_DRAFT_STATUS;
                $model->save();

                $examination_id = $model->examination_id;
            } else {
                $model = new Examination;
                $model->examination_type = $request->examination_type;
                $model->name = $request->name;
                $model->year_group_id = $request->year_group_id ?? null;
                $model->subject_id = $request->subject_id ?? null;
                $model->lesson_id = $request->lesson_id ?? null;
                $model->created_by = $request->created_by ?? null;
                $model->creator_type = $request->creator_type ?? null;
                $model->homework = $request->homework ?? 0;
                $model->status = $request->examination_status ?? GlobalVars::EXAM_DRAFT_STATUS;
                $model->save();

                $examination_id = $model->examination_id;
            }

            $questionOptions = $request->questions ?? array();
            if (count($questionOptions) > 0) {
                for ($i = 0; $i < count($questionOptions); $i++) {
                    //create question master
                    $questionInfo = [
                        'question' => $questionOptions[$i]['question'],
                        'question_type' => $questionOptions[$i]['question_type'],
                        'level' => $questionOptions[$i]['level'],
                        'require_file_upload' => $questionOptions[$i]['require_file_upload'] ?? 0,
                        'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                    ];
                    $question_id = $questionOptions[$i]['question_id'] ?? null;
                    if ($question_id != null) {
                        //existing question bank question selected
                        $existingQuestion = Question::find($request->question_id);
                        if (!empty($existingQuestion)) {
                            $question_id = $existingQuestion->question_id;

                            //question id not in use by other examination
                            $checkExam = ExaminationQuestion::where('examination_id', '<>', $examination_id)->where('question_id', $question_id)->first();
                            if (!empty($checkExam)) {
                                //create new question element as edit is forced
                                $modelQuestion = new Question;
                                $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                $modelQuestion->year_group_id = $request->year_group_id;
                                $modelQuestion->subject_id = $request->subject_id;
                                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                $modelQuestion->question = $questionOptions[$i]['question'];
                                $modelQuestion->level = $questionOptions[$i]['level'];
                                $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                $modelQuestion->created_by = $request->created_by;
                                $modelQuestion->creator_type = $request->creator_type;
                                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                $modelQuestion->save();

                                $question_id = $modelQuestion->question_id;
                            } else {
                                //update existing question element as edit is forced & is not in use by other
                                $modelQuestion = Question::find($question_id);
                                $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                $modelQuestion->year_group_id = $request->year_group_id;
                                $modelQuestion->subject_id = $request->subject_id;
                                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                $modelQuestion->question = $questionOptions[$i]['question'];
                                $modelQuestion->level = $questionOptions[$i]['level'];
                                $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                $modelQuestion->created_by = $request->created_by;
                                $modelQuestion->creator_type = $request->creator_type;
                                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                $modelQuestion->save();

                                $question_id = $modelQuestion->question_id;
                            }

                        } else {
                            //wrong master question id provided - create new question master
                            $modelQuestion = new Question;
                            $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                            $modelQuestion->year_group_id = $request->year_group_id;
                            $modelQuestion->subject_id = $request->subject_id;
                            $modelQuestion->lesson_id = $request->lesson_id ?? null;
                            $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                            $modelQuestion->question = $questionOptions[$i]['question'];
                            $modelQuestion->level = $questionOptions[$i]['level'];
                            $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                            $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                            $modelQuestion->created_by = $request->created_by;
                            $modelQuestion->creator_type = $request->creator_type;
                            $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                            $modelQuestion->save();

                            $question_id = $modelQuestion->question_id;
                        }

                    } else {
                        //create question master
                        $modelQuestion = new Question;
                        $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                        $modelQuestion->year_group_id = $request->year_group_id;
                        $modelQuestion->subject_id = $request->subject_id;
                        $modelQuestion->lesson_id = $request->lesson_id ?? null;
                        $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                        $modelQuestion->question = $questionOptions[$i]['question'];
                        $modelQuestion->level = $questionOptions[$i]['level'];
                        $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                        $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                        $modelQuestion->created_by = $request->created_by;
                        $modelQuestion->creator_type = $request->creator_type;
                        $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                        $modelQuestion->save();

                        $question_id = $modelQuestion->question_id;
                    }

                    $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                    if ($examination_question_id != null) {
                        $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                        $modelExaminationQuestion->examination_id = $examination_id;
                        $modelExaminationQuestion->question_id = $question_id;
                        $modelExaminationQuestion->page_id = 1;
                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                        $modelExaminationQuestion->save();

                        $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                    } else {
                        $modelExaminationQuestion = new ExaminationQuestion;
                        $modelExaminationQuestion->examination_id = $examination_id;
                        $modelExaminationQuestion->question_id = $question_id;
                        $modelExaminationQuestion->page_id = 1;
                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                        $modelExaminationQuestion->save();

                        $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                    }

                }
            }
            $tot_time_questions = ExaminationQuestion::where('examination_id', $examination_id)->sum('time_inseconds');

            // $total_time_minutes = ($tot_time_questions / 60);
            $init = $tot_time_questions;
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $total_time = "$hours:$minutes:$seconds";
            // dd($total_time);
            //update examination data
            $modelExamination = Examination::find($examination_id);

            $modelExamination->total_time = $total_time;

            $modelExamination->save();
            $response = Examination::find($examination_id);
            // dd($response->options);
            DB::commit();
        } catch (\Exception $e) {
            // throw ($e);
            DB::rollback();

        }
        return $response;
    }

    public function qb_createExamWithQuestionsNSubquestionsOld(Request $request)
    {
        // dd($request->all());
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {
            $examination_id = $request->examination_id ?? null;
            if ($examination_id != null) {
                //modify examination
                $model = Examination::find($examination_id);
                $model->examination_type = $request->examination_type;
                $model->name = $request->name;
                $model->homework = $request->homework;
                $model->year_group_id = $request->year_group_id ?? null;
                $model->subject_id = $request->subject_id ?? null;
                $model->lesson_id = $request->lesson_id ?? null;
                $model->created_by = $request->created_by ?? null;
                $model->creator_type = $request->creator_type ?? null;
                $model->status = $request->examination_status ?? GlobalVars::EXAM_DRAFT_STATUS;
                $model->save();

                $examination_id = $model->examination_id;
            } else {
                //add examination
                $model = new Examination;
                $model->examination_type = $request->examination_type;
                $model->name = $request->name;
                $model->homework = $request->homework;
                $model->year_group_id = $request->year_group_id ?? null;
                $model->subject_id = $request->subject_id ?? null;
                $model->lesson_id = $request->lesson_id ?? null;
                $model->created_by = $request->created_by ?? null;
                $model->creator_type = $request->creator_type ?? null;
                $model->status = $request->examination_status ?? GlobalVars::EXAM_DRAFT_STATUS;
                $model->save();

                $examination_id = $model->examination_id;
            }
            // dd($examination_id);
            $questionOptions = $request->questions ?? array();
            // dd($questionOptions);
            if (count($questionOptions) > 0) {
                for ($i = 0; $i < count($questionOptions); $i++) {

                    //create question master
                    $questionInfo = [
                        'question' => $questionOptions[$i]['question'],
                        'question_type' => $questionOptions[$i]['question_type'],
                        'level' => $questionOptions[$i]['level'],
                        'require_file_upload' => $questionOptions[$i]['require_file_upload'] ?? 0,
                        'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                    ];
                    $question_id = $questionOptions[$i]['question_id'] ?? null;
                    if ($questionOptions[$i]['question_type'] == 'linked') {
                        if ($question_id != null) {
                            //modify linked question
                            //existing question bank question selected
                            $existingQuestion = Question::find($question_id);
                            // dd($existingQuestion);
                            if (!empty($existingQuestion)) {
                                $question_id = $existingQuestion->question_id;

                                //question id not in use by other examination
                                $checkExam = ExaminationQuestion::where('examination_id', '<>', $examination_id)->where('question_id', $question_id)->first();
                                // dd($checkExam);
                                if (!empty($checkExam)) {
                                    //create new question element as edit is forced
                                    // dd($existingQuestion);
                                    $modelQuestion = new Question;
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                    $modelQuestion->year_group_id = $request->year_group_id;
                                    $modelQuestion->subject_id = $request->subject_id;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'];
                                    $modelQuestion->level = $questionOptions[$i]['level'];
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by;
                                    $modelQuestion->creator_type = $request->creator_type;
                                    $modelQuestion->linked_question = 1;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                    $parent_question_id = $question_id;
                                    $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;
                                    if ($examination_question_id != null) {
                                        $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();

                                        $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                    } else {
                                        $modelExaminationQuestion = new ExaminationQuestion;
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->linked_question = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();

                                        $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                        $parent_examination_question_id = $examination_question_id;
                                    }
                                    //add subquestion under parent
                                    if (is_array($questionOptions[$i]['subquestions']) && $questionOptions[$i]['subquestions'] != null && count($questionOptions[$i]['subquestions']) > 0) {
                                        $subquestions = $questionOptions[$i]['subquestions'];

                                        for ($j = 0; $j < count($subquestions); $j++) {
                                            $questionInfoSub = [
                                                'question' => $subquestions[$j]['question'],
                                                'question_type' => $subquestions[$j]['question_type'],
                                                'level' => $subquestions[$j]['level'],
                                                'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                                'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                            ];
                                            if ($subquestions[$j]['question_id'] != null) {
                                                $modelQuestionSub = Question::find($subquestions[$j]['question_id']);
                                            } else {
                                                $modelQuestionSub = new Question;

                                            }
                                            $modelQuestionSub->question_type = $subquestions[$j]['question_type'];
                                            $modelQuestionSub->year_group_id = $request->year_group_id;
                                            $modelQuestionSub->subject_id = $request->subject_id;
                                            $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                            $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                            $modelQuestionSub->question = $subquestions[$j]['question'];
                                            $modelQuestionSub->level = $subquestions[$j]['level'];
                                            $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                            $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                            $modelQuestionSub->created_by = $request->created_by;
                                            $modelQuestionSub->creator_type = $request->creator_type;
                                            $modelQuestionSub->parent_question_id = $parent_question_id;
                                            $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                            $modelQuestionSub->save();

                                            $sub_question_id = $modelQuestionSub->question_id;

                                            $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                            if ($sub_examination_question_id != null) {
                                                $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                                $modelExaminationQuestionSub->examination_id = $examination_id;
                                                $modelExaminationQuestionSub->question_id = $question_id;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $questionOptions[$i]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                            } else {
                                                $modelExaminationQuestionSub = new ExaminationQuestion;
                                                $modelExaminationQuestionSub->examination_id = $examination_id;
                                                $modelExaminationQuestionSub->question_id = $sub_question_id;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id;
                                                $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                            }

                                        }
                                    }
                                } else {
                                    //update existing question element as edit is forced & is not in use by other
                                    $modelQuestion = Question::find($question_id);
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                    $modelQuestion->year_group_id = $request->year_group_id;
                                    $modelQuestion->subject_id = $request->subject_id;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'];
                                    $modelQuestion->level = $questionOptions[$i]['level'];
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by;
                                    $modelQuestion->creator_type = $request->creator_type;
                                    $modelQuestion->linked_question = 1;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                    $parent_question_id = $question_id;
                                    $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                                    if ($examination_question_id != null) {
                                        $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();

                                        $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                    } else {
                                        $modelExaminationQuestion = new ExaminationQuestion;
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->linked_question = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();

                                        $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                        $parent_examination_question_id = $examination_question_id;
                                    }
                                    //add subquestion under parent
                                    if (is_array($questionOptions[$i]['subquestions']) && $questionOptions[$i]['subquestions'] != null && count($questionOptions[$i]['subquestions']) > 0) {
                                        $subquestions = $questionOptions[$i]['subquestions'];

                                        for ($j = 0; $j < count($subquestions); $j++) {
                                            $questionInfoSub = [
                                                'question' => $subquestions[$j]['question'],
                                                'question_type' => $subquestions[$j]['question_type'],
                                                'level' => $subquestions[$j]['level'],
                                                'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                                'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                            ];
                                            if ($subquestions[$j]['question_id'] != null) {
                                                $modelQuestionSub = Question::find($subquestions[$j]['question_id']);
                                            } else {
                                                $modelQuestionSub = new Question;

                                            }
                                            $modelQuestionSub->question_type = $subquestions[$j]['question_type'];
                                            $modelQuestionSub->year_group_id = $request->year_group_id;
                                            $modelQuestionSub->subject_id = $request->subject_id;
                                            $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                            $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                            $modelQuestionSub->question = $subquestions[$j]['question'];
                                            $modelQuestionSub->level = $subquestions[$j]['level'];
                                            $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                            $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                            $modelQuestionSub->created_by = $request->created_by;
                                            $modelQuestionSub->creator_type = $request->creator_type;
                                            $modelQuestionSub->parent_question_id = $parent_question_id;
                                            $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                            $modelQuestionSub->save();

                                            $sub_question_id = $modelQuestionSub->question_id;

                                            $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                            if ($sub_examination_question_id != null) {
                                                $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                                $modelExaminationQuestionSub->examination_id = $examination_id;
                                                $modelExaminationQuestionSub->question_id = $question_id;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $questionOptions[$i]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                            } else {
                                                $modelExaminationQuestionSub = new ExaminationQuestion;
                                                $modelExaminationQuestionSub->examination_id = $examination_id;
                                                $modelExaminationQuestionSub->question_id = $sub_question_id;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id;
                                                $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                            }

                                        }
                                    }
                                }

                            } else {
                                //wrong master question id provided - create new question master
                                $modelQuestion = new Question;
                                $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                $modelQuestion->year_group_id = $request->year_group_id;
                                $modelQuestion->subject_id = $request->subject_id;
                                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                $modelQuestion->question = $questionOptions[$i]['question'];
                                $modelQuestion->level = $questionOptions[$i]['level'];
                                $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                $modelQuestion->created_by = $request->created_by;
                                $modelQuestion->creator_type = $request->creator_type;
                                $modelQuestion->linked_question = 1;
                                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                $modelQuestion->save();

                                $question_id = $modelQuestion->question_id;
                                $parent_question_id = $question_id;
                                if ($examination_question_id != null) {
                                    $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                } else {
                                    $modelExaminationQuestion = new ExaminationQuestion;
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->linked_question = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                    $parent_examination_question_id = $examination_question_id;
                                }
                                //add subquestion under parent
                                if (is_array($questionOptions[$i]['subquestions']) && $questionOptions[$i]['subquestions'] != null && count($questionOptions[$i]['subquestions']) > 0) {
                                    $subquestions = $questionOptions[$i]['subquestions'];

                                    for ($j = 0; $j < count($subquestions); $j++) {
                                        $questionInfoSub = [
                                            'question' => $subquestions[$j]['question'],
                                            'question_type' => $subquestions[$j]['question_type'],
                                            'level' => $subquestions[$j]['level'],
                                            'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                            'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                        ];
                                        if ($subquestions[$j]['question_id'] != null) {
                                            $modelQuestionSub = Question::find($subquestions[$j]['question_id']);
                                        } else {
                                            $modelQuestionSub = new Question;

                                        }
                                        $modelQuestionSub->question_type = $subquestions[$j]['question_type'];
                                        $modelQuestionSub->year_group_id = $request->year_group_id;
                                        $modelQuestionSub->subject_id = $request->subject_id;
                                        $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                        $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                        $modelQuestionSub->question = $subquestions[$j]['question'];
                                        $modelQuestionSub->level = $subquestions[$j]['level'];
                                        $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                        $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                        $modelQuestionSub->created_by = $request->created_by;
                                        $modelQuestionSub->creator_type = $request->creator_type;
                                        $modelQuestionSub->parent_question_id = $parent_question_id;
                                        $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                        $modelQuestionSub->save();

                                        $sub_question_id = $modelQuestionSub->question_id;

                                        $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                        if ($sub_examination_question_id != null) {
                                            $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                            $modelExaminationQuestionSub->examination_id = $examination_id;
                                            $modelExaminationQuestionSub->question_id = $question_id;
                                            $modelExaminationQuestionSub->page_id = 1;
                                            $modelExaminationQuestionSub->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                            $modelExaminationQuestionSub->point = $questionOptions[$i]['point'] ?? 0;
                                            $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                            $modelExaminationQuestionSub->save();

                                            $examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                        } else {
                                            $modelExaminationQuestionSub = new ExaminationQuestion;
                                            $modelExaminationQuestionSub->examination_id = $examination_id;
                                            $modelExaminationQuestionSub->question_id = $sub_question_id;
                                            $modelExaminationQuestionSub->page_id = 1;
                                            $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id;
                                            $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                            $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                            $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                            $modelExaminationQuestionSub->save();

                                            // $examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                        }

                                    }
                                }
                            }

                        } else {
                            //add linked question
                            $modelQuestion = new Question;
                            $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                            $modelQuestion->year_group_id = $request->year_group_id;
                            $modelQuestion->subject_id = $request->subject_id;
                            $modelQuestion->lesson_id = $request->lesson_id ?? null;
                            $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                            $modelQuestion->question = $questionOptions[$i]['question'];
                            $modelQuestion->level = $questionOptions[$i]['level'];
                            $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                            $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                            $modelQuestion->created_by = $request->created_by;
                            $modelQuestion->creator_type = $request->creator_type;
                            $modelQuestion->linked_question = 1;
                            $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                            $modelQuestion->save();

                            $question_id = $modelQuestion->question_id;
                            $parent_question_id = $question_id;
                            $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                            if ($examination_question_id != null) {
                                $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                $modelExaminationQuestion->examination_id = $examination_id;
                                $modelExaminationQuestion->question_id = $question_id;
                                $modelExaminationQuestion->page_id = 1;
                                $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                $modelExaminationQuestion->save();

                                $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                            } else {
                                $modelExaminationQuestion = new ExaminationQuestion;
                                $modelExaminationQuestion->examination_id = $examination_id;
                                $modelExaminationQuestion->question_id = $question_id;
                                $modelExaminationQuestion->page_id = 1;
                                $modelExaminationQuestion->linked_question = 1;
                                $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                $modelExaminationQuestion->save();

                                $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                $parent_examination_question_id = $examination_question_id;
                            }
                            //add subquestion under parent
                            if (is_array($questionOptions[$i]['subquestions']) && $questionOptions[$i]['subquestions'] != null && count($questionOptions[$i]['subquestions']) > 0) {
                                $subquestions = $questionOptions[$i]['subquestions'];
                                for ($j = 0; $j < count($subquestions); $j++) {
                                    $questionInfoSub = [
                                        'question' => $subquestions[$j]['question'],
                                        'question_type' => $subquestions[$j]['question_type'],
                                        'level' => $subquestions[$j]['level'],
                                        'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                        'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                    ];

                                    $modelQuestionSub = new Question;
                                    $modelQuestionSub->question_type = $subquestions[$j]['question_type'];
                                    $modelQuestionSub->year_group_id = $request->year_group_id;
                                    $modelQuestionSub->subject_id = $request->subject_id;
                                    $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                    $modelQuestionSub->question = $subquestions[$j]['question'];
                                    $modelQuestionSub->level = $subquestions[$j]['level'];
                                    $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                    $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestionSub->created_by = $request->created_by;
                                    $modelQuestionSub->creator_type = $request->creator_type;
                                    $modelQuestionSub->parent_question_id = $parent_question_id;
                                    $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestionSub->save();

                                    $sub_question_id = $modelQuestionSub->question_id;

                                    $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                    if ($sub_examination_question_id != null) {
                                        $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                        $modelExaminationQuestionSub->examination_id = $examination_id;
                                        $modelExaminationQuestionSub->question_id = $sub_question_ids;
                                        $modelExaminationQuestionSub->page_id = 1;
                                        $modelExaminationQuestionSub->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestionSub->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestionSub->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestionSub->save();

                                        $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                    } else {
                                        $modelExaminationQuestionSub = new ExaminationQuestion;
                                        $modelExaminationQuestionSub->examination_id = $examination_id;
                                        $modelExaminationQuestionSub->question_id = $sub_question_id;
                                        $modelExaminationQuestionSub->page_id = 1;
                                        $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id;
                                        $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                        $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                        $modelExaminationQuestionSub->save();

                                        $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                    }

                                }
                            }
                        }
                    } else {
                        if ($question_id != null) {
                            //modify single question

                            //existing question bank question selected
                            $existingQuestion = Question::find($question_id);
                            if (!empty($existingQuestion)) {
                                $question_id = $existingQuestion->question_id;

                                //question id not in use by other examination
                                $checkExam = ExaminationQuestion::where('examination_id', '<>', $examination_id)->where('question_id', $question_id)->first();
                                if (!empty($checkExam)) {
                                    //create new question element as edit is forced
                                    $modelQuestion = new Question;
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                    $modelQuestion->year_group_id = $request->year_group_id;
                                    $modelQuestion->subject_id = $request->subject_id;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'];
                                    $modelQuestion->level = $questionOptions[$i]['level'];
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by;
                                    $modelQuestion->creator_type = $request->creator_type;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                } else {
                                    //update existing question element as edit is forced & is not in use by other
                                    $modelQuestion = Question::find($question_id);
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                    $modelQuestion->year_group_id = $request->year_group_id;
                                    $modelQuestion->subject_id = $request->subject_id;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'];
                                    $modelQuestion->level = $questionOptions[$i]['level'];
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by;
                                    $modelQuestion->creator_type = $request->creator_type;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                }

                                $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                                if ($examination_question_id != null) {
                                    $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                } else {
                                    $modelExaminationQuestion = new ExaminationQuestion;
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                }

                            } else {
                                //wrong master question id provided - create new question master
                                $modelQuestion = new Question;
                                $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                $modelQuestion->year_group_id = $request->year_group_id;
                                $modelQuestion->subject_id = $request->subject_id;
                                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                $modelQuestion->question = $questionOptions[$i]['question'];
                                $modelQuestion->level = $questionOptions[$i]['level'];
                                $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                $modelQuestion->created_by = $request->created_by;
                                $modelQuestion->creator_type = $request->creator_type;
                                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                $modelQuestion->save();

                                $question_id = $modelQuestion->question_id;

                                $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                                if ($examination_question_id != null) {
                                    $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                } else {
                                    $modelExaminationQuestion = new ExaminationQuestion;
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                }

                            }
                        } else {
                            //add single question
                            $modelQuestion = new Question;
                            $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                            $modelQuestion->year_group_id = $request->year_group_id;
                            $modelQuestion->subject_id = $request->subject_id;
                            $modelQuestion->lesson_id = $request->lesson_id ?? null;
                            $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                            $modelQuestion->question = $questionOptions[$i]['question'];
                            $modelQuestion->level = $questionOptions[$i]['level'];
                            $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                            $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                            $modelQuestion->created_by = $request->created_by;
                            $modelQuestion->creator_type = $request->creator_type;
                            $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                            $modelQuestion->save();

                            $question_id = $modelQuestion->question_id;

                            $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                            if ($examination_question_id != null) {
                                $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                $modelExaminationQuestion->examination_id = $examination_id;
                                $modelExaminationQuestion->question_id = $question_id;
                                $modelExaminationQuestion->page_id = 1;
                                $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                $modelExaminationQuestion->save();

                                $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                            } else {
                                $modelExaminationQuestion = new ExaminationQuestion;
                                $modelExaminationQuestion->examination_id = $examination_id;
                                $modelExaminationQuestion->question_id = $question_id;
                                $modelExaminationQuestion->page_id = 1;
                                $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                $modelExaminationQuestion->save();

                                // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                            }
                        }
                    }
                }
            }
            $tot_time_questions = ExaminationQuestion::where('examination_id', $examination_id)->sum('time_inseconds');

            // $total_time_minutes = ($tot_time_questions / 60);
            $init = $tot_time_questions;
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $total_time = "$hours:$minutes:$seconds";
            // dd($total_time);
            //update examination data
            $modelExamination = Examination::find($examination_id);

            $modelExamination->total_time = $total_time;

            $modelExamination->save();
            DB::commit();
            // $response = Examination::find($examination_id);
            $response = $modelExamination;
            // dd($response->options);

        } catch (\Exception $e) {
            // throw ($e);
            DB::rollback();

        }
        return $response;
    }

    public function qb_createExamWithQuestionsNSubquestions(Request $request)
    {
        // dd($request->all());
        $response = null;
        $myfile = fopen("linked-question.txt", "a") or die("Unable to open file!");
        //Begin database transaction
        DB::beginTransaction();
        try {
            $examination_id = $request->examination_id ?? null;

            $existingExamQuestionIds = array();
            if ($examination_id != null) {
                //modify examination
                $model = Examination::find($examination_id);
                $model->examination_type = $request->examination_type;
                $model->name = $request->name;
                $model->year_group_id = $request->year_group_id ?? null;
                $model->subject_id = $request->subject_id ?? null;
                $model->lesson_id = $request->lesson_id ?? null;
                // $model->topic_id = $request->topic_id ?? null;
                // $model->sub_topic_id = $request->sub_topic_id ?? null;
                $model->created_by = $request->created_by ?? null;
                $model->creator_type = $request->creator_type ?? null;
                $model->homework = $request->homework ?? 0;
                $model->status = $request->examination_status ?? GlobalVars::EXAM_DRAFT_STATUS;
                $model->save();

                $examination_id = $model->examination_id;
                // dd($examination_id);
                $txt = json_encode($model);
                fwrite($myfile, $txt);
                fwrite($myfile, "*Modify ExamID* \n");

                $existingExamQuestionIds = ExaminationQuestion::where('examination_id', $examination_id)->pluck('examination_question_id')->toArray();

                // dd($existingExamQuestionIds);

            } else {
                //add examination

                $model = new Examination;
                $model->examination_type = $request->examination_type;
                $model->name = $request->name;
                $model->year_group_id = $request->year_group_id ?? null;
                $model->subject_id = $request->subject_id ?? null;
                $model->lesson_id = $request->lesson_id ?? null;
                // $model->topic_id = $request->topic_id ?? null;
                // $model->sub_topic_id = $request->sub_topic_id ?? null;
                $model->created_by = $request->created_by ?? null;
                $model->creator_type = $request->creator_type ?? null;
                $model->homework = $request->homework ?? 0;
                $model->status = $request->examination_status ?? GlobalVars::EXAM_DRAFT_STATUS;
                $model->save();

                $examination_id = $model->examination_id;
                $txt = json_encode($model);
                fwrite($myfile, $txt);
                fwrite($myfile, "*ExamID* \n");

            }
            $questionOptions = $request->questions ?? array();
            if (count($questionOptions) > 0) {
                $txt = json_encode($questionOptions);
                fwrite($myfile, $txt);
                fwrite($myfile, "*Question Array* \n");
                //loop questions in request
                for ($i = 0; $i < count($questionOptions); $i++) {
                    //create question master
                    fwrite($myfile, "*Inside question loop* \n");

                    $question_id = $questionOptions[$i]['question_id'] ?? null;
                    fwrite($myfile, "*Question ID *" . $question_id . " \n");

                    $questionInfo = [
                        'question' => $questionOptions[$i]['question'] ?? '',
                        'question_type' => $questionOptions[$i]['question_type'] ?? 'linked',
                        'level' => $questionOptions[$i]['level'] ?? '',
                        'require_file_upload' => $questionOptions[$i]['require_file_upload'] ?? 0,
                        'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                    ];

                    if ($question_id != '') {
                        fwrite($myfile, "*Question ID not null::*" . $question_id . " \n");
                        if ($questionOptions[$i]['question_type'] == 'linked') {
                            //have subqustion child array in request - parent question
                            //edit parent question
                            $txt = json_encode($questionOptions[$i]);
                            fwrite($myfile, $txt);
                            fwrite($myfile, "*Linked Question condition* \n");

                            //existing question bank question selected
                            $existingQuestion = Question::find($question_id);
                            // dd($existingQuestion);
                            if (!empty($existingQuestion)) {
                                fwrite($myfile, "*Existing question found* \n");
                                $question_id = $existingQuestion->question_id;
                                // dd($question_id);
                                //question id not in use by other examination
                                $checkExam = ExaminationQuestion::where('examination_id', '<>', $examination_id)->where('question_id', $question_id)->first();
                                // dd($checkExam);
                                if (!empty($checkExam)) {
                                    //create new question element as edit is forced
                                    fwrite($myfile, "*create new question element as edit is forced* \n");
                                    // dd($existingQuestion);
                                    $modelQuestion = new Question;
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'];
                                    $modelQuestion->year_group_id = $request->year_group_id ?? null;
                                    $modelQuestion->subject_id = $request->subject_id ?? null;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                                    $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by ?? null;
                                    $modelQuestion->creator_type = $request->creator_type ?? null;
                                    $modelQuestion->linked_question = 1;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                    $parent_question_id = $question_id;
                                    fwrite($myfile, "*QuestionB saved::" . $question_id . "* \n");
                                    $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;
                                    if ($examination_question_id != null) {
                                        fwrite($myfile, "*check exam exam quetion id not null* \n");
                                        $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                        $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                        $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                        $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                        $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                        $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();
                                        fwrite($myfile, "*Exam question updated::" . $examination_question_id . "* \n");

                                        if (count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                            unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                        }

                                        // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                    } else {
                                        fwrite($myfile, "*check exam exam quetion id is null* \n");
                                        $modelExaminationQuestion = new ExaminationQuestion;
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                        $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                        $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                        $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                        $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                        $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->linked_question = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();

                                        $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                        $parent_examination_question_id = $examination_question_id;
                                        fwrite($myfile, "*Exam Question saved::" . $examination_question_id . "* \n");
                                    }
                                    //add subquestion under parent
                                    if (is_array($questionOptions[$i]['subquestions']) && count($questionOptions[$i]['subquestions']) > 0) {
                                        $subquestions = $questionOptions[$i]['subquestions'];
                                        $txt = json_encode($subquestions);
                                        fwrite($myfile, $txt);
                                        fwrite($myfile, "*check exam not null - Sub Question:: leid::" . $examination_question_id . "* \n");
                                        for ($j = 0; $j < count($subquestions); $j++) {
                                            $questionInfoSub = [
                                                'question' => $subquestions[$j]['question'] ?? null,
                                                'question_type' => $subquestions[$j]['question_type'] ?? null,
                                                'level' => $subquestions[$j]['level'] ?? null,
                                                'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                                'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                            ];
                                            if ($subquestions[$j]['question_id'] != null) {
                                                fwrite($myfile, "*Existing Sub Question::" . $subquestions[$j]['question_id'] . "* \n");
                                                $modelQuestionSub = Question::find($subquestions[$j]['question_id']);
                                            } else {
                                                $modelQuestionSub = new Question;
                                                fwrite($myfile, "*New Sub Question::* \n");
                                            }
                                            $modelQuestionSub->question_type = $subquestions[$j]['question_type'] ?? null;
                                            $modelQuestionSub->year_group_id = $request->year_group_id ?? null;
                                            $modelQuestionSub->subject_id = $request->subject_id ?? null;
                                            $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                            $modelQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                            $modelQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                            $modelQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                            $modelQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                            $modelQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                            $modelQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                            $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                            $modelQuestionSub->question = $subquestions[$j]['question'] ?? null;
                                            $modelQuestionSub->level = $subquestions[$j]['level'] ?? null;
                                            $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                            $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                            $modelQuestionSub->created_by = $request->created_by ?? null;
                                            $modelQuestionSub->creator_type = $request->creator_type ?? null;
                                            $modelQuestionSub->parent_question_id = $parent_question_id;
                                            $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                            $modelQuestionSub->save();

                                            $sub_question_id = $modelQuestionSub->question_id;

                                            fwrite($myfile, "*Saved Sub Question::" . $sub_question_id . "* \n");

                                            $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                            if ($sub_examination_question_id != null) {
                                                fwrite($myfile, "*Sub Exam Question::" . $sub_examination_question_id . "* \n");
                                                //nedd revsit
                                                $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                                $modelExaminationQuestionSub->examination_id = $examination_id;
                                                $modelExaminationQuestionSub->question_id = $sub_question_id;
                                                $modelExaminationQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                                $modelExaminationQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                                $modelExaminationQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                                $modelExaminationQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                                $modelExaminationQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                                $modelExaminationQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();
                                                fwrite($myfile, "*Saved Sub Exam Question::" . $sub_examination_question_id . "* \n");

                                                if (count($existingExamQuestionIds) > 0 && in_array($sub_examination_question_id, $existingExamQuestionIds)) {
                                                    unset($existingExamQuestionIds[array_search($sub_examination_question_id, $existingExamQuestionIds)]);
                                                }

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                            } else {
                                                fwrite($myfile, "*New Sub Exam Question::* \n");
                                                $modelExaminationQuestionSub = new ExaminationQuestion;
                                                $modelExaminationQuestionSub->examination_id = $examination_id;
                                                $modelExaminationQuestionSub->question_id = $sub_question_id;
                                                $modelExaminationQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                                $modelExaminationQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                                $modelExaminationQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                                $modelExaminationQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                                $modelExaminationQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                                $modelExaminationQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id;
                                                $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                                fwrite($myfile, "*Saved New Sub Exam Question::" . $modelExaminationQuestionSub->examination_question_id . "* \n");
                                            }

                                        }
                                    }
                                } else {
                                    //update existing question element as edit is forced & is not in use by other
                                    fwrite($myfile, "*check exam  null - update existing question element as edit is forced & is not in use by other* \n");
                                    // dd($question_id);
                                    $modelQuestion = Question::find($question_id);
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? null;
                                    $modelQuestion->year_group_id = $request->year_group_id ?? null;
                                    $modelQuestion->subject_id = $request->subject_id ?? null;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                                    $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by ?? null;
                                    $modelQuestion->creator_type = $request->creator_type ?? null;
                                    $modelQuestion->linked_question = 1;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                    $parent_question_id = $question_id;
                                    // dd($modelQuestion);
                                    fwrite($myfile, "*check exam  null - Updated Sub Question::" . $question_id . "* \n");
                                    $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;
                                    // dd($examination_question_id);
                                    if ($examination_question_id != null) {
                                        fwrite($myfile, "*check exam  null - not null examination_question_id::" . $examination_question_id . "* \n");
                                        $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);

                                        // dd($modelExaminationQuestion);

                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                        $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                        $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                        $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                        $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                        $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();
                                        $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                        $parent_examination_question_id = $examination_question_id;

                                        if (count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                            unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                        }
                                        // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                    } else {
                                        // dd('add eqid');
                                        fwrite($myfile, "*check exam  null -  null examination_question_id* \n");
                                        $modelExaminationQuestion = new ExaminationQuestion;
                                        $modelExaminationQuestion->examination_id = $examination_id;
                                        $modelExaminationQuestion->question_id = $question_id;
                                        $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                        $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                        $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                        $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                        $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                        $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                        $modelExaminationQuestion->page_id = 1;
                                        $modelExaminationQuestion->linked_question = 1;
                                        $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                        $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                        $modelExaminationQuestion->save();

                                        $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                        $parent_examination_question_id = $examination_question_id;
                                    }
                                    //add subquestion under parent
                                    if (is_array($questionOptions[$i]['subquestions']) && $questionOptions[$i]['subquestions'] != null && count($questionOptions[$i]['subquestions']) > 0) {
                                        $subquestions = $questionOptions[$i]['subquestions'];
                                        // dd($subquestions);
                                        $txt = json_encode($subquestions);
                                        fwrite($myfile, $txt);
                                        fwrite($myfile, "*check exam null - Sub Question:: leid::" . $examination_question_id . "* \n");

                                        for ($j = 0; $j < count($subquestions); $j++) {
                                            $questionInfoSub = [
                                                'question' => $subquestions[$j]['question'] ?? null,
                                                'question_type' => $subquestions[$j]['question_type'] ?? null,
                                                'level' => $subquestions[$j]['level'] ?? null,
                                                'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                                'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                            ];
                                            // dd($subquestions[$j]);
                                            if ($subquestions[$j]['question_id'] ?? '' != null) {
                                                $modelQuestionSub = Question::find($subquestions[$j]['question_id']);
                                                fwrite($myfile, "*check exam null - Sub Question:: id::" . $subquestions[$j]['question_id'] . "* \n");
                                            } else {
                                                $modelQuestionSub = new Question;
                                                // dd($modelQuestionSub);
                                                fwrite($myfile, "*check exam null - New Sub Question* \n");
                                            }

                                            // dd($modelQuestionSub);

                                            $modelQuestionSub->question_type = $subquestions[$j]['question_type'] ?? null;
                                            $modelQuestionSub->year_group_id = $request->year_group_id ?? null;
                                            $modelQuestionSub->subject_id = $request->subject_id ?? null;
                                            $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                            $modelQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                            $modelQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                            $modelQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                            $modelQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                            $modelQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                            $modelQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                            $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                            $modelQuestionSub->question = $subquestions[$j]['question'] ?? null;
                                            $modelQuestionSub->level = $subquestions[$j]['level'] ?? null;
                                            $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                            $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                            $modelQuestionSub->created_by = $request->created_by ?? null;
                                            $modelQuestionSub->creator_type = $request->creator_type ?? null;
                                            $modelQuestionSub->parent_question_id = $parent_question_id;
                                            $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                            $modelQuestionSub->save();

                                            $sub_question_id = $modelQuestionSub->question_id;
                                            // dd($sub_question_id);
                                            fwrite($myfile, "*check exam null - Saved Sub Question::" . $sub_question_id . "* \n");
                                            $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                            if ($sub_examination_question_id != null) {
                                                fwrite($myfile, "*check exam null - Exam Sub Question::" . $sub_examination_question_id . "* \n");

                                                $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                                $modelExaminationQuestionSub->examination_id = $examination_id ?? null;
                                                $modelExaminationQuestionSub->question_id = $sub_question_id ?? null;
                                                $modelExaminationQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                                $modelExaminationQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                                $modelExaminationQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                                $modelExaminationQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                                $modelExaminationQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                                $modelExaminationQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                if (count($existingExamQuestionIds) > 0 && in_array($sub_examination_question_id, $existingExamQuestionIds)) {
                                                    unset($existingExamQuestionIds[array_search($sub_examination_question_id, $existingExamQuestionIds)]);
                                                }
                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                                fwrite($myfile, "*check exam null - Saved Exam Sub Question::" . $modelExaminationQuestionSub->examination_question_id . "* \n");
                                            } else {
                                                fwrite($myfile, "*check exam null - new Exam Sub Question* \n");

                                                $modelExaminationQuestionSub = new ExaminationQuestion;
                                                $modelExaminationQuestionSub->examination_id = $examination_id ?? null;
                                                $modelExaminationQuestionSub->question_id = $sub_question_id ?? null;
                                                $modelExaminationQuestionSub->page_id = 1;
                                                $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id ?? null;
                                                $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                                $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                                $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                                $modelExaminationQuestionSub->save();

                                                // $sub_examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                                fwrite($myfile, "*check exam null - Saved new Exam Sub Question::" . $modelExaminationQuestionSub->examination_question_id . "* \n");
                                            }

                                        }
                                    }
                                }

                            } else {
                                //wrong master question id provided - create new question master
                                fwrite($myfile, "*wrong master question id provided - create new question master* \n");
                                $modelQuestion = new Question;
                                $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? null;
                                $modelQuestion->year_group_id = $request->year_group_id ?? null;
                                $modelQuestion->subject_id = $request->subject_id ?? null;
                                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                                $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                                $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                $modelQuestion->created_by = $request->created_by ?? null;
                                $modelQuestion->creator_type = $request->creator_type ?? null;
                                $modelQuestion->linked_question = 1;
                                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                $modelQuestion->save();

                                $question_id = $modelQuestion->question_id;
                                $parent_question_id = $question_id;
                                if ($examination_question_id != null) {
                                    $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    if (count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                        unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                    }
                                    $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                } else {
                                    $modelExaminationQuestion = new ExaminationQuestion;
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->linked_question = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                    $parent_examination_question_id = $examination_question_id;
                                }
                                //add subquestion under parent
                                if (is_array($questionOptions[$i]['subquestions']) && $questionOptions[$i]['subquestions'] != null && count($questionOptions[$i]['subquestions']) > 0) {
                                    $subquestions = $questionOptions[$i]['subquestions'];

                                    for ($j = 0; $j < count($subquestions); $j++) {
                                        $questionInfoSub = [
                                            'question' => $subquestions[$j]['question'] ?? null,
                                            'question_type' => $subquestions[$j]['question_type'] ?? null,
                                            'level' => $subquestions[$j]['level'] ?? null,
                                            'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                            'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                        ];
                                        if ($subquestions[$j]['question_id'] != null) {
                                            $modelQuestionSub = Question::find($subquestions[$j]['question_id']);
                                        } else {
                                            $modelQuestionSub = new Question;

                                        }
                                        $modelQuestionSub->question_type = $subquestions[$j]['question_type'] ?? null;
                                        $modelQuestionSub->year_group_id = $request->year_group_id ?? null;
                                        $modelQuestionSub->subject_id = $request->subject_id ?? null;
                                        $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                        $modelQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                        $modelQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                        $modelQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                        $modelQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                        $modelQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                        $modelQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                        $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                        $modelQuestionSub->question = $subquestions[$j]['question'] ?? null;
                                        $modelQuestionSub->level = $subquestions[$j]['level'] ?? null;
                                        $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                        $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                        $modelQuestionSub->created_by = $request->created_by ?? null;
                                        $modelQuestionSub->creator_type = $request->creator_type ?? null;
                                        $modelQuestionSub->parent_question_id = $parent_question_id;
                                        $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                        $modelQuestionSub->save();

                                        $sub_question_id = $modelQuestionSub->question_id;

                                        $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                        if ($sub_examination_question_id != null) {
                                            $modelExaminationQuestionSub = ExaminationQuestion::find($sub_examination_question_id);
                                            $modelExaminationQuestionSub->examination_id = $examination_id;
                                            $modelExaminationQuestionSub->question_id = $sub_question_id;
                                            $modelExaminationQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                            $modelExaminationQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                            $modelExaminationQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                            $modelExaminationQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                            $modelExaminationQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                            $modelExaminationQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                            $modelExaminationQuestionSub->page_id = 1;
                                            $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                            $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                            $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                            $modelExaminationQuestionSub->save();

                                            // $examination_question_id = $modelExaminationQuestionSub->examination_question_id;

                                            if (count($existingExamQuestionIds) > 0 && in_array($sub_examination_question_id, $existingExamQuestionIds)) {
                                                unset($existingExamQuestionIds[array_search($sub_examination_question_id, $existingExamQuestionIds)]);
                                            }
                                        } else {
                                            $modelExaminationQuestionSub = new ExaminationQuestion;
                                            $modelExaminationQuestionSub->examination_id = $examination_id;
                                            $modelExaminationQuestionSub->question_id = $sub_question_id;
                                            $modelExaminationQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                            $modelExaminationQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                            $modelExaminationQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                            $modelExaminationQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                            $modelExaminationQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                            $modelExaminationQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                            $modelExaminationQuestionSub->page_id = 1;
                                            $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id;
                                            $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                            $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                            $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                            $modelExaminationQuestionSub->save();

                                            // $examination_question_id = $modelExaminationQuestionSub->examination_question_id;
                                        }

                                    }
                                }
                            }

                        } else {

                            //single question with no parent or child
                            //edit single question
                            //existing question bank question selected
                            $existingQuestion = Question::find($question_id);
                            // dd($existingQuestion);
                            if (!empty($existingQuestion)) {
                                $question_id = $existingQuestion->question_id;

                                //question id not in use by other examination
                                $checkExam = ExaminationQuestion::where('examination_id', '<>', $examination_id)->where('question_id', $question_id)->first();
                                if (!empty($checkExam)) {
                                    //create new question element as edit is forced
                                    $modelQuestion = new Question;
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? null;
                                    $modelQuestion->year_group_id = $request->year_group_id ?? null;
                                    $modelQuestion->subject_id = $request->subject_id ?? null;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                                    $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by ?? null;
                                    $modelQuestion->creator_type = $request->creator_type ?? null;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                } else {
                                    //update existing question element as edit is forced & is not in use by other
                                    $modelQuestion = Question::find($question_id);
                                    $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? null;
                                    $modelQuestion->year_group_id = $request->year_group_id ?? null;
                                    $modelQuestion->subject_id = $request->subject_id ?? null;
                                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                    $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                                    $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                                    $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                    $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestion->created_by = $request->created_by ?? null;
                                    $modelQuestion->creator_type = $request->creator_type ?? null;
                                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestion->save();

                                    $question_id = $modelQuestion->question_id;
                                }

                                $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                                if ($examination_question_id != null) {
                                    $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    if (is_array($existingExamQuestionIds) && count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                        unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                    }
                                    // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                } else {
                                    $modelExaminationQuestion = new ExaminationQuestion;
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                }

                            } else {
                                //wrong master question id provided - create new question master
                                $modelQuestion = new Question;
                                $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? null;
                                $modelQuestion->year_group_id = $request->year_group_id ?? null;
                                $modelQuestion->subject_id = $request->subject_id ?? null;
                                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                                $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                                $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                                $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                                $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                                $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                $modelQuestion->created_by = $request->created_by ?? null;
                                $modelQuestion->creator_type = $request->creator_type ?? null;
                                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                                $modelQuestion->save();

                                $question_id = $modelQuestion->question_id;

                                $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;

                                if ($examination_question_id != null) {
                                    $modelExaminationQuestion = ExaminationQuestion::find($examination_question_id);
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    if (count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                        unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                    }
                                    // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                } else {
                                    $modelExaminationQuestion = new ExaminationQuestion;
                                    $modelExaminationQuestion->examination_id = $examination_id;
                                    $modelExaminationQuestion->question_id = $question_id;
                                    $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                    $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                    $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                    $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                    $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                    $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                    $modelExaminationQuestion->page_id = 1;
                                    $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                    $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                    $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                    $modelExaminationQuestion->save();

                                    // $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
                                }

                            }

                        }
                    } else {
                        fwrite($myfile, "*Question ID null* \n");
                        if ($questionOptions[$i]['question_type'] == 'linked') {
                            //have subqustion child array in request - parent question
                            //add parent question

                            $txt = json_encode($questionOptions[$i]);
                            fwrite($myfile, $txt);
                            fwrite($myfile, "*Linked Question condition* \n");

                            $modelQuestion = new Question;
                            $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? '';
                            $modelQuestion->year_group_id = $request->year_group_id ?? null;
                            $modelQuestion->subject_id = $request->subject_id ?? null;
                            $modelQuestion->lesson_id = $request->lesson_id ?? null;
                            $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                            $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                            $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                            $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                            $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                            $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                            $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                            $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                            $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                            $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                            $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                            $modelQuestion->created_by = $request->created_by ?? null;
                            $modelQuestion->creator_type = $request->creator_type ?? null;
                            $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                            $modelQuestion->linked_question = 1;
                            $modelQuestion->save();

                            $question_id = $modelQuestion->question_id;
                            $parent_question_id = $question_id; //parent question - question bank
                            $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;
                            //create question exam
                            if ($examination_question_id != null) {} else {
                                $modelExaminationQuestion = new ExaminationQuestion;
                                $modelExaminationQuestion->examination_id = $examination_id;
                                $modelExaminationQuestion->question_id = $question_id;
                                $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                $modelExaminationQuestion->page_id = 1;
                                $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                $modelExaminationQuestion->question_info = json_encode($questionInfo);
                                $modelExaminationQuestion->linked_question = 1;
                                $modelExaminationQuestion->save();
                                $examination_question_id = $modelExaminationQuestion->examination_question_id;
                                $parent_examination_question_id = $examination_question_id; //parent question - exam question
                                if (count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                    unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                }
                            }
                            //add subquestion under parent
                            if (is_array($questionOptions[$i]['subquestions']) && count($questionOptions[$i]['subquestions']) > 0) {
                                $subquestions = $questionOptions[$i]['subquestions'];
                                for ($j = 0; $j < count($subquestions); $j++) {
                                    $questionInfoSub = [
                                        'question' => $subquestions[$j]['question'] ?? null,
                                        'question_type' => $subquestions[$j]['question_type'] ?? null,
                                        'level' => $subquestions[$j]['level'] ?? null,
                                        'require_file_upload' => $subquestions[$j]['require_file_upload'] ?? 0,
                                        'source' => $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT,
                                    ];
                                    //create sub question with parent id - question bank
                                    $modelQuestionSub = new Question;
                                    $modelQuestionSub->question_type = $subquestions[$j]['question_type'];
                                    $modelQuestionSub->year_group_id = $request->year_group_id ?? null;
                                    $modelQuestionSub->subject_id = $request->subject_id ?? null;
                                    $modelQuestionSub->lesson_id = $request->lesson_id ?? null;
                                    $modelQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                    $modelQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                    $modelQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                    $modelQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                    $modelQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                    $modelQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                    $modelQuestionSub->question_category_id = $subquestions[$j]['question_category_id'] ?? null;
                                    $modelQuestionSub->question = $subquestions[$j]['question'] ?? null;
                                    $modelQuestionSub->level = $subquestions[$j]['level'] ?? null;
                                    $modelQuestionSub->require_file_upload = $subquestions[$j]['require_file_upload'] ?? 0;
                                    $modelQuestionSub->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                                    $modelQuestionSub->created_by = $request->created_by ?? null;
                                    $modelQuestionSub->creator_type = $request->creator_type ?? null;
                                    $modelQuestionSub->parent_question_id = $parent_question_id ?? null;
                                    $modelQuestionSub->status = GlobalVars::ACTIVE_STATUS;
                                    $modelQuestionSub->save();

                                    $sub_question_id = $modelQuestionSub->question_id;
                                    $sub_examination_question_id = $subquestions[$j]['examination_question_id'] ?? null;

                                    if ($sub_examination_question_id != null) {} else {
                                        $modelExaminationQuestionSub = new ExaminationQuestion;
                                        $modelExaminationQuestionSub->examination_id = $examination_id ?? null;
                                        $modelExaminationQuestionSub->question_id = $sub_question_id ?? null;
                                        $modelExaminationQuestionSub->topic_id = $subquestions[$j]['topic_id'] ?? null;
                                        $modelExaminationQuestionSub->sub_topic_id = $subquestions[$j]['sub_topic_id'] ?? null;
                                        $modelExaminationQuestionSub->tc = $subquestions[$j]['tc'] ?? 0;
                                        $modelExaminationQuestionSub->ms = $subquestions[$j]['ms'] ?? 0;
                                        $modelExaminationQuestionSub->ps = $subquestions[$j]['ps'] ?? 0;
                                        $modelExaminationQuestionSub->at = $subquestions[$j]['at'] ?? 0;
                                        $modelExaminationQuestionSub->page_id = 1;
                                        $modelExaminationQuestionSub->parent_examination_question_id = $parent_examination_question_id ?? null;
                                        $modelExaminationQuestionSub->time_inseconds = ($subquestions[$j]['time_inseconds'] ?? 0) * 60;
                                        $modelExaminationQuestionSub->point = $subquestions[$j]['point'] ?? 0;
                                        $modelExaminationQuestionSub->question_info = json_encode($questionInfoSub);

                                        $modelExaminationQuestionSub->save();

                                        if (count($existingExamQuestionIds) > 0 && in_array($sub_examination_question_id, $existingExamQuestionIds)) {
                                            unset($existingExamQuestionIds[array_search($sub_examination_question_id, $existingExamQuestionIds)]);
                                        }

                                    }

                                } //loop sub question
                            }

                        } else {
                            //single question with no parent or child
                            //add single question
                            $modelQuestion = new Question;
                            $modelQuestion->question_type = $questionOptions[$i]['question_type'] ?? null;
                            $modelQuestion->year_group_id = $request->year_group_id ?? null;
                            $modelQuestion->subject_id = $request->subject_id ?? null;
                            $modelQuestion->lesson_id = $request->lesson_id ?? null;
                            $modelQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                            $modelQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                            $modelQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                            $modelQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                            $modelQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                            $modelQuestion->at = $questionOptions[$i]['at'] ?? 0;
                            $modelQuestion->question_category_id = $questionOptions[$i]['question_category_id'] ?? null;
                            $modelQuestion->question = $questionOptions[$i]['question'] ?? null;
                            $modelQuestion->level = $questionOptions[$i]['level'] ?? null;
                            $modelQuestion->require_file_upload = $questionOptions[$i]['require_file_upload'] ?? 0;
                            $modelQuestion->source = $request->examination_type ?? GlobalVars::EXAMINATION_TYPE_ASSESSMENT;
                            $modelQuestion->created_by = $request->created_by ?? null;
                            $modelQuestion->creator_type = $request->creator_type ?? null;
                            $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                            $modelQuestion->save();

                            $question_id = $modelQuestion->question_id;
                            $examination_question_id = $questionOptions[$i]['examination_question_id'] ?? null;
                            //create question exam
                            if ($examination_question_id != null) {} else {
                                $modelExaminationQuestion = new ExaminationQuestion;
                                $modelExaminationQuestion->examination_id = $examination_id ?? null;
                                $modelExaminationQuestion->question_id = $question_id ?? null;
                                $modelExaminationQuestion->topic_id = $questionOptions[$i]['topic_id'] ?? null;
                                $modelExaminationQuestion->sub_topic_id = $questionOptions[$i]['sub_topic_id'] ?? null;
                                $modelExaminationQuestion->tc = $questionOptions[$i]['tc'] ?? 0;
                                $modelExaminationQuestion->ms = $questionOptions[$i]['ms'] ?? 0;
                                $modelExaminationQuestion->ps = $questionOptions[$i]['ps'] ?? 0;
                                $modelExaminationQuestion->at = $questionOptions[$i]['at'] ?? 0;
                                $modelExaminationQuestion->page_id = 1;
                                $modelExaminationQuestion->time_inseconds = ($questionOptions[$i]['time_inseconds'] ?? 0) * 60;
                                $modelExaminationQuestion->point = $questionOptions[$i]['point'] ?? 0;
                                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                                $modelExaminationQuestion->save();

                                if (count($existingExamQuestionIds) > 0 && in_array($examination_question_id, $existingExamQuestionIds)) {
                                    unset($existingExamQuestionIds[array_search($examination_question_id, $existingExamQuestionIds)]);
                                }
                            }
                        }
                    }

                } //end loop
            }
            $existingExamQuestionIds = array_values($existingExamQuestionIds);
            // dd($existingExamQuestionIds);
            if (count($existingExamQuestionIds) > 0) {
                ExaminationQuestion::whereIn('examination_question_id', $existingExamQuestionIds)->delete();
            }

            $tot_time_questions = ExaminationQuestion::where('examination_id', $examination_id)->sum('time_inseconds');

            // $total_time_minutes = ($tot_time_questions / 60);
            $init = $tot_time_questions;
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $total_time = "$hours:$minutes:$seconds";
            // dd($total_time);
            //update examination data
            $modelExamination = Examination::find($examination_id);

            $modelExamination->total_time = $total_time;

            $modelExamination->save();
            DB::commit();
            $response = Examination::find($examination_id);
            // $response = $modelExamination;
        } catch (\Exception $e) {
            fwrite($myfile, "*Error* \n");
            $txt = json_encode($e);
            fwrite($myfile, $txt);
            fwrite($myfile, $e->getMessage());
            DB::rollback();
            // throw ($e->getMessage());

        }
        fclose($myfile);
        return $response;
    }

    public function qb_creatorExaminationPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;
        $created_by = $request->created_by;
        $examination_type = $request->examination_type;

        if ($search_text != '') {
            $listing = Examination::where('examination_type', $examination_type)->where('created_by', $created_by)->where('name', 'like', '%' . $search_text . '%')->with(['subject', 'subject.yeargroup', 'subject.academicyear'])->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
        } else {
            $listing = Examination::where('examination_type', $examination_type)->where('created_by', $created_by)->with(['subject', 'subject.yeargroup', 'subject.academicyear'])->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);
        }

        return $listing;
    }

    public function qb_allCreatorExaminations(Request $request)
    {
        $created_by = $request->created_by;
        $examination_type = $request->examination_type;
        $homework = $request->homework ?? '0';

        if ($homework > 0) {
            $listing = Examination::where('homework', 1)->where('created_by', $created_by)->where('status', GlobalVars::ACTIVE_STATUS)->with(['subject', 'subject.yeargroup', 'subject.academicyear'])->get();
        } else {
            $listing = Examination::where('homework', 0)->where('examination_type', $examination_type)->where('created_by', $created_by)->where('status', GlobalVars::ACTIVE_STATUS)->with(['subject', 'subject.yeargroup', 'subject.academicyear'])->get();
        }

        return $listing;
    }

    public function qb_createOrUpdateExaminationQuestion(Request $request)
    {
        // dd($request->all());
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {

            $questionInfo = [
                'question' => $request->question,
                'question_type' => $request->question_type,
                'level' => $request->level,
                'require_file_upload' => $request->require_file_upload ?? 0,
                'source' => $request->source ?? 'Q',
                'options' => $request->options ?? array(),
            ];
            if ($request->question_type == 'text') {
                $questionInfo = [
                    'question' => $request->question,
                    'question_type' => $request->question_type,
                    'level' => $request->level,
                    'require_file_upload' => $request->require_file_upload ?? 0,
                    'source' => $request->source ?? 'Q',
                ];

            }
            // dd(json_encode($questionInfo));
            $question_id = $request->question_id ?? null;
            if (isset($request->question_id) && $request->question_id != null) {

                //existing question bank question selected
                $existingQuestion = Question::find($request->question_id);
                if (!empty($existingQuestion)) {
                    // dd('existing question');
                    $question_id = $existingQuestion->question_id;

                    //question id not in use by other examination
                    $checkExam = ExaminationQuestion::where('examination_id', '<>', $request->examination_id)->where('question_id', $question_id)->first();

                    if (!empty($checkExam)) {
                        // dd($checkExam);
                        //create new question element as edit is forced
                        $modelQuestion = new Question;
                        $modelQuestion->question_type = $request->question_type;
                        $modelQuestion->year_group_id = $request->year_group_id;
                        $modelQuestion->subject_id = $request->subject_id;
                        $modelQuestion->lesson_id = $request->lesson_id ?? null;
                        $modelQuestion->topic_id = $request->topic_id ?? null;
                        $modelQuestion->sub_topic_id = $request->sub_topic_id ?? null;
                        $modelQuestion->tc = $request->tc ?? 0;
                        $modelQuestion->ms = $request->ms ?? 0;
                        $modelQuestion->ps = $request->ps ?? 0;
                        $modelQuestion->at = $request->at ?? 0;
                        $modelQuestion->question_category_id = $request->question_category_id ?? null;
                        $modelQuestion->question = $request->question;
                        $modelQuestion->level = $request->level;
                        $modelQuestion->require_file_upload = $request->require_file_upload ?? 0;
                        $modelQuestion->source = $request->source ?? 'Q';
                        $modelQuestion->created_by = $request->created_by;
                        $modelQuestion->creator_type = $request->creator_type;
                        $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                        $modelQuestion->save();

                        $question_id = $modelQuestion->question_id;
                        if ($request->question_type != 'text') {
                            $questionOptions = $request->options ?? array();
                            if (count($questionOptions) > 0) {
                                for ($i = 0; $i < count($questionOptions); $i++) {

                                    $modelOption = new QuestionOption;
                                    $modelOption->question_id = $question_id;
                                    $modelOption->option_value = $questionOptions[$i]['option_value'];
                                    $modelOption->is_correct = $questionOptions[$i]['is_correct'];

                                    $modelOption->save();
                                }
                            }
                        }

                    } else {
                        //update existing question element as edit is forced & is not in use by other
                        $modelQuestion = Question::find($question_id);
                        $modelQuestion->question_type = $request->question_type;
                        $modelQuestion->year_group_id = $request->year_group_id;
                        $modelQuestion->subject_id = $request->subject_id;
                        $modelQuestion->lesson_id = $request->lesson_id ?? null;
                        $modelQuestion->topic_id = $request->topic_id ?? null;
                        $modelQuestion->sub_topic_id = $request->sub_topic_id ?? null;
                        $modelQuestion->tc = $request->tc ?? 0;
                        $modelQuestion->ms = $request->ms ?? 0;
                        $modelQuestion->ps = $request->ps ?? 0;
                        $modelQuestion->at = $request->at ?? 0;
                        $modelQuestion->question_category_id = $request->question_category_id ?? null;
                        $modelQuestion->question = $request->question;
                        $modelQuestion->level = $request->level;
                        $modelQuestion->require_file_upload = $request->require_file_upload ?? 0;
                        $modelQuestion->source = $request->source ?? 'Q';
                        $modelQuestion->created_by = $request->created_by;
                        $modelQuestion->creator_type = $request->creator_type;
                        $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                        $modelQuestion->save();

                        QuestionOption::where('question_id', $question_id)->delete();

                        $question_id = $modelQuestion->question_id;
                        if ($request->question_type != 'text') {
                            $questionOptions = $request->options ?? array();
                            if (count($questionOptions) > 0) {
                                for ($i = 0; $i < count($questionOptions); $i++) {

                                    $modelOption = new QuestionOption;
                                    $modelOption->question_id = $question_id;
                                    $modelOption->option_value = $questionOptions[$i]['option_value'];
                                    $modelOption->is_correct = $questionOptions[$i]['is_correct'];

                                    $modelOption->save();
                                }
                            }
                        }
                    }

                } else {
                    //wrong master question id provided - create new question master
                    // dd('existing new question');
                    $modelQuestion = new Question;
                    $modelQuestion->question_type = $request->question_type;
                    $modelQuestion->year_group_id = $request->year_group_id;
                    $modelQuestion->subject_id = $request->subject_id;
                    $modelQuestion->lesson_id = $request->lesson_id ?? null;
                    $modelQuestion->topic_id = $request->topic_id ?? null;
                    $modelQuestion->sub_topic_id = $request->sub_topic_id ?? null;
                    $modelQuestion->tc = $request->tc ?? 0;
                    $modelQuestion->ms = $request->ms ?? 0;
                    $modelQuestion->ps = $request->ps ?? 0;
                    $modelQuestion->at = $request->at ?? 0;
                    $modelQuestion->question_category_id = $request->question_category_id ?? null;
                    $modelQuestion->question = $request->question;
                    $modelQuestion->level = $request->level;
                    $modelQuestion->require_file_upload = $request->require_file_upload ?? 0;
                    $modelQuestion->source = $request->source ?? 'Q';
                    $modelQuestion->created_by = $request->created_by;
                    $modelQuestion->creator_type = $request->creator_type;
                    $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                    $modelQuestion->save();

                    $question_id = $modelQuestion->question_id;
                    if ($request->question_type != 'text') {
                        $questionOptions = $request->options ?? array();
                        if (count($questionOptions) > 0) {
                            for ($i = 0; $i < count($questionOptions); $i++) {

                                $modelOption = new QuestionOption;
                                $modelOption->question_id = $question_id;
                                $modelOption->option_value = $questionOptions[$i]['option_value'];
                                $modelOption->is_correct = $questionOptions[$i]['is_correct'];

                                $modelOption->save();
                            }
                        }
                    }
                }
            } else {
                // dd('new question');
                //create question master
                $modelQuestion = new Question;
                $modelQuestion->question_type = $request->question_type;
                $modelQuestion->year_group_id = $request->year_group_id;
                $modelQuestion->subject_id = $request->subject_id;
                $modelQuestion->lesson_id = $request->lesson_id ?? null;
                $modelQuestion->topic_id = $request->topic_id ?? null;
                $modelQuestion->sub_topic_id = $request->sub_topic_id ?? null;
                $modelQuestion->tc = $request->tc ?? 0;
                $modelQuestion->ms = $request->ms ?? 0;
                $modelQuestion->ps = $request->ps ?? 0;
                $modelQuestion->at = $request->at ?? 0;
                $modelQuestion->question_category_id = $request->question_category_id ?? null;
                $modelQuestion->question = $request->question;
                $modelQuestion->level = $request->level;
                $modelQuestion->require_file_upload = $request->require_file_upload ?? 0;
                $modelQuestion->source = $request->source ?? 'Q';
                $modelQuestion->created_by = $request->created_by;
                $modelQuestion->creator_type = $request->creator_type;
                $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
                $modelQuestion->save();

                $question_id = $modelQuestion->question_id;

                if ($request->question_type != 'text') {
                    $questionOptions = $request->options ?? array();
                    if (count($questionOptions) > 0) {
                        for ($i = 0; $i < count($questionOptions); $i++) {

                            $modelOption = new QuestionOption;
                            $modelOption->question_id = $question_id;
                            $modelOption->option_value = $questionOptions[$i]['option_value'];
                            $modelOption->is_correct = $questionOptions[$i]['is_correct'];

                            $modelOption->save();
                        }
                    }
                }
            }
            $examinationQuestionId = null;
            if (isset($request->examination_question_id) && $request->examination_question_id != null) {
                //edit examination question data
                $examinationQuestionId = $request->examination_question_id;
                $modelExaminationQuestion = ExaminationQuestion::find($examinationQuestionId);
                $modelExaminationQuestion->examination_id = $request->examination_id;
                $modelExaminationQuestion->question_id = $question_id;
                $modelExaminationQuestion->topic_id = $request->topic_id ?? null;
                $modelExaminationQuestion->sub_topic_id = $request->sub_topic_id ?? null;
                $modelExaminationQuestion->tc = $request->tc ?? 0;
                $modelExaminationQuestion->ms = $request->ms ?? 0;
                $modelExaminationQuestion->ps = $request->ps ?? 0;
                $modelExaminationQuestion->at = $request->at ?? 0;
                $modelExaminationQuestion->page_id = $request->page_id;
                $modelExaminationQuestion->time_inseconds = ($request->time_inseconds ?? 0) * 60;
                $modelExaminationQuestion->point = $request->point ?? 0;
                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                $modelExaminationQuestion->save();
            } else {
                $modelExaminationQuestion = new ExaminationQuestion;
                $modelExaminationQuestion->examination_id = $request->examination_id;
                $modelExaminationQuestion->question_id = $question_id;
                $modelExaminationQuestion->topic_id = $request->topic_id ?? null;
                $modelExaminationQuestion->sub_topic_id = $request->sub_topic_id ?? null;
                $modelExaminationQuestion->tc = $request->tc ?? 0;
                $modelExaminationQuestion->ms = $request->ms ?? 0;
                $modelExaminationQuestion->ps = $request->ps ?? 0;
                $modelExaminationQuestion->at = $request->at ?? 0;
                $modelExaminationQuestion->page_id = $request->page_id;
                $modelExaminationQuestion->time_inseconds = ($request->time_inseconds ?? 0) * 60;
                $modelExaminationQuestion->point = $request->point ?? 0;
                $modelExaminationQuestion->question_info = json_encode($questionInfo);

                $modelExaminationQuestion->save();

                $examinationQuestionId = $modelExaminationQuestion->examination_question_id;
            }

            $tot_time_questions = ExaminationQuestion::where('examination_id', $request->examination_id)->sum('time_inseconds');

            // $total_time_minutes = ($tot_time_questions / 60);
            $init = $tot_time_questions;
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $total_time = "$hours:$minutes:$seconds";
            // dd($total_time);
            //update examination data
            $modelExamination = Examination::find($request->examination_id);
            $modelExamination->year_group_id = $request->year_group_id ?? null;
            $modelExamination->subject_id = $request->subject_id ?? null;
            $modelExamination->lesson_id = $request->lesson_id ?? null;

            $modelExamination->total_time = $total_time;

            $modelExamination->save();

            $response = ExaminationQuestion::find($examinationQuestionId);
            // dd($response->options);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw ($e);

        }
        return $response;
    }

    public function qb_getExaminationById($examination_id)
    {
        // dd($examination_id);
        $response = null;
        $examination = Examination::where('examination_id', $examination_id)->with(['examquestions', 'examquestions.subquestions', 'subject'])->first();
        if (!empty($examination)) {
            $page_count = ExaminationQuestion::where('examination_id', $examination_id)
                ->distinct('page_id')
                ->count();
            $response = [
                'examination' => $examination,
                'page_count' => $page_count,
            ];
        }

        return $response;
    }

    public function qb_getExaminationQuestionById($examination_question_id)
    {
        // dd($examination_question_id);
        $response = null;
        $examination_question = ExaminationQuestion::where('examination_question_id', $examination_question_id)->with(['question'])->first();
        if (!empty($examination_question)) {
            $response = [
                'examination_question' => $examination_question,
            ];
        }

        return $response;
    }

    public function qb_removeExaminationPage($examination_id, $page_id)
    {
        $response = null;
        $examination = Examination::where('examination_id', $examination_id)->first();
        if (!empty($examination)) {
            $deleted = ExaminationQuestion::where('examination_id', $examination_id)->where('page_id', $page_id)->delete();

            $page_count = ExaminationQuestion::where('examination_id', $examination_id)
                ->distinct('page_id')
                ->count();

            if ($page_count == 0) {
                $modelExamination = Examination::find($examination_id);
                $modelExamination->year_group_id = null;
                $modelExamination->subject_id = null;
                $modelExamination->lesson_id = null;
                $modelExamination->total_time = null;

                $modelExamination->save();

            }
            $examination = Examination::where('examination_id', $examination_id)->first();
            $response = [
                'examination' => $examination,
                'page_count' => $page_count,
            ];
        }

        return $response;

    }
    public function qb_updateStatusExamination($examination_id, $status)
    {
        $response = null;
        $examination = Examination::where('examination_id', $examination_id)->first();
        if (!empty($examination)) {
            $page_count = ExaminationQuestion::where('examination_id', $examination_id)
                ->distinct('page_id')
                ->count();

            $modelExamination = Examination::find($examination_id);
            $modelExamination->status = $status;

            $modelExamination->save();

            $examination = Examination::where('examination_id', $examination_id)->first();
            $response = [
                'examination' => $examination,
                'page_count' => $page_count,
            ];
        }

        return $response;
    }
    public function qb_updateHwExamination($examination_id, $homework)
    {
        $response = null;
        $examination = Examination::where('examination_id', $examination_id)->first();
        if (!empty($examination)) {
            $page_count = ExaminationQuestion::where('examination_id', $examination_id)
                ->distinct('page_id')
                ->count();

            $modelExamination = Examination::find($examination_id);
            $modelExamination->homework = $homework;

            $modelExamination->save();

            $examination = Examination::where('examination_id', $examination_id)->first();
            $response = [
                'examination' => $examination,
                'page_count' => $page_count,
            ];
        }

        return $response;
    }

    public function qb_consumerExaminationPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;
        $consumed_by = $request->consumed_by;
        $examination_type = $request->examination_type;

        $consumerSubjectIds = UserSubject::where('user_subjects.user_id', $consumed_by)
            ->distinct('subject_id')
            ->pluck('subject_id')
            ->toArray();

        $consumerTaskIds = TaskAllocation::where('task_allocations.user_id', $consumed_by)
            ->distinct('task_id')
            ->pluck('task_id')
            ->toArray();
        $consumerExamIds = TaskExamination::whereIn('task_examinations.task_id', $consumerTaskIds)
            ->distinct('examination_id')
            ->pluck('examination_id')
            ->toArray();

        $listing = Examination::where('examination_type', $examination_type)
            ->select('examinations.*', DB::raw("(select count(*) from user_results where user_results.examination_id= examinations.examination_id and user_results.user_id=" . $consumed_by . ") as is_submitted"), DB::raw("(select lesson_name from lessons where lessons.lesson_id= examinations.lesson_id) as lesson_name"))
            ->where('status', GlobalVars::EXAM_ACTIVE_STATUS)
            ->whereIn('subject_id', $consumerSubjectIds)
            ->whereIn('examinations.examination_id', $consumerExamIds)
            ->where(function ($query) use ($search_text) {
                if ($search_text != null) {
                    $query->where('name', 'like', '%' . $search_text . '%');
                }
            })
            ->with(['subject', 'subject.yeargroup', 'subject.academicyear'])
            ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

        return $listing;
    }

    public function qb_getExaminationQuestions(Request $request)
    {
        $examination_id = CommonHelper::decryptId($request->examination_id);
        // $examination_id = $request->examination_id;
        // dd($examination_id);
        $response = null;
        $examination_questions = ExaminationQuestion::where('examination_id', $examination_id)->where('parent_examination_question_id', '0')->with(['subquestions', 'question'])->get();
        if (count($examination_questions) > 0) {
            $examination = Examination::find($examination_id);
            $response = [
                'examination' => $examination,
                'examination_questions' => $examination_questions,
            ];
        }

        return $response;
    }

    public function qb_saveConsumerExam(Request $request)
    {
        // dd($request->all());
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {

            $modelUserResult = new UserResult;
            $modelUserResult->examination_id = $request->examination_id;
            $modelUserResult->user_id = $request->consumed_by;
            $modelUserResult->start_time = $request->start_time;
            $modelUserResult->end_time = $request->end_time;
            $modelUserResult->total_time_in_mins = $request->total_time_in_mins;
            $modelUserResult->time_taken_inmins = $request->time_taken_inmins;
            $modelUserResult->total_marks = $request->total_marks;

            $modelUserResult->save();

            $user_result_id = $modelUserResult->user_result_id;
            $answerOptions = $request->options ?? array();
            if (count($answerOptions) > 0) {
                for ($i = 0; $i < count($answerOptions); $i++) {

                    $usrAnswer = $answerOptions[$i]['answer'] ?? null;
                    $modelUserResultInput = new UserResultInput;
                    $modelUserResultInput->user_result_id = $user_result_id;
                    $modelUserResultInput->examination_question_id = $answerOptions[$i]['examination_question_id'];
                    $modelUserResultInput->answer = $usrAnswer;

                    $modelUserResultInput->time_taken_inmins = $answerOptions[$i]['time_taken_inmins'];
                    if (isset($answerOptions[$i]['attachment_file']) && $answerOptions[$i]['attachment_file'] != '') {
                        $attachment_file = array();
                        // dd($answerOptions[$i]['attachment_file']);
                        foreach ($answerOptions[$i]['attachment_file'] as $attachfile) {

                            //image without mime information
                            $dataWithoutMime = explode('base64,', $attachfile);
                            // dd($dataWithoutMime);
                            $file = $attachfile;
                            if (isset($dataWithoutMime[1])) {
                                $file = base64_decode($dataWithoutMime[1]);
                            }

                            if ($file) {

                                $folderPath = \GlobalVars::USER_ATTACHMENT_PATH . $request->examination_id . '/' . $request->consumed_by . '/';
                                $extension = '.png';
                                if (isset($dataWithoutMime[1])) {

                                    $dataMimeType = str_replace('data:', '', $dataWithoutMime[0]);
                                    $dataMimeType = str_replace(';', '', $dataMimeType);
                                    $dataMimeType = trim($dataMimeType);
                                    $extension = array_search($dataMimeType, \GlobalVars::EXT_MIMETYPE);
                                }
                                // dd($extension);

                                $base64_file = $file;
                                $file1 = $folderPath . uniqid() . $extension;
                                // $file1 = uniqid() . '.' . $extension;

                                Storage::disk('public')->put($file1, $base64_file);

                                // $modelTenant->logo = $file_logo1;
                                array_push($attachment_file, $file1);
                            }
                        }

                        $modelUserResultInput->attachment_file = json_encode($attachment_file);
                        // dd($modelUserResultInput);
                    }

                    $answer_status = GlobalVars::EXAMREVIEW_NOTATTEMPTED_STATUS;
                    if ($modelUserResultInput->attachment_file != null) {
                        $answer_status = GlobalVars::EXAMREVIEW_ATTEMPTED_STATUS;
                    }
                    if ($usrAnswer != null) {
                        $answer_status = GlobalVars::EXAMREVIEW_ATTEMPTED_STATUS;
                    }

                    $modelUserResultInput->answer_status = $answer_status;

                    $modelUserResultInput->save();
                }
            }
            $response = UserResult::with('inputs')->find($user_result_id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw ($e);

        }
        return $response;
    }

    public function qb_creatorExaminationForReviewPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;
        $created_by = $request->created_by;
        $examination_type = $request->examination_type ?? 'Q';

        $examIds = Examination::where('created_by', $created_by)
            ->where('examination_type', $examination_type)
            ->distinct('examination_id')
            ->pluck('examination_id');

        $listing = UserResult::whereIn('examination_id', $examIds)
            ->with(['examination' => function ($query) use ($search_text) {
                if ($search_text != null) {
                    $query->where('name', 'like', '%' . $search_text . '%');
                }
            }, 'consumer' => function ($query) use ($search_text) {
                if ($search_text != null) {
                    $query->where('email', 'like', '%' . $search_text . '%')
                        ->orWhere('phone', 'like', '%' . $search_text . '%')
                        ->where('first_name', 'like', '%' . $search_text . '%')
                        ->orWhere('last_name', 'like', '%' . $search_text . '%');
                }
            }, 'examination.subject.yeargroup', 'examination.subject.academicyear'])
            ->where('is_reviewed', 'N')
            ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

        return $listing;
    }

    public function qb_getExaminationSubmissionDetails(Request $request)
    {
        $user_result_id = CommonHelper::decryptId($request->user_result_id);

        $response = null;
        $user_result = UserResult::with(['inputs', 'examination', 'examination.examquestions', 'examination.examquestions.subquestions', 'examination.subject', 'consumer', 'examination.subject.yeargroup', 'examination.subject.academicyear'])->find($user_result_id);

        // $pages=$user_result->examination
        //dd($user_result->examination->examquestions->unique('page_id')->count());

        if (!empty($user_result)) {

            //$response = $user_result;
            $response = [
                'user_result' => $user_result,
                'page_count' => $user_result->examination->examquestions->unique('page_id')->count(),
            ];
        }

        return $response;
    }

    public function qb_saveCreatorExamReview(Request $request)
    {
        // dd($request->all());
        $user_result_id = CommonHelper::decryptId($request->user_result_id);

        // dd($user_result_id);
        $response = null;
        //Begin database transaction
        DB::beginTransaction();
        try {

            $answerOptions = $request->options ?? array();
            $totalMarksObtained = 0;
            if (count($answerOptions) > 0) {
                // dd(count($answerOptions));
                for ($i = 0; $i < count($answerOptions); $i++) {
                    $totalMarksObtained = $totalMarksObtained + $answerOptions[$i]['marks_given'];

                    $modelUserResultInput = UserResultInput::where('user_result_id', $user_result_id)->where('examination_question_id', $answerOptions[$i]['examination_question_id'])->first();

                    // dd($modelUserResultInput);

                    $usrAnswerStatus = $modelUserResultInput->answer_status;
                    if ($usrAnswerStatus != GlobalVars::EXAMREVIEW_NOTATTEMPTED_STATUS) {
                        if ($answerOptions[$i]['is_correct']) {
                            $usrAnswerStatus = GlobalVars::EXAMREVIEW_CORRECT_STATUS;
                        } else {
                            $usrAnswerStatus = GlobalVars::EXAMREVIEW_INCORRECT_STATUS;
                        }
                    }

                    $modelUserResultInput->user_result_id = $user_result_id;
                    $modelUserResultInput->examination_question_id = $answerOptions[$i]['examination_question_id'];
                    $modelUserResultInput->marks_given = $answerOptions[$i]['marks_given'];
                    $modelUserResultInput->reviewer_comments = $answerOptions[$i]['reviewer_comments'];
                    $modelUserResultInput->answer_status = $usrAnswerStatus;
                    // dd($modelUserResultInput);
                    $modelUserResultInput->save();
                }
            }
            $modelUserResult = UserResult::find($user_result_id);
            $modelUserResult->marks_obtained = $totalMarksObtained;
            $modelUserResult->reviewer_user_id = $request->reviewer_user_id;
            $modelUserResult->is_reviewed = 'Y';

            $examInfo = Examination::find($modelUserResult->examination_id);
            $examSubject = Subject::find($examInfo->subject_id);

            $percentage = round(($totalMarksObtained / $modelUserResult->total_marks) * 100, 2);
            $getGrade = $this->qb_getGradeInfo($percentage, date('Y-m-d'), $examSubject->board_id);
            // dd($getGrade);
            $grade = null;
            $grade_id = null;
            if ($getGrade != null) {
                $grade = $getGrade->grade;
                $grade_id = $getGrade->grade_id;
            }
            $modelUserResult->grade = $grade;
            $modelUserResult->grade_id = $grade_id;
            $modelUserResult->save();
            DB::commit();

            $user_result = UserResult::with(['inputs', 'examination', 'examination.examquestions', 'consumer'])->find($user_result_id);
            if (!empty($user_result)) {
                $response = [
                    'user_result' => $user_result,
                    'page_count' => $user_result->examination->examquestions->unique('page_id')->count(),
                ];
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw ($e);

        }
        return $response;
    }

    public function qb_creatorExaminationReviewedPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;
        $search_academic_year_id = $request->search_academic_year_id;
        $search_year_group_id = $request->search_year_group_id;
        $search_subject_id = $request->search_subject_id;
        $created_by = $request->created_by;
        $examination_type = $request->examination_type ?? 'Q';

        $consumerArray = ViewStudent::where(function ($query) use ($search_text) {
            if ($search_text != null) {
                $query->where('email', 'like', '%' . $search_text . '%')
                    ->orWhere('phone', 'like', '%' . $search_text . '%')
                    ->orWhere('first_name', 'like', '%' . $search_text . '%')
                    ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                    ->orWhere('last_name', 'like', '%' . $search_text . '%');
            }
        })
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        // print_r($consumerArray);

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

        // print_r($subjectArray);

        $filteredExamIds = Examination::where('examination_type', $examination_type)
            ->where('status', GlobalVars::EXAM_ACTIVE_STATUS)
            ->where('created_by', $created_by)
            ->where(function ($query) use ($search_text, $subjectArray) {

                if ($search_text != null) {
                    $query->where('name', 'like', '%' . $search_text . '%');
                }

                if (count($subjectArray) > 0) {
                    $query->whereIn('subject_id', $subjectArray);
                }

            })
            ->distinct('examination_id')
            ->pluck('examination_id')
            ->toArray();

        // print_r($filteredExamIds);die;

        $listing = UserResult::with(['examination', 'examination.subject', 'consumer', 'examination.subject.yeargroup', 'examination.subject.academicyear'])
            ->where('is_reviewed', 'Y')
            ->whereIn('examination_id', $filteredExamIds)
            ->where(function ($query) use ($consumerArray) {
                if (count($consumerArray) > 0) {
                    $query->whereIn('user_id', $consumerArray);
                }
            })
            ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

        return $listing;
    }

    public function qb_consumerExaminationReviewedPaginated(Request $request)
    {
        $page = $request->page;
        $search_text = $request->search_text;
        $search_academic_year_id = $request->search_academic_year_id;
        $search_year_group_id = $request->search_year_group_id;
        $search_subject_id = $request->search_subject_id;
        $consumed_by = $request->consumed_by;
        $examination_type = $request->examination_type ?? 'Q';

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

        $filteredExamIds = Examination::where('examination_type', $examination_type)
            ->where('status', GlobalVars::EXAM_ACTIVE_STATUS)

            ->where(function ($query) use ($search_text, $examination_type, $search_subject_id, $search_academic_year_id, $search_year_group_id, $subjectArray) {

                if ($search_text != null) {
                    $query->where('name', 'like', '%' . $search_text . '%');
                }
                // if ($search_year_group_id != null) {
                //     $query->where('year_group_id', $search_year_group_id);
                // }
                // if ($search_subject_id != null) {
                //     $query->where('subject_id', $search_subject_id);
                // }
                if (count($subjectArray) > 0) {
                    $query->whereIn('subject_id', $subjectArray);
                }

            })
            ->distinct('examination_id')
            ->pluck('examination_id')
            ->toArray();

        $listing = UserResult::where('user_id', $consumed_by)
            ->with(['examination', 'examination.subject', 'consumer', 'examination.subject.yeargroup', 'examination.subject.academicyear'])
            ->where('is_reviewed', 'Y')
            ->whereIn('examination_id', $filteredExamIds)
            ->paginate(GlobalVars::ADMIN_RECORDS_PER_PAGE, ['*'], $pageName = 'page', $page = $page);

        return $listing;
    }

    public function qb_examinationResults(Request $request)
    {
        $search_text = $request->search_text;
        $search_academic_year_id = $request->search_academic_year_id;
        $search_year_group_id = $request->search_year_group_id;
        $search_subject_id = $request->search_subject_id;
        $examination_type = $request->examination_type ?? '';

        $consumerArray = ViewStudent::where(function ($query) use ($search_text) {
            if ($search_text != null) {
                $query->where('email', 'like', '%' . $search_text . '%')
                    ->orWhere('phone', 'like', '%' . $search_text . '%')
                    ->orWhere('first_name', 'like', '%' . $search_text . '%')
                    ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                    ->orWhere('last_name', 'like', '%' . $search_text . '%');
            }
        })
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        // print_r($consumerArray);

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

        // print_r($subjectArray);

        $filteredExamIds = Examination::where('status', GlobalVars::EXAM_ACTIVE_STATUS)
            ->where(function ($query) use ($search_text, $subjectArray, $examination_type) {

                if ($search_text != null) {
                    $query->orWhere('name', 'like', '%' . $search_text . '%');
                }
                if ($examination_type != null) {
                    $query->where('examination_type', $examination_type);
                }

                //if (count($subjectArray) > 0) {
                $query->orWhereIn('subject_id', $subjectArray);
                //}

            })
            ->distinct('examination_id')
            ->pluck('examination_id')
            ->toArray();

        $listing = UserResult::with(['examination',
            'examination.subject',
            'consumer', 'examination.subject.yeargroup', 'examination.subject.academicyear'])
            ->whereIn('examination_id', $filteredExamIds)
            ->where(function ($query) use ($consumerArray) {
                //if (count($consumerArray) > 0) {
                $query->whereIn('user_id', $consumerArray);
                //}
            })
            ->where('is_reviewed', 'Y')
            ->get();

        return $listing;
    }

    public function qb_parentExaminationResults(Request $request)
    {
        $parent_id = $request->parent_id;
        $search_text = $request->search_text;
        $search_student_id = $request->search_student_id;
        $search_academic_year_id = $request->search_academic_year_id;
        $search_year_group_id = $request->search_year_group_id;
        $search_subject_id = $request->search_subject_id;
        $examination_type = $request->examination_type;

        // print_r($request->all());

        $siblingUsers = UserSibling::where('parent_user_id', $parent_id)
            ->where(function ($query) use ($search_student_id) {
                if ($search_student_id != null) {
                    $query->where('sibling_user_id', $search_student_id);
                }
            })
            ->distinct('sibling_user_id')
            ->pluck('sibling_user_id')
            ->toArray();

        $consumerArray = ViewStudent::where(function ($query) use ($search_text) {
            if ($search_text != null) {
                $query->where('email', 'like', '%' . $search_text . '%')
                    ->orWhere('phone', 'like', '%' . $search_text . '%')
                    ->orWhere('first_name', 'like', '%' . $search_text . '%')
                    ->orWhere('middle_name', 'like', '%' . $search_text . '%')
                    ->orWhere('last_name', 'like', '%' . $search_text . '%');
            }
        })
            ->whereIn('user_id', $siblingUsers)
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        // print_r($consumerArray);

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

        //print_r($subjectArray);

        $filteredExamIds = Examination::where('status', GlobalVars::EXAM_ACTIVE_STATUS)
            ->where(function ($query) use ($search_text, $subjectArray, $examination_type) {

                if ($search_text != null) {
                    $query->where('name', 'like', '%' . $search_text . '%');
                }
                if ($examination_type != null) {
                    $query->where('examination_type', $examination_type);
                }

                //if (count($subjectArray) > 0) {
                $query->whereIn('subject_id', $subjectArray);
                //}

            })
            ->distinct('examination_id')
            ->pluck('examination_id')
            ->toArray();

        $listing = UserResult::with(['examination',
            'examination.subject',
            'consumer', 'examination.subject.yeargroup', 'examination.subject.academicyear'])
            ->whereIn('examination_id', $filteredExamIds)
            ->where(function ($query) use ($consumerArray) {
                //if (count($consumerArray) > 0) {
                $query->whereIn('user_id', $consumerArray);
                //}
            })
            ->where('is_reviewed', 'Y')
            ->get();

        return $listing;
    }

    public function qb_getGradeInfo($percentage, $query_date, $board_id)
    {
        $response = null;
        // dd($query_date);
        $gradeInfo = Grade::where('status', GlobalVars::ACTIVE_STATUS)
            ->where('min_value', '<=', $percentage)
            ->where('max_value', '>=', $percentage)
            ->where('effective_date', '<=', $query_date)
            ->where('board_id', $board_id)
            ->orderBy('effective_date', 'desc')
            ->first();

        // dd($gradeInfo);

        if (!empty($gradeInfo)) {
            $response = $gradeInfo;
        }

        return $response;
    }

    public function qb_getUserExaminationResult(Request $request)
    {

        $user_id = $request->user_id;
        $examination_id = $request->examination_id;

        $response = null;
        $user_result = UserResult::with(['inputs', 'examination', 'examination.examquestions', 'examination.examquestions.subquestions', 'examination.subject', 'consumer', 'examination.subject.yeargroup', 'examination.subject.academicyear'])
            ->where('user_id', $user_id)
            ->where('examination_id', $examination_id)
            ->first();

        // $pages=$user_result->examination
        //dd($user_result->examination->examquestions->unique('page_id')->count());

        if (!empty($user_result)) {

            //$response = $user_result;
            $response = [
                'user_result' => $user_result,
            ];
        }

        return $response;
    }
}
