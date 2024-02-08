<?php

namespace App\Models\Oracle;

use App\Filament\Http\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;

class Config
{
    public static function dynamicConfig(&$config)
    {

        if (Auth::check()) {
            $config['username'] = Login::getOraUser();
            $config['password'] = Login::getOraPass();
        }

    }
}
