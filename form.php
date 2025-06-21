<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>セミナーお申し込みフォーム</title>
  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/style.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Noto+Sans+JP:wght@100..900&family=Zen+Antique+Soft&display=swap"
    rel="stylesheet">
</head>

<body>
  <header>
    <h1>セミナーお申し込みフォーム</h1>
  </header>
  <main>
    <div class="container">
      <form action="submit.php" method="POST">
        <div class="form-group">
          <label for="name_input" class="form-title">氏名</label>
          <input type="text" name="name" id="name_input">
        </div>
        <div class="form-group">
          <label for="email_input" class="form-title">メールアドレス</label>
          <input type="text" name="email" id="email_input">
        </div>
        <div class="radio form-group">
          <p class="form-title">受講方法</p>
          <div>
            <label for="method_local">会場</label>
            <input type="radio" name="method" value="local" id="method_local">
            <label for="method_online">オンライン</label>
            <input type="radio" name="method" value="online" id="method_online">
            <label for="method_archive">録画</label>
            <input type="radio" name="method" value="archive" id="method_archive">
          </div>
        </div>
        <div class="reason form-group">
          <p class="form-title">受講理由</p>
          <select name="reason" id="reason">
            <option value="notselect">選択してください</option>
            <option value="interest">この分野に関心があったから</option>
            <option value="skill_up">スキルアップのため</option>
            <option value="love">好きだから</option>
          </select>
        </div>
        <div class="form-group">
          <label for="qa_text" class="form-title qat">講師に質問があればご入力ください</label>
          <textarea name="qa" id="qa_text"></textarea>
        </div>
        <div class="submit-btn"><button id="btn">送信する</button></div>
      </form>
      <p class="akaji">ご注意：これはテストフォームです。ご入力いただいてもお申し込みいただけません。</p>
    </div>
  </main>

  <footer>
    <p>&copy;2025 セミナーお申し込みフォーム&nbsp;/&nbsp;PHP</p>
  </footer>
</body>

</html>