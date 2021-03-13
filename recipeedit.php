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

  if ($result[0]['user_id'] !== $_SESSION['user_id']){
    $_SESSION['error'] = 'You can only edit recipes you created. If you want to create a modified version of the recipe select the fork option';
    header('Location: recipeview.php?recipe_id='.$_GET['recipe_id']);
    return;
  }

?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: Update your recipe</title>
  </head>
  <body>
    <h1>Your Recipe</h1>

  <!-- give recipe title -->
  <form method="post">
    <p>
      <h3>Recipe Title</h3><input type="text" name="recipetitle" value=<?= $result[0]['title']?> size=50px><br>
      <label for="recipeShare">Recipe privacy status: </label><select class="recipeShare" name="recipeShare">
        <option value="0" selected>Public</option>
        <option value="1">Private</option>
      </select>
    </p>

  <div class="recipe_characteristics">


      <h4>Recipe Characteristics</h4>
      <p>
      <input type="checkbox" name="vegetarian" value="veggie" name="recipechar1" <?= if($result[0]['vegetarian']>0){echo 'selected'}?> ><label for="recipechar1">Vegetarian</label><br>
      <input type="checkbox" name="vegan" value="vegan" name="recipechar2"><label for="recipechar2">Vegan</label><br>
      <input type="checkbox" name="glutenfree" value="glutenfree" name="recipechar3"><label for="recipechar3">Gluten Free</label><br>
      </p>
      <p>
      <label for="serves">Number served: </label><input type="number" name="serves" value=<?= $result[0]['numserved']?> min="1" max="10" step="1"><br>
      </p>
  </div>
  <!-- add ingredients -->



  <div class="add_recipe_ingredient">

      <h4>Add Ingredients</h4>

      <table>
        <tr>
          <th>Quantity</th><th>Measure</th><th>Ingredient</th>
        </tr>
        <tr>
          <td><input type="text" name="quantity" value=""></td> <!-- use javascript to ensure value is numeric -->
          <td><select class="quantity" name="quantity" >
            <option value="0" selected></option>
            <option value="1">g</option>
            <option value="2">kg</option>
            <option value="3">ml</option>
            <option value="1">l</option>
          </select></td>
          <td><input type="text" name="addIngredient" value="" id="addIngredient" placeholder="Ingredient name"></td>
        </tr>
      </table>

<!-- use javascript to fill in a table on the browser, ingredient by ingredient -->


  </div>


  <!-- add recipe steps -->

  <!-- image upload -->


  <!-- submit completed recipe -->

  <input type="submit" name="recipesubmit" value="Update Recipe">
  </form>

  </body>
</html>
