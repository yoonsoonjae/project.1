<?php
include 'db.php';
session_start();

// 사용자 인증 확인
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to post.");
}

// POST 데이터 가져오기
$title = isset($_POST['title']) ? $_POST['title'] : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';

// 데이터 검증
if (empty($title) || empty($content) || empty($category)) {
    die("All fields are required.");
}

// 유효한 카테고리인지 확인
$allowedCategories = ['free', 'review', 'new'];
if (!in_array($category, $allowedCategories)) {
    die("Invalid category.");
}

try {
    // 데이터베이스에 삽입
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, category, user_id, created_at) VALUES (:title, :content, :category, :user_id, NOW())");
    $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':category' => $category,
        ':user_id' => $_SESSION['user_id']
    ]);

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>