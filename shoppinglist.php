<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
  }
  $message = false;
  if (isset($_POST['itemSubmit'])) {
    // if item is empty return an error
    if (strlen($_POST['newItem'])<1) {
      $message = "Items on shopping list must be named";
    }
    // if not add to shopping list
    $stmt = $pdo -> prepare('INSERT INTO shoppingList (user_id, itemname, quantity, addDT) VALUES (:user_id, :itemname, :quantity, NOW())');
    $stmt -> execute(array(
      ':user_id'=>$_SESSION['userid'],
      ':itemname'=>htmlentities($_POST['newItem']),
      ':quantity'=>htmlentities($_POST['newQuantity'])
    ));
  }

  if (isset($_POST['bought'])){
    $stmt = $pdo -> prepare('UPDATE shoppingList SET purchasedDT = NOW() WHERE (user_id = :user_id AND item_id = :item_id);');
    $stmt -> execute(array(
      ':user_id'=>$_SESSION['userid'],
      ':item_id'=>$_POST['item_id']
    ));
  }

  // prepare output of the saved shopping list, includes buttons
  // to remove item and mark as bought
  $stmt = $pdo -> prepare('SELECT item_id, itemname, quantity FROM shoppingList WHERE user_id = :user_id AND purchasedDT IS NULL');
  $statementOutput = $stmt -> execute( array(
    ':user_id' => $_SESSION['userid']));
  $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
  //print_r($result[0]['itemname']);
  $tablerow = '';
  if (count($result)!== false){
    $tablerow = "<table>";
    for ($i = 0; $i < count($result); $i++) {
      $tablerow = $tablerow.'<tr><td>'.$result[$i]['quantity']."</td><td>".$result[$i]['itemname'].'</td><td><form method="post">
        <input type="hidden" name="item_id" value='.$result[$i]["item_id"].'>
      <input type="submit" name="bought" value="Bought"></form>'."</td></tr>";
    }
    $tablerow = $tablerow."</table>";
  }



?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: Shopping List</title>
  </head>
  <body>

    <h1>Your shopping list</h1>
    <table>
      <tr>
        <form method="post">
        <td>
          <input type="text" name="newItem" placeholder="Item" id="newItem" size="50px">
         </td>
         <td>
           <input type="text" name="newQuantity" placeholder="Quantity" id="newQuantity" size= "10px">
          </td>
          <td>
            <input type="submit" name="itemSubmit" value="Add" id="newQuantity" size= "20px">
           </td>
        </form>
      </tr>
    </table>
    <p style="color: red;"><?php echo $message ?></p>
    <p> <?php echo $tablerow ?> </p>
  </body>
</html>
