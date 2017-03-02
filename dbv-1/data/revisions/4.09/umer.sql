CREATE TABLE IF NOT EXISTS `tblHelpDeskTickets` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `TicketID` int(11) DEFAULT NULL,
  `Subject` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Description` text COLLATE utf8_unicode_ci NOT NULL,
  `Priority` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `GUID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Group` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to_emails` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RequestEmail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TicketType` tinyint(4) NOT NULL DEFAULT '0',
  `TicketAgent` int(11) NOT NULL DEFAULT '0',
  `ApiCreatedDate` datetime DEFAULT NULL,
  `ApiUpdateDate` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Fresh desk tickets will save  here temporarily\r\nprc_getAccountTimeLine will use it';

-- ##############################################################
-- ALTER TABLE `tblAccountTickets`	ADD COLUMN `TicketType` TINYINT NULL DEFAULT '0' AFTER `RequestEmail`;
-- ALTER TABLE `tblAccountTickets`	CHANGE COLUMN `TicketType` `TicketType` TINYINT(4) NOT NULL DEFAULT '0' AFTER `RequestEmail`;
-- ALTER TABLE `tblAccountTickets`	ADD COLUMN `TicketAgent` INT NOT NULL DEFAULT '0' AFTER `TicketType`;
-- ##############################################################
 INSERT INTO tblHelpDeskTickets FROM tblAccountTickets;
-- ##############################################################
insert into tblHelpDeskTickets (CompanyID,AccountID,TicketID,Subject,Description,Priority,`Status`,`Type`,GUID,`Group`,to_emails,RequestEmail,TicketType,TicketAgent,ApiCreatedDate,ApiUpdateDate,created_at,created_by,updated_at,updated_by)
(select CompanyID,AccountID,TicketID,Subject,Description,Priority,`Status`,`Type`,GUID,`Group`,to_emails,RequestEmail,0 as TicketType, 0 as TicketAgent,ApiCreatedDate,ApiUpdateDate,created_at,created_by,updated_at,updated_by from tblAccountTickets ta);
-- ##############################################################
ALTER TABLE `AccountEmailLog`
	ADD COLUMN `ContactID` INT(11) NULL DEFAULT NULL AFTER `AccountID`,
	ADD COLUMN `UserType` TINYINT NULL DEFAULT '0' COMMENT '0 for account,1 for contact' AFTER `ContactID`;
-- ##############################################################
ALTER TABLE `tblEmailTemplate`
	ADD COLUMN `EmailFrom` VARCHAR(100) NULL DEFAULT NULL AFTER `Type`,
	ADD COLUMN `StaticType` TINYINT NULL DEFAULT '0' COMMENT '0 for allow deleted,1 for not  allowed to delete' AFTER `EmailFrom`;
ALTER TABLE `tblEmailTemplate`
	ADD COLUMN `SystemType` VARCHAR(100) NULL DEFAULT NULL AFTER `StaticType`;
ALTER TABLE `tblEmailTemplate`
	ADD COLUMN `Status` TINYINT NULL DEFAULT '1' AFTER `SystemType`;
ALTER TABLE `tblEmailTemplate`
	ADD COLUMN `StatusDisabled` TINYINT(4) NULL DEFAULT '0' AFTER `Status`;
ALTER TABLE `tblEmailTemplate`
	CHANGE COLUMN `StatusDisabled` `StatusDisabled` TINYINT(4) NULL DEFAULT '0' COMMENT '0 for allow disable,1 for not  allow disable' AFTER `Status`;
-- ##############################################################
CREATE TABLE IF NOT EXISTS `tbl_Account_Contacts_Activity` (
  `Timeline_id` int(11) NOT NULL AUTO_INCREMENT,
  `Timeline_type` int(11) NOT NULL DEFAULT '0',
  `TaskTitle` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TaskName` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TaskPriority` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DueDate` datetime DEFAULT NULL,
  `TaskDescription` longtext COLLATE utf8_unicode_ci,
  `TaskStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `followup_task` int(11) DEFAULT NULL,
  `TaskID` int(11) DEFAULT NULL,
  `Emailfrom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailTo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailToName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailSubject` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailMessage` longtext COLLATE utf8_unicode_ci,
  `EmailCc` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailBcc` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailAttachments` longtext COLLATE utf8_unicode_ci,
  `AccountEmailLogID` int(11) DEFAULT NULL,
  `NoteID` int(11) DEFAULT NULL,
  `ContactNoteID` int(11) DEFAULT '0',
  `Note` longtext COLLATE utf8_unicode_ci,
  `TicketID` int(11) DEFAULT NULL,
  `TicketSubject` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TicketStatus` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RequestEmail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TicketPriority` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TicketType` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TicketGroup` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TicketDescription` longtext COLLATE utf8_unicode_ci,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `TableCreated_at` datetime DEFAULT NULL,
  `GUID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Timeline_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='temp table';
