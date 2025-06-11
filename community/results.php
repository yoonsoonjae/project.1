<?php
// 1) TMDB API 함수 로드
include 'tmdb_api.php';

// 2) 검색어, 응답, 결과 변수 초기화 (반드시 HTML 이전에)
$query    = trim($_GET['query'] ?? '');
$response = [];
$results  = [];

// 3) 검색어가 있으면 TMDB 호출
if ($query !== '') {
    $response = tmdb_request("search/movie", [
        'query'         => $query,
        'page'          => 1,
        'include_adult' => false,
    ]);
    $results = $response['results'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>검색 결과</title>
  <link rel="stylesheet" href="/community/css/community.css">
  <link rel="stylesheet" href="/community/css/results.css">
</head>
<body>
  <div class="results-box">
    <h1>검색 결과: <?= htmlspecialchars($query) ?></h1>

    <?php if (!$query): ?>
      <p class="error">❗ 검색어가 입력되지 않았습니다.</p>

    <?php elseif (isset($response['error'])): ?>
      <p class="error">❗ API 오류: <?= htmlspecialchars($response['error']) ?></p>

    <?php elseif (!is_array($results)): ?>
      <p class="error">❗ 응답 구조가 올바르지 않습니다.</p>

    <?php elseif (count($results) === 0): ?>
      <p class="error">🔍 검색 결과가 없습니다. 다른 키워드로 시도하세요.</p>

    <?php else: ?>
      <ul>
        <?php foreach ($results as $movie): ?>
          <li>
            <a href="movie_detail.php?id=<?= $movie['id'] ?>" target="_blank">
              <?= htmlspecialchars($movie['title']) ?>
              (<?= htmlspecialchars($movie['release_date']) ?>)
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>
</html>








