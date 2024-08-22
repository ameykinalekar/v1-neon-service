<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class TargetDetail extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'target_detail_id';

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject', 'subject_id', 'subject_id');
    }

}
