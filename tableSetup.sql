USE foodRecoTest;

CREATE TABLE `languages` (
  `lang_id` INT(2) unsigned NOT NULL AUTO_INCREMENT,
  `lang_short` VARCHAR(2) UNIQUE,
  `lang_long` VARCHAR(10) UNIQUE,
  PRIMARY KEY (`lang_id`),
  INDEX (`lang_long`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

INSERT INTO languages (lang_short, lang_long) VALUES ('de','German');
INSERT INTO languages (lang_short, lang_long) VALUES ('en','English');
INSERT INTO languages (lang_short, lang_long) VALUES ('es','Spanish');

CREATE TABLE `users` (
  `user_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `surname` VARCHAR(50) NOT NULL,
  `forename` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `preferred_lang` INT(2) unsigned,
  PRIMARY KEY (`user_id`),
  CONSTRAINT FOREIGN KEY (`preferred_lang`) REFERENCES languages (lang_id),
  INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE recipeHead (
  `recipe_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `vegetarian` INT(1) NOT NULL,
  `vegan` INT(1) NOT NULL,
  `glutenfree` INT(1) NOT NULL,
  `private` INT(1) NOT NULL,
  `numserved` INT(2) NOT NULL,
  `cookingtimeMIN` INT(3),
  `fork_id` INT(11) unsigned,                 -- recipes can be created as a fork from another recipe
  `user_id` INT(11) unsigned,
  `lang_id` INT(2) unsigned,
  PRIMARY KEY (`recipe_id`),
  CONSTRAINT FOREIGN KEY (user_id) REFERENCES users (user_id),
  CONSTRAINT FOREIGN KEY (fork_id) REFERENCES recipeHead (recipe_id),
  CONSTRAINT FOREIGN KEY (`lang`) REFERENCES languages (lang_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE ingredients (
  `ingredient_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100),
  `lang_id` INT(2) unsigned,
  PRIMARY KEY (`ingredient_id`),
  CONSTRAINT FOREIGN KEY (`lang_id`) REFERENCES languages (lang_id),
  UNIQUE KEY (`name`,`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE recipeIngredients (
  `recipe_id` INT(11) unsigned NOT NULL,
  `ingredient_id` INT(11) unsigned NOT NULL,
  `quantity` FLOAT,
  `measure` VARCHAR(5),
  `input_rank` INT(2) unsigned NOT NULL,
  CONSTRAINT FOREIGN KEY (`recipe_id`) REFERENCES recipeHead (`recipe_id`),
  CONSTRAINT FOREIGN KEY (`ingredient_id`) REFERENCES ingredients (`ingredient_id`),
  PRIMARY KEY (`recipe_id`, `ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE recipeSteps (
  `recipe_id` INT(11) unsigned NOT NULL,
  `stepNumber` INT(3),
  `stepTitle` VARCHAR(100),
  `stepText` TEXT,
  CONSTRAINT FOREIGN KEY (`recipe_id`) REFERENCES recipeHead (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE recipeRatings (
  `user_id` INT(11) unsigned NOT NULL,
  `recipe_id` INT(11) unsigned NOT NULL,
  `rating` INT(1),
  CONSTRAINT FOREIGN KEY (`recipe_id`) REFERENCES recipeHead (`recipe_id`),
  CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
  PRIMARY KEY (`user_id`,`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE shoppingItems (
  `item_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `itemname` VARCHAR(50) NOT NULL UNIQUE,
  `lang` INT(2) unsigned,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY (`itemname`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `shoppingList` (
  `user_id` INT(11) unsigned NOT NULL,
  `item_id` INT(11) unsigned NOT NULL,
  `quantity` VARCHAR(20),
  `measure` VARCHAR(5),
  `addDT` DATETIME NOT NULL,
  `sourcerecipe_id` INT(11) unsigned,
  `inpantryDT` DATETIME,
  `purchasedDT` DATETIME,
  `modifiedDT` DATETIME,
   CONSTRAINT FOREIGN KEY (`item_id`) REFERENCES shoppingItems (`item_id`),
   CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
   CONSTRAINT FOREIGN KEY (`sourcerecipe_id`) REFERENCES recipeHead (`recipe_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `shoppingListConsolidated` (
  `user_id` INT(11) unsigned NOT NULL,
  `item_id` INT(11) unsigned NOT NULL,
  `quantity` VARCHAR(20),
  `measure` VARCHAR(5),
  `inpantryDT` DATETIME,
  `purchasedDT` DATETIME,
  `modifiedDT` DATETIME,
  CONSTRAINT FOREIGN KEY (`item_id`) REFERENCES shoppingItems (`item_id`),
  CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE ingredientShopping (
  `ingredient_id` INT(11) unsigned NOT NULL UNIQUE,
  `item_id` INT(11) unsigned NOT NULL UNIQUE,
  CONSTRAINT FOREIGN KEY (`item_id`) REFERENCES shoppingItems (`item_id`),
  CONSTRAINT FOREIGN KEY (`ingredient_id`) REFERENCES ingredients (`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DELIMITER $$
CREATE TRIGGER shoppingItemUpdate
    AFTER INSERT
    ON ingredients FOR EACH ROW
    BEGIN
      SET @itemfound = (SELECT COUNT(*) FROM shoppingItems WHERE itemname=NEW.name AND lang=NEW.lang_id);
      IF @itemfound = 0 THEN
      INSERT INTO shoppingItems (itemname, lang) VALUES (NEW.name, NEW.lang_id);
      END IF;
      INSERT INTO ingredientShopping (ingredient_id, item_id)
        SELECT NEW.ingredient_id, item_id FROM shoppingItems WHERE shoppingItems.itemname=NEW.name;
END;$$
DELIMITER ;
