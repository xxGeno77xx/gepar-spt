<?php

namespace App\Console\Commands;

use App\Mail\VisiteMail;
use App\Models\User;
use App\Support\Database\RolesEnum;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
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
    // public function handle()
    // {
    //     $visiteMail = new VisiteMail;

    //     $notifiedUsers = User::where('notification', true)->pluck('email');

    //     if (count($visiteMail->mailableEngines) >= 1) {
    //         (Mail::to($notifiedUsers)->send(new VisiteMail($visiteMail->mailableEngines)));

    //         foreach ($visiteMail->mailableEngines as $engine) {
    //             $engine->visites_mail_sent = true;
    //             $engine->save();
    //         }
    //         $this->info('The command was successful, Visite mails sent!!!');
    //     } else {
    //         $this->info('The command successfull but no Visite mails to send!!!');
    //     }

    // }

    public function handle()
    {

        $visiteMail = new VisiteMail;

        $notifiedUsers = User::Role(RolesEnum::Dpl()->value)->get(); // to do: change to DPL users

        if (count($visiteMail->mailableEngines) >= 1) {

            Notification::make('alerte')
                ->title('Alerte Visite technique')
                ->icon('heroicon-o-information-circle')
                ->iconColor('danger')
                ->body('Les visites techniques des engins suivants arrivent à expiration:')
                ->actions(function () use ($visiteMail) {

                    foreach ($visiteMail->mailableEngines as $engine) {
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

            Mail::to($notifiedUsers)->send($visiteMail);

            $this->info('The command was successful, Visite notif sent!!!');
        } else {
            $this->info('The command successfull but no Visite notif to be sent!!!');
        }

    }
}
