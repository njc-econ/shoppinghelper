<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
  }

  $stmt = $pdo -> prepare('SELECT shoppingList.item_id, itemname, quantity, measure FROM shoppingList JOIN shoppingItems ON shoppingList.item_id = shoppingItems.item_id WHERE user_id = :user_id AND purchasedDT IS NULL');
  $statementOutput = $stmt -> execute( array(
    ':user_id' => $_SESSION['user_id']));
  $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);

  $output = json_encode($result);
  header('Content-Type: application/json; charset=utf-8');
  echo($output);

?>
