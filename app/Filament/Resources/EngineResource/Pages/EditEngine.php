<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use App\Filament\Resources\EngineResource\Widgets\EngineFuelConsumption;
use App\Models\Chauffeur;
use App\Models\Engine;
use App\Models\Engine as Engin;
use App\Models\User;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngine extends EditRecord
{
    protected static string $resource = EngineResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::engines_delete()->value])) {
            return [
                // Actions\DeleteAction::make(),
                Actions\Action::make('Supprimer')
                    ->color('danger')
                    ->action(function (?Engin $record) {
                        $this->record->update(['state' => StatesClass::Deactivated()->value]);
                        redirect('/engines');
                        Notification::make()
                            ->title('Supprimé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->requiresConfirmation(),

                // Actions\Action::make("Retirer_du_patrimoine")
                //     ->color('danger')
                //     ->steps([
                //         Step::make('Motif du retrait')
                //             ->description('Donnez la raison du retrait')
                //             ->schema([
                //                 Select::make('Motif')
                //                     ->options([
                //                         'Véhicule cassé',
                //                         'Kilométrage excessif',
                //                         "Non conformité",
                //                         "Vente"
                //                     ])
                //                     ->searchable()
                //                     ->required()
                //                     ->reactive(),

                //                 Select::make('Acheteur')
                //                     ->searchable()
                //                     ->visible(fn($get): bool => $get('Motif') == 1) //change option value to be the desired select field
                //                     ->options([
                //                         'Proprietaire actuel',
                //                         'Nouveau propriétaire',
                //                     ])

                //             ])
                //             ->columns(2),
                //         Step::make('Details')
                //             ->description('Rajoutez quelques détails sur le retrait')
                //             ->schema([
                //                 MarkdownEditor::make('description'),
                //             ]),
                //         Step::make('Visibility')
                //             ->description('Control who can view it')
                //             ->schema([
                //                 Toggle::make('is_visible')
                //                     ->label('Visible to customers.')
                //                     ->default(true),
                //             ]),

                //     ])

                //     ->action(function (?Engin $record) {

                //         redirect('/engines');
                //         Notification::make()
                //             ->title('Retrait du patrimoine')
                //             ->iconColor('danger')
                //             ->body('L\'engin immatriculé ' . $this->record->plate_number . ' ne fait désormais plus partie de votre patrimoine')
                //             ->icon('heroicon-o-shield-exclamation')
                //             ->persistent()
                //             ->send();
                //     })

            ];
        } else {
            return [];
        }
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // public function afterSave()
    // {
    //     $engin = $this->record;

    //     $engin->update(['updated_at_user_id' => auth()->user()->id]);

    //     $data = $this->data;

    // if(isset( $data["chauffeur_id"]))
    // {
    //     Chauffeur::find($data['chauffeur_id'])->update([
    //         "engine_id" => Engine::where('plate_number', $data["plate_number"])->value('id')
    //     ]);
    // }
}

// public function beforeSave()
// {
//     $engin = $this->record;

//     $data = $this->data;

//     $chauffeurSelectionne =  $data['chauffeur_id'];

//     $chauffeur = Chauffeur::find($chauffeurSelectionne);

//     if ( $chauffeurSelectionne ) {

//         if (!is_null($chauffeur->engine_id) && $chauffeur->id !== $engin->chauffeur_id) {
//             Notification::make()
//                 ->warning()
//                 ->title('Attention!')
//                 ->body("Le chauffeur choisi est déjà associé à un engin")
//                 ->persistent()
//                 ->send();

//             $this->halt();
//         }

//     }
//     if (!isset($data["chauffeur_id"])) {
//         Chauffeur::find($engin['chauffeur_id'])->update([
//             "engine_id" => null
//         ]);
//     }

// }

// protected function getFooterWidgets(): array
// {
//     return [
//         EngineFuelConsumption::class
//     ];
// }

// }
