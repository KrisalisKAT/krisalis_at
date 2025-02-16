import {initDb, promiseRequest} from './db.js'

/** @typedef {{setNum?: string, name: string, set: string, num: number, finishes: string[], imageStatus: string, imageUri: string, savedAt?: number}} mtgCard */
/** @typedef {{set: string, num: number}} CardRef */
/** @typedef {CardRef & {readonly card: mtgCard|null}} CardPop */
/** @typedef {{q: string, results: CardRef[]|CardPop[]|null}} CardSearch */
/** @typedef {{key?: number|null, listName?: string|null, search?: {q: string, results: CardRef[]|null}|null, card?: CardRef|null, foil?: boolean}} dbRowRecordable */
/** @typedef {{key?: number, listName?: string, search?: CardSearch, card: CardRef|CardPop|null, foil: boolean}} RowRecord */
/** @typedef {RowRecord & {card: CardPop|null, error: *, hasError: boolean}} ActiveRowRecord */
/** @typedef {{name: string, set_type: string, parent_set_code: string|null, printed_size: number|null, card_count: number, icon_svg_uri: string, code: string, year: number|null}} mtgSet */
/** @typedef {{get(CardRef): (mtgCard|null), set(mtgCard): Promise<void>, remember((mtgCard|null)): mtgCard|null, recall(CardRef): Promise<mtgCard|null>}} cacheCards */
/** @typedef {{rows: ActiveRowRecord[], add(dbRowRecordable): Promise<ActiveRowRecord>, remove(RowRecord): Promise<void>, clear(): Promise<void>, update(RowRecord): Promise<void>, readonly byNew: ActiveRowRecord[], populate(): Promise<void>}} cacheList */
/** @typedef {{cards: cacheCards, getLists(): Promise<string[]>, list(string): cacheList}} MtgListerApp */

