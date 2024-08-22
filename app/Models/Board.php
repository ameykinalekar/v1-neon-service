<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Board extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];

    protected $primaryKey = 'board_id';

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'country_id');
    }
}
