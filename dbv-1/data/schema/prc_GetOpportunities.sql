CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetOpportunities`(IN `p_CompanyID` INT, IN `p_BoardID` INT, IN `p_OpportunityName` VARCHAR(50), IN `p_Tags` VARCHAR(50), IN `p_OwnerID` VARCHAR(100), IN `p_AccountID` INT, IN `p_Status` VARCHAR(50), IN `p_CurrencyID` INT, IN `p_OpportunityClosed` INT)
BEGIN
 
	DECLARE v_WorthTotal DECIMAL(18,8);
	DECLARE v_Round_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_Active_ int;
	SET v_Active_ = 1;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	SELECT cr.Symbol INTO v_CurrencyCode_ from tblCurrency cr where cr.CurrencyId = p_CurrencyID;
		
 DROP TEMPORARY TABLE IF EXISTS tmp_Oppertunites_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Oppertunites_(
		BoardColumnID int,
		BoardColumnName varchar(255),
		Height varchar(10),
		Width varchar(10),
		OpportunityID int,
		OpportunityName varchar(255),
		BackGroundColour varchar(10),
		TextColour varchar(10),
		Company varchar(100),
		Title varchar(10),
		FirstName varchar(50),
		LastName varchar(50),
		Owner varchar(100),
		UserID int,
		Phone varchar(50),
		Email varchar(255),
		BoardID int,
		AccountID int,
		Tags varchar(255),
		Rating int,
		TaggedUsers varchar(100),
		`Status` int,
 	   Worth DECIMAL(18,8),
		OpportunityClosed int,
		ClosingDate varchar(15),
		ExpectedClosing varchar(15),
		StartTime varchar(15)
);
  
		INSERT INTO tmp_Oppertunites_
		SELECT 
				bc.BoardColumnID,
				bc.BoardColumnName,
				bc.Height,
				bc.Width,
				o.OpportunityID,
				o.OpportunityName,
				o.BackGroundColour,
				o.TextColour,
				o.Company,
				o.Title,
				o.FirstName,
				o.LastName,
				concat(u.FirstName,concat(' ',u.LastName)) as Owner,
				o.UserID,
				o.Phone,
				o.Email,
				b.BoardID,
				o.AccountID,
				o.Tags,
				o.Rating,
				o.TaggedUsers,
				o.`Status`,
			   o.Worth,
			   o.OpportunityClosed,
			   Date(o.ClosingDate) as ClosingDate,
			   Date(o.ExpectedClosing) as ExpectedClosing,
				Time(o.ExpectedClosing) as StartTime		
		FROM tblCRMBoards b
		INNER JOIN tblCRMBoardColumn bc on bc.BoardID = b.BoardID AND b.BoardID = p_BoardID
		LEFT JOIN tblOpportunity o on o.BoardID = b.BoardID
					AND o.BoardColumnID = bc.BoardColumnID
					AND o.CompanyID = p_CompanyID
					AND (o.OpportunityClosed = p_OpportunityClosed)
					AND (p_Tags = '' OR find_in_set(o.Tags,p_Tags))
					AND (p_OpportunityName = '' OR o.OpportunityName LIKE Concat('%',p_OpportunityName,'%'))
					AND (p_OwnerID = '' OR o.UserID = p_OwnerID)
					AND (p_AccountID = 0 OR o.AccountID = p_AccountID)
					AND (p_Status = '' OR find_in_set(o.`Status`,p_Status))
					AND (p_CurrencyID = 0 OR p_CurrencyID in (Select CurrencyId FROM tblAccount Where tblAccount.AccountID = o.AccountID))
					AND (v_Active_ = (Select `Status` FROM tblAccount Where tblAccount.AccountID = o.AccountID limit 1))
					AND (v_Active_ = (Select `Status` FROM tblUser Where tblUser.UserID = o.UserID limit 1))
-- 		LEFT JOIN tblAccount ac on ac.AccountID = o.AccountID AND ac.`Status` = 1
	 	LEFT JOIN tblUser u on u.UserID = o.UserID
		ORDER BY bc.`Order`,o.`Order`; 

SELECT sum(Worth) as TotalWorth INTO v_WorthTotal from tmp_Oppertunites_;


 SELECT		*,	           
			ROUND(IFNULL(v_WorthTotal,0.00),v_Round_) as WorthTotal,
			v_CurrencyCode_ as CurrencyCode			
        FROM tmp_Oppertunites_ ;
END