/** @returns {MtgListerApp} */
function mtgLister() {
    /**
     * @param cardRef {CardRef}
     * @returns {string}
     */
    function cardKey(cardRef) {
        const {set, num} = cardRef
        return `${set} ${num}`
    }

    /**
     * @param card {CardRef}
     * @returns {CardRef}
     */
    function asCardRef(card) {
        const {set, num} = card
        return {set, num}
    }

    function mtgListDb() {
        const db = initDb('mtg-lister', 1, (db) => {
            db.createObjectStore('cards', {keyPath: 'setNum'})

            const cardListStore = db.createObjectStore('cardList', {autoIncrement: true})
            cardListStore.createIndex('listName', 'listName', {unique: false});

            db.createObjectStore('options', {keyPath: 'name'})
        })

        /** @type {{save(mtgCard): Promise<void>, get(CardRef): Promise<mtgCard|null>}} */
        const cards = {
            async save(card) {
                card.setNum = card.setNum || cardKey(card)
                card.savedAt = Date.now()

                await promiseRequest((await db).transaction(['cards'], 'readwrite')
                    .objectStore('cards').put(card))
            },
            async get(cardRef) {
                try {
                    return promiseRequest(
                        (await db).transaction(['cards'])
                            .objectStore('cards')
                            .get(cardKey(cardRef)))
                } catch (e) {
                    return null
                }
            },
        }

        /**
         * @param {dbRowRecordable} row
         * @returns {RowRecord}
         */
        function mapRowForDb(row) {
            const {key = null, listName = null, search = null, foil = false} = row

            return {
                key: key || undefined,
                listName: listName || undefined,
                search: search ? {q: search.q, results: search.results?.map(asCardRef)} : undefined,
                card: row.card ? asCardRef(row.card) : null,
                foil,
            }
        }

        /** @type {{getLists(): Promise<string[]>, getList(string): Promise<RowRecord[]>, addRow(dbRowRecordable, string): Promise<RowRecord>, updateRow(RowRecord): Promise<void>, removeRow(number): Promise<void>}} */
        const lists = {
            /**
             * @returns {Promise<string[]>}
             */
            async getLists() {
                return promiseRequest(
                    (await db).transaction(['cardList'])
                        .objectStore('cardList')
                        .index('listName')
                        .getAllKeys())
            },
            /**
             * @param name {string}
             * @returns {Promise<RowRecord[]>}
             */
            async getList(name = '') {
                return promiseRequest(
                    (await db).transaction(['cardList'])
                        .objectStore('cardList')
                        .index('listName')
                        .getAll(name))
            },
            /**
             * @param row {dbRowRecordable}
             * @param listName {string}
             * @returns {Promise<RowRecord>}
             */
            async addRow(row, listName = '') {
                row = mapRowForDb(row)
                row.listName = listName
                const dbCardList = (await db).transaction(['cardList'], 'readwrite').objectStore('cardList')
                row.key = await promiseRequest(dbCardList.add(row))
                await promiseRequest(dbCardList.put(row, row.key))
                return row
            },
            /**
             * @param {RowRecord} row
             * @returns {Promise<void>}
             */
            async updateRow(row) {
                row = mapRowForDb(row)
                await promiseRequest((await db).transaction(['cardList'], 'readwrite').objectStore('cardList').put(row, row.key))
            },
            async removeRow(key) {
                await promiseRequest((await db).transaction(['cardList'], 'readwrite').objectStore('cardList').delete(key))
            },
            async clearList(listName = '') {
                const mtgDb = await db
                return new Promise((resolve) => {
                    const cardListDb = mtgDb.transaction(['cardList'], 'readwrite').objectStore('cardList')
                    const list = cardListDb.index('listName')
                    list.openCursor(IDBKeyRange.only(listName)).onsuccess = (event) => {
                        /** @type IDBCursor */
                        const cursor = event.target.result
                        if (cursor) {
                            cursor.delete()
                            cursor.continue()
                        } else {
                            resolve()
                        }
                    }
                })
            }
        }
        const options = {
            async get(name, _default = null) {
                const record = await promiseRequest((await db).transaction(['options']).objectStore('options').get(name))
                return record?.value || _default
            },
            async set(name, value) {
                await promiseRequest((await db).transaction(['options'], 'readwrite').objectStore('options').put({
                    name,
                    value,
                }))
            },
        }

        return {cards, lists, options}
    }

    const db = mtgListDb()

    /** @type {cacheCards} */
    const cards = Alpine.reactive({
        /**
         * @param cardRef {CardRef}
         * @returns {mtgCard|null}
         */
        get(cardRef) {
            return cards[cardKey(cardRef)] || null
        },
        /**
         * @param card {mtgCard}
         * @returns {Promise<void>}
         */
        set(card) {
            return db.cards.save(this.remember(card))
        },
        /**
         * @param card {mtgCard|null}
         * @returns {mtgCard|null}
         */
        remember(card) {
            if (card) {
                const key = cardKey(card)
                cards[key] = card
                Alpine.effect(() => {
                    console.log(cards[key].name + ' remembered')
                })
            }
            return card
        },
        /**
         * @param cardRef {CardRef}
         * @returns {Promise<mtgCard|null>}
         */
        async recall(cardRef) {
            return this.get(cardRef) || this.remember(await db.cards.get(cardRef))
        },
    })

    /** @returns {Promise<string[]>} */
    async function getLists() {
        return db.lists.getLists()
    }

    /**
     * @param cardRef {CardRef}
     * @returns {CardPop}
     */
    function cardPop(cardRef) {
        const {set, num} = cardRef
        return {
            set,
            num,
            get card() {
                return cards.get(cardRef)
            },
        }
    }

    /**
     * @param {RowRecord} row
     * @returns {ActiveRowRecord}
     */
    function invigorateRow(row) {
        return {
            ...row,
            search: row.search ? {q: row.search.q, results: row.search.results?.map(cardPop)} : undefined,
            card: row.card ? cardPop(row.card) : null,
            error: null,
            get hasError() {
                return Boolean(this.error)
            },
        }
    }

    /**
     * @param {string} name
     * @returns {cacheList}
     */
    function list(name) {
        return {
            rows: [],
            /**
             * @param row {dbRowRecordable}
             * @returns {Promise<ActiveRowRecord>}
             */
            async add(row) {
                const rowRecord = await db.lists.addRow(row, name)
                const newRow = invigorateRow(rowRecord)
                this.rows.push(newRow)
                return newRow
            },
            /**
             * @param row {RowRecord}
             * @returns {Promise<void>}
             */
            async remove(row) {
                this.rows.splice(this.rows.indexOf(row), 1)
                return this.removeRowRecord(row)
            },
            async removeRowRecord(row) {
                return db.lists.removeRow(row.key)
            },
            async clear() {
                this.rows = []
                await db.lists.clearList(name)
            },
            /**
             * @param row {RowRecord}
             * @returns {Promise<void>}
             */
            async update(row) {
                return await db.lists.updateRow(row)
            },
            /**
             * @returns {ActiveRowRecord[]}
             */
            get byNew() {
                return this.rows.toReversed()
            },
            /**
             * @returns {Promise<void>}
             */
            async populate() {
                await Promise.allSettled((await db.lists.getList(name)).map(
                    /**
                     * @param row {RowRecord}
                     * @returns {Promise<void>}
                     */
                    async (row) => {
                        if (row.card) {
                            await cards.recall(row.card)
                        } else if (row.search?.results) {
                            await Promise.allSettled(row.search.results.map(
                                /**
                                 * @param result {CardRef}
                                 * @returns {Promise<void>}
                                 */
                                async result => {
                                    await cards.recall(result)
                                }),
                            )
                        }
                        this.rows.push(invigorateRow(row))
                    }),
                )
            },
        }
    }

    return {
        cards,
        getLists,
        list,
    }
}

