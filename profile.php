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

#region nacteni profilu uživatele
$name = $_SESSION['user_name'];
$description = '';

//zjistime informace o uzivateli z databaze
$userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:id LIMIT 1;');
$userQuery->execute([
    ':id'=>$_SESSION['user_id']
]);
if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)) {
    $description = $user['description'];
}

#endregion nacteni profilu uživatele


if (!empty($_POST)){
    #region zpracování formuláře
    $description=$_POST['description'];

    if ($_SESSION['user_id']) {
        $saveQuery=$db->prepare('UPDATE users SET description=:description WHERE user_id=:id LIMIT 1;');
        $saveQuery->execute([
            ':description' =>$description,
            ':id'=>$_SESSION['user_id']
        ]);
    }

        #region přesměrování
        header('Location: index.php'); //todo smer
        exit();
        #endregion přesměrování

    #endregion zpracování formuláře
}

//vložíme do stránek hlavičku

    $pageTitle='Editace profilu';


include 'inc/header.php';
?>
<h2><?php echo $name?></h2>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $_SESSION['user_id'];?>" />

        <div class="form-group">
            <label for="description">Popis:</label>
            <textarea name="description" id="description"><?php echo htmlspecialchars($description)?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">uložit...</button>
        <a href="index.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';