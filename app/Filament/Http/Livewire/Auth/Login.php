<?php

namespace App\Filament\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use App\Models\Oracle\OracleConnector;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Concerns\InteractsWithForms;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

/**
 * @property ComponentContainer $form
 */
class Login extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public  $username = '';

    public $email = '';

    public  $password = '';

    public $remember = false;

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
                $conn = oci_connect(strtoupper($data['username']), $data['password'], config('app.serverIP'));

            } catch (\Exception $e) {

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
            throw ValidationException::withMessages(['username' => "Votre compte n'est pas autorisé sur cette application"]);
        } elseif (Auth::login($userToLogIn)) {

            if ($userToLogIn->login_attempts >= $authenticationLimit) {
                $userToLogIn->update([
                    'state' => 0,
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
                'state' => 1,
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

    public static function getOraUser()
    {
    
        return 'GATEWAY';
    }

    public static function getOraPass()
    {
        return 'gateway_2021';
    }
}
