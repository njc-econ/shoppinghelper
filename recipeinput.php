<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
  }

?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: Add new recipe</title>
  </head>
  <body>
    <h1>Your Recipe</h1>

  <!-- give recipe title -->
  <form method="post">
    <p>
      <h3>Recipe Title</h3><input type="text" name="recipetitle" value="" size=50px><br>
      <label for="recipeShare">Recipe privacy status: </label><select class="recipeShare" name="recipeShare">
        <option value="0" selected>Public</option>
        <option value="1">Private</option>
      </select>
    </p>

  <div class="recipe_characteristics">


      <h4>Recipe Characteristics</h4>
      <p>
      <input type="checkbox" name="vegetarian" value="veggie" name="recipechar1" selected><label for="recipechar1">Vegetarian</label><br>
      <input type="checkbox" name="vegan" value="vegan" name="recipechar2"><label for="recipechar2">Vegan</label><br>
      <input type="checkbox" name="glutenfree" value="glutenfree" name="recipechar3"><label for="recipechar3">Gluten Free</label><br>
      </p>
      <p>
      <label for="serves">Number served: </label><input type="number" name="serves" value="2" min="1" max="10" step="1"><br>
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

  <input type="submit" name="recipesubmit" value="Upload Recipe">
  </form>

  </body>
</html>
