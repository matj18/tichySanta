<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

//vložíme do stránek hlavičku
$pageTitle='Uživatelé';
include __DIR__.'/inc/header.php';

if (!empty($_SESSION['user_id'])){
    echo '<h2>Seznam uživatelů</h2>';
    $users=$db->query('SELECT * FROM users ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($users)){
        echo '<ul>';
        foreach ($users as $user){
            //spočítáme dárky ve wishlistu
            $countQuery=$db->prepare('SELECT COUNT(gift_id) FROM gifts WHERE gift_for =:id;');
            $countQuery->execute([
                ':id'=>$user['user_id']
            ]);
            $count_wishlist=$countQuery->fetch(PDO::FETCH_COLUMN);

            echo '<li><a href="wishlist.php?id='.$user['user_id'].'">'.htmlspecialchars($user['name']).' ('.$count_wishlist.')</a></li>';
        }
        echo '</ul>';
    }
}else{
        //uživatel není přihlášný
        header('Location: index.php');
        exit();

}

//vložíme do stránek patičku
include __DIR__.'/inc/footer.php';