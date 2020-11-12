<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5</title>
</head>
<body>
    <?php
    
        //データベース接続設定
        $dsn = 'mysql:dbname=データべース名;host=localhost';
	    $user = 'ユーザー名';
	    $password = 'パスワード';
    	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		
	    //テーブルの作成
		$sql = "CREATE TABLE IF NOT EXISTS posts"
	    ." ("
	    . "id INT AUTO_INCREMENT PRIMARY KEY,"
	    . "name char(32),"
    	. "comment TEXT,"//パスワードいるわ
    	. "time TEXT,"
    	. "password TEXT"
	    .");";
	    $stmt = $pdo->query($sql);
        
        //変数の初期化
        $mode = 'add';
        $form_name = "名前";
        $form_comment = "コメント";
        //識別値の取得
        set_post_to_var($password, "password");
        //$password = $_POST["password"];
        //$sendform = $_POST["formtype"];
        set_post_to_var($sendform, "formtype");
        $time = date("Y/m/d h:i:s");
        
        //$edited_id = $_POST["edited_id"];
        set_post_to_var($edited_id, "edited_id");
        //$name = $_POST["name"];
        set_post_to_var($name, "name");
        //$comment = $_POST["comment"];
        set_post_to_var($comment, "comment");
        //$d_id = $_POST["d_id"];
        set_post_to_var($d_id, "d_id");
        //$e_id = $_POST["e_id"];
        set_post_to_var($e_id, "e_id");
        
        if(isset($sendform))
        {
            switch($sendform)
            {
                case 'add_comment'://追加フォームの処理
                    //値の取得
                    if(!empty($name)&&!empty($comment)&&!empty($password))
                    {
                        if(empty($edited_id))//追加モードの処理
                        {
                            $sql = $pdo -> prepare(
                               "INSERT INTO posts (name, comment, time, password) VALUES (:name, :comment, :time, :password)");
	                        $sql -> bindParam(':name', $name, PDO::PARAM_STR);
	                        $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	                        $sql -> bindParam(':time', $time, PDO::PARAM_STR);
	                        $sql -> bindParam(':password', $password, PDO::PARAM_STR);
	                        $sql -> execute();
                        } else {//編集モードの処理
                            $sql = "UPDATE posts SET name=:name,comment=:comment,password=:password WHERE id=:edited_id";
                            $stmt = $pdo->prepare($sql);
	                        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
	                        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
	                        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
	                        $stmt->bindParam(':edited_id', $edited_id, PDO::PARAM_INT);
	                        $stmt->execute();
                            
                        }
                    }
                    break;
                    
                case 'del_comment'://削除フォームの処理
                    if(!empty($d_id))
                    {   
                        //パスワードが一致したときの処理
                        if(take_data_posts ($d_id, "password", $pdo) == $password)
                        {
                            $sql = 'DELETE FROM posts WHERE id=:d_id';
	                        $stmt = $pdo->prepare($sql);
	                        $stmt->bindParam(':d_id', $d_id, PDO::PARAM_INT);
	                        $stmt->execute();
                        }
                    }
                    break;
                    
                case 'edit_comment'://編集フォームの処理
                    
                    if(!empty($e_id))
                    {
                        if(take_data_posts ($e_id, "password", $pdo) == $password){
                            $mode = "edit";//編集モードに移行
                        }
                    }
                    break;
            }
            
        }
        
        if($mode == "edit")
        {
            $form_name = take_data_posts($e_id, "name", $pdo);
            $form_comment = take_data_posts($e_id, "comment", $pdo);
        }
        
        //ユーザ定義関数
        //パスワードの取得
        function take_data_posts ($id, $column_name,  $pdo) {
            $sql = 'SELECT * FROM posts WHERE id=:id ';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();
            $one_comments = $stmt->fetchAll(); 
            foreach ($one_comments as $data) {//もし複数あった時のため
                $target_data = $data[$column_name];
            }
            return $target_data;
        }
        
        function set_post_to_var(&$var, $post_name) {
            if(!empty($_POST["$post_name"])){
                $var = $_POST["$post_name"];
            }
        }
    ?>

    
    <h3>新規投稿フォーム</h3>
    <form action="" method="post">
        <?php if($mode=="edit") {echo "<p>編集モード</p>";} ?>
        <input value="<?php echo $form_name ?>" type="text" name="name" required><br>
        <input value="<?php echo $form_comment ?>" type = "text" name ="comment" required><br>
        <span>パスワード</span><input type = "password" name ="password" required><br>
        <input type = "submit">
        
        <!-- フォームタイプの判定 -->
        <input type="hidden" value="add_comment" name="formtype">
        <!-- 編集番号の送信 -->
        <input type="hidden" name="edited_id" value="<?php if($mode=="edit"){echo $e_id;} ?>"> 
    </form>
    
    <h3>削除フォーム</h3>
    <form action="" method="post">
        <input step=1 type="number" name="d_id" required><br>
        <span>パスワード</span>
        <input type = "password" name ="password" required><br>
        <input type = "submit" value = "削除">
        
        <!-- フォームタイプの判定 -->
        <input type="hidden" value="del_comment" name="formtype">
    </form>
    
    <h3>編集フォーム</h3>
     <form action="" method="post">
        <input step=1 type="number" name="e_id" required><br>
        <span>パスワード</span>
        <input type = "password" name ="password" required><br>
        <input type = "submit" value = "編集">
        
        <!-- フォームタイプの判定 -->
        <input type="hidden" value="edit_comment" name="formtype">
     </form>
    <?php 
    $sql = 'SELECT * FROM posts';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
		//$rowの中にはテーブルのカラム名が入る
		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].',';
		echo $row['time'].',';
		echo $row['password'].'<br>';
	echo "<hr>";
	}
	//テーブルの確認
	/*
	$sql ='SHOW TABLES';
	$result = $pdo -> query($sql);
	foreach ($result as $row){
		echo $row[0];
		echo '<br>';
	}
	echo "<hr>";
	*/
	?>