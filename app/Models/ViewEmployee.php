<?php

namespace App\Models;

use Eloquent;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ViewEmployee extends Eloquent implements Auditable
{
    protected $table = 'employees';
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'user_id';
}
