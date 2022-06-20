<?php

/**
 * Class FatturaElettronicaBody
 */
class FatturaElettronicaBody extends BaseEntity {

	private $DatiGenerali;
	private $DatiBeniServizi;
	private $DatiPagamento;

	/**
	 * FatturaElettronicaBody constructor.
	 *
	 * @param DatiGenerali $DatiGenerali
	 * @param DatiBeniServizi $DatiBeniServizi
	 * @param DatiPagamento $DatiPagamento
	 */
	public function __construct(
		DatiGenerali $DatiGenerali,
		DatiBeniServizi $DatiBeniServizi,
		DatiPagamento $DatiPagamento
	) {
		$this->DatiGenerali = $DatiGenerali;
		$this->DatiBeniServizi = $DatiBeniServizi;
		$this->DatiPagamento = $DatiPagamento;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <FatturaElettronicaBody>
			<?php echo $this->DatiGenerali->getXML(); ?>
			<?php echo $this->DatiBeniServizi->getXML(); ?>
			<?php echo $this->DatiPagamento->getXML(); ?>
        </FatturaElettronicaBody>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return DatiGenerali
	 */
	public function getDatiGenerali() {
		return $this->DatiGenerali;
	}

	/**
	 * @param DatiGenerali $DatiGenerali
	 */
	public function setDatiGenerali( $DatiGenerali ) {
		$this->DatiGenerali = $DatiGenerali;
	}

	/**
	 * @return DatiBeniServizi
	 */
	public function getDatiBeniServizi() {
		return $this->DatiBeniServizi;
	}

	/**
	 * @param DatiBeniServizi $DatiBeniServizi
	 */
	public function setDatiBeniServizi( $DatiBeniServizi ) {
		$this->DatiBeniServizi = $DatiBeniServizi;
	}

	/**
	 * @return DatiPaganti
	 */
	public function getDatiPagamento() {
		return $this->DatiPagamento;
	}

	/**
	 * @param DatiPaganti $DatiPagamento
	 */
	public function setDatiPagamento( $DatiPagamento ) {
		$this->DatiPagamento = $DatiPagamento;
	}
}