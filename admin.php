<?php
    // 管理ページのログインパスワード
    define('PASSWORD', 'adminPassword');

    // データベースの接続情報
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'board');

    // タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');

    // 変数の初期化
    $currnt_date=null;
    $message=array();
    $message_array=array();
    $success_message=null;
    $error_message=array();
    $pdo=null;
    $stmt=null;
    $res=null;
    $option=null;

    session_start();

    if(!empty($_GET['btn_logout'])){
        unset($_SESSION['admin_login']);
    }

    // データベースに接続
    try{

        $option=array(
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS=>false,
        );
        $pdo=new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS, $option);
    } catch(PDOException $e){

        // 接続エラーのときエラー内容を確認する。
        $error_message[]=$e->getMessage();
    }

    if(!empty($_POST['btn_submit'])){

        if(!empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD) {
            $_SESSION['admin_login'] = true;
        } else {
            $error_message[]='ログインに失敗しました。';
        }

    }

    if(!empty($pdo)){

        // メッセージのデータを取得する
        $sql="SELECT * FROM message ORDER BY post_date DESC";
        $message_array=$pdo->query($sql);
    }

    // データベースの接続を閉じる
    $stmt=null;
    $pdo=null;
    
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
            <select name="limit">
                <option value="">全て</option>
                <option value="10">10件</option>
                <option value="30">30件</option>
            </select>
            <input type="submit" name="btn_download" value="ダウンロード">
        </form>
<!-- ここに投稿されたメッセージを表示 -->
    <?php if(!empty($message_array)){ ?>
    <?php foreach($message_array as $value){ ?>
    <article>
        <div class="info">
            <h2><?php echo htmlspecialchars($value['view_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
            <p>
                <a href="edit.php?message_id=<?php echo $value['id']; ?>">編集</a>
                <a href="delete.php?message_id=<?php echo $value['id']; ?>">削除</a>
            </p>
        </div>
        <p><?php echo nl2br(htmlspecialchars($value['message'], ENT_QUOTES, 'UTF-8')); ?></p>
    </article>
    <?php } ?>
    <?php } ?>

    <form method="get" action="">
        <input type="submit" name="btn_logout" value="ログアウト">
    </form>
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