# Docker 컨테이너 접속 명령어 설정
. $PSScriptRoot\docker_alias.ps1

Write-Host "Docker container commands" -ForegroundColor Yellow
Write-Host ""

# frontend
Write-Host " Frontend container connect : " -ForegroundColor White -NoNewline
Write-Host "front_connect" -ForegroundColor Green
Write-Host " Frontend container delete  : " -ForegroundColor White -NoNewline
Write-Host "front_delete" -ForegroundColor Green
Write-Host ""

# backend
Write-Host " Backend container connect  : " -ForegroundColor White -NoNewline
Write-Host "back_connect" -ForegroundColor Cyan
Write-Host " Backend container delete   : " -ForegroundColor White -NoNewline
Write-Host "back_delete" -ForegroundColor Cyan
Write-Host ""

# database
Write-Host " mariadb container connect : " -ForegroundColor White -NoNewline
Write-Host "db_connect" -ForegroundColor Magenta
Write-Host " mariadb container delete  : " -ForegroundColor White -NoNewline
Write-Host "db_delete" -ForegroundColor Magenta
Write-Host ""

# nginx
Write-Host " Nginx container connect    : " -ForegroundColor White -NoNewline
Write-Host "nginx_connect" -ForegroundColor Blue
Write-Host " Nginx container delete     : " -ForegroundColor White -NoNewline
Write-Host "nginx_delete" -ForegroundColor Blue
Write-Host ""

# 최초 세팅용
Write-Host " Setup container Up         : " -ForegroundColor White -NoNewline
Write-Host "set_up" -ForegroundColor DarkYellow
Write-Host " Setup container down       : " -ForegroundColor White -NoNewline
Write-Host "set_down" -ForegroundColor DarkYellow
Write-Host " setup container reset      : " -ForegroundColor White -NoNewline
Write-Host "set_reset" -ForegroundColor DarkYellow
Write-Host ""

# 개발용
Write-Host " dev container up           : " -ForegroundColor White -NoNewline
Write-Host "dev_up" -ForegroundColor DarkGreen
Write-Host " dev container down         : " -ForegroundColor White -NoNewline
Write-Host "dev_down" -ForegroundColor DarkGreen
Write-Host " dev container reset        : " -ForegroundColor White -NoNewline
Write-Host "dev_reset" -ForegroundColor DarkGreen
Write-Host ""