# 1860.ai

[English Version Below](#english)

---

## فارسی (Persian)

این پروژه یک اپلیکیشن وب مدرن است که با استفاده از آخرین تکنولوژی‌های اکوسیستم لاراول توسعه یافته است.

### تکنولوژی‌های مورد استفاده
- **Backend:** PHP 8.4, Laravel 13
- **Frontend:** TailwindCSS, AlpineJS
- **Livewire:** Version 4 (Beta)
- **UI Library:** FluxUI Pro
- **Icons:** Lucide Icons

### پیش‌نیازها
قبل از نصب، مطمئن شوید موارد زیر روی سیستم شما نصب است:
- PHP >= 8.4
- Composer
- Node.js & NPM
- MySQL یا هر پایگاه داده مورد حمایت لاراول

### مراحل نصب

1. **کلون کردن پروژه:**
   ```bash
   git clone <repository-url>
   cd <project-folder>
   ```

2. **تنظیمات فایل محیطی:**
   فایل `.env.example` را به `.env` کپی کرده و اطلاعات پایگاه داده خود را در آن وارد کنید.
   ```bash
   cp .env.example .env
   ```

3. **نصب وابستگی‌ها و راه‌اندازی سریع:**
   این پروژه دارای یک اسکریپت آماده برای نصب است که تمام مراحل (نصب پکیج‌ها، ایجاد کلید، مهاجرت دیتابیس و ساخت فایل‌های فرانت‌اند) را انجام می‌دهد:
   ```bash
   composer run setup
   ```

   *نکته: اگر می‌خواهید مراحل را دستی انجام دهید:*
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

4. **اجرای پروژه:**
   ```bash
   php artisan serve
   ```
   و در ترمینال دیگر برای فایل‌های فرانت‌‌اند:
   ```bash
   npm run dev
   ```

---

<a name="english"></a>
## English

This project is a modern web application developed using the latest technologies in the Laravel ecosystem.

### Tech Stack
- **Backend:** PHP 8.4, Laravel 13
- **Frontend:** TailwindCSS, AlpineJS
- **Livewire:** Version 4 (Beta)
- **UI Library:** FluxUI Pro
- **Icons:** Lucide Icons

### Prerequisites
Before installation, ensure you have the following installed:
- PHP >= 8.4
- Composer
- Node.js & NPM
- MySQL or any Laravel-supported database

### Installation Steps

1. **Clone the Repository:**
   ```bash
   git clone <repository-url>
   cd <project-folder>
   ```

2. **Environment Configuration:**
   Copy `.env.example` to `.env` and configure your database settings.
   ```bash
   cp .env.example .env
   ```

3. **Install Dependencies & Quick Setup:**
   The project includes a setup script that handles everything (installing packages, generating keys, running migrations, and building assets):
   ```bash
   composer run setup
   ```

   *Note: If you prefer manual installation:*
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

4. **Running the Application:**
   ```bash
   php artisan serve
   ```
   And in another terminal for assets:
   ```bash
   npm run dev
   ```

---

### ⚠️ Important Note / نکته مهم

**English:** You MUST purchase a **FluxUI Pro** license to use this software. This project relies on Pro components that require a valid license key from [fluxui.dev](https://fluxui.dev/).

**فارسی:** برای استفاده از این نرم‌افزار، شما الزماً باید لایسنس **FluxUI Pro** را خریداری کنید. این پروژه از اجزای نسخه Pro استفاده می‌کند که نیازمند کلید لایسنس معتبر از سایت [fluxui.dev](https://fluxui.dev/) می‌باشد.
