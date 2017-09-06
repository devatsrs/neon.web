CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getContactTimeLine`(
	IN `p_ContactID` INT,
	IN `p_CompanyID` INT,
	IN `p_TicketType` INT,
	IN `p_GUID` VARCHAR(100),
	IN `p_Start` INT,
	IN `p_RowspPage` INT



)
BEGIN
DECLARE v_OffSet_ int;
DECLARE v_ActiveSupportDesk int;
SET v_OffSet_ = p_Start;
SET sql_mode = 'ALLOW_INVALID_DATES';
	
	DROP TEMPORARY TABLE IF EXISTS tmp_actvity_timeline_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_actvity_timeline_(
		`Timeline_type` int(11),		
		Emailfrom varchar(50),
		EmailTo varchar(50),
		EmailToName varchar(50),
		EmailSubject varchar(200),
		EmailMessage LONGTEXT,
		EmailCc varchar(500),
		EmailBcc varchar(500),
		EmailAttachments LONGTEXT,
		AccountEmailLogID int(11),
	    NoteID int(11),
		Note longtext,		
		TicketID int(11),
		TicketSubject varchar(200),
		TicketStatus varchar(100),
		RequestEmail varchar(100),
		TicketPriority varchar(100),
		TicketType varchar(100),
		TicketGroup varchar(100),
		TicketDescription LONGTEXT,		
		CreatedBy varchar(50),
		created_at datetime,
		updated_at datetime		
	);
	
		
	
	
	INSERT INTO tmp_actvity_timeline_
	select 2 as Timeline_type, Emailfrom, EmailTo,	
	case when concat(tu.FirstName,' ',tu.LastName) IS NULL or concat(tu.FirstName,' ',tu.LastName) = ''
            then ael.EmailTo
            else concat(tu.FirstName,' ',tu.LastName)
       end as EmailToName, 
	Subject,Message,Cc,Bcc,AttachmentPaths as EmailAttachments,AccountEmailLogID,0 as NoteID,'' as Note ,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,ael.CreatedBy,ael.created_at, ael.updated_at 
	from `AccountEmailLog` ael
	left JOIN tblUser tu
		ON tu.EmailAddress = ael.EmailTo
	where 
	ael.ContactID = p_ContactID and
	ael.CompanyID = p_CompanyID and 
	ael.UserType=1 and
	ael.EmailParent=0 and
	
	
	ael.TicketID = 0
	order by ael.created_at desc;
	
	INSERT INTO tmp_actvity_timeline_
	select 3 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at 
	from `tblContactNote` where (`CompanyID` = p_CompanyID and `ContactID` = p_ContactID) order by created_at desc;
	

	IF p_TicketType=2 
	THEN
	INSERT INTO tmp_actvity_timeline_
	select 4 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
	TAT.TicketID,	TAT.Subject as 	TicketSubject,	TAT.Status as	TicketStatus,	TAT.RequestEmail as 	RequestEmail,	TAT.Priority as 	TicketPriority,	TAT.`Type` as 	TicketType,	TAT.`Group` as 	TicketGroup,	TAT.`Description` as TicketDescription,created_by,ApiCreatedDate as created_at,ApiUpdateDate as updated_at from `tblHelpDeskTickets` TAT where (TAT.`CompanyID` = p_CompanyID and TAT.`ContactID` = p_ContactID and TAT.GUID = p_GUID);
	END IF;

	IF p_TicketType=1 
	THEN
	INSERT INTO tmp_actvity_timeline_
		select 4 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
		TT.TicketID,TT.Subject as 	TicketSubject,	TFV.FieldValueAgent  as	TicketStatus,	TT.Requester as 	RequestEmail,TP.PriorityValue as TicketPriority,	TFVV.FieldValueAgent as 	TicketType,	TG.GroupName as TicketGroup,	TT.`Description` as TicketDescription,TT.created_by,TT.created_at,TT.updated_at 
	from 
		`tblTickets` TT
	LEFT JOIN tblTicketfieldsValues TFV
		ON TFV.ValuesID = TT.Status		
	LEFT JOIN tblTicketPriority TP
		ON TP.PriorityID = TT.Priority
	LEFT JOIN tblTicketGroups TG
		ON TG.GroupID = TT.`Group`
	LEFT JOIN tblTicketfieldsValues TFVV
		ON TFVV.ValuesID = TT.Type
	LEFT JOIN tblContact TC
		ON TC.Email = TT.Requester		
	where 			
		(TT.`CompanyID` = p_CompanyID and TC.`ContactID` = p_ContactID);	
	END IF;
	
	select * from tmp_actvity_timeline_ order by created_at desc LIMIT p_RowspPage OFFSET v_OffSet_ ;
END