<?php
////////////////////////
// データベース接続
//

// サーバーとXAMPPで使えるように！！！
// サーバー名が 'localhost' または IPアドレスが '127.0.0.1' ならXAMPP環境と判断
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    require_once 'config_local.php'; // XAMPP用の設定を読み込む
} else {
    // サーバー用の設定を読み込む、セキュリティのため別フォルダに保管config_server.php
    // $_SERVER['DOCUMENT_ROOT'] は、ウェブ公開ディレクトリのパスを指す。
    // その親ディレクトリ (dirname()) から config_files フォルダの中を指定。
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config_files_private/config_server.php';
}

// データベース接続
try {
  //dbh データベースハンドルの略、データベースを運転できる人
 $dsn = 'mysql:dbname=' . DB_NAME . ';charset=' . DB_CHARSET . ';host=' . DB_HOST;
    $dbh = new PDO($dsn, DB_USER, DB_PASS);
  // PDOのエラーモード設定、これ必須！
  // PDO::ATTR_ERRMODE PDOのエラーレポートモード。値を1つを指定できる
  // PDO::ERRMODE_EXCEPTION ：PDOExceptionをスローする
  // DB操作の安全性とデバッグのしやすさを劇的に向上させる非常に重要な設定
  //データベース接続を管理する $dbhオブジェクトに対して、エラーが発生した場合には例外（Exception）として報告してください」と指示
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // プリペアドステートメントのエミュレーションを無効に
} catch(PDOException $e){
  // もしデータ取得でエラーが起きたら
  exit('DB_CONTENT:' .$e->getMessage()); //エラーメッセージを表示して終了
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
// 円グラフ(受講理由)
//

try {
    // 1. 受講理由を集計するSQLクエリの準備
    $display_reason = "SELECT reason, COUNT(*) AS count FROM kadai_06 GROUP BY reason";
    // SQL実行準備のためのステートメントオブジェクト作成
    $reason_map = $dbh->prepare($display_reason);
    // 2. クエリを実行
    // execute() はステートメントオブジェクト ($reason_map) に対して呼び出す
    $reason_map->execute(); 
    // 3. 結果を連想配列の配列として取得し、$chart_data に格納
    // DBから取得した結果をPHPで非常に使いやすい形に変換する命令
    $chart_data = $reason_map->fetchAll(PDO::FETCH_ASSOC); 
} catch (PDOException $e) {
    // もしデータ取得でエラーが起きたら
    exit('受講理由の集計エラー: ' . $e->getMessage()); //エラーメッセージ表示して終了
}


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

    <!-- 円グラフ -->
    <div class="container">
      <h2>受講理由</h2>
      <div style="width: 550px; margin: 35px auto;">
        <canvas id="reasonChart"></canvas>
      </div>
    </div>

    <!-- 受講者からの質問 -->
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

  <!-- 受講理由の円グラフ JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

  <script>

    Chart.register(ChartDataLabels); // 件数をグラフに表示するために追加
    // PHPから渡されるデータをJavaScript変数に格納
    // PHPの $chart_data 配列をJSON形式に変換し、JavaScriptに渡します
    const chartData = <?php echo json_encode($chart_data); ?>;

    // Chart.jsで必要な形式にデータを整形
    const labels = []; // グラフの各項目の名前（例: "関心があったから"）
    const counts = []; // 各項目の値（件数）

    // データベースに保存されている英語の理由名を、グラフ表示用の日本語に変換するマップ
    const reasonTranslations = {
      'interest': 'この分野に関心があったから',
      'skill_up': 'スキルアップのため',
      'love': '好きだから',
    };

    // 取得したデータ（chartData）をループ処理して、ラベルと件数を抽出
    chartData.forEach(item => {
      // item.reason（例: 'interest'）を日本語に変換
      // もし reasonTranslations にない場合は、元の英語のまま使用
      labels.push(reasonTranslations[item.reason] || item.reason);
      counts.push(item.count);
    });

    // グラフのデータ設定
    // Chart.js がグラフを描くために必要なデータ形式
    const data = {
      labels: labels, // 上で作成した日本語のラベル（項目名）
      datasets: [{
        data: counts,      // 上で作成した件数（各項目の値）
        backgroundColor: [ // 各項目の背景色（円グラフの各セグメントの色）
          'rgba(206, 54, 87, 0.7)', // 赤系
          'rgba(59, 137, 188, 0.7)', // 青系
          'rgba(234, 182, 51, 0.7)', // 黄系
        ],
        borderColor: [ // 各項目の枠線の色
          'rgba(206, 54, 87, 0.7)', // 赤系
          'rgba(59, 137, 188, 0.7)', // 青系
          'rgba(234, 182, 51, 0.7)', // 黄系
        ],
        borderWidth: 0 // 枠線の太さ
      }]
    };

    // グラフのオプション設定(見た目や挙動)
   const config = {
      type: 'pie', // 円グラフ
      data: data, // 定義した'data'変数を使ってグラフを描く
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          // プラグインの追加でdatalabelsの設定が可能になった
          datalabels: {
            color: '#ffffff', // ラベルの色
            textAlign: 'center', // テキストの配置
            font: {
              weight: 'bold', // フォントの太さ
              size: 17 // フォントサイズ
            },
            // formatter関数：
            formatter: function(value, context) {
              // ここで表示するテキストをフォーマット
              // value は件数（countsのデータ）
              // context.chart.data.labels[context.dataIndex] でラベル名が取れる
              // context.dataset.data でデータセット全体が取れる
              let sum = 0;
              let dataArr = context.dataset.data;
              dataArr.map(data => {
                  sum += data;
              });
              let percentage = (value * 100 / sum).toFixed(1); // 割合を計算して小数点以下1桁
              return `${value}件\n（${percentage}%）`; // 「件数 (割合%)」の形式で表示
            }
          }
        }
      }
    };

    // JSで円グラフを画面上に描画するための最後の、そして最も重要な命令
    // グラフを描画するcanvas要素を取得
    const ctx = document.getElementById('reasonChart').getContext('2d');
    // 新しいChartインスタンスを作成してグラフを描画
    new Chart(ctx, config);
  </script>

</body>
</html>