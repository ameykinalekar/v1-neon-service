<?php

namespace App\Traits;

use App\Helpers\CommonHelper;
use App\Models\Tenant;
use GlobalVars;
use JWTAuth;
use OwenIt\Auditing\Models\Audit;

trait GeneralMethods
{

    //Retrieve tenant id from subdomain part in url
    public function getTenantId()
    {
        $current_uri = request()->segments();
        // dd($current_uri[1]);
        if (count($current_uri) > 1 && $current_uri[0] == 'api') {
            $subdomain = $current_uri[1];
        } else {
            $subdomain = $current_uri[0] ?? '';
        }
        // $subdomain = explode('.', request()->getHost())[0];
        $tenantId = "";
        $tenant = Tenant::where('subdomain', '=', $subdomain)->select('tenant_id')->first();
        // dd($tenant);
        if (!empty($tenant)) {
            $tenantId = $tenant->tenant_id;
        }

        return $tenantId;
    }

    //Retrieve subdomain part in url
    public function getTenantSubdomain()
    {
        $current_uri = request()->segments();
        // dd($current_uri[1]);
        if (count($current_uri) > 1 && $current_uri[0] == 'api') {
            $subdomain = $current_uri[1];
        } else {
            $subdomain = '';
        }
        // $subdomain = explode('.', request()->getHost())[0];
        // $tenantId = "";
        // $tenant = Tenant::where('subdomain', '=', $subdomain)->select('tenant_id')->first();
        // if (!empty($tenant)) {
        //     $tenantId = $tenant->tenant_id;
        // }

        return $subdomain;
    }

    //Invalidate Portal Admin or Portal Admin users
    public function invalidatePortalUser()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->tenant_id > 0) {
            return false;
        }
        return true;
    }
    //Invalidate Trustee users
    public function invalidateTrusteeUser()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->user_type != GlobalVars::TRUSTEE_USER_TYPE) {
            return false;
        }
        return true;
    }

    //Invalidate Tenant Admin or Tenant Admin users
    public function invalidateTenantUser()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->tenant_id > 0 && $this->tenantId != $user->tenant_id) {
            return false;
        }
        return true;
    }

    //Audit LogIn
    public function auditValidLogIn($credentials, $token, $guard = '')
    {
        if (isset($credentials['password'])) {
            $credentials['password'] = CommonHelper::encryptId($credentials['password']);
        }

        $auditable_type = "App\Models\User";
        if ($guard == 'tenant') {
            $auditable_type = "App\Models\Tenant\User";
        }

        $audit = new Audit([
            'auditable_id' => auth($guard)->user()->user_id,
            'auditable_type' => $auditable_type,
            'event' => "Logged In",
            'old_values' => $credentials,
            'new_values' => $token,
            'url' => request()->fullUrl(),
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
        ]);

        $audit->user()->associate(auth($guard)->user());
        $audit->save();
    }

    //Audit Invalid LogIn attempt
    public function auditInvalidLogIn($credentials, $guard = '')
    {
        if (isset($credentials['password'])) {
            $credentials['password'] = CommonHelper::encryptId($credentials['password']);
        }
        $auditable_type = "App\Models\User";
        if ($guard == 'tenant') {
            $auditable_type = "App\Models\Tenant\User";
        }
        $audit = new Audit([
            'auditable_id' => 0,
            'auditable_type' => $auditable_type,
            'event' => "Invalid Log In attempt",
            'old_values' => $credentials,
            'url' => request()->fullUrl(),
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
        ]);

        $audit->user()->associate(auth()->user());
        $audit->save();
    }

    //Audit LogOut
    public function auditLogOut($token)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $audit = new Audit([
            'auditable_id' => $user->user_id,
            'auditable_type' => "App\Models\User",
            'event' => "Log Out",
            'old_values' => $token,
            'url' => request()->fullUrl(),
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
        ]);

        $audit->user()->associate(auth()->user());
        $audit->save();
    }
}
