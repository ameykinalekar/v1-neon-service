<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Task extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'task_id';

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'user_id');
    }
    public function allocations()
    {
        return $this->hasMany('App\Models\TaskAllocation', 'task_id', 'task_id');
    }
    public function exams()
    {
        return $this->hasMany('App\Models\TaskExamination', 'task_id', 'task_id');
    }

}
