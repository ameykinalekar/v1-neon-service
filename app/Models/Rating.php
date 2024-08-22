<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Rating extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'rating_id';

    public function teacher()
    {
        return $this->belongsTo('App\Models\ViewTeacher', 'creator_id', 'user_id');
    }

    public function student()
    {
        return $this->belongsTo('App\Models\ViewStudent', 'rating_created_by', 'user_id');
    }
    
    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson', 'lesson_id', 'lesson_id');
    }
}
