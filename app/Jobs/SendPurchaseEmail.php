<?php

namespace App\Jobs;

use Mail;
use App\Http\Models\Receipt;
use App\Jobs\Job;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPurchaseEmail extends Job implements SelfHandling, ShouldQueue
{
    use SerializesModels;

    protected $receipt;
    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $mailer->raw('You have received a new purchase of '. $this->receipt->product->credits .' credits for '. $this->receipt->price, function ($message) {

            $message->from('billing@whatscarrier.com', 'Whatscarrier');

            $message->subject('You have received new purchase')->to('mohd.sulaiman@sudirman.info');
            
        });
    }
}