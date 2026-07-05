<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workforce Execution MVP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .mobile-frame { width: 375px; height: 812px; background: #fff; margin: 0 auto; border-radius: 40px; border: 12px solid #1e293b; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow-y: auto; position: relative; }
        .offline-banner { background: #ef4444; color: white; text-align: center; padding: 5px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div id="app" class="h-screen flex flex-col">
        <!-- HEADER -->
        <header class="bg-slate-900 text-white p-4 shadow-md flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-industry text-orange-500 text-xl"></i>
                <h1 class="text-xl font-bold">Workforce Platform MVP (Laravel 11 Backend)</h1>
            </div>
            <div v-if="role" class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-300">İnternet:</span>
                    <button @click="toggleOffline" :class="isOffline ? 'bg-red-500' : 'bg-green-500'" class="px-3 py-1 rounded text-xs font-bold transition">
                        @{{ isOffline ? 'OFFLINE (Sinyal Yok)' : 'ONLINE' }}
                    </button>
                </div>
                <div class="border-l border-slate-700 pl-4">
                    <span class="text-orange-400 font-semibold mr-3">@{{ roleDisplay }}</span>
                    <button @click="logout" class="text-sm bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded">Çıkış</button>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-auto p-6">
            
            <!-- LOGIN SCREEN -->
            <div v-if="!role" class="max-w-md mx-auto mt-20 bg-white p-8 rounded-xl shadow-lg border border-slate-200">
                <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">Sisteme Giriş</h2>
                <div class="space-y-4">
                    <button @click="login('tech')" class="w-full p-4 border border-blue-200 bg-blue-50 rounded-lg flex items-center gap-4 transition">
                        <div class="bg-blue-500 text-white p-3 rounded-full"><i class="fa-solid fa-laptop-code"></i></div>
                        <div class="text-left"><p class="font-bold">Technical Office</p><p class="text-xs">Plan Oluştur (T-1)</p></div>
                    </button>
                    <button @click="login('hom')" class="w-full p-4 border border-orange-200 bg-orange-50 rounded-lg flex items-center gap-4 transition">
                        <div class="bg-orange-500 text-white p-3 rounded-full"><i class="fa-solid fa-hard-hat"></i></div>
                        <div class="text-left"><p class="font-bold">Head of Master (Mobil)</p><p class="text-xs">Sahadan Veri Gir (T0)</p></div>
                    </button>
                    <button @click="login('sc')" class="w-full p-4 border border-emerald-200 bg-emerald-50 rounded-lg flex items-center gap-4 transition">
                        <div class="bg-emerald-500 text-white p-3 rounded-full"><i class="fa-solid fa-clipboard-check"></i></div>
                        <div class="text-left"><p class="font-bold">Site Chief / PM</p><p class="text-xs">Rapor ve Onay</p></div>
                    </button>
                </div>
            </div>

            <!-- TECH OFFICE -->
            <div v-if="role === 'tech'" class="max-w-6xl mx-auto grid grid-cols-3 gap-6">
                <div class="col-span-1 bg-white p-6 rounded-xl shadow border">
                    <h3 class="font-bold text-slate-700 mb-4 border-b pb-2">Yeni Günlük Plan Ata</h3>
                    <form @submit.prevent="createPlan" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold mb-1">Tarih</label>
                            <input type="date" v-model="newPlan.date" required class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1">Lokasyon</label>
                            <select v-model="newPlan.kkk" required class="w-full p-2 border rounded">
                                <option value="30AAA">30AAA</option><option value="30DDD">30DDD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1">WBS / ZZZ</label>
                            <select v-model="newPlan.zzz_code" required class="w-full p-2 border rounded text-xs">
                                <option v-for="w in wbsData" :value="w.zzz_code">@{{ w.zzz_code }} - @{{ w.tow }}/@{{ w.sstow }} (@{{ w.unit }})</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-xs font-bold mb-1">Miktar</label><input type="number" step="0.01" v-model="newPlan.planned_qty" required class="w-full p-2 border rounded"></div>
                            <div><label class="block text-xs font-bold mb-1">Manday</label><input type="number" v-model="newPlan.planned_manday" required class="w-full p-2 border rounded"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1">Atanacak HoM</label>
                            <select v-model="newPlan.hom" required class="w-full p-2 border rounded">
                                <option value="30DDD-hom@icn.com">30DDD-hom@icn.com</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded">Kaydet ve Ata</button>
                    </form>
                </div>
                <div class="col-span-2 bg-white p-6 rounded-xl shadow border">
                    <h3 class="font-bold text-slate-700 mb-4 border-b pb-2">Aktif Planlar (Tüm Sistem)</h3>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-100"><tr><th class="p-2">ID</th><th class="p-2">Tarih</th><th class="p-2">ZZZ</th><th class="p-2">Plan</th><th class="p-2">HoM</th><th class="p-2">Durum</th></tr></thead>
                        <tbody>
                            <tr v-for="p in plans" :key="p.id" class="border-b">
                                <td class="p-2 text-xs">#@{{ p.id }}</td><td class="p-2">@{{ p.report_date }}</td><td class="p-2 font-bold">@{{ p.zzz_code }}</td>
                                <td class="p-2">@{{ p.planned_qty }}</td><td class="p-2">@{{ p.assigned_hom }}</td>
                                <td class="p-2"><span class="px-2 py-1 text-[10px] font-bold rounded-full" :class="statusColor(p.status)">@{{ p.status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- HEAD OF MASTER (MOBILE) -->
            <div v-if="role === 'hom'">
                <div class="mobile-frame bg-slate-50">
                    <div v-if="isOffline" class="offline-banner">OFFLINE - Lokal DB Devrede</div>
                    <div class="p-4 bg-orange-500 text-white"><h3 class="font-bold">HoM İş Listesi</h3></div>
                    <div class="p-4 space-y-4">
                        <div v-if="pendingSync.length > 0" class="bg-amber-100 p-3 rounded-lg border border-amber-200">
                            <p class="text-xs text-amber-700 font-bold"><i class="fa-solid fa-cloud-arrow-up"></i> İnternet Bekleyen Veri: @{{ pendingSync.length }}</p>
                        </div>
                        <div v-for="p in homPlans" :key="p.id" class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                            <p class="text-xs text-slate-400 font-mono">ZZZ: @{{ p.zzz_code }}</p>
                            <p class="text-sm font-bold">@{{ getTow(p.zzz_code) }}</p>
                            <div class="bg-slate-50 p-2 rounded mt-2 text-xs flex justify-between">
                                <span>Hedef:</span><span class="font-bold">@{{ p.planned_qty }} @{{ getUnit(p.zzz_code) }}</span>
                            </div>
                            <button @click="openFactModal(p)" class="w-full mt-3 bg-slate-900 text-white text-sm py-2 rounded-lg">Veri Gir (T0)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SITE CHIEF DASHBOARD -->
            <div v-if="role === 'sc'" class="max-w-6xl mx-auto">
                <div class="bg-white rounded-xl shadow border overflow-hidden mb-8">
                    <div class="bg-slate-50 p-4 border-b"><h3 class="font-bold">Onay Bekleyenler (Pending)</h3></div>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-white"><tr><th class="p-3">ZZZ</th><th class="p-3">Plan</th><th class="p-3">Fact</th><th class="p-3">Açıklama</th><th class="p-3 text-right">Aksiyon</th></tr></thead>
                        <tbody>
                            <tr v-for="p in pendingPlans" :key="p.id" class="border-t">
                                <td class="p-3 font-bold">@{{ p.zzz_code }}</td>
                                <td class="p-3">@{{ p.planned_qty }}</td>
                                <td class="p-3 bg-orange-50"><span class="font-bold" :class="p.fact?.fact_qty == -1 ? 'text-red-500' : ''">@{{ p.fact?.fact_qty == -1 ? 'BAŞLAMADI' : p.fact?.fact_qty }}</span></td>
                                <td class="p-3 text-xs">@{{ p.fact?.comment || '-' }}</td>
                                <td class="p-3 text-right"><button @click="approve(p.id)" class="bg-emerald-500 text-white px-3 py-1 rounded text-xs font-bold">Onayla</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>

        <!-- FACT MODAL -->
        <div v-if="activeFactModal" class="fixed inset-0 bg-slate-900/80 flex items-center justify-center z-50">
            <div class="bg-white w-[350px] rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-slate-100 p-4 flex justify-between"><h3 class="font-bold">Fact Girişi</h3><button @click="activeFactModal = null"><i class="fa-solid fa-xmark"></i></button></div>
                <div class="p-5 space-y-4">
                    <form @submit.prevent="submitFact">
                        <div class="mb-3"><label class="block text-xs font-bold mb-1">Fact Qty (-1 iptal demektir)</label><input type="number" step="0.01" v-model="factForm.fact_qty" required class="w-full p-2 border rounded"></div>
                        <div class="mb-4"><label class="block text-xs font-bold mb-1">Açıklama</label><textarea v-model="factForm.comment" :required="factForm.fact_qty == -1" class="w-full p-2 border rounded"></textarea></div>
                        <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg">Kaydet</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        const { createApp } = Vue;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const api = async (url, method = 'GET', body = null) => {
            const options = {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            };
            if(body) options.body = JSON.stringify(body);
            const res = await fetch(url, options);
            return await res.json();
        };

        createApp({
            data() {
                return {
                    role: null,
                    isOffline: false,
                    wbsData: [],
                    plans: [],
                    pendingSync: JSON.parse(localStorage.getItem('pendingFacts') || '[]'),
                    newPlan: { date: new Date().toISOString().split('T')[0], kkk: '30DDD', zzz_code: '', planned_qty: '', planned_manday: '', hom: '30DDD-hom@icn.com' },
                    activeFactModal: null,
                    factForm: { fact_qty: '', overtime: 0, crew_type: 'Rebar Fixer', comment: '' }
                }
            },
            mounted() {
                this.loadData();
            },
            computed: {
                roleDisplay() { return this.role === 'tech' ? 'Technical Office' : (this.role === 'hom' ? 'Head of Master' : 'Site Chief'); },
                homPlans() { return this.plans.filter(p => p.status === 'ASSIGNED'); },
                pendingPlans() { return this.plans.filter(p => p.status === 'PENDING_SC'); }
            },
            methods: {
                async loadData() {
                    this.wbsData = await api('/api/wbs');
                    let url = '/api/plans';
                    if (this.role === 'hom') url += '?role=hom&email=30DDD-hom@icn.com';
                    this.plans = await api(url);
                    if(this.wbsData.length > 0) this.newPlan.zzz_code = this.wbsData[0].zzz_code;
                },
                login(r) { this.role = r; this.loadData(); },
                logout() { this.role = null; },
                async toggleOffline() { 
                    this.isOffline = !this.isOffline; 
                    if(!this.isOffline && this.pendingSync.length > 0) {
                        await api('/api/facts/sync', 'POST', { facts: this.pendingSync });
                        this.pendingSync = [];
                        localStorage.removeItem('pendingFacts');
                        this.loadData();
                        alert("Offline kayıtlar sunucuya gönderildi!");
                    }
                },
                getUnit(zzz) { return this.wbsData.find(w => w.zzz_code === zzz)?.unit || '-'; },
                getTow(zzz) { return this.wbsData.find(w => w.zzz_code === zzz)?.tow || ''; },
                statusColor(status) {
                    return status==='ASSIGNED' ? 'bg-blue-100' : (status==='PENDING_SC' ? 'bg-orange-100' : 'bg-emerald-100');
                },
                async createPlan() {
                    await api('/api/plans', 'POST', this.newPlan);
                    this.newPlan.planned_qty = ''; this.newPlan.planned_manday = '';
                    this.loadData();
                },
                openFactModal(plan) {
                    this.activeFactModal = plan;
                    this.factForm = { fact_qty: '', overtime: 0, crew_type: 'Rebar Fixer', comment: '' };
                },
                async submitFact() {
                    const factPayload = {
                        plan_id: this.activeFactModal.id,
                        fact_qty: this.factForm.fact_qty,
                        overtime: this.factForm.overtime,
                        crew_type: this.factForm.crew_type,
                        comment: this.factForm.comment,
                        local_id: crypto.randomUUID()
                    };

                    if(this.isOffline) {
                        this.pendingSync.push(factPayload);
                        localStorage.setItem('pendingFacts', JSON.stringify(this.pendingSync));
                        this.plans = this.plans.filter(p => p.id !== factPayload.plan_id); // Remove from assigned
                    } else {
                        await api('/api/facts/sync', 'POST', { facts: [factPayload] });
                        this.loadData();
                    }
                    this.activeFactModal = null;
                },
                async approve(id) {
                    await api(`/api/plans/${id}/approve`, 'POST');
                    this.loadData();
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
