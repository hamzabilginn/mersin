import { defineStore } from 'pinia';
import { SyncManager } from '@/services/sync';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        users: [],
        currentUser: null,
        isOnline: navigator.onLine,
        syncQueueCount: 0
    }),
    
    actions: {
        async initConnection() {
            window.addEventListener('online', () => this.setOnline(true));
            window.addEventListener('offline', () => this.setOnline(false));
            await this.updateSyncCount();
        },

        async setOnline(status) {
            this.isOnline = status;
            if (status) {
                // Flush queue on reconnect
                await SyncManager.flushQueue();
                await this.updateSyncCount();
            }
        },

        async updateSyncCount() {
            this.syncQueueCount = await SyncManager.getQueueCount();
        },

        async loadUsers() {
            try {
                if (this.isOnline) {
                    const res = await fetch('/api/users');
                    this.users = await res.json();
                } else {
                    this.users = [
                        { id: 1, name: 'Project Manager', email: 'pm@icn.com', role: 'pm' },
                        { id: 2, name: 'A Bolge SC', email: 'abolge_sc@icn.com', role: 'sc' },
                        { id: 3, name: 'A Bolge Tech Office', email: 'abolgetechoffice@icn.com', role: 'tech_office' },
                        { id: 4, name: 'HoM 30AAA', email: '30AAA-hom@icn.com', role: 'hom' }
                    ];
                }
                
                const savedUserId = localStorage.getItem('simulated_user_id');
                if (savedUserId) {
                    const found = this.users.find(u => u.id == savedUserId);
                    this.currentUser = found || this.users[0];
                } else if (!this.currentUser && this.users.length > 0) {
                    this.currentUser = this.users[0];
                }
            } catch (err) {
                console.error(err);
            }
        },

        setCurrentUser(user) {
            this.currentUser = user;
            if (user) {
                localStorage.setItem('simulated_user_id', user.id);
            } else {
                localStorage.removeItem('simulated_user_id');
            }
        }
    }
});
