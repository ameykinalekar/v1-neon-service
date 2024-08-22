<?php

namespace App\Models;

use Eloquent;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ViewPlan extends Eloquent implements Auditable
{

    protected $table = 'plans';
    use AuditableTrait;
    protected $guarded = [];
}
