# 🚀 Complete Google OAuth Setup for Laravel Filament

Panduan lengkap untuk mengintegrasikan Google OAuth dengan Laravel Filament menggunakan `filament-socialite`. Dokumentasi ini mencakup semua langkah dari awal hingga akhir, termasuk solusi untuk masalah SSL yang sering terjadi di environment Windows.

## 🎓 Konsep Fundamental (Wajib Dipahami)

### 🔐 **Authentication vs Authorization**
- **Authentication**: "Siapa kamu?" → Memverifikasi identitas user
- **Authorization**: "Apa yang boleh kamu lakukan?" → Menentukan permission

OAuth 2.0 fokus ke **Authorization**, tapi sering digunakan untuk Authentication juga.

### 🌐 **Traditional Login vs OAuth Login**

**Traditional Login:**
```
User → Email + Password → Your App → Database Check → Login Success
```

**OAuth Login:**
```
User → Click "Google" → Google Login → Google sends data → Your App → Login Success
```

**Keunggulan OAuth:**
- ✅ User tidak perlu remember password baru
- ✅ Security handled by Google (2FA, breach detection)
- ✅ Faster registration/login process
- ✅ Trust factor (people trust Google)

### 🏗️ **Arsitektur Aplikasi**

```
Browser (User Interface)
    ↓
Laravel Filament (Admin Panel)
    ↓
Filament Socialite (OAuth Integration)
    ↓
Laravel Socialite (OAuth Client)
    ↓
Google OAuth API (OAuth Server)
```

### 🔄 **Development vs Production Environment**

| Aspect | Development | Production |
|--------|-------------|------------|
| SSL | Bisa disabled | Harus enabled |
| Domain | localhost/127.0.0.1 | Real domain |
| Secrets | Bisa hardcode | Harus environment variables |
| Debugging | Error details visible | Error details hidden |

---

## 📋 Prerequisites

- Laravel 11+ dengan Filament 4.x
- PHP 8.2+
- Database (MySQL/SQLite)
- Laragon/XAMPP/WAMP (untuk Windows)

---

## 🔧 Step 1: Install Required Packages

```bash
composer require dutchcodingcompany/filament-socialite laravel/socialite
```

**Optional: Install Font Awesome untuk icon Google**
```bash
composer require owenvoke/blade-fontawesome
```

---

## 🗄️ Step 2: Database Configuration

### 📚 **Pemahaman Database Schema untuk OAuth (Edukasi)**

**Mengapa password perlu nullable?**
Dalam traditional authentication, user register dengan email + password. Tapi dengan OAuth:
- User tidak pernah set password di aplikasi kita
- Authentication dilakukan oleh Google
- Kita hanya menerima data user yang sudah terverifikasi

**Mengapa ada default password 'google123'?**
- **Compatibility**: Beberapa sistem internal mungkin expect password exist
- **Future flexibility**: User bisa set password nanti jika mau login traditional
- **Admin access**: Admin bisa login dengan password jika perlu

**Apa itu socialite_users table?**
Table ini menyimpan mapping antara:
- Local user di aplikasi kita
- OAuth provider (Google, Facebook, etc)
- User ID di provider tersebut

Struktur: `user_id` ← → `provider` + `provider_id`

**Migration Strategy:**
1. `migrate:fresh` → Hapus semua data, rebuild schema (development)
2. `migrate` → Apply new migrations only (production)
3. `migrate:rollback` → Undo migration terakhir

### 2.1 Publish Migration Files
```bash
php artisan vendor:publish --tag="filament-socialite-migrations"
```

### 2.2 Update Users Table Migration
Edit file `database/migrations/0001_01_01_000000_create_users_table.php`:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password')->nullable()->default(bcrypt('google123')); // ← Ubah ini
    $table->rememberToken();
    $table->timestamps();
});
```

### 2.3 Run Migrations
```bash
php artisan migrate:fresh
```

---

## ⚙️ Step 3: Configuration Files

### 3.1 Update `config/services.php`
Tambahkan konfigurasi Google:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

### 3.2 Update `.env` File
```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/admin/oauth/callback/google

