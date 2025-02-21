import {mtgListerData} from 'js/mtgLister/mtgListerData.js'

import 'js/mtgLister/types.js'

document.addEventListener('alpine:init', () => {
    const placeholder = () => (cards => cards[Math.floor(Math.random() * cards.length)])([
        'eld 299', 'fdn 128', 'snc 425', 'woe 287', 'ncc 13',
    ])
    const app = mtgListerData()
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
                try {
                    return mtgSets.find(set => set.code === code.toLowerCase()) || null
                } catch (e) {
                    console.error(code, e)
                    return null
                }
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
                    console.log('Error fetching and/or storing card', {ref: cardRef, result: card || null, row})
                    row.error = error
                    console.error(error)
                    await this.list.removeRowRecord(row)
                }
            }
        },
        /**
         * @param {string} search
         * @returns {Promise<void>}
         */
        async searchCard(search) {
            const row = await this.list.add({search: {q: search}})
            const query = new URLSearchParams()
            query.set('q', search)
            query.set('unique', 'prints')
            query.set('order', 'released')

            try {
                let results = await this.scryfall(`cards/search?${query.toString()}`)
                /** @type CardPop[] */
                results = await Promise.all(results.data.map(card => {
                    card = this.prepareScryfallCard(card)
                    return app.cards.set(card).then(() => app.cardPop(card))
                }))
                if (results.length === 1) {
                    console.log('updating row with single result', results)
                    await this.updateRow(row, {card: results[0]})
                } else {
                    console.log('updating row with results list', results)
                    await this.updateRow(row, {results})
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
         * @param {CardRef[]|null} results
         * @param {boolean|null} foil
         * @returns {Promise<void>}
         */
        async updateRow(row, {card = null, results = null, foil = null}) {
            if (card) {
                row.card = card
                row.search = undefined
            } else if (results) {
                row.search.results = results
            }
            if (foil !== null) {
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
            const {name, set, collector_number, finishes, image_status, image_uris = null, card_faces = []} = card
            const imageUri = image_uris?.png || card_faces[0]?.image_uris?.png || null
            return {
                name,
                set,
                num: collector_number,
                finishes,
                imageStatus: image_status,
                imageUri,
                fetchedAt: Date.now()
            }
        },
    }))
})
