CREATE DEFINER=`root`@`localhsot` PROCEDURE `prc_GetTasksBoard`(
	IN `p_CompanyID` INT,
	IN `p_BoardID` INT,
	IN `p_TaskName` VARCHAR(50),
	IN `p_UserIDs` VARCHAR(50),
	IN `p_AccountIDs` INT,
	IN `p_Periority` INT,
	IN `p_DueDateFrom` VARCHAR(50),
	IN `p_DueDateTo` VARCHAR(50),
	IN `p_Status` INT,
	IN `p_Closed` INT
)
BEGIN
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
		ts.Tags,
		ts.TaggedUsers,
		ts.BoardID,
		concat( u.FirstName,' ',u.LastName) as userName,
		ts.taskClosed
	FROM tblCRMBoards b
	INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID
			AND b.BoardID = p_BoardID
	LEFT JOIN tblTask ts on ts.BoardID = b.BoardID
		AND ts.BoardColumnID = bc.BoardColumnID
		AND ts.CompanyID= p_CompanyID
		AND (ts.taskClosed=p_Closed)
		AND (p_TaskName='' OR ts.Subject LIKE Concat('%',p_TaskName,'%'))
		AND (p_UserIDs=0 OR  FIND_IN_SET (ts.UsersIDs,p_UserIDs))
		AND (p_AccountIDs=0 OR ts.AccountIDs=p_AccountIDs)
		AND (p_Periority=0 OR ts.Priority = p_Periority) 
		AND (p_Status=0 OR ts.BoardColumnID=p_Status)
		AND (p_DueDateFrom=0 
				OR (p_DueDateFrom=1 AND (ts.DueDate !='0000-00-00 00:00:00' AND ts.DueDate < NOW() AND bc.SetCompleted=0))
				OR (p_DueDateFrom=2 AND (ts.DueDate !='0000-00-00 00:00:00' AND ts.DueDate >= NOW() AND ts.DueDate <= DATE(DATE_ADD(NOW(), INTERVAL +3 DAY)))) 
				OR ((p_DueDateFrom!='' OR p_DueDateTo!='') AND ts.DueDate BETWEEN STR_TO_DATE(p_DueDateFrom,'%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(p_DueDateTo,'%Y-%m-%d %H:%i:%s'))
			)
		AND (1 in (Select `Status` FROM tblUser Where tblUser.UserID = ts.UsersIDs))
	LEFT JOIN tblUser u on u.UserID = ts.UsersIDs 
	ORDER BY bc.`Order`,ts.`Order`;
END