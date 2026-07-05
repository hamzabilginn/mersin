<template>
    <div class="flex w-screen h-screen overflow-hidden relative bg-[#f8fafc] text-slate-800 font-sans">
        
        <!-- Sidebar Navigation (Light Premium Theme) -->
        <aside class="w-72 flex-shrink-0 bg-white/95 backdrop-blur-md border-r border-slate-200/80 flex flex-col p-6 z-40 transition-all duration-300 md:translate-x-0"
               :class="{'translate-x-0': showMobileMenu, '-translate-x-full': !showMobileMenu, 'fixed inset-y-0 left-0': isMobile}">
            
            <div class="flex items-center gap-3 mb-8 p-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-xl font-bold text-slate-950 shadow-lg shadow-amber-500/20">🚧</div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">FieldFlow</h2>
                    <span class="text-[9px] text-amber-500 font-bold uppercase tracking-widest block -mt-0.5">Şantiye Otomasyon</span>
                </div>
            </div>

            <!-- Switcher Widget -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-6">
                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2">Aktif Kullanıcı Simülasyonu</div>
                <select v-model="selectedUserId" @change="switchUser" 
                        class="w-full bg-white border border-slate-200 rounded-xl text-slate-750 px-3 py-2 text-xs font-semibold outline-none focus:border-amber-500 cursor-pointer">
                    <option v-for="user in authStore.users" :key="user.id" :value="user.id">
                        {{ user.name }}
                    </option>
                </select>
                <div :class="['inline-block mt-3 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border', getRoleClass(authStore.currentUser?.role)]">
                    {{ authStore.currentUser?.role }}
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="flex flex-col gap-1.5 flex-grow">
                <router-link v-for="item in navItems" :key="item.tab"
                             :to="item.path"
                             @click="showMobileMenu = false"
                             active-class="bg-amber-500/10 text-amber-600 border-l-4 border-l-amber-500"
                             class="flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-all border-l-4 border-l-transparent">
                    <span class="text-base">{{ item.icon }}</span>
                    <span>{{ item.label }}</span>
                </router-link>
            </nav>

            <!-- Sync Widget -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mt-auto flex flex-col gap-3">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-500">Bağlantı:</span>
                    <div class="flex items-center gap-1.5">
                        <span :class="['w-2 h-2 rounded-full', authStore.isOnline ? 'bg-emerald-500 shadow-lg shadow-emerald-500' : 'bg-rose-500']"></span>
                        <span class="text-xs font-bold" :class="authStore.isOnline ? 'text-emerald-600' : 'text-rose-600'">
                            {{ authStore.isOnline ? 'Çevrimiçi' : 'Çevrimdışı' }}
                        </span>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400" id="sync-queue-count">
                    {{ authStore.syncQueueCount > 0 ? `${authStore.syncQueueCount} işlem bekliyor` : 'Tüm veriler eşitlendi' }}
                </div>
                <button @click="triggerManualSync"
                        class="w-full bg-white border border-slate-250 hover:bg-slate-50 text-[10px] font-bold py-2 rounded-lg text-slate-700 hover:text-slate-900 transition flex items-center justify-center gap-1 cursor-pointer shadow-xs">
                    🔄 Eşitle
                </button>
            </div>
        </aside>

        <!-- Main Viewport -->
        <main class="flex-grow h-screen overflow-y-auto flex flex-col relative">
            <!-- Global Header (Clean Light Theme) -->
            <header class="h-20 border-b border-slate-200/80 px-6 md:px-8 flex items-center justify-between bg-white/80 backdrop-blur-md sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <button v-if="isMobile" @click="showMobileMenu = !showMobileMenu" class="text-slate-600 text-lg mr-1 cursor-pointer">☰</button>
                    <h1 class="text-base md:text-lg font-bold text-slate-800 tracking-tight">{{ pageTitle }}</h1>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Simulate connection switch -->
                    <button @click="toggleConnection"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-[10px] font-bold border transition cursor-pointer',
                                authStore.isOnline 
                                    ? 'bg-rose-50/80 border-rose-200 text-rose-600 hover:bg-rose-100' 
                                    : 'bg-emerald-50/80 border-emerald-200 text-emerald-600 hover:bg-emerald-100'
                            ]">
                        {{ authStore.isOnline ? 'Simüle Çevrimdışı Yap' : 'Simüle Çevrimiçi Yap' }}
                    </button>

                    <!-- Notifications Dropdown -->
                    <div class="relative" ref="notificationDropdown">
                        <button @click="showNotifications = !showNotifications" class="relative p-2.5 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-slate-600 hover:text-slate-800 transition cursor-pointer">
                            <span>🔔</span>
                            <span v-if="notificationStore.unreadCount > 0" class="absolute -top-1.5 -right-1.5 bg-rose-600 text-[8px] font-bold text-white w-5 h-5 rounded-full border-2 border-white flex items-center justify-center animate-bounce">
                                {{ notificationStore.unreadCount }}
                            </span>
                        </button>

                        <!-- Panel -->
                        <div v-if="showNotifications" class="absolute right-0 mt-3 w-80 bg-white border border-slate-250 rounded-2xl shadow-2xl p-4 flex flex-col gap-3 z-50 animate-in fade-in slide-in-from-top-3 duration-200">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                                <span class="text-xs font-bold text-slate-800">Bildirimler</span>
                                <button @click="markAllRead" class="text-[10px] font-bold text-amber-600 hover:text-amber-700 cursor-pointer">Tümünü Okundu Say</button>
                            </div>
                            <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                                <div v-if="notificationStore.notifications.length === 0" class="text-center py-6 text-slate-400 text-[10px]">
                                    Bildirim bulunmuyor.
                                </div>
                                <div v-else v-for="notif in notificationStore.notifications" :key="notif.id"
                                     @click="readNotif(notif)"
                                     :class="['p-3 rounded-xl border text-[11px] cursor-pointer transition-colors', notif.is_read ? 'bg-slate-50/50 border-slate-100 text-slate-400' : 'bg-amber-500/5 border-amber-500/10 text-slate-800 font-medium']">
                                    <div class="font-bold mb-0.5 text-slate-700">{{ notif.title }}</div>
                                    <div>{{ notif.message }}</div>
                                    <div class="text-[9px] text-slate-400 mt-1">{{ formatTime(notif.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Render views -->
            <section class="p-6 md:p-8 flex-grow">
                <router-view />
            </section>
        </main>
        
        <!-- Mobile menu background overlay -->
        <div v-if="showMobileMenu && isMobile" @click="showMobileMenu = false" class="fixed inset-0 bg-black/60 z-30"></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useNotificationStore } from '@/stores/notificationStore';
import { useTaskStore } from '@/stores/taskStore';
import { useChatStore } from '@/stores/chatStore';
import { initDB } from '@/services/sync';

const route = useRoute();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const taskStore = useTaskStore();
const chatStore = useChatStore();

const selectedUserId = ref(null);
const showNotifications = ref(false);
const showMobileMenu = ref(false);
const isMobile = ref(false);

const navItems = [
    { tab: 'dashboard', label: 'Durum Paneli', path: '/', icon: '📊' },
    { tab: 'tasks', label: 'Görevler', path: '/tasks', icon: '📋' },
    { tab: 'chat', label: 'Saha İletişim', path: '/chat', icon: '💬' },
    { tab: 'ai', label: 'AI Copilot', path: '/ai', icon: '🤖' },
    { tab: 'analytics', label: 'Darboğaz Analizi', path: '/analytics', icon: '📈' },
    { tab: 'documentation', label: 'Teknik Rapor', path: '/documentation', icon: '📄' },
    { tab: 'guide', label: 'Uygulama Rehberi', path: '/guide', icon: '📖' }
];

const pageTitle = computed(() => {
    switch (route.name) {
        case 'dashboard': return 'Şantiye Durum Paneli';
        case 'tasks': return 'Görev Yönetim Sistemi';
        case 'chat': return 'Şantiye İletişim Kanalı';
        case 'ai': return 'Şantiye AI Yardımcısı';
        case 'analytics': return 'Analitik & Darboğaz Raporu';
        case 'documentation': return 'Teknik Mimari Rapor';
        case 'guide': return 'Uygulama Kurgusu & Rehber';
        default: return 'FieldFlow';
    }
});

onMounted(async () => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Init IndexedDB
    await initDB();
    await authStore.initConnection();
    await authStore.loadUsers();
    
    if (authStore.currentUser) {
        selectedUserId.value = authStore.currentUser.id;
    }
    
    await loadAppData();
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkMobile);
});

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

