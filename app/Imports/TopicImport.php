<?php

namespace App\Imports;

use App\Models\SubTopic;
use App\Models\Topic;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TopicImport implements ToModel, WithHeadingRow
{
    private $_subjectId;
    private $_lessonId;
    private $_created_by;
    private $_creator_type;

    public function __construct($subjectId, $lessonId, $created_by, $creator_type)
    {
        //dd($subjectId);
        $this->_subjectId = $subjectId;
        $this->_lessonId = $lessonId;
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
        if ($row['topic'] != '') {
            $topic_id = '';
            $data = Topic::where('topic', '=', trim($row['topic']))
                ->where('subject_id', '=', $this->_subjectId)
                ->where('lesson_id', '=', $this->_lessonId)
                ->first();

            if (empty($data)) {
                $model = new Topic;
                $model->subject_id = $this->_subjectId;
                $model->lesson_id = $this->_lessonId;
                $model->topic = trim($row['topic']);
                $model->created_by = $this->_created_by;
                $model->creator_type = $this->_creator_type;
                $model->status = GlobalVars::ACTIVE_STATUS;
                $model->save();

                $topic_id = $model->topic_id;
            } else {
                $topic_id = $data->topic_id;

                $model = Topic::find($topic_id);
                $model->subject_id = $this->_subjectId;
                $model->lesson_id = $this->_lessonId;
                $model->topic = trim($row['topic']);
                $model->created_by = $this->_created_by;
                $model->creator_type = $this->_creator_type;
                $model->status = GlobalVars::ACTIVE_STATUS;
                $model->save();
            }

            if ($row['sub_topic'] != '' && $topic_id != '') {
                $existingSubTopic = SubTopic::where('sub_topic', '=', trim($row['sub_topic']))
                    ->where('subject_id', '=', $this->_subjectId)
                    ->where('lesson_id', '=', $this->_lessonId)
                    ->where('topic_id', '=', $topic_id)
                    ->first();

                if (empty($existingSubTopic)) {
                    $modelSub = new SubTopic;
                    $modelSub->subject_id = $this->_subjectId;
                    $modelSub->lesson_id = $this->_lessonId;
                    $modelSub->topic_id = $topic_id;
                    $modelSub->sub_topic = trim($row['sub_topic']);
                    $modelSub->created_by = $this->_created_by;
                    $modelSub->creator_type = $this->_creator_type;
                    $modelSub->status = GlobalVars::ACTIVE_STATUS;
                    $modelSub->save();
                } else {
                    $modelSub = SubTopic::find($existingSubTopic->sub_topic_id);
                    $modelSub->subject_id = $this->_subjectId;
                    $modelSub->lesson_id = $this->_lessonId;
                    $modelSub->topic_id = $topic_id;
                    $modelSub->sub_topic = trim($row['sub_topic']);
                    $modelSub->created_by = $this->_created_by;
                    $modelSub->creator_type = $this->_creator_type;
                    $modelSub->status = GlobalVars::ACTIVE_STATUS;
                    $modelSub->save();
                }
            }
        }
        return;
    }
}
