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

    #region obnova kategorie u prispevku
    //najdeme nejakou kategorii
    $catQuery=$db->prepare('SELECT * FROM categories LIMIT 1;');
    $catQuery->execute();
    if ($cat=$catQuery->fetch(PDO::FETCH_ASSOC)) {
        //kdyz ji mame, dame ji prispevkum bez kategorie
        $saveQuery=$db->prepare('UPDATE gifts SET category_id=:category WHERE category_id is NULL ;');
        $saveQuery->execute([
            ':category'=>$cat['category_id']
        ]);
    }

    #endregion obnova kategorie u prispevku


    header('Location: editcategories.php');
    exit();
}

header('Location: index.php');
exit();