-- ##############################################################
DROP PROCEDURE IF EXISTS `prc_GetFromEmailAddress`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetFromEmailAddress`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_Ticket` INT,
	IN `p_Admin` INT
)
BEGIN
	DECLARE V_Admin int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	
	/*select count(*) into V_Admin from tblUser tu where tu.UserID=p_userID and tu.Roles like '%Admin';*/
	
	IF p_Ticket = 1
	THEN
		SELECT DISTINCT GroupEmailAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupEmailAddress IS NOT NULL;
	END IF;	
	IF p_Ticket = 0
	THEN
		IF p_Admin > 0
		THEN
			SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
				UNION ALL
			SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu;
		END IF;
		IF p_Admin < 1	
		THEN
			SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
				UNION ALL
			SELECT tu.EmailAddress as EmailFrom from tblUser tu where tu.UserID = 1;							
		END IF;
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- ####################################################################
DROP PROCEDURE IF EXISTS `prc_getAccountTimeLine`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountTimeLine`(
	IN `p_AccountID` INT,
	IN `p_CompanyID` INT,
	IN `p_TicketType` INT,
	IN `p_GUID` VARCHAR(100),
	IN `p_Time` DATE,
	IN `p_Start` INT,
	IN `p_RowspPage` INT
)
BEGIN
DECLARE v_OffSet_ int;
DECLARE v_ActiveSupportDesk int;
DECLARE v_contacts_ids varchar(200);
DECLARE v_contacts_emails varchar(200);
DECLARE v_account_emails varchar(200);
DECLARE v_account_contacts_emails varchar(300);

SET v_OffSet_ = p_Start;
SET sql_mode = 'ALLOW_INVALID_DATES';

/*delete 2 days old records*/
if(p_Start>0)
THEN
	DELETE FROM tbl_Account_Contacts_Activity  WHERE  `TableCreated_at` < DATE_SUB(p_Time, INTERVAL 2 DAY);
END IF;
if(p_Start= 0)
THEN
		/*storing contact ids and email addresses*/
		SELECT 
			group_concat(CC.ContactID separator ',')
		FROM
			tblContact CC
		WHERE 
			CC.Owner = p_AccountID	INTO v_contacts_ids;
			
		SELECT 
			group_concat(DISTINCT CC.Email separator ',')
		FROM
			tblContact CC
		WHERE 
			CC.Owner = p_AccountID	INTO v_contacts_emails;
			
		/*storing account emails*/	
		SELECT 
		(CASE WHEN tac.Email = tac.BillingEmail THEN tac.Email ELSE concat(tac.Email,',',tac.BillingEmail) END) 
		FROM
			tblAccount tac
		WHERE 
			tac.AccountID = p_AccountID	INTO v_contacts_emails;	
		/*combining account and contacts emails*/
		select concat(v_contacts_emails,',',v_contacts_emails) into v_account_contacts_emails;

		
			/*tasks start*/
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
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
				t.TaskID,
				'' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,0 as NoteID,'' as Note ,
				0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,
				t.CreatedBy,t.created_at, t.updated_at,p_GUID as GUID,p_Time as TableCreated_at
			FROM tblTask t
			INNER JOIN tblCRMBoardColumn bc on  t.BoardColumnID = bc.BoardColumnID	
			JOIN tblUser u on  u.UserID = t.UsersIDs		
			WHERE t.AccountIDs = p_AccountID and t.CompanyID =p_CompanyID;
			/*tasks end*/
			
			
			/*emails start*/
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 2 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus,0 as Task_type,0 as TaskID, Emailfrom, EmailTo,	
			case when concat(tu.FirstName,' ',tu.LastName) IS NULL or concat(tu.FirstName,' ',tu.LastName) = ''
					then ael.EmailTo
					else concat(tu.FirstName,' ',tu.LastName)
			   end as EmailToName, 
			Subject,Message,Cc,Bcc,AttachmentPaths as EmailAttachments,AccountEmailLogID,0 as NoteID,'' as Note ,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,ael.CreatedBy,ael.created_at, ael.updated_at,p_GUID as GUID,p_Time as TableCreated_at
			from `AccountEmailLog` ael
			left JOIN tblUser tu
				ON tu.EmailAddress = ael.EmailTo
			where ael.CompanyID = p_CompanyID AND ((ael.AccountID = p_AccountID) OR (FIND_IN_SET(ael.ContactID,(v_contacts_ids)) ))
			and ael.EmailParent=0 order by ael.created_at desc;
			/*emails end*/
			
			/*nots start*/
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 3 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at ,p_GUID as GUID,p_Time as TableCreated_at
			from `tblNote` 
			where (`CompanyID` = p_CompanyID and `AccountID` = p_AccountID) order by created_at desc;
			
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `ContactNoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 3 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at ,p_GUID as GUID,p_Time as TableCreated_at
			from `tblContactNote` where (`CompanyID` = p_CompanyID and FIND_IN_SET(ContactID,(v_contacts_ids)))  order by created_at desc;
			/*notes end*/
			
			/*tickets start*/
			/*IF v_ActiveSupportDesk=1*/
				
			IF p_TicketType=2 /*FRESH DESK TICKETS*/
			THEN
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 4 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
			TAT.TicketID,	TAT.Subject as 	TicketSubject,	TAT.Status as	TicketStatus,	TAT.RequestEmail as 	RequestEmail,	TAT.Priority as 	TicketPriority,	TAT.`Type` as 	TicketType,	TAT.`Group` as 	TicketGroup,	TAT.`Description` as TicketDescription,created_by,ApiCreatedDate as created_at,ApiUpdateDate as updated_at,p_GUID as GUID,p_Time as TableCreated_at 
			from 
				`tblHelpDeskTickets` TAT 
			where 
				(TAT.`CompanyID` = p_CompanyID and TAT.`AccountID` = p_AccountID and TAT.GUID = p_GUID);
			END IF;

			IF p_TicketType=1 /*SYSTEM TICKETS*/
			THEN
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
				select 4 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
				TT.TicketID,TT.Subject as 	TicketSubject,	TFV.FieldValueAgent  as	TicketStatus,	TT.Requester as 	RequestEmail,TP.PriorityValue as TicketPriority,	TFVV.FieldValueAgent as 	TicketType,	TG.GroupName as TicketGroup,	TT.`Description` as TicketDescription,TT.created_by,TT.created_at,TT.updated_at,p_GUID as GUID,p_Time as TableCreated_at 
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
			/*LEFT JOIN tblAccount TA
				ON TA.Email = TT.Requester	
			LEFT JOIN tblAccount TAT
				ON TAT.BillingEmail = TT.Requester	
			LEFT JOIN tblContact TC
				ON TC.Email = TT.Requester	*/
			where 			
				TT.`CompanyID` = p_CompanyID and 
			/*(((TA.`AccountID` = p_AccountID) OR (TAT.`AccountID` = p_AccountID) ) OR (TC.ContactID IN (v_contacts_ids)))*/
			FIND_IN_SET(TT.Requester,(v_account_contacts_emails));	
			END IF;
			/*tickets end*/
