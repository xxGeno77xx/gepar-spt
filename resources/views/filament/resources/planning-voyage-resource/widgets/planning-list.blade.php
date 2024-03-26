@php
    use carbon\carbon;

    use App\Models\Departement;
    use App\Models\Chauffeur;
    use Filament\Pages\Actions\Action;
    use App\Models\Pays;
    use App\Support\Database\StatesClass;

@endphp

<x-filament::widget>
    <x-filament::card>

        @if ($record)

            <style>
                .custom-div {
                    border-radius: 10px;
                    /* Bordures arrondies */
                    background-color: rgba(0, 0, 255, 0.5);
                    /* Couleur de fond bleue transparente */
                    color: rgba(255, 255, 255, 0.7);
                    /* Texte blanc transparent */
                    padding: 20px;
                    /* Espacement intérieur */
                    width: 200px;
                    /* Largeur */
                    height: 100px;
                    /* Hauteur */
                }
            </style>
            <h2 class="text-4xl  text-center py-6 font-bold dark:text-white" style="color:rgb(235, 134, 3)">Planning de
                voyage pour les dates du
                {{ Carbon::parse($record->date_debut)->translatedformat('d F') }} au
                {{ Carbon::parse($record->date_fin)->translatedformat('d F Y') }}</h2>
            <div class="overflow-hidden rounded-lg border border-gray-200 shadow-md m-5">
                <table class="w-full border-collapse bg-white text-left text-sm ">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium text-gray-900">Noms et prénoms</th>
                            <th scope="col" class="px-6 py-4 font-medium text-gray-900">Affectation</th>
                            <th scope="col" class="px-6 py-4 font-medium text-gray-900">Dates</th>
                            @if ($record->exterieur)
                                <th scope="col" class="px-6 py-4 font-medium text-gray-900">Destination</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-gray-100 border-t border-gray-100">
                        @foreach ($data as $ordre)
                            <tr class="hover:bg-gray-50">
                                <th class="flex gap-3 px-6 py-4 font-normal text-gray-900">
                                    <div class="text-sm">
                                        <div class="text-gray-400">{{ Chauffeur::find($ordre['chauffeur'])->fullname }}
                                        </div>
                                    </div>
                                </th>
                                <td class="px-6 py-4">
                                    <span class="h-1.5 w-1.5 rounded-full text-gray-400" style="font-weight: bold">
                                        <div style="color:rgb(230, 150, 45)" class="text-gray-400">
                                            {{ Departement::find($ordre['affectation'])->sigle_centre }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="h-1.5 w-1.5 rounded-full text-gray-400" style="font-weight: bold">
                                        {{ Carbon::parse($ordre['date_debut'])->translatedFormat('D d F Y') }} <span
                                            class="text-gray" style="font-weight: bold"> au </span>
                                        {{ Carbon::parse($ordre['date_fin'])->translatedFormat('D d F Y') }}</span>
                                </td>
                                @if ($record->exterieur)
                                    <td class="px-6 py-4">
                                        <span class="h-1.5 w-1.5 rounded-full text-gray-400" style="font-weight: bold; color:rgb(31, 163, 108)">
                                            {{ Pays::find($ordre['pays'])->libelle }}</span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        @endif
    </x-filament::card>
</x-filament::widget>
