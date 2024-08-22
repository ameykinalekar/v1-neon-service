<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class UserSubject extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $primaryKey = 'user_subject_id';
    protected $connection = 'tenantdb';
}
