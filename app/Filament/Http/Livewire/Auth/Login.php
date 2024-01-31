<?php

namespace App\Filament\Http\Livewire\Auth;

use App\Models\User;
use App\Support\Database\StatesClass;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * @property ComponentContainer $form
 */
class Login extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public $email = '';

    public $password = '';

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
        $authenticationLimit = env('LOGIN_LIMIT', 4);

        $data = $this->form->getState();

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => "Ce compte n'existe pas"]);
        }

        if ($user && $user->state == StatesClass::Deactivated()) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte a été désactivé. Contactez votre administrateur.',
            ])->status(403);
        }

        if (! Filament::auth()->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $data['remember'])) {

            if ($user->login_attempts >= $authenticationLimit) {
                $user->update([
                    'state' => StatesClass::Deactivated(),
                    'login_attempts' => 0,
                ]);
                throw ValidationException::withMessages([
                    'email' => 'Ce compte a été désactivé. Contactez votre administrateur.',
                ])->status(403);

            }

            if ($user && $user->state == StatesClass::Deactivated()) {

                throw ValidationException::withMessages([
                    'email' => 'Ce compte a été désactivé. Contactez votre administrateur.',
                ])->status(403);
            }

            $user->increment('login_attempts', 1);

            throw ValidationException::withMessages([
                'email' => __('filament::login.messages.failed'),
            ]);
        } else {
            $user->update([
                'state' => StatesClass::activated(),
                'login_attempts' => 0,
            ]);

            session()->regenerate();

        }

        return app(LoginResponse::class);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label(__('filament::login.fields.email.label'))
                ->email()
                ->required()
                ->autocomplete(),
            TextInput::make('password')
                ->label(__('filament::login.fields.password.label'))
                ->password()
                ->required(),
            Checkbox::make('remember')
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
}
