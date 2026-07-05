<template>
    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm flex h-[calc(100vh-140px)]">
        
        <!-- Chat Target Sidebar -->
        <aside class="w-64 md:w-72 border-r border-slate-200/80 flex flex-col bg-slate-50/40 flex-shrink-0">
            <div class="p-4 border-b border-slate-200 bg-slate-50/60">
                <h4 class="text-xs font-bold text-slate-450 uppercase tracking-wider">Konuşmalar</h4>
            </div>
            
            <div class="flex-grow overflow-y-auto p-3 space-y-4">
                <!-- Channels Section -->
                <div class="space-y-1">
                    <span class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kanallar</span>
                    <button @click="selectTarget('general')"
                            :class="[
                                'w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-left transition-all cursor-pointer',
                                activeTarget === 'general'
                                    ? 'bg-amber-500/10 text-amber-600 border-l-4 border-l-amber-500 font-bold'
                                    : 'text-slate-600 hover:text-slate-800 hover:bg-slate-100/60 font-medium'
                            ]">
                        <span class="text-sm">📢</span>
                        <div class="flex-grow min-w-0">
                            <div class="text-xs truncate">Genel İletişim</div>
                            <span class="text-[9px] text-slate-400 block -mt-0.5">Tüm saha ekibi</span>
                        </div>
                    </button>
                </div>

                <!-- Direct Messages Section -->
                <div class="space-y-1">
                    <span class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kişiler (Özel)</span>
                    <button v-for="user in otherUsers" :key="user.id"
                            @click="selectTarget(user)"
                            :class="[
                                'w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-left transition-all cursor-pointer',
                                activeTarget !== 'general' && activeTarget?.id === user.id
                                    ? 'bg-amber-500/10 text-amber-600 border-l-4 border-l-amber-500 font-bold'
                                    : 'text-slate-600 hover:text-slate-800 hover:bg-slate-100/60 font-medium'
                            ]">
                        <span class="w-7 h-7 rounded-full bg-slate-200/80 border border-slate-300 flex items-center justify-center text-xs">👤</span>
                        <div class="flex-grow min-w-0">
                            <div class="text-xs truncate font-bold text-slate-750">{{ user.name }}</div>
                            <span :class="['text-[8px] font-extrabold uppercase tracking-wide px-1.5 py-0.25 rounded inline-block', getRoleClass(user.role)]">
                                {{ getRoleLabel(user.role) }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <div class="flex-grow flex flex-col h-full bg-white">
            <!-- Header -->
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-base">{{ activeTarget === 'general' ? '📢' : '💬' }}</span>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">
                            {{ activeTarget === 'general' ? 'Saha Genel İletişim Kanalı' : activeTarget.name }}
                        </h4>
                        <span class="text-[9px] text-slate-400 block -mt-0.5">
                            {{ activeTarget === 'general' ? 'Herkese açık ortak şantiye kanalı' : getRoleLabel(activeTarget.role) + ' ile özel mesajlaşma' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="!chatStore.hasMore && filteredMessages.length > 0" class="text-[9px] text-slate-400 font-bold uppercase tracking-wider bg-slate-100 px-2 py-0.5 rounded-md">
                        Tüm Geçmiş Yüklendi
                    </span>
                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest bg-slate-200 px-2 py-0.5 rounded-md">
                        {{ activeTarget === 'general' ? '#general' : '@ozel' }}
                    </span>
                </div>
            </div>

            <!-- Messages list -->
            <div class="flex-grow p-5 overflow-y-auto space-y-4 bg-slate-50/20" ref="messagesContainer" @scroll="handleScroll">
                <!-- Infinite Scroll Loading Indicator -->
                <div v-if="loadingMore" class="text-center py-2 text-[10px] text-slate-450 font-bold flex items-center justify-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-bounce"></span>
                    Eski mesajlar yükleniyor...
                </div>

                <div v-if="filteredMessages.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 gap-2">
                    <span class="text-3xl">📭</span>
                    <span class="text-xs font-semibold">Henüz mesaj bulunmuyor. İlk mesajı siz gönderin!</span>
                </div>
                <div v-else v-for="msg in filteredMessages" :key="msg.id"
                     :class="['flex flex-col max-w-[75%] gap-1', msg.sender_id == authStore.currentUser?.id ? 'ml-auto items-end' : 'mr-auto items-start']">
                    
                    <div class="text-[10px] text-slate-455 flex items-center gap-1.5 font-bold">
                        <span class="text-slate-600">{{ msg.sender?.name || 'Sistem' }}</span>
                        <span :class="['px-1.5 py-0.25 rounded text-[8px] font-extrabold uppercase', getRoleClass(msg.sender?.role)]">
                            {{ getRoleLabel(msg.sender?.role) }}
                        </span>
                    </div>

                    <div :class="[
                             'px-4 py-2.5 rounded-2xl text-xs leading-relaxed break-words shadow-xs border',
                             msg.sender_id == authStore.currentUser?.id
                                ? 'bg-amber-500 text-slate-950 border-amber-600 rounded-tr-none font-bold shadow-sm shadow-amber-500/10'
                                : 'bg-white text-slate-700 border-slate-200 rounded-tl-none font-medium'
                         ]">
                        {{ msg.content }}
                    </div>
                    
                    <span class="text-[9px] text-slate-400 mt-0.5">{{ formatTime(msg.created_at) }}</span>
                </div>
            </div>

            <!-- Input Bar -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-3">
                <input type="text" v-model="newMessage" 
                       @keydown.enter="sendMsg"
                       class="flex-grow bg-white border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-amber-500 transition-colors shadow-xs" 
                       :placeholder="activeTarget === 'general' ? 'Genel kanala yazın...' : `${activeTarget.name} kullanıcısına özel mesaj gönder...`">
                <button @click="sendMsg"
                        class="bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold px-5 py-3 rounded-xl shadow-lg shadow-amber-500/10 transition cursor-pointer">
                    Gönder
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useChatStore } from '@/stores/chatStore';
import { useAuthStore } from '@/stores/authStore';

const chatStore = useChatStore();
const authStore = useAuthStore();

const newMessage = ref('');
const activeTarget = ref('general');
const messagesContainer = ref(null);
const loadingMore = ref(false);

const otherUsers = computed(() => {
    return authStore.users.filter(u => u.id !== authStore.currentUser?.id);
});

// Dynamic filtering of chat messages locally
const filteredMessages = computed(() => {
    const curUserId = authStore.currentUser?.id;
    if (!curUserId) return [];

    if (activeTarget.value === 'general') {
        return chatStore.messages.filter(m => m.channel === 'general');
    } else {
        const targetUserId = activeTarget.value.id;
        return chatStore.messages.filter(m => 
            !m.channel && (
                (m.sender_id == curUserId && m.receiver_id == targetUserId) ||
                (m.sender_id == targetUserId && m.receiver_id == curUserId)
            )
        );
    }
});

onMounted(async () => {
    await chatStore.loadMessages();
    scrollToBottom();
});

// Reset activeTarget to general if currentUser changes, to avoid cross-user state leak and DM filter bugs
watch(() => authStore.currentUser?.id, async () => {
    activeTarget.value = 'general';
    await chatStore.loadMessages();
    scrollToBottom();
});

// Smart auto-scroll: scroll to bottom only on sending or when already at the bottom
watch(() => filteredMessages.value, async (newVal) => {
    await nextTick();
    if (!newVal || newVal.length === 0) return;
    
    const container = messagesContainer.value;
    if (!container) return;

    const lastMsg = newVal[newVal.length - 1];
    const sentByMe = lastMsg && lastMsg.sender_id == authStore.currentUser?.id;
    const wasNearBottom = container.scrollHeight - container.clientHeight - container.scrollTop < 120;

    if (sentByMe || wasNearBottom) {
        scrollToBottom();
    }
}, { deep: true });

const selectTarget = (target) => {
    activeTarget.value = target;
};

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const handleScroll = async () => {
    const container = messagesContainer.value;
    if (!container) return;

    // Check if scrolled to the top and we have more messages to load
    if (container.scrollTop < 10 && !loadingMore.value && chatStore.hasMore) {
        loadingMore.value = true;
        const oldScrollHeight = container.scrollHeight;

        await chatStore.loadMessages(true);

        await nextTick();
        // Restore scroll position so it doesn't jump
        container.scrollTop = container.scrollHeight - oldScrollHeight;
        loadingMore.value = false;
    }
};

const sendMsg = async () => {
    if (!newMessage.value.trim()) return;
    const content = newMessage.value.trim();
    newMessage.value = '';

    if (activeTarget.value === 'general') {
        await chatStore.sendMessage(content, null, 'general');
    } else {
        await chatStore.sendMessage(content, activeTarget.value.id, null);
    }
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const getRoleLabel = (role) => {
    switch (role) {
        case 'planner': return 'Planlamacı';
        case 'field_worker': return 'Saha Çalışanı';
        case 'manager': return 'Yönetici';
        default: return role;
    }
};

const getRoleClass = (role) => {
    switch (role) {
        case 'planner': return 'bg-blue-50 text-blue-700 border border-blue-200';
        case 'field_worker': return 'bg-amber-50 text-amber-700 border border-amber-200';
        case 'manager': return 'bg-violet-50 text-violet-750 border border-violet-200';
        default: return 'bg-slate-100 text-slate-700';
    }
};
</script>
