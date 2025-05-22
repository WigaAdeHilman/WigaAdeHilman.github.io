# Portfolio Contact Form dengan Database Integration

## Setup dan Instalasi

### 1. Persiapan Database

**A. Buat Database MySQL**
```sql
-- Jalankan query SQL berikut di phpMyAdmin atau MySQL client
CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread'
);

CREATE INDEX idx_email ON contact_messages(email);
CREATE INDEX idx_created_at ON contact_messages(created_at);
CREATE INDEX idx_status ON contact_messages(status);
```

**B. Konfigurasi Database**
- Edit file `config/database.php`
- Sesuaikan kredensial database:
  ```php
  private $host = 'localhost';        // Host database
  private $db_name = 'portfolio_db';  // Nama database
  private $username = 'root';         // Username database
  private $password = '';             // Password database
  ```

### 2. Struktur Folder

Buat struktur folder seperti berikut:
```
project-root/
│
├── index.html
├── script.js
├── style.css (atau /style/style.css)
├── process_contact.php
│
├── config/
│   └── database.php
│
├── admin/
│   └── view_messages.php
│
└── assets/
    └── image/
        ├── ppWiga.png
        ├── work1.png
        └── porto1.png
```

### 3. Konfigurasi Server

**A. Requirements:**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

**B. Enable Extensions:**
- PDO MySQL extension
- JSON extension

### 4. Testing

**A. Test Koneksi Database:**
1. Akses `config/database.php` melalui browser
2. Pastikan tidak ada error koneksi

**B. Test Contact Form:**
1. Buka `index.html`
2. Isi formulir contact
3. Submit dan cek apakah data tersimpan

**C. Test Admin Panel:**
1. Akses `admin/view_messages.php`
2. Cek apakah pesan tampil dengan benar

### 5. Fitur yang Tersedia

**Frontend:**
- ✅ Validasi form client-side
- ✅ AJAX submission tanpa reload page
- ✅ Response message dengan styling
- ✅ Error handling dan loading state
- ✅ Responsive design

**Backend:**
- ✅ Validasi server-side
- ✅ Sanitasi input data
- ✅ Error handling dengan try-catch
- ✅ JSON response format
- ✅ Prepared statements untuk security

**Admin Panel:**
- ✅ View semua pesan
- ✅ Pagination untuk navigasi
- ✅ Update status (unread/read/replied)
- ✅ Delete pesan
- ✅ Quick reply via email
- ✅ Statistics dashboard
- ✅ Responsive admin interface

### 6. Security Features

- **SQL Injection Protection:** Menggunakan prepared statements
- **XSS Protection:** Input sanitization dengan htmlspecialchars
- **Input Validation:** Validasi di client dan server side
- **Data Length Limits:** Pembatasan panjang input
- **Error Handling:** Tidak menampilkan error sensitive

### 7. Troubleshooting

**Problem: "Connection error"**
- Cek kredensial database di `config/database.php`
- Pastikan MySQL service berjalan
- Cek nama database sudah benar

**Problem: "AJAX Error"**
- Cek path file `process_contact.php`
- Pastikan server mendukung CORS
- Cek browser console untuk detail error

**Problem: "Form tidak submit"**
- Pastikan jQuery loaded dengan benar
- Cek ID form dan field sudah sesuai
- Cek JavaScript console untuk error

**Problem: "Admin panel tidak tampil"**
- Cek path ke `config/database.php`
- Pastikan ada data di database
- Cek permission file PHP

### 8. Customization

**Mengubah Validasi:**
- Edit fungsi `validateInput()` di `process_contact.php`
- Update validasi JavaScript di `script.js`

**Mengubah Database Fields:**
- Modify tabel `contact_messages`
- Update query di `ContactHandler` class
- Sesuaikan form HTML dan validation

**Styling:**
- Edit CSS di `style.css` untuk response messages
- Customize admin panel styling
- Update responsive breakpoints

### 9. Production Deployment

**Security Checklist:**
- [ ] Ganti default database credentials
- [ ] Enable HTTPS
- [ ] Restrict admin panel access
- [ ] Enable PHP error logging
- [ ] Set proper file permissions
- [ ] Enable database backup

**Performance:**
- [ ] Enable PHP OPcache
- [ ] Optimize database queries
- [ ] Add indexes untuk fields yang sering diquery
- [ ] Implement rate limiting untuk form submission

### 10. Future Enhancements

Fitur yang bisa ditambahkan:
- Email notification untuk pesan baru
- Export data ke CSV/Excel
- Advanced filtering dan search
- User authentication untuk admin
- Multi-language support
- File attachment support
- Email template system
- Analytics dashboard