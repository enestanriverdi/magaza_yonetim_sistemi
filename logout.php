<?php
session_start();

// Tüm session (oturum) değişkenlerini temizle
session_unset();

// Oturumu tamamen sonlandır
session_destroy();

// Çıkış yaptıktan sonra login sayfasına yönlendir
header("Location: login.php");
exit;
?>