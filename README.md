# 🍽️ Website Restoran

Website Restoran adalah aplikasi berbasis web yang dikembangkan menggunakan **PHP** dan **MySQL**, dengan lingkungan pengembangan **Laragon**.

## 📌 Fitur

- **Manajemen Pengguna**: Mendukung peran pengguna seperti Admin, Kasir, Pelayan, dan User.
- **Sistem Pemesanan**: Pelanggan dapat memesan makanan secara online.
- **Manajemen Menu**: Admin dapat menambah, mengedit, dan menghapus item menu.
- **Pelacakan Pesanan**: Pelanggan dapat memeriksa status pesanan mereka melalui `check_status.php`.
- **Autentikasi Pengguna**: Sistem login untuk memastikan keamanan akses.

## 📂 Struktur Folder

```
📦 Website-Restoran
├── 📁 admin            # Modul untuk manajemen oleh admin
├── 📁 assets           # File aset seperti gambar dan skrip
├── 📁 config           # File konfigurasi aplikasi
├── 📁 css              # File stylesheet
├── 📁 database         # Skrip dan file terkait database
├── 📁 kasir            # Modul untuk kasir
├── 📁 pelayan          # Modul untuk pelayan
├── 📁 proses           # Skrip pemrosesan data
├── 📁 user             # Modul untuk pengguna umum
├── 📁 vendor           # Dependensi pihak ketiga
├── 📄 check_status.php # Halaman untuk memeriksa status pesanan
├── 📄 composer.json    # File konfigurasi Composer
├── 📄 composer.lock    # File kunci dependensi Composer
├── 📄 index.php        # Halaman utama aplikasi
└── 📄 login.php        # Halaman login pengguna
```

## 🛠️ Cara Instalasi

1. **Kloning Repository**

   ```sh
   git clone https://github.com/ReCode222/Website-Restoran.git
   cd Website-Restoran
   ```

2. **Konfigurasi Database**

   - Buat database baru dengan nama `restoran_db` di MySQL.
   - Import file SQL yang sesuai dari folder `database` ke dalam database yang baru dibuat.

3. **Konfigurasi Aplikasi**

   - Pastikan file konfigurasi di folder `config` telah disesuaikan dengan pengaturan database Anda.

4. **Menjalankan Aplikasi**

   - Pastikan Laragon atau server lokal lainnya sedang berjalan.
   - Akses aplikasi melalui browser di `http://localhost/Website-Restoran/`.

## 🤝 Kontribusi

Kontribusi sangat dihargai! Jika Anda ingin berkontribusi, silakan fork repository ini dan buat pull request dengan perubahan yang Anda usulkan.

## 📧 Kontak

Jika Anda memiliki pertanyaan atau masukan, silakan buat issue di [GitHub Issues](https://github.com/ReCode222/Website-Restoran/issues).

---

**Selamat menggunakan Website Restoran!** 🍕🍔🍣
