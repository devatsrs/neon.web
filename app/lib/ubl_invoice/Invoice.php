<?php
/**
 * Created by PhpStorm.
 * User: bram.vaneijk
 * Date: 13-10-2016
 * Time: 16:29
 */

namespace App\UblInvoice;


use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use XeroPHP\Models\Accounting\Currency;

class Invoice implements XmlSerializable{
    private $UBLVersionID = '2.1';

    /**
     * @var int
     */
    private $id;
    /**
     * @var bool
     */
    private $copyIndicator = false;

    /**
     * @var \DateTime
     */
    private $issueDate;

    /**
     * @var \DateTime
     */
    private $dueDate;

    /**
     * @var \DateTime
     */
    private $startDate;
    /**
     * @var \DateTime
     */
    private $endDate;
    /**
     * @var string
     */

    private $invoiceTypeCode;

    /**
     * @var AdditionalDocumentReference
     */
    private $additionalDocumentReference;

    /**
     * @var Party
     */
    private $accountingSupplierParty;
    /**
     * @var Party
     */
    private $accountingCustomerParty;
    /**
     * @var TaxTotal
     */
    private $taxTotal;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $note;

    /**
     * @var string
     */
    private $terms;

    /**
     * @var LegalMonetaryTotal
     */
    private $legalMonetaryTotal;
    /**
     * @var InvoiceLine[]
     */
    private $invoiceLines;
    /**
     * @var AllowanceCharge[]
     */
    private $allowanceCharges;


