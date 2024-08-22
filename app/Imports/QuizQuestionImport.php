<?php

namespace App\Imports;

use App\Models\Examination;
use App\Models\ExaminationQuestion;
use App\Models\Question;
use App\Models\QuestionOption;
use DB;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuizQuestionImport implements ToModel, WithHeadingRow
{
    private $_pageId;
    private $_examinationId;
    private $_yearGroupId;
    private $_subjectId;
    private $_lessonId;
    private $_topicId;
    private $_subTopicId;
    private $_tenantId;
    private $_created_by;
    private $_creator_type;

    public function __construct($pageId, $examinationId, $yearGroupId, $subjectId, $lessonId, $topicId, $subTopicId, $tenantId, $created_by, $creator_type)
    {
        //dd($subjectId);
        $this->_pageId = $pageId;
        $this->_examinationId = $examinationId;
        $this->_yearGroupId = $yearGroupId;
        $this->_subjectId = $subjectId;
        $this->_lessonId = $lessonId;
        $this->_topicId = $topicId;
        $this->_subTopicId = $subTopicId;
        $this->_tenantId = $tenantId;
        $this->_created_by = $created_by;
        $this->_creator_type = $creator_type;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        DB::beginTransaction();
        try {
            // dd($row);
            $question = $row['question'] ?? '';
            $time_in_minutes = $row['time_in_minutes'] ?? 0;
            $marks_allotted = $row['marks_allotted'] ?? 0;
            $question_type = $row['question_type'] ?? 'radio';
            $level = $row['difficulty_level'] ?? 'low';
            $require_file_upload = 0;
            $tc = 0;
            if (strtolower(trim($row['tc'])) == 'yes') {
                $tc = 1;
            }
            $ms = 0;
            if ($row['ms'] == 'yes') {
                $ms = 1;
            }
            $ps = 0;
            if ($row['ps'] == 'yes') {
                $ps = 1;
            }
            $at = 0;
            if ($row['at'] == 'yes') {
                $at = 1;
            }

            $no_of_options = $row['no_of_options'] ?? 0;
            $correct_option = $row['correct_option'];

            $options = array();

            // dd($row['option1']);

            for ($i = 0; $i < $no_of_options; $i++) {
                $col = $i + 1;
                if ($col == $correct_option) {
                    $ele = [
                        "option_value" => $row['option' . $col],
                        "is_correct" => '1',
                    ];
                } else {
                    $ele = [
                        "option_value" => $row['option' . $col],
                        "is_correct" => '0',
                    ];
                }

                array_push($options, $ele);

            }
            // dd($options);

            $questionInfo = [
                'question' => $question,
                'question_type' => $question_type,
                'level' => $level,
                'require_file_upload' => $require_file_upload ?? 0,
                'source' => 'Q',
                'options' => $options ?? array(),
            ];
            if ($question_type == 'text') {
                $questionInfo = [
                    'question' => $question,
                    'question_type' => $question_type,
                    'level' => $level,
                    'require_file_upload' => $require_file_upload ?? 0,
                    'source' => 'Q',
                ];

            }

            // dd($questionInfo);
            //create question master
            $modelQuestion = new Question;
            $modelQuestion->question_type = $question_type;
            $modelQuestion->year_group_id = $this->_yearGroupId;
            $modelQuestion->subject_id = $this->_subjectId;
            $modelQuestion->lesson_id = $this->_lessonId ?? null;
            $modelQuestion->topic_id = $this->_topicId ?? null;
            $modelQuestion->sub_topic_id = $this->_subTopicId ?? null;
            $modelQuestion->tc = $tc ?? 0;
            $modelQuestion->ms = $ms ?? 0;
            $modelQuestion->ps = $ps ?? 0;
            $modelQuestion->at = $at ?? 0;
            $modelQuestion->question_category_id = null;
            $modelQuestion->question = $question;
            $modelQuestion->level = $level;
            $modelQuestion->require_file_upload = $require_file_upload ?? 0;
            $modelQuestion->source = 'Q';
            $modelQuestion->created_by = $this->_created_by;
            $modelQuestion->creator_type = $this->_creator_type;
            $modelQuestion->status = GlobalVars::ACTIVE_STATUS;
            $modelQuestion->save();

            $question_id = $modelQuestion->question_id;

            if ($question_type != 'text') {
                $questionOptions = $options ?? array();
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

            $modelExaminationQuestion = new ExaminationQuestion;
            $modelExaminationQuestion->examination_id = $this->_examinationId;
            $modelExaminationQuestion->question_id = $question_id;
            $modelExaminationQuestion->topic_id = $this->_topicId ?? null;
            $modelExaminationQuestion->sub_topic_id = $this->_subTopicId ?? null;
            $modelExaminationQuestion->tc = $tc ?? 0;
            $modelExaminationQuestion->ms = $ms ?? 0;
            $modelExaminationQuestion->ps = $ps ?? 0;
            $modelExaminationQuestion->at = $at ?? 0;
            $modelExaminationQuestion->page_id = $this->_pageId;
            $modelExaminationQuestion->time_inseconds = ($time_in_minutes ?? 0) * 60;
            $modelExaminationQuestion->point = $marks_allotted ?? 0;
            $modelExaminationQuestion->question_info = json_encode($questionInfo);

            $modelExaminationQuestion->save();

            $examinationQuestionId = $modelExaminationQuestion->examination_question_id;

            $tot_time_questions = ExaminationQuestion::where('examination_id', $this->_examinationId)->sum('time_inseconds');

            // $total_time_minutes = ($tot_time_questions / 60);
            $init = $tot_time_questions;
            $hours = floor($init / 3600);
            $minutes = floor(($init / 60) % 60);
            $seconds = $init % 60;
            $total_time = "$hours:$minutes:$seconds";
            // dd($total_time);
            //update examination data
            $modelExamination = Examination::find($this->_examinationId);
            $modelExamination->year_group_id = $this->_yearGroupId ?? null;
            $modelExamination->subject_id = $this->_subjectId ?? null;
            $modelExamination->lesson_id = $this->_lessonId ?? null;

            $modelExamination->total_time = $total_time;

            $modelExamination->save();

            $response = ExaminationQuestion::find($examinationQuestionId);
            // dd($response->options);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw ($e);

        }

        return;
    }
}
