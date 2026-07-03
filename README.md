# ALM-System · 연차 관리 시스템

근로기준법 기준으로 사원의 연차를 **자동 산정**하고, 신청·승인·차감을 한 곳에서 관리하는 사내용 연차 관리 시스템입니다. 개인 프로젝트로 기획부터 백엔드·프론트엔드 구현까지 직접 진행했습니다.

> Laravel(API) + Vue(SPA) + Docker 기반 풀스택 웹 애플리케이션

---

## ✨ 주요 기능

- **연차 자동 계산** — 입사일을 기준으로 근로기준법에 맞춰 연차를 산정
  - 1년 이상: 15일 / 1년 미만: 매 1개월 개근 시 1일(최대 11일)
- **사원 관리** — 사원 등록·수정, 퇴직 처리 및 재직자 기준 일괄 연차 계산
- **연차 신청 워크플로우** — 신청 → 승인 / 반려 → **잔여일수 자동 차감**
- **대시보드 캘린더** — 승인된 연차를 월별 캘린더로 표시(FullCalendar), **공휴일 외부 API 연동**
- **인증** — 토큰 기반 인증(만료·갱신 처리, 만료 시 401)
- **로그 기록** — 주요 작업 이력을 기록해 추적 가능

## 🛠️ 기술 스택

| 구분 | 사용 기술 |
|------|-----------|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Vue 3, Pinia, Vue Router, Tailwind CSS, daisyUI, FullCalendar |
| Database | MySQL |
| Infra | Docker, Nginx |

## 🧩 핵심 설계 포인트

- **연차 계산 로직 분리** — 계산 규칙을 모델(`AnnualLeave::calculate`)에 캡슐화해 재직자 일괄 계산과 단건 조회가 동일 로직을 공유
- **상태 기반 승인 흐름** — 신청 내역에 상태(대기/승인/반려)를 두고, 승인된 연차만 캘린더·차감에 반영
- **서비스 레이어** — 로그 기록을 `LogService`로 분리해 컨트롤러를 얇게 유지
- **민감정보 분리** — 인증·DB·메일 설정은 모두 환경변수(`.env`)로 관리(저장소에는 `.env.example`만 포함)

## 📁 프로젝트 구조

```
ALM-System/
├── backend/      # Laravel API (연차 계산·인증·로그)
├── frontend/     # Vue SPA (대시보드·연차/사원 관리 화면)
├── nginx/        # Nginx 설정
├── docker/       # 컨테이너 구성
└── docker-compose.yml
```

## 🚀 실행 방법

### 1. 환경변수 설정
```bash
# backend
cp backend/.env.example backend/.env
# frontend
cp frontend/.env.example frontend/.env   # 공휴일 API 키 입력
```

### 2. 백엔드
```bash
cd backend
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### 3. 프론트엔드
```bash
cd frontend
npm install
npm run dev
```

> Docker로 한 번에 실행하려면 루트의 `docker-compose.yml`을 사용합니다.

## 🔐 보안 메모

- `.env`, DB 비밀번호, API 키 등 모든 비밀정보는 저장소에 포함하지 않으며, `.env.example`로 필요한 키 목록만 제공합니다.

## 🤖 개발 방식

이 프로젝트는 역할별 AI 에이전트 팀(기획·백엔드·프론트엔드·QA·리뷰·보안)을 직접 구성·운영하는 [AI Agent Workspace](https://github.com/muhwa91/ai-agent-workspace) 거버넌스 아래에서 개발·유지보수됩니다 — API 계약 동결 후 병렬 구현(Contract-First), 훅 기반 품질 게이트.
