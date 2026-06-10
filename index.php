<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Güvenlik: Giriş yapılmamışsa login sayfasına gönder
if(!isset($_SESSION['kullanici_id'])){
    header("Location: login.php");
    exit;
}

// SADECE ADMİN İÇİN İSTATİSTİKLERİ HESAPLA
// Personelin şirketin genel cirosunu veya toplam iş hacmini görmesini engelliyoruz
if ($_SESSION['rol'] === 'Admin') {
    $toplam_siparis = $db->query("SELECT COUNT(*) FROM tedarik_yonetimi")->fetchColumn();
    $bekleyen_is = $db->query("SELECT COUNT(*) FROM tedarik_yonetimi WHERE durum IN ('Beklemede', 'Baskıda')")->fetchColumn();
    $kargolanan = $db->query("SELECT COUNT(*) FROM tedarik_yonetimi WHERE durum = 'Kargolandı'")->fetchColumn();
    $toplam_kazanc = $db->query("SELECT SUM(fiyat) FROM tedarik_yonetimi")->fetchColumn();
    $toplam_kazanc = $toplam_kazanc ? $toplam_kazanc : 0;
}

// ANA EKRAN İÇİN KISA ÖZET: Sadece son eklenen 5 siparişi çekelim
$sorgu = $db->query("SELECT t.id, t.urun_adi, t.durum, t.sorumlu_kisi, k.kullanici_adi 
                     FROM tedarik_yonetimi t 
                     INNER JOIN kullanicilar k ON t.kullanici_id = k.id 
                     ORDER BY t.id DESC LIMIT 5");
$son_kayitlar = $sorgu->fetchAll();
?>

<?php if ($_SESSION['rol'] === 'Admin'): ?>
<div class="row mb-4 mt-2">
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-white bg-primary shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="card-title opacity-75"><i class="bi bi-box-seam"></i> Toplam Sipariş</h6>
                <h2 class="mb-0 fw-bold"><?= $toplam_siparis ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-dark bg-warning shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="card-title opacity-75"><i class="bi bi-hourglass-split"></i> Bekleyen/Baskıda</h6>
                <h2 class="mb-0 fw-bold"><?= $bekleyen_is ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-white bg-success shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="card-title opacity-75"><i class="bi bi-truck"></i> Kargolanan</h6>
                <h2 class="mb-0 fw-bold"><?= $kargolanan ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-dark shadow-sm h-100 border-0">
            <div class="card-body">
                <h6 class="card-title opacity-75"><i class="bi bi-currency-dollar"></i> Toplam Kazanç</h6>
                <h2 class="mb-0 fw-bold">₺<?= number_format($toplam_kazanc, 2, ',', '.') ?></h2>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info shadow-sm mb-4 mt-2 border-0">
    <h4 class="alert-heading"><i class="bi bi-person-workspace"></i> Hoş Geldin, <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>!</h4>
    <p class="mb-0">Personel yetkisi ile sisteme giriş yaptın. Üst menüden yeni sipariş ekleyebilir veya mevcut siparişlerin listesine ulaşarak operasyon durumlarını (Hazır, Kargolandı vb.) güncelleyebilirsin.</p>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Son Eklenen Siparişler</h5>
        <a href="siparisler.php" class="btn btn-sm btn-outline-primary">Tümünü Gör <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Ürün Adı</th>
                        <th>Sorumlu Kişi</th>
                        <th class="text-end pe-4">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($son_kayitlar) > 0): ?>
                        <?php foreach ($son_kayitlar as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['urun_adi']) ?></td>
                                <td><i class="bi bi-person-badge text-muted"></i> <?= htmlspecialchars($row['sorumlu_kisi']) ?></td>
                                <td class="text-end pe-4">
                                    <?php 
                                        $renk = 'secondary';
                                        if($row['durum'] == 'Baskıda') $renk = 'warning text-dark';
                                        if($row['durum'] == 'Hazır') $renk = 'success';
                                        if($row['durum'] == 'Kargolandı') $renk = 'primary';
                                    ?>
                                    <span class="badge bg-<?= $renk ?>"><?= $row['durum'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Henüz sipariş bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>