import { defineStore } from 'pinia';
import { localDb } from '@/services/sync';
import { useAuthStore } from './authStore';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        notifications: []
    }),

    getters: {
        unreadCount: (state) => state.notifications.filter(n => !n.is_read).length
    },

    actions: {
        async loadNotifications() {
            const authStore = useAuthStore();
            if (!authStore.currentUser) return;

            try {
                if (authStore.isOnline) {
                    const res = await fetch(`/api/notifications?user_id=${authStore.currentUser.id}`);
                    const data = await res.json();
                    this.notifications = data;
                    await localDb.clear('notifications');
                    await localDb.putAll('notifications', data);
                } else {
                    const allCached = await localDb.getAll('notifications');
                    this.notifications = allCached.filter(n => n.user_id == authStore.currentUser.id);
                }
            } catch (err) {
                console.error(err);
            }
        },

        async markAsRead(notificationId) {
            const authStore = useAuthStore();
            const notif = this.notifications.find(n => n.id == notificationId);
            if (notif) {
                notif.is_read = true;
                await localDb.put('notifications', notif);
            }

            if (authStore.isOnline) {
                try {
                    await fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' });
                } catch (err) {
                    console.error(err);
                }
            }
        },

        async markAllRead() {
            const authStore = useAuthStore();
            this.notifications.forEach(n => n.is_read = true);
            await localDb.putAll('notifications', this.notifications);

            if (authStore.isOnline) {
                try {
                    await fetch('/api/notifications/read-all', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ user_id: authStore.currentUser.id })
                    });
                } catch (err) {
                    console.error(err);
                }
            }
        }
    }
});
