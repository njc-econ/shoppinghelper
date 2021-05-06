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
      header('Location: shoppinglist.php');
      return;
    }
    // if not add to shopping list

      // first check if item has been logged before, if so collect item_id
      $stmt = $pdo -> prepare('SELECT item_id FROM shoppingItems WHERE itemname=:itemname AND lang=:lang');
      $stmtOutput = $stmt -> execute(array(
        ':itemname' => trim($_POST['newItem']),
        ':lang' => $_SESSION['lang']
      ));
      $row = $stmt -> fetch(PDO::FETCH_ASSOC);
      // if not insert into shopping items
      if ($row == false){
        $stmtInsert = $pdo -> prepare('INSERT INTO shoppingItems (itemname, lang) VALUES (:itemname, :lang)');
        $stmtInsert -> execute(array(
          ':itemname' => trim($_POST['newItem']),
          ':lang' => $_SESSION['lang']
        ));
        $item_id = $pdo -> lastInsertId();
      } else {
        $item_id = $row['item_id'];
      }

      // put on shopping list

    $stmt = $pdo -> prepare('INSERT INTO shoppingList (user_id, item_id, quantity, addDT) VALUES (:user_id, :item_id, :quantity, NOW())');
    $stmt -> execute(array(
      ':user_id' => $_SESSION['user_id'],
      ':item_id' => $item_id,
      ':quantity'=> htmlentities($_POST['newQuantity'])
    ));
  }

  if (isset($_POST['bought'])){
    $stmt = $pdo -> prepare('UPDATE shoppingList SET purchasedDT = NOW() WHERE (user_id = :user_id AND item_id = :item_id);');
    $stmt -> execute(array(
      ':user_id'=>$_SESSION['user_id'],
      ':item_id'=>$_POST['item_id']
    ));
  }

  // prepare output of the saved shopping list, includes buttons
  // to remove item and mark as bought
  $stmt = $pdo -> prepare('SELECT shoppingList.item_id, itemname, quantity FROM shoppingList JOIN shoppingItems ON shoppingList.item_id = shoppingItems.item_id WHERE user_id = :user_id AND purchasedDT IS NULL');
  $statementOutput = $stmt -> execute( array(
    ':user_id' => $_SESSION['user_id']));
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
    <?php require_once("headerscript.php") ?>
  </head>

  <header>
    <?php require_once("headerIn.html") ?>
  </header>


  <body>
    <!-- First list all the recipes that have been added to
         the shopping list, allow the user to remove them easily -->
    <h2>Recipes on Shopping List</h2>
    <div class="recipeList" id="recipeList">

    </div>


    <!-- Then list the consolidated ingredients of the recipes,
         allow the user to modify the quantities -->
    <h2>Recipe Items</h2>
    <div class="recipeItems" id="recipeItems">

    </div>


    <h2>Your Shopping List</h2>
    <!-- Then allow the user to add additional items -->



    <table>
      <tr>
        <form method="post">
          <td>
            <input type="text" name="newQuantity" placeholder="Quantity" id="newQuantity" size= "10px">
           </td>
        <td>
          <input type="text" name="newItem" placeholder="Item" id="newItem" size="50px">
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

<script type="text/javascript">

    function recipeRemove(id) {
      $.post("recipeshopremove.php?recipe_id="+id, function(data){
        if (data==="1"){
          location.href = 'shoppinglist.php';
        }
      });
    }


    // populate a list of recipes on shopping list
    $.getJSON("recipeonlist.php", function(data){
      if (data.length > 0){
        tableText = '<table id="shopRecipe">';
        tableText += '<tr><th>Recipe Name</th><th>Serves</th><th></th></tr>';
        for (i in data){
          tableText += '<tr><td><a href="recipe.php?recipe_id='+data[i].recipe_id+'">'+data[i].title +'</a></td><td>'+data[i].numserved;
          tableText += '</td><td><input type="hidden" name="recipe_id" value='+data[i].recipe_id+'>';
          tableText += '<input type="submit" name="remove" value="Remove" onclick="recipeRemove('+data[i].recipe_id+')"></form></td>';

          tableText += '</td></tr>';
        }
        tableText += '</table>';
        $('#recipeList').append(tableText);
      }

    });

    // populate the table for items for recipes.

    $.getJSON("shoppinglistrecipes.php", function(data){
      if (data.length > 0) {

        tableText = '<table id=recipeIngred>';
        for (i in data){
            tableText += '<tr><td>';
            tableText += '<input type="hidden" name="item_id" value="'+data[i].item_id+'">';
            tableText += '<div >'+data[i].quantity +data[i].measure+'</div></td>';
            tableText += '<td>'+data[i].name +'</td>';
            tableText += '<td><form method="post">';
            tableText += '<input type="hidden" name="item_id" value='+data[i].item_id+'>';
            tableText += '<input type="submit" name="bought" value="Bought" id="'+"item"+i+'"></form></td>';
            tableText += '</tr>';
        }
        tableText += '</table>';
        $('#recipeItems').append(tableText);
      }

    });

    // process data to send to server

    $(document).ready(


      function(){
        console.log("Hello Nathan");
        if (! document.getElementById("recipeIngred") == null){
          elementsOnList = document.getElementById("recipeIngred").childElementCount;
          console.log(elementsOnList);
          if (elementsOnList > 0){
            for (i=0; i<elementsOnList; i++){
              console.log(i);
            }

          }
        }

        $('#item1').click(
          function(event){
            event.preventDefault();
            console.log("What now?");
          }
        );

      }
    )

</script>
