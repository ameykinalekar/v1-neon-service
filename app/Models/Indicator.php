<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Indicator extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'indicator_id';

    public function sub_indicators()
    {
        return $this->hasMany('App\Models\SubIndicator', 'indicator_id', 'indicator_id');
    }
}
