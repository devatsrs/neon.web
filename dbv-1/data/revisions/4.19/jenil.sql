/* 4.19*/


/* ticket changes - 1657 */

DROP PROCEDURE IF EXISTS `prc_GetSystemTicket`;
DELIMITER //
CREATE PROCEDURE `prc_GetSystemTicket`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `P_Status` VARCHAR(100),
	IN `P_Priority` VARCHAR(100),
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100),
	IN `P_DueBy` VARCHAR(50),
	IN `P_CurrentDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_is_StartDate` INT,
	IN `p_is_EndDate` INT


)
BEGIN

	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_Groups_ varchar(200);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			T.TicketID,
			T.Subject,


  CASE
 	 WHEN T.AccountID>0   THEN     (SELECT CONCAT(IFNULL(TAA.AccountName,''),' (',T.Requester,')') FROM tblAccount TAA WHERE TAA.AccountID = T.AccountID  )
  	 WHEN T.ContactID>0   THEN     (select CONCAT(IFNULL(TCCC.FirstName,''),' ',IFNULL(TCCC.LastName,''),' (',T.Requester,')') FROM tblContact TCCC WHERE TCCC.ContactID = T.ContactID)
     WHEN T.UserID>0      THEN  	 (select CONCAT(IFNULL(TUU.FirstName,''),' ',IFNULL(TUU.LastName,''),' (',T.Requester,')') FROM tblUser TUU WHERE TUU.UserID = T.UserID )
    ELSE CONCAT(T.RequesterName,' (',T.Requester,')')
  END AS Requester,
			T.Requester as RequesterEmail,
			TFV.FieldValueAgent  as TicketStatus,
			TP.PriorityValue,
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			TG.GroupName,
			T.created_at,

			(select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =1 and tc.EmailParent>0 order by tc.AccountEmailLogID desc limit 1) as CustomerResponse,
		    (select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =0  order by tc.AccountEmailLogID desc limit 1) as AgentResponse,
			(select TAC.AccountID from tblAccount TAC where 	TAC.Email = T.Requester or TAC.BillingEmail =T.Requester limit 1) as ACCOUNTID,
			T.`Read` as `Read`,
			T.TicketSlaID,
			T.DueDate,
			T.updated_at,
         T.Status,
         T.AgentRepliedDate,
         T.CustomerRepliedDate
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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND (
					P_DueBy = '' OR
					(  P_DueBy != '' AND
						(
							   (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate))
							OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
							OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
							OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate )
						)
					)
				)


			ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN T.Subject
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN T.Subject
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN TicketStatus
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN TicketStatus
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentASC') THEN TU.FirstName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentDESC') THEN TU.FirstName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN T.created_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN T.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN T.updated_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN T.updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterASC') THEN T.Requester
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterDESC') THEN T.Requester
			END DESC
			LIMIT
				p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate));

	SELECT
			DISTINCT(TG.GroupID),
			TG.GroupName
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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ) );

	END IF;
	IF p_isExport = 1
	THEN
	SELECT
			T.TicketID,
			T.Subject,
			T.Requester,
			T.RequesterCC as 'CC',
			TFV.FieldValueAgent  as 'Status',
			TP.PriorityValue as 'Priority',
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			T.created_at as 'Date Created',
			TG.GroupName as 'Group'
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
		LEFT JOIN tblContact TCC
			ON TCC.Email = T.`Requester`

		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


/* Admin - Update Template - bug - 1642 */

INSERT INTO `Ratemanagement3`.`tblFileUploadTemplateType` (`TemplateType`, `Title`, `UploadDir`, `created_at`) VALUES ('FTPCDR', 'FTP CDR', 'CDR_UPLOAD', '2018-11-19 16:28:34');



/* Task:- PBX Re-Import CDR */

