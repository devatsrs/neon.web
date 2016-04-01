CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSInsertJobLog`(IN `p_companyId` INT , IN `p_accountId` INT , IN `p_processId` VARCHAR(50) , IN `p_process` VARCHAR(50) , IN `p_message` LONGTEXT)
BEGIN
	 
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
	INSERT  INTO tblLog
    ( CompanyID ,
      AccountID ,
      ProcessID ,
      Process ,
      Message
    )
    SELECT  p_companyId ,
            ( CASE WHEN ( p_accountId IS NOT NULL
                          AND p_accountId > 0
                        ) THEN p_accountId
                   ELSE ( SELECT    AccountID
                          FROM      tblJob
                          WHERE     ProcessID = p_processId
                        )
              END ) ,
            p_processId ,
            p_process ,
            p_message;
     SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;   
  
    END