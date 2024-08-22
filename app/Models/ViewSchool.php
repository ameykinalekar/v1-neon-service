<?php

namespace App\Models;

use Eloquent;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ViewSchool extends Eloquent implements Auditable
{

    protected $table = 'schools';
    use AuditableTrait;
    protected $guarded = [];
}
