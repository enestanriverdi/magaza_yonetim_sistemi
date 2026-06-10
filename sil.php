<?php
session_start();
require_once 'config/db.php';

// Güvenlik 1: Giriş yapılmamışsa at
if(!isset($_SESSION['kullanici_id'])){
    header("Location: login.php");
    exit;
}

// Güvenlik 2 (YENİ): Admin değilse silme işlemini yapmadan ana sayfaya geri yolla
if($_SESSION['rol'] !== 'Admin'){
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $db->prepare("DELETE FROM tedarik_yonetimi WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
?>