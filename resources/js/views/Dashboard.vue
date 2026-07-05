<template>
    <div class="space-y-6">
        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 border-l-4 border-l-rose-500">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Geciken Görevler</div>
                <div class="mt-2 text-3xl font-bold text-slate-800">{{ delayedCount }}</div>
                <div class="text-xs text-slate-400 mt-1">T0 Bitiş tarihi geçmiş işler</div>
            </div>
            
            <div class="relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 border-l-4 border-l-violet-500">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Onay Bekleyenler</div>
                <div class="mt-2 text-3xl font-bold text-slate-800">{{ waitingCount }}</div>
                <div class="text-xs text-slate-400 mt-1">SC / PM Onayında</div>
            </div>
            
            <div class="relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 border-l-4 border-l-amber-500">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sahada Devam Eden</div>
                <div class="mt-2 text-3xl font-bold text-slate-800">{{ progressCount }}</div>
                <div class="text-xs text-slate-400 mt-1">HoM Fact girişi bekleniyor</div>
            </div>
            
            <div class="relative overflow-hidden bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 border-l-4 border-l-emerald-500">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kabul Edilen</div>
                <div class="mt-2 text-3xl font-bold text-slate-800">{{ approvedCount }}</div>
                <div class="text-xs text-slate-400 mt-1">Kesinleşmiş gerçekleşmeler</div>
            </div>
        </div>

        <!-- Dashboard Body Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Critical Alarm Alerts -->
            <div class="lg:col-span-2 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span>⚠️</span> Darboğaz & Kritik Alarmlar
                </h2>
                
                <div class="mt-4 space-y-3">
                    <div v-if="criticalAlarms.length === 0" class="text-center text-slate-400 py-8 text-xs font-medium">
                        Şu an için kritik bir darboğaz alarmı bulunmuyor. Her şey yolunda!
                    </div>
                    
                    <div v-else v-for="alarm in criticalAlarms" :key="alarm.id" 
                         :class="[
                             'p-4 rounded-xl border flex justify-between items-center transition-all',
                             ['pending_sc', 'pending_pm'].includes(alarm.status) 
                                ? 'bg-violet-50/80 border-violet-100 text-violet-750' 
                                : 'bg-rose-50/80 border-rose-100 text-rose-750'
                         ]">
                        <div>
                            <span class="font-bold text-xs" :class="['pending_sc', 'pending_pm'].includes(alarm.status) ? 'text-violet-700' : 'text-rose-700'">
                                {{ ['pending_sc', 'pending_pm'].includes(alarm.status) ? 'ONAY BEKLİYOR:' : 'GECİKMİŞ GÖREV:' }}
                            </span>
                            <span class="ml-1 text-slate-800 font-bold text-sm">{{ alarm.zzz_code !== '0' ? alarm.zzz_code : alarm.tow }}</span>
                            <div class="text-xs text-slate-500 mt-1">
                                HoM: {{ alarm.hom?.name || '-' }} | T0 Tarihi: {{ alarm.due_date }}
                            </div>
                        </div>
                        <router-link to="/tasks" class="bg-white border border-slate-200 text-slate-700 text-xs px-3 py-1.5 rounded-lg font-bold transition hover:bg-slate-50">
                            İncele
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Guidance Column -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-800 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span>💡</span> Şantiye Rol Kılavuzu (ICN)
                </h2>
                
                <div class="text-xs text-slate-600 space-y-4 leading-relaxed font-medium">
                    <div class="flex gap-3">
                        <span class="text-amber-500 font-bold text-sm">👷</span>
                        <p><strong class="text-slate-800">Head of Master (HoM)</strong> rolündeyseniz, atanan T0 görevlerine gün sonu "Fact Qty / Man Day / Overtime" değerlerini girip SC onayına sunarsınız.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <span class="text-blue-500 font-bold text-sm">📝</span>
                        <p><strong class="text-slate-800">Teknik Ofis (Tech Office)</strong> rolündeyseniz T-1 gününden "Planned Qty / Man Day" belirleyerek ZZZ kodlarına iş atarsınız.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <span class="text-violet-500 font-bold text-sm">👑</span>
                        <p><strong class="text-slate-800">Site Chief & PM</strong> rollerindeyseniz sahadan gelen gerçekleşmeleri inceleyerek sırayla onay sürecini işletirsiniz.</p>
                    </div>

                    <div class="flex gap-3">
                        <span class="text-emerald-500 font-bold text-sm">📶</span>
                        <p>Sistem <strong class="text-slate-800">Offline-First</strong> (PWA) tasarlanmıştır. Çölün ortasında da, tünelde de veri girebilirsiniz!</p>
                    </div>
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
    await taskStore.loadTasks();
});

const today = new Date().toISOString().split('T')[0];

const delayedCount = computed(() => 
    taskStore.tasks.filter(t => t.status !== 'approved' && t.due_date < today).length
);

const waitingCount = computed(() => 
    taskStore.tasks.filter(t => ['pending_sc', 'pending_pm'].includes(t.status)).length
);

const progressCount = computed(() => 
    taskStore.tasks.filter(t => ['assigned', 'in_progress'].includes(t.status)).length
);

const approvedCount = computed(() => 
    taskStore.tasks.filter(t => t.status === 'approved').length
);

const criticalAlarms = computed(() => {
    const delayed = taskStore.tasks.filter(t => t.status !== 'approved' && t.due_date < today);
    const stuck = taskStore.tasks.filter(t => ['pending_sc', 'pending_pm'].includes(t.status));
    return [...delayed, ...stuck].slice(0, 5);
});
</script>
