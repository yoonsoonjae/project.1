# Movie Community Project

이 README 파일은 PHP로 작성된 영화 커뮤니티 프로젝트의 설정, 사용 방법, 그리고 발생할 수 있는 주요 오류와 해결 방법을 정리합니다.

## 목차

1. [개요](#개요)
2. [필수 사항](#필수-사항)
3. [설치 및 설정](#설치-및-설정)

   * [MAMP 설치 및 실행](#mamp-설치-및-실행)
   * [데이터베이스 생성](#데이터베이스-생성)
   * [환경 변수 설정](#환경-변수-설정)
4. [TMDB API 연동](#tmdb-api-연동)
5. [문제 해결 (Troubleshooting)](#문제-해결-troubleshooting)

   * [1. SQLSTATE 1045 Access denied](#1-sqlstate-1045-access-denied)
   * [2. ERR\_CONNECTION\_REFUSED](#2-err_connection_refused)
   * [3. Unknown database 1049](#3-unknown-database-1049)
   * [4. Table not found 1146](#4-table-not-found-1146)
   * [5. session\_start() Notice](#5-session_start-notice)
   * [6. Invalid category Notice](#6-invalid-category-notice)
   * [7. Invalid argument for foreach](#7-invalid-argument-for-foreach)
   * [8. Undefined variable: query/first/response Notices](#8-undefined-variable-queryfirstresponse-notices)
   * [9. SSL certificate problem](#9-ssl-certificate-problem)
   * [10. TMDB API 요청 실패](#10-tmdb-api-요청-실패)
   * [11. 빈 검색 결과](#11-빈-검색-결과)
   * [12. 개요(overview) 빈 문자열](#12-개요overview-빈-문자열)

---

## 개요

이 프로젝트는 영화 정보를 TMDB(The Movie Database) API로 가져와 사용자에게 보여주는 영화 커뮤니티 웹 애플리케이션입니다.

## 필수 사항

* PHP 7.4 이상
* MAMP (Apache + MySQL)
* Composer (선택사항)
* TMDB API 키

## 설치 및 설정

### MAMP 설치 및 실행

1. [MAMP 공식 사이트](https://www.mamp.info/)에서 macOS 버전 다운로드 및 설치
2. 앱 실행 후 **Start Servers** 클릭하여 Apache와 MySQL 서버 시작
3. Apache 포트(기본 8888)와 MySQL 포트(기본 8889)를 확인

### 데이터베이스 생성

1. phpMyAdmin 접속: `http://localhost:8888/phpMyAdmin`
2. SQL 탭에서 아래 쿼리 실행:

   ```sql
   CREATE DATABASE community_db
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_general_ci;
   ```
3. `users`, `posts` 등 필요한 테이블 생성

### 환경 변수 설정

1. 프로젝트 루트에 `.env` 파일 생성
2. 다음 항목 추가:

   ```dotenv
   DB_HOST=localhost
   DB_PORT=8889
   DB_NAME=community_db
   DB_USER=root
   DB_PASS=root
   TMDB_API_KEY=여기에_발급받은_API_키
   ```
3. `vlucas/phpdotenv` 라이브러리 사용 시, `db.php`에서 로드

## TMDB API 연동

PHP에서 TMDB API를 호출할 때는 cURL 또는 `file_get_contents()`를 사용합니다. 기본 검색 예시:

```php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['TMDB_API_KEY'];
$query  = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING) ?? '';
$url    = sprintf(
    'https://api.themoviedb.org/3/search/movie?api_key=%s&language=ko-KR&query=%s',
    $apiKey,
    urlencode($query)
);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$json      = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "cURL 오류: $curlError";
    exit;
}
if ($httpCode !== 200) {
    echo "❗ TMDB API 요청 실패 (HTTP 코드: $httpCode)";
    exit;
}

$data = json_decode($json, true);
$results = $data['results'] ?? [];
```

## 문제 해결 (Troubleshooting)

### 1. SQLSTATE\[HY000] \[1045] Access denied for user 'root'@'localhost' (using password: NO)

* **원인**: DB 접속 시 비밀번호가 빠졌거나 잘못됨
* **해결**:

  * `.env`, `db.php`에 올바른 `DB_USER`, `DB_PASS` 설정
  * MAMP 기본: `root` / `root`

### 2. ERR\_CONNECTION\_REFUSED

* **원인**: Apache 서버 미실행 또는 잘못된 포트 접속
* **해결**:

  * MAMP에서 서버 시작
  * URL 포트(`http://localhost:8888/프로젝트`) 확인

### 3. SQLSTATE\[HY000] \[1049] Unknown database 'community\_db'

* **원인**: DB 미생성
* **해결**:

  * phpMyAdmin에서 DB 생성

### 4. SQLSTATE\[42S02]: Base table or view not found: 1146 Table 'community\_db.users' doesn't exist

* **원인**: 테이블 생성 누락 또는 오타
* **해결**:

  * 필요한 테이블(SQL 스크립트) 생성

### 5. Notice: session\_start(): Ignoring session\_start() because a session is already active

* **원인**: 중복 `session_start()` 호출
* **해결**:

  ```php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  ```

### 6. Invalid category Notice

* **원인**: 허용되지 않은 카테고리 파라미터
* **해결**:

  ```php
  $allowed = ['domestic','foreign','review'];
  $cat     = $_GET['category'] ?? 'domestic';
  if (!in_array($cat, $allowed, true)) {
      $cat = 'domestic';
  }
  ```

### 7. Invalid argument supplied for foreach()

* **원인**: 배열이 아닌 변수로 `foreach`
* **해결**:

  ```php
  if (!empty($data['results']) && is_array($data['results'])) {
      foreach ($data['results'] as $movie) {
          // …
      }
  }
  ```

### 8. Undefined variable: query / first / response Notices

* **원인**: 변수를 선언·초기화하지 않고 사용
* **해결**:

  ```php
  $query  = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING) ?? '';
  $first  = true;
  $response = [];
  ```

### 9. SSL certificate problem: unable to get local issuer certificate

* **원인**: CA 번들 경로 미지정
* **해결**:

  1. `php.ini`에 `openssl.cafile` 경로 설정
  2. 개발용으로 cURL에서 검증 끄기

### 10. ❗ TMDB API 요청 실패

* **원인**: HTTP 코드 ≠ 200 혹은 cURL 오류
* **해결**:

  ```php
  if (curl_errno($ch)) {
      echo 'cURL 오류: ' . curl_error($ch);
  } elseif ($httpCode !== 200) {
      echo "❗ TMDB API 요청 실패 (HTTP 코드: $httpCode)";
  }
  ```

### 11. 항상 빈 검색 결과 (results: \[])

* **원인**: 잘못된 쿼리, API 키 누락, URL 인코딩 오류
* **해결**:

  * `urlencode($query)` 사용
  * Postman에서 직접 호출 테스트
  * `json_last_error()` 확인

### 12. `$movie['overview']`가 빈 문자열로 나옴

* **원인**: 해당 영화 개요가 제공되지 않음
* **해결**:

  1. 상세 조회 API 호출로 `overview` 확보
  2. 대체 문구 표시

     ```php
     echo $movie['overview'] ?: '개요 정보가 없습니다.';
     ```

---

모든 설정과 문제 해결 방법을 따라가시면 프로젝트가 원활히 동작할 것입니다. 추가 문의 사항은 언제든 이슈를 열어 주세요!
