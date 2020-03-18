<?php
/**
 * Created by PhpStorm.
 * User: Vasim
 * Date: 05/03/2020
 * Time: 04:30 PM
 */

class LegacyRateImport {

    // access components
    public static $access_components = [
        'OneOffCost',
        'MonthlyCost',
        'CostPerCall',
        'CostPerMinute',
        'SurchargePerCall',
        'SurchargePerMinute',
        'OutpaymentPerCall',
        'OutpaymentPerMinute',
        'Surcharges',
        'Chargeback',
        'CollectionCostAmount',
        'CollectionCostPercentage',
        'RegistrationCostPerNumber'
    ];

    // package components
    public static $package_components = [
        'OneOffCost',
        'MonthlyCost',
        'PackageCostPerMinute',
        'RecordingCostPerMinute'
    ];

    public static function getAccessSpecialRateComparison($CLIRateTableID) {

        // get Access Default RateTableID and SpecialRateTableID
        $RateTableIDs_q = "SELECT RateTableID, SpecialRateTableID FROM tblCLIRateTable cli WHERE cli.CLIRateTableID=$CLIRateTableID";
        $RateTableIDs = DB::select($RateTableIDs_q);

        // generate access unique key to loop through (how many rates can be combined default and special)
        $q = "SELECT
                    CONCAT(rtr.RateID,'_',rtr.OriginationRateID,'_',rtr.AccessType,'_',rtr.City,'_',rtr.Tariff,'_',rtr.TimezonesID) AS `Key`
                FROM
                    tblRateTableDIDRate rtr
                INNER JOIN
                    tblRate r ON r.RateID = rtr.RateID
                INNER JOIN
                    tblCLIRateTable cli ON rtr.RateTableId = cli.SpecialRateTableID
                    AND cli.Prefix = r.CODE AND cli.NoType = rtr.AccessType
                    AND cli.City = rtr.City AND cli.Tariff = rtr.Tariff
                WHERE
                    cli.CLIRateTableID=$CLIRateTableID

                UNION

                SELECT
                    CONCAT(rtr.RateID,'_',rtr.OriginationRateID,'_',rtr.AccessType,'_',rtr.City,'_',rtr.Tariff,'_',rtr.TimezonesID) AS `Key`
                FROM
                    tblRateTableDIDRate rtr
                INNER JOIN
                    tblRate r ON r.RateID = rtr.RateID
                INNER JOIN
                    tblCLIRateTable cli ON rtr.RateTableId = cli.RateTableID
                    AND cli.Prefix = r.CODE AND cli.NoType = rtr.AccessType
                    AND cli.City = rtr.City AND cli.Tariff = rtr.Tariff
                WHERE
                    cli.CLIRateTableID=$CLIRateTableID
        ";
        $rows = DB::select($q);

        // get access default rates
        $DefaultRates_q = "SELECT
                                CONCAT(rtr.RateID,'_',rtr.OriginationRateID,'_',rtr.AccessType,'_',rtr.City,'_',rtr.Tariff,'_',rtr.TimezonesID) AS `Key`,
                                t.Title AS `TimezoneTitle`,IFNULL(o_r.Code,'') AS `Origination`, cli.Prefix,
                                rtr.*
                            FROM
                                tblRateTableDIDRate rtr
                            INNER JOIN
                                tblRate r ON r.RateID = rtr.RateID
                            LEFT JOIN
                                tblRate o_r ON o_r.RateID = rtr.OriginationRateID
                            INNER JOIN
                                tblTimezones t ON t.TimezonesID = rtr.TimezonesID
                            INNER JOIN
                                tblCLIRateTable cli ON rtr.RateTableId = cli.RateTableID
                                AND cli.Prefix = r.CODE AND cli.NoType = rtr.AccessType
                                AND cli.City = rtr.City AND cli.Tariff = rtr.Tariff
                            WHERE
                                cli.CLIRateTableID=$CLIRateTableID
        ";
        $DefaultRates = DB::select($DefaultRates_q);
        $DefaultRates = array_map(function ($value) {
            return (array)$value;
        }, $DefaultRates);

        // get access special rates
        $SpecialRates_q = "SELECT
                                CONCAT(rtr.RateID,'_',rtr.OriginationRateID,'_',rtr.AccessType,'_',rtr.City,'_',rtr.Tariff,'_',rtr.TimezonesID) AS `Key`,
                                t.Title AS `TimezoneTitle`,IFNULL(o_r.Code,'') AS `Origination`, cli.Prefix,
                                rtr.*
                            FROM
                                tblRateTableDIDRate rtr
                            INNER JOIN
                                tblRate r ON r.RateID = rtr.RateID
                            LEFT JOIN
                                tblRate o_r ON o_r.RateID = rtr.OriginationRateID
                            INNER JOIN
                                tblTimezones t ON t.TimezonesID = rtr.TimezonesID
                            INNER JOIN
                                tblCLIRateTable cli ON rtr.RateTableId = cli.SpecialRateTableID
                                AND cli.Prefix = r.CODE AND cli.NoType = rtr.AccessType
                                AND cli.City = rtr.City AND cli.Tariff = rtr.Tariff
                            WHERE
                                cli.CLIRateTableID=$CLIRateTableID
        ";
        $SpecialRates = DB::select($SpecialRates_q);
        $SpecialRates = array_map(function ($value) {
            return (array)$value;
        }, $SpecialRates);

        $final_data = [];
        // loop through all the keys generated above (generate access unique key to loop through (how many rates can be combined default and special))
        foreach ($rows as $row) {
            $current_row = [];
            $key = $row->Key; // unique access rate key

            // check if key exist in default access rates then return index(key) from $DefaultRates
            $DefaultRow = array_search($key, array_column($DefaultRates, 'Key'));
            // check if key exist in special access rates then return index(key) from $SpecialRates
            $SpecialRow = array_search($key, array_column($SpecialRates, 'Key'));

            // to get common data which both can have but if anyone has it or both has it,
            // we will take from default or special whichever has the rates
            $CurrentRowData = $SpecialRow !== false ? $SpecialRates[$SpecialRow] : $DefaultRates[$DefaultRow];

            $current_row['RateTableDIDRateID']  = $CurrentRowData['RateTableDIDRateID'];
            $current_row['OriginationRateID']   = $CurrentRowData['OriginationRateID'];
            $current_row['RateID']              = $CurrentRowData['RateID'];
            $current_row['TimezonesID']         = $CurrentRowData['TimezonesID'];
            $current_row['TimezoneTitle']       = $CurrentRowData['TimezoneTitle'];
            $current_row['Origination']         = $CurrentRowData['Origination'];
            $current_row['Prefix']              = $CurrentRowData['Prefix'];
            $current_row['City']                = $CurrentRowData['City'];
            $current_row['Tariff']              = $CurrentRowData['Tariff'];
            $current_row['AccessType']          = $CurrentRowData['AccessType'];
            $current_row['Key']                 = $CurrentRowData['Key'];
            $current_row['DefaultRateTableId']  = $RateTableIDs[0]->RateTableID;
            $current_row['SpecialRateTableId']  = $RateTableIDs[0]->SpecialRateTableID;

            // rows will be created by components so, loop through all the components in every rate entry and create row array
            foreach (self::$access_components as $component) {
                $current_row_component['component']     = $component;
                $current_row_component['DefaultCost']   = $DefaultRow !== false ? $DefaultRates[$DefaultRow][$component] : '';
                $current_row_component['SpecialCost']   = $SpecialRow !== false ? $SpecialRates[$SpecialRow][$component] : '';
                $current_row_component['DefaultCost']   = $current_row_component['DefaultCost'] != null ? $current_row_component['DefaultCost'] : '';
                $current_row_component['SpecialCost']   = $current_row_component['SpecialCost'] != null ? $current_row_component['SpecialCost'] : '';

                $current_row_component['NewPrice']      = '';

                $final_data[] = $current_row + $current_row_component;
            }
        }
        return $final_data;
    }

