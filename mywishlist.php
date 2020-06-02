<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

//vložíme do stránek hlavičku
$pageTitle='Můj seznam přání';
include __DIR__.'/inc/header.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášný
    header('Location: index.php');
    exit();
}

echo '<h2>Můj seznam přání</h2>';
echo '<div class="btn-prani"><a href="gift.php" class="btn btn-primary">Přidat přání</a></div>';

if (!empty($_GET['category']) || !empty($_GET['prices'])){
//aspon jedna hodnota zvolena
    if (!empty($_GET['category'])) {
        if (!empty($_GET['prices'])) {
            //mame obe hodnoty
            $query = $db->prepare('SELECT
                gifts.*, categories.name AS category_name, prices_from, prices_upto
                FROM gifts JOIN categories USING (category_id) JOIN prices USING (prices_id) JOIN users ON users.user_id = gifts.gift_for
                WHERE gifts.category_id=:category AND gifts.prices_id =:prices AND gift_for=:user
                ORDER BY until ASC');
            $query->execute([
                ':category'=>$_GET['category'],
                ':prices'=>$_GET['prices'],
                ':user'=>$_SESSION['user_id']
            ]);
        } else {
            //mame jen kategorii
            $query = $db->prepare('SELECT
                gifts.*, categories.name AS category_name, prices_from, prices_upto
                FROM gifts JOIN categories USING (category_id) JOIN prices USING (prices_id) JOIN users ON users.user_id = gifts.gift_for
                WHERE gifts.category_id=:category AND gift_for=:user
                ORDER BY until ASC;');
            $query->execute([
                ':category'=>$_GET['category'],
                ':user'=>$_SESSION['user_id']
            ]);
        }

    } else {
        //mame jen cenu
        $query = $db->prepare('SELECT
                gifts.*, categories.name AS category_name, prices_from, prices_upto
                FROM gifts JOIN categories USING (category_id) JOIN prices USING (prices_id) JOIN users ON users.user_id = gifts.gift_for
                WHERE gifts.prices_id =:prices AND gift_for=:user
                ORDER BY until ASC;');
        $query->execute([
            ':prices'=>$_GET['prices'],
            ':user'=>$_SESSION['user_id']
        ]);
    }

} else {
//zadna hodnota
    $query = $db->prepare('SELECT
gifts.*, categories.name AS category_name, prices_from, prices_upto
FROM gifts JOIN categories USING (category_id) JOIN prices USING (prices_id) JOIN users ON users.user_id = gifts.gift_for
WHERE gift_for=:user
ORDER BY until ASC;');
    $query->execute([
        ':user'=>$_SESSION['user_id']
    ]);
};




#region formulář s výběrem kategorií
echo '<form method="get" id="categoryFilterForm">
    <label for="category">Kategorie:</label>
    <select name="category" id="category" onchange="document.getElementById(\'categoryFilterForm\').submit();">
        <option value="">--nerozhoduje--</option>';

        $categories=$db->query('SELECT * FROM categories ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($categories)){
        foreach ($categories as $category){
        echo '<option value="'.$category['category_id'].'"';//u category_id nemusí být ošetření speciálních znaků, protože jde o číslo
        if ($category['category_id']==@$_GET['category']){
        echo ' selected="selected" ';
        }
        echo '>'.htmlspecialchars($category['name']).'</option>';
        }
        }

        echo '  </select>
    <label for="prices">Cena:</label>
    <select name="prices" id="prices" onchange="document.getElementById(\'categoryFilterForm\').submit();">
        <option value="">--nerozhoduje--</option>';

        $prices=$db->query('SELECT * FROM prices ORDER BY prices_id;')->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($prices)){
        foreach ($prices as $price){
        echo '<option value="'.$price['prices_id'].'"';//u prices_id nemusí být ošetření speciálních znaků, protože jde o číslo
        if ($price['prices_id']==@$_GET['prices']){
        echo ' selected="selected" ';
        }
        echo '>'.htmlspecialchars($price['prices_from']).' - '.htmlspecialchars($price['prices_upto']).'</option>';
        }
        }

        echo '  </select>
     
  
    <input type="submit" value="OK" class="d-none" />
</form>';
#region formulář s výběrem kategorií

$gifts = $query->fetchAll(PDO::FETCH_ASSOC);
if (!empty($gifts)){
#region výpis wishlist
    echo '<table><tr>
<th>Dárek</th><th>Do kdy</th><th>Kategorie</th><th>Cena Kč</th>
</tr>';
    foreach ($gifts as $gift){
        if (strtotime($gift['until']) >= time()) {
        echo '<tr class="caranahore">
                <td>'.htmlspecialchars($gift['gift']).'</td>
                <td>'.htmlspecialchars(date('d. m. Y',strtotime($gift['until']))).'</td>
                <td>'.htmlspecialchars($gift['category_name']).'</td>
                <td>'.htmlspecialchars($gift['prices_from']).' - '.htmlspecialchars($gift['prices_upto']).'</td>
              </tr>';
        echo '<tr>
                <td colspan="3">'.htmlspecialchars($gift['description']).'</td>  
                <td class="odsazeni"><a href="gift.php?id='.$gift['gift_id'].'" class="btn btn-secondary">Upravit</a></td>           
              </tr>';
    }
    }
        foreach ($gifts as $gift){
            if (strtotime($gift['until']) < time()) {
                echo '<tr class="text-secondary">
                <td>'.htmlspecialchars($gift['gift']).'</td>
                <td>'.htmlspecialchars(date('d. m. Y',strtotime($gift['until']))).'</td>
                <td>'.htmlspecialchars($gift['category_name']).'</td>
                <td>'.htmlspecialchars($gift['prices_from']).' - '.htmlspecialchars($gift['prices_upto']).'</td>
              </tr>';
            }
        }
    echo '</table>';
#endregion výpis wishlist
}else{
echo '<div class="alert alert-info">V seznamu přání nejsou zatím žádné dárky. </div>';
}

//vložíme do stránek patičku
include __DIR__.'/inc/footer.php';