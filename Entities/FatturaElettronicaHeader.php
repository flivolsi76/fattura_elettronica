<?php

/**
 * Class FatturaElettronicaHeader
 */
class FatturaElettronicaHeader extends BaseEntity {

	private $DatiTrasmissione;
	private $CedentePrestatore;
	private $CessionarioCommittente;

	/**
	 * FatturaElettronicaHeader constructor.
	 *
	 * @param DatiTrasmissione $DatiTrasmissione
	 * @param CedentePrestatore $CedentePrestatore
	 * @param CessionarioCommittente $CessionarioCommittente
	 */
	public function __construct(
		DatiTrasmissione $DatiTrasmissione,
		CedentePrestatore $CedentePrestatore,
		CessionarioCommittente $CessionarioCommittente
	) {
		$this->DatiTrasmissione = $DatiTrasmissione;
		$this->CedentePrestatore = $CedentePrestatore;
		$this->CessionarioCommittente = $CessionarioCommittente;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <FatturaElettronicaHeader>
			<?php echo $this->DatiTrasmissione->getXML(); ?>
			<?php echo $this->CedentePrestatore->getXML(); ?>
			<?php echo $this->CessionarioCommittente->getXML(); ?>
        </FatturaElettronicaHeader>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return DatiTrasmissione
	 */
	public function getDatiTrasmissione() {
		return $this->DatiTrasmissione;
	}

	/**
	 * @param DatiTrasmissione $DatiTrasmissione
	 */
	public function setDatiTrasmissione( $DatiTrasmissione ) {
		$this->DatiTrasmissione = $DatiTrasmissione;
	}

	/**
	 * @return CedentePrestatore
	 */
	public function getCedentePrestatore() {
		return $this->CedentePrestatore;
	}

	/**
	 * @param CedentePrestatore $CedentePrestatore
	 */
	public function setCedentePrestatore( $CedentePrestatore ) {
		$this->CedentePrestatore = $CedentePrestatore;
	}

	/**
	 * @return CessionarioCommittente
	 */
	public function getCessionarioCommittente() {
		return $this->CessionarioCommittente;
	}

	/**
	 * @param CessionarioCommittente $CessionarioCommittente
	 */
	public function setCessionarioCommittente( $CessionarioCommittente ) {
		$this->CessionarioCommittente = $CessionarioCommittente;
	}
}