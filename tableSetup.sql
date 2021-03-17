USE foodRecoTest;

CREATE TABLE `users` (
  `user_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `surname` VARCHAR(50) NOT NULL,
  `forename` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`user_id`),
  INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE recipeHead (
  `recipe_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `vegetarian` BIT(1) NOT NULL,
  `vegan` BIT(1) NOT NULL,
  `glutenfree` BIT(1) NOT NULL,
  `private` INT(1) NOT NULL,
  `numserved` INT(2) NOT NULL,
  `fork_id` INT(11),                 -- recipes can be created as a fork from another recipe
  `user_id` INT(11),
  PRIMARY KEY (`recipe_id`),
  CONSTRAINT FOREIGN KEY (user_id) REFERENCES users (user_id),
  CONSTRAINT FOREIGN KEY (fork_id) REFERENCES recipeHead (recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE ingredients (
  `ingredient_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) UNIQUE,
  PRIMARY KEY (`ingredient_id`),
  INDEX (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE recipeIngredients (
  `recipe_id` INT(11) NOT NULL,
  `ingredient_id` INT(11) NOT NULL,
  `quantity` FLOAT,
  `measure` VARCHAR(5),
  `input_rank` INT(2) NOT NULL,
  CONSTRAINT FOREIGN KEY (`recipe_id`) REFERENCES recipeHead (`recipe_id`),
  CONSTRAINT FOREIGN KEY (`ingredient_id`) REFERENCES ingredients (`ingredient_id`),
  PRIMARY KEY (`recipe_id`, `ingredient_id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE recipeSteps (
  `recipe_id` INT(11) NOT NULL,
  `stepNumber` INT(3),
  `stepText` TEXT,
  CONSTRAINT FOREIGN KEY (`recipe_id`) REFERENCES recipeHead (`recipe_id`)
)



CREATE TABLE recipeRatings (
  `user_id` INT(11) NOT NULL,
  `recipe_id` INT(11) NOT NULL,
  `rating` INT(1),
  CONSTRAINT FOREIGN KEY (`recipe_id`) REFERENCES recipeHead (`recipe_id`),
  CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
  PRIMARY KEY (`user_id`,`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

CREATE TABLE shoppingItems (
  `item_id` INT(15) unsigned NOT NULL AUTO_INCREMENT,
  `itemname` VARCHAR(50) NOT NULL
)



CREATE TABLE `shoppingList` (
  `item_id` INT(15) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `itemname` VARCHAR(50) NOT NULL,
  `quantity` VARCHAR(20),
  `addDT` DATETIME NOT NULL,
  `purchasedDT` DATETIME,
   `modifiedDT` DATETIME,
   PRIMARY KEY (`item_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
