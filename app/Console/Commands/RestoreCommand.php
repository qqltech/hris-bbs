<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\LaradevController as Laradev;
use Illuminate\Support\Str;
/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class RestoreCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "restore {--src=} {--with-upload} {--migrate} {--with-env}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Restore All Editable Project Files to Project App";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $isFromUrl = false;
        $path = $this->option('src')??(env('BACKUP_PATH')?env('BACKUP_PATH'):base_path('app_generated_backup'));
        if( str_starts_with($path, 'https') ){
            $isFromUrl = true;
            $this->info( "Download zip file from $path" );
            $temp_file = tempnam(sys_get_temp_dir(), 'temporary_file_backup.zip');
            
            unlink($temp_file);
            
            if( !copy($path, $temp_file) ) return $this->error("failed to download backup file from $path");

            $zip = new \ZipArchive();
            if ($zip->open($temp_file) === TRUE) {
                $path =  env('RESTORE_PATH', tempnam(sys_get_temp_dir(), 'temporary_directory_backup'));
                $isFromUrl = !env('RESTORE_PATH');
                if(File::exists($path)) File::deleteDirectory($path);
                mkdir($path);
                $zip->extractTo( $path );
                $zip->close();
                unlink($temp_file);
            } else {
                return $this->error("failed to extract backup file from $temp_file");
            }
        }
        $CommandOptions = $this->options();
        $withUpload = $this->option('with-upload');
        $withMigrate = $this->option('migrate');
        $withEnv = $this->option('with-env');

        try {
            umask(0000);
            if( ! File::exists( $path ) ){
                File::makeDirectory( $path, 493, true);
                File::makeDirectory( $path."/sqldump", 493, true);
            }

            File::copyDirectory( "$path/app/Models/CustomModels", app_path('Models/CustomModels') ); // CustomModels
            File::copyDirectory( "$path/app/Cores", app_path('Cores') ); // CustomModels
            File::copyDirectory( "$path/tests", base_path('tests') ); // tests
            File::copyDirectory( "$path/database/migrations/projects", database_path('migrations/projects') ); //   migrations
            File::copyDirectory( "$path/database/migrations/alters", database_path('migrations/alters') );  //  alters
            if($withEnv) File::put( base_path(".env"), File::get("$path/env") ); // .env
            File::copyDirectory(  "$path/public/js", public_path('js') ); // public/js

            if($withUpload){
                File::copyDirectory( "$path/public/uploads", public_path('uploads') );      // public/uploads            
            }

            File::copyDirectory( "$path/resources/views/projects", resource_path('views/projects') );   //  views/projects
            umask(0000);

            $host = env('DB_HOST');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $database = env('DB_DATABASE');
            $port = env('DB_PORT');
            //  migrate to schema
            $files  = scandir("$path/sqldump", SCANDIR_SORT_DESCENDING);
            
            if( count($files)==0 ){
                trigger_error("files have been restored, but no .sql file to be migrated.");
            }

            $file = $files[0];
            
            if( $withMigrate && getDriver()=='mysql' ){
                // --column-statistics=0
                $command = sprintf('mysql -h %s -u %s -p\'%s\' %s < %s', 
                            $host, 
                            $username, 
                            $password, 
                            $database, 
                            "$path/sqldump/$file");
                
                exec( $command );
                $this->info( "migrated_sql:$file" );
            }elseif( $withMigrate && getDriver()=='pgsql' ){
                $file = str_replace(".sql", ".tar", $file);
                $command = sprintf( "pg_restore --clean --dbname=postgresql://$username:$password@$host:$port/$database %s", $sqlPath = "$path/sqldump/$file");
                exec($command);
                $this->info( "migrated_sql:$file" );
            }

            if( $isFromUrl ){
                if (File::exists($path)) File::deleteDirectory($path);
            }
            (new Laradev)->createModels( new \Illuminate\Http\Request );

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info("project files have been restored from $path successfully");
    }
}