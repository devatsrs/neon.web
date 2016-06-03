CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCRMBoardColumns`(IN `p_CompanyID` INT, IN `p_BoardID` INT)
BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
SELECT `BoardColumnName`, `Order`,`Height`,`Width`,`SetCompleted`,`BoardColumnID`
FROM `tblCRMBoardColumn` 
WHERE (`CompanyID` = p_CompanyID 
	AND `BoardID` = p_BoardID) 
ORDER BY `Order` ASC;
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END