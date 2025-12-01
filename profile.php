<!DOCTYPE heml>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>一言日記</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header>
            <h1>一言日記</h1>
            <form action="" method="post">
                <input type = "submit" name = "logout" value = "ログアウト">
                <?php
                    ini_set('session.cookie_lifetime', 0);
                    ini_set('session.gc_maxlifetime', 3600);
                    session_start();
                    $unique_token = bin2hex(random_bytes(32));
                    $_SESSION['token'] = $unique_token;

                    if (isset($_POST["logout"])) {
                        session_unset();
                        session_destroy();
                        header("Location: login.php");
                        exit;
                    }
                ?>
            </form>
            <p>
                <?php
                    function get_date() {
                        return date("Y/m/d");
                    }
                    $today = get_date();

                    echo $today;
                ?>
            </p>
        </header>
        <br />
        <div id = "mypage">
            <h2>マイページ</h2>

            <?php
                if (!isset($_SESSION["username"])) {
                    header("Location: login.php");
                    exit;
                } else {
                    $dsn = 'mysql:dbname=データベース名;host=localhost';
                    $user = 'ユーザ名';
                    $password = 'パスワード';
                    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
                    $username = $_SESSION["username"];

                    $sql = 'SELECT * FROM userlogin WHERE username = :username';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetchAll();

                    foreach ($result as $row) {
                        echo $row["nickname"] . " さん、こんにちは！";
                    }

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                            $upload_dir = 'uploads/';
                            $info = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $filename = uniqid() . '.' . $info;

                            $filepath = $upload_dir . $filename;
                            move_uploaded_file($_FILES['image']['tmp_name'], $filepath);

                            $sql = 'SELECT * FROM userlogin WHERE username = :username';
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                            $stmt->execute();
                            $result = $stmt->fetchAll();

                            foreach ($result as $row) {
                                if (isset($row['path']) && !empty($row['path'])){
                                    $old_image = $row["path"];
                                    if (file_exists($old_image)){
                                        unlink($old_image);
                                    }
                                }
                            }
                            $stmt = $pdo->prepare("UPDATE userlogin SET path = :path WHERE username = :username");
                            $stmt->bindValue(':path', $filepath, PDO::PARAM_STR);
                            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                            $stmt->execute();
                            echo "<br />アップロード成功！<br />";
                        }
                    }
                }
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <?php
                    $sql = 'SELECT * FROM userlogin WHERE username = :username';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->execute();
                    $row = $stmt->fetch();

                    if (isset($row['path']) && !empty($row['path'])) {
                        echo '<img src="' . $row['path'] . '" style="max-width:200px; height:auto;">';
                    }
                ?>
                <br />
                <p>写真の登録・変更はこちらから</p>
                <input type="file" name="image">
                <input type="submit" value="アップロード">
            </form>
        </div>
        <br />
        <div id = "post">
            <h2>日記投稿</h2>
            <form action="" method="post">
                <select name = "emotion">
                    <option value = "happy">喜び</option>
                    <option value = "sad">悲しみ</option>
                    <option value = "angry">怒り</option>
                    <option value = "relax">安心</option>
                    <option value = "surprised">驚き</option>
                    <option value = "others">その他</option>
                </select>
                <br />
                <textarea name="comment" rows="4" cols="50" placeholder="出来事を記入"></textarea>
                <br />
                <input type="submit" name="submit" value="送信">
                <?php
                    if (isset($_POST["submit"])) {
                        $emotion = isset($_POST["emotion"]) ? $_POST["emotion"] : null;
                        $comment = isset($_POST["comment"]) ? $_POST["comment"] : '';

                        if (empty($emotion)) {
                            echo "感情を選んでください";
                        } elseif (empty($comment)) {
                            echo "コメントを入力してください";
                        } else {
                            $username = $_SESSION["username"];
                            $sql = "INSERT INTO `" . $username . "_diary` (emotion, comment, date) VALUES (:emotion, :comment, :date)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':emotion', $emotion, PDO::PARAM_STR);
                            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                            $stmt->bindParam(':date', $today, PDO::PARAM_STR);
                            $stmt->execute();

                            echo "<br />日記が投稿されました！";
                        }
                    }
                ?>
            </form>
        </div>

        <div id = "diary">
            <h2>過去の日記</h2>

            <table border = "1" style = "width:600px;">

                <tr>
                <th>投稿番号</th>
                <th>投稿日時</th>
                <th>感情</th>
                <th>コメント</th>
                </tr>

                <?php
                    $sql = 'SELECT * FROM ' . $username . '_diary';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->execute();
                    $results = $stmt->fetchAll();

                    if (empty($results)) {
                        echo "<tr><td colspan='3'>投稿がありません。</td></tr>";
                    } else {
                        foreach ($results as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['emotion']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['comment']) . "</td>";
                            echo "</tr>";
                        }
                    }
                ?>
            </table>
        </div>
    </body>
</html>