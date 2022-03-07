<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AccountHolder;

class ProcessAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * AccountHolder instance
     * @var AccountHolder
     */
    protected AccountHolder $accountHolder;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AccountHolder $accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->accountHolder->save();
    }
}