# SSL Configuration (untuk development Windows)
CURL_CA_BUNDLE=""
SSL_VERIFY_PEER=false
```

---

## 🎨 Step 4: Configure Filament Panel

### 📚 **Pemahaman Filament Panel Provider (Edukasi)**

**Apa itu Panel Provider?**
Panel Provider adalah konfiguration class yang menentukan:
- Authentication method (traditional + OAuth)
- UI theme dan styling
- Available resources (tables, forms, etc)
- Middleware stack
- Plugin integration

**Plugin System di Filament:**
Filament menggunakan plugin architecture untuk extend functionality:
- `FilamentSocialitePlugin`: Adds OAuth providers
- Each provider has configuration: icon, color, label, etc
- Plugins di-load saat panel initialization

**Provider Configuration Options:**
```php
Provider::make('google')          // Provider name (must match config/services.php)
    ->label('Google')             // Button text
    ->icon('fab-google')          // Font Awesome icon class
    ->color(Color::Red)           // Button color theme
    ->outlined(false)             // Button style (filled vs outlined)
    ->stateless(false)            // Session vs stateless mode
```

**Registration vs No-Registration:**
- `->registration(true)`: Auto-create user jika belum ada
- `->registration(false)`: Hanya allow existing users login

Edit `app/Providers/Filament/AdminPanelProvider.php`:

```php
<?php

namespace App\Providers\Filament;

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Support\Colors\Color;
// ... other imports

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->plugin(
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->icon('fab-google')
                            ->color(Color::Red)
                            ->outlined(false)
                            ->stateless(false),
                    ])
                    ->registration(true)
                    ->userModelClass(\App\Models\User::class)
            )
            // ... rest of configuration
            ;
    }
}
```

---

## 🔐 Step 5: Fix SSL Issues (Windows Development)

### 📚 **Pemahaman SSL/TLS (Edukasi)**

**Apa itu SSL/TLS?**
SSL (Secure Sockets Layer) dan TLS (Transport Layer Security) adalah protokol keamanan yang mengenkripsi komunikasi antara browser dan server. Ketika Laravel Socialite melakukan request ke Google API (`https://www.googleapis.com/oauth2/v4/token`), ia perlu memverifikasi bahwa server Google benar-benar Google, bukan penyerang yang menyamar.

**Mengapa terjadi error di Windows/Local Development?**
1. **Certificate Authority (CA) Bundle Missing**: Windows development environment (Laragon/XAMPP) sering tidak memiliki certificate bundle yang up-to-date
2. **Path Issues**: PHP tidak bisa menemukan file `cacert.pem` di path yang dikonfigurasi
3. **Local vs Production**: Di production server, biasanya sudah ada certificate bundle yang proper

**Mengapa tidak terjadi di Linux/Mac?**
Linux dan Mac memiliki system-wide certificate store yang sudah terintegrasi dengan PHP, sedangkan Windows perlu konfigurasi manual.

**Apakah aman disable SSL verification?**
- ✅ **Development**: Relatif aman karena hanya local
- ❌ **Production**: Sangat berbahaya! Bisa kena Man-in-the-Middle attack

### 5.1 Create Socialite Service Provider
Buat file `app/Providers/SocialiteServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class SocialiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Disable SSL verification untuk development Windows
        if (app()->environment('local')) {
            Socialite::extend('google', function ($app) {
                $config = $app['config']['services.google'];
                
                $httpClient = new Client([
                    'verify' => false,
                    'timeout' => 30,
                ]);

                return Socialite::buildProvider(
                    \Laravel\Socialite\Two\GoogleProvider::class,
                    $config
                )->setHttpClient($httpClient);
            });
        }
    }
}
```

### 5.2 Register Service Provider

### 📚 **Pemahaman Laravel Service Provider (Edukasi)**

**Apa itu Service Provider?**
Service Provider adalah pusat konfigurasi aplikasi Laravel. Mereka menentukan bagaimana services di-bind ke container dan bagaimana aplikasi di-bootstrap.

**Lifecycle Service Provider:**
1. **Register Phase**: Bind services ke container (dependency injection)
2. **Boot Phase**: Configure services setelah semua registered

**Mengapa buat SocialiteServiceProvider terpisah?**
- **Separation of Concerns**: OAuth logic terpisah dari app logic
- **Reusability**: Bisa digunakan di multiple panels
- **Testing**: Easier to mock/test
- **Maintainability**: Changes isolated to one file

**Urutan loading di `providers.php`:**
```php
App\Providers\AppServiceProvider::class,        // ← Core app services
App\Providers\SocialiteServiceProvider::class,  // ← OAuth configuration  
App\Providers\Filament\AdminPanelProvider::class, // ← UI configuration
```

