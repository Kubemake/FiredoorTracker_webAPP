DELETE FROM Buildings WHERE 1;
DELETE FROM InspectionFieldFiles WHERE 1;
DELETE FROM DoorsFormFields WHERE 1;
DELETE FROM Inspections WHERE 1;
DELETE FROM Address WHERE 1;
DELETE FROM Doors WHERE 1;
DELETE FROM Files WHERE 1;
DELETE FROM History WHERE 1;
DELETE FROM UserBuildings WHERE 1;
DELETE FROM Users WHERE 1;
DELETE FROM UserTokens WHERE 1;

ALTER TABLE Buildings auto_increment = 1;
ALTER TABLE InspectionFieldFiles auto_increment = 1;
ALTER TABLE DoorsFormFields auto_increment = 1;
ALTER TABLE Inspections auto_increment = 1;
ALTER TABLE Address auto_increment = 1;
ALTER TABLE Doors auto_increment = 1;
ALTER TABLE Files auto_increment = 1;
ALTER TABLE History auto_increment = 1;
ALTER TABLE UserBuildings auto_increment = 1;
ALTER TABLE Users auto_increment = 1;
ALTER TABLE UserTokens auto_increment = 1;

INSERT INTO `Users` (`idUsers`, `idAddress`, `email`, `password`, `firstName`, `lastName`, `officePhone`, `mobilePhone`, `license`, `expired`, `role`, `parent`, `lastLogin`, `logoFilePath`, `deleted`) VALUES
(1, NULL, 'admin@test.nor', '4b9d141061099f264b1920494ccfd97a', 'Main', 'Frame', '', '', '', '0000-00-00', 4, 0, '2015-05-07 16:31:23', NULL, 0),
(2, NULL, 'dir@br.nor', 'c191f6b5ebec6e861154cbc5e561d7b1', 'Bryan', 'Wong', '', '', '', '0000-00-00', 1, 2, '0000-00-00 00:00:00', NULL, 0),
(3, NULL, 'dir@cj.nor', '25cb380227efd1c83cea1908ced196ed', 'CJ', 'Levy', '', '', '', '0000-00-00', 1, 3, '0000-00-00 00:00:00', NULL, 0),
(4, NULL, 'dir@ku.nor', '8284692dbb126365dfa3c5bb8f7bcdc8', 'Ihor', 'Kubalskyi', '', '', '', '0000-00-00', 1, 4, '0000-00-00 00:00:00', NULL, 0),
(5, NULL, 'dir@test.nor', '4b9d141061099f264b1920494ccfd97a', 'Dev', 'Test', '', '', '', '0000-00-00', 1, 5, '0000-00-00 00:00:00', NULL, 0),
(6, NULL, 'dir@cm.nor', '09057de44bec70c9bc8ff80f62880802', 'C', 'M', '', '', '', '0000-00-00', 1, 6, '0000-00-00 00:00:00', NULL, 0),
(7, NULL, 'dir@hw.nor', 'ef70326db4a17e2d3acebd23c1947e6b', 'H', 'W', '', '', '', '0000-00-00', 1, 7, '0000-00-00 00:00:00', NULL, 0),
(8, NULL, 'dir@mi.nor', 'c9ff14de8cee6fe22039e5f264bfe494', 'Michael', '', '', '', '', '0000-00-00', 1, 8, '0000-00-00 00:00:00', NULL, 0),
(9, NULL, 'dir@pb.nor', 'a579cd20cfe6c894c22d588dfe2a978a', 'P', 'B', '', '', '', '0000-00-00', 1, 9, '0000-00-00 00:00:00', NULL, 0),
(10, NULL, 'sv@br.nor', 'c191f6b5ebec6e861154cbc5e561d7b1', 'Bryan', 'Wong', '', '', '', '0000-00-00', 2, 2, '0000-00-00 00:00:00', NULL, 0),
(11, NULL, 'sv@cj.nor', '25cb380227efd1c83cea1908ced196ed', 'CJ', 'Levy', '', '', '', '0000-00-00', 2, 3, '0000-00-00 00:00:00', NULL, 0),
(12, NULL, 'sv@ku.nor', '8284692dbb126365dfa3c5bb8f7bcdc8', 'Ihor', 'Kubalskyi', '', '', '', '0000-00-00', 2, 4, '0000-00-00 00:00:00', NULL, 0),
(13, NULL, 'sv@test.nor', '4b9d141061099f264b1920494ccfd97a', 'Dev', 'Test', '', '', '', '0000-00-00', 2, 5, '0000-00-00 00:00:00', NULL, 0),
(14, NULL, 'sv@cm.nor', '09057de44bec70c9bc8ff80f62880802', 'C', 'M', '', '', '', '0000-00-00', 2, 6, '0000-00-00 00:00:00', NULL, 0),
(15, NULL, 'sv@hw.nor', 'ef70326db4a17e2d3acebd23c1947e6b', 'H', 'W', '', '', '', '0000-00-00', 2, 7, '0000-00-00 00:00:00', NULL, 0),
(16, NULL, 'sv@mi.nor', 'c9ff14de8cee6fe22039e5f264bfe494', 'Michael', '', '', '', '', '0000-00-00', 2, 8, '0000-00-00 00:00:00', NULL, 0),
(17, NULL, 'sv@pb.nor', 'a579cd20cfe6c894c22d588dfe2a978a', 'P', 'B', '', '', '', '0000-00-00', 2, 9, '0000-00-00 00:00:00', NULL, 0),
(18, NULL, 'm@br.nor', 'c191f6b5ebec6e861154cbc5e561d7b1', 'Bryan', 'Wong', '', '', '', '0000-00-00', 3, 2, '0000-00-00 00:00:00', NULL, 0),
(19, NULL, 'm@cj.nor', '25cb380227efd1c83cea1908ced196ed', 'CJ', 'Levy', '', '', '', '0000-00-00', 3, 3, '0000-00-00 00:00:00', NULL, 0),
(20, NULL, 'm@ku.nor', '8284692dbb126365dfa3c5bb8f7bcdc8', 'Ihor', 'Kubalskyi', '', '', '', '0000-00-00', 3, 4, '0000-00-00 00:00:00', NULL, 0),
(21, NULL, 'm@test.nor', '4b9d141061099f264b1920494ccfd97a', 'Dev', 'Test', '', '', '', '0000-00-00', 3, 5, '0000-00-00 00:00:00', NULL, 0),
(22, NULL, 'st@test.nor', '4b9d141061099f264b1920494ccfd97a', 'Dev', 'Test', '', '', '', '0000-00-00', 3, 5, '0000-00-00 00:00:00', NULL, 0),
(23, NULL, 'dm@test.nor', '4b9d141061099f264b1920494ccfd97a', 'Dev', 'Test', '', '', '', '0000-00-00', 3, 5, '0000-00-00 00:00:00', NULL, 0),
(24, NULL, 'm@cm.nor', '09057de44bec70c9bc8ff80f62880802', 'C', 'M', '', '', '', '0000-00-00', 3, 6, '0000-00-00 00:00:00', NULL, 0),
(25, NULL, 'm@hw.nor', 'ef70326db4a17e2d3acebd23c1947e6b', 'H', 'W', '', '', '', '0000-00-00', 3, 7, '0000-00-00 00:00:00', NULL, 0),
(26, NULL, 'm@mi.nor', 'c9ff14de8cee6fe22039e5f264bfe494', 'Michael', '', '', '', '', '0000-00-00', 3, 8, '0000-00-00 00:00:00', NULL, 0),
(27, NULL, 'm@pb.nor', 'a579cd20cfe6c894c22d588dfe2a978a', 'P', 'B', '', '', '', '0000-00-00', 3, 9, '0000-00-00 00:00:00', NULL, 0);

INSERT INTO `Buildings` (`idBuildings`, `name`, `parent`, `root`, `level`, `buildingOrder`, `deleted`) VALUES
(1, 'Building #1', 0, 1, 0, 0, 0),
(2, 'Building #1', 0, 2, 0, 0, 0),
(3, 'Building #1', 0, 3, 0, 0, 0),
(4, 'Building #1', 0, 4, 0, 0, 0),
(5, 'Building #1', 0, 5, 0, 0, 0),
(6, 'Building #1', 0, 6, 0, 0, 0),
(7, 'Building #1', 0, 7, 0, 0, 0),
(8, 'Building #1', 0, 8, 0, 0, 0),
(9, 'Building #1', 0, 9, 0, 0, 0);

INSERT INTO `UserBuildings` (`Buildings_idBuildings`, `Users_idUsers`, `Addresses_idAddress`) VALUES
(1, 2, 0),
(2, 3, 0),
(3, 4, 0),
(4, 5, 0),
(5, 6, 0),
(6, 7, 0),
(7, 8, 0),
(8, 9, 0),
(9, 10, 0);