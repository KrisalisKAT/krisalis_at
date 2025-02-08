<x-layout title="MTG Lister">
    <div class="mx-auto container pa-4 flex flex-col gap-y-4">
        <div class="card w-full">
            <h1 class="font-bold text-2xl">MTG Lister</h1>
            <p>A little utility for getting a list of card names</p>
        </div>
        <div class="flex gap-6" x-data="mtgLister">
            <div class="flex flex-col gap-y-10">
                <form class="flex flex-col"
                      @submit.prevent="findCard()">
                    <label for="searchInput" class="mb-2">
                        Enter "SET ###" or search with <a href="https://scryfall.com/docs/syntax" target="_blank" class="link">
                            Scryfall syntax <x-icon.newTab />
                        </a>
                    </label>
                    <div class="flex items-center gap-x-4">
                        <input id="searchInput" x-ref="searchInput"
                               class="input input-bordered input-sm flex-grow"
                               autofocus
                               x-model="search"/>
                        <button class="btn btn-sm btn-outline">
                            Add Card
                        </button>
                    </div>
                    <span>use "SET ###F" for foils</span>
                </form>
                <div class="flex flex-col" x-show="hasSetsData">
                    <span class="text-lg">Set Lookup</span>
                    <div class="flex gap-x-2">
                        <div class="flex flex-col">
                            <label for="setLookupName" class="mb-2">
                                Name
                            </label>
                            <input id="setLookupName" x-model="setLookup.name"
                                   class="input input-bordered input-sm ">
                        </div>
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
                    <ul x-show="setLookupResults.length" class="mt-4 flex flex-col items-start">
                        <template x-for="mtgSet in setLookupResults">
                            <button class="btn btn-link" @click="search = mtgSet.code+' '; $refs.searchInput.focus()">
                                <span x-text="mtgSet.code" class="w-12 text-left uppercase inline-block"></span>
                                <span class="inline-block bg-white">
                                    <img :src="mtgSet.icon_svg_uri" :alt="`${mtgSet.code} set icon`" class="size-6">
                                </span>
                                <span x-text="`${mtgSet.name} (${mtgSet.year})`"></span>
                            </button>
                        </template>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col gap-y-4 pl-10">
                <div><a class="btn btn-primary btn-sm"
                        :href="csvDownload"
                        download="mtg-cards.csv"
                        x-show="resolvedCards.length">Download CSV</a></div>
                <ul class="flex flex-col items-stretch">
                    <template x-for="row in cards" :key="row.id">
                        <li class="flex gap-x-6">
                            <button class="btn btn-sm" @click="addAnother(row)">+</button>
                            <template x-if="row.card">
                                <div class="flex-grow flex gap-x-6 px-2 py-1 items-center border border-primary border-b-0 rounded-t-lg">
                                    <a href="#preview_modal" class="flex-grow p-0 tooltip"
                                            data-tip="view card"
                                            x-text="row.card.name"
                                            @click.prevent="preview = row.card; preview_modal.showModal()"></a>
                                    <button class="btn btn-sm size-6 min-h-6 p-0 flex justify-center group tooltip"
                                            :data-tip="row.isFoil ? 'foil' : 'not foil'"
                                            :class="row.isFoil ? '' : 'btn-outline'"
                                            @click="row.isFoil = !row.isFoil">
                                        <x-icon.sparkles size="size-6"
                                             display="" ::class="row.isFoil ? 'inline' : 'hidden group-hover:inline'" />
                                    </button>
                                    <span x-text="`${row.card.set.toUpperCase()} ${row.card.collector_number.padStart(3, '0')}`"></span>
                                </div>
                            </template>
                            <template x-if="!row.card">
                                <div class="flex-grow flex gap-x-6 px-2 py-1 items-center border border-primary border-b-0 rounded-t-lg">
                                    <span class="flex-grow" x-text="row.search"></span>
                                    <x-icon.bolt x-show="!row.error && !row.results" class="animate-pulse" />
                                    <span x-show="row.error" class="text-error">
                                        <x-icon.error size="size-6"/>
                                        Service error
                                    </span>
                                    <a href="#select_modal"
                                       x-show="row.results"
                                       x-text="`${row.results.total_cards} cards found`"
                                       @click.prevent="select = row; select_modal.showModal()"></a>
                                </div>
                            </template>
                            <button class="btn btn-sm" @click="remove(row)">-</button>
                        </li>
                    </template>
                </ul>
            </div>
            <dialog id="preview_modal" class="modal">
                <div class="modal-box p-0">
                    <template x-if="preview">
                        <img :src="preview.image_uris.png" :alt="preview.name">
                    </template>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
            <dialog id="select_modal" class="modal">
                <div class="modal-box p-0 overflow-x-auto max-w-[90vw] w-fit">
                    <template x-if="select && select.results">
                        <div class="flex gap-x-4 w-full items-stretch">
                            <template x-for="result in select.results.data">
                                <button class="flex-shrink-0 flex flex-col items-center" @click="select.card = result; select_modal.close()">
                                    <img :src="result.image_uris.png" :alt="result.name" class="max-h-[75vh]">
                                    <span x-text="`${setName(result.set)} ${result.collector_number.padStart(3, '0')}`"></span>
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
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mtgLister', () => ({
                search: '',
                showFullSearch: false,
                set: '',
                cardNum: '',
                isFoil: false,
                cards: [],
                preview: null,
                select: null,
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
                        (!size || set.printed_size === Number(size))
                    )
                },
                setName(code) {
                    if (this.hasSetsData) {
                        const set = mtgSets.find(set => set.code === code.toLowerCase())
                        if (set) {
                            return set.name
                        }
                    }
                    return code.toUpperCase()
                },
                addAnother(row) {
                    const {isFoil, card} = row
                    this.newCardProcess({
                        isFoil, card
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
                get csvDownload() {
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
                    return encodeURI('data:text/csv;charset=utf-8,'
                        + "Count,Name,Edition,Collector Number,Foil\r\n"
                        + rows.map(row => [
                            row.count,
                            `"${row.name.replace('"', '""')}"`,
                            row.set,
                            row.cn,
                            row.foil ? 'foil' : '',
                        ].join(',')).join("\r\n"));
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
                    this.isFoil = false
                    this.setLookup.name = ''
                    this.setLookup.year = ''
                    this.setLookup.size = ''
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

                    const set = mtgSets.find(set => set.code === parts[0])
                    if (!set) return null

                    const numberFoil = parts[1].match(/(?<number>\d{1,4})(?<foil>f?)/i)
                    if (!numberFoil) return null

                    const num = Number(numberFoil.groups.number)
                    if (num > set.printed_size) return null

                    return {
                        set: set.code,
                        num,
                        foil: Boolean(numberFoil.groups.foil),
                    }
                },
                findCard() {
                    const match = this.matchSetNum(this.search)
                    if (match) {
                        this.getCard(match.set, match.num, match.foil)
                    } else {
                        this.searchCard(this.search, this.isFoil)
                    }
                    this.resetInputs()
                },
                newCardProcess(data) {
                    const process = {
                        id: kat.uniqueId(),
                        error: false,
                        card: false,
                        isFoil: false,
                        ...data
                    }
                    this.cards.unshift(process)
                    return this.cards[0]
                },
                async searchCard(search, isFoil) {
                    const process = this.newCardProcess({
                        search,
                        results: null,
                        isFoil,
                    })
                    const query = new URLSearchParams()
                    query.set('q', search)
                    query.set('unique', 'prints')
                    query.set('order', 'released')
                    try {
                        process.results = await this.scryfall(`cards/search?${query.toString()}`)
                        if (process.results.length === 1) {
                            process.card = process.results[0]
                        }
                        console.log(this.cards)
                    } catch (error) {
                        process.error = true
                        process.results = []
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
</x-layout>
