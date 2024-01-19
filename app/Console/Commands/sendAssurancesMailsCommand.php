<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\AssuranceMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class sendAssurancesMailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assurances:sendMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check and send assurance mails if any';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assuranceMail= new AssuranceMail;

        
        {
             $notifiedUsers=User::where('notification', true)->pluck('email');

             if(count($assuranceMail->mailableEngines)>=1 )
             {
                (Mail::to($notifiedUsers)->send(new AssuranceMail($assuranceMail->mailableEngines)));

                foreach($assuranceMail->mailableEngines as $engine)
                 {
                    $engine->assurances_mail_sent=true;
                    $engine->save();
                 }
                 $this->info('The command was successful, Assurance mails sent!!!');
             }
            else $this->info('The command successfull but no Assurance mails to be sent!!!');

            
        };
    }
}
