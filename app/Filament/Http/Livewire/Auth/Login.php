<?php

namespace App\Filament\Http\Livewire\Auth;

use App\Models\DbaUser;
use App\Models\Departement;
use App\Models\DepartementUser;
use App\Models\User;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * @property ComponentContainer $form
 */
class Login extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public $data;

    public $username = '';

    public $email = '';

    public $password = '';

    public $remember = false;

    const OPEN = 'OPEN';

    const LOCKED = 'LOCKED';

    const EXPIREDLOCKED = 'LOCKED(TIMED)';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {

        $authenticationLimit = config('app.LOGIN_LIMIT', 4);

        $data = $this->form->getState();

        try {
            $this->rateLimit(5);

            try {

                $oracleuser = DbaUser::where('username', strtoupper($data['username']))->first();

                $conn = oci_connect(strtoupper($data['username']), $data['password'], env('CONNECTION'));

            } catch (\Exception $e) {
                if ($oracleuser) {
                    if (in_array($oracleuser->account_status, [self::LOCKED, self::EXPIREDLOCKED])) {
                        throw ValidationException::withMessages(['username' => 'Votre compte est bloqué, veuiller contactez votre administrateur.']);
                    }

                } else {

                    throw ValidationException::withMessages(['username' => 'Ce compte n\'existe pas']);
                }

                throw ValidationException::withMessages(['username' => 'Nom d\'utilisateur ou mot de passe incorrect']);
            }

        } catch (TooManyRequestsException $exception) {
            $this->addError('username', __('filament::login.messages.throttled', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));

            return null;
        }

        $userToLogIn = User::where('username', $data['username'])->first();

        if (! $userToLogIn) {

            if (! isset($data['departement_id'])) {
                throw ValidationException::withMessages(['username' => 'C\'est votre première connexion. Cochez la case "Nouvel utilisateur" puis choisissez votre département et votre poste']);
            }

            $createUser = User::create([
                'email' => $data['email'],
                'password' => Hash::make('L@poste+2024'),
                'name' => $data['name'],
                'lastName' => $data['lastName'],
                'username' => $data['username'],
                'notification' => true,
                'login_attempts' => 0,
                'departement_id' => $data['departement_id'],
                'created_at' => now(),
                'updated_at' => now(),
                'state' => StatesClass::Activated()->value,
                'poste' => $data['poste'],
            ]);

            $newUser = User::where('username', $data['username'])->first();

            switch ($data['departement_id']) {
                case Departement::where('sigle_centre', 'DPL')->first()->code_centre:
                    $newUser->syncRoles(RolesEnum::Dpl()->value, RolesEnum::Delegue_Division()->value);
                    break;

                case Departement::where('sigle_centre', 'DIGA')->first()->code_centre:
                    $newUser->syncRoles(RolesEnum::Diga()->value, RolesEnum::Delegue_Direction()->value);
                    break;

                case Departement::where('sigle_centre', 'DPAS')->first()->code_centre:
                    $newUser->syncRoles(RolesEnum::Dpas()->value, RolesEnum::Delegue_Division()->value);
                    break;

                case Departement::where('sigle_centre', 'DCGBT')->first()->code_centre:
                    $newUser->syncRoles(RolesEnum::Budget()->value, RolesEnum::Delegue_Division()->value);
                    break;
            }

            DepartementUser::create([
                'departement_code_centre' => $data['departement_id'],
                'user_id' => $newUser->id,
            ]);

            Auth::login($newUser);

        } elseif (Auth::login($userToLogIn)) {

            if ($userToLogIn->login_attempts >= $authenticationLimit) {
                $userToLogIn->update([
                    'state' => StatesClass::Activated()->value,
                    'login_attempts' => 0,
                ]);
                throw ValidationException::withMessages([
                    'username' => 'Ce compte a été désactivé. Contactez votre administrateur.',
                ])->status(403);

            }

            if ($userToLogIn && $userToLogIn->state == false) {

                throw ValidationException::withMessages([
                    'username' => 'Ce compte a été désactivé. Contactez votre administrateur.',
                ])->status(403);
            }

            $userToLogIn->increment('login_attempts', 1);

            throw ValidationException::withMessages([
                'username' => __('filament::login.messages.failed'),
            ]);
        } else {
            $userToLogIn->update([
                'state' => StatesClass::Activated()->value,
                'login_attempts' => 0,
            ]);

            session()->regenerate();

        }

        return app(LoginResponse::class);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('username')
                ->label(__("Nom d'utilisateur"))
                ->required()
                ->autocomplete(),
            TextInput::make('password')
                ->label(__('filament::login.fields.password.label'))
                ->password()
                ->required(),
            Checkbox::make('new_user')
                ->label('Nouvel utilisateur')
                ->dehydrated(false)
                ->reactive(),

            TextInput::make('lastName')
                ->label(__('Nom de famille'))
                ->required()
                ->visible(fn ($get) => $get('new_user') == 1 ? true : false)
                ->required(fn ($get) => $get('new_user') == 1 ? true : false)
                ->autocomplete(),

            TextInput::make('name')
                ->label(__('Prénom'))
                ->required()
                ->visible(fn ($get) => $get('new_user') == 1 ? true : false)
                ->required(fn ($get) => $get('new_user') == 1 ? true : false)
                ->autocomplete(),

            TextInput::make('email')
                ->label(__('Adresse mail'))
                ->required()
                ->regex('/.*@laposte\.tg$/')
                ->email()
                ->visible(fn ($get) => $get('new_user') == 1 ? true : false)
                ->required(fn ($get) => $get('new_user') == 1 ? true : false)
                ->autocomplete(),

            Select::make('departement_id')
                ->label('Département')
                ->options(Departement::pluck('sigle_centre', 'code_centre'))
                ->visible(fn ($get) => $get('new_user') == 1 ? true : false)
                ->required(fn ($get) => $get('new_user') == 1 ? true : false)
                ->searchable(),

            TextInput::make('poste')
                ->label('Poste occupé')
                ->required(fn ($get) => $get('new_user') == 1 ? true : false)
                ->visible(fn ($get) => $get('new_user') == 1 ? true : false),

            // Radio::make("level")
            // // ->inline()
            // ->visible(fn($get) => $get("new_user") == 1 ? true : false)
            // ->label("Appartenance : ")
            // ->options([
            //     0 => "Division",
            //     1 => "Direction (Secrétaires de Direction uniquement)",
            //     2 => "Direction générale (Secrétaires du DG uniquement)",
            // ]),

            Hidden::make('remember')
                ->label(__('filament::login.fields.remember.label')),
        ];
    }

    public function render(): View
    {
        return view('filament::login')
            ->layout('filament::components.layouts.card', [
                'title' => __('filament::login.title'),
            ]);
    }

    // public static function getOraUser()
    // {

    //     return 'GATEWAY';
    // }

    // public static function getOraPass()
    // {
    //     return 'gateway_2021';
    // }

    // protected function getFormStatePath():string
    // {
    //     return 'data';
    // }

    // public static function  getOraUser()
    // {
    //     return Self::$dataB['username'];
    // }

    // public static function getOraPass()
    // {
    //     return Self::$dataB['password'];
    // }
}