END IF;
	select * from tbl_Account_Contacts_Activity where GUID = p_GUID order by created_at desc LIMIT p_RowspPage OFFSET v_OffSet_ ;
END//
DELIMITER ;
-- ##########################################################
DROP PROCEDURE IF EXISTS `prc_getContactTimeLine`;
DELIMITER //
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
	ael.EmailParent=0 
	order by ael.created_at desc;
	
	INSERT INTO tmp_actvity_timeline_
	select 3 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at 
	from `tblContactNote` where (`CompanyID` = p_CompanyID and `ContactID` = p_ContactID) order by created_at desc;
	/*IF v_ActiveSupportDesk=1*/

	IF p_TicketType=2 /*FRESH DESK TICKETS*/
	THEN
	INSERT INTO tmp_actvity_timeline_
	select 4 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
	TAT.TicketID,	TAT.Subject as 	TicketSubject,	TAT.Status as	TicketStatus,	TAT.RequestEmail as 	RequestEmail,	TAT.Priority as 	TicketPriority,	TAT.`Type` as 	TicketType,	TAT.`Group` as 	TicketGroup,	TAT.`Description` as TicketDescription,created_by,ApiCreatedDate as created_at,ApiUpdateDate as updated_at from `tblHelpDeskTickets` TAT where (TAT.`CompanyID` = p_CompanyID and TAT.`ContactID` = p_ContactID and TAT.GUID = p_GUID);
	END IF;

	IF p_TicketType=1 /*SYSTEM TICKETS*/
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
END//
DELIMITER ;
-- ##########################################################
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`) VALUES (1, 'Invoice Send', 'New Invoice {{InvoiceNumber}} from {{CompanyName}} ', 'Hi {{AccountName}}<br><br>\r\n\r\n\r\n{{CompanyName}} has sent you an invoice of {{InvoiceGrandTotal}} {{Currency}}, \r\nto download copy of your invoice please click the below link.&nbsp;<br><br>\r\n\r\n\r\n<div><a href="{{InvoiceLink}}" style="background-color:#ff9600;border:1px solid #ff9600;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:30px;text-align:center;text-decoration:none;width:100px;-webkit-text-size-adjust:none;mso-hide:all;" title="Link: {{InvoiceLink}}">View</a></div>\r\n<br><br>{{InvoiceOutstanding}} &nbsp;{{InvoiceNumber}} &nbsp;{{InvoiceGrandTotal}}{{Signature}}<br><br>\r\n\r\n\r\n\r\nBest Regards,<br><br>\r\n\r\n\r\n{{CompanyName}}<br>', '2017-02-13 10:28:29', '', '2017-03-01 16:18:58', '', NULL, 2, 'abc@email.com', 1, 'InvoiceSingleSend', 1, 1);
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`) VALUES (1, 'Estimate Send', 'New estimate {{EstimateNumber}} from {{CompanyName}} ', 'Hi {{AccountName}},<br><br>\r\n\r\n\r\n{{CompanyName}} has sent you an estimate of {{EstimateGrandTotal}} {{Currency}}, \r\nto download copy of your estimate please click the below link. <br><br>\r\n\r\n\r\n<div><a href="{{EstimateLink}}" style="background-color:#ff9600;border:2px solid #ff9600;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:30px;text-align:center;text-decoration:none;width:100px;-webkit-text-size-adjust:none;mso-hide:all;" title="Link: {{EstimateLink}}">View Estimate</a></div>\r\n<br><br>{{Comment}} &nbsp;<br><br>\r\n\r\n\r\n\r\nBest Regards,<br><br>\r\n\r\n\r\n{{CompanyName}}\r\n<br>{{CompanyAddress1}}<br>{{CompanyAddress2}}<br>{{CompanyAddress3}}<br>{{CompanyVAT}}<br>{{CompanyCountry}}<br>', '2017-02-13 10:28:29', '', '2017-03-01 16:19:12', '', NULL, 5, 'abc@email.com', 1, 'EstimateSingleSend', 1, 1);
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`) VALUES (1, 'New Comment on Estimate', 'New Comment added to Estimate {{EstimateNumber}}', 'Dear {{AccountName}},<br><br>\r\n\r\nComment added to Estimate {{EstimateNumber}}<br>\r\n{{Comment}}<br><br>\r\n\r\n\r\nBest Regards,<br><br>\r\n\r\n{{CompanyName}}<br>', '2017-02-13 10:28:29', '', '2017-03-01 16:18:45', '', NULL, 5, 'abc@email.com', 1, 'EstimateSingleComment', 1, 0);
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`) VALUES (1, 'New Comment on Task', 'New Comment by {{User}} on Task', '<meta charset="utf-8">\r\n    <meta name="viewport" content="width=device-width">\r\n    <title>Commit</title>\r\n\r\n\r\n<table style="vertical-align: top; border-collapse: collapse; padding-bottom: 0px; padding-top: 0px; padding-left: 0px; border-spacing: 0; padding-right: 0px; width: 95%; background-color: #f0f0f0" align="center">\r\n    <tbody>\r\n    <tr style="vertical-align: top; padding-bottom: 0px; text-align: center; padding-top: 0px; padding-left: 0px; padding-right: 0px">\r\n        <td style="vertical-align: top; padding-bottom: 0px; text-align: center; padding-top: 0px; padding-left: 0px; padding-right: 0px"><img src="{{Logo}}" title="Image: {{Logo}}" width="" border="0"></td>\r\n    </tr>\r\n    </tbody>\r\n</table>\r\n<br>{{FirstName}}&nbsp;{{LastName}}{{Address1}}{{Address2}}{{Address3}}{{PostCode}}<br><br><br>{{subject}}&nbsp;{{user}} {{Comment}} {{Logo}} &nbsp;{{CompanyCountry}}<br><br><br><table style="vertical-align: top; border-collapse: collapse; padding-bottom: 0px; padding-top: 0px; padding-left: 0px; border-spacing: 0; padding-right: 0px; width: 95%; background-color: #f0f0f0" align="center">\r\n    <tbody>\r\n    <tr style="vertical-align: top; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; padding-right: 0px">\r\n        <td width="5%"></td>\r\n        <td style="font-size: 14px; font-family: \'helvetica\', \'arial\', sans-serif; vertical-align: top; border-collapse: collapse; font-weight: normal; color: #4d4d4d; padding-bottom: 16px; text-align: center; padding-top: 16px; padding-left: 0px; margin: 0px; line-height: 19px; padding-right: 0px; -moz-hyphens: auto; -webkit-hyphens: auto; hyphens: auto" width="90%">\r\n            <div style="width: 90%; padding-bottom: 12px; padding-top: 12px; padding-left: 16px; margin: 0px auto; display: block; padding-right: 16px">\r\n                <h6 style="font-size: 20px; font-family: \'helvetica\', \'arial\', sans-serif; word-break: normal; font-weight: normal; color: #4d4d4d; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; margin: 0px 0px 12px; line-height: 1.3; padding-right: 0px">Update\r\n                </h6>\r\n                <table class="phenom" style="vertical-align: top; border-collapse: collapse; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; margin: 12px 0px; border-spacing: 0; padding-right: 0px" width="100%">\r\n                    <tbody>\r\n                    <tr style="vertical-align: top; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; padding-right: 0px">\r\n                        <td class="phenom-details" style="font-size: 14px; font-family: \'helvetica\', \'arial\', sans-serif; vertical-align: top; border-collapse: collapse; font-weight: normal; color: #4d4d4d; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; margin: 0px; line-height: 19px; padding-right: 0px; -moz-hyphens: auto; -webkit-hyphens: auto; hyphens: auto">\r\n                            <div style="float: left;clear:left;">\r\n                                <strong>{{User}}</strong> Commented on {{subject}}\r\n                                <p style="font-size: 14px; font-family: \'helvetica\', \'arial\', sans-serif; font-weight: normal; color: #4d4d4d; text-align: left; margin: 0px 0px 10px; line-height: 19px; padding: 10px;background-color:#ffffff;border-color: #d6cfcf;border-radius: 10px;border-style: solid;border-width: 1px 3px 3px 1px;margin: 5px;max-width: 95%;padding: 5px;">\r\n                                    {{Comment}}\r\n                                </p>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    </tbody>\r\n                </table>\r\n            </div>\r\n        </td>\r\n        <td width="5%"></td>\r\n    </tr>\r\n    </tbody>\r\n</table>', '2017-02-13 10:28:29', '', '2017-03-01 16:18:00', '', NULL, 8, 'abc@email.com', 1, 'TaskCommentEmail', 1, 0);
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`) VALUES (1, 'New Comment on Opportunity', 'New Comment by {{User}} on Opportunity', '<meta charset="utf-8">\r\n    <meta name="viewport" content="width=device-width">\r\n    <title>Commit</title>\r\n\r\n\r\n{{FirstName}} {{subject}} {{user}} &nbsp;{{CompanyName}} &nbsp;{{CompanyVAT}} {{CompanyCountry}} &nbsp;&nbsp;{{Address3}}<br><br><br>{{Logo}}<br><table style="vertical-align: top; border-collapse: collapse; padding-bottom: 0px; padding-top: 0px; padding-left: 0px; border-spacing: 0; padding-right: 0px; width: 95%; background-color: #f0f0f0" align="center">\r\n    <tbody>\r\n    <tr style="vertical-align: top; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; padding-right: 0px">\r\n        <td width="5%"></td>\r\n        <td style="font-size: 14px; font-family: \'helvetica\', \'arial\', sans-serif; vertical-align: top; border-collapse: collapse; font-weight: normal; color: #4d4d4d; padding-bottom: 16px; text-align: center; padding-top: 16px; padding-left: 0px; margin: 0px; line-height: 19px; padding-right: 0px; -moz-hyphens: auto; -webkit-hyphens: auto; hyphens: auto" width="90%">\r\n            <div style="width: 90%; padding-bottom: 12px; padding-top: 12px; padding-left: 16px; margin: 0px auto; display: block; padding-right: 16px">\r\n                <h6 style="font-size: 20px; font-family: \'helvetica\', \'arial\', sans-serif; word-break: normal; font-weight: normal; color: #4d4d4d; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; margin: 0px 0px 12px; line-height: 1.3; padding-right: 0px">Update\r\n                </h6>\r\n                <table class="phenom" style="vertical-align: top; border-collapse: collapse; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; margin: 12px 0px; border-spacing: 0; padding-right: 0px" width="100%">\r\n                    <tbody>\r\n                    <tr style="vertical-align: top; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; padding-right: 0px">\r\n                        <td class="phenom-details" style="font-size: 14px; font-family: \'helvetica\', \'arial\', sans-serif; vertical-align: top; border-collapse: collapse; font-weight: normal; color: #4d4d4d; padding-bottom: 0px; text-align: left; padding-top: 0px; padding-left: 0px; margin: 0px; line-height: 19px; padding-right: 0px; -moz-hyphens: auto; -webkit-hyphens: auto; hyphens: auto">\r\n                            <div style="float: left;clear:left;">\r\n                                <strong>{{User}}</strong> Commented on {{subject}}\r\n                                <p style="font-size: 14px; font-family: \'helvetica\', \'arial\', sans-serif; font-weight: normal; color: #4d4d4d; text-align: left; margin: 0px 0px 10px; line-height: 19px; padding: 10px;background-color:#ffffff;border-color: #d6cfcf;border-radius: 10px;border-style: solid;border-width: 1px 3px 3px 1px;margin: 5px;max-width: 95%;padding: 5px;">\r\n                                    {{Comment}}\r\n                                </p>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    </tbody>\r\n                </table>\r\n            </div>\r\n        </td>\r\n        <td width="5%"></td>\r\n    </tr>\r\n    </tbody>\r\n</table>', '2017-02-13 10:28:29', '', '2017-03-01 16:18:14', '', NULL, 9, 'abc@email.com', 1, 'OpportunityCommentEmail', 1, 0);
-- ###########################################################
update tblEmailTemplate set EmailFrom = (select Email from tblCompany);
-- ###########################################################