CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTasksGrid`(IN `p_CompanyID` INT, IN `p_BoardID` INT, IN `p_TaskName` VARCHAR(50), IN `p_UserIDs` VARCHAR(50), IN `p_AccountIDs` INT, IN `p_Periority` INT, IN `p_DueDateFrom` VARCHAR(50), IN `p_DueDateTo` VARCHAR(50), IN `p_Status` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50))
BEGIN 
	DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET SESSION group_concat_max_len = 1024;
	    
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
		
		SELECT 
		bc.BoardColumnID,
		bc.BoardColumnName,
		bc.SetCompleted,
		ts.TaskID,
		ts.UsersIDs,
		(select GROUP_CONCAT( concat(u.FirstName,' ',u.LastName) SEPARATOR ', ') as Users from tblUser u where u.UserID = ts.UsersIDs) as Users,
		ts.AccountIDs,
		(select a.AccountName as company from tblAccount a where a.AccountID = ts.AccountIDs) as company,
		ts.Subject,
		ts.Description,
		Date(ts.DueDate) as DueDate,
		Time(ts.DueDate) as StartTime,
		ts.BoardColumnID as TaskStatus,
		ts.Priority,
		CASE 	WHEN ts.Priority=1 THEN 'High'
			 	WHEN ts.Priority=2 THEN 'Medium'
				WHEN ts.Priority=3 THEN 'Low'
		END as PriorityText,				
		ts.TaggedUsers,
		ts.BoardID
		FROM tblCRMBoards b
		INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
				AND b.BoardID = p_BoardID
		INNER JOIN tblTask ts on ts.BoardID = b.BoardID 
		AND ts.BoardColumnID = bc.BoardColumnID
		AND ts.CompanyID= p_CompanyID
		AND (p_TaskName='' OR ts.Subject LIKE Concat('%',p_TaskName,'%'))
		AND (p_UserIDs=0 OR  FIND_IN_SET (ts.UsersIDs,p_UserIDs))
		AND (p_AccountIDs=0 OR ts.AccountIDs=p_AccountIDs)
		AND (p_Periority=0 OR ts.Priority = p_Periority) 
		AND (p_Status=0 OR ts.BoardColumnID=p_Status )
		AND (p_DueDateFrom=0 
				OR (p_DueDateFrom=1 AND (ts.DueDate !='0000-00-00 00:00:00' AND ts.DueDate < NOW() AND bc.SetCompleted=0))
				OR (p_DueDateFrom=2 AND (ts.DueDate !='0000-00-00 00:00:00' AND ts.DueDate >= NOW() AND ts.DueDate <= DATE(DATE_ADD(NOW(), INTERVAL +2 DAY)))) 
				OR ((p_DueDateFrom!='' OR p_DueDateTo!='') AND ts.DueDate BETWEEN STR_TO_DATE(p_DueDateFrom,'%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(p_DueDateTo,'%Y-%m-%d %H:%i:%s'))
			)
		LEFT JOIN tblUser u on u.UserID = ts.UsersIDs
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
		 END ASC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserIDDESC') THEN concat(u.FirstName,' ',u.LastName)
		 END DESC,
		 CASE
		     WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserIDASC') THEN concat(u.FirstName,' ',u.LastName)
		 END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT COUNT(ts.TaskID) as totalcount
		FROM tblCRMBoards b
		INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
				AND b.BoardID = p_BoardID
		INNER JOIN tblTask ts on ts.BoardID = b.BoardID 
		AND ts.BoardColumnID = bc.BoardColumnID
		AND ts.CompanyID= p_CompanyID
		AND (p_TaskName='' OR ts.Subject LIKE Concat('%',p_TaskName,'%'))
		AND (p_UserIDs=0 OR  FIND_IN_SET (ts.UsersIDs,p_UserIDs))
		AND (p_Periority=0 OR ts.Priority = p_Periority) 
		AND (p_Status=0 OR ts.BoardColumnID=p_Status )
		AND (p_DueDateFrom=0 
				OR (p_DueDateFrom=1 AND (ts.DueDate !='0000-00-00 00:00:00' AND ts.DueDate < NOW() AND bc.SetCompleted=0))
				OR (p_DueDateFrom=2 AND (ts.DueDate !='0000-00-00 00:00:00' AND ts.DueDate >= NOW() AND ts.DueDate <= DATE(DATE_ADD(NOW(), INTERVAL +2 DAY)))) 
				OR ((p_DueDateFrom!='' OR p_DueDateTo!='') AND ts.DueDate BETWEEN STR_TO_DATE(p_DueDateFrom,'%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(p_DueDateTo,'%Y-%m-%d %H:%i:%s'))
			);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END