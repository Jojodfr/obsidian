# claude-obsidian.ps1 - PowerShell workflow module for claude-obsidian on Windows
# Usage: Import-Module .\bin\claude-obsidian.ps1
#        claude-obsidian status
#        claude-obsidian ingest <source>
#        claude-obsidian save
#        claude-obsidian query <question>
#
# This module provides the key claude-obsidian commands when the full
# Claude Code CLI (npm install -g @anthropic-ai/claude-code) is not installed.

function claude-obsidian {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true, Position = 0)]
        [ValidateSet(
            "status",
            "ingest",
            "query",
            "lint",
            "save",
            "lock-acquire",
            "lock-release",
            "lock-list",
            "lock-clear",
            "transport-detect",
            "install-cli"
        )]
        [string]$Command,

        [Parameter(Position = 1)]
        [string]$Argument,

        [switch]$Help
    )

    $VaultRoot = $PWD
    $MetaDir = Join-Path $VaultRoot ".vault-meta"
    $TransportFile = Join-Path $MetaDir "transport.json"

    function Get-VaultStatus {
        $ok = $true
        Write-Host "=== claude-obsidian Vault Status ===" -ForegroundColor Cyan

        # Check wiki/ exists
        if (Test-Path (Join-Path $VaultRoot "wiki")) {
            $pages = (Get-ChildItem (Join-Path $VaultRoot "wiki") -Recurse -Filter "*.md" -ErrorAction SilentlyContinue).Count
            Write-Host "  wiki/:        OK ($($pages) pages found)" -ForegroundColor Green
        }
        else {
            Write-Host "  wiki/:        MISSING (run /wiki to scaffold)" -ForegroundColor Red
            $ok = $false
        }

        # Check .raw/ exists
        if (Test-Path (Join-Path $VaultRoot ".raw")) {
            $raw = (Get-ChildItem (Join-Path $VaultRoot ".raw") -Filter "*.md" -ErrorAction SilentlyContinue).Count
            Write-Host "  .raw/:        OK ($raw sources)" -ForegroundColor Green
        }
        else {
            Write-Host "  .raw/:        MISSING" -ForegroundColor Yellow
        }

        # Check transport
        if (Test-Path $TransportFile) {
            $t = Get-Content $TransportFile -Raw | ConvertFrom-Json
            Write-Host "  transport:     $($t.preferred)" -ForegroundColor Green
        }
        else {
            Write-Host "  transport:     NOT DETECTED (run: claude-obsidian transport-detect)" -ForegroundColor Yellow
        }

        # Check locks
        $lockDir = Join-Path $MetaDir "locks"
        if (Test-Path $lockDir) {
            $locks = (Get-ChildItem $lockDir -Filter "*.lock" -ErrorAction SilentlyContinue).Count
            Write-Host "  locks:         $locks active" -ForegroundColor $(if ($locks -gt 0) { "Yellow" } else { "Green" })
        }
        else {
            Write-Host "  locks:         none" -ForegroundColor Green
        }

        # Check Node.js / claude CLI
        $node = Get-Command "node" -ErrorAction SilentlyContinue
        $npm = Get-Command "npm" -ErrorAction SilentlyContinue
        $claude = Get-Command "claude" -ErrorAction SilentlyContinue
        if ($claude) {
            Write-Host "  claude CLI:    $($claude.Source)" -ForegroundColor Green
        }
        elseif ($node -and $npm) {
            Write-Host "  claude CLI:    NOT INSTALLED (Node.js found - run: claude-obsidian install-cli)" -ForegroundColor Yellow
        }
        else {
            Write-Host "  claude CLI:    NOT INSTALLED (Node.js not found - run: claude-obsidian install-cli)" -ForegroundColor Red
        }

        # Check plugins
        $excalidraw = Test-Path (Join-Path $VaultRoot ".obsidian\plugins\obsidian-excalidraw-plugin\main.js")
        Write-Host "  excalidraw:    $(if ($excalidraw) { "OK" } else { "MISSING main.js" })" -ForegroundColor $(if ($excalidraw) { "Green" } else { "Yellow" })

        return $ok
    }

    switch ($Command) {
        "status" {
            Get-VaultStatus
        }

        "transport-detect" {
            $script = Join-Path $VaultRoot "scripts\detect-transport.ps1"
            if (Test-Path $script) {
                & $script -VaultRoot $VaultRoot
            }
            else {
                Write-Error "detect-transport.ps1 not found at $script"
            }
        }

        "lock-acquire" {
            if (-not $Argument) { Write-Error "Usage: claude-obsidian lock-acquire <path>"; exit 1 }
            $script = Join-Path $VaultRoot "scripts\wiki-lock.ps1"
            & $script -Action acquire -LockPath $Argument
        }

        "lock-release" {
            if (-not $Argument) { Write-Error "Usage: claude-obsidian lock-release <path>"; exit 1 }
            $script = Join-Path $VaultRoot "scripts\wiki-lock.ps1"
            & $script -Action release -LockPath $Argument
        }

        "lock-list" {
            $script = Join-Path $VaultRoot "scripts\wiki-lock.ps1"
            & $script -Action list
        }

        "lock-clear" {
            $script = Join-Path $VaultRoot "scripts\wiki-lock.ps1"
            & $script -Action clear-stale
        }

        "install-cli" {
            Write-Host "=== Installing Claude Code CLI ===" -ForegroundColor Cyan
            $node = Get-Command "node" -ErrorAction SilentlyContinue
            if (-not $node) {
                Write-Host "Node.js not found. Installing via winget..." -ForegroundColor Yellow
                winget install OpenJS.NodeJS --accept-source-agreements --accept-package-agreements
                if ($LASTEXITCODE -ne 0) {
                    Write-Error "Node.js installation failed. Install manually from https://nodejs.org"
                    exit 1
                }
                Write-Host "Node.js installed. Restart your terminal and run this command again." -ForegroundColor Green
                exit 0
            }
            Write-Host "Node.js found: $($node.Source)" -ForegroundColor Green
            Write-Host "Installing Claude Code CLI..." -ForegroundColor Yellow
            npm install -g @anthropic-ai/claude-code
            if ($LASTEXITCODE -eq 0) {
                Write-Host "Claude Code CLI installed successfully!" -ForegroundColor Green
                Write-Host "Run: claude --version" -ForegroundColor Gray
            }
            else {
                Write-Error "Installation failed. Check npm output above."
            }
        }

        "ingest" {
            Write-Host "=== Ingest Workflow ===" -ForegroundColor Cyan
            if (-not $Argument) {
                Write-Error "Usage: claude-obsidian ingest <filename-or-path>"
                Write-Host "  The source file should be in .raw/ or a path you specify."
                Write-Host "  Then tell Claude: 'ingest <filename>' in the chat."
                exit 1
            }
            Write-Host "To ingest '$Argument', tell Claude in this chat:" -ForegroundColor Yellow
            Write-Host "  ingest $Argument" -ForegroundColor White
        }

        "query" {
            Write-Host "=== Query Workflow ===" -ForegroundColor Cyan
            if (-not $Argument) {
                Write-Error "Usage: claude-obsidian query <question>"
                Write-Host "  Tell Claude in this chat: 'what do you know about <topic>?'"
                exit 1
            }
            Write-Host "To query, tell Claude in this chat:" -ForegroundColor Yellow
            Write-Host "  what do you know about $Argument?" -ForegroundColor White
        }

        "lint" {
            Write-Host "=== Lint Workflow ===" -ForegroundColor Cyan
            Write-Host "Tell Claude in this chat: 'lint the wiki'" -ForegroundColor Yellow
        }

        "save" {
            Write-Host "=== Save Workflow ===" -ForegroundColor Cyan
            Write-Host "Tell Claude in this chat: '/save'" -ForegroundColor Yellow
        }
    }
}

