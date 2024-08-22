<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\ViewStudent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceImport implements ToModel, WithHeadingRow
{
    private $_subjectId;
    private $_lessonId;
    private $_createdBy;

    public function __construct($subjectId, $lessonId, $createdBy)
    {
        $this->_subjectId = $subjectId;
        $this->_lessonId = $lessonId;
        $this->_createdBy = $createdBy;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row['email'] != '') {

            $userInfo = ViewStudent::where('email', $row['email'])->first();

            if (!empty($userInfo)) {
                $attendanceDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date'])->format('Y-m-d');
                $existingAttendance = Attendance::where('attendance_date', $attendanceDate)
                    ->where('subject_id', $this->_subjectId)
                    ->where('lesson_id', $this->_lessonId)
                    ->where('user_id', $userInfo['user_id'])
                    ->first();
                // dd($existingAttendance);
                if (empty($existingAttendance)) {
                    $model = new Attendance;
                    $model->attendance_date = $attendanceDate ?? null;
                    $model->subject_id = $this->_subjectId ?? '';
                    $model->lesson_id = $this->_lessonId ?? '';
                    $model->created_by = $this->_createdBy ?? '';
                    $model->user_id = $userInfo['user_id'] ?? '';
                    $model->is_present = $row['is_present'] ?? 0;
                    $model->remarks = $row['comment'] ?? '';
                    // dd($model);
                    // $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();
                } else {
                    $model = Attendance::find($existingAttendance->attendance_id);
                    $model->attendance_date = $attendanceDate ?? null;
                    $model->subject_id = $this->_subjectId ?? '';
                    $model->lesson_id = $this->_lessonId ?? '';
                    $model->created_by = $this->_createdBy ?? '';
                    $model->user_id = $userInfo['user_id'] ?? '';
                    $model->is_present = $row['is_present'] ?? 0;
                    $model->remarks = $row['comment'] ?? '';
                    // $model->status = GlobalVars::ACTIVE_STATUS;
                    $model->save();

                }
            }

        }
        return;
    }

}
