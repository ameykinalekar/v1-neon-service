<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Attendance extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'attendance_id';

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson', 'lesson_id', 'lesson_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\ViewStudent', 'user_id', 'user_id');
    }
    public function creator()
    {
        return $this->belongsTo('App\Models\ViewTeacher', 'created_by', 'user_id');
    }

}
