CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetOpportunities`(IN `p_CompanyID` INT, IN `p_BoardID` INT, IN `p_OpportunityName` VARCHAR(50), IN `p_Tags` VARCHAR(50), IN `p_OwnerID` INT, IN `p_AccountID` INT, IN `p_Status` VARCHAR(50))
BEGIN

SELECT 
		bc.BoardColumnID,
		bc.BoardColumnName,
		bc.Height,
		bc.Width,
		o.OpportunityID,
		o.OpportunityName,
		o.BackGroundColour,
		o.TextColour,
		o.Company,
		o.Title,
		o.FirstName,
		o.LastName,
		concat(u.FirstName,concat(' ',u.LastName)) as Owner,
		o.UserID,
		o.Phone,
		o.Email,
		b.BoardID,
		o.AccountID,
		o.Tags,
		o.Rating,
		o.TaggedUsers,
		o.`Status`
FROM tblCRMBoards b
INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
			AND b.BoardID = p_BoardID
LEFT JOIN tblOpportunity o on o.BoardID = b.BoardID
			AND o.BoardColumnID = bc.BoardColumnID
			AND o.CompanyID = p_CompanyID
			AND (p_Tags = '' OR find_in_set(o.Tags,p_Tags))
			AND (p_OpportunityName = '' OR o.OpportunityName LIKE Concat('%',p_OpportunityName,'%'))
			AND (p_OwnerID = 0 OR o.UserID = p_OwnerID)
			AND (p_AccountID = 0 OR o.AccountID = p_AccountID)
			AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
LEFT JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.AccountType = 0
			AND ac.`Status` = 1
LEFT JOIN tblContact con on con.Owner = ac.AccountID
LEFT JOIN tblUser u on u.UserID = o.UserID
ORDER BY bc.`Order`,o.`Order`;

END