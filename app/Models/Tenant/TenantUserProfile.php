<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class TenantUserProfile extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $table = 'user_profiles';
    protected $primaryKey = 'user_profile_id';
    protected $connection = 'tenantdb';
}
