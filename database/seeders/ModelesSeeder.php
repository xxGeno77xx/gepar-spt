<?php

namespace Database\Seeders;

use App\Models\Marque;
use App\Models\Modele;
use Illuminate\Database\Seeder;
use App\Support\Database\ModelesClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModelesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ModelesClass::toValues();

        // Modele::truncate();
        
        foreach($types as  $type)
        {
            $brandName=substr(($type),0,strpos($type,"_"));// offset is set to 0 so starts at 0 and stops at first occrence of underscore
            
            $modelName=substr(($type),strpos($type,"_") + 1);//offset is set to be  underscore caracter's position so trimming starts there

            Modele::firstOrCreate([
                'nom_modele'=>$modelName, 
                'marque_id'=>Marque::where('nom_marque', "=", $brandName)->value('id'),
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);

        }


    }
}
