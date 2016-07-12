-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.11 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.3.0.5098
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for procedure NeonRMDev.prc_GetOpportunityGrid
DELIMITER //
CREATE DEFINER=`neon-user-abubakar`@`122.129.78.153` PROCEDURE `prc_GetOpportunityGrid`(
	IN `p_CompanyID` INT,
	IN `p_BoardID` INT,
	IN `p_OpportunityName` VARCHAR(50),
	IN `p_Tags` VARCHAR(50),
	IN `p_OwnerID` INT,
	IN `p_AccountID` INT,
	IN `p_Status` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50)




)
BEGIN
	DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET SESSION group_concat_max_len = 1024;
	    
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
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
		o.`Status`,
 	   o.Worth
FROM tblCRMBoards b
INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
			AND b.BoardID = p_BoardID
INNER JOIN tblOpportunity o on o.BoardID = b.BoardID
			AND o.BoardColumnID = bc.BoardColumnID
			AND o.CompanyID = p_CompanyID
			AND (p_Tags = '' OR find_in_set(o.Tags,p_Tags))
			AND (p_OpportunityName = '' OR o.OpportunityName LIKE Concat('%',p_OpportunityName,'%'))
			AND (p_OwnerID = 0 OR o.UserID = p_OwnerID)
			AND (p_AccountID = 0 OR o.AccountID = p_AccountID)
			AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
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
			AND ac.`Status` = 1
LEFT JOIN tblContact con on con.Owner = ac.AccountID
LEFT JOIN tblUser u on u.UserID = o.UserID;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
