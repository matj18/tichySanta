<?php
///načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

//vložíme do stránek hlavičku
$pageTitle='Editace kategorií';
include 'inc/header.php';

if ($_SESSION['role'] != 'administrátor') {
    header('Location: index.php');
    exit();
}

$categories=$db->query('SELECT * FROM categories ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
if (!empty($categories)){
    foreach ($categories as $category){
        echo '<div>'.htmlspecialchars($category['name']).' - <a href="editcategory.php?id='.$category['category_id'].'" class="text-danger">upravit</a></div>';
    }
}
else {
    echo '<div class="alert alert-info">Nebyly nalezeny žádné kategorie.</div>';
}
echo '<div>
            <a href="editcategory.php" class="btn btn-primary">Přidat kategorii</a>
          </div>';

include 'inc/footer.php';