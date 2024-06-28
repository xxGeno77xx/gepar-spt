<?php

namespace App\Console\Commands;

use App\Mail\TvmMail;
use App\Models\User;
use App\Support\Database\RolesEnum;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class SendTvmMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tvms:sendMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check for expiring tvms and send mails if found';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $tvmMail = new TvmMail;

        $notifiedUsers = User::Role(RolesEnum::Dpl()->value)->get(); // to do: change to DPL users

        if (count($tvmMail->mailableEngines) >= 1) {

            Notification::make('alerte')
                ->title('Alerte Visite technique')
                ->icon('heroicon-o-information-circle')
                ->iconColor('danger')
                ->body('Les visites techniques des engins suivants arrivent à expiration:')
                ->actions(function () use ($tvmMail) {

                    foreach ($tvmMail->mailableEngines as $engine) {
                        $engine->visites_mail_sent = true;
                        $engine->save();

                        return [
                            Action::make('view')
                                ->label($engine->plate_number)
                                ->color('danger')
                                ->url(route('filament.resources.engines.view', $engine->id), shouldOpenInNewTab: true)
                                ->button(),
                        ];
                    }
                })

                ->sendToDatabase($notifiedUsers);

            // (Mail::to($notifiedUsers)->send(new VisiteMail($visiteMail->mailableEngines)));

            $this->info('The command was successful, tvm notif sent!!!');
        } else {
            $this->info('The command successfull but no tvm notif to be sent!!!');
        }

    }
}
