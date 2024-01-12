<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ProjectStartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default editable directories  (Models,Migrations,Tests,Cores) for Editting via editor API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        umask(0000);
        $dirs = [
            'app/Cores', 'app/Models/CustomModels','app/Models/BasicModels',
            'tests', 'testlogs', 'public/js',
            'resources/views/projects', 'database/migrations/projects',
            'database/migrations/alters', 'storage','storage/framework','storage/framework/cache','storage/framework/cache/data', 'public/uploads'
        ];
        
        try{
            foreach( $dirs as $idx => $dir ){
                $dir = base_path( $dir );
                if( File::exists( $dir ) ) {
                    chmod($dir, 0777);
                    $this->info("Chmod 777 to existing: $dir");
                }else{
                    mkdir($dir, 0777, true);
                    $this->info("Created Successfully: $dir");
                }
            }

        }catch(\Exception $err){
            $this->error($dir. "-". $err->getMessage());
        }
    }
}
