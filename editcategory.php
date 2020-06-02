<?php
///načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';
//načteme inicializaci knihovny pro Facebook
require_once 'inc/facebook.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] != 'administrátor')){
    //uživatel není přihlášen nebo není administrátor
    header('Location: index.php');
    exit();
}

//pomocné proměnné pro přípravu dat do formuláře
$categoryId='';
$categoryName='';

#region načtení existující kategorie z DB
if (!empty($_REQUEST['id'])){
    $categoryQuery=$db->prepare('SELECT * FROM categories WHERE category_id=:id LIMIT 1;');
    $categoryQuery->execute([':id'=>$_REQUEST['id']]);
    if ($category=$categoryQuery->fetch(PDO::FETCH_ASSOC)){
        //naplníme pomocné proměnné daty příspěvku
        $categoryId=$category['category_id'];
        $categoryName=$category['name'];
    }else{
        exit('Kategorie neexistuje.');
    }
}
#endregion načtení existujícíh kategorie z DB

$errors=[];
if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola názvu
    $categoryName=trim(@$_POST['name']);
    if (empty($categoryName)){
        $errors['name']='Musíte zadat název kategorie.';
    } else { //kontrola duplicity
        $categoryQuery=$db->prepare('SELECT * FROM categories WHERE name=:name LIMIT 1;');
        $categoryQuery->execute([
            ':name'=>$_POST['name']
        ]);
        if ($categoryQuery->rowCount()>0){
            $errors['name']='Taková kategorie už existuje!';
        }
    }
    #endregion kontrola názvu

    if (empty($errors)){
        #region uložení dat

        if ($categoryId){
            #region aktualizace existující kategorie
            $saveQuery=$db->prepare('UPDATE categories SET name=:name WHERE category_id=:id LIMIT 1;');
            $saveQuery->execute([
                ':name'=>$categoryName,
                ':id'=>$categoryId
            ]);
            #endregion aktualizace existujícího příspěvku
        }else{
            #region uložení nové kategorie
            $saveQuery=$db->prepare('INSERT INTO categories (category_id, name) VALUES (NULL, :name);');
            $saveQuery->execute([
                ':name'=>$categoryName
            ]);
            #endregion uložení nové kategorie
        }

        #endregion uložení dat
        #region přesměrování
        header('Location: editcategories.php');
        exit();
        #endregion přesměrování
    }
    #endregion zpracování formuláře
}

//vložíme do stránek hlavičku
if ($categoryId){
    $pageTitle='Úprava kategorie';
}else{
    $pageTitle='Nová kategorie';
}

include 'inc/header.php';
?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $categoryId;?>" />

        <div class="form-group">
            <label for="name">Název kategorie:</label>
            <textarea name="name" id="name" required class="form-control <?php echo (!empty($errors['name'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($categoryName)?></textarea>
            <?php
            if (!empty($errors['name'])){
                echo '<div class="invalid-feedback">'.$errors['name'].'</div>';
            }
            ?>
        </div>

        <button type="submit" class="btn btn-primary">uložit...</button>
        <a href="editcategories.php" class="btn btn-light">zrušit</a>
        <?php
        if ($categoryId){
            echo '<a href="delete.php?type=categories&id='.$categoryId.'" class="btn btn-danger">smazat</a>';
        }
        ?>
    </form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';