<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Lesson extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'lesson_id';

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject', 'subject_id', 'subject_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'user_id');
    }
}
