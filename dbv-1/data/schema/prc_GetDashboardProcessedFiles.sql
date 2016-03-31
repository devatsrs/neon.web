CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDashboardProcessedFiles`(IN `p_companyId` int , IN `p_userId` int , IN `p_isadmin` int)
BEGIN
    select `tblJob`.`JobID`, `tblJob`.`Title`, `tblJobStatus`.`Title` as Status, `tblJob`.`CreatedBy`, `tblJob`.`created_at` from tblJob inner join tblJobStatus on `tblJob`.`JobStatusID` = `tblJobStatus`.`JobStatusID` inner join tblJobType on `tblJob`.`JobTypeID` = `tblJobType`.`JobTypeID`
        where ( tblJobStatus.Title = 'Success' OR tblJobStatus.Title = 'Completed' ) and `tblJob`.`CompanyID` = p_companyId and
        ( p_isadmin = 1 OR ( p_isadmin = 0 and `tblJob`.`JobLoggedUserID` = p_userId))  and `tblJob`.`updated_at` >= DATE_ADD(NOW(),INTERVAL -7 DAY)
		  LIMIT 10;
END