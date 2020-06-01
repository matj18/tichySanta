<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

//vložíme do stránek hlavičku
include __DIR__.'/inc/header.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášný
    header('Location: index.php');
    exit();
}

//if (!empty($_GET['category'])){
//#region výběr příspěvků z konkrétní kategorie
//$query = $db->prepare('SELECT
//posts.*, users.name AS user_name, users.email, categories.name AS category_name
//FROM posts JOIN users USING (user_id) JOIN categories USING (category_id) WHERE posts.category_id=:category ORDER BY updated DESC;');
//$query->execute([
//':category'=>$_GET['category']
//]);
//#endregion výběr příspěvků z konkrétní kategorie
//}else{
#region výběr příspěvků bez ohledu na kategorii
$query = $db->prepare('SELECT
gifts.*, name AS category_name, prices_from, prices_upto
FROM gifts JOIN categories USING (category_id) JOIN prices USING (prices_id)
ORDER BY until ASC');
$query->execute();
#region výběr příspěvků bez ohledu na kategorii
//}

//#region formulář s výběrem kategorií
//echo '<form method="get" id="categoryFilterForm">
//    <label for="category">Kategorie:</label>
//    <select name="category" id="category" onchange="document.getElementById(\'categoryFilterForm\').submit();">
//        <option value="">--nerozhoduje--</option>';
//
//        $categories=$db->query('SELECT * FROM categories ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
//        if (!empty($categories)){
//        foreach ($categories as $category){
//        echo '<option value="'.$category['category_id'].'"';//u category_id nemusí být ošetření speciálních znaků, protože jde o číslo
//        if ($category['category_id']==@$_GET['category']){
//        echo ' selected="selected" ';
//        }
//        echo '>'.htmlspecialchars($category['name']).'</option>';
//        }
//        }
//
//        echo '  </select>
//    <input type="submit" value="OK" class="d-none" />
//</form>';
//#region formulář s výběrem kategorií

$gifts = $query->fetchAll(PDO::FETCH_ASSOC);
if (!empty($gifts)){
#region výpis wishlist
    echo '<table><tr>
<th>Dárek</th><th>Do kdy</th><th>Kategorie</th><th>Cena</th>
</tr>';
    foreach ($gifts as $gift){
        echo '<tr>
                <td>'.htmlspecialchars($gift['gift']).'</td>
                <td>'.htmlspecialchars(date_format(date_create_from_format('Y-m-d', $gift['until']), 'j. n. y')).'</td>
                <td>'.htmlspecialchars($gift['category_name']).'</td>
                <td>'.htmlspecialchars($gift['prices_from']).' - '.htmlspecialchars($gift['prices_upto']).'</td>
              </tr>';
        if ($gift['description']) {
            echo '<tr>
                <td colspan="4">'.htmlspecialchars($gift['description']).'</td>             
              </tr>';
        }
    }
    echo '</table>';
#endregion výpis wishlist
}else{
echo '<div class="alert alert-info">V seznamu přání nejsou zatím žádné dárky. Button</div>';
}


//vložíme do stránek patičku
include __DIR__.'/inc/footer.php';