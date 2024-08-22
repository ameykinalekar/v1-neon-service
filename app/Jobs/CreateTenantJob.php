<?php
// app/Jobs/CreateTenantJob.php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CreateTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;
    private $response;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
        // dd($tenant);
    }

    public function handle()
    {
        // Extract subdomain and other tenant details
        $subdomain = $this->tenant->subdomain;
        $dbpss = CommonHelper::decryptId($this->tenant->dbpassword);
        // Add other details as needed

        $tenantDatabase = "tenant_" . $subdomain;

        $dbCreated = false;
        $dbUserCreated = false;
        $dbUserGranted = false;
        $dataMigrated = false;

        // Now we can create a MySQL Database
        $schema = $this->createSchema($tenantDatabase);
        // dd($schema);
        if ($schema) {
            $dbCreated = true;
            $createUser = $this->createUser($tenantDatabase, $dbpss);
            // dd($createUser);
            if ($createUser) {
                $dbUserCreated = true;
                $grantUser = $this->grantPermission($tenantDatabase, $tenantDatabase);
                if ($grantUser) {
                    $dbUserGranted = true;
                    try {
                        Config::set('database.connections.tenantdb.database', $tenantDatabase);
                        Config::set('database.connections.tenantdb.username', $tenantDatabase);
                        Config::set('database.connections.tenantdb.password', $dbpss);

                        DB::reconnect('tenantdb');
                        $pdo = DB::connection('tenantdb')->getPdo();

                        $sql = file_get_contents('../scripts/tenant_neonedu.sql');

                        $qr = $pdo->exec($sql);

                        $studentView = "CREATE VIEW students AS SELECT user_profiles.*,users.tenant_id,users.role,users.email,users.phone,users.user_logo,users.status,users.code,(SELECT GROUP_CONCAT(year_groups.name) FROM `user_year_groups`,year_groups WHERE year_groups.year_group_id=user_year_groups.year_group_id and user_year_groups.`user_id`=users.user_id) as year_group_names,(SELECT GROUP_CONCAT(user_year_groups.year_group_id) FROM `user_year_groups` WHERE  user_year_groups.`user_id`=users.user_id) as year_group_ids,(SELECT GROUP_CONCAT(user_subjects.subject_id) FROM `user_subjects` WHERE  user_subjects.`user_id`=users.user_id) as subject_ids,(SELECT GROUP_CONCAT(concat(year_groups.name,'-',subjects.subject_name),' ') FROM `user_subjects`,subjects,year_groups WHERE subjects.subject_id=user_subjects.subject_id and subjects.year_group_id=year_groups.year_group_id and user_subjects.`user_id`=users.user_id) as subject_names FROM users INNER JOIN user_profiles on user_profiles.user_id=users.user_id where users.user_type='TU' and users.role='S'  ORDER BY users.user_id";

                        $qr = $pdo->exec($studentView);

                        $teacherView = "CREATE VIEW teachers AS SELECT user_profiles.*,users.tenant_id,users.role,users.email,users.phone,users.user_logo,users.status,(SELECT GROUP_CONCAT(year_groups.name) FROM `user_year_groups`,year_groups WHERE year_groups.year_group_id=user_year_groups.year_group_id and user_year_groups.`user_id`=users.user_id) as year_group_names,(SELECT GROUP_CONCAT(user_year_groups.year_group_id) FROM `user_year_groups` WHERE  user_year_groups.`user_id`=users.user_id) as year_group_ids,(SELECT GROUP_CONCAT(user_subjects.subject_id) FROM `user_subjects` WHERE  user_subjects.`user_id`=users.user_id) as subject_ids,(SELECT GROUP_CONCAT(concat(year_groups.name,'-',subjects.subject_name),' ') FROM `user_subjects`,subjects,year_groups WHERE subjects.subject_id=user_subjects.subject_id and subjects.year_group_id=year_groups.year_group_id and user_subjects.`user_id`=users.user_id) as subject_names FROM users INNER JOIN user_profiles on user_profiles.user_id=users.user_id where users.user_type='TU' and users.role='T'  ORDER BY users.user_id";

                        $qr = $pdo->exec($teacherView);

                        $teacherAssistantView = "CREATE VIEW teacher_assistants AS SELECT user_profiles.*,users.tenant_id,users.role,users.email,users.phone,users.user_logo,users.status,(SELECT GROUP_CONCAT(year_groups.name) FROM `user_year_groups`,year_groups WHERE year_groups.year_group_id=user_year_groups.year_group_id and user_year_groups.`user_id`=users.user_id) as year_group_names,(SELECT GROUP_CONCAT(user_year_groups.year_group_id) FROM `user_year_groups` WHERE  user_year_groups.`user_id`=users.user_id) as year_group_ids,(SELECT GROUP_CONCAT(user_subjects.subject_id) FROM `user_subjects` WHERE  user_subjects.`user_id`=users.user_id) as subject_ids,(SELECT GROUP_CONCAT(concat(year_groups.name,'-',subjects.subject_name),' ') FROM `user_subjects`,subjects,year_groups WHERE subjects.subject_id=user_subjects.subject_id and subjects.year_group_id=year_groups.year_group_id and user_subjects.`user_id`=users.user_id) as subject_names FROM users INNER JOIN user_profiles on user_profiles.user_id=users.user_id where users.user_type='TU' and users.role='TA'  ORDER BY users.user_id";

                        $qr = $pdo->exec($teacherAssistantView);

                        $employeeView = "CREATE VIEW employees AS SELECT user_profiles.*,users.tenant_id,users.role,users.email,users.phone,users.user_logo,users.status,(SELECT department_name FROM `departments` WHERE departments.`department_id`=user_profiles.department_id) as department_name FROM users INNER JOIN user_profiles on user_profiles.user_id=users.user_id where users.user_type='TU' and users.role='OU'  ORDER BY users.user_id";

                        $qr = $pdo->exec($employeeView);

                        $parentView = "CREATE VIEW parents AS SELECT user_profiles.*,users.tenant_id,users.role,users.email,users.code,users.phone,users.user_logo,users.status FROM users INNER JOIN user_profiles on user_profiles.user_id=users.user_id where users.user_type='P' and users.role='P'  ORDER BY users.user_id";

                        $qr = $pdo->exec($parentView);

                        // Reset the database connection
                        DB::disconnect('tenantdb');
                        $dataMigrated = true;

                    } catch (\Exception $e) {
                        //throw $e;
                    }

                }

            }

        }
        $this->response = [
            'dbcreated' => $dbCreated,
            'dbusercreated' => $dbUserCreated,
            'dbusergranted' => $dbUserGranted,
            'datamigrated' => $dataMigrated,
        ];

    }
    public function getResponse()
    {
        return $this->response;
    }
    /**
     * Creates a new database schema.

     * @param  string $schemaName The new schema name.
     * @return bool
     */
    public function createSchema($schemaName)
    {
        // We will use the `statement` method from the connection class so that
        // we have access to parameter binding.
        return DB::connection('mysql')->statement("CREATE DATABASE " . $schemaName);
    }
    public function createUser($username, $password)
    {
        // We will use the `statement` method from the connection class so that
        // we have access to parameter binding.
        return DB::connection('mysql')->statement("CREATE USER '" . $username . "'@'%' IDENTIFIED BY '" . $password . "'");
    }
    public function grantPermission($username, $schemaName)
    {
        // We will use the `statement` method from the connection class so that
        // we have access to parameter binding.
        return DB::connection('mysql')->statement("GRANT ALL PRIVILEGES ON `" . $schemaName . "`.* TO '" . $username . "'@'%' WITH GRANT OPTION;");
    }
}
