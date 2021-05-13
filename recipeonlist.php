<?php
  require_once 'pdo.php';
  session_start();

  $stmt = $pdo -> prepare('
    SELECT
      sourcerecipe_id AS recipe_id
      ,title
      ,numserved
    FROM recipeShopping
    INNER JOIN recipeHead
    ON recipeShopping.sourcerecipe_id = recipeHead.recipe_id
    WHERE recipeShopping.user_id = :user_id AND toshoppingDT IS NULL'
  );

  $result = $stmt -> execute(array(
    ':user_id' => $_SESSION['user_id']
  ));
  $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
  $output = json_encode($result);
  header('Content-Type: application/json; charset=utf-8');
  echo($output);

?>
