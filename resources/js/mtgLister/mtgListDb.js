import {initDb, promiseRequest} from 'js/db.js'
import {asCardRef, cardKey} from 'js/mtgLister/utils.js'
import 'js/mtgLister/types.js'

export function mtgListDb() {
    const db = initDb('mtg-lister', 1, (db) => {
        db.createObjectStore('cards', {keyPath: 'setNum'})

        const cardListStore = db.createObjectStore('cardList', {autoIncrement: true})
        cardListStore.createIndex('listName', 'listName', {unique: false});

        db.createObjectStore('options', {keyPath: 'name'})
    })

    const cards = {
        async save(card) {
            await this.saveAll([card])
        },
        async saveAll(cards) {
            cards.forEach(card => {
                card.setNum = card.setNum || cardKey(card)
                card.savedAt = Date.now()
            })
            const cardsStore = (await db).transaction(['cards'], 'readwrite')
                .objectStore('cards')
            for (let i = 0; i < cards.length; i++) {
                try {
                    await cardsStore.put(cards[i])
                } catch (e) {
                    console.log('Error storing card to DB', {card: cards[i], cards})
                    console.error(e)
                }
            }
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
        async getList(cardRefs) {
            const cardKeys = Object.keys(cardRefs.map(ref => cardKey(ref)).reduce((collection, key) => {
                collection[key] = null
                return collection
            }, {}))
            const cards = []
            const cardsStore = (await db).transaction(['cards']).objectStore('cards')
            let card
            for (let i = 0; i < cardRefs.length; i++) {
                try {
                    card = await promiseRequest(cardsStore.get(cardKeys[i]))
                    if (card) {
                        cards.push(card)
                    } else {
                        console.log('card not found', cardKeys[i])
                    }
                } catch (e) {
                    console.log('Error retrieving card '+cardKeys[i])
                    console.error(e)
                }
            }
            return cards
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
            listName: listName === null ? undefined : listName,
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
            const mtgDb = await db
            const lists = []
            await new Promise((resolve) => {
                const cardListDb = mtgDb.transaction(['cardList']).objectStore('cardList')
                const list = cardListDb.index('listName')
                list.openKeyCursor(null, 'nextunique').onsuccess = (event) => {
                    /** @type IDBCursor */
                    const cursor = event.target.result
                    if (cursor) {
                        lists.push(cursor.key)
                        cursor.continue()
                    } else {
                        resolve()
                    }
                }
            })
            return lists
        },
        /**
         * @param name {string}
         * @returns {Promise<RowRecord[]>}
         */
        async getList(name = '') {
            return promiseRequest(
                (await db).transaction(['cardList'])
                    .objectStore('cardList')
                    // .index('listName')
                    // .getAll(name))
                    .getAll())
        },
        /**
         * @param row {dbRowRecordable}
         * @param listName {string}
         * @returns {Promise<RowRecord>}
         */
        async addRow(row, listName = '') {
            try {
                row = mapRowForDb(row)
                row.listName = listName
                const dbCardList = (await db).transaction(['cardList'], 'readwrite').objectStore('cardList')
                row.key = await promiseRequest(dbCardList.add(row))
                await promiseRequest(dbCardList.put(row, row.key))
                return row
            } catch (e) {
                console.log('Error adding row')
                console.error(e)
            }
        },
        /**
         * @param {RowRecord} row
         * @returns {Promise<void>}
         */
        async updateRow(row) {
            try {
                row = mapRowForDb(row)
                const cardListStore = (await db).transaction(['cardList'], 'readwrite')
                    .objectStore('cardList')
                await promiseRequest(cardListStore.put(row, row.key))
            } catch (e) {
                console.log('error updating row')
                console.error(e)
            }
        },
        async removeRow(key) {
            console.error('removing row', key)
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
