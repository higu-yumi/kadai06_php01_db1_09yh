<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ファイルからの申し込みデータ</title>
  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/display_file.css">
  <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Noto+Sans+JP:wght@100..900&family=Zen+Antique+Soft&display=swap"
        rel="stylesheet">
</head>
<body>
  <main>
    <div class="container">
      <h2>ファイルに書き出された申し込みデータ</h2>

<?php
// --- ファイル読み込み表示処理 ---
// $_SERVER['DOCUMENT_ROOT'] は Web公開ディレクトリ (例: /home/your_account/www) を指す
// dirname($_SERVER['DOCUMENT_ROOT']) でその親ディレクトリに上がる
$file_path = dirname($_SERVER['DOCUMENT_ROOT']) . '/config_files_private/file_data/s-file.txt';

      // 受講方法の日本語変換
      $method_map = [
          'local' => '会場',
          'online' => 'オンライン',
          'archive' => '録画',
      ];

      // 受講理由の日本語変換
      $reason_map = [
          'interest' => 'この分野に関心があったから',
          'skill_up' => 'スキルアップのため',
          'love' => '好きだから',
      ];

      // 質問リストを格納する変数
      $qalist = "";

      // 1. ファイルが存在するか確認
      if (file_exists($file_path)) {
          // 2. ファイルを「読み込みモード ("r")」でオープン
          $file_handle = fopen($file_path, "r");
          // 3. ファイルが正常にオープンできたか確認
          if ($file_handle) {
      ?>
              <table>
                <thead>
                  <tr>
                    <th>氏名</th>
                    <th>メールアドレス</th>
                    <th>受講方法</th>
                    <th>受講理由</th>
                  </tr>
                </thead>
                <tbody>
      <?php
              // 4. ファイルの内容を1行ずつ読み込むループを開始
              while (($line = fgets($file_handle)) !== false) {
                  $line = trim($line); // 読み込んだ行の末尾にある改行や空白を削除
                  if (!empty($line)) { // 空行でなければ処理を進めます。
                      $data = explode(",", $line); // 各データをカンマで区切って配列に分割
                      // データ項目が5つ（氏名、メール、受講方法、受講理由）あることを確認
                      if (count($data) >= 5) {

                          // 受講方法の日本語変換
                          $display_method = $method_map[$data[2]] ?? htmlspecialchars($data[2], ENT_QUOTES, 'UTF-8');

                          // 受講理由の日本語変換
                          $display_reason = $reason_map[$data[3]] ?? htmlspecialchars($data[3], ENT_QUOTES, 'UTF-8');
      ?>

       <tr>
          <td><?= htmlspecialchars($data[0], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?= htmlspecialchars($data[1], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?= $display_method; ?></td>
          <td><?= $display_reason; ?></td>
        </tr>

      <?php
                          // 質問リスト (<li>) の構築
                          // 質問内容が空でない場合のみリストに追加
                          if (!empty($data[4])) {
                              // nl2br() はデータベースからの改行コード \n を <br> タグに変換
                              // ファイルに書き出された質問にも改行が含まれる可能性があるので適用
                              $qalist .= "<li>" . nl2br(htmlspecialchars($data[4], ENT_QUOTES, 'UTF-8')) . "</li>";
                          }

                      }
                  }
              } // while ループの終わり
              fclose($file_handle); // 5. ファイルを閉じる
      ?>
                </tbody>
              </table>

    <!-- 受講者からの質問 -->
    <div class="qa-list container">
      <h2>受講者からの質問</h2>
      <ul>
        <?php echo $qalist; ?>
      </ul>
    </div>



      <?php
          } else {
              // ファイルがオープンできなかった場合
      ?>
              <p class='no-data'>ファイルを読み込むことができませんでした。</p>
      <?php
          }
      } else {
          // ファイル自体が存在しない場合（まだ誰もフォームを送信していない状態など）
      ?>
          <p class='no-data'>ファイルに申し込みデータがありません。</p>
      <?php
      }
      ?>

    </div>

    

    <div class="list">
      <a href="form.php" class="list-btn">お申し込みフォームへ戻る</a>
    </div>
    <div class="list">
      <a href="display.php" class="list-btn">データベースからの申込状況を確認する</a>
    </div>

  </main>

  <footer>
    <p>&copy;2025 セミナーお申し込み状況&nbsp;/&nbsp;PHP</p>
  </footer>
</body>
</html>