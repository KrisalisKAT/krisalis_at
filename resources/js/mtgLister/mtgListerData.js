import {mtgListDb} from 'js/mtgLister/mtgListDb.js'
import {cardKey} from 'js/mtgLister/utils.js'
import 'js/mtgLister/types.js'

/** @returns {MtgListerData} */
export function mtgListerData() {

    const db = mtgListDb()

    const cardCache = Alpine.reactive({})
    // /** @type {cacheCards} */
    const cards = {
        /**
         * @param cardRef {CardRef}
         * @returns {mtgCard|null}
         */
        get(cardRef) {
            return cardCache[cardKey(cardRef)] || null
        },
        /**
         * @param card {mtgCard}
         * @returns {Promise<void>}
         */
        set(card) {
            return db.cards.save(this.remember(card))
        },
        setList(cards) {
            cards.forEach(card => this.remember(card))
            return db.cards.saveAll(cards)
        },
        /**
         * @param card {mtgCard|null}
         * @returns {mtgCard|null}
         */
        remember(card) {
            if (card) {
                cardCache[cardKey(card)] = card
            }
            return card
        },
        /**
         * @param cardRef {CardRef}
         * @returns {Promise<mtgCard|null>}
         */
        async recall(cardRef) {
            let card = this.get(cardRef)
            if (card) return card

            card = await db.cards.get(cardRef)
            if (card) {
                console.info('card recalled', card)
            }
            return this.remember(card)
        },

        async recallList(cardRefs) {
            const cards = await db.cards.getList(cardRefs.filter(ref => !this.get(ref)))
            cards.forEach(card => this.remember(card))
        }
    }

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
                const index = this.rows.push(newRow)
                return this.rows[index - 1]
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
                await db.lists.updateRow(row)
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
                const dbList = await db.lists.getList(name)
                const list = dbList.map(row => invigorateRow(row))
                this.rows.push(...list)
                const cardKeyCollector = {}
                list.forEach(row => {
                    if (row.card) {
                        cardKeyCollector[cardKey(row.card)] = row.card
                    } else if (row.search?.results) {
                        row.search.results.forEach(result => {
                            cardKeyCollector[cardKey(result)] = result
                        })
                    }
                })

                await cards.recallList(Object.values(cardKeyCollector))
            },
        }
    }

    return {
        cards,
        cardPop,
        getLists,
        list,
    }
}
