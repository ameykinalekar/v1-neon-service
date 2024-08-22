<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class StudyGroupMember extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'study_group_member_id';

    public function memberinfo()
    {
        return $this->belongsTo('App\Models\Tenant\TenantUser', 'member_user_id', 'user_id');

    }

}
