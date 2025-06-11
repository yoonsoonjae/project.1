<?php
define('TMDB_API_KEY', '본인의 api 키값을 넣으세요!');

function tmdb_request(string $endpoint, array $params = []): array {
    $base_url = "https://api.themoviedb.org/3/";

    // 1) API Key 무조건 설정
    $params['api_key'] = TMDB_API_KEY;

    // 2) 언어 설정 (필요시 ko-KR, 영어 검색 문제 있을 땐 en-US)
    $params['language'] = 'ko-KR';

    $url = $base_url . $endpoint . '?' . http_build_query($params);

    // (디버그용) 실제 호출되는 URL이 맞는지 한 번만 확인
    // echo "<p style='color:yellow;'>📡 TMDB 요청 URL: <a href='$url' target='_blank'>$url</a></p>";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // tmdb_api.php 내 curl 옵션에 추가
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    // curl 요청을 브라우저처럼 위장
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

    $raw = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $err  = curl_errno($ch) ? curl_error($ch) : null;
    curl_close($ch);

    if ($err) {
        error_log("cURL error: $err");
        return ['results' => [], 'error' => $err];
    }
    if ($http !== 200) {
        error_log("TMDB API HTTP $http: $raw");
        return ['results' => [], 'error' => "HTTP $http"];
    }

    $json = json_decode($raw, true);
    return is_array($json) ? $json : ['results' => [], 'error' => 'Invalid JSON'];
}



