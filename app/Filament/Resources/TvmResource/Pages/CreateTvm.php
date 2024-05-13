<?php

namespace App\Filament\Resources\TvmResource\Pages;

use Filament\Pages\Actions;
use App\Filament\Resources\TvmResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;

class CreateTvm extends CreateRecord
{
    protected static string $resource = TvmResource::class;

    protected function handleRecordCreation(array $data): Model
    {


        foreach ($data["engins_prix"] as $key => $engine) {
            //if forelast then break 
            if ($key == count($data["engins_prix"]) - 1) {

                break;

            }

            $model = static::getModel()::create([
                "date_debut" => $data["date_debut"],
                "date_fin" => $data["date_fin"],
                "reference" => $data["reference"],
                "engine_id" => intval($engine["engine_id"]),
                "prix" => intval($engine["prix"]),
                "user_id" => $data["user_id"],
                "updated_at_user_id" => $data["updated_at_user_id"],
            ]);


           

        }

        return static::getModel()::create([
            "date_debut" => $data["date_debut"],
            "date_fin" => $data["date_fin"],
            "reference" => $data["reference"],
            "engine_id" => intval($data["engins_prix"][count($data["engins_prix"]) - 1]["engine_id"]),
            "prix" => intval($data["engins_prix"][count($data["engins_prix"]) - 1]["prix"]),
            "user_id" => $data["user_id"],
            "updated_at_user_id" => $data["updated_at_user_id"],
        ]);;

    }
}
