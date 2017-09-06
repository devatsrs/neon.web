CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTicketGroups`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT 
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_isExport = 0
	THEN
		SELECT 
			TG.GroupID,
			TG.GroupName,
			(CONCAT(TG.GroupEmailAddress,' <br>(', CASE WHEN TG.GroupEmailStatus >0 THEN 'Verified'  ELSE 'Unverified' END,')')) as GroupEmailAddress,
			(SELECT count(TGA.GroupAgentsID) FROM tblTicketGroupAgents TGA WHERE TGA.GroupID = TG.GroupID ) as TotalAgents,
			TG.GroupAssignTime,
			(select concat(tu.FirstName,' ',tu.LastName)  from tblUser tu where tu.UserID = TG.GroupAssignEmail) as AssignUser,
			(select count(*)  from tblTickets tt where tt.Group = TG.GroupID) as GroupTickets
		FROM 
			tblTicketGroups TG				
		WHERE   
			TG.CompanyID = p_CompanyID			
			AND (p_Search = '' OR (TG.GroupName like Concat('%',p_Search,'%') OR  TG.GroupDescription like Concat('%',p_Search,'%')))
			group by TG.GroupID
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GroupNameASC') THEN TG.GroupName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GroupNameDESC') THEN TG.GroupName
			END DESC,			 							
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GroupAssignTimeASC') THEN TG.GroupAssignTime
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GroupAssignTimeDESC') THEN TG.GroupAssignTime
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalAgentsASC') THEN TotalAgents
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalAgentsDESC') THEN TotalAgents
			END DESC,			
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GroupEmailAddressASC') THEN GroupEmailAddress
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GroupEmailAddressDESC') THEN GroupEmailAddress
			END DESC,			
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AssignUserASC') THEN AssignUser
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AssignUserDESC') THEN AssignUser
			END DESC		
					
					
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(TG.GroupID) AS totalcount
		FROM tblTicketGroups TG			
		WHERE   TG.CompanyID = p_CompanyID			
			AND (p_Search = '' OR (TG.GroupName like Concat('%',p_Search,'%') OR  TG.GroupDescription like Concat('%',p_Search,'%'))); 

	END IF;
	IF p_isExport = 1	
	THEN
	SELECT 
			TG.GroupID,
			TG.GroupName,
			(CONCAT(TG.GroupEmailAddress,' (', CASE WHEN TG.GroupEmailStatus >0 THEN 'verified'  ELSE 'Unverified'	END,')')) as GroupEmailAddress,
			(SELECT count(TGA.GroupAgentsID) FROM tblTicketGroupAgents TGA WHERE TGA.GroupID = TG.GroupID ) as TotalAgents,
			TG.GroupAssignTime,
			(select  CASE WHEN TG.GroupAssignEmail >0 THEN concat(tu.FirstName,' ',tu.LastName) ELSE 'None' END as AssignUser from tblUser tu where tu.UserID = TG.GroupAssignEmail) as AssignUser
		FROM 
			tblTicketGroups TG				
		WHERE   
			TG.CompanyID = p_CompanyID			
			AND (p_Search = '' OR (TG.GroupName like Concat('%',p_Search,'%') OR  TG.GroupDescription like Concat('%',p_Search,'%')))
			group by TG.GroupID;
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END