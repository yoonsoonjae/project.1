<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Username already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashed_password
            ]);
            header('Location: login.php');
            exit;
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
    <title>회원가입 - 우린구른다</title>
    <link rel="stylesheet" href="/community/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <form class="register-box" action="register.php" method="post">
        <img src="/community/css/banner_logo.png" alt="로고" class="logo" style = "width: 200px; height: auto;">

        <h2>회원가입</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="input-box form-element">
            <i class="fa fa-user"></i>
            <input type="text" name="username" placeholder="아이디" required>
        </div>

        <div class="input-box form-element">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="비밀번호" required>
        </div>

        <div class="form-element">
            <button type="submit">회원가입</button>
        </div>
        <p class="link">이미 계정이 있으신가요? <a href="login.php">로그인</a></p>
    </form>

</body>
</html>