<?php
  require_once 'pdo.php';
  session_start();

  if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to add recipe to your shopping list';
    header('Location: recipe.php?recipe_id='.$_POST['recipe_id']);
    return;
  }

  // make sure the recipe id is valid, is in the database and the user has access to it
  //$_GET['recipe_id']



  // if valid, add recipe ingredients to the shopping list

  $stmt = $pdo -> prepare('INSERT INTO shoppingList (user_id, item_id, quantity, measure, sourcerecipe_id ,addDT)
                  SELECT :user_id AS user_id, item_id, quantity, measure, :recipe_id AS sourcerecipe_id , NOW() as addDT
                  FROM recipeIngredients
                  JOIN ingredientShopping
                  ON recipeIngredients.ingredient_id=ingredientShopping.ingredient_id
                  WHERE recipe_id = :recipe_id;');
  $stmt -> execute(array(
    ':user_id' => $_SESSION['user_id'],
    ':recipe_id' => $_GET['recipe_id']
  ));
  header('Content-Type: text/html; charset=UTF-8');
  echo $stmt !== false ? 1 : 0;
?>
