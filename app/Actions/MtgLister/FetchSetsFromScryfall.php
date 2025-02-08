<?php

namespace App\Actions\MtgLister;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchSetsFromScryfall
{
    use AsAction;

    public string $commandSignature = 'scryfall:fetch';

    public function handle()
    {
        $sets = GetScryfall::make()->handle('sets');
        Storage::put('mtgLister/sets.json', json_encode($sets['data'], JSON_PRETTY_PRINT));
    }
}
