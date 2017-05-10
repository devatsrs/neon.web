USE `Ratemanagement3`;


INSERT INTO `tblEmailTemplate` (`TemplateID`, `CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (null, 1, 'Notification - Invoice Paid by Customer', 'Invoice {{InvoiceNumber}} from {{CompanyName}} Paid by {{FirstName}} {{LastName}}', '<br>Invoice {{InvoiceNumber}} from {{CompanyName}} Paid by Customer {{FirstName}} {{LastName}}<br>\r\n\r\nto view paid invoice please click the below link.<br><br>\r\n\r\n\r\n<div><!--[if mso]>\r\n  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{InvoiceLink}}" style="height:30px;v-text-anchor:middle;width:100px;" arcsize="10%" strokecolor="#ff9600" fillcolor="#ff9600">\r\n    <w:anchorlock/>\r\n    <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">View</center>\r\n  </v:roundrect>\r\n<![endif]-->\r\n<a href="{{InvoiceLink}}" style="background-color:#ff9600;border:1px solid #ff9600;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:30px;text-align:center;text-decoration:none;width:100px;-webkit-text-size-adjust:none;mso-hide:all;" title="Link: {{InvoiceLink}}">View</a></div>\r\n<br><br>', '2017-04-11 19:30:19', 'System', '2017-04-11 19:34:24', 'System', NULL, 0, '', 1, 'InvoicePaidNotification', 1, 0, 0);
INSERT INTO `tblEmailTemplate` (`TemplateID`, `CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (70, 1, 'Dispute Raised', 'A dispute from {{CompanyName}} ', 'Hi {{AccountName}}<br><br>\r\n\r\n\r\n{{CompanyName}} has added a dispute of {{DisputeAmount}} {{Currency}} against {{InvoiceNumber}}\r\n\r\n\r\n\r\nBest Regards,<br><br>\r\n\r\n\r\n{{CompanyName}}', '2017-04-13 18:28:39', 'System', '2017-04-13 18:28:39', 'System', NULL, 0, '', 1, 'DisputeEmailCustomer', 1, 0, 0);


DROP TABLE IF EXISTS `tblTicketLog`;
CREATE TABLE IF NOT EXISTS `tblTicketLog` (
  `TicketLogID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT '0',
  `AccountID` int(11) DEFAULT '0',
  `CompanyID` int(11) DEFAULT NULL,
  `TicketID` int(11) DEFAULT NULL,
  `TicketFieldID` int(11) DEFAULT '0',
  `TicketFieldValueFromID` int(11) DEFAULT '0',
  `TicketFieldValueToID` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`TicketLogID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `tblTicketDashboardTimeline`;
CREATE TABLE IF NOT EXISTS `tblTicketDashboardTimeline` (
  `TicketDashboardTimelineID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `TimelineType` int(11) DEFAULT NULL,
  `UserName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `CustomerType` int(11) DEFAULT '0' COMMENT '0 for user,1 for Account,2 for Contact',
  `EmailCall` int(11) DEFAULT NULL,
  `TicketID` int(11) DEFAULT NULL,
  `TicketSubmit` int(11) DEFAULT NULL,
  `Subject` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RecordID` int(11) DEFAULT NULL,
  `AgentID` int(11) DEFAULT NULL,
  `GroupID` int(11) DEFAULT NULL,
  `TicketFieldID` int(11) DEFAULT NULL,
  `TicketFieldValueFromID` int(11) DEFAULT NULL,
  `TicketFieldValueToID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_at_table` datetime DEFAULT NULL,
  PRIMARY KEY (`TicketDashboardTimelineID`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP PROCEDURE IF EXISTS `prc_GetTicketDashboardTimeline`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTicketDashboardTimeline`(
	IN `p_CompanyID` INT,
	IN `P_Group` INT,
	IN `P_Agent` INT,
	IN `p_Time` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowsPage` INT
)
BEGIN
	DECLARE v_MAXDATE DATETIME;

	SELECT MAX(created_at) as created_at INTO v_MAXDATE FROM tblTicketDashboardTimeline;
	IF(p_PageNumber=0)
	THEN
		DELETE FROM tblTicketDashboardTimeline WHERE created_at_table < DATE_SUB(p_Time, INTERVAL 1 MONTH);
		INSERT INTO tblTicketDashboardTimeline
		SELECT
			NULL,
			tc.CompanyID,
			1 as TimeLineType,
			CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,'')) as UserName,
			u.UserID,
			CASE WHEN tc.AccountID != 0 THEN tc.AccountID
					WHEN tc.ContactID != 0 THEN tc.ContactID
					ELSE 0 END as CustomerID,
			CASE WHEN tc.AccountID != 0 THEN 1
					WHEN tc.ContactID != 0 THEN 2
					ELSE 0 END as CustomerType,
			acel.EmailCall,
			tc.TicketID,
			CASE WHEN tc.AccountEmailLogID != 0 AND acel.EmailParent=0 THEN 1 ELSE 0 END as TicketSubmit,
			tc.Subject,
			acel.AccountEmailLogID as ID,
			tc.Agent,
			tc.`Group`,
			0 as TicketFieldID,
			0 as TicketFieldValueFromID,
			0 as TicketFieldValueToID,
			acel.created_at,
			p_Time
		FROM AccountEmailLog acel
		INNER JOIN tblTickets tc ON tc.TicketID = acel.TicketID
		LEFT JOIN tblAccount ac ON ac.AccountID = acel.AccountID
		LEFT JOIN tblUser u ON u.UserID = acel.UserID
		WHERE acel.CompanyID = p_CompanyID
		AND (v_MAXDATE IS NULL OR acel.created_at > v_MAXDATE)
		AND acel.EmailCall = 0
		AND u.UserID IS NOT NULL;

		INSERT INTO tblTicketDashboardTimeline
		SELECT
		NULL,
		tc.CompanyID,
		2 as TimeLineType,
		CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,''))  as UserName,
		TN.UserID as UserID,
		0 as CustomerID,
		0 as CustomerType,
		0 as EmailCall,
		tc.TicketID,
		IF(tc.AccountEmailLogID = 0,0,1) as TicketSubmit,
		tc.Subject,
		TN.NoteID as ID,
		tc.Agent,
		tc.`Group`,
		0 as TicketFieldID,
		0 as TicketFieldValueFromID,
		0 as TicketFieldValueToID,
		TN.created_at,
		p_Time
		FROM `tblNote` TN
		INNER JOIN tblTickets tc ON tc.TicketID = TN.TicketID
		INNER JOIN tblUser u ON u.UserID = TN.UserID
		WHERE TN.CompanyID = p_CompanyID
		AND (v_MAXDATE IS NULL OR TN.created_at > v_MAXDATE);

		INSERT INTO tblTicketDashboardTimeline
		SELECT
			NULL,
			tc.CompanyID,
			3 as TimeLineType,
			CASE WHEN tl.AccountID = 0 THEN CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,'')) ELSE CONCAT(IFNULL(a.FirstName,''),' ',IFNULL(a.LastName,''))  END as UserName,
			tl.UserID as UserID,
			CASE WHEN tl.TicketFieldID = 0 THEN
				CASE WHEN tc.AccountID != 0 THEN tc.AccountID
						WHEN tc.ContactID != 0 THEN tc.ContactID
						ELSE 0 END
			ELSE
				tl.AccountID
			END as CustomerID,
			CASE WHEN tl.TicketFieldID = 0 THEN
				CASE WHEN tc.AccountID != 0 THEN 1
						WHEN tc.ContactID != 0 THEN 2
						ELSE 0 END
				ELSE 0 END as CustomerType,
			0 as EmailCall,
			tc.TicketID,
			IF(tc.AccountEmailLogID = 0,0,1) as TicketSubmit,
			tc.Subject,
			tl.TicketLogID as ID,
			tc.Agent,
			tc.`Group`,
			tl.TicketFieldID,
			tl.TicketFieldValueFromID,
			tl.TicketFieldValueToID,
			tl.created_at,
			p_Time
		FROM tblTicketLog tl
		INNER JOIN tblTickets tc ON tc.TicketID = tl.TicketID
		LEFT JOIN tblUser u ON u.UserID = tl.UserID
		LEFT JOIN tblAccount a ON a.AccountID = tl.AccountID
		WHERE tl.CompanyID = p_CompanyID
		AND (v_MAXDATE IS NULL OR tl.created_at > v_MAXDATE);
	END IF;
	SELECT * FROM tblTicketDashboardTimeline tl
	WHERE (P_Agent = 0 OR tl.AgentID = p_Agent)
	AND(P_Group = 0 OR tl.`GroupID` = p_Group)
	ORDER BY created_at DESC
	LIMIT p_RowsPage OFFSET p_PageNumber;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetCronJobHistory`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCronJobHistory`(
	IN `p_CronJobID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_SearchText` VARCHAR(50),
	IN `p_Status` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

    IF p_isExport = 0
    THEN
            SELECT
                tblCronJobCommand.Title,
                tblCronJobLog.CronJobStatus,
                tblCronJobLog.Message,
                tblCronJobLog.created_at
            FROM tblCronJob
            INNER JOIN tblCronJobLog
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
                AND tblCronJobLog.CronJobID = p_CronJobID
                AND (p_Status = '' OR tblCronJobLog.CronJobStatus = p_Status)
                AND tblCronJobLog.created_at between p_StartDate and p_EndDate
                AND (p_SearchText='' OR tblCronJobLog.Message like Concat('%',p_SearchText,'%'))
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN tblCronJobCommand.Title
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN tblCronJobCommand.Title
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusDESC') THEN tblCronJobLog.CronJobStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CronJobStatusASC') THEN tblCronJobLog.CronJobStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageDESC') THEN tblCronJobLog.Message
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MessageASC') THEN tblCronJobLog.Message
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tblCronJobLog.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tblCronJobLog.created_at
                END ASC
           LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(tblCronJobLog.CronJobID) as totalcount
        FROM tblCronJob
            INNER JOIN tblCronJobLog
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
                AND tblCronJobLog.CronJobID = p_CronJobID
                AND (p_Status = '' OR tblCronJobLog.CronJobStatus = p_Status)
                AND tblCronJobLog.created_at between p_StartDate and p_EndDate
                AND (p_SearchText='' OR tblCronJobLog.Message like Concat('%',p_SearchText,'%'))
            INNER JOIN tblCronJobCommand
                ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID;
    END IF;

    IF p_isExport = 1
    THEN
        SELECT
            tblCronJobCommand.Title,
            tblCronJobLog.CronJobStatus,
            tblCronJobLog.Message,
            tblCronJobLog.created_at
        FROM tblCronJob
        INNER JOIN tblCronJobLog
                ON tblCronJob.CronJobID = tblCronJobLog.CronJobID
                AND tblCronJobLog.CronJobID = p_CronJobID
                AND (p_Status = '' OR tblCronJobLog.CronJobStatus = p_Status)
                AND tblCronJobLog.created_at between p_StartDate and p_EndDate
                AND (p_SearchText='' OR tblCronJobLog.Message like Concat('%',p_SearchText,'%'))
        INNER JOIN tblCronJobCommand
            ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID;
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetTicketDashboardSummary`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTicketDashboardSummary`(
	IN `p_CompanyID` INT,
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100)
)
BEGIN

	DECLARE v_Open_ INT;
	DECLARE v_OnHold_ INT;
	DECLARE v_UnResolved_ INT;
	DECLARE v_TotalPaymentOut_ INT;
	DECLARE v_UnAssigned_ INT;
	DECLARE v_OnHoldChech_ VARCHAR(500);
	DECLARE v_UnResolvedCheck_ VARCHAR(500);

	SELECT CONCAT('Pending',',',GROUP_CONCAT(tfv.FieldValueAgent SEPARATOR ',')) as FieldValueAgent INTO v_OnHoldChech_ FROM tblTicketfieldsValues tfv
	WHERE tfv.FieldsID = (SELECT tf.TicketFieldsID FROM tblTicketfields tf WHERE tf.FieldType = 'default_status' LIMIT 1)
	AND tfv.FieldType !=0
	AND (tfv.FieldSlaTime=0)
	AND tfv.FieldValueAgent!='All UnResolved';

	DROP TEMPORARY TABLE IF EXISTS tmp_Tickets_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Tickets_(
		TicketAgentID VARCHAR(50),
		TicketStatusID int,
		TicketStatus VARCHAR(30)
	);

	INSERT INTO tmp_Tickets_
	SELECT
		T.Agent as TicketAgentID,
		TFV.ValuesID as  TicketStatusID,
		TFV.FieldValueAgent  as TicketStatus
	FROM
		tblTickets T
	LEFT JOIN tblTicketfieldsValues TFV
		ON TFV.ValuesID = T.Status
	LEFT JOIN tblTicketPriority TP
		ON TP.PriorityID = T.Priority
	LEFT JOIN tblUser TU
		ON TU.UserID = T.Agent
	LEFT JOIN tblTicketGroups TG
		ON TG.GroupID = T.`Group`
	WHERE
		T.CompanyID = p_CompanyID
		AND (P_Group = '' OR FIND_IN_SET(T.`Group`,P_Group))
		AND (P_Agent = '' OR FIND_IN_SET(T.`Agent`,P_Agent));

	SELECT COUNT(TicketStatusID) INTO v_Open_  FROM	tmp_Tickets_
	WHERE TicketStatus = 'Open';

	SELECT COUNT(TicketStatusID) INTO v_OnHold_  FROM	tmp_Tickets_
	WHERE FIND_IN_SET(TicketStatus,v_OnHoldChech_);

	SELECT COUNT(TicketStatusID) INTO v_UnResolved_  FROM	tmp_Tickets_
	WHERE !FIND_IN_SET(TicketStatus,'All UnResolved,Resolved,Closed');

	SELECT COUNT(TicketStatusID) INTO v_UnAssigned_  FROM	tmp_Tickets_
	WHERE TicketAgentID = 0;

	SELECT v_Open_ as `Open`, v_OnHold_ as OnHold,v_UnResolved_ as UnResolved, v_UnAssigned_ as UnAssigned;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CheckDueTickets`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CheckDueTickets`(
	IN `p_CompanyID` int,
	IN `p_currentDateTime` DATETIME,
	IN `P_Group` VARCHAR(50),
	IN `P_Agent` VARCHAR(50)

)
BEGIN
	DECLARE V_Status varchar(100);
	DECLARE V_OverDue int(11);
	DECLARE V_DueToday int(11);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='';

	SELECT
		 group_concat(TFV.ValuesID separator ',') INTO V_Status FROM tblTicketfieldsValues TFV
	LEFT JOIN tblTicketfields TF
		ON TF.TicketFieldsID = TFV.FieldsID
	WHERE
		TF.FieldType = 'default_status' AND TFV.FieldValueAgent!='Closed' AND TFV.FieldValueAgent!='Resolved';

	 DROP TEMPORARY TABLE IF EXISTS tmp_tickets_sla_voilation_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tickets_sla_voilation_(
		TicketID int,
		TicketSlaID int,
		CreatedDate datetime,
		ResolveTime datetime,
		DueDate datetime,
		IsResolvedVoilation int
	);
		insert into tmp_tickets_sla_voilation_
		SELECT
			T.TicketID,
			T.TicketSlaID as TicketSlaID,
			T.created_at as CreatedDate,
	  	CASE WHEN (TST.ResolveType = 'Minute') THEN
		       DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Minute)
	 	  	  WHEN ResolveType = 'Hour' THEN
		   	 DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Hour)
		 	  WHEN (TST.ResolveType = 'Day') THEN
		       DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Day)
	 	  	  WHEN ResolveType = 'Month' THEN
	   		 DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Month)
	  END AS ResolveTime,
	  T.DueDate,
	  T.ResolveSlaPolicyVoilationEmailStatus AS IsResolvedVoilation
		FROM
			tblTickets T
		LEFT JOIN tblTicketSlaTarget TST
			ON TST.TicketSlaID = T.TicketSlaID
		WHERE
			T.CompanyID = p_CompanyID
			AND TST.PriorityID = T.Priority
			AND (V_Status = '' OR find_in_set(T.`Status`,V_Status))
			AND (T.RespondSlaPolicyVoilationEmailStatus = 0 OR T.ResolveSlaPolicyVoilationEmailStatus = 0)
			AND T.TicketSlaID>0
			AND (P_Group = '' OR FIND_IN_SET(T.`Group`,P_Group))
			AND (P_Agent = '' OR FIND_IN_SET(T.`Agent`,P_Agent));


			UPDATE tmp_tickets_sla_voilation_ TSV SET
			TSV.IsResolvedVoilation  =
			CASE
				WHEN p_currentDateTime>=TSV.ResolveTime THEN 1 ELSE 0
			END;

			/*SELECT * FROM tmp_tickets_sla_voilation_ where IsResolvedVoilation >0;*/
			select count(*) as OverDue INTO V_OverDue from tmp_tickets_sla_voilation_ where IsResolvedVoilation >0;
			select count(*) as DueToday INTO V_DueToday from tmp_tickets_sla_voilation_ where IsResolvedVoilation >0 and DATE(p_currentDateTime) = DATE(ResolveTime);

			SELECT V_OverDue as OverDue,V_DueToday as DueToday;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;






