<?php

namespace App\Jobs;

class Background extends Job
{
    
    public $class=null;
    public $function=null;
    public $arguments=null;
    public function __construct($class,$function,$arguments)
    {
        $this->class    = $class;
        $this->function = $function;
        $this->arguments= $arguments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $class = new $this->class;
        call_user_func_array([$class, $this->function], $this->arguments);
    }
}
