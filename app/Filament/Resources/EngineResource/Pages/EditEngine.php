<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Departement;
use App\Models\Engine;
use App\Models\Engine as Engin;
use App\Models\User;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngine extends EditRecord
{
    protected static string $resource = EngineResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::Engines_update()->value])) {
            return [
                // Actions\DeleteAction::make(),
                // Actions\Action::make('Supprimer')
                //     ->color('danger')
                //     ->icon('heroicon-o-eye-off')
                //     ->action(function (?Engin $record) {
                //         $this->record->update(['state' => StatesClass::Deactivated()->value]);
                //         redirect('/engines');
                //         Notification::make()
                //             ->title('Supprimé(e)')
                //             ->success()
                //             ->persistent()
                //             ->send();
                //     })
                //     ->requiresConfirmation(),

                // Actions\Action::make('Retirer_du_patrimoine')
                //     ->color('danger')
                //     ->steps([
                //         Step::make('Motif du déclassment')
                //             ->schema([
                //                 Select::make('Motif')
                //                     ->options([
                //                         'Véhicule accidenté',
                //                         'Véhicule volé',
                //                         'Non conformité',
                //                         'Vente aux enchères',
                //                         'Autre',
                //                     ])
                //                     ->searchable()
                //                     ->required()
                //                     ->reactive(),

                //                 TextInput::make('Acheteur')
                //                     ->visible(fn ($get): bool => $get('Motif') == 4) //change option value to be the desired select field
                //                 ,

                //             ])
                //             ->columns(2),
                //         Step::make('Details')
                //             ->schema([
                //                 MarkdownEditor::make('description'),
                //             ]),

                //     ])

                //     ->action(function (?Engin $record) {

                //         dd($this->record->update);
                //         redirect('/engines');
                //         Notification::make()
                //             ->title('Retrait du patrimoine')
                //             ->iconColor('danger')
                //             ->body('L\'engin immatriculé '.$this->record->plate_number.' ne fait désormais plus partie de votre patrimoine')
                //             ->icon('heroicon-o-shield-exclamation')
                //             ->persistent()
                //             ->send();
                //     }),

                Actions\Action::make('Réaffecter')
                    ->action(function (array $data): void {

                        $engine = $this->record;

                        $oldDepartement = $this->record->departement_id;

                        $engine->update(['departement_id' => $data['departement_id']]);

                        $this->refreshFormData(['departement_id']);

                        $newDepartement = Departement::where('code_centre', '=', $this->record->departement_id)->value('sigle_centre');

                        Affectation::firstOrCreate([

                            'engine_id' => $this->record->id,
                            'departement_origine_id' => $oldDepartement,
                            'departement_cible_id' => $data['departement_id'],
                            'date_reaffectation' => $data['date_reaffectation'],
                        ]);

                        Notification::make()
                            ->title('Affectation')
                            ->iconColor('primary')
                            ->body('L\'engin immatriculé '.$this->record->plate_number.' a été affecté à '.$newDepartement)
                            ->icon('heroicon-o-chat-alt-2')
                            ->persistent()
                            ->send();

                    })
                    ->form([
                        Grid::make(3)
                            ->schema([

                                DatePicker::make('date_reaffectation')
                                    ->label('Date de réaffectation')
                                    ->beforeOrEqual(now()->format('d-m-Y'))
                                    ->required(),

                                Select::make('departement_id')
                                    ->label('De')
                                    ->disabled()
                                    ->default(Departement::where('code_centre', '=', $this->record->departement_id)->value('sigle_centre'))
                                    ->searchable(),

                                Select::make('departement_id')
                                    ->label('Vers')
                                    ->options(Departement::where('sigle_centre', '<>', '0')->pluck('sigle_centre', 'code_centre'))
                                    ->searchable()
                                    ->required(),
                            ]),

                    ])
                    ->after(function () {
                        $this->emit('refreshAffectations');
                    }),

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

// }