# Also expose as a simpler "claude" function for convenience
function claude {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory = $true, Position = 0)]
        [string]$SubCommand,

        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]]$Rest
    )

    switch ($SubCommand) {
        "plugin" {
            switch ($Rest[0]) {
                "list" {
                    Write-Host "=== claude-obsidian Skills ===" -ForegroundColor Cyan
                    $skillDirs = Get-ChildItem (Join-Path $PWD "skills") -Directory -ErrorAction SilentlyContinue
                    if (-not $skillDirs) {
                        Write-Host "  No skills directory found." -ForegroundColor Red
                        return
                    }
                    foreach ($dir in $skillDirs) {
                        $skillFile = Join-Path $dir.FullName "SKILL.md"
                        if (Test-Path $skillFile) {
                            $name = $dir.Name
                            $lines = Get-Content $skillFile -TotalCount 10
                            $descLine = $lines | Select-String "^description:" | Select-Object -First 1
                            $desc = if ($descLine) { ($descLine -split "description:")[1].Trim().Trim("'").Trim('"') } else { "" }
                            Write-Host "  $name" -ForegroundColor White -NoNewline
                            if ($desc) { Write-Host "  - $desc" -ForegroundColor Gray }
                            else { Write-Host "" }
                        }
                    }
                }
                "install" {
                    Write-Host "The Claude Code CLI is not installed in this environment." -ForegroundColor Red
                    Write-Host "" -ForegroundColor White
                    Write-Host "To get the full CLI with skill routing and hooks:" -ForegroundColor Yellow
                    Write-Host "  claude-obsidian install-cli" -ForegroundColor White
                    Write-Host "" -ForegroundColor White
                    Write-Host "For now, use the manual workflow:" -ForegroundColor Yellow
                    Write-Host "  - Ingest:  say 'ingest <filename>' in this chat" -ForegroundColor Gray
                    Write-Host "  - Query:   say 'what do you know about X?'" -ForegroundColor Gray
                    Write-Host "  - Save:    say '/save' in this chat" -ForegroundColor Gray
                    Write-Host "  - Status:  claude-obsidian status" -ForegroundColor Gray
                }
                "marketplace" {
                    Write-Host "Marketplace requires the Claude Code CLI." -ForegroundColor Red
                    Write-Host "Install: claude-obsidian install-cli" -ForegroundColor Yellow
                }
                default {
                    Write-Host "Usage: claude plugin <list|install|marketplace>" -ForegroundColor Yellow
                }
            }
        }
        "mcp" {
            Write-Host "MCP configuration requires the Claude Code CLI." -ForegroundColor Yellow
            Write-Host "Manual setup: skills/wiki/references/mcp-setup.md" -ForegroundColor Gray
        }
        default {
            Write-Host "Unknown command: $SubCommand" -ForegroundColor Red
            Write-Host "Use 'claude plugin list' for available skills, or 'claude-obsidian status' for vault status." -ForegroundColor Yellow
        }
    }
}

Export-ModuleMember -Function claude-obsidian, claude

Write-Host "claude-obsidian PowerShell module loaded." -ForegroundColor Green
Write-Host "Commands:  claude-obsidian status | ingest | query | lint | save | install-cli" -ForegroundColor Gray
Write-Host "           claude plugin list | install | marketplace" -ForegroundColor Gray
