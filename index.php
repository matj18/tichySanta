<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';
  //načteme inicializaci knihovny pro Facebook
  require_once 'inc/facebook.php';

  //vložíme do stránek hlavičku
  include __DIR__.'/inc/header.php';

  if (!empty($_SESSION['user_id'])){
      //zjistime informace o uzivateli z databaze

    echo 'Tady bude profil';
  }else{
    echo '<p>Uživatel není přihlášen.</p>';
    echo '<a href="login.php" class="btn btn-primary">přihlásit se</a>';

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
  }

  //vložíme do stránek patičku
  include __DIR__.'/inc/footer.php';