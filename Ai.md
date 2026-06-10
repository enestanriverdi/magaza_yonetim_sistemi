
**Soru/Prompt :** Veritabanı mimarisini kurmam lazım. DTF baskı siparişleri için PHP ve PDO kullanacağım. Kullanıcılar ve siparişler tablosunda hangi veri tiplerini kullanmalıyım?

**Cevap :** Güvenlik için şifreleri veritabanında düz metin yerine `hash` ile tutmalı, sipariş durumları için ise (Beklemede, Baskıda, Kargolandı vb.) `ENUM` veri tipi kullanmalısın. Ayrıca veritabanı bağlantını SQL Injection'a karşı korumak için PDO'nun Prepared Statements yapısını kullanacağız.

---

**Soru/Prompt :** Sayfada yüzlerce sipariş alt alta çok uzun oldu, sayfalama yaptım. Bir de bu listeyi Excel'e indirme butonu koyalım ama Excel'de Türkçe karakterler (ş, ç, ğ) bozulmasın.

**Cevap :** Excel çıktısı için ağır bir kütüphane kurmana gerek yok; salt PHP ile arka plana bir `UTF-8 BOM` (`\xEF\xBB\xBF`) damgası basarak Türkçe karakter sorununu tamamen çözebiliriz.
```
<?php
require_once 'config/db.php';

// Tarayıcıya dosya indireceğimizi bildiren HTTP Header Ayarları
$dosya_adi = "dtf_siparis_listesi_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $dosya_adi . '";');

// KRİTİK DETAY: Excel'in Türkçe karakterleri doğru tanıması için UTF-8 BOM damgası basıyoruz
echo "\\xEF\\xBB\\xBF";

// Çıktı akışını açıp verileri satır satır yazıyoruz
$output = fopen('php://output', 'w');

// Başlık satırı (Ayrıcı olarak ';' kullanıyoruz)
fputcsv($output, ['Sipariş ID', 'Ürün Adı', 'Baskı Tasarımı', 'Adet', 'Tutar (TL)', 'Durum'], ';');

// Veritabanından gelen kayıtları döngüyle Excel'e basma
foreach ($kayitlar as $row) {
    fputcsv($output, [
        $row['id'],
        $row['urun_adi'],
        $row['baski_tasarimi'],
        $row['adet'],
        $row['fiyat'],
        $row['durum']
    ], ';');
}
fclose($output);
exit;
?>
```
---

**Soru/Prompt :** Sistemin güvenliğini arttırmak istiyorum. Toplam ciroyu ve "Sil" butonlarını personeller görmesin. Ayrıca dışarıdan üye olanlar, ben (Admin) onaylamadan içeri giremesin. Nasıl fikir?

**Cevap :** Bu çok profesyonel bir yaklaşım! Veritabanına `rol` (Admin/Personel) ve `onay_durumu` (Bekliyor/Onaylandı) sütunlarını ekleyelim. Giriş yapan kullanıcının rolünü `$_SESSION` (oturum) hafızasına alıp, if-else bloklarıyla arayüzdeki kritik butonları ve ciro kartlarını personellerden gizleyelim.

---

**Soru/Prompt :** Arayüz tasarımında yenilikler yaptım ama yeni tasarıma geçerken bazı hatalar alıyorum. Ekranda `Undefined array key "rol"` ve `Unknown column 'kayit_tarihi'` gibi uyarılar çıkıyor, giriş yapamıyorum.

**Cevap :** İlk hatanın sebebi tarayıcında askıda kalan eski çerezler. Sistemin seni hatırlaması için `logout.php` üzerinden eski oturumu temizlemelisin. İkinci hata ise Ekip Yönetimi sayfasını yaparken tabloya kayıt tarihi sütununu eklemeyi unutmuşsun. Hemen phpMyAdmin'den ufak bir `ALTER TABLE` sorgusu çalıştırarak o eksik sütunu ekle.

Çözüm SQL Sorgusu:
```ALTER TABLE kullanicilar ADD kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP;```

---

**Soru/Prompt :** İlk attığım kodda index.php çok kalabalıktı; filtreler, sayfalamalar, upuzun bir tablo hepsi oradaydı. Sonra bu tabloyu siparisler.php'ye taşıdık ve index.php'yi sadece bir "Dashboard" yaptık. Bunun yazılım mimarisindeki mantığı nedir?

**Cevap :** Bu mimari adım, UX (Kullanıcı Deneyimi) ve sistem performansı için çok kritikti. Bir Yönetici (Admin) sisteme girdiğinde ilk görmek istediği şey uzun bir veri yığını değil; "Toplam kaç paramız var?", "Kaç iş bekliyor?" gibi özet istatistiklerdir. Bu yüzden ağır SQL sorgularını (filtreleme vb.) siparisler.php operasyon merkezine devrettik. index.php'yi ise sadece SUM() ve COUNT() SQL fonksiyonlarıyla çalışan, sisteme anında yüklenen hızlı bir kokpit haline getirdik.

---

**Soru/Prompt :** Veritabanı bağlantısında SQL Injection saldırılarını PDO ile engelledik. Peki ya kötü niyetli bir kullanıcı, sipariş eklerken "Ürün Adı" kısmına HTML veya zararlı bir JavaScript kodu yazarsa? Paneli patlatmasını nasıl engelleriz?

**Cevap :** Harika bir güvenlik tespiti! Buna XSS (Cross-Site Scripting) saldırısı denir. Birisi form alanına <script>alert('Hacklendin');</script> yazarsa, o sipariş tablolarda listelendiği an tarayıcı o kodu çalıştırır. Bunu engellemek için veritabanından çektiğimiz ve ekrana bastığımız tüm metin verilerini htmlspecialchars() fonksiyonundan geçirdik. Bu fonksiyon, zararlı HTML etiketlerini zararsız metin karakterlerine dönüştürür.

Geliştirilen Güvenlik Kod Bloğu:

```php
<tr>
    <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?= htmlspecialchars($row['urun_adi'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?= htmlspecialchars($row['baski_tasarimi'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?= htmlspecialchars($row['adet'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td>
        <span class="badge bg-success">
            <?= htmlspecialchars($row['durum'], ENT_QUOTES, 'UTF-8'); ?>
        </span>
    </td>
</tr>
```
---
