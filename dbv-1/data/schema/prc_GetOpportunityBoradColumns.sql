CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetOpportunityBoradColumns`(IN `p_CompanyID` INT, IN `p_OpportunityBoardID` INT)
BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
SELECT `OpportunityBoardColumnName`, `Order`, `OpportunityBoardColumnID` 
FROM `tblOpportunityBoardColumn` 
WHERE (`CompanyID` = p_CompanyID 
	AND `OpportunityBoardID` = p_OpportunityBoardID) 
ORDER BY `Order` ASC;
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END