Order matters! Socialite harus loaded sebelum Filament panel.

Edit `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SocialiteServiceProvider::class, // ← Tambah ini
    App\Providers\Filament\AdminPanelProvider::class,
];
```

### 5.3 Update AppServiceProvider (Alternative)
Edit `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    // Disable SSL verification untuk development
    if (app()->environment('local')) {
        $this->app->resolving(\GuzzleHttp\Client::class, function ($client) {
            $client->getConfig()['verify'] = false;
        });
    }
}
```

---

## 🌐 Step 6: Google Cloud Console Setup

### 📚 **Pemahaman OAuth 2.0 Flow (Edukasi)**

**Apa itu OAuth 2.0?**
OAuth 2.0 adalah standar authorization yang memungkinkan aplikasi mendapatkan akses terbatas ke akun user di service lain (Google) tanpa perlu tahu password user.

**OAuth Flow Step-by-Step:**
```
1. User klik "Login dengan Google" di aplikasi kita
   ↓
2. User diarahkan ke Google dengan parameter:
   - client_id: ID aplikasi kita
   - redirect_uri: URL callback kita
   - scope: data apa yang kita minta
   ↓
3. User login di Google dan approve akses
   ↓
4. Google redirect user kembali ke aplikasi kita dengan "authorization code"
   ↓
5. Aplikasi kita exchange "code" dengan "access token" ke Google
   ↓
6. Dengan access token, kita bisa ambil data user dari Google API
   ↓
7. Kita create/login user berdasarkan data dari Google
```

**Mengapa perlu Redirect URI?**
- **Security**: Google perlu tahu kemana boleh mengirim authorization code
- **Validation**: Mencegah attacker menggunakan client_id kita untuk app mereka
- **Flow Control**: Menentukan endpoint mana yang handle callback

**Mengapa Client Secret penting?**
Client Secret digunakan saat exchange authorization code dengan access token. Ini memastikan hanya aplikasi kita yang bisa menukar code tersebut.

### 6.1 Buat Google OAuth Application
1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Pilih atau buat project baru
3. Enable **Google+ API** atau **Google OAuth2 API**
4. Pergi ke **APIs & Services** → **Credentials**
5. Klik **Create Credentials** → **OAuth 2.0 Client IDs**

### 6.2 Configure OAuth Consent Screen
1. Pilih **External** (untuk testing)
2. Isi **App name**, **User support email**, **Developer contact**
3. Tambahkan **Authorized domains** (opsional untuk testing)
4. **Save and Continue**

### 6.3 Set Authorized Redirect URIs
Tambahkan URL berikut di **Authorized redirect URIs**:
```
http://127.0.0.1:8000/admin/oauth/callback/google
http://localhost:8000/admin/oauth/callback/google
```

### 6.4 Copy Credentials
Salin **Client ID** dan **Client Secret** ke file `.env`

---

## 🧪 Step 7: Testing

### 7.1 Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

### 7.2 Start Development Server
```bash
php artisan serve
```

### 7.3 Test OAuth Flow
1. Buka: `http://127.0.0.1:8000/admin`
2. Klik tombol **Google**
3. Login dengan akun Google
4. Seharusnya redirect ke dashboard Filament

---

## 🎯 Available Routes

Laravel Socialite otomatis membuat routes berikut:

| Route | Purpose |
|-------|---------|
| `/admin` | Halaman login Filament |
| `/admin/oauth/google` | Redirect ke Google OAuth |
| `/admin/oauth/callback/google` | Callback dari Google |

**Check routes:**
```bash
php artisan route:list --name=oauth
```

---

## 🔧 Troubleshooting

### 📚 **Deep Dive: Debugging OAuth Issues (Edukasi)**

**OAuth Error Categories:**
1. **Configuration Errors**: Wrong client_id, secret, atau redirect_uri
2. **Network Errors**: SSL, firewall, atau connectivity issues
3. **State Mismatch**: Session atau CSRF token issues
4. **Scope Errors**: Missing permissions atau invalid scopes

**Debugging Strategy:**
1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Enable debug mode**: `APP_DEBUG=true` di `.env`
3. **Check network tab**: Browser DevTools → Network
4. **Google OAuth Playground**: Test credentials manually

### ❌ "cURL error 77: error setting certificate file"

