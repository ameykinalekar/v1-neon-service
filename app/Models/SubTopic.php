<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class SubTopic extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'sub_topic_id';

    public function topic()
    {
        return $this->belongsTo('App\Models\Topic', 'topic_id', 'topic_id');
    }
    public function subject()
    {
        return $this->belongsTo('App\Models\Subject', 'subject_id', 'subject_id');
    }

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson', 'lesson_id', 'lesson_id');
    }

}
