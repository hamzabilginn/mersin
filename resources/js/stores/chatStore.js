import { defineStore } from 'pinia';
import { localDb, SyncManager } from '@/services/sync';
import { useAuthStore } from './authStore';

export const useChatStore = defineStore('chat', {
    state: () => ({
        messages: [],
        aiMessages: [],
        loading: false,
        hasMore: true
    }),

    actions: {
        async loadMessages(loadMore = false) {
            this.loading = true;
            const authStore = useAuthStore();
            const userId = authStore.currentUser?.id || '';

            if (!loadMore) {
                this.hasMore = true;
            }

            const limit = 20;
            const offset = loadMore ? this.messages.length : 0;

            try {
                if (authStore.isOnline) {
                    const res = await fetch(`/api/messages?user_id=${userId}&limit=${limit}&offset=${offset}`);
                    const data = await res.json();
                    
                    if (loadMore) {
                        this.messages.unshift(...data);
                    } else {
                        this.messages = data;
                    }
                    
                    if (data.length < limit) {
                        this.hasMore = false;
                    }
                    
                    await localDb.putAll('messages', data);
                } else {
                    const allMsgs = await localDb.getAll('messages');
                    const userMsgs = allMsgs.filter(m => 
                        m.channel === 'general' || 
                        m.sender_id == userId || 
                        m.receiver_id == userId
                    );
                    
                    userMsgs.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    
                    if (loadMore) {
                        const start = Math.max(0, userMsgs.length - offset - limit);
                        const end = userMsgs.length - offset;
                        const nextBatch = userMsgs.slice(start, end);
                        this.messages.unshift(...nextBatch);
                        if (userMsgs.length - offset <= limit) {
                            this.hasMore = false;
                        }
                    } else {
                        const start = Math.max(0, userMsgs.length - limit);
                        const latestBatch = userMsgs.slice(start);
                        this.messages = latestBatch;
                        if (userMsgs.length <= limit) {
                            this.hasMore = false;
                        }
                    }
                }
            } catch (err) {
                console.error(err);
                const allMsgs = await localDb.getAll('messages');
                const userMsgs = allMsgs.filter(m => 
                    m.channel === 'general' || 
                    m.sender_id == userId || 
                    m.receiver_id == userId
                );
                userMsgs.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                
                if (loadMore) {
                    const start = Math.max(0, userMsgs.length - offset - limit);
                    const end = userMsgs.length - offset;
                    const nextBatch = userMsgs.slice(start, end);
                    this.messages.unshift(...nextBatch);
                    if (userMsgs.length - offset <= limit) {
                        this.hasMore = false;
                    }
                } else {
                    const start = Math.max(0, userMsgs.length - limit);
                    const latestBatch = userMsgs.slice(start);
                    this.messages = latestBatch;
                    if (userMsgs.length <= limit) {
                        this.hasMore = false;
                    }
                }
            } finally {
                this.loading = false;
            }
        },

        async sendMessage(content, receiverId = null, channel = 'general') {
            const authStore = useAuthStore();
            const offlineId = 'msg-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            
            const payload = {
                sender_id: authStore.currentUser.id,
                content,
                channel: receiverId ? null : channel,
                receiver_id: receiverId,
                offline_id: offlineId
            };

            // Optimistic UI push
            const tempMsg = {
                id: 'temp-' + Date.now(),
                sender_id: authStore.currentUser.id,
                content,
                channel: receiverId ? null : channel,
                receiver_id: receiverId,
                offline_id: offlineId,
                sender: authStore.currentUser,
                receiver: receiverId ? authStore.users.find(u => u.id == receiverId) : null,
                created_at: new Date().toISOString()
            };

            this.messages.push(tempMsg);

            // Strip Vue proxies/circular references by preparing a clean plain object for IndexedDB clone algorithm
            const cleanDbMsg = {
                id: tempMsg.id,
                sender_id: tempMsg.sender_id,
                content: tempMsg.content,
                channel: tempMsg.channel,
                receiver_id: tempMsg.receiver_id,
                offline_id: tempMsg.offline_id,
                sender: { id: authStore.currentUser.id, name: authStore.currentUser.name, role: authStore.currentUser.role },
                receiver: receiverId ? { id: receiverId, name: authStore.users.find(u => u.id == receiverId)?.name, role: authStore.users.find(u => u.id == receiverId)?.role } : null,
                created_at: tempMsg.created_at
            };
            await localDb.put('messages', cleanDbMsg);

            if (authStore.isOnline) {
                try {
                    const res = await fetch('/api/messages', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        const savedMsg = await res.json();
                        // Replace temp msg with server saved msg
                        const idx = this.messages.findIndex(m => m.offline_id === offlineId);
                        if (idx !== -1) {
                            this.messages[idx] = savedMsg;
                        }
                        await localDb.put('messages', savedMsg);
                        return savedMsg;
                    }
                } catch (err) {
                    console.error('Failed online, stored in queue:', err);
                }
            }

            // Sync queue fallback
            await SyncManager.queueRequest('/api/messages', 'POST', payload);
            await authStore.updateSyncCount();
            return tempMsg;
        },

        async loadAIMessages() {
            const authStore = useAuthStore();
            if (!authStore.currentUser) return;
            
            this.loading = true;
            try {
                if (authStore.isOnline) {
                    const res = await fetch(`/api/ai-messages?user_id=${authStore.currentUser.id}`);
                    const data = await res.json();
                    this.aiMessages = data;
                    await localDb.clear('ai_messages');
                    await localDb.putAll('ai_messages', data);
                } else {
                    const allAI = await localDb.getAll('ai_messages');
                    this.aiMessages = allAI.filter(m => m.user_id === authStore.currentUser.id);
                }
            } catch (err) {
                console.error(err);
                const allAI = await localDb.getAll('ai_messages');
                this.aiMessages = allAI.filter(m => m.user_id === authStore.currentUser.id);
            } finally {
                this.loading = false;
            }
            
            // Seed welcome message if empty
            if (this.aiMessages.length === 0) {
                const defaultMsg = {
                    user_id: authStore.currentUser.id,
                    sender: 'ai',
                    text: 'Merhaba! Ben şantiye yapay zeka asistanıyım. Çevrimdışı/çevrimiçi şantiye verilerine bakarak sorularınızı yanıtlayabilirim. Örneğin: **"Geciken görevler hangileri?"** yazabilirsiniz.',
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                    timestamp: Date.now()
                };
                this.aiMessages.push(defaultMsg);
                await localDb.put('ai_messages', defaultMsg);
                
                if (authStore.isOnline) {
                    try {
                        await fetch('/api/ai-messages', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                user_id: defaultMsg.user_id,
                                sender: defaultMsg.sender,
                                text: defaultMsg.text,
                                time: defaultMsg.time
                            })
                        });
                    } catch (e) {
                        console.error('Failed to sync seeded AI message:', e);
                    }
                }
            }
        },

        async sendAIMessage(text) {
            const authStore = useAuthStore();
            if (!authStore.currentUser) return;
            
            const timeStr = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const userMsg = {
                user_id: authStore.currentUser.id,
                sender: 'self',
                text,
                time: timeStr,
                timestamp: Date.now()
            };
            this.aiMessages.push(userMsg);
            await localDb.put('ai_messages', userMsg);

            // Sync user question to database
            if (authStore.isOnline) {
                try {
                    await fetch('/api/ai-messages', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            user_id: userMsg.user_id,
                            sender: userMsg.sender,
                            text: userMsg.text,
                            time: userMsg.time
                        })
                    });
                } catch (e) {
                    console.error('Failed online, stored in queue:', e);
                    await SyncManager.queueRequest('/api/ai-messages', 'POST', {
                        user_id: userMsg.user_id,
                        sender: userMsg.sender,
                        text: userMsg.text,
                        time: userMsg.time
                    });
                    await authStore.updateSyncCount();
                }
            } else {
                await SyncManager.queueRequest('/api/ai-messages', 'POST', {
                    user_id: userMsg.user_id,
                    sender: userMsg.sender,
                    text: userMsg.text,
                    time: userMsg.time
                });
                await authStore.updateSyncCount();
            }

            let aiReplyText = '';
            try {
                const res = await fetch('/api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: text,
                        user_id: authStore.currentUser.id
                    })
                });

                if (res.ok) {
                    const data = await res.json();
                    aiReplyText = data.reply;
                } else {
                    throw new Error();
                }
            } catch (err) {
                aiReplyText = 'Üzgünüm, şu anda yerel yapay zeka servisine bağlanırken teknik bir aksaklık yaşandı.';
            }

            const aiReply = {
                user_id: authStore.currentUser.id,
                sender: 'ai',
                text: aiReplyText,
                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                timestamp: Date.now()
            };

            this.aiMessages.push(aiReply);
            await localDb.put('ai_messages', aiReply);

            // Sync AI reply to database
            if (authStore.isOnline) {
                try {
                    await fetch('/api/ai-messages', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            user_id: aiReply.user_id,
                            sender: aiReply.sender,
                            text: aiReply.text,
                            time: aiReply.time
                        })
                    });
                } catch (e) {
                    console.error('Failed online, stored in queue:', e);
                    await SyncManager.queueRequest('/api/ai-messages', 'POST', {
                        user_id: aiReply.user_id,
                        sender: aiReply.sender,
                        text: aiReply.text,
                        time: aiReply.time
                    });
                    await authStore.updateSyncCount();
                }
            } else {
                await SyncManager.queueRequest('/api/ai-messages', 'POST', {
                    user_id: aiReply.user_id,
                    sender: aiReply.sender,
                    text: aiReply.text,
                    time: aiReply.time
                });
                await authStore.updateSyncCount();
            }
        }
    }
});
