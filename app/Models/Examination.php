<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Examination extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'examination_id';

    public function examquestions()
    {
        return $this->hasMany('App\Models\ExaminationQuestion', 'examination_id', 'examination_id')->where('examination_questions.parent_examination_question_id', '0')->orderBy('page_id', 'asc')->orderBy('examination_question_id', 'asc');
    }

    public function subject()
    {
        return $this->belongsTo('App\Models\Subject', 'subject_id', 'subject_id');
    }

}
