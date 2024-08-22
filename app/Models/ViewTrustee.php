<?php

namespace App\Models;

use Eloquent;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ViewTrustee extends Eloquent implements Auditable
{
    protected $table = 'trustees';
    use AuditableTrait;
    protected $guarded = [];
}
