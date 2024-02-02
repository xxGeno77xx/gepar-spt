<?php

namespace App\Models\Oracle;

use Illuminate\Support\Facades\Auth;
use App\Filament\Http\Livewire\Auth\Login;

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
