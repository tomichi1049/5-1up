<!DOCTYPE HTML>
<HTML lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission5-1</title>
    </head>
    
    <body>
        <?php
//以下掲示板+データベース接続
            //変数の定義
            $tablename='tbtest';
            //データベースに接続
            $dsn = 'mysql:dbname=databasename;host=localhost';
            $user = 'username';
            $password = 'password';
            //エラーのときに「エラーですよ」と表示してもらう
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            //テーブルの作成
            $sql = "CREATE TABLE IF NOT EXISTS tbtest"
            ." ("
            //登録できる項目（カラム：縦）
            . "id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,"
            . "name char(32) NOT NULL,"
            . "comment TEXT NOT NULL,"
            //日付の登録
            . "date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,"
            //パスワードの登録
            . "pass CHAR(32) NOT NULL"
            .");";
            //クエリ（指示）の実行
            $pdo->query($sql);
            echo "<hr>";
            //テーブルの表示
            $sql='SHOW TABLES';
            $result=$pdo->query($sql);
            foreach($result as $row){
                echo $row[0];
                echo '<br>' ;
            }
            //テーブルの構成詳細確認
            $sql='SHOW CREATE TABLE tbtest';
            $result=$pdo->query($sql);
            foreach($result as $row){
                echo $row[1];
                echo "<hr>";
            }
        if(!empty($_POST["name"])&&!empty($_POST["comment"])&&!empty($_POST["pass"])){
            $name=$_POST["name"];
            $comment=$_POST["comment"];
            $date = date("Y/m/d H:i:s");
            $pass=$_POST["pass"];
//投稿機能
            if(empty($_POST["checkEditnumber"])){
                $name=$_POST["name"];
                $comment=$_POST["comment"];
                $date = date("Y/m/d H:i:s");
                $pass=$_POST["pass"];
                //データ（レコード）を登録(変数の定義とセットで行う)
                $sql="INSERT tbtest(name, comment, date, pass) VALUES (:name, :comment, :date, :pass)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                $stmt->execute();
            }
//編集機能
            else{
                //入力データの受け取りを変数に代入
                $editnum=$_POST["editnum"];
                $editpass=$_POST["editpass"];
                $checkeditnum=$_POST["checkEditnumber"];
                //bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
                $id = $_POST["id"]; //変更する投稿番号
                $name = $_POST["name"];
                $comment = $_POST["comment"]; 
                $date = date("Y/m/d H:i:s");
                $pass=$_POST["pass"];
                //データ（レコード）を変更後に登録(変数の定義とセットで行う)
                $sql = 'UPDATE tbtest 
                        SET name=:name,comment=:comment,date=:date,pass=:pass,id=:id  
                        WHERE id=:id';
                //bind関数でデータベースに編集後のデータを書き込む
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                echo '<br>';
            }
        }
//POST送信で「編集対象番号」を送信
        if(!empty($_POST["editnum"])&&!empty($_POST["editpass"])){
            //変数の定義
            $editnum=$_POST["editnum"];
            $pass=$_POST["pass"];
            $editpass=$_POST["editpass"];
            $name=$_POST["name"];
            $comment=$_POST["comment"];
            $date = date("Y/m/d H:i:s");
            //SELECT文で表示
            $sql = 'SELECT * FROM tbtest';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                if($row['id'] == $editnum){
                    if($editpass = $row['pass']){
                        $editname=$row['name'];
                        $editcomment=$row['comment'];
                    }
                    else{
                        echo "パスワードが違います。";
                    }
                }
            }
        }
//削除機能
        if(!empty($_POST["deletenum"])&&!empty($_POST["deletepass"])){
            //削除機能で使用する変数を定義する
            $deletenum=$_POST["deletenum"];
            $deletepass=$_POST["deletepass"];
            //IDと削除用番号の一致したときに削除したい
            $id = $deletenum;
            $pass=$deletepass;
            //DELETE文でデータを削除する
            $sql = 'DELETE from tbtest where id=:id AND pass=:pass';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->execute();
        }
        ?>
<!--投稿用フォームの設置-->
        <h1>掲示板</h1>
        <form action="" method="post">
            <!--既存の投稿フォームに、上記で取得した「名前」と「コメント」の内容が
                既に入っている状態で表示させる-->
            <!--定義したものをvalueのif文に入れる-->
            <input type=text name="name" placeholder="名前" value="<?php  if(!empty($editname)) {echo $editname;}?>">
            <input type=text name="comment" placeholder="コメント" value="<?php  if(!empty($editcomment)) {echo $editcomment;}?>">
            <!--新規投稿フォームに「パスワード」の入力欄を追加-->
            <input type=password name="pass" placeholder="パスワード">
            <!--以下の記述で、編集を要請した番号が表示されるようになった-->
            <input type=number name="checkEditnumber" placeholder="※未記入：番号チェック" value="<?php echo $editnum;?>">
            <input type=submit name="submit">
            <!--新規投稿フォームに「パスワード」の入力欄を追加-->
            <input type="hidden" name="id" value="<?php if (!empty($editnum)) echo $editnum; ?>">
            <br>
            <!--入力フォームと並べて「削除番号指定用フォーム」を用意：「削除対象番号」の入力と「削除」ボタンが1つある
                POST送信で「削除対象番号」を送信。-->
            <input type=number name="deletenum" placeholder="削除番号" value="<?php if($_POST["pass"]==$_POST["deletepass"])?>">
            <!--削除フォームにも「パスワード」の入力欄を追加する-->
            <input type=password name="deletepass" placeholder="削除：パスワード">
            <input type=submit name="submit2">
            <br>
            <!--「入力フォーム」「削除フォーム」と並べて「編集番号指定用フォーム」を用意
                「編集対象番号」の入力と「編集」ボタンが1つある-->
            <input type=number name="editnum" placeholder="編集番号" value="">
            <!--編集フォームにも「パスワード」の入力欄を追加する-->
            <input type=password name="editpass" placeholder="編集：パスワード">
            <input type=submit name="submit3">
            <br>
        </form>
        <?php
        $sql = 'SELECT * 
                        FROM tbtest 
                        WHERE name IS NOT NULL
                        AND comment IS NOT NULL
                        AND date IS NOT NULL
                        AND pass IS NOT NULL';
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll();
                foreach ($results as $row){
                    //$rowの中にはテーブルのカラム名が入る
                    echo $row['id'].',';
                    echo $row['name'].',';
                    echo $row['comment'].',';
                    //日付を追加表示、パスワードは表示しない
                    echo $row['date'].'<br>';
                    echo "<hr>";
                }
        ?>
    </body>
</HTML>