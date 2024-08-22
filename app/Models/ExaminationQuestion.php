<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ExaminationQuestion extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'examination_question_id';

    public function question()
    {
        return $this->belongsTo('App\Models\Question', 'question_id', 'question_id');
    }
    public function subquestions()
    {
        return $this->hasMany(self::class, 'parent_examination_question_id');
    }

}
