<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class SubscriptionPlan extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $primaryKey = 'subscription_plan_id';
}
