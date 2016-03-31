CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetOpportunities`(IN `p_CompanyID` INT, IN `p_BoardID` INT, IN `p_OpportunityName` VARCHAR(50), IN `p_OwnerID` INT, IN `p_AccountID` INT)
BEGIN

SELECT 
		bc.OpportunityBoardColumnID,
		bc.OpportunityBoardColumnName,
		o.OpportunityID,
		o.OpportunityName,
		o.BackGroundColour,
		o.TextColour,
		ac.AccountName as Company,
		(select con.FirstName from tblContact con where con.AccountID=o.AccountID limit 1) ContactName,
		concat(u.FirstName,concat(' ',u.LastName)) as Owner,
		o.UserID,
		ac.Phone,
		ac.Email,
		b.OpportunityBoardID,
		o.AccountID,
		o.Tags,
		o.Rating
FROM tblOpportunityBoards b
INNER JOIN tblOpportunityBoardColumn bc on bc.OpportunityBoardID = b.OpportunityBoardID
			AND b.OpportunityBoardID = p_BoardID
LEFT JOIN tblOpportunity o on o.OpportunityBoardID = b.OpportunityBoardID
			AND o.OpportunityBoardColumnID = bc.OpportunityBoardColumnID
			AND o.CompanyID = p_CompanyID
			AND (p_OpportunityName = '' OR o.OpportunityName LIKE Concat('%',p_OpportunityName,'%'))
			AND (p_OwnerID = 0 OR o.UserID = p_OwnerID)
			AND (p_AccountID = 0 OR o.AccountID = p_AccountID)
LEFT JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.AccountType = 0
			AND ac.`Status` = 1
LEFT JOIN tblContact con on con.Owner = ac.AccountID
LEFT JOIN tblUser u on u.UserID = o.UserID
ORDER BY bc.`Order`,o.`Order`;

END