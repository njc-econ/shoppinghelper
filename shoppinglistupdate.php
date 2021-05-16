<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
  }

  // process post data and update database

try {
  $pdo -> beginTransaction();

  foreach ($_POST as $row){
    // if the item_id is stored check if the name still matches the item
    $item_id = $row['item_id'];

    if (!($item_id==="NA")){
      $stmt = $pdo -> prepare('SELECT itemname FROM shoppingItems WHERE item_id = :item_id');
      $stmt -> execute(array(
        ':item_id' => $row['item_id']
      ));
      $result = $stmt -> fetch(PDO::FETCH_ASSOC);

      if ($result['itemname']===trim($row['itemname'])){
        // if yes, check if the quantity matches what was already on the list or
        $stmt = $pdo -> prepare('SELECT quantity FROM shoppingList WHERE user_id=:user_id AND item_id=:item_id AND purchasedDT IS NULL AND inpantryDT IS NULL');
        $stmt -> execute(array(
          ':user_id' => $_SESSION['user_id'],
          ':item_id' => $row['item_id']
        ));
        $result = $stmt -> fetch(PDO::FETCH_ASSOC);

        if ($result['quantity'] !== $row['quantity']){
            $stmtUpdate = $pdo -> prepare('UPDATE shoppingList SET quantity = :quantity, modifiedDT = NOW() WHERE user_id = :user_id AND item_id=:item_id AND purchasedDT IS NULL AND inpantryDT IS NULL');
            $stmtUpdate -> execute(array(
              ':user_id' => $_SESSION['user_id'],
              ':item_id' => $row['item_id'],
              ':quantity' => $row['quantity']
            ));

            if ($stmtUpdate === false){
              throw new Exception('Unable to add data to database');
            }
        }

      } else {
        // if the item_id doesn't match the name:
        // remove the old item from the shopping list
        $stmtDelete = $pdo -> prepare('DELETE FROM shoppingList WHERE user_id = :user_id AND item_id = :item_id AND purchasedDT IS NULL AND inpantryDT IS NULL');
        $stmtDelete -> execute(array(
          ':user_id' => $_SESSION['user_id'],
          ':item_id' => $row['item_id'],
        ));

        if ($stmtDelete === false){
          throw new Exception('Unable to add data to database');
        }

        $item_id = "NA";

      }
    }
    if ($item_id === "NA"){
      // get the item_id, if not already existing add new item

      $stmt = $pdo -> prepare('SELECT item_id FROM shoppingItems WHERE itemname = :itemname AND lang_id = :lang_id');

      $result = $stmt -> execute(array(
        ':itemname' => trim($row['itemname']),
        ':lang_id' => $_SESSION['lang']
      ));

      $row = $stmt -> fetch(PDO::FETCH_ASSOC);

      if ($row === false || $row === 0){
        $stmtInsert = $pdo -> prepare('INSERT INTO shoppingItems (itemname, lang_id) VALUES (:itemname, :lang_id)');
        $stmtInsert -> execute(array(
          ':itemname' => trim($row['itemname']),
          ':lang' => $_SESSION['lang']
        ));
        $item_id = $pdo -> lastInsertId();
      } else {
        $item_id = $row['item_id'];
      }
      // update shopping list
      $stmtShopping = $pdo -> prepare('INSERT INTO shoppingList (user_id, item_id, quantity, addDT) VALUES (:user_id, :item_id, :quantity, NOW())');
      $stmtShopping -> execute(array(
        ':user_id' => $_SESSION['user_id'],
        ':item_id' => $item_id,
        ':quantity' => trim($row['quantity'])
      ));

    }

    // if the item has been bought update the shopping list
    if ($row['bought']==="1"){
      $stmt = $pdo -> prepare('UPDATE shoppingList SET boughtDT = NOW() WHERE user_id = :user_id AND item_id=:item_id');
      $stmt -> execute(array(
        ':user_id' => $_SESSION['user_id'],
        ':item_id' => $item_id
      ));
    }


    if ($stmt === false){
      throw new Exception('Unable to add data to database');
    }



  }

  $pdo -> commit();



} catch (Exception $e) {
  $pdo -> rollBack();
  $stmt = false;
  header('Content-Type: text/html; charset=UTF-8');
  echo $e -> getMessage();
  return;
}

header('Content-Type: text/html; charset=UTF-8');
echo $stmt !== false ? 1 : 0;

?>
