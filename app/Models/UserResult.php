<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class UserResult extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'user_result_id';

    public function inputs()
    {
        return $this->hasMany('App\Models\UserResultInput', 'user_result_id', 'user_result_id');
    }

    public function consumer()
    {
        return $this->belongsTo('App\Models\ViewStudent', 'user_id', 'user_id');
    }

    public function examination()
    {
        return $this->belongsTo('App\Models\Examination', 'examination_id', 'examination_id');
    }
}
