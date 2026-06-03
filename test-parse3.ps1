$ForceTransport = $false
$transportScript = "scripts\detect-transport.ps1"
if (Test-Path $transportScript) {
    & $transportScript -VaultRoot $PWD -Force:$ForceTransport
}
else {
    Write-Host "not found"
}
