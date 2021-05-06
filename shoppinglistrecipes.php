<?php
  require_once 'pdo.php';
  session_start();

  $stmt = $pdo -> prepare('
  SELECT
  ingredientShopping.item_id,
  SUM(recipeIngredients.quantity) AS quantity,
  recipeIngredients.measure,
  ingredients.name
  FROM recipeShopping
  JOIN recipeIngredients
  ON recipeShopping.sourcerecipe_id = recipeIngredients.recipe_id
  JOIN ingredientShopping
  ON recipeIngredients.ingredient_id=ingredientShopping.ingredient_id
  JOIN ingredients
  ON recipeIngredients.ingredient_id = ingredients.ingredient_id
  WHERE user_id = :user_id
  GROUP BY ingredientShopping.item_id, recipeIngredients.measure, ingredients.name
  ');

  $result = $stmt -> execute(array(
    ':user_id' => $_SESSION['user_id']
  ));
  $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
  $output = json_encode($result);
  header('Content-Type: application/json; charset=utf-8');
  echo($output);
?>
