<?php
  require_once "pdo.php";
  session_start();

  if (!isset($_SESSION['user_id'])){
    header('Location: index.php');
  }

  // process post data and update database

  try {
    $pdo -> beginTransaction();

    foreach ($_POST as $row){

      // recipe items on shopping list

      if ($row['inpantry']==="1"){
        // if indicated as inpantry save date when indicated as in pantry

        $stmt = $pdo -> prepare('INSERT INTO shoppingListConsolidated (user_id, item_id, quantity, measure, inpantryDT, modifiedDT)
        SELECT
        user_id,
        ingredientShopping.item_id,
        SUM(recipeIngredients.quantity) AS quantity,
        recipeIngredients.measure,
        NOW() AS inPantryDT,
        NOW() AS modifiedDT
        FROM recipeShopping
        JOIN recipeIngredients
        ON recipeShopping.sourcerecipe_id = recipeIngredients.recipe_id
        JOIN ingredientShopping
        ON recipeIngredients.ingredient_id=ingredientShopping.ingredient_id
        JOIN ingredients
        ON recipeIngredients.ingredient_id = ingredients.ingredient_id
        WHERE user_id = :user_id AND item_id = :item_id AND toShopping IS NULL
        GROUP BY ingredientShopping.item_id, recipeIngredients.measure, ingredients.name
        ');

      } else if (($row['inpantry']==="0")){
        // if not in pantry add to shopping list and remove from recipeshoppinglist
        $stmt = $pdo -> prepare('INSERT INTO shoppingListConsolidated (user_id, item_id, quantity, measure, toshoppinglistDT, modifiedDT)
        SELECT
        user_id,
        ingredientShopping.item_id,
        SUM(recipeIngredients.quantity) AS quantity,
        recipeIngredients.measure,
        NOW() AS toshoppinglistDT,
        NOW() AS modifiedDT
        FROM recipeShopping
        JOIN recipeIngredients
        ON recipeShopping.sourcerecipe_id = recipeIngredients.recipe_id
        JOIN ingredientShopping
        ON recipeIngredients.ingredient_id=ingredientShopping.ingredient_id
        JOIN ingredients
        ON recipeIngredients.ingredient_id = ingredients.ingredient_id
        WHERE user_id = :user_id AND item_id = :item_id AND toshoppingDT IS NULL
        GROUP BY ingredientShopping.item_id, recipeIngredients.measure, ingredients.name
        ');

      }

      $stmt -> execute(array(
        ':user_id' => $_SESSION['user_id'],
        ':item_id' => $row['item_id']
      ));



      if ($stmt === false){
        throw new Exception('Unable to add data to database');
      }



    }


    $stmt = $pdo -> prepare('UPDATE recipeShopping SET toshoppingDT = NOW() WHERE user_id = :user_id AND toshoppingDT IS NULL');

    $stmt -> execute(array(
      ':user_id' => $_SESSION['user_id']
    ));

    if ($stmt === false){
      throw new Exception('Unable to add data to database');
    }

    $pdo -> commit();

  } catch(Exception $e) {
    $pdo -> rollBack();
    $stmt = false;
  }

  header('Content-Type: text/html; charset=UTF-8');
  echo $stmt !== false ? 1 : 0;

 ?>
