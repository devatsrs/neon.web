CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updatePrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
/*
      -- update trunk with first trunk if not set UseInBilling
   set @stm1 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN LocalRatemanagement.tblCustomerTrunk ct
        ON ct.AccountID = ga.AccountID AND ct.Status =1
        AND UseInBilling = 0 and ud.processId = "' , p_processId , '"
    LEFT JOIN LocalRatemanagement.tblTrunk t
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE
    ud.processId = "' , p_processId , '"
    AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.cost > 0);
    ');

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	*/

/*
 -- update trunk if set UseInBilling
     set @stm2 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN LocalRatemanagement.tblCustomerTrunk ct
        ON ct.AccountID = ga.AccountID AND ct.Status =1 and ud.processId = "' , p_processId , '"
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
    LEFT JOIN LocalRatemanagement.tblTrunk t
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE
    ud.processId = "' , p_processId , '"
	 AND  ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.cost > 0)
    AND t.Trunk IS NOT NULL;
    ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    */


   DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
			TempUsageDetailID int,
			prefix varchar(50),
            trunk varchar(50)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
            TempUsageDetailID int,
            prefix varchar(50),
            trunk varchar(50)
    );



   /* insert customer rate prefix into table */
    set @stmt22 = CONCAT('
         INSERT INTO tmp_TempUsageDetail_
        SELECT
            ud.TempUsageDetailID,
            MAX(r.Code) AS prefix,
            IFNULL(MAX(t.Trunk),"Other") as trunk
            FROM LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
            Inner JOIN tblGatewayAccount ga
                    ON ud.GatewayAccountID = ga.GatewayAccountID
                    AND ud.CompanyGatewayID = ga.CompanyGatewayID
                    AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
            Inner JOIN LocalRatemanagement.tblCustomerTrunk ct
                  ON ct.AccountID = ga.AccountID AND ct.Status =1 AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 )
            Inner JOIN LocalRatemanagement.tblTrunk t ON t.TrunkID = ct.TrunkID
            Inner JOIN LocalRatemanagement.tblCustomerRate cr
                        ON cr.CustomerID = ga.AccountID AND  cr.TrunkID = ct.TrunkID
            Inner join LocalRatemanagement.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = ct.CodeDeckId and cr.RateID = r.RateID and cr.EffectiveDate <= Now()
            where ud.ProcessID = "' , p_processid  , '" and ud.cld like concat( r.Code , "%" )
            group by ud.TempUsageDetailID ;
        ');

    PREPARE stmt22 FROM @stmt22;
    EXECUTE stmt22;
    DEALLOCATE PREPARE stmt22;


   /* insert rate table prefix into table */
    set @stm3 = CONCAT('
     INSERT INTO tmp_TempUsageDetail2_
        SELECT
              ud.TempUsageDetailID,
            MAX(r.Code) AS prefix,
            IFNULL(MAX(t.Trunk),"Other") as trunk
            FROM LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
            LEFT join tmp_TempUsageDetail_ tmpcr on ud.processId = "' , p_processId , '" and tmpcr.TempUsageDetailID = ud.TempUsageDetailID
            Inner JOIN tblGatewayAccount ga
                    ON ud.GatewayAccountID = ga.GatewayAccountID
                    AND ud.CompanyGatewayID = ga.CompanyGatewayID
                    AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
            Inner JOIN LocalRatemanagement.tblCustomerTrunk ct
                  ON ct.AccountID = ga.AccountID AND ct.Status =1 AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 )
            Inner JOIN LocalRatemanagement.tblTrunk t ON t.TrunkID = ct.TrunkID
            Inner join LocalRatemanagement.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = ct.RateTableId
            Inner join LocalRatemanagement.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId
            Inner join LocalRatemanagement.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID and rtr.EffectiveDate <= Now()
            where tmpcr.TempUsageDetailID  is null and ud.ProcessID = "' , p_processid  , '" and ud.cld like concat( r.Code , "%" )
            group by ud.TempUsageDetailID;
    ');



    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;


    /* merge tables */
    Insert into tmp_TempUsageDetail_ (TempUsageDetailID,prefix,trunk  )
    select (TempUsageDetailID,prefix,trunk     )  from tmp_TempUsageDetail2_;



	set @stm4 = CONCAT('UPDATE LocalRMCdr.' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempUsageDetail_ tbl ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
    SET tbl2.area_prefix = tbl.prefix,
    tbl2.trunk = tbl.trunk
    WHERE tbl2.processId = "' , p_processId , '"
	 ');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
        
END