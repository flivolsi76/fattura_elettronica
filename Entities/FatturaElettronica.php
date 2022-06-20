<?php

/**
 * Class FatturaElettronica
 */
class FatturaElettronica extends BaseEntity {

	private $FatturaElettronicaHeader;
	private $FatturaElettronicaBody;

	/**
	 * FatturaElettronica constructor.
	 *
	 * @param FatturaElettronicaHeader $FatturaElettronicaHeader
	 * @param FatturaElettronicaBody $FatturaElettronicaBody
	 */
	public function __construct(
		FatturaElettronicaHeader $FatturaElettronicaHeader,
		FatturaElettronicaBody $FatturaElettronicaBody
	) {
		$this->FatturaElettronicaHeader = $FatturaElettronicaHeader;
		$this->FatturaElettronicaBody = $FatturaElettronicaBody;
	}

	/**
	 * @return string
	 */
	public function getXML() {
		$xmlHeader = '<?xml version="1.0" encoding="UTF-8"?><p:FatturaElettronica versione="FPR12" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" 
xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2 http://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/Schema_del_file_xml_FatturaPA_versione_1.2.xsd">%s</p:FatturaElettronica>';
		/*
		 * <p:FatturaElettronica versione="FPR12"
                              xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                              xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2"
                              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
		 */
		$xmlContent = $this->FatturaElettronicaHeader->getXML();
		$xmlContent .= $this->FatturaElettronicaBody->getXML();

		return sprintf( $xmlHeader, $xmlContent );
	}

	/**
	 * @return FatturaElettronicaHeader
	 */
	public function getFatturaElettronicaHeader() {
		return $this->FatturaElettronicaHeader;
	}

	/**
	 * @param FatturaElettronicaHeader $FatturaElettronicaHeader
	 */
	public function setFatturaElettronicaHeader( $FatturaElettronicaHeader ) {
		$this->FatturaElettronicaHeader = $FatturaElettronicaHeader;
	}

	/**
	 * @return FatturaElettronicaBody
	 */
	public function getFatturaElettronicaBody() {
		return $this->FatturaElettronicaBody;
	}

	/**
	 * @param FatturaElettronicaBody $FatturaElettronicaBody
	 */
	public function setFatturaElettronicaBody( $FatturaElettronicaBody ) {
		$this->FatturaElettronicaBody = $FatturaElettronicaBody;
	}


}