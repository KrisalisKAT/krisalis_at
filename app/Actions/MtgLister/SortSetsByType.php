<?php

namespace App\Actions\MtgLister;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class SortSetsByType
{
    use AsAction;

    const SET_TYPES_ORDER = [
        'core',
        'expansion',
        'masters',
        'alchemy',
        'masterpiece',
        'arsenal',
        'from_the_vault',
        'spellbook',
        'premium_deck',
        'duel_deck',
        'draft_innovation',
        'treasure_chest',
        'commander',
        'planechase',
        'archenemy',
        'vanguard',
        'funny',
        'starter',
        'box',
        'promo',
        'token',
        'memorabilia',
        'minigame',
    ];

    public function handle(Collection $sets)
    {
        $grouped = $sets->groupBy('set_type');
        return collect(self::SET_TYPES_ORDER)
            ->flatMap(fn ($type) => $grouped->get($type, []));
    }
}
