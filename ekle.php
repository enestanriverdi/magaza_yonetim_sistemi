<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Onaylı kullanıcıları çek
$kullaniciSor = $db->prepare("
    SELECT id, kullanici_adi
    FROM kullanicilar
    WHERE onay_durumu = 'Onaylandı'
    ORDER BY kullanici_adi ASC");

$kullaniciSor->execute();
$kullanicilar = $kullaniciSor->fetchAll(PDO::FETCH_ASSOC);

// Güvenlik kontrolü
if(!isset($_SESSION['kullanici_id'])){
    header("Location: login.php");
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $urun_adi = trim($_POST['urun_adi']);
    $kumas_turu = trim($_POST['kumas_turu']);
    $baski_tasarimi = trim($_POST['baski_tasarimi']);
    $adet = (int)$_POST['adet'];
    
    // Yeni eklenen alanlar
    $satis_kanali = trim($_POST['satis_kanali']);
    $sorumlu_kisi = trim($_POST['sorumlu_kisi']);
    $fiyat = (float)$_POST['fiyat'];

    $tedarikci_bilgisi = trim($_POST['tedarikci_bilgisi']);
    $durum = $_POST['durum'];
    $notlar = trim($_POST['notlar']);
    $kullanici_id = $_SESSION['kullanici_id'];

    if (empty($urun_adi) || empty($kumas_turu) || empty($adet)) {
        $mesaj = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Lütfen zorunlu alanları doldurun.</div>';
    } else {
        // INSERT sorgusu yeni sütunları kapsayacak şekilde güncellendi
        $ekle_stmt = $db->prepare("INSERT INTO tedarik_yonetimi 
            (kullanici_id, urun_adi, kumas_turu, baski_tasarimi, adet, satis_kanali, sorumlu_kisi, fiyat, tedarikci_bilgisi, durum, notlar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        if ($ekle_stmt->execute([$kullanici_id, $urun_adi, $kumas_turu, $baski_tasarimi, $adet, $satis_kanali, $sorumlu_kisi, $fiyat, $tedarikci_bilgisi, $durum, $notlar])) {
            $mesaj = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Sipariş başarıyla eklendi! <a href="index.php" class="alert-link">Listeye dön</a></div>';
        } else {
            $mesaj = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Kayıt sırasında bir hata oluştu.</div>';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary bg-gradient text-white py-3">
                <h5 class="mb-0"><i class="bi bi-plus-square"></i> Yeni Sipariş / Üretim Kaydı</h5>
            </div>
            <div class="card-body p-4">
                <?= $mesaj ?>
                <form method="POST" action="">
                    <h6 class="text-muted border-bottom pb-2 mb-3">Temel Ürün Bilgileri</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ürün Adı *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-box"></i></span>
                                <input type="text" name="urun_adi" class="form-control" placeholder="Örn: Siyah Oversize Tişört" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kumaş Türü *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-layers"></i></span>
                                <input type="text" name="kumas_turu" class="form-control" placeholder="Örn: %100 Pamuk, İki İplik" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Baskı Tasarımı / Kodu *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-palette"></i></span>
                                <input type="text" name="baski_tasarimi" class="form-control" placeholder="Örn: Astro-9 Logo" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sipariş Adedi *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="number" name="adet" class="form-control" min="1" value="1" required>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-3">Satış ve Operasyon Detayları</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Satış Kanalı</label>
                            <select name="satis_kanali" class="form-select">
                                <option value="Shopier">Shopier</option>
                                <option value="Instagram">Instagram</option>
                                <option value="WhatsApp">WhatsApp</option>
                                <option value="Elden/Referans">Elden/Referans</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Sipariş Tutarı (₺)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="number" name="fiyat" class="form-control" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Sipariş Durumu</label>
                            <select name="durum" class="form-select">
                                <option value="Beklemede">Beklemede</option>
                                <option value="Baskıda">Baskıda</option>
                                <option value="Hazır">Hazır</option>
                                <option value="Kargolandı">Kargolandı</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tedarikçi Bilgisi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shop-window"></i></span>
                                <input type="text" name="tedarikci_bilgisi" class="form-control" placeholder="Örn: Bursa Merkez Kumaşçılık">
                            </div>
                        </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Görevli Kişi / Sorumlu</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-badge"></i>
                                    </span>

                                    <select name="sorumlu_kisi" class="form-select">
                                        <option value="">Sorumlu Seçiniz</option>

                                        <?php foreach($kullanicilar as $kullanici): ?>
                                            <option value="<?= htmlspecialchars($kullanici['kullanici_adi'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($kullanici['kullanici_adi'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>                        
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Ek Notlar</label>
                        <textarea name="notlar" class="form-control" rows="3" placeholder="Örn: Müşteri hediye paketi istedi..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left"></i> İptal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Siparişi Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>