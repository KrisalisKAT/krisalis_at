/**
 * @param cardRef {CardRef}
 * @returns {string}
 */
export function cardKey(cardRef) {
    const {set, num} = cardRef
    return `${set} ${num}`
}

/**
 * @param card {CardRef}
 * @returns {CardRef}
 */
export function asCardRef(card) {
    const {set, num} = card
    return {set, num}
}
