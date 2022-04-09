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
class RestoreCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "restore {--src=} {--with-upload}";

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
        $path = $this->option('src')??(env('BACKUP_PATH')?env('BACKUP_PATH'):base_path('app_generated_backup'));
        $withUpload = $this->option('with-upload');

        try {
            umask(0000);
            if( ! File::exists( $path ) ){
                File::makeDirectory( $path, 493, true);
                File::makeDirectory( $path."/sqldump", 493, true);
            }

            File::copyDirectory( "$path/app/Models/CustomModels", app_path('Models/CustomModels') ); // CustomModels
            File::copyDirectory( "$path/tests", base_path('tests') ); // tests
            File::copyDirectory( "$path/database/migrations/projects", database_path('migrations/projects') ); //   migrations
            File::copyDirectory( "$path/database/migrations/alters", database_path('migrations/alters') );  //  alters
            File::put( base_path(".env"), File::get("$path/env") ); // .env
            File::copyDirectory(  "$path/public/js", public_path('js') ); // public/js

            if($withUpload){
                File::copyDirectory( "$path/public/uploads", public_path('uploads') );      // public/uploads            
            }

            File::copyDirectory( "$path/resources/views/projects", resource_path('views/projects') );   //  views/projects

            // $host = env('DB_HOST');
            // $username = env('DB_USERNAME');
            // $password = env('DB_PASSWORD');
            // $database = env('DB_DATABASE');
            
            // $pathFile = "$path/sqldump/";
            // // --column-statistics=0
            // $command = sprintf('mysql -h %s -u %s -p\'%s\' %s < %s', 
            //             $host, 
            //             $username, 
            //             $password, 
            //             $database, 
            //             "$path/sqldump/$file");
            
            // exec($command);
            
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->info("project files have been restored from $path successfully");
    }
}