CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_RateCompareRateUpdate`(
	IN `p_CompanyID` INT,
	IN `p_GroupBy` VARCHAR(50),
	IN `p_Type` VARCHAR(50),
	IN `p_TypeID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(50)

,
	IN `p_TrunkID` INT
,
	IN `p_Effective` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE
)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


    IF ( p_Type = 'VendorRate') THEN

      IF ( p_GroupBy = 'description') THEN

        Select *
        from tblVendorRate v
          inner join tblRate r on r.RateID = v.RateId
        where r.CountryID = p_CompanyID AND
              r.Description = p_Description AND
              v.AccountId = p_TypeID AND
              v.TrunkID = p_TrunkID
              AND
              (
                ( p_Effective = 'Now' AND v.EffectiveDate <= NOW() )
                OR
                ( p_Effective = 'Future' AND v.EffectiveDate > NOW())
                OR
                ( p_Effective = 'Selected' AND v.EffectiveDate <= DATE(p_SelectedEffectiveDate) )
              );

      ELSE

          Select *
          from tblVendorRate v
            inner join tblRate r on r.RateID = v.RateId
          where r.CountryID = p_CompanyID AND
                r.Code = p_Code AND
                r.Description = p_Description AND
                v.AccountId = p_TypeID AND
                v.TrunkID = p_TrunkID AND
                v.EffectiveDate = p_EffectiveDate;

      END IF;



    END IF;


    IF ( p_Type = 'RateTable') THEN

      IF ( p_GroupBy = 'description') THEN

        Select *
        from tblRateTableRate rtr
          inner join tblRate r on r.RateID = rtr.RateId
        where r.CountryID = p_CompanyID AND
              r.Description = p_Description AND
              rtr.RateTableId = p_TypeID
              AND
              (
                ( p_Effective = 'Now' AND rtr.EffectiveDate <= NOW() )
                OR
                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW())
                OR
                ( p_Effective = 'Selected' AND rtr.EffectiveDate <= DATE(p_SelectedEffectiveDate) )
              );

      ELSE

        Select *
        from tblRateTableRate rtr
          inner join tblRate r on r.RateID = rtr.RateId
        where r.CountryID = p_CompanyID AND
              r.Code = p_Code AND
              r.Description = p_Description AND
              rtr.RateTableId = p_TypeID AND
              rtr.EffectiveDate = p_EffectiveDate;


      END IF;


    END IF;

    IF ( p_Type = 'CustomerRate') THEN

      IF ( p_GroupBy = 'description') THEN

        Select *
        from tblVendorRate v
          inner join tblRate r on r.RateID = v.RateId
        where r.CountryID = p_CompanyID AND
              r.Description = p_Description AND
              v.AccountId = p_TypeID AND
              v.TrunkID = p_TrunkID
              AND
              (
                ( p_Effective = 'Now' AND v.EffectiveDate <= NOW() )
                OR
                ( p_Effective = 'Future' AND v.EffectiveDate > NOW())
                OR (
                  p_Effective = 'Selected' AND v.EffectiveDate <= DATE(p_SelectedEffectiveDate)
                )
              );

      ELSE

        Select *
        from tblVendorRate v
          inner join tblRate r on r.RateID = v.RateId
        where r.CountryID = p_CompanyID AND
              r.Code = p_Code AND
              r.Description = p_Description AND
              v.AccountId = p_TypeID AND
              v.TrunkID = p_TrunkID AND
              v.EffectiveDate = p_EffectiveDate
              ;

      END IF;

    END IF;
 
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


  END