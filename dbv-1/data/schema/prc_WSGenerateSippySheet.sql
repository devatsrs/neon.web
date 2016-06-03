CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateSippySheet`(IN `p_CustomerID` INT , IN `p_Trunks` VARCHAR(200), IN `p_Effective` VARCHAR(50))
BEGIN
    
    DECLARE v_codedeckid_ INT;
    DECLARE v_ratetableid_ INT;
    DECLARE v_RateTableAssignDate_ DATETIME;
    DECLARE v_NewA2ZAssign_ INT;
    DECLARE v_companyid_ INT;
    DECLARE v_TrunkID_ INT;
    DECLARE v_pointer_ INT ;
    DECLARE v_rowCount_ INT ;
   
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   
    /* if you chnage this table change in all sheet download and customer rate */
    DROP TEMPORARY TABLE IF EXISTS tmp_customerrateall_;
    CREATE TEMPORARY TABLE tmp_customerrateall_ (
        RateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        RoutinePlanName VARCHAR(50),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        TrunkID INT,
        RateTableRateId INT,
        IncludePrefix TINYINT,
        Prefix VARCHAR(50),
        RatePrefix VARCHAR(50),
        AreaPrefix VARCHAR(50)
    );
        
    DROP TEMPORARY TABLE IF EXISTS tmp_trunks_;
    CREATE TEMPORARY TABLE tmp_trunks_  (
        TrunkID INT,
        RowNo INT
    );
            
            
    SELECT 
        CompanyId INTO v_companyid_
    FROM tblAccount
    WHERE AccountID = p_CustomerID; 
        
        
        
    INSERT INTO tmp_trunks_
    SELECT TrunkID,
        @row_num := @row_num+1 AS RowID
    FROM tblCustomerTrunk,(SELECT @row_num := 0) x
    WHERE  FIND_IN_SET(tblCustomerTrunk.TrunkID,p_Trunks)!= 0 
        AND tblCustomerTrunk.AccountID = p_CustomerID;
        
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_trunks_);
        
        
    WHILE v_pointer_ <= v_rowCount_
    DO
         
        SET v_TrunkID_ = (SELECT TrunkID FROM tmp_trunks_ t WHERE t.RowNo = v_pointer_);
     
        CALL prc_GetCustomerRate(v_companyid_,p_CustomerID,v_TrunkID_,null,null,null,p_Effective,1,0,0,0,'','',2);
        
        INSERT INTO tmp_customerrateall_
        SELECT * FROM tmp_customerrate_;
        
        SET v_pointer_ = v_pointer_ + 1;
    END WHILE;
            
    SELECT 
            'A' as `Action [A|D|U|S|SA` ,
            '' as id,
            Concat(IFNULL(Prefix,''), Code) as Prefix,
            Description as COUNTRY ,
            Interval1 as `Interval 1`,
            IntervalN as `Interval N`,
            Rate as `Price 1`,
            Rate as `Price N`,
            0  as Forbidden,
            0 as `Grace Period`,
            'NOW' as `Activation Date`,
            '' as `Expiration Date` 
    FROM tmp_customerrateall_

    ORDER BY Prefix;
        
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END