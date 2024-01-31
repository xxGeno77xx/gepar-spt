<?php

namespace Database\Seeders;

use App\Models\Marque;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Storage::delete(Storage::files('public/'));

        $files = File::files(public_path('images'));

        foreach ($files as $file) {

            $filename = $file->getFilename();

            $fileContent = file_get_contents($file->getPathname());

            Storage::put('public/'.$filename, $fileContent);

            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            Marque::create([
                'nom_marque' => basename($filename, '.'.$extension),
                'logo' => $filename,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
