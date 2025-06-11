<?php
include 'db.php';
include 'tmdb_api.php';

$topRatedResponse = tmdb_request("movie/top_rated", [
    'page'     => 1,
    'language' => 'ko-KR',
]);
$topRated = $topRatedResponse['results'] ?? [];

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$postsPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

$category = isset($_GET['category']) ? $_GET['category'] : 'free';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>우린구른다</title>
    <link rel="stylesheet" href="/community/css/community.css">
    <script>
        function showTab(tabName) {
            window.location.href = '?category=' + tabName;
        }
    </script>
</head>
<body>
    <h1>
        <a href="index.php">
            <img src="/community/css/banner_logo.png" alt="로고" style="width: 200px; height: auto;">
        </a>
    </h1>

    <div class="top-bar">
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                <a href="/community/logout.php" class="auth-button">로그아웃</a>
                <span class="welcome-message">
                    🤗[환영합니다.]🎉, [<?= htmlspecialchars($_SESSION['username']) ?>] 님
                </span>
            <?php else: ?>
                <a href="/community/login.php" class="auth-button">로그인</a>
            <?php endif; ?>
        </div>

        <!-- 영화 검색 버튼 -->
    <div class="stat-button-box">
        <a href="search.php" class="stat-button">
            영화 검색
        </a>
    </div>


        <!-- 글쓰기 버튼 (로그인 시에만) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="write-button-box">
                <a href="write.php?category=<?= $category ?>" class="write-button">
                    글쓰기
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- 평점 높은 영화화 버튼 (로그인 시에만) -->
    <div class="top-rated-container">
        <button class="carousel-btn prev">&lt;</button>
        <div class="top-rated-box">
            <div class="carousel-track">
                <?php foreach ($topRated as $i => $movie): ?>
                    <a href="movie_detail.php?id=<?= $movie['id'] ?>"
                    class="top-rated-item"
                    target="_blank">

                    <?php 
                        // 0,1,2번 포스터에만 각각 하나씩 뱃지 출력
                        if ($i === 0): ?>
                        <div class="badge">🥇 평점 1위</div>
                    <?php elseif ($i === 1): ?>
                        <div class="badge">🥈 평점 2위</div>
                    <?php elseif ($i === 2): ?>
                        <div class="badge">🥉 평점 3위</div>
                    <?php endif; // badge 출력 종료 ?>

                    <img src="https://image.tmdb.org/t/p/w200<?= htmlspecialchars($movie['poster_path']) ?>"
                        alt="<?= htmlspecialchars($movie['title']) ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="carousel-btn next">&gt;</button>
    </div>



    <div class="tabs">
        <div class="tab <?= $category == 'free' ? 'active' : '' ?>" onclick="showTab('free')">자유게시판</div>
        <div class="tab <?= $category == 'review' ? 'active' : '' ?>" onclick="showTab('review')">영화후기게시판</div>
        <div class="tab <?= $category == 'news' ? 'active' : '' ?>" onclick="showTab('news')">영화소식게시판</div>
    </div>

    <div class="tab-content">
        <?php
        $categoryNames = [
            'free' => '자유게시판',
            'review' => '영화후기게시판',
            'news' => '영화소식게시판'
        ];
        ?>
        <h2><?= $categoryNames[$category] ?? '게시판' ?></h2>

        <?php
        try {
            $stmt = $pdo->prepare("SELECT posts.id AS post_id, posts.title, posts.content, posts.created_at, posts.user_id, users.username
                                    FROM posts
                                    JOIN users ON posts.user_id = users.id
                                    WHERE posts.category = :category
                                    ORDER BY posts.created_at DESC LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            foreach ($stmt->fetchAll() as $post) {
                echo "<div class='post'>
                    <h3>" . htmlspecialchars($post['title']) . "</h3>
                    <p>" . nl2br(htmlspecialchars($post['content'])) . "</p>
                    <p><small>By " . htmlspecialchars($post['username']) . " on " . htmlspecialchars($post['created_at']) . "</small></p>";

                $stmt_comments = $pdo->prepare("
                    SELECT comments.*, users.username 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE comments.post_id = :post_id
                    ORDER BY comments.created_at ASC
                ");
                $stmt_comments->execute([':post_id' => $post['post_id']]);
                $comments = $stmt_comments->fetchAll();

                foreach ($comments as $comment) {
                    echo "<div class='comment'>";
                    echo "<p>" . nl2br(htmlspecialchars($comment['content'])) . "</p>";
                    echo "<small>By " . htmlspecialchars($comment['username']) . " on " . htmlspecialchars($comment['created_at']) . "</small>";
                    echo "</div>";
                }

                if (isset($_SESSION['user_id'])) {
                    echo "<form action='comment.php' method='post' class='comment-form'>
                            <input type='hidden' name='post_id' value='" . $post['post_id'] . "'>
                            <textarea name='content' placeholder='댓글 내용을 입력하시오.' required></textarea>
                            <button type='submit'>댓글 추가</button>
                        </form>";
                }

                echo "</div>";
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category = :category");
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->execute();
            $totalPosts = $stmt->fetchColumn();

            $totalPages = ceil($totalPosts / $postsPerPage);
            echo '<div class="pagination">';
            for ($i = 1; $i <= $totalPages; $i++) {
                echo "<a href='?category=$category&page=$i'" . ($i == $page ? " class='active'" : "") . ">$i</a>";
            }
            echo '</div>';
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>

    <script>
        (function(){
        const track   = document.querySelector('.carousel-track');
        const items   = Array.from(track.children);
        const prevBtn = document.querySelector('.carousel-btn.prev');
        const nextBtn = document.querySelector('.carousel-btn.next');

        // ① 포스터(아이템) 실제 너비(폭)만 가져오기
        const itemWidth = items[0].getBoundingClientRect().width;
        // ② margin-right 값(숫자)만 가져오기
        const style       = getComputedStyle(items[0]);
        const marginRight = parseFloat(style.marginRight);
        // ③ 총 이동폭 = 너비 + 마진
        const slideWidth  = itemWidth + marginRight;

        let index    = 0;
        const maxIdx = items.length - 1;

        function update() {
            track.style.transform = `translateX(-${index * slideWidth}px)`;
            prevBtn.disabled = index === 0;
            nextBtn.disabled = index === maxIdx;
        }

        prevBtn.addEventListener('click', ()=> {
            if (index > 0) index--;
            update();
        });
        nextBtn.addEventListener('click', ()=> {
            if (index < maxIdx) index++;
            update();
        });

        // 초기 상태
        update();
        })();
    </script>

</body>
<footer class="site-footer">
    <p>우린구른다🏀</p>
    <p>컴퓨터공학과 | 20211465 윤순재🏀</p>
    <p>컴퓨터공학과 | 20211488 이예찬🏀</p>
    <p>컴퓨터공학과 | 20211240 임재현🏀</p>
    <p>© 2025 movie.community - 영화 정보 커뮤니티</p>
    <p>
        <a href="/terms" style="color: #aaa; text-decoration: none;">서비스 이용약관</a> |
        <a href="/privacy" style="color: #aaa; text-decoration: none;">개인정보처리방침</a>
    </p>
</footer>
</html>
