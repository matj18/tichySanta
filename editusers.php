<?php
///načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

//vložíme do stránek hlavičku
include 'inc/header.php';

if ($_SESSION['role'] != 'administrátor') {
    header('Location: index.php');
    exit();
}

echo '<h2>Seznam uživatelů</h2>';
$users=$db->query('SELECT * FROM users ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
if (!empty($users)){
    echo '<ul>';
    foreach ($users as $user){
        if ($user['user_id'] == $_SESSION['user_id']) {
            continue;
        }
        echo '<li>'.htmlspecialchars($user['name']).' <a href="changeadmin.php?id='.$user['user_id'].'">';
        if ($user['role'] == 'uživatel') {
            echo 'Dát administrátorská práva';
        } else {
            echo 'Odebrat administrátorská práva';
        }
        echo '</a></li>';
    }
    echo '</ul>';
}
else {
    echo '<div class="alert alert-info">Nebyli nalezeni žádní uživatelé.</div>';
}


include 'inc/footer.php';