<template>
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 md:p-8 shadow-sm space-y-8">
        <!-- Title Header -->
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <span>📖</span> Uygulama Kurgusu & Kullanım Kılavuzu
            </h2>
            <p class="text-xs text-slate-500 mt-1">Mülakat Değerlendirme Ekibi İçin Saha İş Akışları ve Özellikler Özeti</p>
        </div>

        <!-- Guide Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-slate-700 text-xs md:text-sm leading-relaxed font-medium">
            <!-- Left Column: Business Flow -->
            <div class="space-y-6">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">🔄 Saha Görev Döngüsü Kurgusu</h3>
                
                <div class="space-y-4">
                    <div class="relative flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px]">1</div>
                        <div>
                            <strong class="text-slate-800 block text-xs font-bold mb-0.5">Planlayıcı Görevi Oluşturur</strong>
                            Planlayıcı (John Planner) rolündeyken **"Görevler"** sayfasında *"Yeni Görev Ekle"* butonuna basıp bir iş emri oluşturur ve saha çalışanına atar. Görev ilk olarak **"Bekliyor (Pending)"** durumunda başlar.
                        </div>
                    </div>
                    
                    <div class="relative flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-[10px]">2</div>
                        <div>
                            <strong class="text-slate-800 block text-xs font-bold mb-0.5">Çalışan Görevi Başlatır</strong>
                            Çalışan (Bob Worker) rolüne geçilip görev detay modalı açıldığında çalışan **"Çalışmayı Başlat"** butonuna basarak görevi **"İşlemde (In Progress)"** durumuna getirir.
                        </div>
                    </div>

                    <div class="relative flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-violet-100 text-violet-750 flex items-center justify-center font-bold text-[10px]">3</div>
                        <div>
                            <strong class="text-slate-800 block text-xs font-bold mb-0.5">Çalışan Onaya Gönderir</strong>
                            İşlem bittiğinde çalışan detay modalında raporunu yazarak **"Tamamlandı - Onaya Gönder"** butonuna basar. Görev **"Onay Bekliyor (Waiting Approval)"** durumuna çekilir ve yöneticilere bildirim gider.
                        </div>
                    </div>

                    <div class="relative flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]">4</div>
                        <div>
                            <strong class="text-slate-800 block text-xs font-bold mb-0.5">Yönetici Görevi Kapatır / Revizyon İster</strong>
                            Yönetici (Alice Manager) rolüne geçilip görev açıldığında yönetici raporu inceler. **"Göreve Onay Ver"** diyerek görevi kapatabilir (**Onaylandı/Approved**) veya açıklama yazıp **"Reddet"** diyerek revizyona geri gönderebilir (**Reddedildi/Rejected**).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Key Technical Features -->
            <div class="space-y-6">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">⚙️ Test Edilebilir Kritik Özellikler</h3>
                
                <div class="space-y-4">
                    <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl">
                        <strong class="text-slate-850 block text-xs font-bold mb-1">📶 Çevrimdışı (Offline-First) Senkronizasyon Testi</strong>
                        1. Sağ üstteki **"Simüle Çevrimdışı Yap"** butonuna basın.<br>
                        2. Görev atayın veya durum güncelleyin. Arayüzün yerel IndexedDB önbelleği sayesinde anında güncellendiğini görün (Optimistic UI).<br>
                        3. Sol alt widget'ta kaç işlemin sırda biriktiğini takip edin.<br>
                        4. **"Simüle Çevrimiçi Yap"** butonuna basın. Arka plandaki `SyncManager` sıradaki işleri otomatik olarak sunucuya yazar ve veritabanıyla tam eşitlenir.
                    </div>

                    <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl">
                        <strong class="text-slate-850 block text-xs font-bold mb-1">🤖 AI Copilot (Yapay Zeka) Testi</strong>
                        Yapay zeka asistanımız şantiyedeki anlık duruma reaktif yanıtlar verir. İnternet kesilse bile yerel AI kurallar motoru devreye girerek *"Hangi görevler gecikti?"* veya *"Onay bekleyen ne var?"* sorularına internet bağımsız yerel SQLite verileriyle nokta atışı cevap verir.
                    </div>

                    <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl">
                        <strong class="text-slate-850 block text-xs font-bold mb-1">📊 Darboğaz & Analitik Analizi</strong>
                        Analiz sayfasında 24 saatten uzun süredir onay kuyruğunda bekleyen veya süresi geçen (Gecikmiş) işlerin detayları, şantiyenin genel ilerleme ve kabul oranları grafiksel olarak raporlanır.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/authStore';
const authStore = useAuthStore();
</script>
