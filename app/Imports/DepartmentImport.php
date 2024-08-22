<?php

namespace App\Imports;

use App\Models\Department;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DepartmentImport implements ToModel, WithHeadingRow
{

    public function __construct()
    {
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row['department_name'] != '') {
            $data = Department::where('department_name', '=', $row['department_name'])->first();
            if (empty($data)) {

                return new Department([
                    'department_name' => $row['department_name'],
                    'status' => GlobalVars::ACTIVE_STATUS,
                ]);
            }

        }
        return;
    }
}
