<?php

/**
 * Class DatiGenerali
 */
class DatiGenerali extends BaseEntity {

	//Tipo Documento
    const FATTURA = "TD01";
	const NOTA_DI_CREDITO = "TD04";

	private $TipoDocumento;
	private $Divisa;
	private $Data;
	private $Numero;
	private $ImportoTotaleDocumento;
	private $ScontoMaggiorazione;

	/**
	 * DatiGenerali constructor.
	 *
	 * @param $TipoDocumento
	 * @param $Divisa
	 * @param $Data
	 * @param $Numero
	 * @param $ImportoTotaleDocumento
	 */
	public function __construct( $TipoDocumento, $Divisa, $Data, $Numero, $ImportoTotaleDocumento, $ScontoMaggiorazione = null ) {
		$this->TipoDocumento = $TipoDocumento;
		$this->Divisa = $Divisa;
		$this->Data = $Data;
		$this->Numero = $Numero;
		$this->ImportoTotaleDocumento = $ImportoTotaleDocumento;
		$this->ScontoMaggiorazione = $ScontoMaggiorazione;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <DatiGenerali>
            <DatiGeneraliDocumento>
                <TipoDocumento><?php echo $this->TipoDocumento; ?></TipoDocumento>
                <Divisa><?php echo $this->Divisa; ?></Divisa>
                <Data><?php echo $this->Data; ?></Data>
                <Numero><?php echo $this->Numero; ?></Numero>
                <?php if (null != $this->ScontoMaggiorazione) {
                	echo $this->ScontoMaggiorazione->getXML();
                } ?>
                <ImportoTotaleDocumento><?php echo $this->ImportoTotaleDocumento; ?></ImportoTotaleDocumento>
            </DatiGeneraliDocumento>
        </DatiGenerali>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getTipoDocumento() {
		return $this->TipoDocumento;
	}

	/**
	 * @param mixed $TipoDocumento
	 */
	public function setTipoDocumento( $TipoDocumento ) {
		$this->TipoDocumento = $TipoDocumento;
	}

	/**
	 * @return mixed
	 */
	public function getDivisa() {
		return $this->Divisa;
	}

	/**
	 * @param mixed $Divisa
	 */
	public function setDivisa( $Divisa ) {
		$this->Divisa = $Divisa;
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->Data;
	}

	/**
	 * @param mixed $Data
	 */
	public function setData( $Data ) {
		$this->Data = $Data;
	}

	/**
	 * @return mixed
	 */
	public function getNumero() {
		return $this->Numero;
	}

	/**
	 * @param mixed $Numero
	 */
	public function setNumero( $Numero ) {
		$this->Numero = $Numero;
	}

	/**
	 * @return mixed
	 */
	public function getImportoTotaleDocumento() {
		return $this->ImportoTotaleDocumento;
	}

	/**
	 * @param mixed $ImportoTotaleDocumento
	 */
	public function setImportoTotaleDocumento( $ImportoTotaleDocumento ) {
		$this->ImportoTotaleDocumento = $ImportoTotaleDocumento;
	}
}