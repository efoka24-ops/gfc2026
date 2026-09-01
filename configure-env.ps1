$php = "C:\Users\YCXL3291\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.1_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

Set-Location "C:\Projects\gfc\api"

$envContent = @"
APP_NAME="GFC Championnat"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=trugro9159_gfc
DB_USERNAME=trugro9159_gfc
DB_PASSWORD=fxTach7b#C?s

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:3000
"@

[System.IO.File]::WriteAllText("C:\Projects\gfc\api\.env", $envContent, [System.Text.Encoding]::UTF8)
Write-Host ".env written"

& $php artisan key:generate 2>&1
Write-Host "Key done"
