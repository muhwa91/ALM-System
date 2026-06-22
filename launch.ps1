Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

$scriptDir = if ($PSScriptRoot -ne "") { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Definition }
Set-Location $scriptDir

function Test-DockerReady {
    try { docker info 2>&1 | Out-Null; return $LASTEXITCODE -eq 0 }
    catch { return $false }
}

function Show-LoadingForm($text) {
    $f = New-Object System.Windows.Forms.Form
    $f.Text = "연차관리 시스템"
    $f.Size = New-Object System.Drawing.Size(320, 110)
    $f.StartPosition = "CenterScreen"
    $f.FormBorderStyle = "FixedDialog"
    $f.ControlBox = $false
    $lbl = New-Object System.Windows.Forms.Label
    $lbl.Text = $text
    $lbl.AutoSize = $false
    $lbl.Size = New-Object System.Drawing.Size(280, 50)
    $lbl.Location = New-Object System.Drawing.Point(20, 25)
    $lbl.TextAlign = [System.Drawing.ContentAlignment]::MiddleCenter
    $f.Controls.Add($lbl)
    $f.Show()
    [System.Windows.Forms.Application]::DoEvents()
    return $f
}

# Docker Desktop 시작
if (-not (Test-DockerReady)) {
    $dockerPaths = @(
        "$env:ProgramFiles\Docker\Docker\Docker Desktop.exe",
        "$env:LOCALAPPDATA\Programs\Docker\Docker\Docker Desktop.exe"
    )
    foreach ($p in $dockerPaths) {
        if (Test-Path $p) { Start-Process $p; break }
    }
    $lf = Show-LoadingForm "Docker를 시작하는 중입니다. 잠시 기다려주세요..."
    $start = Get-Date
    while (-not (Test-DockerReady) -and ((Get-Date) - $start).TotalSeconds -lt 120) {
        [System.Windows.Forms.Application]::DoEvents()
        Start-Sleep -Milliseconds 500
    }
    $lf.Close()
}

# 컨테이너 시작
$lf = Show-LoadingForm "시스템을 시작하는 중입니다. 잠시 기다려주세요..."

$job = Start-Job -ScriptBlock {
    param($dir); Set-Location $dir
    docker compose up -d 2>&1; $LASTEXITCODE
} -ArgumentList $scriptDir

while ($job.State -eq "Running") {
    [System.Windows.Forms.Application]::DoEvents()
    Start-Sleep -Milliseconds 300
}

$out  = Receive-Job $job
$code = $out | Select-Object -Last 1
Remove-Job $job
$lf.Close()

if ($code -ne 0) {
    [System.Windows.Forms.MessageBox]::Show("시스템 시작 실패`n`n$($out -join "`n")", "오류")
    exit 1
}

# 브라우저 열기
Start-Process "http://localhost:8080"

# 트레이 아이콘
$tray = New-Object System.Windows.Forms.NotifyIcon
$tray.Icon    = [System.Drawing.SystemIcons]::Application
$tray.Text    = "연차관리 시스템 실행 중"
$tray.Visible = $true
$tray.ShowBalloonTip(2000, "연차관리 시스템", "시스템이 시작되었습니다.", [System.Windows.Forms.ToolTipIcon]::Info)

$menu   = New-Object System.Windows.Forms.ContextMenuStrip
$mOpen  = $menu.Items.Add("웹 열기")
$mStop  = $menu.Items.Add("종료")

$mOpen.Add_Click({ Start-Process "http://localhost:8080" })
$mStop.Add_Click({
    $tray.Visible = $false
    Set-Location $scriptDir
    docker compose down 2>&1 | Out-Null
    [System.Windows.Forms.Application]::Exit()
})

$tray.ContextMenuStrip = $menu
$tray.add_DoubleClick({ Start-Process "http://localhost:8080" })

[System.Windows.Forms.Application]::Run()
