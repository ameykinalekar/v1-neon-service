<?php

namespace App\Http\Middleware;

use App\Helpers\CommonHelper;
use App\Models\Tenant;
use Closure;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        $current_uri = request()->segments();
        // dd($request->all());
        if (count($current_uri) > 1 && $current_uri[0] == 'api') {
            $subdomain = $current_uri[1];
            $tenantDatabase = '';
            $tenant_info = Tenant::where('tenants.subdomain', $subdomain)->first();
            $dbpss = '';
            $dbuser = '';
            if (!empty($tenant_info)) {
                $dbpss = CommonHelper::decryptId($tenant_info->dbpassword);
                $tenantDatabase = $tenant_info->dbname;
                $dbuser = $tenant_info->dbuser;
            }

            Config::set('database.connections.tenantdb.database', $tenantDatabase);
            Config::set('database.connections.tenantdb.username', $dbuser);
            Config::set('database.connections.tenantdb.password', $dbpss);

            DB::reconnect('tenantdb');

            // Continue processing the request
            $response = $next($request);

            // Reset the database connection
            DB::disconnect('tenantdb');
        } else {
            $response = $next($request);
        }

        return $response;

        // if (in_array($locale, config('app.locales'))) {
        //     \App::setLocale($locale);
        //     return $next($request);
        // }

        // if (!in_array($locale, config('app.locales'))) {

        //     $segments = $request->segments();
        //     $segments[0] = config('app.fallback_locale');

        //     return redirect(implode('/', $segments));
        // }
    }
}