    function validate()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('Missing invoice id');
        }

        if ($this->id === null) {
            throw new \InvalidArgumentException('Missing invoice id');
        }

        if (!$this->issueDate instanceof \DateTime) {
            throw new \InvalidArgumentException('Invalid invoice issueDate');
        }

        if ($this->invoiceTypeCode === null) {
            throw new \InvalidArgumentException('Missing invoice invoiceTypeCode');
        }

        if ($this->note === null) {
            throw new \InvalidArgumentException('Missing invoice note');
        }

        if ($this->accountingSupplierParty === null) {
            throw new \InvalidArgumentException('Missing invoice accountingSupplierParty');
        }

        if ($this->accountingCustomerParty === null) {
            throw new \InvalidArgumentException('Missing invoice accountingCustomerParty');
        }

        if ($this->invoiceLines === null) {
            throw new \InvalidArgumentException('Missing invoice lines');
        }

        if ($this->currencyCode === null) {
            throw new \InvalidArgumentException('Missing invoice currency code.');
        }

        if ($this->legalMonetaryTotal === null) {
            throw new \InvalidArgumentException('Missing invoice LegalMonetaryTotal');
        }
    }

    function xmlSerialize(Writer $writer)
    {
        $cbc = '{urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2}';
        $cac = '{urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2}';

        $this->validate();
        $res = [
            $cbc . 'UBLVersionID' => $this->UBLVersionID,
            //$cbc . 'CustomizationID' => 'OIOUBL-2.01',
            $cbc . 'ID' => $this->id,
            //$cbc . 'CopyIndicator' => $this->copyIndicator ? 'true' : 'false',
            $cbc . 'IssueDate' => $this->issueDate->format('Y-m-d'),
            $cbc . 'InvoiceTypeCode' => $this->invoiceTypeCode,
            $cbc . 'Note' => $this->note,
            $cbc . 'DocumentCurrencyCode' => $this->currencyCode,
            $cac . 'AdditionalDocumentReference' => $this->additionalDocumentReference,
            $cac . 'AccountingSupplierParty' => [$cac . "Party" => $this->accountingSupplierParty],
            $cac . 'AccountingCustomerParty' => [$cac . "Party" => $this->accountingCustomerParty],
        ];


        if ($this->startDate != null && $this->endDate != null) {
            $dates = [
                $cac . 'InvoicePeriod' => [
                    $cbc . "StartDate" => $this->startDate->format('Y-m-d'),
                    $cbc . "EndDate" => $this->endDate->format('Y-m-d')
                ]
            ];
            $res = array_slice($res, 0, 6, true) + $dates + array_slice($res, 6, count($res) - 1, true) ;
        }

        if ($this->dueDate != null) {
            $dates = [$cbc . 'DueDate' => $this->dueDate->format('Y-m-d')];
            $res = array_slice($res, 0, 3, true) + $dates + array_slice($res, 3, count($res) - 1, true) ;
        }

        $writer->write($res);
        if ($this->allowanceCharges != null) {
            foreach ($this->allowanceCharges as $invoiceLine) {
                $writer->write([
                    Schema::CAC . 'AllowanceCharge' => $invoiceLine
                ]);
            }
        }


        if ($this->terms != null) {
            $writer->write([
                $cac . 'PaymentTerms' => [
                    $cbc . "Note" => $this->terms
                ]
            ]);
        }

        if ($this->taxTotal != null) {
            $writer->write([
                Schema::CAC . 'TaxTotal' => $this->taxTotal
            ]);
        }

        $writer->write([
            $cac . 'LegalMonetaryTotal' => $this->legalMonetaryTotal
        ]);

        foreach ($this->invoiceLines as $invoiceLine) {
            $writer->write([
                Schema::CAC . 'InvoiceLine' => $invoiceLine
            ]);
        }

    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Invoice
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCopyIndicator() {
        return $this->copyIndicator;
    }

    /**
     * @param boolean $copyIndicator
     * @return Invoice
     */
    public function setCopyIndicator($copyIndicator) {
        $this->copyIndicator = $copyIndicator;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIssueDate() {
        return $this->issueDate;
    }

    /**
     * @param \DateTime $issueDate
     * @return Invoice
     */
    public function setIssueDate($issueDate) {
        $this->issueDate = $issueDate;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getDueDate() {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     * @return Invoice
     */
    public function setDueDate($dueDate) {
        $this->dueDate = $dueDate;
        return $this;
    }


    /**
     * @return String
     */
    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    /**
     * @param String $currencyCode
     * @return Invoice
     */
    public function setCurrencyCode($currencyCode) {
        $this->currencyCode = $currencyCode;
        return $this;
    }


    /**
     * @return String
     */
    public function getNote() {
        return $this->note;
    }

    /**
     * @param String $note
     * @return Invoice
     */
    public function setNote($note) {
        $this->note = $note;
        return $this;
    }


    /**
     * @return String
     */
    public function getTerms() {
        return $this->terms;
    }

    /**
     * @param String $terms
     * @return Invoice
     */
    public function setTerms($terms) {
        $this->terms = $terms;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return Invoice
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return Invoice
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceTypeCode() {
        return $this->invoiceTypeCode;
    }

    /**
     * @param string $invoiceTypeCode
     * @return Invoice
     */
    public function setInvoiceTypeCode($invoiceTypeCode) {
        $this->invoiceTypeCode = $invoiceTypeCode;
        return $this;
    }

    /**
     * @return AdditionalDocumentReference
     */
    public function getAdditionalDocumentReference() {
        return $this->additionalDocumentReference;
    }

    /**
     * @param AdditionalDocumentReference $additionalDocumentReference
     * @return Invoice
     */
    public function setAdditionalDocumentReference($additionalDocumentReference) {
        $this->additionalDocumentReference = $additionalDocumentReference;
        return $this;
    }

    /**
     * @return Party
     */
    public function getAccountingSupplierParty() {
        return $this->accountingSupplierParty;
    }

    /**
     * @param Party $accountingSupplierParty
     * @return Invoice
     */
    public function setAccountingSupplierParty($accountingSupplierParty) {
        $this->accountingSupplierParty = $accountingSupplierParty;
        return $this;
    }

    /**
     * @return Party
     */
    public function getAccountingCustomerParty() {
        return $this->accountingCustomerParty;
    }

    /**
     * @param Party $accountingCustomerParty
     * @return Invoice
     */
    public function setAccountingCustomerParty($accountingCustomerParty) {
        $this->accountingCustomerParty = $accountingCustomerParty;
        return $this;
    }

    /**
     * @return TaxTotal
     */
    public function getTaxTotal() {
        return $this->taxTotal;
    }

    /**
     * @param TaxTotal $taxTotal
     * @return Invoice
     */
    public function setTaxTotal($taxTotal) {
        $this->taxTotal = $taxTotal;
        return $this;
    }

    /**
     * @return LegalMonetaryTotal
     */
    public function getLegalMonetaryTotal() {
        return $this->legalMonetaryTotal;
    }

    /**
     * @param LegalMonetaryTotal $legalMonetaryTotal
     * @return Invoice
     */
    public function setLegalMonetaryTotal($legalMonetaryTotal) {
        $this->legalMonetaryTotal = $legalMonetaryTotal;
        return $this;
    }

    /**
     * @return InvoiceLine[]
     */
    public function getInvoiceLines() {
        return $this->invoiceLines;
    }

    /**
     * @param InvoiceLine[] $invoiceLines
     * @return Invoice
     */
    public function setInvoiceLines($invoiceLines) {
        $this->invoiceLines = $invoiceLines;
        return $this;
    }

    /**
     * @return AllowanceCharge[]
     */
    public function getAllowanceCharges() {
        return $this->allowanceCharges;
    }

    /**
     * @param AllowanceCharge[] $allowanceCharges
     * @return Invoice
     */
    public function setAllowanceCharges($allowanceCharges) {
        $this->allowanceCharges = $allowanceCharges;
        return $this;
    }

}
