<?php

namespace App\Models\Oracle;

use Illuminate\Support\Facades\Auth;

class Config
{
    public static function dynamicConfig(&$config)
    {

        if (Auth::check()) {
            $config['username'] = App\Filament\Http\Livewire\Auth::authenticate();
            $config['password'] = App\Filament\Http\Livewire\Auth::authenticate();
        }

    }
}
