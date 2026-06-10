<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Güvenlik kontrolü
if(!isset($_SESSION['kullanici_id'])){
    header("Location: login.php");
    exit;
}

$mesaj = '';
$kayit = null;

// URL'den gelen ID'yi kontrol et ve mevcut verileri çek
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM tedarik_yonetimi WHERE id = ?");
    $stmt->execute([$id]);
    $kayit = $stmt->fetch();

    if (!$kayit) {
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}

// Form gönderildiğinde (POST) güncelleme işlemini yap
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

    if (empty($urun_adi) || empty($kumas_turu) || empty($adet)) {
        $mesaj = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Lütfen zorunlu alanları doldurun.</div>';
    } else {
        // UPDATE sorgusu yeni alanları içerecek şekilde güncellendi
        $guncelle_stmt = $db->prepare("UPDATE tedarik_yonetimi SET 
            urun_adi = ?, kumas_turu = ?, baski_tasarimi = ?, adet = ?, 
            satis_kanali = ?, sorumlu_kisi = ?, fiyat = ?, 
            tedarikci_bilgisi = ?, durum = ?, notlar = ? WHERE id = ?");
            
        if ($guncelle_stmt->execute([$urun_adi, $kumas_turu, $baski_tasarimi, $adet, $satis_kanali, $sorumlu_kisi, $fiyat, $tedarikci_bilgisi, $durum, $notlar, $id])) {
            $mesaj = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Sipariş başarıyla güncellendi! <a href="index.php" class="alert-link">Listeye dön</a></div>';
            
            // Güncel verileri ekranda anlık görebilmek için kaydı tekrar çekiyoruz
            $stmt->execute([$id]);
            $kayit = $stmt->fetch();
        } else {
            $mesaj = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Güncelleme sırasında bir hata oluştu.</div>';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-warning bg-gradient text-dark py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Sipariş Düzenle: #<?= $kayit['id'] ?></h5>
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
                                <input type="text" name="urun_adi" class="form-control" value="<?= htmlspecialchars($kayit['urun_adi']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kumaş Türü *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-layers"></i></span>
                                <input type="text" name="kumas_turu" class="form-control" value="<?= htmlspecialchars($kayit['kumas_turu']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Baskı Tasarımı / Kodu *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-palette"></i></span>
                                <input type="text" name="baski_tasarimi" class="form-control" value="<?= htmlspecialchars($kayit['baski_tasarimi']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sipariş Adedi *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="number" name="adet" class="form-control" min="1" value="<?= $kayit['adet'] ?>" required>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-3">Satış ve Operasyon Detayları</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Satış Kanalı</label>
                            <select name="satis_kanali" class="form-select">
                                <option value="Shopier" <?= $kayit['satis_kanali'] == 'Shopier' ? 'selected' : '' ?>>Shopier</option>
                                <option value="Instagram" <?= $kayit['satis_kanali'] == 'Instagram' ? 'selected' : '' ?>>Instagram</option>
                                <option value="WhatsApp" <?= $kayit['satis_kanali'] == 'WhatsApp' ? 'selected' : '' ?>>WhatsApp</option>
                                <option value="Elden/Referans" <?= $kayit['satis_kanali'] == 'Elden/Referans' ? 'selected' : '' ?>>Elden/Referans</option>
                                <option value="Diğer" <?= $kayit['satis_kanali'] == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Sipariş Tutarı (₺)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="number" name="fiyat" class="form-control" step="0.01" min="0" value="<?= $kayit['fiyat'] ?>">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Sipariş Durumu</label>
                            <select name="durum" class="form-select">
                                <option value="Beklemede" <?= $kayit['durum'] == 'Beklemede' ? 'selected' : '' ?>>Beklemede</option>
                                <option value="Baskıda" <?= $kayit['durum'] == 'Baskıda' ? 'selected' : '' ?>>Baskıda</option>
                                <option value="Hazır" <?= $kayit['durum'] == 'Hazır' ? 'selected' : '' ?>>Hazır</option>
                                <option value="Kargolandı" <?= $kayit['durum'] == 'Kargolandı' ? 'selected' : '' ?>>Kargolandı</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tedarikçi Bilgisi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shop-window"></i></span>
                                <input type="text" name="tedarikci_bilgisi" class="form-control" value="<?= htmlspecialchars($kayit['tedarikci_bilgisi']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Görevli Kişi / Sorumlu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <select name="sorumlu_kisi" class="form-select">
                                    <option value="Atanmadı" <?= $kayit['sorumlu_kisi'] == 'Atanmadı' ? 'selected' : '' ?>>Atanmadı</option>
                                    <option value="Enes" <?= $kayit['sorumlu_kisi'] == 'Enes' ? 'selected' : '' ?>>Enes</option>
                                    <option value="Efe" <?= $kayit['sorumlu_kisi'] == 'Efe' ? 'selected' : '' ?>>Efe</option>
                                    <option value="Erdem" <?= $kayit['sorumlu_kisi'] == 'Erdem' ? 'selected' : '' ?>>Erdem</option>
                                    <option value="Mustafa" <?= $kayit['sorumlu_kisi'] == 'Mustafa' ? 'selected' : '' ?>>Mustafa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Ek Notlar</label>
                        <textarea name="notlar" class="form-control" rows="3"><?= htmlspecialchars($kayit['notlar']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left"></i> İptal</a>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i> Değişiklikleri Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>