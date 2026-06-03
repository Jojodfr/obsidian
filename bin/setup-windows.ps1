# setup-windows.ps1 - One-time Windows setup for claude-obsidian
# Run this from the vault root: .\bin\setup-windows.ps1
#
# What this does:
#   1. Creates .vault-meta/ directory
#   2. Detects transport (obsidian-cli, MCP, filesystem fallback)
#   3. Verifies wiki/ structure exists
#   4. Checks for Node.js and offers to install Claude Code CLI
#   5. Imports the PowerShell workflow module
#   6. Reports status

param(
    [switch]$SkipNodeCheck,
    [switch]$ForceTransport
)

$ErrorActionPreference = "Stop"
$VaultRoot = $PWD

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  claude-obsidian Windows Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# --- Step 1: Verify vault root ---
if (-not (Test-Path (Join-Path $VaultRoot "CLAUDE.md"))) {
    Write-Error "This does not appear to be a claude-obsidian vault. No CLAUDE.md found."
    Write-Error "Run this script from the vault root directory."
    exit 1
}

Write-Host "[1/6] Vault root confirmed: $VaultRoot" -ForegroundColor Green

# --- Step 2: Create .vault-meta ---
$metaDir = Join-Path $VaultRoot ".vault-meta"
if (-not (Test-Path $metaDir)) {
    New-Item -ItemType Directory -Path $metaDir -Force | Out-Null
    Write-Host "[2/6] Created .vault-meta/" -ForegroundColor Green
}
else {
    Write-Host "[2/6] .vault-meta/ exists" -ForegroundColor Green
}

# --- Step 3: Detect transport ---
Write-Host "[3/6] Detecting transport..." -ForegroundColor Yellow
$transportScript = Join-Path $VaultRoot "scripts\detect-transport.ps1"
if (Test-Path $transportScript) {
    & $transportScript -VaultRoot $VaultRoot -Force:$ForceTransport
}
else {
    Write-Host "  detect-transport.ps1 not found - creating default transport config..." -ForegroundColor Yellow
    $defaultTransport = @{
        detected_at      = (Get-Date -Format "yyyy-MM-ddTHH:mm:ssZ")
        platform         = "win32"
        preferred        = "filesystem"
        available        = @("filesystem")
        fallback_chain   = @("filesystem")
        manual_override  = $false
    }
    $defaultTransport | ConvertTo-Json -Depth 3 | Set-Content (Join-Path $metaDir "transport.json") -Encoding UTF8
    Write-Host "  Default transport: filesystem (direct file writes)" -ForegroundColor Green
}

# --- Step 4: Verify wiki structure ---
Write-Host "[4/6] Verifying wiki structure..." -ForegroundColor Yellow
$wikiDir = Join-Path $VaultRoot "wiki"
$required = @("index.md", "hot.md", "log.md", "overview.md")
$missing = @()
foreach ($f in $required) {
    $p = Join-Path $wikiDir $f
    if (-not (Test-Path $p)) { $missing += $f }
}
if ($missing.Count -eq 0) {
    $pages = (Get-ChildItem $wikiDir -Recurse -Filter "*.md" -ErrorAction SilentlyContinue).Count
    Write-Host "  OK: $pages pages" -ForegroundColor Green
}
else {
    Write-Host "  MISSING: $($missing -join ', ')" -ForegroundColor Red
    Write-Host "  Run '/wiki' in Claude to scaffold the vault." -ForegroundColor Yellow
}

# --- Step 5: Check Node.js / Claude CLI ---
if (-not $SkipNodeCheck) {
    Write-Host "[5/6] Checking for Claude Code CLI..." -ForegroundColor Yellow
    $node = Get-Command "node" -ErrorAction SilentlyContinue
    $npm = Get-Command "npm" -ErrorAction SilentlyContinue
    $claude = Get-Command "claude" -ErrorAction SilentlyContinue

    if ($claude) {
        Write-Host "  Claude Code CLI found: $($claude.Source)" -ForegroundColor Green
    }
    elseif ($node -and $npm) {
        Write-Host "  Node.js found but Claude CLI not installed." -ForegroundColor Yellow
        Write-Host "  Run: claude-obsidian install-cli  (or: npm install -g @anthropic-ai/claude-code)" -ForegroundColor Gray
    }
    else {
        Write-Host "  Node.js NOT FOUND." -ForegroundColor Red
        Write-Host "  The vault works in 'manual mode' without the CLI." -ForegroundColor Yellow
        Write-Host "  To install the CLI (optional): claude-obsidian install-cli" -ForegroundColor Gray
    }
}

# --- Step 6: Import PowerShell module ---
Write-Host "[6/6] Loading PowerShell workflow module..." -ForegroundColor Yellow
$modulePath = Join-Path $VaultRoot "bin\claude-obsidian.psm1"
if (Test-Path $modulePath) {
    Import-Module $modulePath -Force
    Write-Host "  Module loaded." -ForegroundColor Green
}
else {
    Write-Host "  Module not found at $modulePath" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Setup complete." -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Run status
claude-obsidian status

Write-Host ""
Write-Host "Quick commands:" -ForegroundColor Cyan
Write-Host "  claude-obsidian status          - Check vault health" -ForegroundColor Gray
Write-Host "  claude-obsidian transport-detect - Re-detect transport" -ForegroundColor Gray
Write-Host "  claude-obsidian install-cli     - Install Claude Code CLI" -ForegroundColor Gray
Write-Host "  claude plugin list              - List available skills" -ForegroundColor Gray
Write-Host "  claude-obsidian lock-list       - Show active locks" -ForegroundColor Gray
Write-Host ""
Write-Host "In-chat workflows (just type these to Claude):" -ForegroundColor Cyan
Write-Host "  ingest <filename>               - Add source to wiki" -ForegroundColor Gray
Write-Host "  what do you know about X?       - Query the wiki" -ForegroundColor Gray
Write-Host "  lint the wiki                   - Health check" -ForegroundColor Gray
Write-Host "  /save                           - File this conversation" -ForegroundColor Gray
