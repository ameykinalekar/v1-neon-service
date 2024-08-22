<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class TaskAllocation extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'task_allocation_id';

    public function students()
    {
        return $this->belongsTo('App\Models\ViewStudent', 'user_id', 'user_id');
    }
}
