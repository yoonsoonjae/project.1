<?php include 'tmdb_api.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>영화 검색</title>
    <link rel="stylesheet" href="/community/css/search.css">
</head>
<body>
    <div class="search-box">
        <h2>🎬 영화 검색</h2>
        <form action="results.php" method="get" target="_blank" style="display: flex; flex-direction: column; align-items: center;">
            <input type="text" name="query" placeholder="🎬 영화 제목을 입력하세요" required>
            <div style="display: flex; justify-content: center; gap: 10px; width: 80%; margin-top: 10px;">
                <button type="submit">검색</button>
                <button type="button" onclick="location.href='index.php'" style="background-color: #555;">취소</button>
            </div>
        </form>
    </div>
</body>
</html>

