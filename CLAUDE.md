# ALM-SYSTEM 개발 가이드 (Claude 전용)

## 항상 한국어로 대답

## 프로젝트 개요
단일 사용자용 연차 관리 시스템

- Frontend: Vue + Tailwind CSS + daisyUI
- Backend: Laravel
- Infra: Docker + Nginx
- 인증: Token 기반 (3시간 만료)

## 프로젝트 구조

```
ALM-SYSTEM/
├── backend/
├── frontend/
├── nginx/
├── docker/
└── README.md
```

## 인증 정책

### 로그인
- 사용자 1명 (env 관리)

### 토큰
- Access Token 기반
- 유효시간 3시간
- 만료 시 401 반환

### 보안
- localStorage 사용 금지
- httpOnly 쿠키 사용
- HTTPS 필수
- Rate Limit 적용

## 코드 스타일

- 들여쓰기: 탭4
- 문자열: 큰따옴표 사용
- 네이밍: 카멜케이스

## Frontend 규칙 (Vue + Tailwind + daisyUI)

### UI 작성 우선순위

1. daisyUI 컴포넌트 사용
2. Tailwind 유틸리티로 보완
3. 커스텀 CSS 최소화

### daisyUI 사용 규칙

- 컴포넌트 기반 UI 구성
- 클래스 조합 최소화
- 디자인 일관성 유지

## Backend 규칙

- Laravel Sanctum 사용
- REST API 유지
- Controller 최소화
- Service 레이어 활용

## 연차 로직 핵심

- 연차 신청
- 승인/반려
- 자동 차감

## 예외 처리

- 잔여 연차 부족 차단
- 과거 날짜 신청 금지
- 공휴일 제외 (추후)
