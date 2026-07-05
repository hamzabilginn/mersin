<template>
    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm flex flex-col h-[calc(100vh-140px)]">
        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-violet-500/5 to-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-violet-500 to-indigo-500 flex items-center justify-center text-lg shadow-lg shadow-violet-500/20 text-white">🤖</div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Saha Asistanı AI</h3>
                    <span class="text-[10px] text-emerald-600 flex items-center gap-1 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Yerel Servis Etkin
                    </span>
                </div>
            </div>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">AI Copilot</span>
        </div>

        <!-- Suggestion Chips (Interviewer helpers) -->
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap gap-2 items-center">
            <span class="text-[10px] text-slate-450 font-bold uppercase tracking-wider mr-1">Hızlı Soru Sür:</span>
            <button v-for="prompt in suggestedPrompts" :key="prompt"
                    @click="askSuggestedPrompt(prompt)"
                    class="bg-white border border-slate-200 hover:border-violet-400 hover:bg-slate-50 text-[10px] px-3 py-1.5 rounded-lg text-slate-600 font-bold transition cursor-pointer">
                {{ prompt }}
            </button>
        </div>

        <!-- Chat messages -->
        <div class="flex-grow p-5 overflow-y-auto space-y-4 bg-slate-50/30" ref="messagesContainer">
            <div v-for="(msg, idx) in chatStore.aiMessages" :key="idx"
                 :class="['flex flex-col max-w-[80%] gap-1', msg.sender === 'self' ? 'ml-auto items-end' : 'mr-auto items-start']">
                
                <div class="text-[10px] text-slate-400 flex items-center gap-1.5 font-bold">
                    <strong :class="msg.sender === 'ai' ? 'text-violet-600' : 'text-slate-500'">
                        {{ msg.sender === 'ai' ? 'Saha Asistanı AI' : authStore.currentUser?.name }}
                    </strong>
                    <span v-if="msg.sender === 'ai'" class="text-[8px] bg-violet-50 text-violet-700 border border-violet-200 px-1 py-0.25 rounded font-bold uppercase">AI Copilot</span>
                </div>

                <div :class="[
                         'px-4 py-2.5 rounded-2xl text-xs leading-relaxed break-words shadow-xs border markdown-body',
                         msg.sender === 'self'
                            ? 'bg-amber-500 text-slate-950 border-amber-600 rounded-tr-none font-bold'
                            : 'bg-violet-50/50 border-violet-100 text-slate-800 rounded-tl-none font-medium'
                     ]"
                     v-html="renderMarkdown(msg.text)">
                </div>

                <span class="text-[9px] text-slate-400 mt-0.5">{{ msg.time }}</span>
            </div>

            <!-- Loader -->
            <div v-if="loading" class="flex flex-col max-w-[80%] gap-1 mr-auto items-start">
                <div class="text-[10px] text-slate-450 flex items-center gap-1.5 font-bold">
                    <strong class="text-violet-600">Saha Asistanı AI</strong>
                    <span class="text-[8px] bg-violet-50 text-violet-750 border border-violet-200 px-1 py-0.25 rounded font-bold uppercase">Yazıyor...</span>
                </div>
                <div class="px-4 py-3 bg-violet-50 border border-violet-200 rounded-2xl rounded-tl-none flex gap-1.5 items-center">
                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full animate-bounce duration-1000"></span>
                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full animate-bounce duration-1000 delay-150"></span>
                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full animate-bounce duration-1000 delay-300"></span>
                </div>
            </div>
        </div>

        <!-- Input Bar -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-3">
            <input type="text" v-model="newMessage" 
                   @keydown.enter="sendMsg"
                   :disabled="loading"
                   class="flex-grow bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-violet-500 transition-colors disabled:opacity-50" 
                   placeholder="Şantiye durumu veya görevler hakkında bir soru sorun...">
            <button @click="sendMsg"
                    :disabled="loading"
                    class="bg-gradient-to-r from-violet-500 to-indigo-500 hover:from-violet-600 hover:to-indigo-600 text-slate-100 text-xs font-bold px-5 py-3 rounded-xl shadow-lg shadow-violet-500/10 transition cursor-pointer disabled:opacity-50">
                Sor
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useChatStore } from '@/stores/chatStore';
import { useAuthStore } from '@/stores/authStore';

const chatStore = useChatStore();
const authStore = useAuthStore();

const newMessage = ref('');
const loading = ref(false);
const messagesContainer = ref(null);

const suggestedPrompts = [
    'Hangi görevler gecikti?',
    'Onay bekleyen görevler neler?',
    'Şantiyenin genel görev durumu nedir?'
];

onMounted(() => {
    scrollToBottom();
});

watch(() => chatStore.aiMessages.length, async () => {
    await nextTick();
    scrollToBottom();
});

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const sendMsg = async () => {
    if (!newMessage.value.trim() || loading.value) return;
    const text = newMessage.value.trim();
    newMessage.value = '';
    
    loading.value = true;
    try {
        await chatStore.sendAIMessage(text);
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const askSuggestedPrompt = async (prompt) => {
    if (loading.value) return;
    newMessage.value = prompt;
    await sendMsg();
};

const renderMarkdown = (text) => {
    if (!text) return '';
    // Basic clean HTML conversions
    return text
        .replace(/\*\*(.*?)\*\//g, '<strong>$1</strong>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>')
        .replace(/- (.*?)<br>/g, '<li class="ml-4 list-disc font-medium text-slate-700">$1</li>');
};
</script>

<style>
.markdown-body strong {
    color: #1e293b;
    font-weight: 700;
}
.markdown-body li {
    margin-top: 4px;
    margin-bottom: 4px;
}
</style>
