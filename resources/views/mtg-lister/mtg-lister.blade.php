<x-layout title="MTG Lister">
    <div class="mx-auto container pa-4">
        <div class="card w-full">
            <h1 class="font-bold text-2xl">MTG Lister</h1>
            <p>A little utility for getting a list of card names</p>
        </div>
        <div class="flex gap-6" x-data="mtgLister">
            <form class="flex flex-wrap gap-x-4">
                <div class="flex flex-col flex-grow" x-if="showFullSearch">
                    <label for="searchInput">
                        Full Scryfall Search
                    </label>
                    <input id="searchInput" x-ref="searchInput"
                           class="input input-bordered input-sm"
                           autofocus
                           @keyup.left="
                               showFullSearch = false;
                               $nextTick(() => $refs.setInput.focus())
                           "
                           x-model="search" />
                </div>
                <div class="flex flex-col flex-grow" x-if="!showFullSearch">
                    <label for="setInput">
                        Set Code
                    </label>
                    <input id="setInput" x-ref="setInput"
                           class="input input-bordered input-sm"
                           autofocus
                           @keyup.left="
                               showFullSearch = true;
                               $nextTick(() => $refs.fullInput.focus())
                           "
                           x-model="set"/>
                </div>
                <div class="flex flex-col flex-grow" x-if="!showFullSearch">
                    <label for="cnInput">
                        Card #
                    </label>
                    <input id="cnInput" x-ref="cnInput"
                           class="input input-bordered input-sm"
                           x-model="cardNum"/>
                </div>
                <div class="flex items-end">
                    <button class="btn btn-sm btn-outline"
                            x-ref="addCardButton"
                            @click="findCard($refs)">
                        Add Card
                    </button>
                </div>
            </form>
            <ul>
                <li x-for="card in cards">

                </li>
            </ul>
        </div>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mtgLister', () => ({
                search: '',
                showFullSearch: false,
                set: '',
                cardNum: '',
                cards: [],
                resetInputs() {
                    this.search = ''
                    this.set = ''
                    this.cardNum = ''
                },
                scryfall: kat.rateLimitedService('https://api.scryfall.com/', 100),
                findCard(refs) {
                    if (this.showFullSearch) {
                        this.searchCard(this.search)
                        refs.searchInput.focus()
                    } else {
                        this.getCard(this.set, this.cardNum)
                        refs.setInput.focus()
                    }
                    this.resetInputs();
                },
                async unwrapJsonResponse(fetch) {
                    const response = await fetch
                    if (!response.ok) {
                        throw new Error(`Response status: ${response.status}`);
                    }
                    return await response.json()
                },
                async searchCard(search) {
                    const process = {
                        search,
                        results: null,
                        error: false,
                        card: null,
                    }
                    const query = new URLSearchParams()
                    query.set('q', search)
                    query.set('unique', 'prints')
                    try {
                        process.results = await this.unwrapJsonResponse(this.scryfall(`cards/search?${query.toString()}`))
                        if (process.results.length === 1) {
                            process.card = process.results[0]
                        }
                    } catch (error) {
                        process.error = true
                        process.results = []
                        console.error({search, error})
                    }
                },
                async getCard(set, cardNum) {
                    const process = {
                        set,
                        cardNum,
                        error: false,
                        card: null,
                    }
                    try {
                        process.card = await this.unwrapJsonResponse(this.scryfall(`cards/${set}/${cardNum}`))
                    } catch (error) {
                        process.error = true
                        console.error({set, cardNum, error})
                    }
                }
            }))
        })
    </script>
</x-layout>
