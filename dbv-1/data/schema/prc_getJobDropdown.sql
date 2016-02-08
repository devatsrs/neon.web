CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getJobDropdown`(IN `p_CompanyId` int , IN `p_userId` int , IN `p_isadmin` int , IN `p_reset` int )
BEGIN
   
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;  
  
  IF p_reset = 1 
  THEN
    update `tblJob` set `ShowInCounter` = '0' where `CompanyID` = p_CompanyId and `ShowInCounter` = '1';
  END IF;
        select  `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`HasRead`, `tblJob`.`CreatedBy`, `tblJob`.`created_at` 
        from `tblJob` inner join `tblJobStatus` on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
        inner join `tblJobType` on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID` 
        where `tblJob`.`CompanyID` = p_CompanyId AND  `tblJob`.JobLoggedUserID = p_userId
        order by `tblJob`.`ShowInCounter` desc, `tblJob`.`updated_at` desc,`tblJob`.JobID desc 
		  limit 10;

	  select count(*) as totalNonVisitedJobs from `tblJob` 
    inner join `tblJobStatus` on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
    where `tblJob`.`CompanyID` = p_CompanyId and `tblJob`.`ShowInCounter` = '1' and `tblJob`.JobLoggedUserID = p_userId;
    
    

    select count(*) as totalPendingJobs from `tblJob` inner join `tblJobStatus` on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` 
    where `tblJobStatus`.`Title` = 'Pending' and `tblJob`.`CompanyID` = p_CompanyId AND 
	`tblJob`.JobLoggedUserID = p_userId;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
    
END