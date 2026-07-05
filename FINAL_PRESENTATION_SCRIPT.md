# 🎤 CANLI MÜLAKAT SUNUM METNİ (TELEPROMPTER)

Bu belgeyi diğer bilgisayarından rahatça okuyabilirsin. Derin bir nefes al, harika bir sistem geliştirdik. Sen sadece bu akışı kendi cümlelerinle oku.

---

## BÖLÜM 1: PROJE DEMOSU (Canlı Uygulama Tanıtımı)

**[GİRİŞ]**
"Merhabalar, bana ilettiğiniz Workforce Execution Platform (Saha Gücü Otomasyonu) case study'sini detaylıca inceledim. Tüm iş kurallarını teoride bırakmak yerine, sahada gerçekten çalışabileceğini kanıtlamak için size canlı bir **MVP (Prototip)** geliştirdim. İzninizle önce sahanın nabzını tuttuğumuz bu uygulamayı, ardından da hedeflenen kurumsal mimarimi göstereceğim."

**[DASHBOARD & OFFLINE]** *(Durum Paneli sayfasını açın)*
"Sisteme girdiğimizde şantiyenin anlık durumunu görüyoruz. Geciken görevler ve onayda tıkanan işler yöneticinin önüne düşüyor. Şantiye ortamındaki internet sorunlarına karşı sistemi 'Offline-First' tasarladım. *(Sol alttan Çevrimdışı yap butonuna basın)*. Bakın internet kopsa bile sistem çökmez, lokal veritabanı (IndexedDB) devreye girer. İşçiler çalışmaya devam edebilir."

**[T-1 VE T0 İŞ AKIŞI]** *(Görevler sayfasına geçin)*
"Sizden gelen WBS (ZZZ Kodları) tablosunu birebir sisteme entegre ettim. Sistem şu akışla çalışıyor:
1. **Tech Office** T-1 gününde sisteme girip `60114402` ZZZ kodlu işi planlar (Planned Qty) ve bir ustaya (HoM) atar.
2. **HoM (Saha Elemanı)** T0 gününde sahaya iner. İşi yaparsa Gerçekleşen (Fact Qty) miktarını girer."

**[KRİTİK "-1" KURALI]** *(HoM rolündeyken bir göreve -1 girmeye çalışın)*
"Case Study'de benden istediğiniz o çok kritik kuralı sisteme gömdüm. Diyelim ki yağmur yağdı ve iş yapılamadı. HoM miktara `-1` yazdığı anda, sistem otomatik olarak 'Açıklama / Mazeret' girilmesini zorunlu kılar. Mazeretsiz boş geçemez! Sonrasında iş SC ve PM onay hiyerarşisine düşer."

**[YAPAY ZEKA - ACTION AGENT - BÜYÜK FİNAL]** *(AI Copilot sayfasını açın)*
"Sisteme sıradan bir chatbot yerine **'Ajan (Action Agent)'** mantığıyla çalışan bir yapay zeka entegre ettim. Elleri tozlu bir inşaat ustası veya ofisteki bir planlamacı tabletten menü aramak zorunda değil. *(Tech Office rolüne geçip şu metni AI'a yazın: 'Yarın sahadaki Ustaya 60114402 numaralı ZZZ işini atar mısın?')*. Gördüğünüz gibi AI cümlenin içinden ZZZ kodunu çekip veritabanında **kendi kendine işi oluşturuyor ve atamayı yapıyor.**"

---

## BÖLÜM 2: YENİ.PDF (Kurumsal Mimari Sunumu)

**[GEÇİŞ CÜMLESİ]** *(PDF dosyasını açarken)*
"Az önce gördüğünüz bu canlı sistem, şantiye kurallarını (-1 kuralı, offline yapı vs.) hızlıca test etmek (Proof of Concept) için geliştirdiğim çevik bir sistemdi. Şimdi ise, bu projenin 6 aylık üretim (Production) aşamasında dönüşeceği **Nihai Kurumsal Mimariyi (Target Architecture)** aktarmak istiyorum."

**[SAYFA: ÇÖZÜM MİMARİSİ & KATMANLI YAPI]**
"Sistemi monolitik bir yapıdan ziyade ölçeklenebilir Mikroservis (veya Katmanlı) bir yapıda kurguladım. Nükleer/İnşaat şantiyelerindeki binlerce verinin akışını yönetebilmek için API Gateway üzerinden güvenli haberleşme sağlanıyor."

**[SAYFA: VERİ MODELİ (ER DİAGRAM) & DDD]**
"Veritabanı tasarımını yaparken Domain-Driven Design (DDD) yaklaşımlarını temel aldım. Az önce tek ekranda gördüğümüz planlama verilerini kurumsal mimaride `WBS_Items` (İş paketleri), `Daily_Plans` (T-1 günleri) ve `Daily_Facts` (T0 sahadaki gerçekleşmeler) olmak üzere profesyonel tablolara böldüm."

**[SAYFA: OFFLINE SENKRONİZASYON VE GÜVENLİK]**
"Sahada internet koptuğunda girilen veriler, internet geldiğinde sunucuya yollanıyor. Burada veritabanında çift kayıt (duplicate) oluşmasını engellemek için `Idempotency-Key` mimarisi tasarladım. Güvenlik tarafında ise Role Based Access Control (RBAC) ile kimin neye onay vereceğini kesin çizgilerle ayırdım."

**[SAYFA: RAPORLAMA VE KAPANIŞ]**
"Son olarak üst düzey yöneticiler için doğrudan Power BI veya Tableau gibi sistemlerin veritabanından beslenerek Darboğaz Analizi yapabileceği bir veri yapısı kurdum.

*Vakit ayırdığınız için teşekkür ederim, benim teknik yaklaşımım ve mimari vizyonum bu şekildedir. Sorularınız varsa zevkle yanıtlayabilirim.*"

---
*(Başarılar dilerim, harika bir sunum olacak!)*
