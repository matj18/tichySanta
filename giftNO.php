<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';


if (empty($_SESSION['user_id'])){
    //uživatel není přihlášný
    header('Location: index.php');
    exit(); //todo smer
}

#region načtení existujícího dárku z DB a odhlaseni
if (!empty($_REQUEST['id'])){
    $giftQuery=$db->prepare('SELECT * FROM gifts WHERE gift_id=:id LIMIT 1;');
    $giftQuery->execute([':id'=>$_REQUEST['id']]);
    if ($gift=$giftQuery->fetch(PDO::FETCH_ASSOC)){

        if ($_SESSION['user_id']!= $gift['gift_from']) {
            //uzivatel se chce odhlasit od darku, ktery nema zamluveny
            header('Location: mywishlist.php');
            exit();
        }
        if (!empty($gift['gift_from']))
            $giftQuery=$db->prepare('UPDATE gifts SET gift_from = NULL WHERE gift_id =:id');
        $giftQuery->execute([
            ':id'=>$_REQUEST['id'],
        ]);
    }
}
#endregion načtení existujícího dárku z DB a odhlaseni

header('Location: tobuy.php');
exit();