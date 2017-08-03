Use Ratemanagement3;

DELIMITER //
CREATE PROCEDURE `prc_getTrunkByMaxMatch`(
	IN `p_CompanyID` INT,
	IN `p_Trunk` VARCHAR(200)
)
BEGIN



	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	DROP TEMPORARY TABLE IF EXISTS tmp_all_trunk_;
   CREATE TEMPORARY TABLE tmp_all_trunk_ (
     RowTrunk  varchar(50),
     TrunkID INT,
     Trunk  varchar(50),
     RowNo int
   );



 insert into tmp_all_trunk_
  SELECT  distinct t.Trunk , t.TrunkID , RIGHT(t.Trunk, x.RowNo) as loopCode , RowNo
			 FROM (
		          SELECT @RowNo  := @RowNo + 1 as RowNo
		          FROM mysql.help_category
		          ,(SELECT @RowNo := 0 ) x
		          limit 20
          ) x
          INNER JOIN tblTrunk AS t
          ON t.CompanyId = p_CompanyID and x.RowNo   <= LENGTH(t.Trunk)
          and ( p_Trunk like concat('%' , t.Trunk ) )
          order by LENGTH(t.Trunk) desc , RowNo desc ;

	-- select TrunkID from tmp_all_trunk_ order by RowNo desc limit 1;

	select TrunkID , @p_Trunk  as Trunk  from tmp_all_trunk_ where ( @p_Trunk like concat('%' , Trunk ) ) order by  RowNo desc limit 1;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END//
DELIMITER ;
