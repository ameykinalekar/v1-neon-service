<?php

namespace App\Imports;

use App\Models\Indicator;
use App\Models\OfsteadFinance;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OfsteadFinanceImport implements ToModel, WithHeadingRow
{
    // private $_academicYearId;

    public function __construct($academicYearId)
    {
        //dd($academicYearId);
        // $this->_academicYearId = $academicYearId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $indicators = Indicator::with('sub_indicators')->where('indicator_type', 'O')->where('data_table', 'ofstead_finances')->first();
        // dd(is_array($indicators->sub_indicators));
        $sub_indicator_id = '';
        $ofsteadArray = array();
        if (count($indicators->sub_indicators) > 0) {
            // dd($row);
            foreach ($indicators->sub_indicators as $si) {
                $data = OfsteadFinance::where('sub_indicator_id', '=', $si->sub_indicator_id)->where('year', '=', $row['period'])->first();
                if (empty($data)) {

                    $ele = array();
                    $ele['year'] = $row['period'];
                    $ele['sub_indicator_id'] = $si->sub_indicator_id;
                    $ele['value'] = $row[$si->excel_column_identifier];
                    $ele['created_at'] = now();
                    array_push($ofsteadArray, $ele);
                }
            }
        }
        // dd($ofsteadArray);
        if (!empty($ofsteadArray)) {
            OfsteadFinance::insert($ofsteadArray);
        }
        // if ($sub_indicator_id != '') {
        //     $data = OfsteadFinance::where('sub_indicator_id', '=', $sub_indicator_id)->where('academic_year_id', '=', $this->_academicYearId)->first();
        //     if (empty($data)) {

        //         return new OfsteadFinance([
        //             'academic_year_id' => $this->_academicYearId,
        //             'name' => $row['name'],
        //             'one_one' => '1:1',
        //             'group' => 'group',
        //             'status' => GlobalVars::ACTIVE_STATUS,
        //         ]);
        //     }

        // }
        return;
    }
}
