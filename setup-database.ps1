# Script de Configuracion Laravel

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Configuracion de Base de Datos" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Paso 1: Crear archivo de base de datos SQLite
Write-Host "Paso 1: Creando archivo de base de datos SQLite..." -ForegroundColor Yellow
$dbPath = "c:\STRIKE\api-prueba\database\smartschool_db"

if (Test-Path $dbPath) {
    Write-Host "  El archivo ya existe" -ForegroundColor Gray
}
else {
    New-Item -Path $dbPath -ItemType File -Force | Out-Null
    Write-Host "  Archivo creado OK" -ForegroundColor Green
}
Write-Host ""

# Paso 2: Detectar PHP
Write-Host "Paso 2: Buscando PHP..." -ForegroundColor Yellow
$phpPaths = @(
    "php",
    "C:\xampp\php\php.exe",
    "C:\laragon\bin\php\php.exe",
    "C:\Program Files\PHP\php.exe"
)

$phpCommand = $null
foreach ($path in $phpPaths) {
    try {
        $ErrorActionPreference = "Stop"
        $output = & $path --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            $phpCommand = $path
            Write-Host "  PHP encontrado: $path" -ForegroundColor Green
            break
        }
    } 
    catch {
        continue
    }
}

if ($null -eq $phpCommand) {
    Write-Host "  PHP no encontrado" -ForegroundColor Red
    Write-Host ""
    Write-Host "Soluciones posibles:" -ForegroundColor Yellow
    Write-Host "  1. Instalar XAMPP: https://www.apachefriends.org/"
    Write-Host "  2. Instalar Laragon: https://laragon.org/"
    Write-Host "  3. Instalar PHP: https://windows.php.net/download/"
    Write-Host ""
    Read-Host "Presiona Enter para salir"
    exit 1
}
Write-Host ""

# Paso 3: Ejecutar migraciones
Write-Host "Paso 3: Ejecutando migraciones..." -ForegroundColor Yellow
Set-Location "c:\STRIKE\api-prueba"

$ErrorActionPreference = "Continue"
& $phpCommand artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "  Migraciones OK" -ForegroundColor Green
}
else {
    Write-Host "  Error en migraciones" -ForegroundColor Red
}
Write-Host ""

# Paso 4: Ejecutar seeders
Write-Host "Paso 4: Creando datos de prueba..." -ForegroundColor Yellow
& $phpCommand artisan db:seed --class=StudentSeeder --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "  Datos creados OK" -ForegroundColor Green
}
else {
    Write-Host "  Error en seeder" -ForegroundColor Red
}
Write-Host ""

# Paso 5: Verificar
Write-Host "Paso 5: Verificando..." -ForegroundColor Yellow
& $phpCommand artisan migrate:status
Write-Host ""

# Resumen
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Configuracion Completada!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Proximos pasos:" -ForegroundColor Yellow
Write-Host "  1. Inicia el servidor:" -ForegroundColor White
Write-Host "     $phpCommand artisan serve --host=192.168.1.89 --port=8000" -ForegroundColor Gray
Write-Host ""
Write-Host "  2. Prueba el endpoint:" -ForegroundColor White
Write-Host "     http://192.168.1.89:8000/api/alumnos" -ForegroundColor Gray
Write-Host ""

Read-Host "Presiona Enter para salir"
