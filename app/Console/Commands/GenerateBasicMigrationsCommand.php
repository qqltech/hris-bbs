<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class GenerateBasicMigrationsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "generate:default";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate All Basic Migration";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $cont = new \App\Http\Controllers\LaradevController;
            $req = new \Illuminate\Http\Request([ 'alter'=>'true' ]);
            Artisan::call("migrate:refresh",[
                "--path"=>"database/migrations/__defaults" , "--force"=>true
                ]
            );
            $this->info("Migrate database/migrations/__defaults OK");


            Artisan::call("db:seed");
            $this->info("Seeding OK");

            Artisan::call("passport:install");
            $this->info("Passport Installed OK");

            
            $this->info("Generating Models...");
            $cont->createModels($req, 'abcdefghijklmnopq' );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info("Generate Basic Migrations OK");
    }
}