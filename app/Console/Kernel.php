<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\BackupCommand::class,
        \App\Console\Commands\RestoreCommand::class,
        \App\Console\Commands\GenerateModelsCommand::class,
        \App\Console\Commands\GenerateBasicMigrationsCommand::class,
        \App\Console\Commands\ProjectStartCommand::class
        // \KitLoong\MigrationsGenerator\MigrateGenerateCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        try{
            if( !Schema::hasTable('default_schedules') ) return;
            $tasks = DB::table('default_schedules')->where('status','ACTIVE')->get();
        }catch(\Exception $e){
            return;
        }
        foreach($tasks as $task){
            $daysArr = $task->days?json_decode($task->days, true):[0, 1, 2, 3, 4, 5, 6];
            $every = trim( $task->every );
            $every_param = $task->every_param;
            $schedule->call(function ()use($task) {
                $class = $task->class_name;
                $func = $task->func_name;
                $param = @$task->parameter_values?json_decode($task->parameter_values,true):null;
                getCustom($class)->$func($param);
            })->before(function ()use($task) {
                DB::table('default_schedules')->where('id', $task->id)->update([
                    'last_executed_at'=>Carbon::now()
                ]);
            })->after(function ()use($task) {
                DB::table('default_schedules')->where('id', $task->id)->update([
                    'end_executed_at'=>Carbon::now()
                ]);
            })->onSuccess(function () {
                // The task succeeded...
            })->onFailure(function (Stringable $output)use($task) {
                DB::table('default_schedules_failed')->insert([
                    'schedule_id' => $task->id,
                    'title' => $task->title,
                    'note' => $output->toString(),
                    'created_at'=>Carbon::now()
                ]);
            })->$every( $every_param )
            ->between($task->start_at??'00:00', $task->end_at??'23:59')
            ->days($daysArr);
        }
    }
}
