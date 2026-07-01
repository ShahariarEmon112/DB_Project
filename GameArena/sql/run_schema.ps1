$env:ORACLE_HOME = "E:\DB_project\dbhomeXE"

$scriptDir = "C:\Users\Shahariar Emon\Desktop\DB_Project\GameArena\sql"

function Run-SQLFile {
    param([string]$filePath, [string]$user = "gamearena/gamearena123@localhost:1521/XEPDB1")
    
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = "E:\DB_project\dbhomeXE\bin\sqlplus.exe"
    $psi.Arguments = "-S $user"
    $psi.RedirectStandardInput = $true
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    $psi.UseShellExecute = $false
    $psi.CreateNoWindow = $true

    $proc = [System.Diagnostics.Process]::Start($psi)

    $sql = Get-Content $filePath -Raw
    $proc.StandardInput.WriteLine($sql)
    $proc.StandardInput.WriteLine("EXIT")

    $proc.StandardInput.Close()

    $stdout = $proc.StandardOutput.ReadToEnd()
    $stdErrText = $proc.StandardError.ReadToEnd()
    $proc.WaitForExit()

    Write-Output "=== Running: $(Split-Path $filePath -Leaf) ==="
    Write-Output $stdout
    if ($stdErrText) { Write-Output "Errors: $stdErrText" }
    Write-Output ""
}

# Run schema.sql
Run-SQLFile "$scriptDir\schema.sql"