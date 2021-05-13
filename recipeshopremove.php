<?php
  require_once 'pdo.php';
  session_start();

  if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to add recipe to your shopping list';
    header('Location: recipe.php?recipe_id='.$_POST['recipe_id']);
    return;
  }

  // need to check that the recipe id is valid, but not so important given
  // that for something to be deleted it must already be on the shopping list,
  // if not 0 rows will be deleted

  $stmt = $pdo -> prepare('DELETE FROM recipeShopping WHERE user_id = :user_id AND sourcerecipe_id = :recipe_id AND toshoppingDT IS NULL');
  $result = $stmt -> execute(array(
    ':user_id' => $_SESSION['user_id'],
    'recipe_id' => $_GET['recipe_id']
  ));


  /*
  $stmt = $pdo -> prepare('DELETE FROM shoppingList WHERE user_id = :user_id AND sourcerecipe_id = :recipe_id');
  $result = $stmt -> execute(array(
    ':user_id' => $_SESSION['user_id'],
    'recipe_id' => $_GET['recipe_id']
  ));
  */


  header('Content-Type: text/html; charset=UTF-8');
  echo $stmt !== false ? 1 : 0;
?>
