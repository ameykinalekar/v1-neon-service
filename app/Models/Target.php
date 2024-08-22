<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Target extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'target_id';

    public function details()
    {
        return $this->hasMany('App\Models\TargetDetail', 'target_id', 'target_id');
    }
    public function yeargroup()
    {
        return $this->belongsTo('App\Models\YearGroup', 'year_group_id', 'year_group_id');
    }
    public function student()
    {
        return $this->belongsTo('App\Models\ViewStudent', 'user_id', 'user_id');
    }
}
