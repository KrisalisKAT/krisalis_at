<div tabindex="0" class="collapse max-lg:collapse-arrow bg-base-200 lg:collapse-open" x-show="hasSetsData"
     x-data="mtgSetLookup" :class="open ? 'collapse-open' : 'collapse-close'"
     @reset-set-search.window="resetSetLookup()">
    <div class="collapse-title cursor-pointer text-lg px-2" @click="open = !open; $nextTick(() => open && $refs.setLookupName.focus())">Set Lookup</div>
    <div class="collapse-content px-2">
        <div class="gap-2 flex flex-wrap">
            <div class="flex flex-col">
                <label for="setLookupName" class="mb-2">
                    Name
                </label>
                <input id="setLookupName" x-ref="setLookupName" x-model="setLookup.name"
                       class="input input-bordered input-sm ">
            </div>
            <div class="flex gap-2">
                <div class="flex flex-col">
                    <label for="setLookupSize" class="mb-2">
                        Card count
                    </label>
                    <input id="setLookup" x-model="setLookup.size"
                           class="input input-bordered input-sm w-24">
                </div>
                <div class="flex flex-col">
                    <label for="setLookupYear" class="mb-2">
                        Year
                    </label>
                    <input id="setLookup" x-model="setLookup.year"
                           class="input input-bordered input-sm w-24">
                </div>
            </div>
        </div>
        <ul x-show="setLookupResults.length" class="mt-4 flex flex-col items-start">
            <template x-for="mtgSet in setLookupResults">
                <button class="btn btn-link" @click="$dispatch('set-selected', mtgSet.code); resetSetLookup()">
                    <span x-text="mtgSet.code" class="w-12 text-left uppercase inline-block"></span>
                    <span class="inline-block dark:bg-white/70 p-0.5">
                        <img :src="mtgSet.icon_svg_uri" :alt="`${mtgSet.code} set icon`" class="size-6">
                    </span>
                    <span x-text="`${mtgSet.name} (${mtgSet.year}, ${mtgSet.card_count} cards)`"></span>
                </button>
            </template>
        </ul>
    </div>
</div>
<x-mtgLister.setsData/>
@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mtgSetLookup', () => ({
                open: false,
                setLookup: {
                    name: '',
                    year: '',
                    size: '',
                },
                hasSetsData: mtgSets.length,
                get setLookupResults() {
                    const {name, year, size} = this.setLookup
                    if (!name && year.length < 4 && !size) {
                        return []
                    }
                    return mtgSets.filter(set =>
                        (!name || set.name.toLowerCase().includes(name.toLowerCase())) &&
                        (year.length < 4 || set.year === Number(year)) &&
                        (!size || set.card_count === Number(size) || set.printed_size === Number(size))
                    )
                },
                resetSetLookup() {
                    this.setLookup.name = ''
                    this.setLookup.year = ''
                    this.setLookup.size = ''
                },
            }))
        })
    </script>
@endpush
