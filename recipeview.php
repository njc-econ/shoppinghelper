<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
    return;
  }

  // ensure the id of the recipe is present on the page and numeric
  if (!(isset($_GET['recipe_id']) || !is_numeric($_GET['recipe_id'])){
      header('Location: index.php');
      return;
  }

  // collect the details for the recipe
  // make sure the person in the session is the owner of the recipe
  $stmt <- $pdo -> prepare('SELECT * FROM recipeHead WHERE recipe_id = :recipe_id');
  $statementOutput = $stmt -> execute(array(
    ':recipe_id' => $_GET['recipe_id']
  ));
  $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);

  if (!(count($result)!== false && count($result)===1)){
    $_SESSION['error'] = 'Recipe not found'
    header('Location: index.php');
    return;
  }

  // collect the ingredients list
  


  // collect the steps list




 ?>
