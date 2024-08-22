<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Country extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];

    protected $primaryKey = 'country_id';
}
