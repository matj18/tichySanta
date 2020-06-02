<!DOCTYPE html>
<html lang="cs">
  <head>
    <title><?php echo (!empty($pageTitle)?$pageTitle.' - ':'')?>Tichý Santa</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="inc/style.css">
  </head>
  <body>
    <header class="container bg-dark">
        <h1 class="text-white py-4 px-2"><a href="index.php" class="nodeco">Tichý Santa</a></h1>

        <div class="text-right text-white">
            <?php
            if (!empty($_SESSION['user_id']) && ($_SESSION['role'] == 'administrátor')){
                echo '<a href="editusers.php" class="text-white">editovat uživatele</a> <a href="editcategories.php" class="text-white">editovat kategorie</a>';
            }
            ?>
        </div>
      <div class="text-right text-white">
      <?php
        if (!empty($_SESSION['user_id'])){
          echo '<strong>'.htmlspecialchars($_SESSION['user_name']).'</strong>';
          echo ' - ';
          echo '<a href="logout.php" class="text-white">odhlásit se</a>';
        }else{
          echo '<a href="login.php" class="text-white">přihlásit se</a>';
        }
      ?>
      </div>
    </header>
    <main class="container pt-2">