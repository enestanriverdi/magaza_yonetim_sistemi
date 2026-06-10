<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Zaten giriş yapmışsa içeri al
if(isset($_SESSION['kullanici_id'])){
    header("Location: index.php");
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);

    if (empty($kullanici_adi) || empty($sifre)) {
        $mesaj = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Lütfen kullanıcı adı ve şifrenizi girin.</div>';
    } else {
        // Kullanıcıyı veritabanında ara
        $stmt = $db->prepare("SELECT id, kullanici_adi, sifre_hash, rol, onay_durumu FROM kullanicilar WHERE kullanici_adi = ?");
        $stmt->execute([$kullanici_adi]);
        $kullanici = $stmt->fetch();

        // Şifre doğrulama
        if ($kullanici && password_verify($sifre, $kullanici['sifre_hash'])) {
            
            // Onay durumu kontrolü
            if ($kullanici['onay_durumu'] === 'Bekliyor') {
                $mesaj = '<div class="alert alert-warning fw-bold"><i class="bi bi-hourglass-split"></i> Hesabınız yönetici onayında bekliyor.</div>';
            } else {
                // Giriş başarılı, oturum değişkenlerini ata
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
                $_SESSION['rol'] = $kullanici['rol']; 
                
                header("Location: index.php");
                exit;
            }
        } else {
            $mesaj = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Kullanıcı adı veya şifre hatalı!</div>';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow border-0 mt-3 mb-5">
            <div class="card-header bg-dark text-white py-3 text-center">
                <h4 class="mb-0"><i class="bi bi-person-lock"></i> Sisteme Giriş</h4>
            </div>
            <div class="card-body p-4">
                <?= $mesaj ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kullanıcı Adı</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="kullanici_adi" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" name="sifre" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 fw-bold py-2"><i class="bi bi-box-arrow-in-right"></i> Giriş Yap</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>