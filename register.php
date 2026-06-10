<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Zaten giriş yapmış bir kullanıcı kayıt sayfasına giremesin, ana sayfaya yönlendirelim
if(isset($_SESSION['kullanici_id'])){
    header("Location: index.php");
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);

    if (empty($kullanici_adi) || empty($sifre)) {
        $mesaj = '<div class="alert alert-danger">Lütfen tüm alanları doldurun.</div>';
    } else {
        // Kullanıcı adı daha önce alınmış mı kontrolü
        $kontrol_stmt = $db->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ?");
        $kontrol_stmt->execute([$kullanici_adi]);
        
        if ($kontrol_stmt->rowCount() > 0) {
            $mesaj = '<div class="alert alert-warning">Bu kullanıcı adı zaten kullanılıyor.</div>';
        } else {
            // Hocanın kuralı: Şifreyi hash'leyerek kaydetme
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
            
            $ekle_stmt = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, sifre_hash) VALUES (?, ?)");
            if ($ekle_stmt->execute([$kullanici_adi, $sifre_hash])) {
                $mesaj = '<div class="alert alert-success">Kayıt başarılı! Giriş yapabilirsiniz.</div>';
            } else {
                $mesaj = '<div class="alert alert-danger">Kayıt sırasında bir hata oluştu.</div>';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">Sisteme Kayıt Ol</h4>
            </div>
            <div class="card-body">
                <?= $mesaj ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" name="kullanici_adi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" name="sifre" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>