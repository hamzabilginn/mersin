/**
 * Enterprise-Grade Sync & Local Cache Layer (IndexedDB Wrapper)
 */
const DB_NAME = 'FieldFlowLocalDB';
const DB_VERSION = 2;
let db = null;

export const initDB = () => {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        
        request.onupgradeneeded = (e) => {
            const localDb = e.target.result;
            if (!localDb.objectStoreNames.contains('tasks')) localDb.createObjectStore('tasks', { keyPath: 'id' });
            if (!localDb.objectStoreNames.contains('messages')) localDb.createObjectStore('messages', { keyPath: 'id', autoIncrement: true });
            if (!localDb.objectStoreNames.contains('notifications')) localDb.createObjectStore('notifications', { keyPath: 'id' });
            if (!localDb.objectStoreNames.contains('sync_queue')) localDb.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
            if (!localDb.objectStoreNames.contains('ai_messages')) localDb.createObjectStore('ai_messages', { keyPath: 'id', autoIncrement: true });
        };

        request.onsuccess = (e) => {
            db = e.target.result;
            resolve(db);
        };

        request.onerror = (e) => reject(e.target.error);
    });
};

export const localDb = {
    getAll: (storeName) => {
        return new Promise((resolve) => {
            if (!db) return resolve([]);
            const tx = db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => resolve([]);
        });
    },

    put: (storeName, data) => {
        return new Promise((resolve) => {
            if (!db) return resolve();
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const req = store.put(data);
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => resolve(null);
        });
    },

    putAll: (storeName, items) => {
        return new Promise((resolve) => {
            if (!db) return resolve();
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            items.forEach(item => store.put(item));
            tx.oncomplete = () => resolve();
            tx.onerror = () => resolve();
        });
    },

    delete: (storeName, key) => {
        return new Promise((resolve) => {
            if (!db) return resolve();
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const req = store.delete(key);
            req.onsuccess = () => resolve();
            req.onerror = () => resolve();
        });
    },

    clear: (storeName) => {
        return new Promise((resolve) => {
            if (!db) return resolve();
            const tx = db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const req = store.clear();
            req.onsuccess = () => resolve();
            req.onerror = () => resolve();
        });
    }
};

/**
 * Background Synchronizer
 */
export const SyncManager = {
    async queueRequest(url, method, body) {
        const item = {
            url,
            method,
            body,
            timestamp: Date.now()
        };
        await localDb.put('sync_queue', item);
        return item;
    },

    async getQueueCount() {
        const queue = await localDb.getAll('sync_queue');
        return queue.length;
    },

    async flushQueue(onItemSynced = null) {
        const queue = await localDb.getAll('sync_queue');
        if (queue.length === 0) return;

        // Preserve execution order (FIFO)
        queue.sort((a, b) => a.timestamp - b.timestamp);

        for (let item of queue) {
            try {
                const response = await fetch(item.url, {
                    method: item.method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: item.body ? JSON.stringify(item.body) : null
                });

                if (response.ok) {
                    await localDb.delete('sync_queue', item.id);
                    if (onItemSynced) onItemSynced(item);
                } else {
                    // Stop flushing if server returns error (e.g. business validation failed)
                    console.error('Sync request failed with status:', response.status);
                    break;
                }
            } catch (err) {
                console.error('Sync network failure, halting queue flush:', err);
                break;
            }
        }
    }
};
