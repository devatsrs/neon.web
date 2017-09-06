CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getMsgsDropdown`(
	IN `p_CompanyId` int ,
	IN `p_userId` int ,
	IN `p_isadmin` int ,
	IN `p_reset` int 


)
BEGIN
   
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;  
  
  IF p_reset = 1 
  THEN
    UPDATE `tblMessages` set `ShowInCounter` = '0' where `CompanyID` = p_CompanyId AND `ShowInCounter` = '1';
  END IF;
        SELECT  
			 `tblMessages`.`MsgID`, `tblMessages`.`Title`,  `tblMessages`.`HasRead`, `tblMessages`.`CreatedBy`, `tblMessages`.`created_at`,`tblMessages`.Description,tblMessages.EmailID
        FROM 
			`tblMessages` 
        WHERE
			`tblMessages`.`CompanyID` = p_CompanyId AND (p_isadmin = 1 OR (p_isadmin = 0 AND tblMessages.MsgLoggedUserID = p_userId))
        order by 
			`tblMessages`.`ShowInCounter` desc, `tblMessages`.`updated_at` desc,`tblMessages`.MsgID desc 
		limit 10;
		  
		SELECT  
			count(*) as totalNonVisitedJobs
        FROM
			`tblMessages` 
			INNER JOIN AccountEmailLog ae
		    ON ae.AccountEmailLogID = tblMessages.EmailID
        WHERE
			`tblMessages`.`CompanyID` = p_CompanyId AND (p_isadmin = 1 OR (p_isadmin = 0 AND tblMessages.MsgLoggedUserID = p_userId))
			AND ae.EmailCall = 1 AND  tblMessages.HasRead=0
        order by
			`tblMessages`.`ShowInCounter` desc, `tblMessages`.`updated_at` desc,`tblMessages`.MsgID desc;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;  
    
END