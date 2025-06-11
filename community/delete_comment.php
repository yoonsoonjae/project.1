<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_id']) && isset($_POST['comment_id'])) {
        $comment_id = $_POST['comment_id'];

        // 해당 댓글 작성자만 삭제 가능
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = :comment_id");
            $stmt->execute([':comment_id' => $comment_id]);
            $comment = $stmt->fetch();

            if ($comment && $comment['user_id'] == $_SESSION['user_id']) {
                // 댓글 삭제
                $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
                $stmt->execute([':comment_id' => $comment_id]);

                header("Location: index.php");
                exit();
            } else {
                echo "You are not allowed to delete this comment.";
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>