**Root Cause Analysis:**
```
Laravel Socialite → Guzzle HTTP Client → cURL → SSL Certificate Validation → ERROR
```

**Mengapa terjadi di Windows?**
- Windows tidak punya system certificate store seperti Linux
- Laragon/XAMPP menggunakan custom PHP installation
- Path ke `cacert.pem` tidak valid atau file tidak ada

**Penyebab:** SSL certificate issue di Windows
**Impact:** Tidak bisa connect ke Google API
**Risk Level:** Low (development only)

**Solusi:**
1. ✅ Pastikan `SocialiteServiceProvider` sudah dibuat dan registered
2. ✅ Set environment variables SSL di `.env`
3. ✅ Restart server dengan `php artisan serve`

**Alternative Solutions:**
- Download fresh `cacert.pem` dari https://curl.se/ca/cacert.pem
- Update php.ini: `curl.cainfo = "path/to/cacert.pem"`
- Use Docker dengan pre-configured certificates

### ❌ "redirect_uri_mismatch"

**Root Cause Analysis:**
```
Google OAuth Request → Redirect URI Check → Database Comparison → ERROR
```

**Mengapa terjadi?**
Google menyimpan list authorized redirect URIs di database mereka. Request harus exact match:
- Protocol: `http` vs `https`
- Domain: `localhost` vs `127.0.0.1` vs `yourdomain.com`
- Port: `:8000` vs no port
- Path: `/admin/oauth/callback/google`

**Penyebab:** URL callback tidak cocok
**Impact:** OAuth flow terputus di step 4
**Risk Level:** High (blocks authentication)

**Solusi:**
1. ✅ Pastikan redirect URI di Google Console sama dengan `.env`
2. ✅ Gunakan `127.0.0.1:8000` bukan `localhost` jika ada masalah
3. ✅ Tambahkan multiple redirect URIs untuk development

**Debug Commands:**
```bash
# Check current routes
php artisan route:list --name=oauth

# Check config values
php artisan tinker
>>> config('services.google')
```

### ❌ Tombol Google tidak muncul

**Root Cause Analysis:**
```
Filament Panel → Plugin Loading → Provider Registration → UI Rendering → ERROR
```

**Possible Causes:**
1. **Cache Issues**: Old configuration cached
2. **Plugin Not Registered**: Missing in panel provider
3. **Provider Config Error**: Invalid provider setup
4. **CSS Issues**: Icon or styling not loaded

**Penyebab:** Cache atau konfigurasi
**Impact:** No OAuth option available
**Risk Level:** Medium (fallback to traditional login)

**Solusi:**
```bash
php artisan config:clear      # Clear config cache
php artisan cache:clear       # Clear application cache  
php artisan view:clear        # Clear compiled views
php artisan filament:clear-cached-components  # Clear Filament cache
```

**Advanced Debugging:**
```php
// Check if provider is loaded
dd(\DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin::class);

// Check panel configuration
dd(filament()->getPanel('admin')->getPlugins());
```

### ❌ Icon Google tidak muncul
**Penyebab:** Font Awesome belum terinstall
**Solusi:**
```bash
composer require owenvoke/blade-fontawesome
```

### ❌ Database error saat login
**Penyebab:** Migration belum jalan atau schema tidak sesuai
**Solusi:**
```bash
php artisan migrate:fresh
```

---

## 🎨 Customization Options

### Custom Button Style
```php
Provider::make('google')
    ->label('Sign in with Google')
    ->icon('fab-google')
    ->color(Color::Red)
    ->outlined(true)  // Outlined button
    ->stateless(false),
```

### Custom User Creation Logic
```php
FilamentSocialitePlugin::make()
    ->providers([...])
    ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
        return \App\Models\User::create([
            'name' => $oauthUser->getName(),
            'email' => $oauthUser->getEmail(),
            'password' => bcrypt('google123'), // Default password
            'email_verified_at' => now(),
        ]);
    })
```

### Domain Restrictions
```php
FilamentSocialitePlugin::make()
    ->providers([...])
    ->domainAllowList(['yourdomain.com'])
    ->registration(true)
```

---

## 🛡️ Security Notes

### 📚 **Deep Dive: OAuth Security (Edukasi)**

**OAuth Security Model:**
OAuth 2.0 dirancang dengan asumsi bahwa beberapa komponen tidak sepenuhnya trusted:
- **Browser**: Bisa di-compromise malware
- **Network**: Bisa ada Man-in-the-Middle attack
- **Client Application**: Bisa ada vulnerability

