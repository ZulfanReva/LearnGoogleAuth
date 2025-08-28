# LearnGoogleAuth with Filament by Zulfan Reva

<p align="center">
    <img src="public/image/Preview.png" width="800" alt="LearnGoogleAuth Preview">
</p>
<p align="center">
    <img src="https://img.shields.io/badge/Laravel-11+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Filament-4.x-F59E0B?style=for-the-badge&logo=laravel&logoColor=white" alt="Filament Version">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
    <img src="https://img.shields.io/badge/Google_OAuth-2.0-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Google OAuth">
</p>

<p align="center">
    <strong>Aplikasi web berbasis Laravel Filament yang mengimplementasikan autentikasi menggunakan Google OAuth</strong>
</p>

## About LearnGoogleAuth

LearnGoogleAuth adalah proyek pembelajaran yang dirancang untuk memudahkan developer dalam memahami dan menerapkan autentikasi berbasis OAuth dengan antarmuka admin yang modern dan responsif dari Filament. 

Proyek ini menunjukkan cara mengintegrasikan Google OAuth dengan Laravel Filament menggunakan paket `filament-socialite` dan `laravel/socialite`, memungkinkan pengguna untuk login menggunakan akun Google mereka dengan proses autentikasi yang aman dan efisien.

## Features

- **🔐 Google OAuth Integration** - Autentikasi seamless menggunakan akun Google
- **🎨 Modern Admin Interface** - Antarmuka admin berbasis Filament yang responsif
- **🛠️ Developer Friendly** - Konfigurasi mudah untuk lingkungan lokal dan produksi
- **📊 Flexible Database Structure** - Mendukung autentikasi OAuth dan tradisional
- **🐞 Error Handling** - Solusi untuk masalah umum seperti SSL di Windows

## Requirements

- PHP 8.2+
- Laravel 11+
- Filament 4.x
- Database (MySQL/SQLite)
- Development Environment (Laragon/XAMPP/WAMP for Windows)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd LearnGoogleAuth
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   ```

5. **Complete setup**
   
   Untuk panduan lengkap setup Google OAuth dan konfigurasi SSL, silakan lihat:
   
   📄 **[GOOGLE_OAUTH_SETUP.md](GOOGLE_OAUTH_SETUP.md)**

## Usage

1. **Start the development server**
   ```bash
   php artisan serve
   ```

2. **Access the application**
   
   Buka browser dan kunjungi: `http://127.0.0.1:8000/admin`

3. **Login with Google**
   
   Klik tombol "Login with Google" untuk menggunakan autentikasi OAuth

## Project Structure

```
LearnGoogleAuth/
├── app/
│   ├── Models/
│   │   └── User.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── SocialiteServiceProvider.php
│   │   └── Filament/
│   │       └── AdminPanelProvider.php
├── bootstrap/
│   └── providers.php
├── config/
│   └── services.php
├── database/
│   └── migrations/
├── .env
└── GOOGLE_OAUTH_SETUP.md
```

## Security Notes

⚠️ **Important Security Considerations:**

- Untuk lingkungan produksi, pastikan menggunakan HTTPS
- Simpan kredensial Google OAuth dengan aman di file `.env`
- Jangan gunakan pengaturan `SSL_VERIFY_PEER=false` di produksi
- Pantau aktivitas autentikasi dengan logging untuk keamanan tambahan

## Documentation & Resources

- [Filament Documentation](https://filamentphp.com/docs)
- [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
- [Google OAuth 2.0 Guide](https://developers.google.com/identity/protocols/oauth2)

## Contributing

Kontribusi sangat diharapkan! Silakan ikuti langkah berikut:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan Anda (`git commit -m 'Add some amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

## Support

Jika Anda mengalami masalah atau memiliki pertanyaan, silakan:

- Buat [issue](../../issues) di repository ini
- Baca dokumentasi lengkap di [GOOGLE_OAUTH_SETUP.md](GOOGLE_OAUTH_SETUP.md)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

<p align="center">
    <strong>🎉 Selamat belajar autentikasi dengan Google OAuth dan Filament! 🎉</strong>
</p>
