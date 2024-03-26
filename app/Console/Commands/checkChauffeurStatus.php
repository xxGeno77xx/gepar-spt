<?php

namespace App\Console\Commands;

use App\Models\Chauffeur;
use App\Models\OrdreDeMission;
use App\Support\Database\ChauffeursStatesClass;
use Illuminate\Console\Command;

class checkChauffeurStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chauffeurs:Status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //chauffeurs  on mission or waiting for departure
        $chauffeurs = Chauffeur::whereIn('mission_state', [ChauffeursStatesClass::Programme()->value, ChauffeursStatesClass::En_mission()->value])
            ->pluck('id')
            ->toArray();

        //latest missions for above chauffeurs
        $missions = OrdreDeMission::whereIn('chauffeur_id', $chauffeurs)
            ->latest()
            ->get()
            ->groupBy('chauffeur_id');

        dd($missions);

    }
}
