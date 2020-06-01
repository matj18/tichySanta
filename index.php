<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';
  //načteme inicializaci knihovny pro Facebook
  require_once 'inc/facebook.php';

  //vložíme do stránek hlavičku
  include __DIR__.'/inc/header.php';

  if (!empty($_SESSION['user_id'])){
      #region profil uživatele
      $name = $_SESSION['user_name'];
      $description = '';
      $count_wishlist = 0;
      $count_tobuy = 0;

      //zjistime informace o uzivateli z databaze
      $userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:id LIMIT 1;');
      $userQuery->execute([
          ':id'=>$_SESSION['user_id']
      ]);
      if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)) {
          $description = $user['description'];
      }
      echo '<h2>'.htmlspecialchars($name).'</h2>
            <div>'.htmlspecialchars($description).'</div>';
      echo '<a class="btn btn-secondary" href="profile.php">Upravit profil</a>';

      //spočítáme dárky ve wishlistu
      $countQuery=$db->prepare('SELECT COUNT(gift_id) FROM gifts WHERE gift_for =:id;');
      $countQuery->execute([
          ':id'=>$_SESSION['user_id']
      ]);
      $count_wishlist=$countQuery->fetch(PDO::FETCH_COLUMN);
      echo '<div><a href="mywishlist.php">Seznam přání: '.htmlspecialchars($count_wishlist).'</a></div>';

      //spočítáme dárky co je potřeba koupit
      $countQuery=$db->prepare('SELECT COUNT(gift_id) FROM gifts WHERE gift_from =:id;');
      $countQuery->execute([
          ':id'=>$_SESSION['user_id']
      ]);
      $count_tobuy=$countQuery->fetch(PDO::FETCH_COLUMN);
      echo '<div><a href="tobuy.php">Sehnat dárky: '.htmlspecialchars($count_tobuy).'</a></div>';


      #endregion profil uživatele
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