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
                        Enter "SET ###"; append "F" for foil
                    </label>
                    <div class="flex items-center gap-x-4">
                        <label class="input input-bordered input-sm flex items-center gap-x-1">
                            <span class="text-secondary -ml-2" x-show="setCodeLock">
                                &lt;<span x-text="setCodeLock"></span>&gt;
                            </span>
                            <input id="searchInput" x-ref="searchInput"
                                   class="grow"
                                   :placeholder="setCodeLock ? lockedSetPlaceholder : placeholder"
                                   autofocus
                                   autocomplete="off"
                                   x-model="search"
                                   @set-selected.window="search = $event.detail+' '; $el.focus()"
                                   @keyup.down="rowActionIndex = Math.min(rowActionIndex + 1, cards.length - 1)"
                                   @keyup.up="rowActionIndex = Math.max(rowActionIndex - 1, 0)"
                            />
                        </label>
                        <button class="btn btn-sm btn-outline"
                                x-text="actionLabel" :disabled="!action.do"></button>
                    </div>
                    <span>
                        or search with <x-link.pop href="https://scryfall.com/docs/syntax">Scryfall syntax</x-link.pop>
                    </span>
                </form>
                @include('mtg-lister.set-lookup')
            </div>
            <div class="flex flex-col gap-y-4 w-fit px-2">
                <div class="flex flex-wrap gap-4">
                    <a class="btn btn-primary btn-sm"
                       :href="csvDownload"
                       :download="csvFileName"
                       :disabled="!resolvedCards.length">Download CSV</a>
                    <x-link.pop href="https://moxfield.com/help/importing-collection#import"
                                class="inline-block md:inline">Moxfield CSV format
                    </x-link.pop>
                </div>
                <ul class="flex flex-col items-stretch">
                    <template x-for="(row, index) in cards" :key="row.id">
                        <li class="flex gap-x-6">
                            <button class="btn btn-sm text-xl tooltip flex"
                                    data-tip="Add Another"
                                    :class="{ 'btn-outline border-primary': action.do === '++' && rowActionId === row.id }"
                                    :disabled="!row.card && !row.results"
                                    @click="addAnother(row)"><span class="-mt-1.5">+</span></button>
                            <template x-if="row.card">
                                <div
                                    class="grow flex gap-x-4 px-2 py-1 items-center border border-primary border-b-0 rounded-t-lg">
                                    <span class="grow">
                                        <a href="#preview_modal" class="p-0 tooltip"
                                           data-tip="view card (v)"
                                           :class="{ 'border-b border-primary -mb-px': action.do === 'vC' && rowActionId === row.id }"
                                           @click.prevent="$dispatch('view-card', row.card)">
                                            <span class="inline-block"
                                                  x-text="row.card.name.split('//')[0]+(row.card.name.split('//').length > 1 ? ' //' : '')"></span>
                                            <span class="inline-block">
                                                <span x-text="row.card.name.split('//')[1] || ''"></span>
                                                <sup>v</sup>
                                            </span>
                                        </a>
                                    </span>
                                    <span>
                                        <span x-text="row.card.set.toUpperCase()"
                                              class="tooltip" :data-tip="setName(row.card.set)"></span>
                                        <span x-text="padCardNum(row.card)"></span>
                                    </span>
                                    <button class="btn btn-xs p-0 tooltip whitespace-nowrap"
                                            :data-tip="row.isFoil ? 'foil' : 'not foil'"
                                            :class="{
                                                'opacity-30': !row.isFoil,
                                                'text-primary': action.do === 'f' && rowActionId === row.id
                                            }"
                                            @click="row.isFoil = !row.isFoil">
                                        <x-icon.sparkles size="size-5"/>
                                        <sup class="-ml-1">f</sup>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!row.card">
                                <div
                                    class="grow flex gap-x-6 px-2 py-1 items-center border border-primary border-b-0 rounded-t-lg">
                                    <span class="grow"
                                          x-text="row.search || `${row.set.toUpperCase()} ${row.cardNum}`"></span>
                                    <x-icon.bolt x-show="!row.error && !row.results" class="animate-pulse"/>
                                    <span x-show="row.error" class="text-error">
                                        <x-icon.error size="size-6"/>
                                        Service error
                                    </span>
                                    <a href="#select_modal"
                                       x-show="row.results"
                                       :class="{ 'border-b border-primary -mb-px': action.do === 'vR' && rowActionId === row.id }"
                                       @click.prevent="$dispatch('view-results', row)">
                                        <span x-text="`${row.results?.total_cards} cards found`"></span>
                                        <sup>v</sup>
                                    </a>
                                </div>
                            </template>
                            <button class="btn btn-sm text-xl flex"
                                    :class="{ 'btn-outline border-primary': action.do === 'x' && rowActionId === row.id }"
                                    @click="remove(row)"><span class="-mt-1.5">x</span></button>
                        </li>
                    </template>
                </ul>
            </div>
            <div
                @view-card.window="preview = $event.detail; preview_modal.showModal()"
                @view-results.window="select = $event.detail; select_modal.showModal(); $nextTick(() => select_modal.querySelector('button').focus())"
            ></div>
            <dialog id="preview_modal" class="modal">
                <template x-if="preview">
                    <div class="modal-box p-0 flex flex-col items-center">
                        <img :src="preview.image_uris.png" :alt="preview.name">
                        <span x-text="`${setName(preview.set)} ${padCardNum(preview)}`"></span>
                    </div>
                </template>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
            <dialog id="select_modal" class="modal">
                <div class="modal-box p-0 overflow-x-auto max-w-[90vw] w-fit">
                    <template x-if="select?.results">
                        <div class="flex gap-x-4 w-full items-stretch p-2">
                            <template x-for="result in select.results.data">
                                <button class="flex-shrink-0 flex flex-col items-center p-2 rounded-3xl"
                                        @click="select.card = result; select_modal.close(); $refs.searchInput.focus()">
                                    <img :src="result.image_uris.png" :alt="result.name" class="max-h-[75vh]">
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
            document.addEventListener('alpine:init', () => {
                const placeholders = ['eld 299', 'fdn 128', 'snc 425', 'woe 287', 'ncc 13']
                Alpine.data('mtgLister', () => ({
                    search: '',
                    set: '',
                    cardNum: '',
                    isFoil: false,
                    cards: [],
                    placeholder: placeholders[Math.floor(Math.random() * placeholders.length)],
                    preview: null,
                    select: null,
                    hasSetsData: mtgSets.length,
                    rowActionIndex: 0,
                    setCodeLock: null,
                    get rowActionId() {
                        return this.cards[this.rowActionIndex].id
                    },
                    setByCode(code) {
                        if (this.hasSetsData) {
                            return mtgSets.find(set => set.code === code.toLowerCase()) || null
                        }
                        return null
                    },
                    get lockedSetPlaceholder() {
                        return this.setCodeLock ?
                            Math.floor(Math.random() * this.lockedSet.card_count) + 1
                            : this.placeholder
                    },
                    get lockedSet() {
                        return this.setCodeLock ? this.setByCode(this.setCodeLock) : null
                    },
                    setName(code) {
                        const set = this.setByCode(code)
                        return set ? set.name : code.toUpperCase()
                    },
                    padCardNum(card) {
                        const set = this.setByCode(card.set)
                        return set ?
                            card.collector_number.padStart((set.card_count + '').length, '0')
                            : card.collector_number
                    },
                    addAnother(row) {
                        const {search, isFoil, card, results} = row
                        this.newCardProcess({
                            search, isFoil, card, results
                        })
                    },
                    indexOfCard(row) {
                        return this.cards.findIndex(item => row.id === item.id)
                    },
                    remove(row) {
                        this.cards.splice(this.indexOfCard(row), 1)
                    },
                    toggleIsFoil(row) {
                        const index = this.indexOfCard(row)
                        this.cards[index].isFoil = !this.cards[index].isFoil
                    },
                    get resolvedCards() {
                        return this.cards.filter(c => c.card)
                    },
                    get distinctCards() {
                        const cards = this.resolvedCards
                        const cardIndexes = {}
                        const rows = []
                        cards.reverse().forEach(c => {
                            const key = `${c.card.set}:${c.card.collector_number}:${c.isFoil ? 'foil' : '-'}`
                            if (cardIndexes[key] !== undefined) {
                                rows[cardIndexes[key]].count++
                            } else {
                                cardIndexes[key] = rows.length
                                rows.push({
                                    count: 1,
                                    name: c.card.name,
                                    set: c.card.set,
                                    cn: c.card.collector_number,
                                    foil: c.isFoil
                                })
                            }
                        })
                        return rows
                    },
                    get csvDownload() {
                        return encodeURI('data:text/csv;charset=utf-8,'
                            + "Count,Name,Edition,Collector Number,Foil\r\n"
                            + this.distinctCards.map(row => [
                                row.count,
                                `"${row.name.replace('"', '""')}"`,
                                row.set,
                                row.cn,
                                row.foil ? 'foil' : '',
                            ].join(',')).join("\r\n"));
                    },
                    csvFileName() {
                        const count = this.resolvedCards.length
                        if (!count) return 'mtg-list_empty.csv'
                        const card = this.distinctCards[0].name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
                        return `mtg-list_${count}_${card}.csv`
                    },
                    displayText(cardRow) {
                        if (cardRow.error) {
                            return 'Service error'
                        }
                        if (cardRow.results) {
                            return cardRow.results.total_cards + ' cards found'
                        }
                        return 'Pending'
                    },
                    resetInputs() {
                        this.search = ''
                        this.set = ''
                        this.cardNum = ''
                        this.rowActionIndex = 0
                    },
                    scryfallService: kat.rateLimitedService('https://api.scryfall.com/', 100),
                    async scryfall(...args) {
                        const response = await this.scryfallService(...args)
                        if (!response.ok) {
                            throw new Error(`Response status: ${response.status}`);
                        }
                        return await response.json()
                    },
                    resetFocus(refs) {
                        refs.searchInput.focus()
                    },
                    matchSetNum(search) {
                        if (!this.hasSetsData) return null

                        const parts = search.toLowerCase().split(' ')
                        if (parts.length !== 2) return null

                        const set = this.setByCode(parts[0])
                        if (!set) return null

                        const numberFoil = parts[1].match(/(?<number>\d{1,4})(?<foil>f?)/i)
                        if (!numberFoil) return null

                        const num = Number(numberFoil.groups.number)
                        if (num > set.card_count) return null

                        return {
                            set: set.code,
                            num,
                            foil: Boolean(numberFoil.groups.foil),
                        }
                    },
                    get action() {
                        const row = this.cards[this.rowActionIndex] || null
                        const card = row?.card
                        const search = this.search.toLowerCase()

                        if (!search) {
                            if (card || row?.results) return {do: '++', row}
                            return {do: null}
                        }

                        if (search.length === 1) {
                            switch (search) {
                                case 'v':
                                    if (row) return {do: card ? 'vC' : 'vR', row}
                                    break
                                case 'f':
                                    if (card) return {do: 'f', row}
                                    break
                                case '<':
                                    if (this.setCodeLock) return {do: 'l-'}
                                    if (card) return {do: 'l+', setCode: card.set}
                                    break
                                case 'x':
                                    if (row) return {do: 'x', row}
                                    break
                            }
                        }

                        if (search.slice(0, 1) === '<' && this.setByCode(search.slice(1))) {
                            return {do: 'l+', setCode: search.slice(1)}
                        }

                        const match = this.matchSetNum(this.setCodeLock ?
                            `${this.setCodeLock} ${search}`
                            : this.search)
                        if (match) {
                            return {do: '+', match}
                        }
                        if (this.setCodeLock) {
                            return {do: 's', search: `${search} set:${this.setCodeLock}`}
                        }
                        return {do: 's', search}
                    },
                    get actionLabel() {
                        switch(this.action.do) {
                            case null:
                            case 's':   return 'Search Card'
                            case '+':   return 'Add Card'
                            case '++':  return 'Add Another'
                            case 'vC':  return 'View Card'
                            case 'vR':  return 'View Results'
                            case 'f':   return 'Toggle Foil'
                            case 'l+':  return 'Lock Set: '+this.action.setCode
                            case 'l-':  return 'Unlock Set'
                            case 'x':   return 'Remove Row'
                        }
                    },
                    mainAction(dispatch) {
                        const action = this.action
                        switch (action.do) {
                            case null: return
                            case '+':
                                this.getCard(action.match.set, action.match.num, action.match.foil);
                                break
                            case 's':
                                this.searchCard(action.search);
                                break
                            case '++':
                                this.addAnother(action.row);
                                break
                            case 'vC':
                                dispatch('view-card', action.row.card);
                                break
                            case 'vR':
                                dispatch('view-results', action.row);
                                break
                            case 'f':
                                action.row.isFoil = !action.row.isFoil;
                                break
                            case 'l+':
                                this.setCodeLock = action.setCode
                                break
                            case 'l-':
                                this.setCodeLock = null
                                break
                            case 'x':
                                this.remove(action.row);
                                break
                        }

                        this.resetInputs()
                        dispatch('reset-set-search')
                    },
                    newCardProcess(data) {
                        const process = {
                            id: kat.uniqueId(),
                            error: false,
                            card: false,
                            isFoil: false,
                            results: null,
                            ...data
                        }
                        this.cards.unshift(process)
                        return this.cards[0]
                    },
                    async searchCard(search) {
                        const process = this.newCardProcess({search})
                        const query = new URLSearchParams()
                        query.set('q', search)
                        query.set('unique', 'prints')
                        query.set('order', 'released')
                        try {
                            const results = await this.scryfall(`cards/search?${query.toString()}`)
                            if (results.total_cards === 1) {
                                process.card = results.data[0]
                            } else {
                                process.results = results
                            }
                        } catch (error) {
                            process.error = true
                            console.error({search, error})
                        }
                    },
                    async getCard(set, cardNum, isFoil) {
                        const process = this.newCardProcess({
                            set,
                            cardNum,
                            isFoil,
                        })
                        try {
                            process.card = await this.scryfall(`cards/${set}/${cardNum}`)
                            console.log(this.cards)
                        } catch (error) {
                            process.error = true
                            console.error({set, cardNum, error})
                        }
                    }
                }))
            })
        </script>
    @endpush
</x-layout>
