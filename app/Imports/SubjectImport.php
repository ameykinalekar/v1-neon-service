<?php

namespace App\Imports;

use App\Models\Subject;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SubjectImport implements ToModel, WithHeadingRow
{
    private $_academicYearId;
    private $_yearGroupId;
    private $_boardId;

    public function __construct($academicYearId, $yearGroupId, $boardId)
    {
        $this->_academicYearId = $academicYearId;
        $this->_yearGroupId = $yearGroupId;
        $this->_boardId = $boardId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // dd($this);
        if ($row['subject_name'] != '') {
            $data = Subject::where('subject_name', '=', $row['subject_name'])->where('academic_year_id', '=', $this->_academicYearId)->where('year_group_id', '=', $this->_yearGroupId)->where('board_id', '=', $this->_boardId)->first();
            if (empty($data)) {

                return new Subject([
                    'academic_year_id' => $this->_academicYearId,
                    'year_group_id' => $this->_yearGroupId,
                    'board_id' => $this->_boardId,
                    'subject_name' => $row['subject_name'],
                    'description' => $row['description'],
                    'status' => GlobalVars::ACTIVE_STATUS,
                ]);
            }

        }
        return;
    }
}
