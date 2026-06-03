# detect-transport.ps1 - Windows-compatible transport detection for claude-obsidian
# Mirrors scripts/detect-transport.sh semantics on PowerShell.
# Writes .vault-meta/transport.json with the detected transport chain.
#
# Fallback chain: Obsidian CLI → mcp-obsidian → mcpvault → filesystem (always works)

param(
    [string]$VaultRoot = $PWD,
    [switch]$Force
)

$ErrorActionPreference = "Stop"

$MetaDir = Join-Path $VaultRoot ".vault-meta"
$TransportFile = Join-Path $MetaDir "transport.json"

# Ensure .vault-meta exists
if (-not (Test-Path $MetaDir)) {
    New-Item -ItemType Directory -Path $MetaDir -Force | Out-Null
}

# Check for manual override
if ((Test-Path $TransportFile) -and (-not $Force)) {
    $existing = Get-Content $TransportFile -Raw | ConvertFrom-Json
    if ($existing.manual_override -eq $true) {
        Write-Host "Transport manually overridden. Use -Force to re-detect." -ForegroundColor Yellow
        Write-Host "Preferred: $($existing.preferred)" -ForegroundColor Gray
        exit 0
    }
}

$transport = @{
    detected_at   = (Get-Date -Format "yyyy-MM-ddTHH:mm:ssZ")
    platform      = "win32"
    preferred     = "filesystem"
    available     = @("filesystem")
    fallback_chain = @("filesystem")
    manual_override = $false
}

# --- Tier 1: Obsidian CLI ---
$cli = Get-Command "obsidian-cli" -ErrorAction SilentlyContinue
if ($cli) {
    $transport.preferred = "cli"
    $transport.available += "cli"
    $transport.cli_path = $cli.Source
    $transport.fallback_chain = @("cli", "filesystem")
    Write-Host "Obsidian CLI detected: $($cli.Source)" -ForegroundColor Green
}

# --- Tier 2: MCP servers (check environment / config) ---
$mcpConfig = Join-Path (Join-Path $env:USERPROFILE ".claude") "settings.json"
$mcpFound = $false
if (Test-Path $mcpConfig) {
    try {
        $mcpSettings = Get-Content $mcpConfig -Raw | ConvertFrom-Json
        if ($mcpSettings.mcpServers -or $mcpSettings.mcp) {
            $mcpFound = $true
        }
    }
    catch {
        # ignore parse errors
    }
}
if ($env:CLAUDE_MCP_OBSIDIAN -or $env:MCP_OBSIDIAN_HOST -or $mcpFound) {
    $transport.available += "mcp-obsidian"
    if ($transport.preferred -eq "filesystem") {
        $transport.preferred = "mcp-obsidian"
        $transport.fallback_chain = @("mcp-obsidian", "filesystem")
    }
    Write-Host "MCP transport available" -ForegroundColor Green
}

# --- Tier 3: mcpvault ---
$mcpvault = Get-Command "npx" -ErrorAction SilentlyContinue
if ($mcpvault) {
    $transport.available += "mcpvault"
}

# Write transport.json
$transport | ConvertTo-Json -Depth 4 | Set-Content $TransportFile -Encoding UTF8

Write-Host "" -ForegroundColor White
Write-Host "Transport detected: $($transport.preferred)" -ForegroundColor Cyan
Write-Host "Available: $($transport.available -join ', ')" -ForegroundColor Gray
Write-Host "Config: $TransportFile" -ForegroundColor Gray

if ($transport.preferred -eq "filesystem") {
    Write-Host "" -ForegroundColor White
    Write-Host "NOTE: Using filesystem transport (fallback). Vault mutations use direct file writes." -ForegroundColor Yellow
    Write-Host "      This is fully functional but bypasses Obsidian's real-time sync." -ForegroundColor DarkGray
}
