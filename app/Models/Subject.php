<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Subject extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'subject_id';

    public function yeargroup()
    {
        return $this->belongsTo('App\Models\YearGroup', 'year_group_id', 'year_group_id');
    }

    public function academicyear()
    {
        return $this->belongsTo('App\Models\AcademicYear', 'academic_year_id', 'academic_year_id');
    }

}
