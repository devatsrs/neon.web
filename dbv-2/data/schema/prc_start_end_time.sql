CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_start_end_time`(IN `p_ProcessID` VARCHAR(2000), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    set @stm1 = CONCAT('select 
   MAX(disconnect_time) as max_date,MIN(connect_time)  as min_date
   from RMCDR3.`' , p_tbltempusagedetail_name , '` where ProcessID = "' , p_ProcessID , '"');
   
   PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END