    public static function getPackageSpecialRateComparison($CLIRateTableID) {

        // get Default RateTableID and SpecialRateTableID
        $RateTableIDs_q = "SELECT asp.RateTableID, asp.SpecialPackageRateTableID FROM tblCLIRateTable cli JOIN tblAccountServicePackage asp ON asp.AccountServicePackageID = cli.AccountServicePackageID WHERE cli.CLIRateTableID=$CLIRateTableID";
        $RateTableIDs = DB::select($RateTableIDs_q);

        // generate unique key to loop through (how many rates can be combined default and special)
        $q = "SELECT
                    CONCAT(rtr.RateID,'_',rtr.TimezonesID) AS `Key`
                FROM
                    tblRateTablePKGRate rtr
                INNER JOIN
                    tblRate r ON r.RateID = rtr.RateID
                INNER JOIN
                    tblAccountServicePackage asp ON rtr.RateTableId = asp.SpecialPackageRateTableID
                INNER JOIN
                    tblCLIRateTable cli ON cli.AccountServicePackageID = asp.AccountServicePackageID
                INNER JOIN
                    tblPackage p ON p.PackageId = asp.PackageId AND p.Name = r.Code
                WHERE
                    cli.CLIRateTableID=$CLIRateTableID

                UNION

                SELECT
                    CONCAT(rtr.RateID,'_',rtr.TimezonesID) AS `Key`
                FROM
                    tblRateTablePKGRate rtr
                INNER JOIN
                    tblRate r ON r.RateID = rtr.RateID
                INNER JOIN
                    tblAccountServicePackage asp ON rtr.RateTableId = asp.RateTableID
                INNER JOIN
                    tblCLIRateTable cli ON cli.AccountServicePackageID = asp.AccountServicePackageID
                INNER JOIN
                    tblPackage p ON p.PackageId = asp.PackageId AND p.Name = r.Code
                WHERE
                    cli.CLIRateTableID=$CLIRateTableID
        ";
        $rows = DB::select($q);

        // get access default rates
        $DefaultRates_q = "SELECT
                                CONCAT(rtr.RateID,'_',rtr.TimezonesID) AS `Key`,
                                t.Title AS `TimezoneTitle`, p.Name AS PackageName,
                                rtr.*
                            FROM
                                tblRateTablePKGRate rtr
                            INNER JOIN
                                tblRate r ON r.RateID = rtr.RateID
                            INNER JOIN
                                tblTimezones t ON t.TimezonesID = rtr.TimezonesID
                            INNER JOIN
                                tblAccountServicePackage asp ON rtr.RateTableId = asp.RateTableID
                            INNER JOIN
                                tblCLIRateTable cli ON cli.AccountServicePackageID = asp.AccountServicePackageID
                            INNER JOIN
                                tblPackage p ON p.PackageId = asp.PackageId AND p.Name = r.Code
                            WHERE
                                cli.CLIRateTableID=$CLIRateTableID
        ";
        $DefaultRates = DB::select($DefaultRates_q);
        $DefaultRates = array_map(function ($value) {
            return (array)$value;
        }, $DefaultRates);

        // get access special rates
        $SpecialRates_q = "SELECT
                                CONCAT(rtr.RateID,'_',rtr.TimezonesID) AS `Key`,
                                t.Title AS `TimezoneTitle`, p.Name AS PackageName,
                                rtr.*
                            FROM
                                tblRateTablePKGRate rtr
                            INNER JOIN
                                tblRate r ON r.RateID = rtr.RateID
                            INNER JOIN
                                tblTimezones t ON t.TimezonesID = rtr.TimezonesID
                            INNER JOIN
                                tblAccountServicePackage asp ON rtr.RateTableId = asp.SpecialPackageRateTableID
                            INNER JOIN
                                tblCLIRateTable cli ON cli.AccountServicePackageID = asp.AccountServicePackageID
                            INNER JOIN
                                tblPackage p ON p.PackageId = asp.PackageId AND p.Name = r.Code
                            WHERE
                                cli.CLIRateTableID=$CLIRateTableID
        ";
        $SpecialRates = DB::select($SpecialRates_q);
        $SpecialRates = array_map(function ($value) {
            return (array)$value;
        }, $SpecialRates);

        $final_data = [];
        // loop through all the keys generated above (generate unique key to loop through (how many rates can be combined default and special))
        foreach ($rows as $row) {
            $current_row = [];
            $key = $row->Key; // unique rate key

            // check if key exist in default rates then return index(key) from $DefaultRates
            $DefaultRow = array_search($key, array_column($DefaultRates, 'Key'));
            // check if key exist in special rates then return index(key) from $SpecialRates
            $SpecialRow = array_search($key, array_column($SpecialRates, 'Key'));

            // to get common data which both can have but if anyone has it or both has it,
            // we will take from default or special whichever has the rates
            $CurrentRowData = $SpecialRow !== false ? $SpecialRates[$SpecialRow] : $DefaultRates[$DefaultRow];

            $current_row['RateTablePKGRateID']  = $CurrentRowData['RateTablePKGRateID'];
            $current_row['RateID']              = $CurrentRowData['RateID'];
            $current_row['TimezonesID']         = $CurrentRowData['TimezonesID'];
            $current_row['TimezoneTitle']       = $CurrentRowData['TimezoneTitle'];
            $current_row['PackageName']         = $CurrentRowData['PackageName'];
            $current_row['Key']                 = $CurrentRowData['Key'];
            $current_row['DefaultRateTableId']  = $RateTableIDs[0]->RateTableID;
            $current_row['SpecialRateTableId']  = $RateTableIDs[0]->SpecialPackageRateTableID;

            // rows will be created by components so, loop through all the components in every rate entry and create row array
            foreach (self::$package_components as $component) {
                $current_row_component['component']     = $component;
                $current_row_component['DefaultCost']   = $DefaultRow !== false ? $DefaultRates[$DefaultRow][$component] : '';
                $current_row_component['SpecialCost']   = $SpecialRow !== false ? $SpecialRates[$SpecialRow][$component] : '';
                $current_row_component['DefaultCost']   = $current_row_component['DefaultCost'] != null ? $current_row_component['DefaultCost'] : '';
                $current_row_component['SpecialCost']   = $current_row_component['SpecialCost'] != null ? $current_row_component['SpecialCost'] : '';

                $current_row_component['NewPrice']      = '';

                $final_data[] = $current_row + $current_row_component;
            }
        }
        return $final_data;
    }

