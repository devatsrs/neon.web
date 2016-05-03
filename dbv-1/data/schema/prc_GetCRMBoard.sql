CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCRMBoard`(IN `p_CompanyID` INT, IN `p_Status` INT, IN `p_BoardName` VARCHAR(30), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(30), IN `p_SortOrder` INT, IN `p_isExport` INT)
BEGIN

DECLARE v_OffSet_ int;

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	/*SELECT concat('myvar is ', v_OffSet_);*/
	IF p_isExport = 0
	THEN
		SELECT BoardName, `Status`, CreatedBy, BoardID 
		FROM tblCRMBoards op 
		WHERE (op.CompanyID = p_CompanyID) 
			AND ( p_Status = 2 OR op.`Status` = p_Status) /* 2 for all */
			AND (p_BoardName = '' OR op.BoardName like Concat('%',p_BoardName,'%'))
			ORDER BY
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'BoardNameASC') THEN op.BoardName
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'BoardNameDESC') THEN op.BoardName
            END DESC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN op.`Status`
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN op.`Status`
            END DESC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN op.CreatedBy
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN op.CreatedBy
            END DESC
				                     
		LIMIT p_RowspPage OFFSET v_OffSet_;
				
		SELECT COUNT(*) AS totalcount
		FROM tblCRMBoards op 
		WHERE (op.CompanyID = p_CompanyID) 
			AND ( p_Status = 2 OR op.`Status` = p_Status)
			AND (p_BoardName ='' OR `OpportunityBoardName` like Concat('%',p_BoardName,'%'));
	END IF;
	
	IF p_isExport = 1
	THEN
	SELECT BoardName, Status, CreatedBy, BoardID 
		FROM tblCRMBoards op 
		WHERE (op.CompanyID = p_CompanyID) 
			AND ( p_Status = 2 OR op.`Status` = p_Status)
			AND (p_BoardName ='' OR `BoardName` like Concat('%',p_BoardName,'%'));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END