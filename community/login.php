<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>로그인 - 우린구른다</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/community/css/login.css"> <!-- 💡 분리된 CSS 파일 불러오기 -->
</head>

<body>
    <form class="login-box" action="login.php" method="post">
        <a href="index.php">
        <img src="/community/css/banner_logo.png" class="avatar" alt="avatar" style = "width: 200px; height: auto;">
        </a>
        <h2>로그인</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="input-box">
            <i class="fa fa-user"></i>
            <input type="text" name="username" placeholder="아이디" required>
        </div>

        <div class="input-box">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="비밀번호" required>
        </div>

        <button type="submit">로그인</button>

        <p style="margin-top: 15px;">계정이 없으신가요? <a href="register.php">회원가입</a></p>
    </form>
</body>
</html>