**Security Mechanisms:**
1. **Client Secret**: Proves client identity to authorization server
2. **Redirect URI Whitelist**: Prevents authorization code theft
3. **State Parameter**: Prevents CSRF attacks
4. **HTTPS**: Encrypts communication (production only)
5. **Short-lived Tokens**: Limits exposure window

**Threat Model & Mitigations:**

| Threat | Description | Mitigation |
|--------|-------------|------------|
| **Authorization Code Interception** | Attacker steals code from redirect | HTTPS + URI whitelist |
| **Client Impersonation** | Fake app uses real client_id | Client secret validation |
| **CSRF Attack** | Malicious site triggers OAuth | State parameter |
| **Token Theft** | Access token stolen | Short expiration + HTTPS |

**Development vs Production Security:**

**Development (Current Setup):**
- ✅ Password field nullable untuk OAuth users
- ✅ Default password `google123` (bisa diganti user nanti)
- ✅ Email verification handled by Google
- ✅ Auto-registration enabled (bisa dimatikan)
- ⚠️ SSL verification disabled **hanya untuk development**

**Production Requirements:**
- ⚠️ Untuk production, gunakan SSL certificate yang valid
- 🔒 Store client_secret di environment variables (tidak di git)
- 🔒 Use HTTPS untuk semua OAuth endpoints
- 🔒 Implement rate limiting untuk OAuth endpoints
- 🔒 Log OAuth events untuk audit trail

**Best Practices:**
```php
// Production configuration example
FilamentSocialitePlugin::make()
    ->providers([
        Provider::make('google')
            ->label('Google')
            ->icon('fab-google')
            ->stateless(false), // Use sessions for better security
    ])
    ->registration(function (string $provider, SocialiteUserContract $oauthUser, ?Authenticatable $user) {
        // Custom logic: only allow company emails
        return str_ends_with($oauthUser->getEmail(), '@yourcompany.com');
    })
    ->domainAllowList(['yourcompany.com']) // Restrict to company domains
```

**OAuth Event Monitoring:**
```php
// Listen for security events
Event::listen([
    \DutchCodingCompany\FilamentSocialite\Events\Login::class,
    \DutchCodingCompany\FilamentSocialite\Events\UserNotAllowed::class,
], function ($event) {
    Log::info('OAuth Event', [
        'event' => class_basename($event),
        'user_email' => $event->oauthUser->getEmail(),
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
});
```

---

## 📁 File Structure

Setelah setup lengkap, struktur file yang berubah:

```
app/
├── Models/
│   └── User.php (updated)
├── Providers/
│   ├── AppServiceProvider.php (updated)
│   ├── SocialiteServiceProvider.php (new)
│   └── Filament/
│       └── AdminPanelProvider.php (updated)
bootstrap/
└── providers.php (updated)
config/
└── services.php (updated)
database/
└── migrations/
    ├── 0001_01_01_000000_create_users_table.php (updated)
    └── 2025_08_28_025538_create_socialite_users_table.php (new)
.env (updated)
```

---

## 📚 Advanced Features

### Multiple Providers
```php
->providers([
    Provider::make('google')->label('Google')->icon('fab-google'),
    Provider::make('github')->label('GitHub')->icon('fab-github'),
    Provider::make('facebook')->label('Facebook')->icon('fab-facebook'),
])
```

### Custom Scopes
```php
Provider::make('google')
    ->scopes(['email', 'profile', 'openid'])
```

### Event Listeners
```php
// Listen to login events
Event::listen(\DutchCodingCompany\FilamentSocialite\Events\Login::class, function ($event) {
    Log::info('User logged in via OAuth', ['user' => $event->socialiteUser]);
});
```

---

## 🔗 Useful Links

