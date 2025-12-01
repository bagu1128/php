<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>一言日記</title>
    </head>
    <body style="background-color:lavender">
        <h1>一言日記</h1>
        <script>
            var today = new Date();
            var todayHtml = today.getFullYear() + '/' + (today.getMonth()+1) + '/' + today.getDate();
            document.write('<p class= "date">' + todayHtml + '</p>');
        </script>
        <form action="" method="post">
            <input type="text" name="username" placeholder="ID" required>
            <br />
            <input type="password" name="pass" placeholder="パスワード" required>
            <br />
            <input type="submit" name="submit" value="送信" >

            <a href="first.php">初めての方はこちら</a>
        </form>
        <?php

            session_start();

            $dsn = 'mysql:dbname=データベース名;host=localhost';
            $user = 'ユーザ名';
            $password = 'パスワード';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            if (isset($_POST["username"], $_POST["pass"]) && !empty($_POST["username"]) && !empty($_POST["pass"])) {
                $username = $_POST["username"];
                $pass = $_POST["pass"];

                $sql = 'SELECT * FROM userlogin WHERE username = :username';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetch();

                if ($row) {

                    if (password_verify($pass, $row["password"])) {
                        $_SESSION["username"] = $username;
                        header("Location: profile.php");
                        exit;
                    } else {
                        echo "<br />パスワードが間違っています";
                    }
                } else {
                    echo "<br />登録されていません";
                }
            }
        ?>
    </body>
</html>