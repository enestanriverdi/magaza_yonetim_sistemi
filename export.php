<?php
session_start();
require_once 'config/db.php';

// Güvenlik kontrolü: Giriş yapmamışsa işlemi durdur
if(!isset($_SESSION['kullanici_id'])){
    header("Location: login.php");
    exit;
}

// Ana sayfadaki filtrelerin aynısını buraya da uyguluyoruz
$arama = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$durum_filtre = isset($_GET['durum_filtre']) ? $_GET['durum_filtre'] : '';

$where_sql = " WHERE 1=1";
$params = [];

if ($arama !== '') {
    $where_sql .= " AND (t.urun_adi LIKE ? OR t.baski_tasarimi LIKE ? OR t.tedarikci_bilgisi LIKE ?)";
    $params[] = "%$arama%";
    $params[] = "%$arama%";
    $params[] = "%$arama%";
}

if ($durum_filtre !== '') {
    $where_sql .= " AND t.durum = ?";
    $params[] = $durum_filtre;
}

// Verileri çekme sorgusu
$sql = "SELECT t.id, t.urun_adi, t.kumas_turu, t.baski_tasarimi, t.adet, t.satis_kanali, t.fiyat, t.durum, t.sorumlu_kisi, t.tedarikci_bilgisi, t.guncelleme_tarihi 
        FROM tedarik_yonetimi t 
        INNER JOIN kullanicilar k ON t.kullanici_id = k.id " . 
        $where_sql . " ORDER BY t.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$kayitlar = $stmt->fetchAll();

// İndirilecek dosyanın adını dinamik yapalım (Örn: dtf_siparis_listesi_2026-06-09.csv)
$dosya_adi = "dtf_siparis_listesi_" . date('Y-m-d_H-i') . ".csv";

// Tarayıcıya dosya indireceğimizi bildiren HTTP Header Ayarları
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $dosya_adi . '";');

//Excel'in Türkçe karakterleri (ş, ç, ğ, ı, ö, ü) doğru tanıması için UTF-8 BOM damgası basıyoruz
echo "\xEF\xBB\xBF";

// Çıktı akışını açalım
$output = fopen('php://output', 'w');

// Excel Sütun Başlıkları (Türkiye Excel yapısı gereği ayrıcı olarak ';' kullanıyoruz)
fputcsv($output, ['Sipariş ID', 'Ürün Adı', 'Kumaş Türü', 'Baskı Tasarımı', 'Adet', 'Satış Kanalı', 'Tutar (TL)', 'Durum', 'Sorumlu Ekip Üyesi', 'Tedarikçi', 'Son Güncelleme'], ';');

// Veritabanı kayıtlarını satır satır Excel dosyasına yazdırıyoruz
foreach ($kayitlar as $row) {
    fputcsv($output, [
        $row['id'],
        $row['urun_adi'],
        $row['kumas_turu'],
        $row['baski_tasarimi'],
        $row['adet'],
        $row['satis_kanali'],
        $row['fiyat'],
        $row['durum'],
        $row['sorumlu_kisi'],
        $row['tedarikci_bilgisi'],
        $row['guncelleme_tarihi']
    ], ';');
}

fclose($output);
exit;
?>