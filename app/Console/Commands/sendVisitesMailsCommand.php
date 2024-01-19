<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\VisiteMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;



class sendVisitesMailsCommand extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visites:sendMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = ' sends reminder mails about expired visites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $visiteMail= new VisiteMail;
        
        {
             $notifiedUsers=User::where('notification', true)->pluck('email');


             if(count($visiteMail->mailableEngines)>=1 )
             {
                 (Mail::to($notifiedUsers)->send(new VisiteMail($visiteMail->mailableEngines)));
                 
                foreach($visiteMail->mailableEngines as $engine)
                 {
                    $engine->visites_mail_sent=true;
                    $engine->save();
                 }
                 $this->info('The command was successful, Visite mails sent!!!');
             }
            else $this->info('The command successfull but no Visite mails to send!!!');

            
        };
        
    }
}
