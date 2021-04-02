<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['user_id'])){
    header('Location: index.php');
    //$_SESSION['error'] = 'Only logged in users can submit recipes. Please login.';
    return;
  }

  $stmt = $pdo -> prepare('SELECT * FROM recipeHead WHERE user_id=:user_id');
  $stmt -> execute(array(
    ':user_id' => $_SESSION['user_id']
  ));
  $recipes = $stmt -> fetchAll(PDO::FETCH_ASSOC);
  $output = json_encode($recipes);
  header('Content-Type: application/json; charset=utf-8');
  echo($output);
 ?>
