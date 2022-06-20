<?php

/**
 * Class DatiBeniServizi
 */
class DatiBeniServizi extends BaseEntity {

	private $DettaglioLinee;
	private $DatiRiepilogo;

	/**
	 * DatiBeniServizi constructor.
	 *
	 * @param DettaglioLinee $DettaglioLinee
	 * @param DatiRiepilogo $DatiRiepilogo
	 */
	public function __construct( DettaglioLinee $DettaglioLinee, DatiRiepilogo $DatiRiepilogo ) {
		$this->DettaglioLinee = $DettaglioLinee;
		$this->DatiRiepilogo = $DatiRiepilogo;
	}

	public function getXML() {
		ob_start();
		?>
        <DatiBeniServizi>
			<?php echo $this->DettaglioLinee->getXML(); ?>
			<?php echo $this->DatiRiepilogo->getXML(); ?>
        </DatiBeniServizi>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return DettaglioLinee
	 */
	public function getDettaglioLinee() {
		return $this->DettaglioLinee;
	}

	/**
	 * @param DettaglioLinee $DettaglioLinee
	 */
	public function setDettaglioLinee( $DettaglioLinee ) {
		$this->DettaglioLinee = $DettaglioLinee;
	}

	/**
	 * @return DatiRiepilogo
	 */
	public function getDatiRiepilogo() {
		return $this->DatiRiepilogo;
	}

	/**
	 * @param DatiRiepilogo $DatiRiepilogo
	 */
	public function setDatiRiepilogo( $DatiRiepilogo ) {
		$this->DatiRiepilogo = $DatiRiepilogo;
	}
}