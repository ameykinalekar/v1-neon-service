<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class StudyGroup extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'study_group_id';

    public function creator()
    {
        return $this->belongsTo('App\Models\Tenant\TenantUser', 'created_by', 'user_id');
    }

    public function internal_members()
    {
        return $this->hasMany('App\Models\StudyGroupMember', 'study_group_id', 'study_group_id')->where('is_external_member', 'N')->join('users', 'users.user_id', '=', 'study_group_members.member_user_id')->join('user_profiles', 'user_profiles.user_id', '=', 'users.user_id');
    }
    public function external_members()
    {
        return $this->hasMany('App\Models\StudyGroupMember', 'study_group_id', 'study_group_id')->where('is_external_member', 'Y')->join('external_users', 'external_users.external_user_id', '=', 'study_group_members.member_user_id');
    }

}
