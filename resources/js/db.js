/**
 * @template T
 * @param {IDBRequest<T>} request
 * @returns {Promise<T>}
 */
function promiseRequest(request) {
    return new Promise((resolve, reject) => {
        request.onsuccess = (event) => {
            resolve(event.target.result)
        }
        request.onerror = (event) => {
            console.error(event)
            reject(event.target.error || event)
        }
    })
}

/**
 * @param name
 * @param version
 * @param schema
 * @returns {Promise<IDBDatabase>}
 */
async function initDb(name, version, schema) {
    const db = await new Promise(resolve => {
        const request = window.indexedDB.open(name, version)
        request.onsuccess = (event) => {
            resolve(event.target.result)
        }
        request.onupgradeneeded = (event) => {
            const db = event.target.result
            db.onerror = (event) => {
                console.error(event.target.error?.message)
            }
            schema(db)
        }
    })
    db.onerror = (event) => {
        console.error(event.target.error?.message)
    }
    return db
}

export { promiseRequest, initDb }
