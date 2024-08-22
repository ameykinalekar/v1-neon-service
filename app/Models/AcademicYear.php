<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class AcademicYear extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'academic_year_id';
}
