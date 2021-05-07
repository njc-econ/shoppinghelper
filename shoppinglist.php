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
    <p style="color: red;"><?php echo $message ?></p>
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
    <div class="listItems" id="listItems">

    </div>

    <div>
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
              <input type="submit" name="itemSubmit" value="Add" id="itemSubmit" size= "20px" onclick="addItem(); return false">
             </td>
          </form>
        </tr>
      </table>
    </div>





  </body>
</html>

<script type="text/javascript">



    function addItem() {
      // validate the data
      // console.log($("#newItem"));
      var itemname = $("#newItem").val();
      if (itemname.length == 0){
        alert("Item name cannot be blank.");
        return false;
      }


      // collect the quantity element
      var quant = $("#newQuantity").val();

      tableContent = '<tr><td><span name="quant">'+quant+'</span></td>';
      tableContent += '<td>'+itemname+'</td>';
      tableContent += '<td><input type="hidden" name="item_id" value="NA">';
      tableContent += '<input type="submit" name="bought" value="Bought" onclick="boughtItem(this)"></td>';
      tableContent += '<td><button type="button" onclick="editQuantity(this)">Edit</button></td></tr>';

      // check if there is already a shopping list on the page
      var table = $("#listItems").children();
      // if not create the table put the data in
      if (table.length == null){
        var tableText = '<table id=shoppingTable>';
        tableText += tableContent + '</table>';
        $('#listItems').append(tableText);
        $('#listItems').append('<input type="submit" value="Save Changes" id="saveToCloud" onclick="saveToCloud()">');
      } else {
        var tableParent = $("#listItems").find("tr").parent();
        tableParent.append(tableContent);
      }

    }

    function recipeRemove(id) {
      $.post("recipeshopremove.php?recipe_id="+id, function(data){
        if (data==="1"){
          location.href = 'shoppinglist.php';
        }
      });
    }

    function itemInPantry(btn) {
      var saveid = $(btn).parents("tr");

      // strike through the text for the items
      $(saveid).children("td").css("text-decoration","line-through");
      var button = $(saveid).children("td").children('input[type="submit"]');

      // remove (hide) the in pantry button
      $(button).attr("type","hidden");

    }



    function editQuantity(btn) {
      // edit the given row of the table to allow the user to edit the
      // quantity
      var saveid = $(btn).parents("tr");

      // take the info
      var quant = $(saveid).children("td").children("span[name='quant']");
      var item = $(saveid).children("td").children("span[name='itemname']");
      var oldquant = quant.text();
      var olditem = item.text();
      quant.text('');
      quant.append('<input type="text" name="quantity" value="'+oldquant+'" size="10">');
      item.text('');
      item.append('<input type="text" name="newItem" placeholder="Item" id="newItem" size="50px" value="'+olditem+'" on>');

      var edit = $(saveid).children("td").children("button");
      $(edit).text('Update');
      $(edit).attr("onclick","updateQuantity(this)");

    }

    function updateQuantity(btn) {
      var saveid = $(btn).parents("tr");

      // take the info
      var quant = $(saveid).children("td").children("span[name='quant']").children("input");
      var oldquant = quant.attr("value");
      $(saveid).children("td").children("span[name='quant']").empty();
      $(saveid).children("td").children("span[name='quant']").text(oldquant);

      var item = $(saveid).children("td").children("span[name='itemname']").children("input");
      var olditem = item.attr("value");
      $(saveid).children("td").children("span[name='itemname']").empty();
      $(saveid).children("td").children("span[name='itemname']").text(olditem);


      //quant.text('');
      //console.log(oldquant);
      var update = $(saveid).children("td").children("button");
      $(update).text('Edit');
      $(update).attr("onclick","editQuantity(this)");

    }

    function saveToCloud() {
      // user edits the shopping list data on the browser,
      // hits button to save changes so that data on server updates
      // i.e. when two people are active on the list other user sees changes


    }

    function boughtItem(btn) {

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
            tableText += data[i].quantity+'<span name="measure">' +data[i].measure+'</span></td>';
            tableText += '<td>'+data[i].name +'</td>';
            tableText += '<td>';
            tableText += '<input type="hidden" name="item_id" value='+data[i].item_id+'>';
            tableText += '<input type="submit" name="inpantry" value="In Pantry" id="'+"item"+i+'" onclick="false; itemInPantry(this)"></td>';
            tableText += '</tr>';
        }
        tableText += '</table>';
        $('#recipeItems').append(tableText);
        $('#recipeItems').append('<button type="button" onclick="">Add to Shopping List</button>')
      }

    });

    $.getJSON("shoppinglistcollect.php", function(data){
      if (data.length > 0) {

        var tableText = '<table id=shoppingTable>'

          for (i in data) {
            tableText += '<tr><td><span name="quant">'+data[i].quantity+'</span></td><td><span name="itemname">'+data[i].itemname+'</span></td>';
            tableText += '<td><input type="hidden" name="item_id" value="'
            tableText += data[i].item_id+'"><input type="submit" name="bought" value="Bought" onclick="boughtItem(this)"></td>';
            tableText += '<td><button type="button" onclick="editQuantity(this)">Edit</button></td></tr>';
          }
          tableText += "</table>";
        }
        $('#listItems').append(tableText);
        $('#listItems').append('<input type="submit" value="Save Changes" id="saveToCloud" onclick="saveToCloud()">');
    });


    // process data to send to server

    $(document).ready(


      function(){
        var counter = 0;
        console.log("Hello Nathan"+counter);
        if (! document.getElementById("#recipeIngred") == null){
          elementsOnList = document.getElementById("#recipeIngred").childElementCount;
          console.log(elementsOnList);
          if (elementsOnList > 0){
            for (i=0; i<elementsOnList; i++){
              console.log(i);
            }

          }
        }


      }
    )

</script>
