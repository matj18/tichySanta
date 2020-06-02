<?php
///načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

if (empty($_SESSION['user_id']) || empty($_REQUEST['type']) || empty($_REQUEST['id'])){
    //uživatel není přihlášen a nemá tu co dělat
    header('Location: index.php');
    exit();
}

$type = $_REQUEST['type'];
$id = $_REQUEST['id'];

if ($type=='gifts') {
    echo "<script type='text/javascript'>alert('zde1');</script>";
    $query = $db->prepare('DELETE FROM gifts WHERE gift_id = :id');
    $query->execute([
        ':id'=>$id
    ]);
    header('Location: mywishlist.php');
    exit();
} elseif ($type=='categories') {
    $query = $db->prepare('DELETE FROM categories WHERE category_id = :id');
    $query->execute([
        ':id'=>$id
    ]);
    header('Location: editcategories.php');
    exit();
}

header('Location: index.php');
exit();