document.addEventListener('alpine:init', () => {
    const placeholder = () => (cards => cards[Math.floor(Math.random() * cards.length)])([
        'eld 299', 'fdn 128', 'snc 425', 'woe 287', 'ncc 13',
    ])
    const app = mtgLister()
    const scryfallService = kat.rateLimitedService('https://api.scryfall.com/', 100)

    Alpine.data('mtgLister', () => ({

        // Data

        search: '',
        cardData: app.cards,
        isFoil: false,
        list: app.list(''),
        lists: [],
        placeholder: placeholder(),
        preview: null,
        select: null,
        hasSetsData: Boolean(mtgSets.length),
        rowActionIndex: 0,
        setCodeLock: null,

        // Init

        async init() {
            this.lists = await app.getLists()
            await this.list.populate()
        },

        // Getters

        /**
         * @returns {ActiveRowRecord|null}
         */
        get actionRow() {
            return this.list.byNew[this.rowActionIndex] || null
        },
        /**
         * @returns {number|null}
         */
        get actionRowKey() {
            return this.actionRow?.key
        },
        /**
         * @returns {string|null}
         */
        get setCodeHint() {
            if (this.action.usePrev) {
                if (this.action.do === '+') {
                    return this.action.match.card.set.toUpperCase()
                }
                if (this.action.do === 'l+') {
                    return this.action.setCode.toUpperCase()
                }
            }
            return this.setCodeLock?.toUpperCase()
        },
        /**
         * @param {string} code
         * @returns {mtgSet|null}
         */
        setByCode(code) {
            if (this.hasSetsData) {
                return mtgSets.find(set => set.code === code.toLowerCase()) || null
            }
            return null
        },
        /**
         * @returns {string}
         */
        get lockedSetPlaceholder() {
            return this.setCodeLock ?
                String(Math.floor(Math.random() * this.lockedSet.card_count) + 1)
                : this.placeholder
        },
        /**
         * @returns {mtgSet|null}
         */
        get lockedSet() {
            return this.setCodeLock ? this.setByCode(this.setCodeLock) : null
        },
        /**
         * @param {string} code
         * @returns {string}
         */
        setName(code) {
            const set = this.setByCode(code)
            return set ? set.name : code.toUpperCase()
        },
        /**
         * @returns {ActiveRowRecord[]}
         */
        get resolvedCards() {
            const rows = this.list.rows
            return rows.filter(row => row.card?.card)
        },
        /**
         * @returns {{count: number, name: string, set: string, num: string, foil: boolean}[]}
         */
        get distinctCards() {
            const list = this.resolvedCards
            const cardIndexes = {}
            const rows = []
            list.forEach(row => {
                const key = `${row.card.set}:${row.card.num}:${row.foil ? 'foil' : '-'}`
                if (cardIndexes[key] !== undefined) {
                    rows[cardIndexes[key]].count++
                } else {
                    cardIndexes[key] = rows.length
                    rows.push({
                        count: 1,
                        name: row.card.card.name,
                        set: row.card.set,
                        num: row.card.num,
                        foil: row.foil,
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
                    row.num,
                    row.foil ? 'foil' : '',
                ].join(',')).join("\r\n"));
        },
        get csvFileName() {
            const count = this.resolvedCards.length
            if (!count) return 'mtg-list_empty.csv'
            const card = this.distinctCards[0].name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            return `mtg-list_${count}_${card}.csv`
        },
        /**
         * @param {string} search
         * @returns {{card: CardRef, foil: boolean, usePrev: boolean}|null}
         */
        matchCardRef(search) {
            if (!this.hasSetsData) return null

            const parts = search.split(' ')
            if (parts.length === 1 && this.setCodeLock) {
                parts.unshift(this.setCodeLock)
            }
            if (parts.length !== 2) return null

            const usePrev = parts[0] === '<'
            if (usePrev) {
                if (this.actionRow?.set) {
                    parts[0] = this.actionRow.set
                } else {
                    return null
                }
            }

            const set = this.setByCode(
                parts[0] === '<' && this.actionRow?.set
                    ? this.actionRow.set
                    : parts[0],
            )
            if (!set) return null

            const numberFoil = parts[1].match(/(?<number>\d{1,4})(?<foil>f?)/i)
            if (!numberFoil) return null

            const num = Number(numberFoil.groups.number)
            if (num > set.card_count) return null

            return {
                card: {set: set.code, num},
                foil: Boolean(numberFoil.groups.foil),
                usePrev,
            }
        },
        /**
         * @returns {{do: string|null, row?: ActiveRowRecord|null, search?: string, match?: {card: CardRef, foil: boolean}, setCode?: string, usePrev?: boolean}}
         */
        get action() {
            const row = this.actionRow
            /** @type {mtgCard|null} */
            const card = row?.card?.card
            const search = this.search.trim().toLowerCase()

            if (!search) {
                if (card || row?.search?.results) return {do: '++', row}
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
                        if (card) return {do: 'l+', setCode: card.set, usePrev: true}
                        break
                    case 'x':
                        if (row) return {do: 'x', row}
                        break
                    case '?':
                        return {do: '?'}
                }
            }

            if (search.slice(0, 1) === '<' && this.setByCode(search.slice(1))) {
                return {do: 'l+', setCode: search.slice(1)}
            }

            const match = this.matchCardRef(search)
            if (match) {
                const {card, foil, usePrev} = match
                return {do: '+', match: {card, foil}, usePrev}
            }
            if (this.setCodeLock) {
                return {do: 's', search: `${search} set:${this.setCodeLock}`}
            }
            return {do: 's', search}
        },
        get actionLabel() {
            switch (this.action.do) {
                case null:
                case 's':
                    return 'Search Card'
                case '+':
                    return 'Add Card'
                case '++':
                    return 'Add Another'
                case 'vC':
                    return 'View Card'
                case 'vR':
                    return 'View Results'
                case 'f':
                    return 'Toggle Foil'
                case 'l+':
                    return 'Lock Set: ' + this.action.setCode
                case 'l-':
                    return 'Unlock Set'
                case 'x':
                    return 'Remove Row'
                case '?':
                    return 'Open Help'
            }
        },

        // Formatters

        /**
         * @param {CardRef} card
         * @returns {string}
         */
        padCardNum(card) {
            const set = this.setByCode(card.set)
            const num = card.num + ''
            return set ? num.padStart((set.card_count + '').length, '0') : num
        },

        // Actions

        mainAction(dispatch) {
            const action = this.action
            switch (action.do) {
                case null:
                    return
                case '+':
                    this.fetchCard(action.match.card, action.match.foil).then()
                    break
                case 's':
                    this.searchCard(action.search).then()
                    break
                case '++':
                    this.addAnother(action.row).then()
                    break
                case 'vC':
                    dispatch('view-card', action.row.card.card)
                    break
                case 'vR':
                    dispatch('view-results', action.row)
                    break
                case 'f':
                    this.updateRow(action.row, {foil: !action.row.foil}).then()
                    break
                case 'l+':
                    this.setCodeLock = action.setCode
                    break
                case 'l-':
                    this.setCodeLock = null
                    break
                case 'x':
                    this.list.remove(action.row).then()
                    break
                case '?':
                    dispatch('open-help')
                    break
            }

            this.resetInputs()
            dispatch('reset-set-search')
        },
        /**
         * @param {CardRef} cardRef
         * @param {boolean} foil
         * @returns {Promise<void>}
         */
        async fetchCard(cardRef, foil) {
            /** @type ActiveRowRecord */
            const row = await this.list.add({card: cardRef, foil})
            let card = row.card.card
            if (!card) {
                card = await app.cards.recall(row.card)
            }
            const aWeekInMs = 1000 * 60 * 60 * 24 * 7
            if (!card || card.savedAt < Date.now() - aWeekInMs) {
                try {
                    card = this.prepareScryfallCard(await this.scryfall(`cards/${cardRef.set}/${cardRef.num}`))
                    await app.cards.set(card)
                } catch (error) {
                    row.error = error
                    console.error(row)
                    await this.list.removeRowRecord(row)
                }
            }
        },
        /**
         * @param {string} search
         * @returns {Promise<void>}
         */
        async searchCard(search) {
            const row = await this.list.add({search})
            const query = new URLSearchParams()
            query.set('q', search)
            query.set('unique', 'prints')
            query.set('order', 'released')

            try {
                let results = await this.scryfall(`cards/search?${query.toString()}`)
                results = results.data.map(card => this.prepareScryfallCard(card))
                results.forEach(card => app.cards.set(card))
                if (row.results.length === 1) {
                    row.card = row.results[0]
                }
            } catch (error) {
                row.error = error
                console.error(row)
                await this.list.removeRowRecord(row)
            }
        },
        /**
         * @param {ActiveRowRecord} row
         * @returns {Promise<ActiveRowRecord>}
         */
        addAnother(row) {
            const {search = null, card = null, foil} = row
            return this.list.add({search, card, foil})
        },
        /**
         * @param {ActiveRowRecord} row
         * @param {CardRef|null} card
         * @param {boolean|null} foil
         * @returns {Promise<void>}
         */
        async updateRow(row, {card = null, foil = null}) {
            if (card) {
                row.card = card
            }
            if (foil) {
                row.foil = foil
            }
            await this.list.update(row)
        },
        /**
         * @param {ActiveRowRecord} row
         * @returns {Promise<void>}
         */
        async removeRow(row) {
            await this.list.remove(row)
        },
        async clearList() {
            if (window.confirm('Are you sure you want to remove all cards from the list?')) {
                await this.list.clear()
            }
        },
        resetInputs() {
            this.search = ''
            this.rowActionIndex = 0
            this.placeholder = placeholder()
        },
        async scryfall(...args) {
            const response = await scryfallService(...args)
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }
            return await response.json()
        },
        /**
         * @param {{name: string, set: string, collector_number: number, finishes: string[], image_status: string, image_uris: {png: string}}} card
         * @returns {mtgCard}
         */
        prepareScryfallCard(card) {
            const {name, set, collector_number, finishes, image_status, image_uris} = card
            const imageUri = image_uris.png || null
            return {
                name,
                set,
                num: collector_number,
                finishes,
                imageStatus: image_status,
                imageUri,
            }
        },
    }))
})
