<x-layout title="MTG Lister">
    <div class="mx-auto container max-w-6xl py-2 flex flex-col gap-y-4 sm:px-4">
        <div class="card w-full px-2">
            <h1 class="font-bold text-2xl">MTG Lister</h1>
            <p>A little utility for getting a list of cards</p>
        </div>
        <div class="flex flex-col lg:flex-row items-start gap-y-4 lg:gap-x-10 xl:gap-x-14" x-data="mtgLister">
            <div class="flex flex-col gap-y-2">
                <form class="flex flex-col gap-2 px-2"
                      @submit.prevent="mainAction($dispatch)">
                    <label for="searchInput">
                        Enter "SET ###"
                    </label>
                    <div class="flex items-center gap-x-4">
                        <label class="input input-bordered input-sm flex items-center gap-x-1 grow">
                            <span class="text-secondary -ml-1" x-show="setCodeHint">
                                <span x-text="setCodeHint"></span>
                            </span>
                            <input id="searchInput" x-ref="searchInput"
                                   class="grow"
                                   :placeholder="setCodeLock ? lockedSetPlaceholder : placeholder"
                                   autofocus
                                   autocomplete="off"
                                   x-model="search"
                                   @set-selected.window="search = $event.detail+' '; $el.focus()"
                                   @keyup.down="rowActionIndex = Math.min(rowActionIndex + 1, list.rows.length - 1)"
                                   @keyup.up="rowActionIndex = Math.max(rowActionIndex - 1, 0)"
                            />
                        </label>
                        <button class="btn btn-sm btn-outline"
                                x-text="actionLabel" :disabled="!action.do"></button>
                    </div>
                    <span>
                        <a href="#how_to" class="kat-link" @click.prevent="how_to.showModal()">
                            How To<span :class="{'text-primary': action.do === '?'}">?</span>
                        </a>
                    </span>
                </form>
                @include('mtg-lister.set-lookup')
            </div>
            <div class="flex flex-col gap-y-4 w-fit px-2 grow">
                <div class="flex justify-between">
                    <div>
                        <a class="btn btn-primary btn-sm"
                           :href="csvDownload"
                           :download="csvFileName"
                           :disabled="!resolvedCards.length">Download CSV</a>
                    </div>
                    <div>
                        <button class="btn btn-warning btn-sm" :disabled="!list.rows.length"
                            @click="clearList()">
                            Clear List
                        </button>
                    </div>
                </div>
                <ul class="flex flex-col items-stretch">
                    <template x-for="row in list.byNew">
                        <li class="flex gap-x-6" :key="row.key">
                            <button class="btn btn-sm text-xl tooltip flex"
                                    data-tip="Add Another"
                                    :class="{ 'btn-outline border-primary': action.do === '++' && actionRowKey === row.key }"
                                    :disabled="!row.card?.card && !row.results"
                                    @click="addAnother(row)"><span class="-mt-1.5">+</span></button>
                            <template x-if="row.card?.card">
                                <div
                                    class="grow flex gap-x-4 px-2 py-1 items-center border border-primary border-b-0 rounded-t-lg">
                                    <span class="grow">
                                        <a href="#preview_modal" class="p-0 tooltip"
                                           data-tip="view card (v)"
                                           :class="{ 'border-b border-primary -mb-px': action.do === 'vC' && actionRowKey === row.key }"
                                           @click.prevent="$dispatch('view-card', row.card.card)">
                                            <span class="inline-block"
                                                  x-text="row.card.card.name.split('//')[0]+(row.card.card.name.split('//').length > 1 ? ' //' : '')"></span>
                                            <span class="inline-block">
                                                <span x-text="row.card.card.name.split('//')[1] || ''"></span>
                                                <sup>v</sup>
                                            </span>
                                        </a>
                                    </span>
                                    <span>
                                        <span x-text="row.card.set.toUpperCase()"
                                              :class="{'text-primary': actionRowKey === row.key
                                                    && action.usePrev && (action.do === '+' || action.do === 'l+')}"
                                              class="tooltip" :data-tip="setName(row.card.set)"></span>
                                        <span x-text="padCardNum(row.card)"></span>
                                    </span>
                                    <button class="btn btn-xs p-0 tooltip whitespace-nowrap"
                                            :data-tip="row.foil ? 'foil' : 'not foil'"
                                            :class="{
                                                'opacity-30': !row.foil,
                                                'text-primary': action.do === 'f' && actionRowKey === row.key
                                            }"
                                            @click="updateRow(row, {foil: !row.foil})">
                                        <x-icon.sparkles size="size-5"/>
                                        <sup class="-ml-1">f</sup>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!row.card?.card">
                                <div
                                    class="grow flex gap-x-6 px-2 py-1 items-center border border-primary border-b-0 rounded-t-lg">
                                    <span class="grow"
                                          x-text="row.search?.q || `${row.card?.set.toUpperCase()} ${row.card?.num}`"></span>
                                    <x-icon.bolt x-show="!row.error && !row.results" class="animate-pulse"/>
                                    <span x-show="row.hasError" class="text-error">
                                        <x-icon.error size="size-6"/>
                                        Service error
                                    </span>
                                    <a href="#select_modal"
                                       x-show="row.search?.results"
                                       :class="{ 'border-b border-primary -mb-px': action.do === 'vR' && actionRowKey === row.key }"
                                       @click.prevent="$dispatch('view-results', row)">
                                        <span x-text="`${row.search?.results?.length} cards found`"></span>
                                        <sup>v</sup>
                                    </a>
                                </div>
                            </template>
                            <button class="btn btn-sm text-xl flex"
                                    :class="{ 'btn-outline border-primary': action.do === 'x' && actionRowKey === row.key }"
                                    @click="list.remove(row)"><span class="-mt-1.5">x</span></button>
                        </li>
                    </template>
                </ul>
            </div>
            <div
                @view-card.window="preview = $event.detail; preview_modal.showModal(); console.log($event.detail)"
                @view-results.window="select = $event.detail; select_modal.showModal(); $nextTick(() => select_modal.querySelector('button').focus())"
                @open-help.window="how_to.showModal()"
            ></div>
            <dialog id="how_to" class="modal">
                <div class="modal-box w-5/6 max-w-3xl">
                    <div class="flex flex-col gap-y-2">
                        <h2 class="text-xl">How To Use MTG Lister</h2>
                        <h3 class="text-lg">The Idea</h3>
                        <p>I wanted to inventory my collection on Moxfield, but everything else I tried to use for that felt too damn slow.
                            I figured the easiest way to build an inventory would be to enter set code and card number.
                            The problem is that <x-link.pop href="https://moxfield.com/help/importing-collection#import"
                                                            class="inline-block md:inline">Moxfield CSV format</x-link.pop>
                            wants the exact card name in addition to those, which is a lot more typing.<br>
                            But I'm a web developer; I figured I could make it. And here it is!
                        </p>
                        <h3 class="text-lg">Commands</h3>
                        <p>The point of this is speedy entry and I find keyboard commands a lot faster than using a mouse or touchpad,
                            so I built in a bunch of controls using just the keyboard.
                        </p>
                        <ul class="list-disc">
                            <li><span class="font-bold text-primary">Set Code + [Space] + Card Number + [Enter]</span> - Add card</li>
                            <li><span class="font-bold text-primary">&lt; + [Space] + Card Number + [Enter]</span> - Add card, using the highlighted set code</li>
                            <li><span class="font-bold text-primary">Scryfall search + [Enter]</span> - Search for a card with
                                <x-link.pop href="https://scryfall.com/docs/syntax">Scryfall syntax</x-link.pop>.<br>
                                This is the fallback action when no other command is recognized.
                                When multiple results are returned, you can view the first page (only, for now) of results and select your card.
                            </li>
                            <li><span class="font-bold text-primary">[Up] / [Down] arrows</span> - Move the action highlight list cursor.<br>
                                The cursor resets to the top of the list after any other command.
                            </li>
                            <li><span class="font-bold text-primary">[Enter]</span> - Add another</li>
                            <li><span class="font-bold text-primary">F + [Enter]</span> - Toggle Foil status</li>
                            <li><span class="font-bold text-primary">V + [Enter]</span> - View the card, or the search results</li>
                            <li><span class="font-bold text-primary">X + [Enter]</span> - Remove the row</li>
                            <li><span class="font-bold text-primary">&lt; + Set Code + [Enter]</span> - Lock to the given set code.<br>
                                e.g. "&lt;blb" will lock to Bloomburrow. Then you can just enter the card number.<br>
                                Searches will also restrict to the set (appending "set:" and the code to your search)
                            </li>
                            <li><span class="font-bold text-primary">&lt; + [Enter]</span> - Toggle Set Code Lock.<br>
                                Lock or unlock the set code. If locking, will get the set code from the list</li>
                            <li><span class="font-bold text-primary">? + [Enter]</span> - Open this help modal</li>
                            <li><span class="font-bold text-primary">[Esc]</span> - Close modals</li>
                        </ul>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
            <dialog id="preview_modal" class="modal">
                <template x-if="preview">
                    <div class="modal-box p-0 flex flex-col items-center">
                        <img :src="preview.imageUri" :alt="preview.name">
                        <span x-text="`${setName(preview.set)} ${padCardNum(preview)}`"></span>
                    </div>
                </template>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
            <dialog id="select_modal" class="modal">
                <div class="modal-box p-0 overflow-x-auto max-w-[90vw] w-fit">
                    <template x-if="select?.search?.results">
                        <div class="flex gap-x-4 w-full items-stretch p-2">
                            <template x-for="result in select.search.results">
                                <button class="flex-shrink-0 flex flex-col items-center p-2 rounded-3xl"
                                        @click="updateRow(select, {card: result}); select_modal.close(); $refs.searchInput.focus()">
                                    <img :src="result.imageUri" :alt="result.name" class="max-h-[75vh]">
                                    <span x-text="`${setName(result.set)} ${padCardNum(result)}`"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>
    </div>
    <x-mtgLister.setsData/>
    @push('scripts')
        <script>
            {!! Vite::content('resources/js/mtg-lister.js') !!}
        </script>
    @endpush
</x-layout>
