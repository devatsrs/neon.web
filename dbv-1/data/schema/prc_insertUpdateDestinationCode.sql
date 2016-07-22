CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertUpdateDestinationCode`(IN `p_DestinationGroupID` INT, IN `p_RateID` TEXT, IN `p_CountryID` INT, IN `p_Code` VARCHAR(50), IN `p_Description` VARCHAR(50))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DELETE FROM tblDestinationGroupCode WHERE DestinationGroupID = p_DestinationGroupID;

	INSERT INTO tblDestinationGroupCode (DestinationGroupID,RateID)
	SELECT p_DestinationGroupID,RateID
	FROM tblRate r
	INNER JOIN tblDestinationGroupSet dgs
		ON dgs.CodedeckID =r.CodeDeckId
	INNER JOIN tblDestinationGroup dg
		ON dgs.DestinationGroupSetID = dg.DestinationGroupSetID
	WHERE dg.DestinationGroupID = p_DestinationGroupID
	AND (p_RateID = '' OR (p_RateID !='' AND FIND_IN_SET (r.RateID,p_RateID)!= 0 ))
	AND (p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
	AND (p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
	AND (p_CountryID = 0 OR r.CountryID = p_CountryID);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END