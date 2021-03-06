<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';
  //načteme inicializaci knihovny pro Facebook
  require_once 'inc/facebook.php';

  if (!empty($_SESSION['user_id'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: index.php');
    exit();
  }

  $errors=false;
  if (!empty($_POST)){
    #region zpracování formuláře
    $userQuery=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1;');
    $userQuery->execute([
      ':email'=>trim($_POST['email'])
    ]);
    if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)){

      if (password_verify($_POST['password'],$user['password'])){
        //heslo je platné => přihlásíme uživatele
        $_SESSION['user_id']=$user['user_id'];
        $_SESSION['user_name']=$user['name'];
        $_SESSION['role'] = $user['role'];

        //smažeme požadavky na obnovu hesla
        $forgottenDeleteQuery=$db->prepare('DELETE FROM forgotten_passwords WHERE user_id=:user;');
        $forgottenDeleteQuery->execute([':user'=>$user['user_id']]);

        header('Location: index.php');
        exit();
      }else{
        $errors=true;
      }

    }else{
      $errors=true;
    }
    #endregion zpracování formuláře
  }

  //vložíme do stránek hlavičku
$pageTitle='Přihlášení';
  include __DIR__.'/inc/header.php';
?>

  <h2>Přihlášení uživatele</h2>

    <?php
    #region přihlašování pomocí Facebooku
    //inicializujeme helper pro vytvoření odkazu
    $fbHelper = $fb->getRedirectLoginHelper();

    //nastavení parametrů pro vyžádání oprávnění a odkaz na přesměrování po přihlášení
    $permissions = ['email'];
    $callbackUrl = htmlspecialchars('https://eso.vse.cz/~matj18/tichySanta/fb-callback.php');

    //necháme helper sestavit adresu pro odeslání požadavku na přihlášení
    $fbLoginUrl = $fbHelper->getLoginUrl($callbackUrl, $permissions);

    //vykreslíme odkaz na přihlášení
    echo ' <a href="'.$fbLoginUrl.'" class="btn btn-primary">přihlásit se pomocí Facebooku</a>';
    #endregion přihlašování pomocí Facebooku
    ?>




  <form method="post">
    <div class="form-group">
      <label for="email">E-mail:</label>
      <input type="email" name="email" id="email" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['email'])?>"/>
      <?php
        echo ($errors?'<div class="invalid-feedback">Neplatná kombinace přihlašovacího e-mailu a hesla.</div>':'');
      ?>
    </div>
    <div class="form-group">
      <label for="password">Heslo:</label>
      <input type="password" name="password" id="password" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" />
    </div>
    <button type="submit" class="btn btn-primary">přihlásit se</button>
    <a href="forgotten-password.php" class="btn btn-light">zapomněl(a) jsem heslo</a>
    <a href="registration.php" class="btn btn-light">registrovat se</a>
    <a href="index.php" class="btn btn-light">zrušit</a>
  </form>

<?php
  //vložíme do stránek patičku
  include __DIR__.'/inc/footer.php';