<?php

/**
 * Class DettaglioLinea
 */
class DettaglioLinea extends BaseEntity
{

	/** @var int number row */
	private $NumeroLinea;
	/**
	 * SKU indicates the type of article code (TARIC, CPV, EAN, SSC, etc.)
	 *
	 * @var string
	 */
	private $CodiceTipo;
	/**
	 * indicates the value of the CodiceTipo.
	 *
	 * @var string
	 */
	private $CodiceValore;
	/**
	 * @var string
	 */
	private $Descrizione;
	/**
	 * @var float
	 */
	private $Quantita;
	/**
	 * @var int PZ
	 */
	private $UnitaMisura;
	/**
	 * Numeric format; decimals must be separated from the integer with the '.' character (point)
	 *
	 * @var float
	 */
	private $PrezzoUnitario;
	/**
	 * Price Total Without VAT
	 *
	 * @var float
	 */
	private $PrezzoTotale;
	/**
	 * Numeric format; decimals must be separated from the integer with the '.' character (point)
	 *
	 * @var float
	 */
	private $AliquotaIVA;

	/**
	 * Code es: N3.2
	 *
	 * @var [type]
	 */
	private $Natura;

	/**
	 * @var string
	 */
	private $TipoCessionePrestazione;

	/**
	 * DettaglioLinea constructor.
	 *
	 * @param $NumeroLinea
	 * @param $CodiceTipo
	 * @param $CodiceValore
	 * @param $Descrizione
	 * @param $Quantita
	 * @param $UnitaMisura
	 * @param $PrezzoUnitario
	 * @param $PrezzoTotale
	 * @param $AliquotaIVA
	 * @param null $TipoCessionePrestazione {SC sconto|PR premio|AB abbuono|AC spesa accessoria}
	 */
	public function __construct(
		$NumeroLinea,
		$CodiceTipo,
		$CodiceValore,
		$Descrizione,
		$Quantita,
		$UnitaMisura,
		$PrezzoUnitario,
		$PrezzoTotale,
		$AliquotaIVA,
		$Natura = null,
		$TipoCessionePrestazione = null
	) {
		$this->NumeroLinea = $NumeroLinea;
		$this->CodiceTipo = $CodiceTipo;
		$this->CodiceValore = $CodiceValore;
		$this->Descrizione = $Descrizione;
		$this->Quantita = $Quantita;
		$this->UnitaMisura = $UnitaMisura;
		$this->PrezzoUnitario = $PrezzoUnitario;
		$this->PrezzoTotale = $PrezzoTotale;
		$this->AliquotaIVA = $AliquotaIVA;
		$this->Natura = $Natura;
		$this->TipoCessionePrestazione = $TipoCessionePrestazione;
	}

	public function getXML()
	{
		ob_start();
?>
		<DettaglioLinee>
			<NumeroLinea><?php echo $this->NumeroLinea; ?></NumeroLinea>
			<?php if (null !== $this->TipoCessionePrestazione) { ?>
				<TipoCessionePrestazione><?php echo $this->TipoCessionePrestazione; ?></TipoCessionePrestazione>
			<?php } ?>
			<CodiceArticolo>
				<CodiceTipo><?php echo $this->CodiceTipo; ?></CodiceTipo>
				<CodiceValore><?php echo $this->CodiceValore; ?></CodiceValore>
			</CodiceArticolo>
			<Descrizione><?php echo $this->Descrizione; ?></Descrizione>
			<Quantita><?php echo number_format($this->Quantita, 2, '.', ''); ?></Quantita>
			<UnitaMisura><?php echo $this->UnitaMisura; ?></UnitaMisura>
			<PrezzoUnitario><?php echo number_format($this->PrezzoUnitario, 2, '.', ''); ?></PrezzoUnitario>
			<PrezzoTotale><?php echo number_format($this->PrezzoTotale, 2, '.', ''); ?></PrezzoTotale>
			<AliquotaIVA><?php echo number_format($this->AliquotaIVA, 2, '.', ''); ?></AliquotaIVA>
			<?php if (null !== $this->Natura) { ?>
				<Natura><?php echo $this->Natura; ?></Natura>
			<?php } ?>
		</DettaglioLinee>
<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getNumeroLinea()
	{
		return $this->NumeroLinea;
	}

	/**
	 * @param mixed $NumeroLinea
	 */
	public function setNumeroLinea($NumeroLinea)
	{
		$this->NumeroLinea = $NumeroLinea;
	}

	/**
	 * @return mixed
	 */
	public function getCodiceTipo()
	{
		return $this->CodiceTipo;
	}

	/**
	 * @param mixed $CodiceTipo
	 */
	public function setCodiceTipo($CodiceTipo)
	{
		$this->CodiceTipo = $CodiceTipo;
	}

	/**
	 * @return mixed
	 */
	public function getCodiceValore()
	{
		return $this->CodiceValore;
	}

	/**
	 * @param mixed $CodiceValore
	 */
	public function setCodiceValore($CodiceValore)
	{
		$this->CodiceValore = $CodiceValore;
	}

	/**
	 * @return mixed
	 */
	public function getDescrizione()
	{
		return $this->Descrizione;
	}

	/**
	 * @param mixed $Descrizione
	 */
	public function setDescrizione($Descrizione)
	{
		$this->Descrizione = $Descrizione;
	}

	/**
	 * @return mixed
	 */
	public function getQuantita()
	{
		return $this->Quantita;
	}

	/**
	 * @param mixed $Quantita
	 */
	public function setQuantita($Quantita)
	{
		$this->Quantita = $Quantita;
	}

	/**
	 * @return mixed
	 */
	public function getUnitaMisura()
	{
		return $this->UnitaMisura;
	}

	/**
	 * @param mixed $UnitaMisura
	 */
	public function setUnitaMisura($UnitaMisura)
	{
		$this->UnitaMisura = $UnitaMisura;
	}

	/**
	 * @return mixed
	 */
	public function getPrezzoUnitario()
	{
		return $this->PrezzoUnitario;
	}

	/**
	 * @param mixed $PrezzoUnitario
	 */
	public function setPrezzoUnitario($PrezzoUnitario)
	{
		$this->PrezzoUnitario = $PrezzoUnitario;
	}

	/**
	 * @return mixed
	 */
	public function getPrezzoTotale()
	{
		return $this->PrezzoTotale;
	}

	/**
	 * @param mixed $PrezzoTotale
	 */
	public function setPrezzoTotale($PrezzoTotale)
	{
		$this->PrezzoTotale = $PrezzoTotale;
	}

	/**
	 * @return mixed
	 */
	public function getAliquotaIVA()
	{
		return $this->AliquotaIVA;
	}

	/**
	 * @param mixed $AliquotaIVA
	 */
	public function setAliquotaIVA($AliquotaIVA)
	{
		$this->AliquotaIVA = $AliquotaIVA;
	}

		/**
	 * @return mixed
	 */
	public function getNatura()
	{
		return $this->Natura;
	}

	/**
	 * @param mixed $Natura
	 */
	public function setNatura($Natura)
	{
		$this->Natura = $Natura;
	}

}
