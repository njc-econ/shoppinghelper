<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['user_id'])){
    header('Location: index.php');
    $_SESSION['error'] = 'Only logged in users can submit recipes. Please login.';
    return;
  }

if (isset($_POST["recipesubmit"])){


  // Validate post data:
  // title
  if (!isset($_POST['recipetitle']) || strlen($_POST['recipetitle']) < 1){
    $_SESSION['error'] = 'Recipe title is required.';
    header('Location: recipeinput.php');
    return;
  }

  if (strlen($_POST['recipetitle']) > 255){
    $_SESSION['error'] = 'Recipe title is too long, please abbreviate. Max 255 characters.';
    header('Location: recipeinput.php');
    return;
  }

  // no. served, must be integer
  if (!is_numeric($_POST['serves'])){
    $_SESSION['error'] = 'The number of people served by the recipe must be provided and be a whole number.';
    header('Location: recipeinput.php');
    return;
  }

    require_once('utilsValidation.php');


    validateIngredients('Location: recipeinput.php');



    validateSteps('Location: recipeinput.php');

    // validate the language

    $lang_id = validateLanguage('Location: recipeinput.php', $pdo);


    // photo upload validation
    $isimage = validateImage('Location: recipeinput.php');

    if ($isimage !== true){
      header('Location: recipeinput.php');
      return;
    }

    //echo "Upload: " . $_FILES["photoUpload"]["name"] . "<br />";
    //echo "Type: " . $_FILES["photoUpload"]["type"] . "<br />";
    //echo "Size: " . ($_FILES["photoUpload"]["size"] / 1024) . " Kb<br />";
    //echo "Temp file: " . $_FILES["photoUpload"]["tmp_name"] . "<br />";
    //echo "Title: ".$_POST["recipetitle"]. "<br />";


    //[$width, $height, $type, $attr] = getimagesize($_FILES["photoUpload"]["tmp_name"]);

    //echo "Width: ".$width. "<br />";
    //echo "Height: ".$height. "<br />";
    //echo "Type: ".$type. "<br />";
    //echo "Attr: ".$attr. "<br />";
    //echo "Language: ".$_POST["lang"]. "<br />";








    try {
      $pdo -> beginTransaction();

      $stmt = $pdo -> prepare('INSERT INTO recipeHead (title, recipeType_id, vegetarian, vegan, glutenfree, numserved, private, fork_id, user_id, lang_id)
                               VALUES (:tit, :recipeType_id, :veggie, :vegan, :glutenfree, :served, :private, :fork, :user, :lang_id)');
      $stmt -> execute(array(
        ':tit' => $_POST['recipetitle'],
        ':recipeType_id' => $_POST['recipeType'],
        ':veggie' => isset($_POST['vegetarian']) ? 1 : 0,
        ':vegan' => isset($_POST['vegan']) ? 1 : 0,
        ':glutenfree' => isset($_POST['glutenfree']) ? 1 : 0,
        ':served' => $_POST['serves'],
        ':private' => $_POST['recipeShare'],
        ':fork' => isset($_GET['fork_id']) ? $_GET['fork_id'] : null,
        ':user' => $_SESSION['user_id'],
        ':lang_id' => $lang_id
      ));


      $recipe_id = $pdo -> lastInsertId();

      // check if ingredients are already listed in the ingredients table
      // if so collect the ingredient id
      // if not add the ingredient and get the ingredient id
      $noIngredients = $_POST['noIngreds_1'];

      for ($i = 0; $i < $noIngredients; $i++){
        $stmt = $pdo -> prepare('SELECT ingredient_id FROM ingredients WHERE name = :ingredientName AND lang_id = :lang_id');
        $result = $stmt -> execute(array(
          ':ingredientName' => trim($_POST['ingredient'.$i]),
          ':lang_id' => $lang_id
        ));
        $row = $stmt -> fetch(PDO::FETCH_ASSOC);
        if ($row === false || $row === 0){
          print_r(1);
          $stmtInsert = $pdo -> prepare('INSERT INTO ingredients (name, lang_id) VALUES (:ingredientName, :lang_id)');
          $stmtInsert -> execute(array(
            ':ingredientName' => trim($_POST['ingredient'.$i]),
            ':lang_id' => $lang_id
          ));
          $ingredient_id = $pdo -> lastInsertId();
        } else {
          $ingredient_id = $row['ingredient_id'];
        }
        // insert ingredient into recipe table
        $stmt = $pdo -> prepare('INSERT INTO recipeIngredients (recipe_id, ingredient_id, quantity, measure, input_rank) VALUES (:recipe_id, :ingredient_id, :quantity, :measure, :input_rank);');
        $stmt -> execute(array(
            ':recipe_id' => $recipe_id,
            ':ingredient_id' => $ingredient_id,
            ':quantity' => $_POST['quantity'.$i],
            ':measure' => $_POST['measure'.$i],
            ':input_rank' => $i +1
        ));

      }

      $noSteps = $_POST['noSteps_1'];


      // next insert the steps to cook the recipe
      for ($i = 0; $i < $noSteps; $i++){
        print_r($i);
        $stmt = $pdo -> prepare('INSERT INTO recipeSteps (recipe_id, stepNumber, stepTitle, stepText) VALUES (:recipe_id, :stepNumber, :stepTitle, :stepText);');
        $stmt -> execute(array(
          ':recipe_id' => $recipe_id,
          ':stepNumber' => $i +1,
          ':stepTitle' => $_POST['stepTitle'.$i],
          ':stepText' => $_POST['stepText'.$i]
        ));


      }

      // save the uploaded image, list the name of the file in the database
      if (isset($_FILES["photoUpload"]) && $_FILES["photoUpload"]["name"]!==""){



        $target_dir = "/Applications/MAMP/htdocs/shoppinghelper/img/recipes/";

        $filename = tempnam($target_dir,'');
        unlink($filename);
        $target_file = $target_dir.basename($_FILES["photoUpload"]["name"]);

        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $savelocation = $filename.".".$imageFileType;
        $filename = basename($filename).".".$imageFileType;
        echo "Image file type: ".$imageFileType. "<br />";



        $succ = move_uploaded_file($_FILES["photoUpload"]["tmp_name"], $savelocation);
        //echo $succ. "<br />";

        if ($succ === true){
          $stmt = $pdo -> prepare('INSERT INTO images (recipe_id, filename, image_rank, upload_date) VALUES (:recipe_id, :filename, :image_rank, NOW())');

          $stmt -> execute(array(
              ':recipe_id' => $recipe_id,
              ':filename' => $filename,
              ':image_rank' => 0
            )
          );
        }

      }


      $pdo -> commit();
      header('Location: recipe.php?recipe_id='.$recipe_id);
      $_SESSION['success'] = 'Recipe successfully loaded.';
      return;

    } catch(Exception $e) {
      $pdo -> rollBack();
      $_SESSION['error'] = 'Error saving recipe to database.'.$e;
      header('Location: recipeinput.php');
      return;
    }
}


?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: Add new recipe</title>
    <?php require_once("headerscript.php") ?>
  </head>

  <header>
    <?php require_once("headerIn.html") ?>
  </header>

  <body>
    <h1>Your Recipe</h1>

    <p class='errormessage'><?php if (isset($_SESSION['error'])){echo $_SESSION['error']; unset($_SESSION['error']);} ?></p>

    <form action="recipeInput.php" method="post" enctype="multipart/form-data">
      <label for="recipetitle">Recipe Title:</label><input class="recipeInput" type="text" name="recipetitle" id="recipetitle" value=""><br>
      <label for="recipeShare">Recipe privacy status: </label>
      <select class="recipeShare" name="recipeShare">
        <option value="0" selected>Public</option>
        <option value="1">Private</option>
      </select><br>


      <label for="photoUpload">Upload an appetizing image:</label>
      <input type="file" name="photoUpload" id="photoUpload">

      <h4>Recipe Characteristics</h4>

      <select class="lang" name="lang" id="recipelang">
          <option value="de">German</option>
          <option value="en" selected>English</option>
          <option value="es">Spanish</option>
      </select>

      <p>
      <input type="checkbox" name="vegetarian" value="veggie" name="recipechar1" selected><label for="recipechar1">Vegetarian</label><br>
      <input type="checkbox" name="vegan" value="vegan" name="recipechar2"><label for="recipechar2">Vegan</label><br>
      <input type="checkbox" name="glutenfree" value="glutenfree" name="recipechar3"><label for="recipechar3">Gluten Free</label><br>
      </p>
      <p>
      <label for="serves">Number served: </label><input type="number" name="serves" value="2" min="1" max="10" step="1"><br>
      </p>


      <select class="recipeType" name="recipeType">
        <option value="1">Starter</option>
        <option value="2" selected>Main meal</option>
        <option value="3">Snack</option>
        <option value="4">Dessert</option>
        <option value="5">Other</option>
      </select>

      <!-- add ingredients -->



      <div class="add_recipe_ingredient">

          <h4>Add Ingredients</h4>
          <table>
            <tr>
              <th>Quantity</th><th>Measure</th><th>Ingredient</th>
            </tr>
            <tr>
              <td><input type="text" name="quantity" value="" size="10" id="quantity"></td> <!-- use javascript to ensure value is numeric -->
              <td><select class="measure" name="measure" id="measure">
                <option value="" selected></option>
                <option value="g">g</option>
                <option value="kg">kg</option>
                <option value="ml">ml</option>
                <option value="l">l</option>
              </select></td>
              <td><input type="text" name="addIngredient" value="" class="inputIngredient" placeholder="Ingredient name" id="ingredient"><input type="submit" name="ingredientSubmit" value="submit" id="addIngredient"></td>

            </tr>
          </table>

    <!-- use javascript to fill in a table on the browser, ingredient by ingredient -->
          <table id="ingredientsTable">
            <thead >
              <tr hidden="true" id="ingredtableheadings">
              <th>Quantity</th>
              <th></th>
              <th>Ingredient</th>
              <th>Delete</th>
            </tr>
            </thead>
            <input type="hidden" name="noIngreds_1" id="noIngreds_1" value="0">
          </table>

      </div>


      <!-- add recipe steps -->
      <h4>Add recipe steps </h4>
      <!-- image upload -->
      <div id="recipeSteps">
      </div>
      <input id="addStep" type="submit" value="+">
      <input type="hidden" name="noSteps_1" id="noSteps_1" value="0">
      <br>
      <button type="submit" name="recipesubmit">Upload Recipe <i class="bi bi-cloud-upload"></i></button>




    </form>
  </body>

  <script type="text/javascript">

    function displayDropdown(inputElement) {
      let x = inputElement.nextElementSibling;
      if (x.style.display=="inline-block") {
        x.style.display="none";
        return false;
      }
      x.style.display="inline-block";
    }


    function validateIngredient() {
      quantity = $('#quantity').val();
      measure = $('#measure').val();
      ingredient = $('#ingredient').val();
      if (ingredient.length < 1){
        alert("Please input an ingredient name");
        return false;
      }

      return true;
    }



    function addIngredient() {
      if ($("#ingredientsTable tbody").length ==0) {
        $("#ingredientsTable").append("<tbody></tbody>");
        $("#ingredtableheadings").removeAttr("hidden");
      }

      noIngredients = $("#ingredientsTable tbody tr").length;
      $("#noIngreds_1").val(noIngredients+1);
      $("#ingredientsTable tbody").append(
        '<tr id="ingred'+noIngredients+'">'+
        '<td>'+'<input type="hidden" name="quantity'+noIngredients+'" id="quantity'+noIngredients+'" value="'+$('#quantity').val()+'">'+$('#quantity').val()+'</td>'+
        '<td>'+'<input type="hidden" name="measure'+noIngredients+'" id="measure'+noIngredients+'" value="'+$('#measure').val()+'">'+$('#measure').val()+'</td>'+
        '<td>'+'<input type="hidden" name="ingredient'+noIngredients+'" id="ingredient'+noIngredients+'" value="'+$('#ingredient').val()+'">'+$('#ingredient').val()+'</td>'+
        '<td>'+'<button type="button" name="button" onclick="delIngredient(this);">Remove</button></td>'+
        '</tr>'
      );

      $('#quantity').val("")
      $('#measure').val("")
      $('#ingredient').val("")
    }

    function validateSteps() {
      noSteps = $("#recipeSteps div").length;
      //console.log(noSteps);
      if (noSteps>0){
        for (var i=0; i<noSteps; i++){
          stepTitle = $("#stepTitle"+i).val();
          stepText = $("#stepTitle"+i).val();
          if (stepTitle.length < 1 || stepText.length < 1){
            alert("Please input in available steps before adding new ones.");
            return false;
          }
        }
      }
      return true;
    }

    function delIngredient(btn) {
      noIngredients = $("#ingredientsTable tbody tr").length;
      saveid = $(btn).parents("tr");
      $(saveid).remove();
      deleteItem = saveid.attr('id').replace('ingred','');
      deletedRow = parseInt(deleteItem);
      if (noIngredients === 1){
        $("#ingredtableheadings").attr("hidden","true");
      }
      $("#noIngreds_1").val(noIngredients-1);
      // for all rows in the table with an id above the
      // deleted item, reduce by 1

      if (deletedRow + 1 < noIngredients){
        for (var i=deletedRow+1; i <= noIngredients-1; i++){
          $("#ingred"+i.toString()).attr("id","ingred"+(i.toString()-1));
          $("#quantity"+i.toString()).attr("name","quantity"+(i.toString()-1));
          $("#quantity"+i.toString()).attr("id","quantity"+(i.toString()-1));
          $("#measure"+i.toString()).attr("name","measure"+(i.toString()-1));
          $("#measure"+i.toString()).attr("id","measure"+(i.toString()-1));
          $("#ingredient"+i.toString()).attr("name","ingredient"+(i.toString()-1));
          $("#ingredient"+i.toString()).attr("id","ingredient"+(i.toString()-1));
        }
      }
    }


    function deleteSteps(btn) {
      noSteps = $("#recipeSteps div").length;
      //console.log("No. of Steps = "+noSteps);
      saveid = $(btn).parents("div div");
      $(saveid).remove();
      deleteItem = saveid.attr('id').replace('step','');
      deletedRow = parseInt(deleteItem);
      $("#noSteps_1").val(noSteps-1);
      // for all cooking steps with an id above the
      // deleted item, reduce by 1
      if (deletedRow + 1 < noSteps){
        for (var i=deletedRow + 1; i <= noSteps-1; i++){
          $("#step"+i.toString()).attr("id","step"+(i.toString()-1));
          $("#stepTitle"+i.toString()).attr("name","stepTitle"+(i.toString()-1));
          $("#stepTitle"+i.toString()).attr("id","stepTitle"+(i.toString()-1));
          $("#stepText"+i.toString()).attr("name","stepText"+(i.toString()-1));
          $("#stepText"+i.toString()).attr("id","stepText"+(i.toString()-1));
          $("#stepNumber"+i.toString()).text(i);
          $("#stepNumber"+i.toString()).attr("id","stepNumber"+(i.toString()-1));
        }
      }
    }

    function addSteps() {
      noSteps = $("#recipeSteps div").length;
      $("#noSteps_1").val(noSteps+1);
      $("#recipeSteps").append(
        '<div id="step'+noSteps+'"><p>Step <span id="stepNumber'+noSteps+'" class="stepNumber">'+(noSteps+1)+'</span><input type="text" value = "" name="stepTitle'+noSteps+'" id="stepTitle'+noSteps+'" size=40px class="stepTitle"><input type="button" value="-" onclick="deleteSteps(this);"></p><textarea id="stepText'+noSteps+'" name ="stepText'+noSteps+'"rows=8" cols="80" class="stepText"></textarea></div>'
      //  '<div id="Step'+noSteps+'"><p>Step '+(noSteps+1)+':</p><input type="text" value="" name="stepHead'+noSteps'"></p><textarea name ="stepDesc'+noSteps+'"rows=8" cols="80"></textarea></div><br>'
      );
    }



    $(document).ready(
      function(){
        noIngredients = document.getElementById("ingredientsTable").childElementCount;
        $('#addIngredient').click(
          function(event){
            event.preventDefault();
            if (validateIngredient()){
                addIngredient();
            };
          }
        );

        $('#addStep').click(
          function(event){
            event.preventDefault();
            if (validateSteps()){
              addSteps();
            }
          }
        )
      }
    )
  </script>

</html>
