<?php

    // データベースの接続情報
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'board');

    // 変数の初期化
    $csv_data=null;
    $sql=null;
    $pdo=null;
    $option=null;
    $message_array=array();
    $limit=null;
    $stmt=null;

    session_start();

    // 取得件数
    if(!empty($_GET['limit'])){

        if($_GET['limit']==="10"){
            $limit=10;
        } elseif($GET['limit']==="30"){
            $limit=30;
        }
    }

    if(!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true){

         // データベースに接続
        try{

            $option=array(
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS=>false,
            );
                $pdo=new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS, $option);

                // メッセージのデータを取得する
                if(!empty($limit)){

                    // SQL作成
                    $stmt=$pdo->prepare("SELECT * FROM message ORDER BY post_date ASC LIMIT :limit");

                    // 値をセット
                    $stmt->bindValue(':limit', $GET['limit'], PDO::PARAM_INT);

                } else{
                    $stmt=$pdo->prepare("SELECT * FROM message ORDER BY post_date ASC");
                }

                // SQLクエリの実行
                $stmt->execute();
                $message_array=$stmt->fetchAll();

                // データベースの接続を閉じる
                $stmt=null;
                $pdo=null;

            } catch(PDOException $e){

            // 管理者ページへリダイレクト
                header("Location: ./admin.php");
            }
        
        // 出力の設定
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=メッセージデータ.csv");
        header("Content-Transfer-Encoding: binary");

        // csvデータを作成
        if(!empty($message_array)){

            // 1行目のラベル作成
            $csv_data .='"ID","表示名","メッセージ","投稿日時"'."\n";

            foreach($message_array as $value){

                // データを1行ずつcsvファイルに書き込む
                $csv_data .='"'.$value['id'].'","'.$value['view_name'].'","'.$value['message'].'","'.$value['post_date']."\"\n";
            }
        }

        // ファイルを出力
        echo $csv_data;

    } else{

        // ログインページへリダイレクト
        header("Location: ./admin.php");
        exit;
    }

    return;

    if(!empty($_POST['btn_submit'])){

        if(!empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD) {
            $_SESSION['admin_login'] = true;
        } else {
            $error_message[]='ログインに失敗しました。';
        }

    }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板　管理ページ</title>
<link href="style.css" rel="stylesheet">
</head>
<body>
<h1>ひと言掲示板　管理ページ</h1>
<?php if(!empty($error_message)): ?>
    <ul class="error_message">
        <?php foreach($error_message as $value): ?>
            <li>・<?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<section>
<!-- ログイン画面 -->
    <?php if(!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true): ?>
        <!-- ダウンロードボタン -->
        <form method="get" action="./download.php">
            <input type="submit" name="btn_download" value="ダウンロード">
        </form>
<!-- ここに投稿されたメッセージを表示 -->
    <?php if(!empty($message_array)){ ?>
    <?php foreach($message_array as $value){ ?>
    <article>
        <div class="info">
            <h2><?php echo htmlspecialchars($value['view_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
        </div>
        <p><?php echo nl2br(htmlspecialchars($value['message'], ENT_QUOTES, 'UTF-8')); ?></p>
    </article>
    <?php } ?>
    <?php } ?>
    <?php else: ?>

    <!-- ログインフォーム -->
    <form method="post">
        <div>
            <label for="admin_password">ログインパスワード</label>
            <input id="admin_password" type="password" name="admin_password" value="">
        </div>
        <input type="submit" name="btn_submit" value="ログイン">
    </form>

    <?php endif; ?>
</section>
</body>
</html>