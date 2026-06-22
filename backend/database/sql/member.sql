-- Member Table Data
-- mb_department : 영업 / 관리
-- mb_affiliation: 영업부서(스타, 정보) / 관리부서(일반, 주말)
-- (주말) 표기된 사원은 mb_affiliation = '주말'

TRUNCATE TABLE member;

INSERT INTO member
(mb_affiliation, mb_department, mb_name, mb_hire_date, mb_retire_date, created_at, updated_at)
VALUES
-- 영업부서
('정보', '영업', '정희주', '2023-01-02', NULL, NOW(), NOW()),
('정보', '영업', '김민영', '2023-02-27', NULL, NOW(), NOW()),
('정보', '영업', '김소형', '2024-01-26', NULL, NOW(), NOW()),
('정보', '영업', '갈희은', '2024-04-04', NULL, NOW(), NOW()),
('스타', '영업', '김주연', '2024-06-16', NULL, NOW(), NOW()),
('스타', '영업', '김윤서', '2024-11-06', NULL, NOW(), NOW()),
('스타', '영업', '우현정', '2024-12-11', NULL, NOW(), NOW()),
('스타', '영업', '남주은', '2025-09-11', NULL, NOW(), NOW()),
('스타', '영업', '이지윤', '2025-09-15', NULL, NOW(), NOW()),
('스타', '영업', '김주희', '2026-03-23', NULL, NOW(), NOW()),
('정보', '영업', '구은지', '2026-04-06', NULL, NOW(), NOW()),
('스타', '영업', '윤지수', '2026-04-02', NULL, NOW(), NOW()),
('스타', '영업', '이예진', '2026-04-20', NULL, NOW(), NOW()),
-- 관리부서
('일반', '관리', '박수연', '2024-03-04', NULL, NOW(), NOW()),
('주말', '관리', '최현희', '2024-11-18', NULL, NOW(), NOW()),
('일반', '관리', '김민주', '2025-03-13', NULL, NOW(), NOW()),
('주말', '관리', '한소현', '2026-01-05', NULL, NOW(), NOW()),
('일반', '관리', '정유진', '2026-01-02', NULL, NOW(), NOW()),
('일반', '관리', '박제민', '2026-04-20', NULL, NOW(), NOW());
