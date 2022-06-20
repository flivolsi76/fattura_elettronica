<?php

/**
 * Class Contatti
 */
class Contatti extends BaseEntity {

	private $Telefono;
	private $Email;

	/**
	 * Contatti constructor.
	 *
	 * @param $Telefono
	 * @param $Email
	 */
	public function __construct( $Telefono, $Email ) {
		$this->Telefono = $Telefono;
		$this->Email = $Email;
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		ob_start();
		?>
        <Contatti>
            <Telefono><?php echo $this->Telefono; ?></Telefono>
            <Email><?php echo $this->Email; ?></Email>
        </Contatti>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getTelefono() {
		return $this->Telefono;
	}

	/**
	 * @param mixed $Telefono
	 */
	public function setTelefono( $Telefono ) {
		$this->Telefono = $Telefono;
	}

	/**
	 * @return mixed
	 */
	public function getEmail() {
		return $this->Email;
	}

	/**
	 * @param mixed $Email
	 */
	public function setEmail( $Email ) {
		$this->Email = $Email;
	}
}