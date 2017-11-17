<?php
if(!empty($_GET['widget_id']) && !empty($_POST['items'])){
    $dsn = "mysql:host=127.0.0.1;dbname=test;charset=utf8";
    $opt = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );
    $pdo = new PDO($dsn, 'root', 'king', $opt);
    foreach($_POST['items'] as $pos => $id){
        $pos++;
        $sql = "UPDATE `sort` SET `order` = :order WHERE `id` = :id AND `widget_id` = :widget_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':order', $pos, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':widget_id', $_GET['widget_id'], PDO::PARAM_INT);
        $stmt->execute();
    }
}