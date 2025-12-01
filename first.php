<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>一言日記　登録</title>
    </head>
    <body style="background-color:lavender">
        <h1>一言日記</h1>
        <h2>初めての方はこちらで登録してください</h2>
        <form action="" method="post">
            お名前
            <br />
            <input type="text" name="name" placeholder="田中　太郎" required>
            <br />
            ニックネーム
            <br />
            <input type="text" name="nickname" placeholder="太郎" required>
            <br />
            ID
            <br />
            <input type="text" name="username" placeholder="ID" required>
            <br />
            パスワード
            <br />
            <input type="password" name="pass" placeholder="パスワード" required>
            <br />
            <input type="submit" name="submit" value="送信" >
            <a href="login.php">ログインはこちら</a>
            <br />
            <?php
            $dsn = 'mysql:dbname=データベース名;host=localhost';
            $user = 'ユーザ名';
            $password = 'パスワード';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            if (isset($_POST["name"], $_POST["nickname"], $_POST["username"], $_POST["pass"]) && !empty($_POST["name"]) && !empty($_POST["nickname"]) && !empty($_POST["username"]) && !empty($_POST["pass"])) {
                $name = $_POST["name"];
                $nickname = $_POST["nickname"];
                $username = $_POST["username"];
                $pass = $_POST["pass"];

                $sql = 'SELECT * FROM userlogin WHERE username = :username';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetch();

                if ($row){
                    echo "<br />このユーザー名はすでに使用されています。<br />";
                } else {
                    $password_hashed = password_hash($pass, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO userlogin (name, nickname, username, password) VALUES (:name, :nickname, :username, :password)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $password_hashed, PDO::PARAM_STR);
                    $stmt->execute();

                    echo "<br />登録完了です！<br />";

                    $dsn = 'mysql:dbname=データベース名;host=localhost';
                    $user = 'ユーザ名';
                    $password = 'パスワード';
                    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

                    $sql = "CREATE TABLE IF NOT EXISTS `" . $username . "_diary`"
                        ." ("
                        . "id INT AUTO_INCREMENT PRIMARY KEY,"
                        . "emotion VARCHAR(50) NOT NULL,"
                        . "comment TEXT NOT NULL,"
                        . "date DATE NOT NULL"
                        .");";

                        $stmt = $pdo->query($sql);
                }

            } else {
                echo "<br />すべて入力してください。<br />";
            }

            $sql = 'SELECT * FROM userlogin';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();

            foreach ($results as $row) {
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['nickname'].',';
                echo $row['username'].'<br />';
                echo "<hr>";
            }

            ?>
        </form>
    </body>
</html>