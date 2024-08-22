<?php

namespace App\Imports;

use App\Models\YearGroup;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class YearGroupImport implements ToModel, WithHeadingRow
{
    private $_academicYearId;

    public function __construct($academicYearId)
    {
        //dd($academicYearId);
        $this->_academicYearId = $academicYearId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row['name'] != '') {
            $data = YearGroup::where('name', '=', $row['name'])->where('academic_year_id', '=', $this->_academicYearId)->first();
            if (empty($data)) {

                return new YearGroup([
                    'academic_year_id' => $this->_academicYearId,
                    'name' => $row['name'],
                    'one_one' => '1:1',
                    'group' => 'group',
                    'status' => GlobalVars::ACTIVE_STATUS,
                ]);
            }

        }
        return;
    }
}
