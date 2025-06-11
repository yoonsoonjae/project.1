<?php
include 'tmdb_api.php';

$id    = $_GET['id'] ?? '';
$movie = null;
$cast  = [];

if ($id) {
    // 한국어 줄거리
    $movie   = tmdb_request("movie/{$id}",         ['language' => 'ko-KR']);
    $credits = tmdb_request("movie/{$id}/credits", ['language' => 'ko-KR']);
    $cast    = array_slice($credits['cast'] ?? [], 0, 5);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($movie['title'] ?? '영화 정보 없음') ?></title>
  <link rel="stylesheet" href="/community/css/movie_detail.css">
</head>
<body>
  <?php if (!$movie || isset($movie['status_code'])): ?>
    <p class="error">❗ 해당 영화 정보를 불러올 수 없습니다.</p>
  <?php else: ?>
    <div class="detail-container">
      <!-- 포스터 -->
      <div class="poster">
        <?php if (!empty($movie['poster_path'])): ?>
          <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="포스터">
        <?php else: ?>
          <div class="error">[포스터 없음]</div>
        <?php endif; ?>
      </div>

      <!-- 영화 정보 -->
      <div class="info">
        <h1><?= htmlspecialchars($movie['title']) ?></h1>
        <?php if (!empty($movie['tagline'])): ?>
          <div class="tagline">“<?= htmlspecialchars($movie['tagline']) ?>”</div>
        <?php endif; ?>

        <div class="meta">
          <div><strong>개봉일 : </strong> <?= htmlspecialchars($movie['release_date']) ?></div>
          <?php if (!empty($movie['genres'])): ?>
            <div><strong>장르 : </strong> <?= implode(', ', array_column($movie['genres'], 'name')) ?></div>
          <?php endif; ?>
          <?php if (!empty($movie['runtime'])): ?>
            <div><strong>상영 시간 : </strong> <?= htmlspecialchars($movie['runtime']) ?> min</div>
          <?php endif; ?>
          <div><strong>평점 : </strong> <?= htmlspecialchars($movie['vote_average']) ?> / 10</div>
        </div>

        <div class="overview">
            <h2>영화 줄거리</h2>
            <?php if (!empty($movie['overview'])): ?>
                <p><?php echo nl2br(htmlspecialchars($movie['overview'])); ?></p>
            <?php else: ?>
                <p class="error">❗ 줄거리 정보가 제공되지 않습니다.</p>
            <?php endif; ?>
        </div>

        <?php if ($cast): ?>
          <div class="cast-list">
            <?php foreach ($cast as $member): ?>
              <div class="cast-member">
                <?php if (!empty($member['profile_path'])): ?>
                  <img src="https://image.tmdb.org/t/p/w185<?= $member['profile_path'] ?>" alt="배우 사진">
                <?php else: ?>
                  <div class="error">[사진 없음]</div>
                <?php endif; ?>
                <div class="name"><?= htmlspecialchars($member['name']) ?></div>
                <div class="character"><?= htmlspecialchars($member['character']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</body>
</html>