/*

ID-96 , Download Fusion PBX CDR
[[{"title":"Fusion PBX Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID -8, Download PBX CDR
[[{"title":"PBX Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID - 83 Download MOR CDR
[[{"title":"MOR Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID- 85 Download Streamco CDR
[[{"title":"STREMCO Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID- 98 Download M2 CDR
[[{"title":"M2 Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID-217 Download VoipNow CDR
[[{"title":"Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID-531 Download SippySQL CDR
[[{"title":"SippySQL Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID - 3 Download Porta CDR
[[{"title":"Porta Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]

ID - 532 Download Voip.MS CDR
[[{"title":"Voip.ms Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]


except vois or sippy

*/

/* 
porta remain 
getStartDate  - TempUsageDownloadLog() every file
*/
/* NeonCDRDev */


DROP PROCEDURE IF EXISTS `prc_deleteCDRFromDate`;
DELIMITER //
CREATE PROCEDURE `prc_deleteCDRFromDate`(
	IN `p_StartDate` DATE,
	IN `p_CompanyGatewayID` INT(11),
	IN `p_processId` INT,
	IN `p_EndDate` DATE
)
BEGIN
     
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
     
     CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);
          
   	WHILE DATE(p_StartDate) <= DATE(p_EndDate) DO
   	
   		
			 DELETE r 
			FROM tblRetailUsageDetail r 
			JOIN tblUsageDetails d 
				on d.UsageDetailID=r.UsageDetailID AND d.ID = r.ID  
			JOIN tblUsageHeader h 
				on h.UsageHeaderID=d.UsageHeaderID 
			WHERE h.StartDate = p_StartDate AND h.CompanyGatewayID=p_CompanyGatewayID;
			
			 DELETE d
			FROM tblUsageDetailFailedCall d 
			JOIN tblUsageHeader h 
				on h.UsageHeaderID=d.UsageHeaderID 
			WHERE h.StartDate = p_StartDate AND h.CompanyGatewayID=p_CompanyGatewayID;
	
	
			 DELETE d
			FROM tblUsageDetails d 
			JOIN tblUsageHeader h 
				on h.UsageHeaderID=d.UsageHeaderID
			WHERE h.StartDate = p_StartDate AND h.CompanyGatewayID=p_CompanyGatewayID;
		
		
			 DELETE 
			FROM tblUsageHeader 
			WHERE StartDate = p_StartDate AND CompanyGatewayID=p_CompanyGatewayID;
			
			
			DELETE 
			FROM RMBilling3.tblTempUsageDownloadLog
			WHERE CompanyGatewayID=p_CompanyGatewayID AND DATE_FORMAT(end_time, "%Y-%m-%d") = p_StartDate;
		
		
			/* Vendor Tables */
				
			DELETE tblVendorCDR
			-- select *
		   FROM tblVendorCDR
			INNER JOIN tblVendorCDRHeader
				ON tblVendorCDR.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
		   WHERE StartDate = p_StartDate
		   AND tblVendorCDRHeader.CompanyGatewayID = p_CompanyGatewayID;
		 
		 
		   DELETE tblVendorCDRFailed
		  FROM tblVendorCDRFailed
		  INNER JOIN tblVendorCDRHeader
		  	ON tblVendorCDRFailed.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
		  WHERE StartDate = p_StartDate
		  AND tblVendorCDRHeader.CompanyGatewayID = p_CompanyGatewayID;
		
						
			DELETE
			FROM tblVendorCDRHeader
			WHERE StartDate = p_StartDate
			AND CompanyGatewayID = p_CompanyGatewayID;
		
		
   		SET p_StartDate = DATE_ADD(p_StartDate, INTERVAL 1 DAY);
   	
   	END WHILE;
     
     
	      
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;

/* Above Done on Staging */


/* Sippy Vendor Rate push */
use NeonRMDev;

