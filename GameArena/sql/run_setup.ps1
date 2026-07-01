$env:ORACLE_HOME = "E:\DB_project\dbhomeXE"

$scriptDir = "C:\Users\Shahariar Emon\Desktop\DB_Project\GameArena\sql"

$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = "E:\DB_project\dbhomeXE\bin\sqlplus.exe"
$psi.Arguments = "-S system/saikat112@localhost:1521/XEPDB1"
$psi.RedirectStandardInput = $true
$psi.RedirectStandardOutput = $true
$psi.RedirectStandardError = $true
$psi.UseShellExecute = $false
$psi.CreateNoWindow = $true

$proc = [System.Diagnostics.Process]::Start($psi)

# Run create_user.sql
$sql = Get-Content "$scriptDir\create_user.sql" -Raw
$proc.StandardInput.WriteLine($sql)
$proc.StandardInput.WriteLine("EXIT")

$proc.StandardInput.Close()

$stdout = $proc.StandardOutput.ReadToEnd()
$stdErrText = $proc.StandardError.ReadToEnd()
$proc.WaitForExit()

Write-Output "=== CREATE USER OUTPUT ==="
Write-Output $stdout
if ($stdErrText) { Write-Output "Errors: $stdErrText" }