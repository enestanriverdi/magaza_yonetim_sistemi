<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Güvenlik: Giriş yapmayan veya Admin olmayan giremez
if(!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'Admin'){
    header("Location: index.php");
    exit;
}

// Onaylama veya Silme işlemi varsa yakala
if (isset($_GET['islem']) && isset($_GET['id'])) {
    $islem_id = (int)$_GET['id'];
    
    if ($_GET['islem'] == 'onayla') {
        $db->prepare("UPDATE kullanicilar SET onay_durumu = 'Onaylandı' WHERE id = ?")->execute([$islem_id]);
    } elseif ($_GET['islem'] == 'reddet') {
        // Reddedilen kullanıcıyı tamamen silebiliriz
        $db->prepare("DELETE FROM kullanicilar WHERE id = ?")->execute([$islem_id]);
    }
    
    header("Location: kullanicilar.php");
    exit;
}

// Tüm kullanıcıları çek
$sorgu = $db->query("SELECT id, kullanici_adi, rol, onay_durumu, kayit_tarihi FROM kullanicilar ORDER BY id DESC");
$uyeler = $sorgu->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <h4 class="mb-0"><i class="bi bi-people-fill"></i> Ekip ve Kullanıcı Yönetimi</h4>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Kayıt Tarihi</th>
                        <th>Rol</th>
                        <th>Durum</th>
                        <th class="text-end pe-4">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($uyeler as $uye): ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= $uye['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($uye['kullanici_adi']) ?></td>
                            <td><?= $uye['kayit_tarihi'] ?></td>
                            <td>
                                <span class="badge bg-<?= $uye['rol'] == 'Admin' ? 'dark' : 'secondary' ?>"><?= $uye['rol'] ?></span>
                            </td>
                            <td>
                                <?php if ($uye['onay_durumu'] == 'Bekliyor'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass"></i> Bekliyor</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Onaylı</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($uye['onay_durumu'] == 'Bekliyor'): ?>
                                    <a href="kullanicilar.php?islem=onayla&id=<?= $uye['id'] ?>" class="btn btn-sm btn-success shadow-sm" title="Onayla"><i class="bi bi-check-lg"></i></a>
                                    <a href="kullanicilar.php?islem=reddet&id=<?= $uye['id'] ?>" class="btn btn-sm btn-danger shadow-sm" title="Reddet/Sil" onclick="return confirm('Bu kayıt talebini silmek istediğinize emin misiniz?')"><i class="bi bi-x-lg"></i></a>
                                <?php else: ?>
                                    <?php if($uye['id'] != $_SESSION['kullanici_id']): ?>
                                        <a href="kullanicilar.php?islem=reddet&id=<?= $uye['id'] ?>" class="btn btn-sm btn-outline-danger" title="Hesabı Sil" onclick="return confirm('Bu kullanıcıyı sistemden tamamen silmek istediğinize emin misiniz?')"><i class="bi bi-trash"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">Sen</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>