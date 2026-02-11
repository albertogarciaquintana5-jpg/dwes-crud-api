param(
    [switch]$KeepServer
)

# Script para pruebas rápidas: aplica migración, arranca servidor temporal, hace login y lista usuarios
$root = Split-Path -Parent $MyInvocation.MyCommand.Definition
Push-Location $root

Write-Output "Ejecutando seed.php..."
php scripts/seed.php

Write-Output "Arrancando servidor PHP integrado en background..."
$phpProc = Start-Process -FilePath php -ArgumentList '-S 127.0.0.1:8000 -t public' -WindowStyle Hidden -PassThru
Start-Sleep -Seconds 1

Write-Output "Haciendo login con Pilar (pilar@example.com / 123456)..."
$body = @{ email = 'pilar@example.com'; password = '123456' } | ConvertTo-Json
try {
    $res = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/auth/login' -Method POST -Body $body -ContentType 'application/json' -ErrorAction Stop
    $token = $res.token
    Write-Output "Token obtenido: $token"
    Write-Output "Pidiendo /users..."
    $users = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/users' -Method GET -Headers @{ Authorization = "Bearer $token" } -ErrorAction Stop
    Write-Output "--- Usuarios recibidos ---"
    $users | ConvertTo-Json -Depth 5 | Write-Output
} catch {
    Write-Error "Error durante la prueba: $_"
}

if (-not $KeepServer) {
    Write-Output "Parando servidor..."
    try { Stop-Process -Id $phpProc.Id -ErrorAction SilentlyContinue } catch {}
} else {
    Write-Output "Servidor en ejecución (PID: $($phpProc.Id)). Para pararlo: Stop-Process -Id $($phpProc.Id)"
}

Pop-Location
