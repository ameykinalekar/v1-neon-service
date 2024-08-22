<?php

namespace App\Imports;

use App\Models\Lesson;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LessonImport implements ToModel, WithHeadingRow
{
    private $_subjectId;
    private $_created_by;
    private $_creator_type;

    public function __construct($subjectId, $created_by, $creator_type)
    {
        //dd($subjectId);
        $this->_subjectId = $subjectId;
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
        if ($row['lesson_name'] != '') {
            $data = Lesson::where('lesson_name', '=', $row['lesson_name'])->where('subject_id', '=', $this->_subjectId)->first();
            if (empty($data)) {

                return new Lesson([
                    'subject_id' => $this->_subjectId,
                    'lesson_number' => $row['lesson_number'],
                    'lesson_name' => $row['lesson_name'],
                    'created_by' => $this->_created_by,
                    'creator_type' => $this->_creator_type,
                    'status' => GlobalVars::ACTIVE_STATUS,
                ]);
            }

        }
        return;
    }
}
