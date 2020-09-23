<?php

namespace App\Jobs;
use App\Models\Defaults\Activities;
class DefaultActivities extends Job
{
    //delete,update,
    public $data=[];
    public function __construct($data)
    {
        $this->data=$data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Activities::create($this->data);
    }
}
