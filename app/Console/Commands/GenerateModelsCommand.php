<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class GenerateModelsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "generate:model";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Re-Generate All Basic Models & models.json";


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

            $cont->createModels($req, 'abcdefghijklmnopq' );
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->info("Refresh semua basic models berhasil");
    }
}