<?php
////////////////////////
// データベース接続
//

// サーバーとXAMPPで使えるように！！！
// サーバー名が 'localhost' または IPアドレスが '127.0.0.1' ならXAMPP環境と判断
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    require_once 'config_local.php'; // XAMPP用の設定を読み込む
} else {
    require_once 'config_server.php'; // サーバー用の設定を読み込む
}

// データベース接続
try {
  //dbh データベースハンドルの略、データベースを運転できる人
$dbh = new PDO('mysql:dbname=yh-deploy_gs_kadai_user;charset=utf8;host=mysql3108.db.sakura.ne.jp','yh-deploy_gs_kadai_user', '55dekitaphp2525');
  // PDOのエラーモード設定、これ必須！
  // PDO::ATTR_ERRMODE PDOのエラーレポートモード。値を1つを指定できる
  // PDO::ERRMODE_EXCEPTION ：PDOExceptionをスローする
  // DB操作の安全性とデバッグのしやすさを劇的に向上させる非常に重要な設定
  //データベース接続を管理する $dbhオブジェクトに対して、エラーが発生した場合には例外（Exception）として報告してください」と指示
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
  exit('DB_CONTENT:' .$e->getMessage());
}

////////////////////////
// データの取得
//

// SQLの定義
// テーブルから全てのカラム(*)を取得する
$sql = 'SELECT * FROM kadai_06';
// SQLの準備(プリペアドステートメント)
$stmt = $dbh->prepare($sql);
// SQLの実行。準備したSQLを実行する
$status = $stmt->execute(); // 実行結果が true/false で返される

////////////////////////
// 申込者リスト、申込者からの質問を表示する
//

$view = ""; // 表示用のHTML文字列を格納する変数、$view 変数を「初期化」するために必要
$qalist = ""; // 変数を「初期化」するために必要

if($status == false){
  //SQL実行が失敗した場合の処理
  $error = $stmt->errorInfo(); // エラー情報の取得
  exit("SQLエラー:" .$error[2]); //エラーメッセージを表示して終了
} else {
  // SQL実行が成功した場合
  // 取得したデータを1行ずつループ処理で取り出す
  while($result = $stmt->fetch(PDO::FETCH_ASSOC)){ // 1行分のデータが連想配列で格納

    //受講方法の日本語変換マップ
    $method_map = [
      'local' => '会場',
      'online' => 'オンライン',
      'archive' => '録画',
    ];

    // データベースの値'online'を日本語'オンライン'に変換
    // もしマップに存在しないキーなら、元の値をそのまま利用 (?? $result['method'])
        $display_method = $method_map[$result['method']] ?? htmlspecialchars($result['method'], ENT_QUOTES, 'UTF-8');

    // HTMLテーブルの行 (<tr>) を構築
    $view .= "<tr>";
    // 各データを出力する際は必ずXSS対策として htmlspecialchars() で対策！
    $view .= "<td class=\"no\">" . htmlspecialchars($result['id'], ENT_QUOTES, 'UTF-8') . "</td>"; // ID
    $view .= "<td>" . htmlspecialchars($result['name'], ENT_QUOTES, 'UTF-8') . "</td>"; // 氏名
    $view .= "<td>" . htmlspecialchars($result['email'], ENT_QUOTES, 'UTF-8') . "</td>"; // メールアドレス
    $view .= "<td class=\"no\">" . $display_method . "</td>"; // 変換後の受講方法
    // 申込日時は秒は表示しないための作業
    // 1. データベースから取得した日付文字列をUnixタイムスタンプに変換
    $timestamp = strtotime($result['indate']);
    // 2. Unixタイムスタンプを指定した書式（YYYY-MM-DD HH:MM）に変換
    $formatted_indate = date('Y/m/d', $timestamp);
    $view .= "<td class=\"time\">" . htmlspecialchars($formatted_indate, ENT_QUOTES, 'UTF-8') . "</td>";
    $view .= "</tr>";

    // 質問リスト (<li>) を構築
    // 質問内容が空でない場合のみリストに追加
    if(!empty($result['qa'])){
      // nl2br() はデータベースからの改行コード \n を <br> タグに変換
      $qalist .= "<li>" . nl2br(htmlspecialchars($result['qa'], ENT_QUOTES, 'UTF-8')) . "</li>";
  }
} // whileの閉じカッコ
}  // elseの閉じカッコ

?>

<!-- HTML -->

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お申し込み状況</title>
  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/display.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Noto+Sans+JP:wght@100..900&family=Zen+Antique+Soft&display=swap"
    rel="stylesheet">
</head>

<body>
  <main>
    <div class="container">
      <h2>申込者リスト</h2>
      <table>
        <thead>
          <tr>
            <th class="no">番号</th>
            <th>氏名</th>
            <th>メールアドレス</th>
            <th>受講方法</th>
            <th>申込日</th>
          </tr>
        </thead>
        <tbody>
          <?php echo $view; ?>
        </tbody>
      </table>
    </div>

    <div class="qa-list container">
      <h2>受講者からの質問</h2>
      <ul>
        <?php echo $qalist; ?>
      </ul>
    </div>

    <div class="list">
      <a href="form.php" class="list-btn">お申し込みフォームへ</a>
    </div>
  </main>
  <footer>
    <p>&copy;2025 セミナーお申し込み状況&nbsp;/&nbsp;PHP</p>
  </footer>
</body>

</html>