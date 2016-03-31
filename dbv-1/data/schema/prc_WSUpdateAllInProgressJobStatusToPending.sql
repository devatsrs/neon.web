CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSUpdateAllInProgressJobStatusToPending`(IN `p_companyid` INT, IN `p_ModifiedBy` LONGTEXT )
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
  UPDATE tblJob 
  SET JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'P' )
  , updated_at = NOW()
  , ModifiedBy = p_ModifiedBy
  where 
  CompanyID =  p_companyid
 AND JobStatusID = ( Select tblJobStatus.JobStatusID from tblJobStatus where tblJobStatus.Code = 'I' )    ;
 
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END