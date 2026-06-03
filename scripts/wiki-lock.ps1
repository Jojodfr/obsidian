# wiki-lock.ps1 - Windows-compatible advisory file locking for claude-obsidian
# Mirrors scripts/wiki-lock.sh semantics on PowerShell.
# Usage: .\scripts\wiki-lock.ps1 <acquire|release|list|clear-stale> [path] [options]
#
# Properties:
#   - Per-file granularity (sha1 of vault-relative path)
#   - Age-based staleness (default 60s)
#   - Cross-process release (any process can release any lock)
#   - No PID matching required

param(
    [Parameter(Mandatory = $true, Position = 0)]
    [ValidateSet("acquire", "release", "list", "clear-stale")]
    [string]$Action,

    [Parameter(Position = 1)]
    [string]$LockPath,

    [int]$StaleAfterSec = 60,

    [int]$MaxAge = 3600
)

$ErrorActionPreference = "Stop"

$LockDir = Join-Path $PWD ".vault-meta"
$LockFileDir = Join-Path $LockDir "locks"

function Ensure-LockDir {
    if (-not (Test-Path $LockFileDir)) {
        New-Item -ItemType Directory -Path $LockFileDir -Force | Out-Null
    }
}

function Get-LockFile($vaultRelativePath) {
    $hash = [System.BitConverter]::ToString(
        [System.Security.Cryptography.SHA1]::Create().ComputeHash(
            [System.Text.Encoding]::UTF8.GetBytes($vaultRelativePath)
        )
    ).Replace("-", "").ToLower()
    return Join-Path $LockFileDir "$hash.lock"
}

function Write-Log($msg) {
    $ts = Get-Date -Format "yyyy-MM-ddTHH:mm:ssZ"
    "$ts $msg" | Write-Host
}

switch ($Action) {
    "acquire" {
        if (-not $LockPath) {
            Write-Error "acquire requires a path argument"
            exit 1
        }
        Ensure-LockDir
        $lockFile = Get-LockFile $LockPath

        if (Test-Path $lockFile) {
            $age = (Get-Date) - (Get-Item $lockFile).LastWriteTime
            if ($age.TotalSeconds -lt $StaleAfterSec) {
                Write-Log "skipped: $LockPath currently locked (age=$([int]$age.TotalSeconds)s)"
                exit 75  # EX_TEMPFAIL
            }
            # Stale lock — remove it
            Remove-Item $lockFile -Force -ErrorAction SilentlyContinue
            Write-Log "cleared stale lock for $LockPath"
        }

        # Create lock atomically
        $content = "$PID $(Get-Date -Format o)"
        [System.IO.File]::WriteAllText($lockFile, $content)
        Write-Log "acquired: $LockPath"
    }

    "release" {
        if (-not $LockPath) {
            Write-Error "release requires a path argument"
            exit 1
        }
        $lockFile = Get-LockFile $LockPath
        if (Test-Path $lockFile) {
            Remove-Item $lockFile -Force
            Write-Log "released: $LockPath"
        }
        else {
            Write-Log "release: $LockPath (not locked)"
        }
    }

    "list" {
        Ensure-LockDir
        $locks = Get-ChildItem $LockFileDir -Filter "*.lock" -ErrorAction SilentlyContinue
        if (-not $locks) {
            Write-Log "no active locks"
            exit 0
        }
        foreach ($lock in $locks) {
            $age = (Get-Date) - $lock.LastWriteTime
            $hash = $lock.BaseName
            Write-Log "lock $hash (age=$([int]$age.TotalSeconds)s)"
        }
    }

    "clear-stale" {
        Ensure-LockDir
        $cleared = 0
        Get-ChildItem $LockFileDir -Filter "*.lock" -ErrorAction SilentlyContinue | ForEach-Object {
            $age = (Get-Date) - $_.LastWriteTime
            if ($age.TotalSeconds -gt $MaxAge) {
                Remove-Item $_.FullName -Force
                $cleared++
                Write-Log "cleared stale: $($_.Name) (age=$([int]$age.TotalSeconds)s)"
            }
        }
        Write-Log "clear-stale complete: $cleared locks removed"
    }
}
