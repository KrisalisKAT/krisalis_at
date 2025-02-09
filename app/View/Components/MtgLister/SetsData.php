<?php

namespace App\View\Components\MtgLister;

use App\Actions\MtgLister\SortSetsByType;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class SetsData extends Component
{
    public array $sets = [];

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        if (Storage::exists('mtgLister/sets.json')) {
            $sets = json_decode(Storage::get('mtgLister/sets.json'), true);
            $this->sets = SortSetsByType::make()->handle(
                collect($sets)
                    ->filter(fn ($set) => !in_array($set['set_type'], ['alchemy', 'treasure_chest']))
            )
                ->map(fn ($set) => Arr::only($set, [
                    'name',
                    'set_type',
                    'parent_set_code',
                    'printed_size',
                    'card_count',
                    'icon_svg_uri',
                ]) + [
                    'code' => strtolower($set['code']),
                    'year' => dateOrNull($set['released_at'])?->year
                ])
                ->all();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'blade'
            <script>
                const mtgSets = {{ Js::from($sets) }}
            </script>
blade;
    }
}
