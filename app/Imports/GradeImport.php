<?php

namespace App\Imports;

use App\Models\Grade;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GradeImport implements ToModel, WithHeadingRow
{

    private $_boardId;

    public function __construct($boardId)
    {
        $this->_boardId = $boardId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row['grade'] != '') {
            $data = Grade::where('grade', $row['grade'])->where('min_value', $row['min_percent'])->where('max_value', $row['max_percent'])->where('effective_date', $row['effective_date'])->first();
            if (empty($data)) {
                return new Grade([
                    'grade' => $row['grade'],
                    'board_id' => $this->_boardId,
                    'min_value' => $row['min_percent'],
                    'max_value' => $row['max_percent'],
                    'effective_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['effective_date'])->format('Y-m-d'),
                    'status' => GlobalVars::ACTIVE_STATUS,
                ]);
            }

        }
        return;
    }

}
