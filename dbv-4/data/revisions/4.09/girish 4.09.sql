USE `StagingReport`;

DROP PROCEDURE IF EXISTS `prc_updateUnbilledAmount`;
DELIMITER |
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateUnbilledAmount`(
	IN `p_CompanyID` INT,
	IN `p_Today` DATETIME
)
BEGIN
	
	DECLARE v_Round_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_LastInvoiceDate_ DATE;
	DECLARE v_FinalAmount_ DOUBLE;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
	CREATE TEMPORARY TABLE tmp_Account_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		LastInvoiceDate DATE
	);
	
	INSERT INTO tmp_Account_ (AccountID)
	SELECT DISTINCT tblSummaryHeader.AccountID  FROM tblSummaryHeader INNER JOIN Ratemanagement3.tblAccount ON tblAccount.AccountID = tblSummaryHeader.AccountID WHERE tblSummaryHeader.CompanyID = p_CompanyID;
	
	UPDATE tmp_Account_ SET LastInvoiceDate = fngetLastInvoiceDate(AccountID);
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Account_);

	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_AccountID_ = (SELECT AccountID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
		SET v_LastInvoiceDate_ = (SELECT LastInvoiceDate FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
		
		CALL prc_getUnbilledReport(p_CompanyID,v_AccountID_,v_LastInvoiceDate_,p_Today,3);
		
		SELECT FinalAmount INTO v_FinalAmount_ FROM tmp_FinalAmount_;
		
		IF (SELECT COUNT(*) FROM Ratemanagement3.tblAccountBalance WHERE AccountID = v_AccountID_) > 0
		THEN
			UPDATE Ratemanagement3.tblAccountBalance SET UnbilledAmount = v_FinalAmount_ WHERE AccountID = v_AccountID_;
		ELSE
			INSERT INTO Ratemanagement3.tblAccountBalance (AccountID,UnbilledAmount,BalanceAmount)
			SELECT v_AccountID_,v_FinalAmount_,v_FinalAmount_;
		END IF;
		
		SET v_pointer_ = v_pointer_ + 1;
	
	END WHILE;
	
	UPDATE 
		Ratemanagement3.tblAccountBalance 
	INNER JOIN
		(
			SELECT 
				DISTINCT tblAccount.AccountID 
			FROM Ratemanagement3.tblAccount  
			LEFT JOIN tmp_Account_ 
				ON tblAccount.AccountID = tmp_Account_.AccountID
			WHERE tmp_Account_.AccountID IS NULL AND tblAccount.CompanyID = p_CompanyID
		) TBL
	ON TBL.AccountID = tblAccountBalance.AccountID
	SET UnbilledAmount = 0;
	
	CALL prc_updateVendorUnbilledAmount(p_CompanyID,p_Today);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

-- Dumping structure for procedure StagingReport.prc_updateVendorUnbilledAmount
DROP PROCEDURE IF EXISTS `prc_updateVendorUnbilledAmount`;
DELIMITER |
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateVendorUnbilledAmount`(
	IN `p_CompanyID` INT,
	IN `p_Today` DATETIME
)
BEGIN
	
	DECLARE v_Round_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_LastInvoiceDate_ DATETIME;
	DECLARE v_FinalAmount_ DOUBLE;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
	CREATE TEMPORARY TABLE tmp_Account_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		LastInvoiceDate DATETIME
	);
	
	INSERT INTO tmp_Account_ (AccountID)
	SELECT DISTINCT tblSummaryVendorHeader.AccountID  FROM tblSummaryVendorHeader INNER JOIN Ratemanagement3.tblAccount ON tblAccount.AccountID = tblSummaryVendorHeader.AccountID WHERE tblSummaryVendorHeader.CompanyID = p_CompanyID;
	
	UPDATE tmp_Account_ SET LastInvoiceDate = fngetLastVendorInvoiceDate(AccountID);
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Account_);

	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_AccountID_ = (SELECT AccountID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
		SET v_LastInvoiceDate_ = (SELECT LastInvoiceDate FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
		
		IF v_LastInvoiceDate_ IS NOT NULL
		THEN
		
			CALL prc_getVendorUnbilledReport(p_CompanyID,v_AccountID_,v_LastInvoiceDate_,p_Today,3);
			
			SELECT FinalAmount INTO v_FinalAmount_ FROM tmp_FinalAmount_;
			
			IF (SELECT COUNT(*) FROM Ratemanagement3.tblAccountBalance WHERE AccountID = v_AccountID_) > 0
			THEN
				UPDATE Ratemanagement3.tblAccountBalance SET VendorUnbilledAmount = v_FinalAmount_ WHERE AccountID = v_AccountID_;
			ELSE
				INSERT INTO Ratemanagement3.tblAccountBalance (AccountID,VendorUnbilledAmount,BalanceAmount)
				SELECT v_AccountID_,v_FinalAmount_,v_FinalAmount_;
			END IF;
			
		END IF;
		
		SET v_pointer_ = v_pointer_ + 1;
	
	END WHILE;
	
	UPDATE 
		Ratemanagement3.tblAccountBalance 
	INNER JOIN
		(
			SELECT 
				DISTINCT tblAccount.AccountID 
			FROM Ratemanagement3.tblAccount  
			LEFT JOIN tmp_Account_ 
				ON tblAccount.AccountID = tmp_Account_.AccountID
			WHERE tmp_Account_.AccountID IS NULL AND tblAccount.CompanyID = p_CompanyID
		) TBL
	ON TBL.AccountID = tblAccountBalance.AccountID
	SET VendorUnbilledAmount = 0;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;