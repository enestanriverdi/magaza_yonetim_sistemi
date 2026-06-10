<?php
// 1. Oturum (Session) Kontrolü
// Eğer daha önce bir oturum başlatılmadıysa başlatır.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Veritabanı Kimlik Bilgileri (Canlı Sunucu Ayarları)
$host = '';
$dbname = ''; 
$username = '';              
$password = '';

// 3. PDO Bağlantı Blokları
try {
    // utf8mb4 bağlantısı sayesinde Türkçe karakterler veritabanına kaydolur
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Hata raporlama modunu aktif eder
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // Bağlantı başarısız olursa projeyi durdurur ve hatayı ekrana basar
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>