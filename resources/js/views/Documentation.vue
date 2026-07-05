<template>
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 md:p-8 shadow-sm space-y-8">
        <!-- Title Header -->
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <span>📄</span> Teknik Mimari Rapor
            </h2>
            <p class="text-xs text-slate-500 mt-1">FieldFlow Saha Görev Yönetimi ve Otomasyon Sistemi Mimarisi</p>
        </div>

        <!-- Document Content -->
        <div class="space-y-6 text-slate-700 text-xs md:text-sm leading-relaxed max-w-4xl font-medium">

            <!-- MVP & Case Study Note -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
                <h3 class="text-blue-800 font-bold flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Mülakat & Case Study Bilgilendirmesi (MVP Prototype)
                </h3>
                <p class="text-blue-700 text-xs leading-relaxed mb-3">
                    Bu proje deposunda (repository) bulunan <strong>Laravel & Vue SPA</strong> kod tabanı, kısa sürede hızlıca geliştirilmiş bir <strong>Konsept Kanıtlama (Proof of Concept / MVP)</strong> uygulamasıdır. Amacı, çevrimdışı (Offline-First) veri eşitleme mantığını, kullanıcı deneyimini (UX) ve yapay zeka (AI) asistanının görev onay süreçlerine entegrasyonunu görsel olarak canlı simüle etmektir.
                </p>
                <p class="text-blue-700 text-xs leading-relaxed">
                    <strong>Hedeflenen Kurumsal Mimari:</strong> Gerçek üretim (Production) ortamı için planlanan mimari çok daha ölçeklenebilir bir yapı olan <strong>.NET Core 8 (Mikroservis / API Gateway), React + TypeScript (Tech Office Web) ve Flutter (Mobil Saha App)</strong> teknolojilerine dayanmaktadır. İlgili tam kapsamlı sistem mimarisi ve veritabanı şemaları (ER Diagram, WBS Hiyerarşisi), proje klasörü içerisine eklenen <code>case_study/</code> dizinindeki <strong>yeni.pdf (Sunum)</strong> ve diğer Case Study dokümanlarında detaylandırılmıştır.
                </p>
            </div>
            
            <section class="space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">1. Giriş ve Hedefler</h3>
                <p>
                    Bu proje (MVP), yüksek güvenlikli ve dış dünyaya kapalı (<strong>Air-Gapped / Intranet</strong>) şantiye sahalarında, saha personeli (çalışanlar, planlayıcılar ve yöneticiler) arasındaki iş emirlerini ve iş akışı onay mekanizmalarını otomatikleştirmek amacıyla simüle edilmiş <strong>Offline-First (Çevrimdışı Öncelikli)</strong> bir kurumsal web uygulamasıdır.
                </p>
            </section>

            <section class="space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">2. Teknoloji Yığını (Tech Stack)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-slate-150 rounded-xl overflow-hidden mt-2 text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <th class="p-3 border border-slate-200">Katman</th>
                                <th class="p-3 border border-slate-200">Teknoloji</th>
                                <th class="p-3 border border-slate-200">Seçim Gerekçesi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 border border-slate-150">Backend</td>
                                <td class="p-3 border border-slate-150">Laravel 11 (PHP 8.3)</td>
                                <td class="p-3 text-slate-500 border border-slate-150">Modüler MVC yapısı, güçlü Migration yetenekleri, dahili Event/Listener sistemi ve kurumsal standartlara uygunluğu.</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 border border-slate-150">Frontend</td>
                                <td class="p-3 border border-slate-150">Vue 3 SPA (Single Page Application)</td>
                                <td class="p-3 text-slate-500 border border-slate-150">İstemci tarafında (Client-Side) sayfa geçişleri sağlayarak uygulamanın internet koptuğunda dahi beyaz sayfa hatası vermeden çalışabilmesi.</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 border border-slate-150">Rota & Durum</td>
                                <td class="p-3 border border-slate-150">Vue Router (Hash History) + Pinia</td>
                                <td class="p-3 text-slate-500 border border-slate-150">Sunucu bağımsız çevrimdışı sayfa yönlendirmesi yapmak ve reaktif durum yönetimini merkezi hale getirmek.</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 border border-slate-150">Stil & Tasarım</td>
                                <td class="p-3 border border-slate-150">TailwindCSS (v4)</td>
                                <td class="p-3 text-slate-500 border border-slate-150">Modern, hızlı ve son derece özelleştirilebilir arayüz öğeleri üretmek.</td>
                            </tr>
                            <tr class="border-b border-slate-100">
                                <td class="p-3 font-bold text-slate-800 border border-slate-150">Yerel Veri Önbelleği</td>
                                <td class="p-3 border border-slate-150">IndexedDB (Native API)</td>
                                <td class="p-3 text-slate-500 border border-slate-150">Çevrimdışı durumda görevlerin, mesajların ve giden kuyruğunun yüksek kapasiteli depolanması.</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-slate-800 border border-slate-150">Veritabanı</td>
                                <td class="p-3 border border-slate-150">SQLite (Lokal)</td>
                                <td class="p-3 text-slate-500 border border-slate-150">Şantiye içi intranet sunucularında veya yerel testlerde sıfır kurulum gerektiren, dosya tabanlı ilişkisel veritabanı.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">3. Yazılım Mimarlık Desenleri (Design Patterns)</h3>
                
                <div class="space-y-3 mt-3">
                    <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl">
                        <strong class="text-slate-800 block text-xs font-bold mb-1">Service Layer Pattern (İş Mantığı Katmanı)</strong>
                        Veritabanı yazma ve iş kuralları denetleme mantığı kontrolcülerden (Controllers) arındırılarak <code>App\Services\TaskService</code> içerisine taşınmıştır. Kontrolcüler sadece HTTP isteklerini almak ve API kaynaklarını döndürmekle yükümlüdür.
                    </div>
                    
                    <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl">
                        <strong class="text-slate-800 block text-xs font-bold mb-1">Event-Driven Architecture (Olay Odaklı Mimari)</strong>
                        Görevlerin durumu değiştiğinde <code>TaskStatusChanged</code> adında bir event tetiklenir. Bu olay, sistemin diğer süreçlerinden tamamen soyutlanmış iki dinleyici (Listener) tarafından işlenir:
                        <ul class="list-disc pl-5 mt-2 space-y-1 text-xs">
                            <li><strong>WriteTaskAuditLog</strong>: Görevin geçmiş durumlarını, değişiklik zamanını ve yorumlarını <code>task_logs</code> tablosuna bağımsız bir işlem olarak yazar (Denetim İzi / Audit Trail).</li>
                            <li><strong>SendTaskNotification</strong>: Değişikliği yapan ve etkilenen rollerin durumuna göre gerçek zamanlı bildirim kayıtları üretir.</li>
                        </ul>
                    </div>
                    
                    <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl">
                        <strong class="text-slate-800 block text-xs font-bold mb-1">Data Transfer & Validation (Form Requests & Resources)</strong>
                        <ul class="list-disc pl-5 space-y-1 text-xs">
                            <li><strong>Form Requests</strong>: <code>StoreTaskRequest</code> ve <code>UpdateTaskStatusRequest</code> ile veriler denetleyiciye ulaşmadan önce doğrulanır ve yetki kontrolünden geçer.</li>
                            <li><strong>API Resources</strong>: <code>TaskResource</code>, <code>UserResource</code> ve <code>MessageResource</code> ile veritabanı modellerinin JSON çıktı yapıları kapsüllenir.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">4. Çevrimdışı Çalışma (Offline-First) ve Eşitleme Akışı</h3>
                <p>
                    Sistem, bağlantı durumunu reaktif olarak izler. Tarayıcının <code>navigator.onLine</code> özelliği ve arayüzdeki simülasyon butonu ile çevrimdışı durum yönetilir.
                </p>
                <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl space-y-2">
                    <p><strong>1. İnternet Varken (Online):</strong> Tüm işlemler anında Laravel API'sine iletilir ve başarılı yanıt dönünce yerel IndexedDB güncellenir.</p>
                    <p><strong>2. İnternet Yokken (Offline):</strong> İşlemler <code>SyncManager</code> tarafından yakalanıp IndexedDB içindeki <code>sync_queue</code> (Giden Kutusu) tablosuna kaydedilir. Ön yüz <strong>Optimistic UI (İyimser Ön Yüz Güncellemesi)</strong> ile anında güncellenir.</p>
                    <p><strong>3. Bağlantı Geri Geldiğinde (Reconnect):</strong> Giden kutusundaki işlemler sırayla (FIFO) Laravel API'sine post edilir. İşlemler başarıyla sunucuya yazıldıktan sonra en güncel durum çekilir ve yerel veriler eşitlenir.</p>
                </div>
            </section>

            <section class="space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">5. Yapay Zeka (AI Copilot) ve Bağlam Yönetimi</h3>
                <p>
                    Sistemde bulunan yapay zeka asistanı, şantiye şefinin sahadaki durumları analiz etmesine yardımcı olur.
                </p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Bağlam Ekleme (Prompt Injection):</strong> Kullanıcı yapay zekaya soru sorduğunda, arka planda o anki veritabanından toplam görev sayıları, geciken görevlerin listesi, onay kuyruğunda bekleyen işler ve aktif ekip listesi toplanır ve Gemini API'ye bağlam olarak iletilir.</li>
                    <li><strong>Gemini API & Çevrimdışı Fallback:</strong> Çevrimiçi durumda Google Gemini 2.5 Flash modeliyle konuşulur. Cihaz çevrimdışı olduğunda ise backend içindeki <strong>Rules-based Offline AI Engine</strong> devreye girer ve yerel verileri tarayarak soruları internet olmasa dahi cevaplandırır.</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h3 class="text-sm font-bold text-slate-800 border-l-4 border-amber-500 pl-2">6. Gelişmiş Saha İletişimi, Sayfalandırma (Infinite Scroll) ve Kalıcılık</h3>
                <p>
                    Saha ekibi arasındaki koordinasyonu artırmak ve veri yükünü optimize etmek amacıyla sohbet sistemi genişletilmiştir:
                </p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Özel Mesajlaşma (DM - Direct Messaging):</strong> Saha çalışanları artık genel kanal (#general) dışında şantiye içerisindeki diğer kullanıcılarla birebir, izole özel sohbetler (DMs) başlatabilmektedir.</li>
                    <li><strong>Sonsuz Kaydırma ile Sayfalandırma (Infinite Scroll Pagination):</strong> Performans optimizasyonu için tüm sohbet geçmişi tek seferde yüklenmez. Mesajlar 20'şerli paketler halinde yüklenir; kullanıcı geçmişe doğru yukarı kaydırdıkça (scroll-up) eski mesajlar sırayla çekilir ve performansı bozmadan sohbet penceresine eklenir.</li>
                    <li><strong>Kullanıcı Bazlı Chatbot Kalıcılığı:</strong> Her kullanıcının yapay zeka asistanı (AI Copilot) ile yaptığı mesajlaşmalar tamamen bağımsızdır ve tarayıcı yenilense dahi IndexedDB (<code>ai_messages</code>) deposunda <code>user_id</code> bazlı filtrelenerek kalıcı olarak saklanır.</li>
                </ul>
            </section>
        </div>
    </div>
</template>

<script setup>
</script>
