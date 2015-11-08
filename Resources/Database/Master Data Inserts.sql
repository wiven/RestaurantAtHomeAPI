-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`kitchentype`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (1, 'Frans');
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (2, 'Belgisch');
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (3, 'Chinees');
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (4, 'Indisch');
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (5, 'Irakees');
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (6, 'Grieks');
INSERT INTO `ID188346_rattest`.`kitchentype` (`id`, `name`) VALUES (7, 'Wereld');

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`producttype`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`producttype` (`id`, `name`) VALUES (1, 'Voorgerecht');
INSERT INTO `ID188346_rattest`.`producttype` (`id`, `name`) VALUES (2, 'Hoofdgerecht');
INSERT INTO `ID188346_rattest`.`producttype` (`id`, `name`) VALUES (3, 'Dessert');
INSERT INTO `ID188346_rattest`.`producttype` (`id`, `name`) VALUES (4, 'Dranken');
INSERT INTO `ID188346_rattest`.`producttype` (`id`, `name`) VALUES (5, 'Extra');

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`promotiontype`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`promotiontype` (`id`, `name`) VALUES (1, 'Op = Op');

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`tag`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`tag` (`id`, `name`) VALUES (1, 'Vegitarisch');

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`paymentmethod`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`paymentmethod` (`id`, `name`, `mollieId`) VALUES (1, 'Cash/Sodexo', NULL);
INSERT INTO `ID188346_rattest`.`paymentmethod` (`id`, `name`, `mollieId`) VALUES (2, ' Bancontact/Mister Cash', 'mistercash');

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`socialmediatype`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`socialmediatype` (`id`, `name`) VALUES (1, 'Facebook');
INSERT INTO `ID188346_rattest`.`socialmediatype` (`id`, `name`) VALUES (2, 'Twitter');
INSERT INTO `ID188346_rattest`.`socialmediatype` (`id`, `name`) VALUES (3, 'Instagram');

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`filterfield`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1001, 'product.name', 1);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1002, 'product.price', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1005, 'product.producttypeId', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1100, 'address.postcode', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (900, 'distancematrix.fromCityId', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (901, 'distancematrix.toCityId', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1050, 'promotiontype.id', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1010, 'product.cityid', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (902, 'distancematrix.distance', 0);
INSERT INTO `ID188346_rattest`.`filterfield` (`id`, `databaseFieldname`, `like`) VALUES (1020, 'tag.id', 0);

COMMIT;


-- -----------------------------------------------------
-- Data for table `ID188346_rattest`.`orderstatus`
-- -----------------------------------------------------
START TRANSACTION;
USE `ID188346_rattest`;
INSERT INTO `ID188346_rattest`.`orderstatus` (`id`, `name`) VALUES (10, 'New');
INSERT INTO `ID188346_rattest`.`orderstatus` (`id`, `name`) VALUES (20, 'Accepted');
INSERT INTO `ID188346_rattest`.`orderstatus` (`id`, `name`) VALUES (30, 'In Progress');
INSERT INTO `ID188346_rattest`.`orderstatus` (`id`, `name`) VALUES (40, 'Ready');
INSERT INTO `ID188346_rattest`.`orderstatus` (`id`, `name`) VALUES (50, 'On Route');
INSERT INTO `ID188346_rattest`.`orderstatus` (`id`, `name`) VALUES (100, 'Finished');

COMMIT;