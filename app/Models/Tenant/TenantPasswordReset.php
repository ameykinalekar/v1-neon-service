<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class TenantPasswordReset extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $table = 'password_resets';
    protected $connection = 'tenantdb';

    const UPDATED_AT = null;
}