- [Filament Socialite Documentation](https://github.com/DutchCodingCompany/filament-socialite)
- [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
- [Google OAuth 2.0 Setup Guide](https://developers.google.com/identity/protocols/oauth2)
- [Google Cloud Console](https://console.cloud.google.com/)

---

## ✅ Final Checklist

- [ ] Packages installed (`filament-socialite`, `socialite`, `blade-fontawesome`)
- [ ] Database migrated dengan password nullable
- [ ] Google OAuth app created di Google Cloud Console
- [ ] Redirect URIs configured di Google Console
- [ ] `.env` file updated dengan credentials
- [ ] `AdminPanelProvider` configured dengan Google provider
- [ ] SSL issues resolved untuk Windows development
- [ ] Service providers registered
- [ ] Cache cleared
- [ ] OAuth flow tested dan berhasil

**🎉 Congratulations! Google OAuth integration selesai dan siap digunakan!**

---

## ❓ FAQ & Best Practices

### 📚 **Frequently Asked Questions**

**Q: Apakah user bisa login dengan email+password traditional setelah OAuth?**
A: Ya! Filament mendukung multiple authentication methods. User yang login via OAuth bisa set password nanti untuk traditional login.

**Q: Bagaimana jika Google user tidak punya akun di aplikasi kita?**
A: Dengan `->registration(true)`, akun otomatis dibuat. Data dari Google (name, email) disimpan ke database.

**Q: Apakah bisa multiple OAuth provider (Google + Facebook)?**
A: Ya! Tambahkan provider lain di array providers:
```php
->providers([
    Provider::make('google')->label('Google')->icon('fab-google'),
    Provider::make('facebook')->label('Facebook')->icon('fab-facebook'),
])
```

**Q: Bagaimana handle user yang punya akun traditional tapi login via OAuth?**
A: Laravel Socialite otomatis link berdasarkan email. Jika email sama, akun yang ada akan di-update.

**Q: Apakah aman simpan Client Secret di .env?**
A: Untuk development, aman. Untuk production, gunakan encrypted environment atau secret management service.

**Q: Kenapa pakai bcrypt('google123') sebagai default password?**
A: 
- Compatibility dengan sistem yang expect password
- User bisa traditional login jika perlu
- Admin bisa access akun jika ada masalah OAuth

### 🎯 **Best Practices untuk Production**

**1. Environment Configuration:**
```env
# Production .env
APP_ENV=production
APP_DEBUG=false
GOOGLE_CLIENT_SECRET=your_production_secret
# JANGAN disable SSL di production!
```

**2. Database Optimization:**
```php
// Add index untuk performance
Schema::table('users', function (Blueprint $table) {
    $table->index('email');
});

Schema::table('socialite_users', function (Blueprint $table) {
    $table->index(['provider', 'provider_id']);
});
```

**3. Monitoring & Logging:**
```php
// Monitor OAuth usage
class OAuthMetrics {
    public static function track($provider, $event) {
        Log::info("OAuth {$event}", [
            'provider' => $provider,
            'timestamp' => now(),
            'ip' => request()->ip(),
        ]);
    }
}
```

**4. Error Handling:**
```php
// Custom error handling
FilamentSocialitePlugin::make()
    ->authorizeUserUsing(function (FilamentSocialitePlugin $plugin, SocialiteUserContract $oauthUser) {
        // Custom authorization logic
        if (!$user = User::where('email', $oauthUser->getEmail())->first()) {
            throw new \Exception('Account not found. Please contact administrator.');
        }
        return true;
    })
```

**5. Rate Limiting:**
```php
// routes/web.php
Route::middleware(['throttle:oauth'])->group(function () {
    // OAuth routes sudah automatic, tapi bisa tambah rate limiting
});

// app/Providers/RouteServiceProvider.php
RateLimiter::for('oauth', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

### 🚀 **Advanced Configuration Examples**

**Multi-tenant dengan OAuth:**
```php
FilamentSocialitePlugin::make()
    ->providers([
        Provider::make('google')
            ->scopes(['email', 'profile', 'openid'])
            ->with([
                'hd' => 'yourcompany.com', // Google Workspace only
            ])
    ])
    ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
        $tenant = Tenant::where('domain', $oauthUser->user['hd'])->first();
        
        return User::create([
            'name' => $oauthUser->getName(),
            'email' => $oauthUser->getEmail(),
            'tenant_id' => $tenant?->id,
        ]);
    })
```

**Custom Redirect Logic:**
```php
FilamentSocialitePlugin::make()
    ->redirectAfterLoginUsing(function (string $provider, FilamentSocialiteUserContract $socialiteUser, FilamentSocialitePlugin $plugin) {
        $user = $socialiteUser->getUser();
        
        // Redirect berdasarkan role
        if ($user->hasRole('admin')) {
            return redirect('/admin/dashboard');
        }
        
        return redirect('/admin/profile'); // First time login
    })
```
