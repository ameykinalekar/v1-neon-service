<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\UserSubject;
use App\Models\UserYearGroup;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeacherImport implements ToModel, WithHeadingRow
{
    private $_subjectId;
    private $_tenantId;

    public function __construct($subjectId, $tenantId)
    {
        //dd($subjectId);
        $this->_subjectId = $subjectId;
        $this->_tenantId = $tenantId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $phone_no = null;
        if ($row['email'] != '') {
            if (trim($row['phone']) != '') {
                if (preg_match("/^\+?[0-9]{10,12}$/", $row['phone'])) {
                    //regex_valid
                    $phone_no = trim($row['phone']);
                }
            }
            //validate existence of email id in any user type
            $ulist = TenantUser::where('email', '=', $row['email'])->first();

            if (empty($ulist)) {

                $modelU = new TenantUser;
                $modelU->email = $row['email'];
                $modelU->tenant_id = $this->_tenantId;
                $modelU->password = $row['password'];
                $modelU->user_type = GlobalVars::TENANT_USER_TYPE;
                $modelU->role = GlobalVars::TENANT_TEACHER_ROLE;
                $modelU->phone = $phone_no;
                $modelU->status = GlobalVars::ACTIVE_STATUS;

                $modelU->save();

                $modelUP = new TenantUserProfile;
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $row['first_name'];
                $modelUP->last_name = $row['last_name'];
                $modelUP->address = $row['address'];
                $modelUP->gender = $row['gender'];
                $modelUP->ni_number = $row['ni_number'];
                $modelUP->about = $row['about'];
                $modelUP->save();

                if ($this->_subjectId != "") {
                    UserSubject::where('user_id', '=', $modelU->user_id)->delete();
                    UserYearGroup::where('user_id', '=', $modelU->user_id)->delete();

                    $subjectArr = explode(',', $this->_subjectId);
                    $yearGroupArr = array();

                    foreach ($subjectArr as $subjectId) {
                        $yearGroupId = Subject::where('subject_id', $subjectId)->value('year_group_id');
                        array_push($yearGroupArr, $yearGroupId);
                        $modelUserSubject = new UserSubject;
                        $modelUserSubject->user_id = $modelU->user_id;
                        $modelUserSubject->subject_id = $subjectId;
                        $modelUserSubject->status = GlobalVars::ACTIVE_STATUS;
                        $modelUserSubject->save();
                    }
                    $yearGroupArr = array_unique($yearGroupArr);
                    foreach ($yearGroupArr as $yearGroupId) {
                        $modelUserYearGroup = new UserYearGroup;
                        $modelUserYearGroup->user_id = $modelU->user_id;
                        $modelUserYearGroup->year_group_id = $yearGroupId;
                        $modelUserYearGroup->status = GlobalVars::ACTIVE_STATUS;
                        $modelUserYearGroup->save();
                    }
                }

            }
        }

        return;
    }
}
