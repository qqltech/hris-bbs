<?php

namespace App\Console\Commands;

use File;
use Exception;
use Illuminate\Console\Command;
/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class BackupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "backup {--path=} {--with-upload}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Export All Editable Project Files to Directory";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->option('path')??(env('BACKUP_PATH')?env('BACKUP_PATH'):base_path('app_generated_backup'));
        $withUpload = $this->option('with-upload');

        try {
            umask(0000);
            if( ! File::exists( $path ) ){
                File::makeDirectory( $path, 493, true);
                File::makeDirectory( $path."/sqldump", 493, true);
            }
            File::copyDirectory(app_path('Models/CustomModels'), "$path/app/Models/CustomModels" );
            File::copyDirectory(base_path('tests'), "$path/tests" );
            File::copyDirectory(database_path('migrations/projects'), "$path/database/migrations/projects" );
            File::copyDirectory(database_path('migrations/alters'), "$path/database/migrations/alters" );
            File::put("$path/env", File::get(base_path('.env')) );
            File::copyDirectory(public_path('js'), "$path/public/js" );
            if($withUpload){
                File::copyDirectory(public_path('uploads'), "$path/public/uploads" );                
            }
            File::copyDirectory(resource_path('views/projects'), "$path/resources/views/projects" );

            $schemaManager = \DB::getDoctrineSchemaManager();
            $schemaManager->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            
            $sqlLineList = $schemaManager->createSchema()->toSql($schemaManager->getDatabasePlatform());
            File::put("$path/sqldump/000-database-schema-only.sql", implode(";\n", $sqlLineList) );

            $ds = DIRECTORY_SEPARATOR;

            $host = env('DB_HOST');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $database = env('DB_DATABASE');
            
            $file = date('Y-m-d') . '-dump-' . $database . '.sql';
            $command = sprintf('mysqldump --column-statistics=0 -h %s -u %s -p\'%s\' %s > %s', 
                        $host, 
                        $username, 
                        $password, 
                        $database, 
                        "$path/sqldump/$file");
            exec($command);
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->info("project files have been copied to $path successfully");
    }
}