    public static function getTerminationSpecialRateComparison($CLIRateTableID) {

        // get Default RateTableID and SpecialRateTableID
        $RateTableIDs_q = "SELECT TerminationRateTableID, SpecialTerminationRateTableID FROM tblCLIRateTable cli WHERE cli.CLIRateTableID=$CLIRateTableID";
        $RateTableIDs = DB::select($RateTableIDs_q);

        // generate unique key to loop through (how many rates can be combined default and special)
        $q = "SELECT
                    CONCAT(c.EUCountry,'_',r.CountryID,'_',IFNULL(r.`Type`,'')) AS `Key`
                FROM
                    tblRateTableRate rtr
                INNER JOIN
                    tblRate r ON r.RateID = rtr.RateID
                INNER JOIN
                    tblCountry c ON c.CountryID = r.CountryID
                INNER JOIN
                    tblCLIRateTable cli ON cli.SpecialTerminationRateTableID = rtr.RateTableId
                WHERE
                    cli.CLIRateTableID=$CLIRateTableID
                GROUP BY
                    c.EUCountry,r.CountryID,r.`Type`,rtr.Rate

                UNION

                SELECT
                    CONCAT(c.EUCountry,'_',r.CountryID,'_',IFNULL(r.`Type`,'')) AS `Key`
                FROM
                    tblRateTableRate rtr
                INNER JOIN
                    tblRate r ON r.RateID = rtr.RateID
                INNER JOIN
                    tblCountry c ON c.CountryID = r.CountryID
                INNER JOIN
                    tblCLIRateTable cli ON cli.TerminationRateTableID = rtr.RateTableId
                WHERE
                    cli.CLIRateTableID=$CLIRateTableID
                GROUP BY
                    c.EUCountry,r.CountryID,r.`Type`,rtr.Rate
        ";
        $rows = DB::select($q);

        // get access default rates
        $DefaultRates_q = "SELECT
                                CONCAT(c.EUCountry,'_',r.CountryID,'_',IFNULL(r.`Type`,'')) AS `Key`,
                                t.Title AS `TimezoneTitle`,r.CountryID,
                                c.EUCountry, MAX(c.Country) AS Country, r.`Type`, t.TimezonesID,MAX(rtr.Rate) AS Rate
                            FROM
                                tblRateTableRate rtr
                            INNER JOIN
                                tblRate r ON r.RateID = rtr.RateID
                            INNER JOIN
                                tblTimezones t ON t.TimezonesID = rtr.TimezonesID
                            INNER JOIN
                                tblCountry c ON c.CountryID = r.CountryID
                            INNER JOIN
                                tblCLIRateTable cli ON cli.TerminationRateTableID = rtr.RateTableId
                            WHERE
                                cli.CLIRateTableID=$CLIRateTableID
                            GROUP BY
                                c.EUCountry,r.CountryID,r.`Type`,rtr.Rate
        ";
        $DefaultRates = DB::select($DefaultRates_q);
        $DefaultRates = array_map(function ($value) {
            return (array)$value;
        }, $DefaultRates);

        // get access special rates
        $SpecialRates_q = "SELECT
                                CONCAT(c.EUCountry,'_',r.CountryID,'_',IFNULL(r.`Type`,'')) AS `Key`,
                                t.Title AS `TimezoneTitle`,r.CountryID,
                                c.EUCountry, MAX(c.Country) AS Country, r.`Type`, t.TimezonesID,MAX(rtr.Rate) AS Rate
                            FROM
                                tblRateTableRate rtr
                            INNER JOIN
                                tblRate r ON r.RateID = rtr.RateID
                            INNER JOIN
                                tblTimezones t ON t.TimezonesID = rtr.TimezonesID
                            INNER JOIN
                                tblCountry c ON c.CountryID = r.CountryID
                            INNER JOIN
                                tblCLIRateTable cli ON cli.SpecialTerminationRateTableID = rtr.RateTableId
                            WHERE
                                cli.CLIRateTableID=$CLIRateTableID
                            GROUP BY
                                c.EUCountry,r.CountryID,r.`Type`,rtr.Rate
        ";
        $SpecialRates = DB::select($SpecialRates_q);
        $SpecialRates = array_map(function ($value) {
            return (array)$value;
        }, $SpecialRates);

        $final_data = [];
        // loop through all the keys generated above (generate unique key to loop through (how many rates can be combined default and special))
        foreach ($rows as $row) {
            $current_row = [];
            $key = $row->Key; // unique rate key

            // check if key exist in default rates then return index(key) from $DefaultRates
            $DefaultRow = array_search($key, array_column($DefaultRates, 'Key'));
            // check if key exist in special rates then return index(key) from $SpecialRates
            $SpecialRow = array_search($key, array_column($SpecialRates, 'Key'));

            // to get common data which both can have but if anyone has it or both has it,
            // we will take from default or special whichever has the rates
            $CurrentRowData = $SpecialRow !== false ? $SpecialRates[$SpecialRow] : $DefaultRates[$DefaultRow];

            $current_row['EUCountry']           = $CurrentRowData['EUCountry'];
            $current_row['CountryID']           = $CurrentRowData['CountryID'];
            $current_row['Country']             = $CurrentRowData['Country'];
            $current_row['Type']                = $CurrentRowData['Type'] != null ? $CurrentRowData['Type'] : '';
            $current_row['TimezonesID']         = $CurrentRowData['TimezonesID'];
            $current_row['TimezoneTitle']       = $CurrentRowData['TimezoneTitle'];
            $current_row['Key']                 = $CurrentRowData['Key'];
            $current_row['component']           = 'Rate';
            $current_row['DefaultRateTableId']  = $RateTableIDs[0]->TerminationRateTableID;
            $current_row['SpecialRateTableId']  = $RateTableIDs[0]->SpecialTerminationRateTableID;

            $component                          = 'Rate';
            $current_row['DefaultCost']         = $DefaultRow !== false ? $DefaultRates[$DefaultRow][$component] : '';
            $current_row['SpecialCost']         = $SpecialRow !== false ? $SpecialRates[$SpecialRow][$component] : '';
            $current_row['DefaultCost']         = $current_row['DefaultCost'] != null ? $current_row['DefaultCost'] : '';
            $current_row['SpecialCost']         = $current_row['SpecialCost'] != null ? $current_row['SpecialCost'] : '';

            $current_row['NewPrice']            = '';

            $final_data[] = $current_row;
        }

        //$manual_response = '{"sEcho":1,"iTotalRecords":'.count($response_data).',"iTotalDisplayRecords":'.count($response_data).',"aaData":'.json_encode($response_data).',"sColumns":["value","name"],"Total":{"totalcount":'.count($response_data).'}}';
        return $final_data;
    }
}