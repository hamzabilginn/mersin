# MÜLAKAT SUNUM METNİ (SPEECH SCRIPT)

**To the Implementation AI Agent:** I have drafted the overarching speech for the user. Please review Part 2 (The MVP section) and feel free to add any technical details you implemented regarding the Vue frontend or the database schemas if you think it will help the user shine.

---

Sevgili Mustafa Hazar Bilgin, bu belge mülakatta jüri karşısında kelimesi kelimesine kullanabileceğin, seni anında "Kıdemli Mimar (Senior Architect)" konumuna yükseltecek sunum notlarıdır. 

## BÖLÜM 1: Kurumsal Mimari Sunumu (`yeni.pdf`)
*(Bu bölümü PDF'i ekrana yansıttığında anlatacaksın)*

**Giriş:**
"Değerli jüri üyeleri, merhabalar. Bana ilettiğiniz Workforce Execution Platform senaryosunu detaylıca inceledim. Gördüğüm kadarıyla buradaki temel problem sadece verileri dijitalleştirmek değil; **nükleer tesis standartlarında güvenlik gerektiren, devasa bir şantiyede internet koptuğunda bile veri kaybı yaşatmayacak ve on binlerce personelin günlük faaliyetini (ZZZ kodlarını) çökmeden işleyecek bir Kurumsal Mimari (Enterprise Architecture)** kurmaktır."

**Mimari Kararlar:**
"Bu yüzden sistemi Monolitik değil, Mikroservis mimarisine uygun bir N-Tier (Katmanlı) yapıda tasarladım. 
- **Backend:** Kurumsal entegrasyon gücü (SAP/ERP uyumluluğu) ve asenkron işlem kapasitesi için **.NET Core 8** seçtim.
- **Veritabanı:** Excel'deki WBS (ToW->SToW->SSToW) ağaç yapısını en hızlı şekilde sorgulayabilmek (Recursive CTE) ve doğrudan Power BI'a 'Native DirectQuery' ile bağlayabilmek için **PostgreSQL** tercih ettim.
- **Onay Akışları (Workflow):** Tech Office, Head of Master ve Site Chief arasındaki karmaşık onay zincirini yönetmek için, sistemin kilitlenmesini önleyecek Event-Driven bir yapı olan **RabbitMQ ve MassTransit** kullandım.
- **Güvenlik ve Deployment:** Tesisin güvenlik regülasyonları gereği veriyi dış buluta (AWS/Azure) asla çıkarmıyoruz. Tamamen On-Premise (İç ağ) sunucularda çalışan **Docker** mimarisi tasarladım."

**Geçiş:**
"Bu anlattıklarım sistemin nihai, ölçeklenebilir ve güvenli 'Target (Hedef) Mimarisi'dir. Ancak bir mimar olarak, kağıt üzerindeki tasarımların sahada çalışabilirliğini kanıtlamak benim görevim. Bu yüzden senaryodaki en zorlu iki iş kuralını kanıtlamak için sizlere bir de Canlı Prototip (MVP) hazırladım."

---

## BÖLÜM 2: Canlı Prototip / MVP Sunumu (Laravel & Vue Projesi)
*(Bu bölümde kodu veya çalışan uygulamayı ekrana vereceksin)*

**MVP'nin Amacı:**
"Mimarinin en büyük iki riski şuydu: Birincisi şantiyede internetin kesilmesi (Offline çalışma). İkincisi ise Head of Master'ın işe başlamadığı durumda (`fact_qty = -1`) sistemin zorunlu yorum (comment) istemesi. Hızlıca konsepti kanıtlamak (Proof of Concept) adına bu canlı MVP'yi kodladım."

**Demo Adımları:**
1. **ZZZ Kodu ve Hiyerarşi:** "Ekranda gördüğünüz gibi, Excel'deki WBS hiyerarşisine tam uyumlu olarak ZZZ kodları sistemde dinamik eşleşiyor."
2. **Kritik İş Kuralı (-1 Testi):** "İş kurallarına göre eğer `fact_qty` kısmına -1 (İş Başlamadı) girersem, sistemin kaydı reddettiğini ve beni 'Yorum' girmeye zorladığını görebilirsiniz. (Bunu ekranda canlı göster)."
3. **Offline Senkronizasyon (Idempotency):** "Şantiyede internet koptuğunda, uygulama lokal veritabanına veri yazar. İnternet geldiğinde ise veriler sunucuya gönderilirken bir `Idempotency-Key` (tekil anahtar) kullanılır. Böylece zayıf bağlantılarda aynı verinin veritabanına 2 kez (mükerrer) yazılması engellenmiş olur."

**Kapanış:**
"Özetle; kağıt üzerinde kusursuz, nükleer standartlara uygun bir kurumsal mimari tasarladım ve bu mimarinin sahadaki en büyük darboğazlarını kodlayarak kanıtladım. Dinlediğiniz için teşekkür ederim, teknik veya mimari her türlü sorunuzu yanıtlamaktan memnuniyet duyarım."
