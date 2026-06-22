# docker 컨테이너 접속
function front_connect { docker exec -it vue sh }
function back_connect { docker exec -it laravel bash }
function db_connect { docker exec -it mariadb bash }
function nginx_connect { docker exec -it nginx_web sh }

# docker 컨테이너 중지 + 삭제
function front_delete { docker compose rm -sf frontend; }
function back_delete { docker compose rm -sf backend; }
function db_delete { docker compose rm -sf mariadb; }
function nginx_delete { docker compose rm -sf web-server; }

# docker 최초 세팅용
function set_up { docker compose -f docker-compose_setting.yml up -d --build; }
function set_down { docker compose -f docker-compose_setting.yml down -v; }
function set_reset { docker compose -f docker-compose_setting.yml down -v; docker compose -f docker-compose_setting.yml up -d --build }

# docker 개발용
function dev_up { docker compose -f docker-compose_dev.yml up -d --build; }
function dev_down { docker compose -f docker-compose_dev.yml down -v; }
function dev_reset { docker compose -f docker-compose_dev.yml down -v; docker compose -f docker-compose_dev.yml up -d --build }