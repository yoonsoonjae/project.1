<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id']) && isset($_POST['post_id'])) {
        $post_id = $_POST['post_id'];

        // 해당 게시글 작성자만 삭제 가능
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = :post_id");
            $stmt->execute([':post_id' => $post_id]);
            $post = $stmt->fetch();

            if ($post && $post['user_id'] == $_SESSION['user_id']) {
                // 게시글 삭제 쿼리 실행
                $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
                $stmt->execute([':post_id' => $post_id]);

                // 게시글 삭제 후 홈으로 리다이렉션
                header("Location: index.php");
                exit();
            } else {
                echo "You are not allowed to delete this post.";
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>