# 👕 Mağaza Yönetimi Paneli

[![Tanıtım Videosu](https://img.shields.io/badge/Tanıtım_Videosu-İzle-red?style=for-the-badge&logo=youtube)](VİDEO_LİNKİNİ_BURAYA_YAPIŞTIR)

## ✨ Öne Çıkan Özellikler

* **Rol Tabanlı Yetkilendirme (ACL):** Admin ve Personel olmak üzere iki farklı yetki seviyesi. Ciro görüntüleme, kayıt silme ve Excel raporu alma gibi kritik işlemler sadece Yönetici (Admin) yetkisine sahip hesaplar tarafından yapılabilir.

<img width="1917" height="767" alt="adminPanel" src="https://github.com/user-attachments/assets/9c3d3634-148d-4cae-93ec-b385b114265d" />
<img width="1912" height="717" alt="personelPanel" src="https://github.com/user-attachments/assets/388252c8-ec06-47c8-84aa-01e612a835bf" />

* **Kullanıcı Onay Mekanizması:** Sisteme dışarıdan yetkisiz erişimi engellemek için, yeni kayıt olan kullanıcılar "Bekliyor" statüsünde kalır ve Admin onayından geçmeden sisteme giriş yapamazlar.

<img width="1917" height="702" alt="ekipYonetimi" src="https://github.com/user-attachments/assets/f0e53b73-c129-4915-b280-a03a42fa71e2" />

* **Excel (.csv) Raporlama:** Filtrelenmiş sipariş listesi, arka planda uygulanan UTF-8 BOM damgası sayesinde **Türkçe karakter sorunu yaşanmadan** tek tıkla Excel uyumlu formatta indirilebilir.
* **Gelişmiş Filtreleme ve Sayfalama:** Yüzlerce sipariş arasında anında dinamik arama yapılabilir. Sunucu performansını korumak adına veriler sayfalara bölünerek (`LIMIT` & `OFFSET`) listelenir.

<img width="1606" height="562" alt="siparis" src="https://github.com/user-attachments/assets/983bb1f4-9f03-4b8b-a1bd-d9f44dd13c99" />

* **Modern UI/UX:** Bootstrap 5 kullanılarak; mobil uyumlu, sol menü (sidebar) destekli ve asenkron hissi veren şık ve temiz bir arayüz tasarlanmıştır.



## 🛡️ Güvenlik Önlemleri

Sistem, en yaygın web zafiyetlerine karşı korumalı olarak inşa edilmiştir:
* **SQL Injection Koruması:** Tüm veritabanı işlemleri PDO (PHP Data Objects) ve *Prepared Statements* (Hazırlanmış Sorgular) kullanılarak gerçekleştirilmiştir.
* **XSS (Cross-Site Scripting) Koruması:** Kullanıcıların girdiği form verileri ekrana basılırken `htmlspecialchars()` fonksiyonundan geçirilerek zararlı betiklerin çalışması engellenmiştir.
* **Güvenli Şifreleme:** Kullanıcı parolaları veritabanında düz metin olarak değil, güvenli `password_hash()` algoritması ile tutulmaktadır.

## 🛠️ Kullanılan Teknolojiler

* **Back-end:** PHP 8+ (Yalın/Vanilla), PDO
* **Veritabanı:** MySQL (MariaDB)
* **Front-end:** HTML5, CSS3, JavaScript, Bootstrap 5.3
* **İkon Kütüphanesi:** Bootstrap Icons

## 🚀 Kurulum ve Çalıştırma (Localhost)

Projeyi kendi lokal sunucunuzda (XAMPP, WAMP vb.) çalıştırmak için aşağıdaki adımları izleyin.

### 1. Veritabanı ve Tabloların Oluşturulması
**phpMyAdmin**'i açın, SQL sekmesine gelin ve aşağıdaki kodları çalıştırarak veritabanı mimarisini kurun:

```
CREATE DATABASE IF NOT EXISTS dtf_tedarik_sistemi DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dtf_tedarik_sistemi;

-- Kullanıcılar Tablosu
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    sifre_hash VARCHAR(255) NOT NULL,
    rol ENUM('Admin', 'Personel') NOT NULL DEFAULT 'Personel',
    onay_durumu ENUM('Bekliyor', 'Onaylandı') NOT NULL DEFAULT 'Bekliyor',
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sipariş Yönetimi Tablosu
CREATE TABLE tedarik_yonetimi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    urun_adi VARCHAR(100) NOT NULL,
    kumas_turu VARCHAR(50),
    baski_tasarimi VARCHAR(100),
    adet INT NOT NULL,
    satis_kanali VARCHAR(50),
    fiyat DECIMAL(10,2),
    durum ENUM('Beklemede', 'Baskıda', 'Hazır', 'Kargolandı') DEFAULT 'Beklemede',
    sorumlu_kisi VARCHAR(50),
    tedarikci_bilgisi VARCHAR(255),
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
);
```
### 2. Yönetici Hesabını Aktifleştirme:
Tarayıcınızdan http://localhost/proje-klasorunuz/register.php adresine giderek bir hesap oluşturun. Ardından phpMyAdmin üzerinden hesabınızı yetkilendirmek için şu SQL kodunu çalıştırın:
```
UPDATE kullanicilar SET onay_durumu = 'Onaylandı', rol = 'Admin' WHERE kullanici_adi = 'kullanici_adiniz';
```
### 👨‍💻 Geliştiriciler
* ENES TANRIVERDİ
* FATMA BERAY ERASLAN
