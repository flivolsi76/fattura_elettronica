<?php

/**
 * Class DatiTrasmissione
 */
class DatiTrasmissione extends BaseEntity {

    //Formato Trasmissione
	const PUBLIC_ADMINISTRATION_CODE = "FPA12";
	const PRIVATE_PARTIES_CODE = "FPR12";

	/**
	 * [IT], [ES], [DK], […]
	 *
	 * @var string
	 */
	private $IdPaese;
	/**
	 * @var string
	 */
	private $IdCodice;
	/**
	 * Ex. 2/2
	 *
	 * @var string
	 */
	private $ProgressivoInvio;
	/**
	 * allowed values:
	 * [FPA12] = invoice to pubblic administrations
	 * [FPR12] = invoice to private parties
	 *
	 * @var string
	 */
	private $FormatoTrasmissione;
	/**
	 *
	 * For invoices to PA (1.1.3 <FormatoTrasmissione> = FPA12) it contains a 6-digit code of the invoice office,
	 * defined by the administration to which it belongs as reported in the "Indice PA" list. For invoices to private
	 * parites (1.1.3 <FormatoTrasmissione> = FPR12) it contains the 7-digit code, assigned by the Exchange System
	 * (SDI) to subjects who have required a transmission channel; if the recipient has not required a channel the item
	 * must be valued with all zeros ('0000000'). For invoices issued to subjects that are non-resident, not
	 * established and not identified in Italy,  the item must be valued with all ‘XXXXXXX’.
	 *
	 * @var string
	 */
	private $CodiceDestinatario;

	/**
	 * @var string
	 */
	private $PECDestinatario;

	/**
	 * DatiTrasmissione constructor.
	 *
	 * @param $IdPaese
	 * @param $IdCodice
	 * @param $ProgressivoInvio
	 * @param $FormatoTrasmissione
	 * @param $CodiceDestinatario
	 * @param $PECDestinatario
	 */
	public function __construct(
		$IdPaese,
		$IdCodice,
		$ProgressivoInvio,
		$FormatoTrasmissione,
		$CodiceDestinatario,
		$PECDestinatario = null
	) {
		$this->IdPaese = $IdPaese;
		$this->IdCodice = $IdCodice;
		$this->ProgressivoInvio = $ProgressivoInvio;
		$this->FormatoTrasmissione = $FormatoTrasmissione;
		$this->CodiceDestinatario = $CodiceDestinatario;
		$this->PECDestinatario = $PECDestinatario;
	}

	/**
	 * getXML
	 */
	public function getXML() {
		ob_start();
		?>
        <DatiTrasmissione>
            <IdTrasmittente>
                <IdPaese><?php echo $this->IdPaese; ?></IdPaese>
                <IdCodice><?php echo $this->IdCodice; ?></IdCodice>
            </IdTrasmittente>
            <ProgressivoInvio><?php echo $this->ProgressivoInvio; ?></ProgressivoInvio>
            <FormatoTrasmissione><?php echo $this->FormatoTrasmissione; ?></FormatoTrasmissione>
            <CodiceDestinatario><?php echo $this->CodiceDestinatario; ?></CodiceDestinatario>
			<?php
			//I will put PEC just only if codice_destinatario does not exist
			if ( null !== $this->PECDestinatario ) { ?>
                <PECDestinatario><?php echo $this->PECDestinatario; ?></PECDestinatario>
			<?php } ?>
        </DatiTrasmissione>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getIdPaese() {
		return $this->IdPaese;
	}

	/**
	 * @param mixed $IdPaese
	 */
	public function setIdPaese( $IdPaese ) {
		$this->IdPaese = $IdPaese;
	}

	/**
	 * @return mixed
	 */
	public function getIdCodice() {
		return $this->IdCodice;
	}

	/**
	 * @param mixed $IdCodice
	 */
	public function setIdCodice( $IdCodice ) {
		$this->IdCodice = $IdCodice;
	}

	/**
	 * @return mixed
	 */
	public function getProgressivoInvio() {
		return $this->ProgressivoInvio;
	}

	/**
	 * @param mixed $ProgressivoInvio
	 */
	public function setProgressivoInvio( $ProgressivoInvio ) {
		$this->ProgressivoInvio = $ProgressivoInvio;
	}

	/**
	 * @return mixed
	 */
	public function getFormatoTrasmissione() {
		return $this->FormatoTrasmissione;
	}

	/**
	 * @param mixed $FormatoTrasmissione
	 */
	public function setFormatoTrasmissione( $FormatoTrasmissione ) {
		$this->FormatoTrasmissione = $FormatoTrasmissione;
	}

	/**
	 * @return mixed
	 */
	public function getCodiceDestinatario() {
		return $this->CodiceDestinatario;
	}

	/**
	 * @param mixed $CodiceDestinatario
	 */
	public function setCodiceDestinatario( $CodiceDestinatario ) {
		$this->CodiceDestinatario = $CodiceDestinatario;
	}

	/**
	 * @return string
	 */
	public function getPECDestinatario() {
		return $this->PECDestinatario;
	}

	/**
	 * @param string $PECDestinatario
	 */
	public function setPECDestinatario( $PECDestinatario ) {
		$this->PECDestinatario = $PECDestinatario;
	}
}