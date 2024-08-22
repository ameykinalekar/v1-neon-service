<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class MessageUser extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = [];
    protected $connection = 'tenantdb';
    protected $primaryKey = 'message_user_id';

}
