CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertUpdateDestinationCode`(IN `p_DestinationGroupID` INT, IN `p_RateID` TEXT, IN `p_CountryID` INT, IN `p_Code` VARCHAR(50), IN `p_Description` VARCHAR(50), IN `p_Action` VARCHAR(50))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_Action = 'Insert'
	THEN

		INSERT INTO tblDestinationGroupCode (DestinationGroupID,RateID)
		SELECT p_DestinationGroupID,r.RateID
		FROM tblRate r
		INNER JOIN tblDestinationGroupSet dgs
			ON dgs.CodedeckID =r.CodeDeckId
		INNER JOIN tblDestinationGroup dg
			ON dgs.DestinationGroupSetID = dg.DestinationGroupSetID
		LEFT JOIN tblDestinationGroupCode dgc 
			ON dgc.DestinationGroupID = dg.DestinationGroupID 
			AND r.RateID = dgc.RateID
		WHERE dg.DestinationGroupID = p_DestinationGroupID
		AND (p_RateID = '' OR (p_RateID !='' AND FIND_IN_SET (r.RateID,p_RateID)!= 0 ))
		AND (p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
		AND (p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
		AND (p_CountryID = 0 OR r.CountryID = p_CountryID)
		AND dgc.DestinationGroupCodeID IS NULL;

	END IF;
	
	IF p_Action = 'Delete'
	THEN

		DELETE dgc
		FROM tblDestinationGroupCode dgc
		INNER JOIN tblRate r 
			ON r.RateID = dgc.RateID
		INNER JOIN tblDestinationGroup dg
			ON dg.DestinationGroupID = dgc.DestinationGroupID
		WHERE dg.DestinationGroupID = p_DestinationGroupID
		AND (p_RateID = '' OR (p_RateID !='' AND FIND_IN_SET (r.RateID,p_RateID)!= 0 ))
		AND (p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
		AND (p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
		AND (p_CountryID = 0 OR r.CountryID = p_CountryID);

	END IF;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END