<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if(!isset($_SESSION['kullanici_id'])){
    header("Location: login.php");
    exit;
}

// Sistemdeki onaylı kullanıcıları (Sorumlu filtresi için) çekiyoruz
$kullaniciSor = $db->prepare("SELECT kullanici_adi FROM kullanicilar WHERE onay_durumu = 'Onaylandı'");
$kullaniciSor->execute();
$kullanicilarListesi = $kullaniciSor->fetchAll(PDO::FETCH_ASSOC);

$arama = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$durum_filtre = isset($_GET['durum_filtre']) ? $_GET['durum_filtre'] : '';
$sorumlu_filtre = isset($_GET['sorumlu_filtre']) ? $_GET['sorumlu_filtre'] : '';

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

if ($sorumlu_filtre !== '') {
    $where_sql .= " AND t.sorumlu_kisi = ?";
    $params[] = $sorumlu_filtre;
}

// SAYFALAMA 
$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
if ($sayfa < 1) $sayfa = 1;
$limit = 10; 

$count_stmt = $db->prepare("SELECT COUNT(*) FROM tedarik_yonetimi t INNER JOIN kullanicilar k ON t.kullanici_id = k.id" . $where_sql);
$count_stmt->execute($params);
$toplam_filtreli_kayit = $count_stmt->fetchColumn();

$toplam_sayfa = ceil($toplam_filtreli_kayit / $limit);
if ($sayfa > $toplam_sayfa && $toplam_sayfa > 0) $sayfa = $toplam_sayfa;
$offset = ($sayfa - 1) * $limit;

$sql = "SELECT t.*, k.kullanici_adi FROM tedarik_yonetimi t INNER JOIN kullanicilar k ON t.kullanici_id = k.id " . 
        $where_sql . " ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$kayitlar = $stmt->fetchAll();

function sayfa_linki($p) {
    $get_parametreleri = $_GET;
    $get_parametreleri['sayfa'] = $p; 
    return 'siparisler.php?' . http_build_query($get_parametreleri);
}
?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <h4 class="mb-0"><i class="bi bi-list-task"></i> Tüm Siparişler</h4>
    <div class="d-flex gap-2">
        <?php if($_SESSION['rol'] === 'Admin'): ?>
            <a href="export.php?<?= http_build_query($_GET) ?>" class="btn btn-outline-success shadow-sm"><i class="bi bi-file-earmark-excel"></i> Excel'e Aktar</a>
        <?php endif; ?>
        <a href="ekle.php" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Yeni Kayıt</a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3 bg-light">
    <div class="card-body p-3">
        <form method="GET" action="" class="mb-0">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="arama" class="form-control border-start-0 ps-0" placeholder="Arama yap..." value="<?= htmlspecialchars($arama) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="durum_filtre" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="Beklemede" <?= $durum_filtre == 'Beklemede' ? 'selected' : '' ?>>Beklemede</option>
                        <option value="Baskıda" <?= $durum_filtre == 'Baskıda' ? 'selected' : '' ?>>Baskıda</option>
                        <option value="Hazır" <?= $durum_filtre == 'Hazır' ? 'selected' : '' ?>>Hazır</option>
                        <option value="Kargolandı" <?= $durum_filtre == 'Kargolandı' ? 'selected' : '' ?>>Kargolandı</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sorumlu_filtre" class="form-select">
                        <option value="">Tüm Sorumlular</option>
                        <?php foreach($kullanicilarListesi as $kisi): ?>
                            <option value="<?= htmlspecialchars($kisi['kullanici_adi'], ENT_QUOTES, 'UTF-8') ?>" <?= $sorumlu_filtre == $kisi['kullanici_adi'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kisi['kullanici_adi'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel"></i> Filtrele</button>
                    <?php if($arama != '' || $durum_filtre != '' || $sorumlu_filtre != ''): ?>
                        <a href="siparisler.php" class="btn btn-outline-danger" title="Temizle"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Detay</th>
                        <?php if($_SESSION['rol'] === 'Admin'): ?><th>Tutar</th><?php endif; ?>
                        <th>Durum</th>
                        <th>Sorumlu</th>
                        <th class="text-end pe-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($kayitlar) > 0): ?>
                        <?php foreach ($kayitlar as $row): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $row['id'] ?></td>
                                <td>
                                    <strong class="d-block text-dark"><?= htmlspecialchars($row['urun_adi']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($row['baski_tasarimi']) ?> (<?= $row['adet'] ?> Adet)</small>
                                </td>
                                <?php if($_SESSION['rol'] === 'Admin'): ?>
                                    <td><strong class="text-success">₺<?= number_format((float)$row['fiyat'], 2, ',', '.') ?></strong></td>
                                <?php endif; ?>
                                <td>
                                    <?php 
                                        $renk = 'secondary';
                                        if($row['durum'] == 'Baskıda') $renk = 'warning text-dark';
                                        if($row['durum'] == 'Hazır') $renk = 'success';
                                        if($row['durum'] == 'Kargolandı') $renk = 'primary';
                                    ?>
                                    <span class="badge bg-<?= $renk ?>"><?= htmlspecialchars($row['durum']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['sorumlu_kisi']) ?></td>
                                <td class="text-end pe-3">
                                    <a href="duzenle.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                                    <?php if($_SESSION['rol'] === 'Admin'): ?>
                                        <a href="sil.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Silinsin mi?')"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= $_SESSION['rol'] === 'Admin' ? '6' : '5' ?>" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($toplam_sayfa > 1): ?>
    <nav>
        <ul class="pagination justify-content-center shadow-sm">
            <li class="page-item <?= ($sayfa <= 1) ? 'disabled' : '' ?>"><a class="page-link text-dark" href="<?= sayfa_linki($sayfa - 1) ?>">&laquo;</a></li>
            <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                <li class="page-item <?= ($sayfa == $i) ? 'active' : '' ?>"><a class="page-link <?= ($sayfa == $i) ? 'bg-dark border-dark text-white' : 'text-dark' ?>" href="<?= sayfa_linki($i) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= ($sayfa >= $toplam_sayfa) ? 'disabled' : '' ?>"><a class="page-link text-dark" href="<?= sayfa_linki($sayfa + 1) ?>">&raquo;</a></li>
        </ul>
    </nav>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>