CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTasksGrid`(IN `p_CompanyID` INT, IN `p_BoardID` INT, IN `p_TaskName` VARCHAR(50), IN `p_UserIDs` VARCHAR(50), IN `p_Periority` INT, IN `p_DueDate` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50))
BEGIN 
	DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET SESSION group_concat_max_len = 1024;
	    
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
		
		SELECT 
		bc.BoardColumnID,
		bc.BoardColumnName,
		ts.TaskID,
		ts.UsersIDs,
		(select GROUP_CONCAT( concat(u.FirstName,' ',u.LastName) SEPARATOR ', ') as Users from tblUser u where u.UserID in (ts.UsersIDs)) as Users,
		ts.AccountIDs,
		ts.Subject,
		ts.Description,
		date(ts.DueDate) as DueDate,
		ts.BoardColumnID as TaskStatus,
		ts.Priority,
		CASE 	WHEN ts.Priority=1 THEN 'High'
			 	WHEN ts.Priority=2 THEN 'Medium'
				WHEN ts.Priority=3 THEN 'Low'
		END as PriorityText,				
		ts.TaggedUser,
		ts.BoardID
		FROM tblCRMBoards b
		INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
				AND b.BoardID = p_BoardID
		INNER JOIN tblTask ts on ts.BoardID = b.BoardID 
		AND ts.BoardColumnID = bc.BoardColumnID
		AND ts.CompanyID= p_CompanyID
		AND (p_TaskName='' OR ts.Subject LIKE Concat('%',p_TaskName,'%'))
		AND (p_UserIDs=0 OR  FIND_IN_SET (ts.UsersIDs,p_UserIDs))
		AND (p_Periority=0 OR ts.Priority = p_Periority) 
		AND (p_DueDate='' OR ts.DueDate =STR_TO_Date(p_DueDate,'%Y-%m-%d') )
		ORDER BY
		CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN ts.Subject
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN ts.Subject
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DueDateDESC') THEN ts.DueDate
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DueDateASC') THEN ts.DueDate
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN bc.`Order`
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN bc.`Order`
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PriorityDESC') THEN ts.Priority
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PriorityASC') THEN ts.Priority
		 END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT COUNT(ts.TaskID) as totalcount,
		bc.BoardColumnID,
		bc.BoardColumnName,
		ts.TaskID,
		ts.UsersIDs,
		ts.AccountIDs,
		ts.Subject,
		ts.Description,
		date(ts.DueDate) as DueDate,
		ts.BoardColumnID as TaskStatus,
		ts.Priority,
		ts.Tags,
		ts.TaggedUser,
		ts.BoardID
		FROM tblCRMBoards b
		INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
				AND b.BoardID = p_BoardID
		INNER JOIN tblTask ts on ts.BoardID = b.BoardID 
		AND ts.BoardColumnID = bc.BoardColumnID
		AND ts.CompanyID= p_CompanyID
		AND (p_TaskName='' OR ts.Subject LIKE Concat('%',p_TaskName,'%'))
		AND (p_UserIDs=0 OR  FIND_IN_SET (ts.UsersIDs,p_UserIDs))
		AND (p_Periority=0 OR ts.Priority = p_Periority); 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END