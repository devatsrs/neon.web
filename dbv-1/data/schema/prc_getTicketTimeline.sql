CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getTicketTimeline`(
	IN `p_CompanyID` INT,
	IN `p_TicketID` INT,
	IN `p_isCustomer` INT







)
BEGIN

DECLARE v_EmailParent int;	
	select AccountEmailLogID into v_EmailParent from tblTickets where TicketID = p_TicketID;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_ticket_timeline_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ticket_timeline_(
		`Timeline_type` int(11),		
		EmailCall int(11),
		EmailfromName varchar(200),
		EmailTo varchar(200),
		Emailfrom varchar(200),
		EmailMessage LONGTEXT,
		EmailCc varchar(500),
		EmailBcc varchar(500),
		AttachmentPaths LONGTEXT,
		AccountEmailLogID int(11),
	    NoteID int(11),
		Note longtext,			
		CreatedBy varchar(50),
		created_at datetime,
		updated_at datetime		
	);

	INSERT INTO tmp_ticket_timeline_
	select 1 as Timeline_type,EmailCall,EmailfromName,EmailTo,Emailfrom,Message,Cc,Bcc,IFNULL(AttachmentPaths,'a:0:{}'),AccountEmailLogID,0 as NoteID,'' as Note,ael.CreatedBy,ael.created_at, ael.updated_at 
	from `AccountEmailLog` ael	
	where 
	
	ael.TicketID = p_TicketID and
	ael.CompanyID = p_CompanyID 
	and ael.EmailParent > 0
	
	order by ael.created_at desc;
	
	IF p_isCustomer =0
	THEN
	
	INSERT INTO tmp_ticket_timeline_
	select 2 as Timeline_type,0 as EmailCall,'' as EmailfromName,'' as EmailTo,'' as Emailfrom,'' as Message,'' as Cc,'' as Bcc,'a:0:{}' as AttachmentPaths,0 as AccountEmailLogID,NoteID,Note,TN.created_by,TN.created_at, TN.updated_at 
	from `tblNote` TN	
	where 
	TN.TicketID = p_TicketID and
	TN.CompanyID = p_CompanyID  	
	order by TN.created_at desc;
END IF;
	select * from tmp_ticket_timeline_  order by created_at asc;		
END