const loadAppData = async () => {
    await Promise.all([
        notificationStore.loadNotifications(),
        taskStore.loadTasks(),
        chatStore.loadMessages(),
        chatStore.loadAIMessages()
    ]);
};

const switchUser = async () => {
    const user = authStore.users.find(u => u.id == selectedUserId.value);
    if (user) {
        authStore.setCurrentUser(user);
        await loadAppData();
    }
};

const toggleConnection = async () => {
    await authStore.setOnline(!authStore.isOnline);
    await loadAppData();
};

const triggerManualSync = async () => {
    if (authStore.isOnline) {
        await authStore.setOnline(true);
        await loadAppData();
    } else {
        alert('Bağlantı çevrimdışı. Lütfen önce bağlantıyı simüle çevrimiçi yapın.');
    }
};

const readNotif = async (notif) => {
    await notificationStore.markAsRead(notif.id);
};

const markAllRead = async () => {
    await notificationStore.markAllRead();
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleString('tr-TR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const getRoleClass = (role) => {
    switch (role) {
        case 'tech_office': return 'bg-blue-500/10 text-blue-500 border-blue-500/30';
        case 'hom': return 'bg-amber-500/10 text-amber-600 border-amber-500/30';
        case 'sc': return 'bg-violet-500/10 text-violet-600 border-violet-500/30';
        case 'pm': return 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30';
        default: return 'bg-slate-800 text-slate-300 border-slate-700';
    }
};
</script>
