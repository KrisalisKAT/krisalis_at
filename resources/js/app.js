import Alpine from 'alpinejs';
// import './bootstrap';

window.Alpine = Alpine;

window.kat = {
    rateLimitedService(baseUrl, timeout) {
        const service = {
            baseUrl,
            timeout,
            requests: [],
            debounceTimer: null,
            fetch(path, options = null) {
                const process = {path, options}
                process.promise = new Promise((resolve, reject) => {
                    process.resolve = resolve;
                    process.reject = reject;
                });
                this.requests.push(process)
                if (!this.debounceTimer) {
                    this.nextRequest()
                }
                return process.promise
            },
            async nextRequest() {
                const debounce = {}
                this.debounceTimer = debounce
                const request = this.requests.shift()
                const promise = fetch(this.baseUrl+request.path, request.options)
                request.resolve(promise)
                try {
                    await promise
                } finally {
                    setTimeout(() => {
                        if (this.requests.length && this.debounceTimer === debounce) {
                            this.nextRequest()
                        } else {
                            this.debounceTimer = null
                        }
                    }, this.timeout)
                }
            }
        }
        return (path, options = null) => service.fetch(path, options)
    },
    uniqueId() {
        return Date.now().toString(36) +
            Math.random().toString(36).substring(2, 10).padStart(8, '0')
    }
}

Alpine.start();
