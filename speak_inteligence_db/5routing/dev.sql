use speakIntelligentRoutingEngine;
-- --------------------------------------------------------
-- Host:                         188.92.57.86
-- Server version:               5.7.24-log - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for procedure speakIntelligentRoutingEngine.prc_RoutingByAccOriDestLoc
DROP PROCEDURE IF EXISTS `prc_RoutingByAccOriDestLoc`;
DELIMITER //
CREATE PROCEDURE `prc_RoutingByAccOriDestLoc`(
	IN `p_OriginationNo` VARCHAR(200),
	IN `p_DestinationNo` VARCHAR(200),
	IN `p_DateAndTime` DATETIME,
	IN `p_AccountNo` VARCHAR(200),
	IN `p_AccountID` int,
	IN `p_AccountDynamicField` VARCHAR(200),
	IN `p_AccountDynamicFieldValue` VARCHAR(200),
	IN `p_Location` VARCHAR(200)


)
PRC:BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_Error_;
		CREATE TEMPORARY TABLE tmp_Error_ (
			Message longtext
		);


		-- find account id
		SET @v_AccountID = 0;
		SET @v_CompanyID = 0;
		SET @v_RoutingProfileID = 0;
		SET @v_TimezonesID = 0;

		IF p_AccountID > 0 THEN

			select AccountID , CompanyID INTO @v_AccountID, @v_CompanyID from speakintelligentRM.tblAccount where AccountID = p_AccountID AND Status = 1 limit 1 ;

		ELSEIF p_AccountNo != '' THEN

			select AccountID , CompanyID INTO @v_AccountID, @v_CompanyID from speakintelligentRM.tblAccount where `Number` = p_AccountNo AND Status = 1 limit 1 ;

		ELSEIF p_AccountDynamicField != '' AND p_AccountDynamicFieldValue != '' THEN

			select distinct dfv.ParentID , df.CompanyID INTO @v_AccountID , @v_CompanyID  from speakintelligentRM.tblDynamicFields df

			inner join  speakintelligentRM.tblDynamicFieldsValue dfv on dfv.DynamicFieldsID = df.DynamicFieldsID

			where    df.`Type` = 'account' AND df.`Status` = 1 AND df.FieldSlug = p_AccountDynamicField AND dfv.FieldValue = p_AccountDynamicFieldValue;

		END IF;




		-- Check if account id found yet
		IF (@v_AccountID IS NULL OR @v_AccountID = 0 )
		THEN

			INSERT INTO tmp_Error_ (Message) VALUES ('AccountID not found');
			SELECT * FROM tmp_Error_;
			LEAVE PRC;

		END IF;



		-- Now find routing profile assigned to customer.

		-- First match on selection code
		select RoutingProfileId into @v_RoutingProfileID from tblRoutingProfile
		where CompanyID = @v_CompanyID AND p_DestinationNo like CONCAT(SelectionCode,"%") AND SelectionCode != ''
		order by length(SelectionCode) desc limit 1;



		IF FOUND_ROWS() = 0 THEN



		/*
			-- Nothing found. Match on Trunk -

			select RoutingProfileID into @v_RoutingProfileID
			from tblRoutingProfileToCustomer rpc
			inner join (

					select AccountID, TrunkID from speakintelligentRM.tblCustomerTrunk

					where CompanyID = @v_CompanyID AND UseInBilling = 1 AND `Prefix` != '' AND p_DestinationNo like CONCAT(`Prefix`,"%")

					order by length(`Prefix`) desc limit 1

			) ct on rpc.AccountID = ct.AccountID and rpc.TrunkID = ct.TrunkID

			limit 1;


		*/


			-- IF FOUND_ROWS() = 0 THEN

			-- Nothing found. Match on CLI -

			select RoutingProfileID into @v_RoutingProfileID
			from tblRoutingProfileToCustomer rpc
			inner join (

					select AccountID,  ServiceID from speakintelligentRM.tblCLIRateTable

					where CompanyID = @v_CompanyID AND  CLI = p_OriginationNo

					limit 1

			) ct on rpc.AccountID = ct.AccountID /* and rpc.TrunkID = ct.TrunkID */ and rpc.ServiceID = ct.ServiceID

			limit 1;

			-- END IF;

			IF FOUND_ROWS() = 0 THEN

					-- if still not found then find RoutingProfileID from AccountID  tblRoutingProfileToCustomer

					select RoutingProfileID into @v_RoutingProfileID
					from tblRoutingProfileToCustomer where AccountID = @v_AccountID
					AND TrunkID = 0 AND ServiceID = 0
					limit 1;

			END IF;


			IF FOUND_ROWS() = 0 THEN

					-- if still not found then find RoutingProfileID from reseller AccountID

					select RoutingProfileID into @v_RoutingProfileID
					from tblRoutingProfileToCustomer rpc
					inner join  speakintelligentRM.tblReseller rs on rs.AccountID = rpc.AccountID AND rs.ChildCompanyID = @v_CompanyID
					where rpc.TrunkID = 0 AND rpc.ServiceID = 0
					limit 1;

			END IF;


		END IF;




		-- @TODO: move to RoutingEngine
		-- find timezoneID
		insert into speakintelligentCDR.tblgetTimezone (connect_time,disconnect_time) values (p_DateAndTime  , p_DateAndTime);
		CALL speakintelligentBilling.prc_updateTempCDRTimeZones('tblgetTimezone');

		select TimezonesID , getTimezoneID into @v_TimezonesID , @v_getTimezoneID from speakintelligentCDR.tblgetTimezone where connect_time = p_DateAndTime AND disconnect_time = p_DateAndTime limit 1;

		delete from speakintelligentCDR.tblgetTimezone where getTimezoneID = @v_getTimezoneID;



		-- ############################################### ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ #################################################################################

		-- rounting records
		-- taken  from prc_getRoutingRecords

		-- now find vendor rates by
		-- @v_RoutingProfileID customer routing profile.
		-- @v_TimezonesID
		-- @v_AccountID


		-- customer currency ID
		select Code,CurrencyID  INTO @v_AccountCurrency_ , @v_AccountCurrencyID_
		from speakintelligentRM.tblCurrency
		where CurrencyId =  (SELECT CurrencyID FROM  `speakintelligentRM`.`tblAccount` WHERE AccountID = @v_AccountID);


		SET @v_RateTypeID = 1; -- voicecall/termination
		SET @v_Vendor = 2; -- Vendor
		SET @v_Active = 1;

		SET @rank:=0;
		SET @VendorConnectionName = '';

		IF @v_RoutingProfileID > 0 THEN


			-- check against p_OriginationNo AND p_DestinationNo

			SELECT
				@rank:=@rank+1 as Position,
				Prefix,
				VendorCLIPrefix,									VendorCLDPrefix,									TrunkPrefix,
				VendorID,									VendorName,									VendorConnectionName,									SipHeader,
				IP,									Port,									Username,									Password,
				AuthenticationMode,									@v_AccountCurrency_ as Currency,									ROUND(Rate,6) as Rate,
				RoutingCategoryOrder,									RoutingCategoryName
				from
				(	select
					@VendorConnectionName := vc.Name,
					concat(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) as Prefix,  -- CallPrefix = TrunkPrefix
					IFNULL(vc.CallPrefix ,"") as TrunkPrefix,
					IFNULL(rtr.OriginationCode ,"") as VendorCLIPrefix,
					rtr.DestinationCode as VendorCLDPrefix,
					vc.AccountId as VendorID,
					a.AccountName as VendorName,
					vc.Name as VendorConnectionName,										vc.SipHeader,										vc.IP,										vc.Port,										vc.Username,										vc.Password,										vc.AuthenticationMode,
					CASE WHEN IFNULL(rtr.RateCurrency,a.CurrencyID) = @v_AccountCurrencyID_
					THEN
						rtr.Rate
					ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = @v_AccountCurrencyID_ )
						* ( rtr.Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,a.CurrencyID) ))
					)
					END as Rate,
					-- rtr.Preference,
					rpc.Order as RoutingCategoryOrder,
					-- rp.RoutingProfileId as RoutingProfileID,
					rc.Name as RoutingCategoryName

					FROM
					tblRoutingProfile rp
					JOIN tblRoutingProfileCategory rpc ON rp.RoutingProfileID = rpc.RoutingProfileID and rp.RoutingProfileId = @v_RoutingProfileID -- customer routing profile id
					JOIN tblRateTableRate rtr ON rpc.RoutingCategoryID =  rtr.RoutingCategoryID
					JOIN tblRoutingCategory rc ON rpc.RoutingCategoryID =  rc.RoutingCategoryID
					JOIN tblRateTable rt ON  rtr.RateTableID = rt.RateTableId AND rt.CompanyId = rp.CompanyID
					JOIN tblVendorConnection vc ON rt.RateTableId = vc.RateTableID AND vc.CompanyId = rt.CompanyID
					JOIN tblAccount a ON vc.AccountId = a.AccountId AND a.CompanyId = vc.CompanyID
					WHERE
					 @VendorConnectionName <> vc.Name

					AND rp.Status = 1 AND vc.Active = 1 AND a.Status = 1 AND a.IsVendor = 1 -- AND a.CurrencyId IS NOT NULL
					AND vc.RateTypeID = @v_RateTypeID -- voicecall/termination

					and rt.Type = @v_RateTypeID -- voicecall/termination
					and rt.AppliedTo = @v_Vendor -- vendor

					AND rtr.EffectiveDate <= NOW()
					AND ( rtr.EndDate IS NULL OR  rtr.EndDate > NOW() )
					AND rtr.Blocked = 0

					-- @TODO Vendor wise timzeone in future
					and ( rtr.TimezonesID = @v_TimezonesID OR ( rtr.TimezonesID <> @v_TimezonesID and rtr.TimezonesID = 1) )

					and ( rtr.OriginationCode is null OR p_OriginationNo like  CONCAT(rtr.OriginationCode,"%") )
					and p_DestinationNo like  CONCAT(rtr.DestinationCode,"%")

					and (p_Location = '' OR vc.Location = p_Location)

					order by IFNULL(rtr.Preference ,5) desc, Rate asc , rpc.`Order`  desc , rtr.DestinationCode desc , vc.Name


				) vendorTable;


				-- check against p_DestinationNo
				IF FOUND_ROWS() = 0 THEN



					SELECT
						@rank:=@rank+1 as Position,
						Prefix,
						VendorCLIPrefix,									VendorCLDPrefix,									TrunkPrefix,
						VendorID,									VendorName,									VendorConnectionName,									SipHeader,
						IP,									Port,									Username,									Password,
						AuthenticationMode,									@v_AccountCurrency_ as Currency,									ROUND(Rate,6) as Rate,
						RoutingCategoryOrder,									RoutingCategoryName
						from
						(	select
							@VendorConnectionName := vc.Name,
							concat(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) as Prefix,  -- CallPrefix = TrunkPrefix
							IFNULL(vc.CallPrefix ,"") as TrunkPrefix,
							IFNULL(rtr.OriginationCode ,"") as VendorCLIPrefix,
							rtr.DestinationCode as VendorCLDPrefix,
							vc.AccountId as VendorID,
							a.AccountName as VendorName,
							vc.Name as VendorConnectionName,										vc.SipHeader,										vc.IP,										vc.Port,										vc.Username,										vc.Password,										vc.AuthenticationMode,
							CASE WHEN IFNULL(rtr.RateCurrency,a.CurrencyID) = @v_AccountCurrencyID_
							THEN
								rtr.Rate
							ELSE
							(
								-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = @v_AccountCurrencyID_ )
								* ( rtr.Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,a.CurrencyID) ))
							)
							END as Rate,
							-- rtr.Preference,
							rpc.Order as RoutingCategoryOrder,
							-- rp.RoutingProfileId as RoutingProfileID,
							rc.Name as RoutingCategoryName

							FROM
							tblRoutingProfile rp
							JOIN tblRoutingProfileCategory rpc ON rp.RoutingProfileID = rpc.RoutingProfileID and rp.RoutingProfileId = @v_RoutingProfileID -- customer routing profile id
							JOIN tblRateTableRate rtr ON rpc.RoutingCategoryID =  rtr.RoutingCategoryID
							JOIN tblRoutingCategory rc ON rpc.RoutingCategoryID =  rc.RoutingCategoryID
							JOIN tblRateTable rt ON  rtr.RateTableID = rt.RateTableId AND rt.CompanyId = rp.CompanyID
							JOIN tblVendorConnection vc ON rt.RateTableId = vc.RateTableID AND vc.CompanyId = rt.CompanyID
							JOIN tblAccount a ON vc.AccountId = a.AccountId AND a.CompanyId = vc.CompanyID
							WHERE
							 @VendorConnectionName <> vc.Name

							AND rp.Status = 1 AND vc.Active = 1 AND a.Status = 1 AND a.IsVendor = 1 -- AND a.CurrencyId IS NOT NULL
							AND vc.RateTypeID = @v_RateTypeID -- voicecall/termination

							and rt.Type = @v_RateTypeID -- voicecall/termination
							and rt.AppliedTo = @v_Vendor -- vendor

							AND rtr.EffectiveDate <= NOW()
							AND ( rtr.EndDate IS NULL OR  rtr.EndDate > NOW() )
							AND rtr.Blocked = 0

							and ( rtr.TimezonesID = @v_TimezonesID OR ( rtr.TimezonesID <> @v_TimezonesID and rtr.TimezonesID = 1) )

							-- and ( rtr.OriginationCode is null OR p_OriginationNo like  CONCAT(rtr.OriginationCode,"%") )
							and p_DestinationNo like  CONCAT(rtr.DestinationCode,"%")

							and (p_Location = '' OR vc.Location = p_Location)

							order by IFNULL(rtr.Preference ,5) desc, Rate asc , rpc.`Order`  desc , rtr.DestinationCode desc , vc.Name


						) vendorTable;



				END IF;






		ELSE


				-- check against p_OriginationNo AND p_DestinationNo without @v_RoutingProfileID

				SELECT
					@rank:=@rank+1 as Position,
					Prefix,
					VendorCLIPrefix,									VendorCLDPrefix,									TrunkPrefix,
					VendorID,									VendorName,									VendorConnectionName,									SipHeader,
					IP,									Port,									Username,									Password,
					AuthenticationMode,									@v_AccountCurrency_ as Currency,									ROUND(Rate,6) as Rate,
					RoutingCategoryOrder,									RoutingCategoryName
					from
					(	select
						@VendorConnectionName := vc.Name,
						concat(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) as Prefix,  -- CallPrefix = TrunkPrefix
						IFNULL(vc.CallPrefix ,"") as TrunkPrefix,
						IFNULL(rtr.OriginationCode ,"") as VendorCLIPrefix,
						rtr.DestinationCode as VendorCLDPrefix,
						vc.AccountId as VendorID,
						a.AccountName as VendorName,
						vc.Name as VendorConnectionName,										vc.SipHeader,										vc.IP,										vc.Port,										vc.Username,										vc.Password,										vc.AuthenticationMode,
						CASE WHEN IFNULL(rtr.RateCurrency,a.CurrencyID) = @v_AccountCurrencyID_
						THEN
							rtr.Rate
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
								(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = @v_AccountCurrencyID_ )
							* ( rtr.Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,a.CurrencyID) ))
						)
						END as Rate,
						-- rtr.Preference,
						rpc.Order as RoutingCategoryOrder,
						-- rp.RoutingProfileId as RoutingProfileID,
						rc.Name as RoutingCategoryName

						FROM
						tblRoutingProfile rp
						JOIN tblRoutingProfileCategory rpc ON rp.RoutingProfileID = rpc.RoutingProfileID -- and rp.RoutingProfileId = @v_RoutingProfileID -- customer routing profile id
						JOIN tblRateTableRate rtr ON rpc.RoutingCategoryID =  rtr.RoutingCategoryID
						JOIN tblRoutingCategory rc ON rpc.RoutingCategoryID =  rc.RoutingCategoryID
						JOIN tblRateTable rt ON  rtr.RateTableID = rt.RateTableId AND rt.CompanyId = rp.CompanyID
						JOIN tblVendorConnection vc ON rt.RateTableId = vc.RateTableID AND vc.CompanyId = rt.CompanyID
						JOIN tblAccount a ON vc.AccountId = a.AccountId AND a.CompanyId = vc.CompanyID
						WHERE
						 @VendorConnectionName <> vc.Name

						AND rp.Status = 1 AND vc.Active = 1 AND a.Status = 1 AND a.IsVendor = 1 -- AND a.CurrencyId IS NOT NULL
						AND vc.RateTypeID = @v_RateTypeID -- voicecall/termination

						and rt.Type = @v_RateTypeID -- voicecall/termination
						and rt.AppliedTo = @v_Vendor -- vendor

						AND rtr.EffectiveDate <= NOW()
						AND ( rtr.EndDate IS NULL OR  rtr.EndDate > NOW() )
						AND rtr.Blocked = 0

						and ( rtr.TimezonesID = @v_TimezonesID OR ( rtr.TimezonesID <> @v_TimezonesID and rtr.TimezonesID = 1) )

						and ( rtr.OriginationCode is null OR p_OriginationNo like  CONCAT(rtr.OriginationCode,"%") )
						and p_DestinationNo like  CONCAT(rtr.DestinationCode,"%")

						and (p_Location = '' OR vc.Location = p_Location)

						order by IFNULL(rtr.Preference ,5) desc, Rate asc , rpc.`Order`  desc , rtr.DestinationCode desc , vc.Name


					) vendorTable;


					-- check against p_DestinationNo
					IF FOUND_ROWS() = 0 THEN



						SELECT
							@rank:=@rank+1 as Position,
							Prefix,
							VendorCLIPrefix,									VendorCLDPrefix,									TrunkPrefix,
							VendorID,									VendorName,									VendorConnectionName,									SipHeader,
							IP,									Port,									Username,									Password,
							AuthenticationMode,									@v_AccountCurrency_ as Currency,									ROUND(Rate,6) as Rate,
							RoutingCategoryOrder,									RoutingCategoryName
							from
							(	select
								@VendorConnectionName := vc.Name,
								concat(IFNULL(vc.CallPrefix ,""),rtr.DestinationCode) as Prefix,  -- CallPrefix = TrunkPrefix
								IFNULL(vc.CallPrefix ,"") as TrunkPrefix,
								IFNULL(rtr.OriginationCode ,"") as VendorCLIPrefix,
								rtr.DestinationCode as VendorCLDPrefix,
								vc.AccountId as VendorID,
								a.AccountName as VendorName,
								vc.Name as VendorConnectionName,										vc.SipHeader,										vc.IP,										vc.Port,										vc.Username,										vc.Password,										vc.AuthenticationMode,
								CASE WHEN IFNULL(rtr.RateCurrency,a.CurrencyID) = @v_AccountCurrencyID_
								THEN
									rtr.Rate
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = @v_AccountCurrencyID_ )
									* ( rtr.Rate  / (Select Value from speakintelligentRM.tblCurrencyConversion where speakintelligentRM.tblCurrencyConversion.CurrencyId = IFNULL(rtr.RateCurrency,a.CurrencyID) ))
								)
								END as Rate,
								-- rtr.Preference,
								rpc.Order as RoutingCategoryOrder,
								-- rp.RoutingProfileId as RoutingProfileID,
								rc.Name as RoutingCategoryName

								FROM
								tblRoutingProfile rp
								JOIN tblRoutingProfileCategory rpc ON rp.RoutingProfileID = rpc.RoutingProfileID -- and rp.RoutingProfileId = @v_RoutingProfileID -- customer routing profile id
								JOIN tblRateTableRate rtr ON rpc.RoutingCategoryID =  rtr.RoutingCategoryID
								JOIN tblRoutingCategory rc ON rpc.RoutingCategoryID =  rc.RoutingCategoryID
								JOIN tblRateTable rt ON  rtr.RateTableID = rt.RateTableId AND rt.CompanyId = rp.CompanyID
								JOIN tblVendorConnection vc ON rt.RateTableId = vc.RateTableID AND vc.CompanyId = rt.CompanyID
								JOIN tblAccount a ON vc.AccountId = a.AccountId AND a.CompanyId = vc.CompanyID
								WHERE
								 @VendorConnectionName <> vc.Name

								AND rp.Status = 1 AND vc.Active = 1 AND a.Status = 1 AND a.IsVendor = 1 -- AND a.CurrencyId IS NOT NULL
								AND vc.RateTypeID = @v_RateTypeID -- voicecall/termination

								and rt.Type = @v_RateTypeID -- voicecall/termination
								and rt.AppliedTo = @v_Vendor -- vendor

								AND rtr.EffectiveDate <= NOW()
								AND ( rtr.EndDate IS NULL OR  rtr.EndDate > NOW() )
								AND rtr.Blocked = 0

								and ( rtr.TimezonesID = @v_TimezonesID OR ( rtr.TimezonesID <> @v_TimezonesID and rtr.TimezonesID = 1) )

								-- and ( rtr.OriginationCode is null OR p_OriginationNo like  CONCAT(rtr.OriginationCode,"%") )
								and p_DestinationNo like  CONCAT(rtr.DestinationCode,"%")

								and (p_Location = '' OR vc.Location = p_Location)

								order by IFNULL(rtr.Preference ,5) desc, Rate asc , rpc.`Order`  desc , rtr.DestinationCode desc , vc.Name


							) vendorTable;



					END IF;


		END IF;



		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
