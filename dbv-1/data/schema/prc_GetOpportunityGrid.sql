CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetOpportunityGrid`(
	IN `p_CompanyID` INT,
	IN `p_BoardID` INT,
	IN `p_OpportunityName` VARCHAR(50),
	IN `p_Tags` VARCHAR(50),
	IN `p_OwnerID` VARCHAR(100),
	IN `p_AccountID` INT,
	IN `p_Status` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_OpportunityClosed` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50)
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET SESSION group_concat_max_len = 1024;
	    
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


SELECT cs.Value INTO v_Round_ from NeonRMDev.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
SELECT 
		bc.BoardColumnID,
		bc.BoardColumnName,
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
		o.`Status`,
 	   ROUND(o.Worth,v_Round_) as Worth,
 	   o.OpportunityClosed,
 	   Date(o.ClosingDate) as ClosingDate,
 	   Date(o.ExpectedClosing) as ExpectedClosing,
		Time(o.ExpectedClosing) as StartTime
FROM tblCRMBoards b
INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
			AND (p_BoardID = 0 OR b.BoardID = p_BoardID)
INNER JOIN tblOpportunity o on o.BoardID = b.BoardID
			AND o.BoardColumnID = bc.BoardColumnID
			AND o.CompanyID = p_CompanyID
			AND (o.OpportunityClosed = p_OpportunityClosed)
			AND (p_Tags = '' OR find_in_set(o.Tags,p_Tags))
			AND (p_OpportunityName = '' OR o.OpportunityName LIKE Concat('%',p_OpportunityName,'%'))
			AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID))
			AND (p_AccountID = 0 OR o.AccountID = p_AccountID)
			AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
			AND (p_CurrencyID = 0 OR p_CurrencyID in (Select CurrencyId FROM tblAccount Where tblAccount.AccountID = o.AccountID))
			AND (1 in (Select `Status` FROM tblAccount Where tblAccount.AccountID = o.AccountID))
			AND (1 in (Select `Status` FROM tblUser Where tblUser.UserID = o.UserID))
LEFT JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.`Status` = 1
LEFT JOIN tblContact con on con.Owner = ac.AccountID
LEFT JOIN tblUser u on u.UserID = o.UserID
ORDER BY
		CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OpportunityNameDESC') THEN o.OpportunityName
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OpportunityNameASC') THEN o.OpportunityName
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RatingDESC') THEN o.Rating
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RatingASC') THEN o.Rating
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN o.`Status`
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN o.`Status`
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserIDDESC') THEN concat(u.FirstName,' ',u.LastName)
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserIDASC') THEN concat(u.FirstName,' ',u.LastName)
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RelatedToDESC') THEN o.Company
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RelatedToASC') THEN o.Company
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ExpectedClosingASC') THEN o.ExpectedClosing
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ExpectedClosingDESC') THEN o.ExpectedClosing
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ValueDESC') THEN o.Worth
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ValueASC') THEN o.Worth
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RatingDESC') THEN o.Rating
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RatingASC') THEN o.Rating
		 END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT 
		count(*) as totalcount
FROM tblCRMBoards b
INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
		AND (p_BoardID = 0 OR b.BoardID = p_BoardID)
INNER JOIN tblOpportunity o on o.BoardID = b.BoardID
			AND o.BoardColumnID = bc.BoardColumnID
			AND o.CompanyID = p_CompanyID
			AND (o.OpportunityClosed = p_OpportunityClosed)
			AND (p_Tags = '' OR find_in_set(o.Tags,p_Tags))
			AND (p_OpportunityName = '' OR o.OpportunityName LIKE Concat('%',p_OpportunityName,'%'))
			AND (p_OwnerID = '' OR find_in_set(o.`UserID`,p_OwnerID))
			AND (p_AccountID = 0 OR o.AccountID = p_AccountID)
			AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
			AND (p_CurrencyID = 0 OR p_CurrencyID in (Select CurrencyId FROM tblAccount Where tblAccount.AccountID = o.AccountID))
			AND (1 in (Select `Status` FROM tblAccount Where tblAccount.AccountID = o.AccountID))
			AND (1 in (Select `Status` FROM tblUser Where tblUser.UserID = o.UserID))
LEFT JOIN tblAccount ac on ac.AccountID = o.AccountID
			AND ac.`Status` = 1
			AND (p_CurrencyID = 0 OR ac.CurrencyId = p_CurrencyID)
LEFT JOIN tblContact con on con.Owner = ac.AccountID
LEFT JOIN tblUser u on u.UserID = o.UserID;
END