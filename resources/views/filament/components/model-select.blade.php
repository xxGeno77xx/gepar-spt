<div class="flex rounded-md relative">
    <div class="flex">
        <div>
            <div style="height: 1%; width:20%;">
                <img src="{{ url('/storage/'.$logo.'') }}" alt="{{ $nom_marque }}" role="img"  width="400" 
                height="500" class="h-2 w-2 rounded-full overflow-hidden  object-cover" />
            </div>
        </div>
        <div>
            <p class="text-sm font-bold pb-1">{{ $nom_marque }}</p>
        </div>
    </div>
</div>
