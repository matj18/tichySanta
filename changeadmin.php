<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';


if (empty($_SESSION['user_id']) || ($_SESSION['role'] != 'administrátor')){
    //uživatel není přihlášen nebo není administrátor
    header('Location: index.php');
    exit();
}

#region načtení uzivatele a zmena prav
if (!empty($_REQUEST['id'])){
    $userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:id LIMIT 1;');
    $userQuery->execute([':id'=>$_REQUEST['id']]);
    if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)){

        if ($_SESSION['user_id']== $user['user_id']) {
            //uzivatel chce menit vlastni prava, tedy by prisel o pozici administratora vlastni chybou
            header('Location: editusers.php');
            exit();
        }

        $role = 'uživatel'; //inicializace
        if ($user['role']=='uživatel') {
            $role = 'administrátor';
        }
        $userQuery=$db->prepare('UPDATE users SET role =:role WHERE user_id =:id');
        $userQuery->execute([
            ':id'=>$user['user_id'],
            ':role'=>$role,
        ]);

    }
}
#endregion načtení uzivatele a zmena prav

header('Location: editusers.php');
exit();