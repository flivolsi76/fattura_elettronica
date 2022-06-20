<?php

/**
 * Class CessionarioCommittente
 */
class CessionarioCommittente extends BaseEntity {

	private $DatiAnagrafici;
	private $Sede;
	private $Contatti;

	public function __construct(
		DatiAnagrafici $DatiAnagrafici,
		Sede $Sede,
		Contatti $Contatti
	) {
		$this->DatiAnagrafici = $DatiAnagrafici;
		$this->Sede = $Sede;
		$this->Contatti = $Contatti;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <CessionarioCommittente>
			<?php echo $this->DatiAnagrafici->getXML(); ?>
			<?php echo $this->Sede->getXML(); ?>
        </CessionarioCommittente>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return DatiAnagrafici
	 */
	public function getDatiAnagrafici() {
		return $this->DatiAnagrafici;
	}

	/**
	 * @param DatiAnagrafici $DatiAnagrafici
	 */
	public function setDatiAnagrafici( $DatiAnagrafici ) {
		$this->DatiAnagrafici = $DatiAnagrafici;
	}

	/**
	 * @return Sede
	 */
	public function getSede() {
		return $this->Sede;
	}

	/**
	 * @param Sede $Sede
	 */
	public function setSede( $Sede ) {
		$this->Sede = $Sede;
	}

	/**
	 * @return Contatti
	 */
	public function getContatti() {
		return $this->Contatti;
	}

	/**
	 * @param Contatti $Contatti
	 */
	public function setContatti( $Contatti ) {
		$this->Contatti = $Contatti;
	}
}