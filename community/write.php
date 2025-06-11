<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$category = $_GET['category'] ?? 'free';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>게시글 작성</title>
    <link rel="stylesheet" href="/community/css/write.css">
</head>
<body>
    <form class="write-box" action="post.php" method="post">
        <a href="index.php">
            <img src="/community/css/banner_logo.png" alt="로고" class="logo" style = "width: 200px; height: auto;">
        </a>

        <h2>게시글 작성</h2>

        <input type="text" name="title" placeholder="제목을 입력하세요" required>

        <textarea name="content" placeholder="내용을 입력하세요" required></textarea>

        <select name="category" required>
            <option value="free" <?= $category === 'free' ? 'selected' : '' ?>>자유게시판</option>
            <option value="review" <?= $category === 'review' ? 'selected' : '' ?>>영화후기게시판</option>
            <option value="news" <?= $category === 'news' ? 'selected' : '' ?>>영화소식게시판</option>
        </select>

        <div class="btn-group">
            <button type="submit">등록</button>
            <button type="button" onclick="location.href='index.php'">취소</button>
        </div>
    </form>
</body>
</html>

