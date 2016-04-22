CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTasksSingle`(IN `p_TaskID` INT)
BEGIN
	SELECT 
		t.Subject,
		 group_concat( concat(u.FirstName,' ',u.LastName)separator ',') as Name,		
		 case when t.Priority =1
			  then 'High'
			  else
		 case when t.Priority =2
			  then 'Medium'
			    else
		 case when t.Priority =3
			  then 'Low' end end end as Priority,
			DueDate,
		t.Description,
		bc.BoardColumnName as TaskStatus,
		t.created_at,
		t.CreatedBy as created_by
	FROM tblTask t
	INNER JOIN tblCRMBoardColumn bc on  t.BoardColumnID = bc.BoardColumnID	
	INNER JOIN tblUser u on  u.UserID = t.UsersIDs		
	WHERE t.TaskID = p_TaskID;
END