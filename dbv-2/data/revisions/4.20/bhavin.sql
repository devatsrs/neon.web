USE `RMBilling3`;

DROP PROCEDURE IF EXISTS `prc_getPBXExportPayment`;
DELIMITER //
CREATE PROCEDURE `prc_getPBXExportPayment`(
	IN `p_CompanyID` INT,
	IN `p_start_date` DATETIME,
	IN `p_Recall` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_Recall=0
	THEN
		SELECT
			ac.Number,
			Concat(PaymentID,' ',Ifnull(Notes,'') ) as Notes,
			PaymentDate,
			Amount
		FROM tblPayment
			INNER JOIN Ratemanagement3.tblAccount as ac ON ac.AccountID=tblPayment.AccountID
		WHERE
			PaymentType='Payment In'
			AND ac.CompanyId = p_CompanyID
			AND tblPayment.Status='Approved'
			AND Recall=p_Recall
			AND PaymentDate>p_start_date
			
			UNION		 
			SELECT 
				a.Number,
				Concat(sl.AccountBalanceSubscriptionLogID,' ',sl.Description) as Notes,
				sl.IssueDate as PaymentDate,
				(sl.TotalAmount * -1) as Amount
			FROM Ratemanagement3.tblAccountBalanceSubscriptionLog sl 
				INNER JOIN Ratemanagement3.tblAccountBalanceLog bl ON bl.AccountBalanceLogID=sl.AccountBalanceLogID
				INNER JOIN Ratemanagement3.tblAccount a ON a.AccountID=bl.AccountID
			WHERE a.CompanyId=p_CompanyID
			AND sl.IssueDate > p_start_date;
			
	END IF;		
	IF p_Recall=1
	THEN
	
		SELECT
			ac.Number,
			Concat(PaymentID,' ',Ifnull(Notes,'') ) as Notes,
			PaymentDate,
			Amount
		FROM tblPayment
			INNER JOIN Ratemanagement3.tblAccount as ac ON ac.AccountID=tblPayment.AccountID
		WHERE
			PaymentType='Payment In'
			AND ac.CompanyId = p_CompanyID
			AND tblPayment.Status='Approved'
			AND Recall=p_Recall
			AND PaymentDate>p_start_date;
	
	END IF;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;