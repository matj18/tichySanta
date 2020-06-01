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

//pomocné proměnné pro přípravu dat do formuláře
$gift_id='';
$gift_name='';
$gift_description='';
$gift_category='';
$gift_prices='';
$gift_until='';
$gift_for = $_SESSION['user_id'];


#region načtení existujícího dárku z DB
if (!empty($_REQUEST['id'])){
    $giftQuery=$db->prepare('SELECT * FROM gifts WHERE gift_id=:id LIMIT 1;');
    $giftQuery->execute([':id'=>$_REQUEST['id']]);
    if ($gift=$giftQuery->fetch(PDO::FETCH_ASSOC)){
        //naplníme pomocné proměnné daty
        $gift_id=$gift['gift_id'];
        $gift_name=$gift['gift'];
        $gift_description=$gift['description'];
        $gift_category=$gift['category_id'];
        $gift_prices=$gift['prices_id'];
        $gift_until=$gift['until'];
        if ($gift_for != $gift['gift_for']) {
            //uzivatel chce editovat cizi darek
            header('Location: index.php');
            exit(); //todo smer
        }
    }
}
#endregion načtení existujícího dárku z DB

$errors=[];
if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola nazvu
    $gift_name=trim(@$_POST['name']);
    if (empty($gift_name)){
        $errors['name']='Zadejte dárek';
    }
    #endregion kontrola nazvu
    #region kontrola kategorie
    if (!empty($_POST['category'])){

        $categoryQuery=$db->prepare('SELECT * FROM categories WHERE category_id=:category LIMIT 1;');
        $categoryQuery->execute([
            ':category'=>$_POST['category']
        ]);
        if ($categoryQuery->rowCount()==0){
            $errors['category']='Zvolená kategorie neexistuje!';
            $gift_category='';
        }else{
            $gift_category=$_POST['category'];
        }

    }else{
        $errors['category']='Musíte vybrat kategorii.';
    }
    #endregion kontrola kategorie
    #region kontrola prices
    if (!empty($_POST['prices'])){

        $categoryQuery=$db->prepare('SELECT * FROM prices WHERE prices_id=:price LIMIT 1;');
        $categoryQuery->execute([
            ':price'=>$_POST['prices']
        ]);
        if ($categoryQuery->rowCount()==0){
            $errors['prices']='Zvolená kategorie cen neexistuje!';
            $gift_prices='';
        }else{
            $gift_prices=$_POST['prices'];
        }

    }else{
        $errors['prices']='Musíte vybrat kategorii cen.';
    }
    #endregion kontrola prices
    #region kontrola until
    //todo nefunguje
    if (!empty($_POST['until'])) {
        $d=strtotime($_POST['until']);
        if ($d < strtotime(date('D-M-Y'))) {
            $errors['until'] = 'Vyberte datum v budoucnosti.';
        }
    } else {
        $errors['until']='Vyberte datum.';
    }
    #endregion kontrola until

    #region pro data bez kontroly
    $gift_description=$_POST['description'];
    #endregion pro data bez kontroly

    if (empty($errors)){
        #region uložení dat

        if ($gift_id){
            #region aktualizace existujícího příspěvku
            $saveQuery=$db->prepare('UPDATE gifts SET gift=:name, description=:description, category_id=:category, prices_id=:prices WHERE gift_id=:id LIMIT 1;');
            $saveQuery->execute([
                ':name' =>$gift_name,
                ':description' =>$gift_description,
                ':category'=>$gift_category,
                ':prices'=>$gift_prices,
                //':until'=>$gift_until,
                ':id'=>$gift_id
            ]);
            #endregion aktualizace existujícího příspěvku
        }else{
            #region uložení nového příspěvku
            $saveQuery=$db->prepare('INSERT INTO gifts (gift, description, category_id, prices_id, gift_for) VALUES (:name, :description, :category, :prices, :user);');
            $saveQuery->execute([
                ':name' =>$gift_name,
                ':description' =>$gift_description,
                ':category'=>$gift_category,
                ':prices'=>$gift_prices,
                //':until'=>$gift_until,
                ':user'=>$gift_for,
            ]);
            #endregion uložení nového příspěvku
        }

        #endregion uložení dat
        #region přesměrování
        header('Location: wishlist.php'); //todo smer
        exit();
        #endregion přesměrování
    }
    #endregion zpracování formuláře
}

//vložíme do stránek hlavičku
if ($gift_id){
    $pageTitle='Editace dárku';
}else{
    $pageTitle='Nový dárek';
}

include 'inc/header.php';
?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $gift_id;?>" />

        <div class="form-group">
            <label for="name">Název dárku:</label>
            <input type="text" name="name" id="name" required class="form-control <?php echo (!empty($errors['name'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars($gift_name)?>"/>
            <?php
            if (!empty($errors['name'])){
                echo '<div class="invalid-feedback">'.$errors['name'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="description">Popis:</label>
            <textarea name="description" id="description"><?php echo htmlspecialchars($gift_description)?></textarea>
        </div>

        <div class="form-group">
            <label for="category">Kategorie:</label>
            <select name="category" id="category" required class="form-control <?php echo (!empty($errors['category'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $categoryQuery=$db->prepare('SELECT * FROM categories ORDER BY category_id;');
                $categoryQuery->execute();
                $categories=$categoryQuery->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($categories)){
                    foreach ($categories as $category){
                        echo '<option value="'.$category['category_id'].'" '.($category['category_id']==$gift_category?'selected="selected"':'').'>'.htmlspecialchars($category['name']).'</option>';
                    }
                }
                ?>
            </select>
            <?php
            if (!empty($errors['category'])){
                echo '<div class="invalid-feedback">'.$errors['category'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="prices">Kategorie ceny:</label>
            <select name="prices" id="prices" required class="form-control <?php echo (!empty($errors['prices'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $pricesQuery=$db->prepare('SELECT * FROM prices ORDER BY prices_id;');
                $pricesQuery->execute();
                $prices=$pricesQuery->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($prices)){
                    foreach ($prices as $price){
                        echo '<option value="'.$price['prices_id'].'" '.($price['prices_id']==$gift_prices?'selected="selected"':'').'>'.htmlspecialchars($price['prices_from']).' - '.htmlspecialchars($price['prices_upto']).'</option>';
                    }
                }
                ?>
            </select>
            <?php
            if (!empty($errors['prices'])){
                echo '<div class="invalid-feedback">'.$errors['prices'].'</div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="until">Do kdy:</label>
            <input name="until" id="until" type="date" required class="form-control <?php echo (!empty($errors['until'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars($gift_until)?>" />
            <?php
            if (!empty($errors['until'])){
                echo '<div class="invalid-feedback">'.$errors['until'].'</div>';
            }
            ?>
        </div>

        <button type="submit" class="btn btn-primary">uložit...</button>
        <a href="mywishlist.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';