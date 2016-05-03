CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountTimeLine`(IN `p_AccountID` INT, IN `p_CompanyID` INT, IN `p_Start` INT, IN `p_RowspPage` INT)
BEGIN
DECLARE v_OffSet_ int;
SET v_OffSet_ = p_Start;

	DROP TEMPORARY TABLE IF EXISTS tmp_actvity_timeline_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_actvity_timeline_(
		`Timeline_type` int(11),
		TaskTitle varchar(100),
		TaskName varchar(200),
		TaskPriority varchar(200),
		DueDate datetime,
		TaskDescription LONGTEXT,
		TaskStatus varchar(50),
		followup_task int(11),		
		Emailfrom varchar(50),
		EmailTo varchar(50),
		EmailToName varchar(50),
		EmailSubject varchar(50),
		EmailMessage varchar(2000),
		EmailCc varchar(500),
		EmailBcc varchar(500),
		EmailAttachments LONGTEXT,
		AccountEmailLogID int(11),
	    NoteID int(11),
		Note longtext,
		CreatedBy varchar(50),
		created_at datetime,
		updated_at datetime		
	);
	
	
	
	INSERT INTO tmp_actvity_timeline_	
		SELECT 
		1 as Timeline_type,
		t.Subject,
		  concat(u.FirstName,' ',u.LastName) as Name,		
		 case when t.Priority =1
			  then 'High'
			  else
		 	   'Low' end  as Priority,
			DueDate,
		t.Description,
		bc.BoardColumnName as TaskStatus,
		t.Task_type,
		'' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,0 as NoteID,'' as Note ,
		t.CreatedBy,t.created_at, t.updated_at
	FROM tblTask t
	INNER JOIN tblCRMBoardColumn bc on  t.BoardColumnID = bc.BoardColumnID	
	JOIN tblUser u on  u.UserID = t.UsersIDs		
	WHERE t.AccountIDs = p_AccountID and t.CompanyID =p_CompanyID;
	
	
	
	
	INSERT INTO tmp_actvity_timeline_
	select 2 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus,0 as Task_type, Emailfrom, EmailTo,	
	case when concat(tu.FirstName,' ',tu.LastName) IS NULL or concat(tu.FirstName,' ',tu.LastName) = ''
            then ael.EmailTo
            else concat(tu.FirstName,' ',tu.LastName)
       end as EmailToName, 
	Subject,Message,Cc,Bcc,AttachmentPaths as EmailAttachments,AccountEmailLogID,0 as NoteID,'' as Note ,ael.CreatedBy,ael.created_at, ael.updated_at from `AccountEmailLog` ael
	left JOIN tblUser tu
		ON tu.EmailAddress = ael.EmailTo
	where (ael.AccountID = p_AccountID and ael.CompanyID = p_CompanyID) order by ael.created_at desc;
	
	INSERT INTO tmp_actvity_timeline_
	select 3 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,created_by,created_at,updated_at from `tblNote` where (`CompanyID` = '1' and `AccountID` = p_AccountID) order by created_at desc;


	select * from tmp_actvity_timeline_ order by created_at desc LIMIT p_RowspPage OFFSET v_OffSet_ ;
	
END