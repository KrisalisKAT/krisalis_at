/**
 * @namespace mtgListerTypes
 */

/** @typedef {{setNum?: string, name: string, set: string, num: number, finishes: string[], imageStatus: string, imageUri: string, savedAt?: number}} mtgCard */
/** @typedef {{set: string, num: number}} CardRef */
/** @typedef {CardRef & {readonly card: mtgCard|null}} CardPop */
/** @typedef {{q: string, results: CardRef[]|CardPop[]|null}} CardSearch */
/** @typedef {{key?: number|null, listName?: string|null, search?: {q: string, results?: CardRef[]|null}|null, card?: CardRef|null, foil?: boolean}} dbRowRecordable */
/** @typedef {{key?: number, listName?: string, search?: CardSearch, card: CardRef|CardPop|null, foil: boolean}} RowRecord */
/** @typedef {RowRecord & {card: CardPop|null, error: *, hasError: boolean}} ActiveRowRecord */
/** @typedef {{name: string, set_type: string, parent_set_code: string|null, printed_size: number|null, card_count: number, icon_svg_uri: string, code: string, year: number|null}} mtgSet */
/** @typedef {{get(CardRef): (mtgCard|null), set(mtgCard): Promise<void>, remember((mtgCard|null)): mtgCard|null, recall(CardRef): Promise<mtgCard|null>}} cacheCards */
/** @typedef {{rows: ActiveRowRecord[], add(dbRowRecordable): Promise<ActiveRowRecord>, remove(RowRecord): Promise<void>, clear(): Promise<void>, update(RowRecord): Promise<void>, readonly byNew: ActiveRowRecord[], populate(): Promise<void>}} cacheList */
/** @typedef {{cards: cacheCards, cardPop(CardRef): CardPop, getLists(): Promise<string[]>, list(string): cacheList}} MtgListerData */

export {}