-- Dumping structure for table NeonRMDev.tblSippyVendorDestiMap
CREATE TABLE IF NOT EXISTS `tblSippyVendorDestiMap` (
  `SippyVendorDestiMapID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `VendorID` int(11) NOT NULL DEFAULT '0',
  `CodeRule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `DestinationSetID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`SippyVendorDestiMapID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Dumping structure for table NeonRMDev.tblDestinationSet
CREATE TABLE IF NOT EXISTS `tblDestinationSet` (
  `DestinationSetID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `Name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DestinationSetID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Dumping structure for procedure NeonRMDev.prc_getSippyVendorDesti
DROP PROCEDURE IF EXISTS `prc_getSippyVendorDesti`;
DELIMITER //
CREATE PROCEDURE `prc_getSippyVendorDesti`(
	IN `p_CompanyID` INT,
	IN `p_VendorID` INT,
	IN `p_TrunkID` INT,
	IN `p_CodeRule` VARCHAR(255),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT

)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

    SELECT   
			tblAccount.AccountName,
			tblSippyVendorDestiMap.CodeRule,
			tblTrunk.Trunk,
			tblDestinationSet.name,
			tblSippyVendorDestiMap.SippyVendorDestiMapID,
			tblSippyVendorDestiMap.VendorID,
			tblSippyVendorDestiMap.TrunkID,
			tblSippyVendorDestiMap.DestinationSetID
            from tblSippyVendorDestiMap
            INNER JOIN tblAccount
            ON tblAccount.AccountID=tblSippyVendorDestiMap.VendorID
            LEFT JOIN tblDestinationSet 
				ON tblDestinationSet.DestinationSetID=tblSippyVendorDestiMap.DestinationSetID
				LEFT JOIN tblTrunk
				ON tblTrunk.TrunkID=tblSippyVendorDestiMap.TrunkID
            where tblSippyVendorDestiMap.CompanyID = p_CompanyID
				and ((p_VendorID=0 OR tblSippyVendorDestiMap.VendorID=p_VendorID))
				AND(p_CodeRule = '' OR tblSippyVendorDestiMap.CodeRule like Concat(p_CodeRule,'%'))
            AND((p_TrunkID = '' OR tblSippyVendorDestiMap.TrunkID = p_TrunkID))
         ORDER BY
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
                END ASC
				
            LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(SippyVendorDestiMapID) AS totalcount
            from tblSippyVendorDestiMap
            INNER JOIN tblAccount
            ON tblAccount.AccountID=tblSippyVendorDestiMap.VendorID
            LEFT JOIN tblDestinationSet 
				ON tblDestinationSet.DestinationSetID=tblSippyVendorDestiMap.DestinationSetID
				LEFT JOIN tblTrunk
				ON tblTrunk.TrunkID=tblSippyVendorDestiMap.TrunkID
            where tblSippyVendorDestiMap.CompanyID = p_CompanyID
				and ((p_VendorID=0 OR tblSippyVendorDestiMap.VendorID=p_VendorID))
				AND(p_CodeRule = '' OR tblSippyVendorDestiMap.CodeRule like Concat(p_CodeRule,'%'))
            AND((p_TrunkID = '' OR tblSippyVendorDestiMap.TrunkID = p_TrunkID));

	ELSE

			SELECT
			tblAccount.AccountName,
			tblSippyVendorDestiMap.CodeRule,
			tblTrunk.Trunk,
			tblDestinationSet.name as DestinationSet,
			tblSippyVendorDestiMap.SippyVendorDestiMapID,
			tblSippyVendorDestiMap.VendorID,
			tblSippyVendorDestiMap.TrunkID,
			tblSippyVendorDestiMap.DestinationSetID
            from tblSippyVendorDestiMap
            INNER JOIN tblAccount
            ON tblAccount.AccountID=tblSippyVendorDestiMap.VendorID
            LEFT JOIN tblDestinationSet 
				ON tblDestinationSet.DestinationSetID=tblSippyVendorDestiMap.DestinationSetID
				LEFT JOIN tblTrunk
				ON tblTrunk.TrunkID=tblSippyVendorDestiMap.TrunkID
            where tblSippyVendorDestiMap.CompanyID = p_CompanyID
				and ((p_VendorID=0 OR tblSippyVendorDestiMap.VendorID=p_VendorID))
				AND(p_CodeRule = '' OR tblSippyVendorDestiMap.CodeRule like Concat(p_CodeRule,'%'))
            AND((p_TrunkID = '' OR tblSippyVendorDestiMap.TrunkID = p_TrunkID));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;




