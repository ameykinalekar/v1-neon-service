<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class StudyGroupContent extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'study_group_content_id';

    public function contentowner()
    {
        return $this->belongsTo('App\Models\StudyGroupMember', 'study_group_member_id', 'study_group_member_id');
    }

}
