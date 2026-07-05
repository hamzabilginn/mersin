<template>
    <div class="space-y-6">
        <!-- Top Toolbar & Filters -->
        <div class="flex flex-wrap justify-between items-center gap-4 bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
            <div class="flex flex-wrap gap-2">
                <button v-for="filter in filterOptions" :key="filter.value"
                        @click="activeFilter = filter.value"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-xl border transition-all cursor-pointer',
                            activeFilter === filter.value
                                ? 'bg-amber-500/10 border-amber-500/30 text-amber-600'
                                : 'bg-slate-50 border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-100'
                        ]">
                    {{ filter.label }}
                </button>
            </div>

            <!-- Tech Office Only Add Plan Button -->
            <button v-if="authStore.currentUser?.role === 'tech_office'"
                    @click="showCreateModal = true"
                    class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-amber-500/10 transition-all hover:scale-[1.01] flex items-center gap-1.5 cursor-pointer">
                <span>➕</span> Yeni Günlük Plan Ata
            </button>
        </div>

        <!-- Tasks Grid -->
        <div v-if="loading" class="text-center py-12 text-slate-400 text-xs font-semibold">
            Planlar yükleniyor...
        </div>
        
        <div v-else-if="filteredTasks.length === 0" class="text-center py-16 bg-white border border-slate-200/85 rounded-2xl text-slate-400 text-xs font-medium shadow-sm">
            Filtrelere uygun plan/görev bulunmuyor.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="task in filteredTasks" :key="task.id"
                 @click="openTaskDetails(task)"
                 class="group bg-white border border-slate-200/80 hover:border-slate-350 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer flex flex-col gap-4">
                <div class="flex justify-between items-start gap-4">
                    <span class="font-bold text-slate-800 group-hover:text-amber-600 transition-colors duration-200 leading-snug text-sm">{{ task.zzz_code !== '0' ? task.zzz_code : task.tow }}</span>
                    <span :class="['px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider', getStatusClass(task.status)]">
                        {{ getStatusLabel(task.status) }}
                    </span>
                </div>
                
                <div class="text-xs text-slate-500 font-medium space-y-1">
                    <div>TOW: {{ task.tow }}</div>
                    <div>STOW: {{ task.stow }}</div>
                    <div class="line-clamp-1">SSTOW: {{ task.sstow }}</div>
                </div>
                
                <div class="border-t border-slate-100 pt-3 text-[11px] text-slate-500 flex flex-col gap-1">
                    <div class="flex items-center gap-1">
                        <span>👷</span> HoM: <strong class="text-slate-700 ml-1 font-bold">{{ task.hom?.name || 'Atanmamış' }}</strong>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <div class="flex items-center gap-1">
                            <span>📝</span> Plan: <strong class="text-slate-700 ml-1 font-bold">{{ task.planned_qty }} Br</strong>
                        </div>
                        <span :class="['font-bold', isDelayed(task) ? 'text-rose-600' : 'text-slate-400']">
                            📅 {{ task.due_date }} {{ isDelayed(task) ? '⚠️' : '' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. TASK DETAIL MODAL -->
        <div v-if="selectedTask" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl flex flex-col animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-slate-100 flex justify-between items-start gap-4 sticky top-0 bg-white z-10 rounded-t-2xl">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">ZZZ: {{ selectedTask.zzz_code }} | {{ selectedTask.tow }}</h2>
                        <div class="mt-2 flex gap-2 items-center">
                            <span :class="['px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider', getStatusClass(selectedTask.status)]">
                                {{ getStatusLabel(selectedTask.status) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium border border-slate-200 px-2 rounded-full">Teslim: {{ selectedTask.due_date }}</span>
                        </div>
                    </div>
                    <button @click="closeDetails" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 cursor-pointer">&times;</button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Workflow Info Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 border border-slate-150 rounded-xl p-4 text-xs font-semibold">
                        <div>
                            <div class="text-slate-400 font-bold uppercase tracking-wider">Tech Office</div>
                            <div class="text-slate-700 mt-1">{{ selectedTask.techOffice?.name || '-' }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 font-bold uppercase tracking-wider">Head of Master</div>
                            <div class="text-slate-700 mt-1">{{ selectedTask.hom?.name || '-' }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 font-bold uppercase tracking-wider">Site Chief</div>
                            <div class="text-slate-700 mt-1">{{ selectedTask.sc?.name || '-' }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 font-bold uppercase tracking-wider">Project Manager</div>
                            <div class="text-slate-700 mt-1">{{ selectedTask.pm?.name || '-' }}</div>
                        </div>
                    </div>

                    <!-- Work Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs font-medium space-y-2">
                            <div class="text-blue-800 font-bold mb-2 uppercase tracking-wider text-[10px]">T-1 Plan Verileri</div>
                            <div class="flex justify-between border-b border-blue-100 pb-1"><span>TOW:</span> <span class="font-bold">{{ selectedTask.tow }}</span></div>
                            <div class="flex justify-between border-b border-blue-100 pb-1"><span>STOW:</span> <span class="font-bold">{{ selectedTask.stow }}</span></div>
                            <div class="flex justify-between border-b border-blue-100 pb-1"><span>SSTOW:</span> <span class="font-bold">{{ selectedTask.sstow }}</span></div>
                            <div class="flex justify-between border-b border-blue-100 pb-1"><span>Planned Qty:</span> <span class="font-bold text-blue-600">{{ selectedTask.planned_qty }}</span></div>
                            <div class="flex justify-between border-b border-blue-100 pb-1"><span>Planned Man Day:</span> <span class="font-bold">{{ selectedTask.planned_man_day }}</span></div>
                        </div>

                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs font-medium space-y-2">
                            <div class="text-amber-800 font-bold mb-2 uppercase tracking-wider text-[10px]">T0 Gerçekleşen (Fact) Veriler</div>
                            <div class="flex justify-between border-b border-amber-100/50 pb-1"><span>Fact Qty:</span> <span class="font-bold text-amber-600">{{ selectedTask.fact_qty !== null ? selectedTask.fact_qty : 'Girilecek' }}</span></div>
                            <div class="flex justify-between border-b border-amber-100/50 pb-1"><span>Fact Man Day:</span> <span class="font-bold">{{ selectedTask.fact_man_day !== null ? selectedTask.fact_man_day : 'Girilecek' }}</span></div>
                            <div class="flex justify-between border-b border-amber-100/50 pb-1"><span>Overtime:</span> <span class="font-bold">{{ selectedTask.overtime !== null ? selectedTask.overtime : 'Yok' }}</span></div>
                            <div class="mt-2 text-slate-600" v-if="selectedTask.comment">
                                <strong class="text-amber-800">Açıklama:</strong> {{ selectedTask.comment }}
                            </div>
                        </div>
                    </div>

                    <!-- Action forms (HoM Fact Entry) -->
                    <div v-if="authStore.currentUser?.role === 'hom' && ['assigned', 'in_progress'].includes(selectedTask.status)" class="border border-slate-200 rounded-xl p-5 space-y-4 bg-white shadow-sm">
                        <h3 class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1"><span>📊</span> Gün Sonu Veri Girişi (Fact)</h3>
                        
                        <div class="grid grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-500 font-bold uppercase">Fact Qty</label>
                                <input type="number" v-model="factData.fact_qty" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-amber-500" placeholder="-1 = İş bitmedi">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-500 font-bold uppercase">Fact Man Day</label>
                                <input type="number" v-model="factData.fact_man_day" step="0.5" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-amber-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-500 font-bold uppercase">Overtime</label>
                                <input type="number" v-model="factData.overtime" step="0.5" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-amber-500">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] text-slate-500 font-bold uppercase">Açıklama (Qty -1 ise zorunlu)</label>
                            <textarea v-model="factData.comment" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-amber-500 h-16" placeholder="Mazeret veya ilerleme durumu..."></textarea>
                        </div>

                        <div class="flex gap-2 justify-end pt-2">
                            <button @click="changeStatus('in_progress', factData)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2 rounded-lg transition cursor-pointer">
                                Taslak Kaydet (In Progress)
                            </button>
                            <button @click="changeStatus('pending_sc', factData)" class="bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold px-4 py-2 rounded-lg shadow transition cursor-pointer">
                                SC Onayına Gönder
                            </button>
                        </div>
                    </div>

                    <!-- Workflow Actions (SC / PM) -->
                    <div v-if="canTakeAction" class="border-t border-slate-150 pt-6 space-y-4">
                        <div class="flex items-center gap-4">
                            <input type="text" v-model="actionComment" placeholder="İsteğe bağlı not..." class="flex-grow border border-slate-200 rounded-lg px-3 py-2 text-xs outline-amber-500 bg-slate-50">
                            
                            <template v-if="authStore.currentUser?.role === 'sc'">
                                <button @click="changeStatus('pending_pm', {comment: actionComment})"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition cursor-pointer">
                                    Uygun Bul - PM'e İlet
                                </button>
                                <button @click="changeStatus('rejected', {comment: actionComment})"
                                        class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition cursor-pointer">
                                    Reddet (HoM'a Dön)
                                </button>
                            </template>

                            <template v-if="authStore.currentUser?.role === 'pm'">
                                <button @click="changeStatus('approved', {comment: actionComment})"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition cursor-pointer">
                                    Nihai Onay (Approve)
                                </button>
                                <button @click="changeStatus('rejected', {comment: actionComment})"
                                        class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition cursor-pointer">
                                    Reddet
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Status History Logs -->
                    <div class="space-y-3">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">📋 Görev Geçmişi (Tarihçe)</h3>
                        <div class="border border-slate-150 rounded-xl overflow-hidden bg-slate-50/50 max-h-48 overflow-y-auto p-4 space-y-4">
                            <div v-if="!selectedTask.logs || selectedTask.logs.length === 0" class="text-xs text-slate-400 text-center py-4">
                                Henüz bir geçmiş kaydı bulunmuyor.
                            </div>
                            <div v-else v-for="log in selectedTask.logs" :key="log.id" class="relative flex gap-4 text-xs">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center">⏱️</div>
                                <div class="bg-white border border-slate-150 rounded-xl p-3 flex-grow space-y-1 shadow-sm">
                                    <div class="flex justify-between text-[10px] text-slate-400">
                                        <span class="font-bold text-slate-600">{{ log.user?.name || 'Sistem' }}</span>
                                        <span>{{ formatDate(log.created_at) }}</span>
                                    </div>
                                    <div class="text-slate-700 font-bold">Durum: {{ getStatusLabel(log.new_status) }}</div>
                                    <div v-if="log.comment" class="text-amber-700 italic font-medium mt-1">"{{ log.comment }}"</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CREATE TASK MODAL (TECH OFFICE ONLY) -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg shadow-2xl flex flex-col animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white">
                    <h2 class="text-sm font-bold text-slate-800">📝 Yeni Günlük Plan / İş Ataması (T-1)</h2>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[11px] text-slate-500 font-bold">ZZZ Code</label>
                            <input type="text" v-model="newTask.zzz_code" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-amber-500" placeholder="Örn: 60114402">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] text-slate-500 font-bold">TOW (Type of Work)</label>
                            <input type="text" v-model="newTask.tow" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-amber-500" placeholder="TOW-02">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] text-slate-500 font-bold">STOW</label>
                            <input type="text" v-model="newTask.stow" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-amber-500" placeholder="STOW-23">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] text-slate-500 font-bold">SSTOW</label>
                            <input type="text" v-model="newTask.sstow" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-amber-500" placeholder="SSTOW-77">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                        <div class="space-y-1">
                            <label class="text-[11px] text-slate-500 font-bold">Planned Qty (Br)</label>
                            <input type="number" step="0.01" v-model="newTask.planned_qty" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-amber-500" placeholder="0.00">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] text-slate-500 font-bold">Planned Man Day</label>
                            <input type="number" step="0.5" v-model="newTask.planned_man_day" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-amber-500" placeholder="0.00">
                        </div>
                    </div>

                    <div class="space-y-1 border-t border-slate-100 pt-4">
                        <label class="text-[11px] text-slate-500 font-bold">Atanacak Head of Master (HoM)</label>
                        <select v-model="newTask.hom_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-700 focus:outline-amber-500 cursor-pointer">
                            <option value="">Seçiniz...</option>
                            <option v-for="h in homList" :key="h.id" :value="h.id">{{ h.name }} ({{ h.email }})</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-slate-500 font-bold">İcra Edilecek Tarih (T0)</label>
                        <input type="date" v-model="newTask.due_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-700 focus:outline-amber-500">
                    </div>

                    <button @click="submitTask" class="w-full mt-4 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold py-3 rounded-xl shadow-lg shadow-amber-500/10 transition cursor-pointer">
                        Planı Ata & Eşitle
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTaskStore } from '@/stores/taskStore';
import { useAuthStore } from '@/stores/authStore';
import { useNotificationStore } from '@/stores/notificationStore';

const taskStore = useTaskStore();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const loading = computed(() => taskStore.loading);
const activeFilter = ref('all');

onMounted(async () => {
    await taskStore.loadTasks();
});

const filterOptions = [
    { label: 'Tümü', value: 'all' },
    { label: 'Atananlar (Plan)', value: 'assigned' },
    { label: 'Devam Edenler', value: 'in_progress' },
    { label: 'SC Onay Bekleyen', value: 'pending_sc' },
    { label: 'PM Onay Bekleyen', value: 'pending_pm' },
    { label: 'Tamamlananlar', value: 'approved' },
];

const filteredTasks = computed(() => {
    if (activeFilter.value === 'all') return taskStore.tasks;
    return taskStore.tasks.filter(t => t.status === activeFilter.value);
});

const today = new Date().toISOString().split('T')[0];
const isDelayed = (task) => task.status !== 'approved' && task.due_date < today;

const getStatusLabel = (status) => {
    switch (status) {
        case 'draft': return 'Taslak';
        case 'assigned': return 'Atandı (Planlandı)';
        case 'in_progress': return 'Saha Girişi (Fact)';
        case 'pending_sc': return 'SC Onayında';
        case 'pending_pm': return 'PM Onayında';
        case 'approved': return 'Onaylandı';
        case 'rejected': return 'Reddedildi';
        default: return status;
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'assigned': return 'bg-blue-50 text-blue-600 border border-blue-200';
        case 'in_progress': return 'bg-amber-50 text-amber-700 border border-amber-200';
        case 'pending_sc': return 'bg-violet-50 text-violet-750 border border-violet-200';
        case 'pending_pm': return 'bg-purple-50 text-purple-750 border border-purple-200';
        case 'approved': return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        case 'rejected': return 'bg-rose-50 text-rose-700 border border-rose-200';
        default: return 'bg-slate-200 text-slate-800';
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleString('tr-TR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

// --- Task Detail Logic ---
const selectedTask = ref(null);
const actionComment = ref('');
const factData = ref({ fact_qty: null, fact_man_day: null, overtime: null, comment: '' });

const openTaskDetails = (task) => {
    selectedTask.value = task;
    actionComment.value = '';
    factData.value = {
        fact_qty: task.fact_qty,
        fact_man_day: task.fact_man_day,
        overtime: task.overtime,
        comment: task.comment || ''
    };
};

const closeDetails = () => {
    selectedTask.value = null;
};

const canTakeAction = computed(() => {
    if (!selectedTask.value) return false;
    const role = authStore.currentUser?.role;
    const task = selectedTask.value;

    if (role === 'sc' && task.status === 'pending_sc') return true;
    if (role === 'pm' && task.status === 'pending_pm') return true;
    
    return false;
});

const changeStatus = async (newStatus, customData = {}) => {
    if (!selectedTask.value) return;

    if (newStatus === 'pending_sc' && customData.fact_qty == -1 && !customData.comment) {
        alert("İş başlamadı (-1) olarak belirtildi, lütfen açıklama giriniz.");
        return;
    }

    const payload = {
        status: newStatus,
        ...customData
    };

    const updated = await taskStore.updateStatus(selectedTask.value.id, payload);
    
    if (updated) {
        selectedTask.value = updated;
    } else {
        selectedTask.value = null;
    }
    await notificationStore.loadNotifications();
    actionComment.value = '';
};

// --- Create Task Logic ---
const showCreateModal = ref(false);
const newTask = ref({
    zzz_code: '',
    tow: '',
    stow: '',
    sstow: '',
    planned_qty: null,
    planned_man_day: null,
    hom_id: '',
    due_date: new Date().toISOString().split('T')[0]
});

const homList = computed(() => authStore.users.filter(u => u.role === 'hom'));

const submitTask = async () => {
    if (!newTask.value.zzz_code || !newTask.value.hom_id) {
        alert('Lütfen en az ZZZ Kodu ve HoM seçimini yapın.');
        return;
    }
    
    await taskStore.createTask(newTask.value);

    // Reset Form
    newTask.value = {
        zzz_code: '',
        tow: '',
        stow: '',
        sstow: '',
        planned_qty: null,
        planned_man_day: null,
        hom_id: '',
        due_date: new Date().toISOString().split('T')[0]
    };
    showCreateModal.value = false;
};
</script>
