<?php

/**
 * Class Sede
 */
class Sede extends BaseEntity {

	private $Indirizzo;
	private $CAP;
	private $Comune;
	private $Provincia;
	private $Nazione;

	/**
	 * Sede constructor.
	 *
	 * @param $Indirizzo
	 * @param $CAP
	 * @param $Comune
	 * @param $Provincia
	 * @param $Nazione
	 */
	public function __construct( $Indirizzo, $CAP, $Comune, $Provincia, $Nazione ) {
		$this->Indirizzo = $Indirizzo;
		$this->CAP = $CAP;
		$this->Comune = $Comune;
		$this->Provincia = $Provincia;
		$this->Nazione = $Nazione;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <Sede>
            <Indirizzo><?php echo $this->Indirizzo; ?></Indirizzo>
            <CAP><?php echo $this->CAP; ?></CAP>
            <Comune><?php echo $this->Comune; ?></Comune>
            <Provincia><?php echo $this->Provincia; ?></Provincia>
            <Nazione><?php echo $this->Nazione; ?></Nazione>
        </Sede>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getIndirizzo() {
		return $this->Indirizzo;
	}

	/**
	 * @param mixed $Indirizzo
	 */
	public function setIndirizzo( $Indirizzo ) {
		$this->Indirizzo = $Indirizzo;
	}

	/**
	 * @return mixed
	 */
	public function getCAP() {
		return $this->CAP;
	}

	/**
	 * @param mixed $CAP
	 */
	public function setCAP( $CAP ) {
		$this->CAP = $CAP;
	}

	/**
	 * @return mixed
	 */
	public function getComune() {
		return $this->Comune;
	}

	/**
	 * @param mixed $Comune
	 */
	public function setComune( $Comune ) {
		$this->Comune = $Comune;
	}

	/**
	 * @return mixed
	 */
	public function getProvincia() {
		return $this->Provincia;
	}

	/**
	 * @param mixed $Provincia
	 */
	public function setProvincia( $Provincia ) {
		$this->Provincia = $Provincia;
	}

	/**
	 * @return mixed
	 */
	public function getNazione() {
		return $this->Nazione;
	}

	/**
	 * @param mixed $Nazione
	 */
	public function setNazione( $Nazione ) {
		$this->Nazione = $Nazione;
	}
}