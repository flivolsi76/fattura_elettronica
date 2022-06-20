<?php

/**
 * Class DatiAnagrafici
 */
class DatiAnagrafici extends BaseEntity
{

	/**
	 * @var string {IT,EN,...}
	 */
	private $IdPaese;
	/**
	 * @var string
	 */
	private $IdCodice;
	/**
	 * @var string
	 */
	private $CodiceFiscale;
	/**
	 * @var string
	 */
	private $Denominazione;
	/**
	 * RF01        Ordinary
	 * RF02        Minimum taxpayers (Art. 1, section 96-117, Italian Law 244/07)
	 * RF04        Agriculture and connected activities and fishing (Arts. 34 and 34-bis, Italian Presidential Decree
	 * 633/72) RF05        Sale of salts and tobaccos (Art. 74, section 1, Italian Presidential Decree 633/72) RF06
	 *    Match sales (Art. 74, section 1, Italian Presidential Decree 633/72) RF07        Publishing (Art. 74, section
	 * 1, Italian Presidential Decree 633/72) RF08        Management of public telephone services (Art. 74, section 1,
	 * Italian Presidential Decree 633/72) RF09        Resale of public transport and parking documents (Art. 74,
	 * section 1, Italian Presidential Decree 633/72) RF10        Entertainment, gaming and other activities referred
	 * to by the tariff attached to Italian Presidential Decree 640/72 (Art. 74, section 6, Italian Presidential Decree
	 * 633/72) RF11        Travel and tourism agencies (Art. 74-ter, Italian Presidential Decree 633/72) RF12
	 * Farmhouse accommodation/restaurants (Art. 5, section 2, Italian law 413/91) RF13        Door-to-door sales (Art.
	 * 25-bis, section 6, Italian Presidential Decree 600/73) RF14        Resale of used goods, artworks, antiques or
	 * collector's items (Art. 36, Italian Decree Law 41/95) RF15        Artwork, antiques or collector's items auction
	 * agencies (Art. 40-bis, Italian Decree Law 41/95) RF16        VAT paid in cash by P.A. (Art. 6, section 5,
	 * Italian Presidential Decree 633/72) RF17        VAT paid in cash by subjects with business turnover below Euro
	 * 200,000 (Art. 7, Italian Decree Law 185/2008) RF18        Other RF19        Flat rate (Art. 1, section 54-89,
	 * Italian Law 190/2014)
	 *
	 * @var string
	 */
	private $RegimeFiscale;

	/**
	 * DatiAnagrafici constructor.
	 *
	 * @param $IdPaese
	 * @param $IdCodice
	 * @param $CodiceFiscale
	 * @param $Denominazione
	 * @param $RegimeFiscale
	 */
	public function __construct(
		$IdPaese,
		$IdCodice,
		$CodiceFiscale,
		$Denominazione,
		$RegimeFiscale = null
	) {
		$this->IdPaese = $IdPaese;
		$this->IdCodice = $IdCodice;
		$this->CodiceFiscale = $CodiceFiscale;
		$this->Denominazione = $Denominazione;
		$this->RegimeFiscale = $RegimeFiscale;
	}

	/**
	 * @return false|string
	 */
	public function getXML()
	{
		ob_start();
?>
		<DatiAnagrafici>
			<?php if (null != $this->IdCodice) { ?>
				<IdFiscaleIVA>
					<IdPaese><?php echo $this->IdPaese; ?></IdPaese>
					<IdCodice><?php echo $this->IdCodice; ?></IdCodice>
				</IdFiscaleIVA>
			<?php } ?>
			<?php if (null != $this->CodiceFiscale) { ?>
				<CodiceFiscale><?php echo $this->CodiceFiscale; ?></CodiceFiscale>
			<?php } ?>
			<Anagrafica>
				<Denominazione><?php echo $this->Denominazione; ?></Denominazione>
			</Anagrafica>
			<?php
			if (isset($this->RegimeFiscale)) {
			?>
				<RegimeFiscale><?php echo $this->RegimeFiscale; ?></RegimeFiscale>
			<?php } ?>
		</DatiAnagrafici>
<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getIdPaese()
	{
		return $this->IdPaese;
	}

	/**
	 * @param mixed $IdPaese
	 */
	public function setIdPaese($IdPaese)
	{
		$this->IdPaese = $IdPaese;
	}

	/**
	 * @return mixed
	 */
	public function getIdCodice()
	{
		return $this->IdCodice;
	}

	/**
	 * @param mixed $IdCodice
	 */
	public function setIdCodice($IdCodice)
	{
		$this->IdCodice = $IdCodice;
	}

	/**
	 * @return mixed
	 */
	public function getCodiceFiscale()
	{
		return $this->CodiceFiscale;
	}

	/**
	 * @param mixed $CodiceFiscale
	 */
	public function setCodiceFiscale($CodiceFiscale)
	{
		$this->CodiceFiscale = $CodiceFiscale;
	}

	/**
	 * @return mixed
	 */
	public function getDenominazione()
	{
		return $this->Denominazione;
	}

	/**
	 * @param mixed $Denominazione
	 */
	public function setDenominazione($Denominazione)
	{
		$this->Denominazione = $Denominazione;
	}

	/**
	 * @return mixed
	 */
	public function getRegimeFiscale()
	{
		return $this->RegimeFiscale;
	}

	/**
	 * @param mixed $RegimeFiscale
	 */
	public function setRegimeFiscale($RegimeFiscale)
	{
		$this->RegimeFiscale = $RegimeFiscale;
	}
}
