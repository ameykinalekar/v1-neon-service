<?php

namespace App\Imports;

use App\Helpers\CommonHelper;
use App\Models\Subject;
use App\Models\Tenant\TenantUser;
use App\Models\Tenant\TenantUserProfile;
use App\Models\UserSibling;
use App\Models\UserSubject;
use App\Models\UserYearGroup;
use GlobalVars;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
{
    private $_batchTypeId;
    private $_subjectId;
    private $_tenantId;

    public function __construct($subjectId, $tenantId, $batchTypeId)
    {
        //dd($subjectId);
        $this->_subjectId = $subjectId;
        $this->_tenantId = $tenantId;
        $this->_batchTypeId = $batchTypeId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $phone_no = null;
        $parent_phone_no = null;
        if ($row['email'] != '') {
            if (trim($row['phone']) != '') {
                if (preg_match("/^\+?[0-9]{10,12}$/", $row['phone'])) {
                    //regex_valid
                    $phone_no = trim($row['phone']);
                }
            }
            if (trim($row['parent_phone']) != '') {
                if (preg_match("/^\+?[0-9]{10,12}$/", $row['parent_phone'])) {
                    //regex_valid
                    $parent_phone_no = trim($row['parent_phone']);
                }
            }
            //validate existence of email id in any user type
            $ulist = TenantUser::where('email', '=', $row['email'])->first();

            if (empty($ulist)) {
                $student_code = CommonHelper::studentCode();

                $modelU = new TenantUser;
                $modelU->email = $row['email'];
                $modelU->tenant_id = $this->_tenantId;
                $modelU->password = $row['password'];
                $modelU->user_type = GlobalVars::TENANT_USER_TYPE;
                $modelU->role = GlobalVars::TENANT_STUDENT_ROLE;
                $modelU->phone = $phone_no;
                $modelU->code = $student_code;
                $modelU->status = GlobalVars::ACTIVE_STATUS;

                $modelU->save();

                $modelUP = new TenantUserProfile;
                $modelUP->user_id = $modelU->user_id;
                $modelUP->first_name = $row['first_name'];
                $modelUP->last_name = $row['last_name'];
                $modelUP->address = $row['address'];
                $modelUP->gender = $row['gender'];
                $modelUP->batch_type_id = 1;
                $modelUP->parent_name = $row['parent_name'];
                $modelUP->parent_phone = $parent_phone_no;
                $modelUP->parent_email = $row['parent_email'];
                $modelUP->have_sensupport_healthcare_plan = $row['have_sensupport_healthcare_plan'] ?? 'N';
                $modelUP->first_lang_not_eng = $row['first_lang_not_eng'] ?? 'N';
                $modelUP->freeschool_eligible = $row['freeschool_eligible'] ?? 'N';
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
                //automatic parent creation

                if ($modelUP->parent_email != '') {

                    $ulistParent = TenantUser::where('email', '=', $modelUP->parent_email)->first();

                    if (empty($ulistParent)) {
                        //create parent & link subling
                        $parent_code = CommonHelper::parentCode();
                        $modelUParent = new TenantUser;
                        $modelUParent->email = $modelUP->parent_email;
                        $modelUParent->tenant_id = $this->_tenantId;
                        $modelUParent->password = $row['password'];
                        $modelUParent->user_type = GlobalVars::TENANT_PARENT_USER_TYPE;
                        $modelUParent->role = GlobalVars::TENANT_PARENT_ROLE;
                        $modelUParent->phone = $modelUP->parent_phone;
                        $modelUParent->code = $parent_code;
                        $modelUParent->status = GlobalVars::ACTIVE_STATUS;
                        $modelUParent->save();

                        $modelUPParent = new TenantUserProfile;
                        $modelUPParent->user_id = $modelUParent->user_id;
                        $modelUPParent->first_name = $modelUP->parent_name;

                        $modelUPParent->save();

                        $parent_user_id = $modelUParent->user_id;
                        $sibling_user_id = $modelU->user_id;

                        $chkSiblingExist = UserSibling::where('parent_user_id', '=', $parent_user_id)->where('sibling_user_id', '=', $sibling_user_id)->whereNull('token')->first();
                        if (empty($chkSiblingExist)) {
                            //need to map sibling
                            $model = new UserSibling;
                            $model->parent_user_id = $parent_user_id;
                            $model->sibling_user_id = $sibling_user_id;
                            $model->token = null;
                            $model->status = GlobalVars::ACTIVE_STATUS;
                            $model->save();
                        }

                    } else {
                        //map subling only
                        $parent_user_id = $ulistParent->user_id;
                        $sibling_user_id = $modelU->user_id;

                        $chkSiblingExist = UserSibling::where('parent_user_id', '=', $parent_user_id)->where('sibling_user_id', '=', $sibling_user_id)->whereNull('token')->first();
                        if (empty($chkSiblingExist)) {
                            //need to map sibling
                            $model = new UserSibling;
                            $model->parent_user_id = $parent_user_id;
                            $model->sibling_user_id = $sibling_user_id;
                            $model->token = null;
                            $model->status = GlobalVars::ACTIVE_STATUS;
                            $model->save();
                        }

                    }

                }
            }

        }

        return;
    }
}
