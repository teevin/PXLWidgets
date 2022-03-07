<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CreditCard;

class ProcessCreditCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * CreditCard instance to save
     * @var CreditCard
     */
    protected CreditCard $creditCard;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->creditCard->save();
    }
}
