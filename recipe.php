<?php
require_once "pdo.php";
session_start();

if (!isset($_SESSION['name'])){
  header('Location: index.php');
}

if (!isset($_GET['recipe_id']) || !is_int($_GET['recipe_id'])){
  $_SESSION['error'] = 'Recipe not found';
  header('Location: start.php');
  return;
}

// get the recipe out of the database

$stmt <- $pdo -> prepare('SELECT * FROM recipeHead WHERE recipe_id = :recipe_id');

$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));

$recipehead = $stmt -> fetch(PDO::FETCH_ASSOC);

if ($recipehead['private']===1 && $row['user_id']!==$_SESSION['user_id']) {
  $_SESSION['error'] = 'Only the creator can view the selected recipe.';
  header('Location: start.php');
  return;
}

// get the ingredients out of the database
$stmt <- $pdo -> prepare('SELECT recipe_id, ingredient_id, quantity, measure, name FROM recipeIngredients JOIN ingredients ON recipeIngredients.ingredient_id = ingredients.ingredient_id WHERE recipe_id = :recipe_id ORDER BY input_rank');
$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));

$recipeIngredients <- $stmt -> fetchAll(PDO::FETCH_ASSOC);

// get the instructions out of the database
$stmt <- $pdo -> prepare('SELECT * FROM recipeSteps WHERE recipe_id = :recipe_id');
$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));
$recipeSteps <- $stmt -> fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <h2 class="recipeTitle"><?= htmlentities($recipehead['title']) ?></h2>
  </body>
</html>
