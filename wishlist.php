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

if ($_REQUEST['id'] == $_SESSION['user_id']) {
    //uživatel jde na svuj profil
    header('Location: mywishlist.php');
    exit();
}


if (empty($_REQUEST['id'])) {
    //nemame urceny profil
    header('Location: users.php');
    exit();
}

//promenne
$user_id = $_REQUEST['id'];
$user_name = '';
$user_description = '';

$userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:id LIMIT 1;');
$userQuery->execute([
    ':id'=>$user_id
]);
if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)) {
    $user_name = $user['name'];
    $user_description = $user['description'];
}
echo '<h2>Seznam přání uživatele '.htmlspecialchars($user_name).'</h2>
            <div>'.htmlspecialchars($user_description).'</div>';


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
                ':user'=>$user_id
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
                ':user'=>$user_id
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
            ':user'=>$user_id
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
        ':user'=>$user_id
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
        echo '<tr>
                <td>'.htmlspecialchars($gift['gift']).'</td>
                <td>'.htmlspecialchars(date_format(date_create_from_format('Y-m-d', $gift['until']), 'j. n. y')).'</td>
                <td>'.htmlspecialchars($gift['category_name']).'</td>
                <td>'.htmlspecialchars($gift['prices_from']).' - '.htmlspecialchars($gift['prices_upto']).'</td>
              </tr>';
        echo '<tr>
                <td colspan="3">'.htmlspecialchars($gift['description']).'</td>  
                <td class="odsazeni">';
        if (empty($gift['gift_from']))
        echo '<a href="giftYES.php?id='.$gift['gift_id'].'" class="btn btn-secondary">Zamluvit</a>';
        echo '</td>           
              </tr>';
    }
    echo '</table>';
#endregion výpis wishlist
}else{
    echo '<div class="alert alert-info">V seznamu přání nemá uživatel zatím žádné dárky.</a></div>';
}


//vložíme do stránek patičku
include __DIR__.'/inc/footer.php';