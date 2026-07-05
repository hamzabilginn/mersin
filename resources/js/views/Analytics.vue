<template>
    <div class="space-y-6">
        <!-- Bottlenecks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Delayed Tasks List -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col">
                <h2 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="text-rose-500">⏳</span> Süresi Geçen Görevler ({{ delayedTasks.length }})
                </h2>
                
                <div class="mt-4 space-y-3 max-h-64 overflow-y-auto pr-2">
                    <div v-if="delayedTasks.length === 0" class="text-xs text-slate-400 py-6 text-center font-medium">
                        Şu an için gecikmiş görev bulunmuyor.
                    </div>
                    
                    <div v-else v-for="t in delayedTasks" :key="t.id"
                         class="bg-slate-50 border border-slate-150 p-3.5 rounded-xl border-l-4 border-l-rose-600 flex justify-between items-center text-xs font-semibold">
                        <div>
                            <strong class="text-slate-800 block font-bold mb-0.5">{{ t.title }}</strong>
                            <span class="text-slate-500">Atanan: {{ t.worker?.name || '-' }} | Son Güncelleme: {{ formatDate(t.updated_at) }}</span>
                        </div>
                        <span class="text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded text-[10px] font-bold">Gecikti</span>
                    </div>
                </div>
            </div>

            <!-- Stuck Tasks List -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col">
                <h2 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="text-violet-500">🚪</span> Onay Kuyruğunda Tıkananlar ({{ stuckTasks.length }})
                </h2>
                
                <div class="mt-4 space-y-3 max-h-64 overflow-y-auto pr-2">
                    <div v-if="stuckTasks.length === 0" class="text-xs text-slate-400 py-6 text-center font-medium">
                        24 saatten uzun süredir onay bekleyen görev bulunmuyor.
                    </div>
                    
                    <div v-else v-for="t in stuckTasks" :key="t.id"
                         class="bg-slate-50 border border-slate-150 p-3.5 rounded-xl border-l-4 border-l-violet-600 flex justify-between items-center text-xs font-semibold">
                        <div>
                            <strong class="text-slate-800 block font-bold mb-0.5">{{ t.title }}</strong>
                            <span class="text-slate-500">Çalışan: {{ t.worker?.name || '-' }} | Bekleme Süresi: {{ getStuckTime(t.updated_at) }}</span>
                        </div>
                        <span class="text-violet-750 bg-violet-55/10 border border-violet-200 px-2 py-0.5 rounded text-[10px] font-bold">Bekliyor</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Statistics chart visualization -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Custom Progress Bars (100% Offline Compatible Chart) -->
            <div class="lg:col-span-2 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <h2 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">
                    📊 Görev Dağılım İstatistikleri
                </h2>
                
                <div class="mt-6 space-y-5" v-if="taskStore.bottlenecks?.chart_data">
                    <div v-for="(count, status) in taskStore.bottlenecks.chart_data" :key="status" class="space-y-1.5">
                        <div class="flex justify-between text-xs font-semibold">
                            <span class="text-slate-500">{{ getStatusLabel(status) }}</span>
                            <span class="text-slate-800 font-bold">{{ count }} Görev</span>
                        </div>
                        
                        <div class="h-3 bg-slate-100 rounded-full overflow-hidden w-full border border-slate-200">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out"
                                 :style="{
                                     width: getPct(count) + '%',
                                     background: getStatusColor(status)
                                 }">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary metrics -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">
                        📈 Özet Metrikler
                    </h2>
                    
                    <div class="mt-4 space-y-4 text-xs font-semibold" v-if="taskStore.bottlenecks?.summary">
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Toplam Sistem Görevi:</span>
                            <strong class="text-slate-800 font-bold text-sm">{{ taskStore.bottlenecks.summary.total }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Aktif Yürütülen Görev Oranı:</span>
                            <strong class="text-slate-800 font-bold text-sm">{{ progressPct }}%</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-3">
                            <span class="text-slate-500">Kabul Oranı (Onay/Toplam):</span>
                            <strong class="text-slate-800 font-bold text-sm">{{ approvedPct }}%</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-3 text-rose-600">
                            <span>Genel Gecikme Oranı:</span>
                            <strong class="font-bold text-sm">{{ delayedPct }}%</strong>
                        </div>
                    </div>
                </div>

                <div class="text-[10px] text-slate-400 italic mt-6 leading-relaxed border-t border-slate-100 pt-3 font-medium">
                    * Veriler yerel olarak IndexedDB ve sunucu verileri üzerinden eş zamanlı hesaplanmaktadır.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useTaskStore } from '@/stores/taskStore';

const taskStore = useTaskStore();

onMounted(async () => {
    await taskStore.loadBottlenecks();
});

const delayedTasks = computed(() => taskStore.bottlenecks?.delayed_tasks || []);
const stuckTasks = computed(() => taskStore.bottlenecks?.stuck_tasks || []);

const maxCount = computed(() => {
    if (!taskStore.bottlenecks?.chart_data) return 1;
    const values = Object.values(taskStore.bottlenecks.chart_data);
    return Math.max(...values, 1);
});

const getPct = (count) => {
    return ((count / maxCount.value) * 100).toFixed(0);
};

const progressPct = computed(() => {
    const summary = taskStore.bottlenecks?.summary;
    if (!summary || !summary.total) return 0;
    return ((summary.in_progress / summary.total) * 100).toFixed(0);
});

const approvedPct = computed(() => {
    const summary = taskStore.bottlenecks?.summary;
    if (!summary || !summary.total) return 0;
    return ((summary.approved / summary.total) * 100).toFixed(0);
});

const delayedPct = computed(() => {
    const summary = taskStore.bottlenecks?.summary;
    if (!summary || !summary.total) return 0;
    return ((summary.delayed_count / summary.total) * 100).toFixed(0);
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' });
};

const getStuckTime = (dateStr) => {
    if (!dateStr) return '0 sa';
    const diffMs = Date.now() - new Date(dateStr).getTime();
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    
    if (diffHours < 24) return `${diffHours} saat`;
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays} gün`;
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Hazırlanıyor (Bekleyen)';
        case 'in_progress': return 'İşlemde (Devam Eden)';
        case 'waiting_approval': return 'Onay Bekliyor';
        case 'approved': return 'Onaylandı (Kapatıldı)';
        case 'rejected': return 'Reddedildi';
        default: return status;
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return '#6b7280'; // gray
        case 'in_progress': return '#3b82f6'; // blue
        case 'waiting_approval': return '#8b5cf6'; // purple
        case 'approved': return '#10b981'; // green
        case 'rejected': return '#ef4444'; // red
        default: return '#cbd5e1';
    }
};
</script>
