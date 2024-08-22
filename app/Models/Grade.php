<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Grade extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'grade_id';
}
