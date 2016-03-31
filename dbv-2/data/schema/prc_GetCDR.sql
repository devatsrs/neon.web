CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCDR`(IN `p_company_id` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT , IN `p_CDRType` CHAR(1), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

	   DECLARE v_OffSet_ int;
	   DECLARE v_BillingTime_ INT;
	   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
	 
		SELECT BillingTime INTO v_BillingTime_
		FROM LocalRatemanagement.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
		LIMIT 1;

		SET v_BillingTime_ = IFNULL(v_BillingTime_,1);


    	Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType);


	IF p_isExport = 0
	THEN
		   SELECT
		        AccountName,
		        connect_time,
		        disconnect_time,
		        billed_duration as duration,
		        cost,
		        cli,
		        cld,
		        AccountID,
		        p_CompanyGatewayID as CompanyGatewayID,
		        p_start_date as StartDate,
		        p_end_date as EndDate,
				  is_inbound as CDRType  from(
		        SELECT
		        		Distinct
		            uh.AccountName as AccountName,
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.billed_duration,
		            uh.cli,
		            uh.cld,
						format(uh.cost,6) as cost,
						AccountID,
						is_inbound
		        FROM tmp_tblUsageDetails_ uh



		    ) AS TBL
		    ORDER BY
		    			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
		            END ASC,

		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeDESC') THEN connect_time
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeASC') THEN connect_time
		            END ASC,

		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeDESC') THEN disconnect_time
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeASC') THEN disconnect_time
		            END ASC,

		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'durationDESC') THEN duration
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'durationASC') THEN duration
		            END ASC,

		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'costDESC') THEN cost
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'costASC') THEN cost
		            END ASC,

		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliDESC') THEN cli
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliASC') THEN cli
		            END ASC,

		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldDESC') THEN cld
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldASC') THEN cld
		            END ASC

			 LIMIT p_RowspPage OFFSET v_OffSet_;


		    SELECT
		        COUNT(*) AS totalcount
		    FROM (
		    select Distinct
		            uh.AccountName as AccountName,
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.duration,
		            uh.cli,
		            uh.cld,
						format(uh.cost,6) as cost,
						AccountID
		    FROM tmp_tblUsageDetails_ uh
		    ) AS TBL2;

		END IF;

		IF p_isExport = 1
		THEN

			SELECT
		        AccountName,
		        connect_time,
		        disconnect_time,
		        billed_duration as duration,
		        cost,
		        cli,
		        cld,
		        is_inbound
			from(
		        SELECT
		        		Distinct
		            uh.AccountName as AccountName,
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.billed_duration,
		            uh.cli,
		            uh.cld,
						format(uh.cost,6) as cost,
						AccountID,
						is_inbound
		        FROM tmp_tblUsageDetails_ uh
		    ) AS TBL;
		
		END IF;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END