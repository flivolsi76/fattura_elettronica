<?php

/**
 * Class ScontoMaggiorazione
 */
class ScontoMaggiorazione extends BaseEntity {

	const TIPO_SC = 'SC';
	const TIPO_MG = 'MG';

	/* SC|MG **/
	private $Tipo;
	/* 12.00 */
	private $Percentuale;
	/* 99.00 */
	private $Importo;


	/**
	 * DatiGenerali constructor.
	 *
	 * @param $Tipo
	 * @param $Percentuale
	 * @param $Importo
	 */
	public function __construct( $Tipo, $Percentuale, $Importo ) {
		$this->Tipo = $Tipo;
		$this->Percentuale = $Percentuale;
		$this->Importo = $Importo;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <ScontoMaggiorazione>
            <Tipo><?php echo $this->Tipo; ?></Tipo>
            <Percentuale><?php echo $this->Percentuale; ?></Percentuale>
            <Importo><?php echo $this->Importo; ?></Importo>
        </ScontoMaggiorazione>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getTipo() {
		return $this->Tipo;
	}

	/**
	 * @param mixed $TipoDocumento
	 */
	public function setTipo( $Tipo ) {
		$this->Tipo = $Tipo;
	}

	/**
	 * @return mixed
	 */
	public function getPercentuale() {
		return $this->Percentuale;
	}

	/**
	 * @param mixed $Divisa
	 */
	public function setPercentuale( $Percentuale ) {
		$this->Per = $Percentuale;
	}

	/**
	 * @return mixed
	 */
	public function getImporto() {
		return $this->Importo;
	}

	/**
	 * @param mixed $Data
	 */
	public function setImporto( $Importo ) {
		$this->Importo = $Importo;
	}

}