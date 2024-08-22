<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class OfsteadFinance extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'ofstead_finance_id';

    public function sub_indicator()
    {
        return $this->belongsTo('App\Models\SubIndicator', 'sub_indicator_id', 'sub_indicator_id');
    }
}
