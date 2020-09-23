<?php

namespace App\Jobs;

class SendEmail extends Job
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
        SendEmail(
            $this->data['to'],
            $this->data['subject'],
            $this->data['template']
        );
    }
}
