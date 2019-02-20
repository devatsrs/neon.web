<?php
/**
 * Created by PhpStorm.
 * User: baselbers
 * Date: 26-10-2017
 * Time: 20:28
 */

namespace App\UblInvoice;


use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class AdditionalDocumentReference implements XmlSerializable {
	private $id;
	private $attachment;
	private $filename;
	private $documentType;

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return AdditionalDocumentReference
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAttachment() {
		return $this->attachment;
	}

	/**
	 * @param mixed $id
	 * @return AdditionalDocumentReference
	 */
	public function setAttachment($attachment) {
		$this->attachment = $attachment;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDocumentType() {
		return $this->attachment;
	}

	/**
	 * @param mixed $id
	 * @return AdditionalDocumentReference
	 */
	public function setDocumentType($type) {
		$this->documentType = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @param mixed $id
	 * @return AdditionalDocumentReference
	 */
	public function setFilename($filename) {
		$this->filename= $filename;
		return $this;
	}

	function xmlSerialize(Writer $writer) {
		$writer->write([
			Schema::CBC.'ID' => $this->id,
			Schema::CBC.'DocumentType' => $this->documentType,
			Schema::CAC.'Attachment' =>[
				Schema::CAC.'ExternalReference' =>[
					['name' => Schema::CBC . 'URI', 'value' => $this->attachment]
				]
			]
		]);
	}
}