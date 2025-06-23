<?php
////////////////////////
// POSTデータ取得[name,email,method,reason,qa]
//
//if文は〈submit.php〉が〈form.php〉から正しくPOSTでデータ送信された場合のみ、その後の処理を実行させるためのもの、不要な処理が走るのを避ける！
// $_SERVER ：PHPが提供するスーパーグローバル変数の一つ
// $_SERVER["REQUEST_METHOD"]：現在のHTTPリクエストがどのメソッド（GET, POST, PUT, DELETE他）で送信されたかを示す文字列を保持

if($_SERVER["REQUEST_METHOD"] == "POST"){ // "POST"と等しいかをチェック
$name = $_POST["name"];
$email = $_POST["email"];
$method = $_POST["method"];
$reason = $_POST["reason"];
$qa = $_POST["qa"];

//
//全てのPOST変換にXSS対策のサニタイズ（XSS：サイバー攻撃）
//
//悪意のあるHTMLタグやJavaScriptコードを無害な形に変換する処理
$name = htmlspecialchars($name,ENT_QUOTES,'UTF-8');
$email = htmlspecialchars($email,ENT_QUOTES,'UTF-8');
$method = htmlspecialchars($method,ENT_QUOTES,'UTF-8');
$reason = htmlspecialchars($reason,ENT_QUOTES,'UTF-8');
$qa = htmlspecialchars($qa,ENT_QUOTES,'UTF-8');

//
//バリデーション(入力チェック)
//

// 氏名が空でないか
if(empty($name)){ 
  echo "氏名が入力されていません<br>";
  exit(); // 処理を中断して終了
}

// メールアドレスが空でないか
if(empty($email)){ 
  echo "メールアドレスが入力されていません。<br>";
  exit(); // 処理を中断して終了
}

// メールアドレスの形式チェック
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
  echo "メールアドレスの形式が正しくありません。<br>";
  exit(); // 処理を中断して終了
}

// 受講方法の選択
$allowed_methods = ['local', 'online', 'archive'];
// empty($method) もチェックしつつ、不正な値もチェック
if(empty($method) || !in_array($method, $allowed_methods)){
  echo "受講方法が選択されていないか、不正な値です。<br>";
  exit();
}

// 受講理由の未選択
if(empty($reason) || $reason === "notselect"){
  echo "受講理由を選択してください。<br>";
  exit(); // 処理を中断して終了
}

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

try{
$dbh = new PDO('mysql:dbname=yh-deploy_gs_kadai_user;charset=utf8;host=mysql3108.db.sakura.ne.jp','*******', '');
  // この1行はPDOのエラーモード設定、これ必須！ display.phpに詳細記載あり
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
  exit('接続エラー:'.$e->getMessage());
}

////////////////////////
// データ登録SQL作成
//
// INSERT文にidとdatetimeは不要。
// idはMySQL自動的に連番を割り振るためPHP側からidの値を渡す必要なし
// indateもDEFAULT CURRENT_TIMESTAMP設定のためMySQLが自動的に日時挿入
$sql = "INSERT INTO kadai_06(name,email,method,reason,qa)VALUES(:name,:email,:method,:reason,:qa);";
// SQLインジェクション対策（サイバー攻撃）単なる文字列として扱われる
$stmt = $dbh->prepare($sql); 
$stmt->bindValue(':name', $name, PDO::PARAM_STR); 
$stmt->bindValue(':email', $email, PDO::PARAM_STR); 
$stmt->bindValue(':method', $method, PDO::PARAM_STR); 
$stmt->bindValue(':reason', $reason, PDO::PARAM_STR); 
$stmt->bindValue(':qa', $qa, PDO::PARAM_STR); 
$status = $stmt->execute(); // true or false



////////////////////////
// データ登録処理後
//
if($status){
  // データ登録成功時の処理
  echo "データ登録が完了しました。<br>";
  header("Location:complete.html"); // 完了ページへ
  exit();

} else {
  // データ登録失敗時の処理
  $error = $stmt->errorInfo();
  exit("SQLエラー:".$error[2]); //"SQL_ERROR:"エラーがどこかわかりや
}

} else {
  echo "このページへの不正なアクセスです。<br>";
  exit();  //以降の処理を中断する
}
