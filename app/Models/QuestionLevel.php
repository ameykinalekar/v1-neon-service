<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class QuestionLevel extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $primaryKey = 'question_level_id';
}
