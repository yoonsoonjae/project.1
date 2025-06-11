<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id'])) {
        $post_id = $_POST['post_id'];
        $content = $_POST['content'];

        // 댓글 내용이 비어 있지 않은지 확인
        if (empty($content)) {
            die("Comment content is required.");
        }

        // 댓글 삽입
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (:post_id, :user_id, :content, NOW())");
            $stmt->execute([
                ':post_id' => $post_id,
                ':user_id' => $_SESSION['user_id'],
                ':content' => $content
            ]);

            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>