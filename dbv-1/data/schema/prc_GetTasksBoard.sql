CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTasksBoard`(IN `p_CompanyID` INT, IN `p_BoardID` INT, IN `p_TaskName` VARCHAR(50), IN `p_UserIDs` VARCHAR(50), IN `p_Periority` INT, IN `p_DueDate` VARCHAR(50))
BEGIN
	SELECT 
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
	LEFT JOIN tblTask ts on ts.BoardID = b.BoardID 
		AND ts.BoardColumnID = bc.BoardColumnID
		AND ts.CompanyID= p_CompanyID
		AND (p_TaskName='' OR ts.Subject LIKE Concat('%',p_TaskName,'%'))
		AND (p_UserIDs=0 OR  FIND_IN_SET (ts.UsersIDs,p_UserIDs))
		AND (p_Periority=0 OR ts.Priority = p_Periority) 
		AND (p_DueDate='' OR ts.DueDate = STR_TO_DATE(p_DueDate,'%Y-%m-%d'))
	ORDER BY bc.`Order`,ts.`Order`;
END