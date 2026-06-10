<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mağaza Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        /* Üst Menü (Navbar) */
        .top-navbar {
            height: 60px;
            background-color: #212529;
            z-index: 1050;
        }
        /* Sol Menü (Sidebar) */
        .sidebar {
            position: fixed;
            top: 60px; 
            bottom: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            padding-top: 15px;
            z-index: 1040;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            font-size: 1.05rem;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #495057;
            border-left: 4px solid #0d6efd;
        }
        /* Ana İçerik Alanı */
        .main-content {
            margin-left: 250px; 
            padding-top: 80px;
            padding-bottom: 40px;
        }

        /* Mobil Ekranlar İçin Düzenleme */
        @media (max-width: 768px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                padding-top: 80px;
            }
            .main-content {
                margin-left: 0;
                padding-top: 20px;
            }
        }
    </style>
</head>
<body>

<?php if(isset($_SESSION['kullanici_id'])): ?>

    <nav class="navbar top-navbar position-fixed w-100 shadow-sm px-4">
        <a class="navbar-brand text-white fw-bold" href="index.php">
            <i class="bi bi-box-seam text-primary me-2"></i> Mağaza Yönetim Sistemi
        </a>
        <div class="d-flex align-items-center">
            <span class="text-secondary me-3 d-none d-md-inline">Hoş geldin, <strong class="text-light"><?= htmlspecialchars($_SESSION['kullanici_adi']) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm fw-bold shadow-sm">
                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
            </a>
        </div>
    </nav>

    <div class="sidebar shadow">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php"><i class="bi bi-house-door me-2"></i> Ana Panel</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="siparisler.php"><i class="bi bi-list-task me-2"></i> Tüm Siparişler</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ekle.php"><i class="bi bi-plus-circle me-2"></i> Yeni Kayıt</a>
            </li>
            
            <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Admin'): ?>
            <li class="nav-item mt-4 mb-1">
                <small class="text-muted px-4 text-uppercase fw-bold" style="letter-spacing: 1px;">Yönetim</small>
            </li>
            <li class="nav-item">
                <a class="nav-link text-warning" href="kullanicilar.php"><i class="bi bi-people-fill me-2"></i> Ekip Yönetimi</a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid px-4">

<?php else: ?>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="login.php">
                <i class="bi bi-box-seam text-primary me-2"></i> Mağaza Yönetim Sistemi
            </a>
            <div>
                <a href="login.php" class="text-light text-decoration-none me-3">Giriş Yap</a>
                <a href="register.php" class="btn btn-primary btn-sm">Kayıt Ol</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">

<?php endif; ?>