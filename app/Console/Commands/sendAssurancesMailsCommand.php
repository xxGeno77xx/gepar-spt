<?php

namespace App\Console\Commands;

use App\Mail\AssuranceMail;
use App\Models\User;
use App\Support\Database\RolesEnum;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

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
        $assuranceMail = new AssuranceMail;

        $notifiedUsers = User::Role(RolesEnum::Dpl()->value)->get(); // to do: change to DPL users

        if (count($assuranceMail->mailableEngines) >= 1) {

            Notification::make('alerte')
                ->title('Alerte assurance')
                ->icon('heroicon-o-information-circle')
                ->iconColor('danger')
                ->body('Les assurances des engins suivants arrivent à expiration:')
                ->actions(function () use ($assuranceMail) {

                    foreach ($assuranceMail->mailableEngines as $engine) {
                        $engine->assurances_mail_sent = true;
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

            $this->info('The command was successful, Assurance notif sent!!!');
        } else {
            $this->info('The command successfull but no Assurance notif to be sent!!!');
        }

    }
}
