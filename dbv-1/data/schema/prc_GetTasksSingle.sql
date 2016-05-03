CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTasksSingle`(IN `p_TaskID` INT)
BEGIN
	SELECT 
		t.Subject,
		 group_concat( concat(u.FirstName,' ',u.LastName)separator ',') as Name,		
		 case when t.Priority =1
			  then 'High'
			    else
			  'Low' end as Priority,
			DueDate,
		t.Description,
		bc.BoardColumnName as TaskStatus,
		t.created_at,
		t.Task_type as followup_task,
		t.CreatedBy as created_by
	FROM tblTask t
	INNER JOIN tblCRMBoardColumn bc on  t.BoardColumnID = bc.BoardColumnID	
	INNER JOIN tblUser u on  u.UserID = t.UsersIDs		
	WHERE t.TaskID = p_TaskID;
END