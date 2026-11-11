@echo off
setlocal EnableDelayedExpansion
title DADO CBT v1.5.3 - Git Auto Push Utility
color 0B

REM Pastikan direktori kerja berada di folder proyek
cd /d "%~dp0"

echo ==========================================================================
echo         DADO CBT v1.5.3 - GITHUB AUTO PUSH UTILITY [WINDOWS]
echo       Alat Otomatis Upload dan Sinkronisasi Kode Sumber ke GitHub
echo ==========================================================================
echo.

REM 1. Verifikasi Git Terpasang
where git >nul 2>nul
if %errorlevel% neq 0 (
    if exist "%ProgramFiles%\Git\cmd\git.exe" (
        set "PATH=%ProgramFiles%\Git\cmd;%PATH%"
    ) else if exist "%ProgramFiles(x86)%\Git\cmd\git.exe" (
        set "PATH=%ProgramFiles(x86)%\Git\cmd;%PATH%"
    ) else if exist "%LocalAppData%\Programs\Git\cmd\git.exe" (
        set "PATH=%LocalAppData%\Programs\Git\cmd;%PATH%"
    ) else (
        color 0C
        echo [ERROR] Git tidak ditemukan di komputer ini!
        echo Silakan unduh dan pasang Git for Windows terlebih dahulu dari:
        echo https://git-scm.com/download/win
        echo.
        pause
        exit /b 1
    )
)

echo [V] Git terdeteksi:
git --version
echo.

REM 2. Cek apakah repositori git sudah diinisialisasi
if not exist ".git" (
    echo [!] Repositori Git lokal belum diinisialisasi di folder ini.
    echo [*] Menginisialisasi repositori Git lokal - Branch: main...
    git init
    git branch -M main
    echo [V] Repositori Git lokal berhasil diinisialisasi.
    echo.
)

REM 3. Cek Remote Origin
git remote get-url origin >nul 2>nul
if %errorlevel% neq 0 (
    echo [!] Remote repository 'origin' belum terpasang.
    echo Silakan masukkan URL repository GitHub Anda.
    echo Contoh: https://github.com/username/dadocbt.git
    echo.
    set /p REPO_URL="URL Repository GitHub: "
    if "!REPO_URL!"=="" (
        color 0C
        echo [ERROR] URL repository tidak boleh kosong!
        pause
        exit /b 1
    )
    git remote add origin !REPO_URL!
    echo [V] Remote origin berhasil ditambahkan: !REPO_URL!
    echo.
) else (
    for /f "delims=" %%r in ('git remote get-url origin') do set CURRENT_REMOTE=%%r
    echo [V] Remote origin terpasang: !CURRENT_REMOTE!
    echo.
)

REM 4. Tampilkan Status Berkas yang Berubah
echo --------------------------------------------------------------------------
echo Status Berkas - Perubahan atau File Baru:
echo --------------------------------------------------------------------------
git status -s
echo --------------------------------------------------------------------------
echo.

REM 5. Input Pesan Commit
for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set CDATE=%%a-%%b-%%c
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set CTIME=%%a:%%b
set DEFAULT_MSG=Update DADO CBT v1.5.3: %CDATE% %CTIME%

set /p COMMIT_INPUT="Pesan commit [Enter untuk: '%DEFAULT_MSG%']: "
if "!COMMIT_INPUT!"=="" (
    set FINAL_MSG=!DEFAULT_MSG!
) else (
    set FINAL_MSG=!COMMIT_INPUT!
)

REM 6. Stage & Set Executable Bit untuk Skrip Linux
echo.
echo [*] Menambahkan seluruh file ke staging area...
git add -A

REM Pastikan skrip bash Linux memiliki bit eksekusi (+x) di index Git
git update-index --chmod=+x autoinstall.sh 2>nul
git update-index --chmod=+x deploy/install-vps.sh 2>nul
git update-index --chmod=+x deploy/update.sh 2>nul
git update-index --chmod=+x deploy/backup.sh 2>nul
git update-index --chmod=+x deploy/status.sh 2>nul

echo [*] Melakukan commit berkas...
git commit -m "!FINAL_MSG!"
if %errorlevel% neq 0 (
    echo [i] Tidak ada perubahan berkas baru untuk di-commit.
)

REM 7. Push ke GitHub
echo.
echo ==========================================================================
echo [*] Mengunggah perubahan ke GitHub - origin main...
echo ==========================================================================
git branch -M main
git push -u origin main

if %errorlevel% equ 0 goto push_success

:push_failed
color 0E
echo.
echo [!] Push ke GitHub gagal atau ditolak.
echo Kemungkinan repository di GitHub memiliki file baru (misal README atau LICENSE).
echo.
echo Pilih tindakan:
echo   [1] Pull dan Rebase lalu push ulang (Disarankan)
echo   [2] Force Push (Timpa isi repository GitHub dengan file lokal ini)
echo   [3] Selesai / Keluar
echo.
set /p PUSH_CHOICE="Pilihan [1/2/3]: "

if "%PUSH_CHOICE%"=="1" (
    echo.
    echo [*] Menjalankan git pull origin main --rebase...
    git pull origin main --rebase
    echo [*] Mengulang push...
    git push -u origin main
    if !errorlevel! equ 0 goto push_success
    echo [!] Push masih gagal. Silakan periksa koneksi atau izin repository Anda.
    pause
    exit /b 1
)

if "%PUSH_CHOICE%"=="2" (
    echo.
    echo [*] Menjalankan git push -u origin main --force...
    git push -u origin main --force
    if !errorlevel! equ 0 goto push_success
    echo [!] Force push gagal. Pastikan branch main tidak diproteksi di GitHub.
    pause
    exit /b 1
)

echo.
echo [!] Proses push dihentikan.
pause
exit /b 0

:push_success
color 0A
echo.
echo ==========================================================================
echo          BERHASIL: KODE DADO CBT BERHASIL DIUNGGAH KE GITHUB!
echo ==========================================================================
for /f "delims=" %%r in ('git remote get-url origin 2^>nul') do set CURRENT_REMOTE=%%r
for /f "delims=" %%c in ('git log -1 --pretty^=format:"%%h - %%s" 2^>nul') do set LAST_C=%%c
if not "!CURRENT_REMOTE!"=="" echo  Repository : !CURRENT_REMOTE!
echo  Branch     : main
if not "!LAST_C!"=="" echo  Commit     : !LAST_C!
echo.
echo Tips Pemasangan Baru di VPS Ubuntu:
echo  1. Login SSH ke VPS: ssh root@ip-vps
echo  2. Clone repo : git clone !CURRENT_REMOTE! dadocbt
echo  3. Masuk & run: cd dadocbt ^&^& sudo bash autoinstall.sh
echo.
echo Tips Otomasi VPS:
echo  1. Jika VPS telah dipasang Webhook, server VPS Anda akan otomatis
echo     ter-update dalam beberapa detik setiap kali push!
echo  2. Atau jalankan 'sudo dadocbt-update' di terminal SSH VPS Anda.
echo ==========================================================================
echo.
pause
