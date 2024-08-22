<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Tenant extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $fillable = ['subdomain', 'dbname', 'dbuser', 'dbpassword'];
    protected $guarded = [];
    protected $primaryKey = 'tenant_id';
}
