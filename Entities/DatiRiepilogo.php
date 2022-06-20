<?php

/**
 * Class DatiRiepilogo
 */
class DatiRiepilogo extends BaseEntity
{

	private $AliquotaIVA;
	private $ImponibileImporto;
	private $Imposta;
	private $EsigibilitaIVA;
	private $Natura;
	private $RiferimentoNormativo;

	/**
	 * DatiRiepilogo constructor.
	 *
	 * @param $AliquotaIVA
	 * @param $ImponibileImporto
	 * @param $Imposta
	 * @param $EsigibilitaIVA
	 */
	public function __construct($AliquotaIVA, $ImponibileImporto, $Imposta, $EsigibilitaIVA, $Natura = null, $RiferimentoNormativo = null)
	{
		$this->AliquotaIVA = $AliquotaIVA;
		$this->ImponibileImporto = $ImponibileImporto;
		$this->Imposta = $Imposta;
		$this->EsigibilitaIVA = $EsigibilitaIVA;
		$this->Natura = $Natura;
		$this->RiferimentoNormativo = $RiferimentoNormativo;
	}

	/**
	 * @return false|string
	 */
	public function getXML()
	{
		ob_start();
?>
		<DatiRiepilogo>
			<AliquotaIVA><?php echo $this->AliquotaIVA; ?></AliquotaIVA>
			<?php if (null !== $this->Natura) { ?>
				<Natura><?php echo $this->Natura; ?></Natura>
			<?php } ?>
			<ImponibileImporto><?php echo $this->ImponibileImporto; ?></ImponibileImporto>
			<Imposta><?php echo $this->Imposta; ?></Imposta>
			<EsigibilitaIVA><?php echo $this->EsigibilitaIVA; ?></EsigibilitaIVA>
			<?php if (null !== $this->RiferimentoNormativo) { ?>
				<RiferimentoNormativo><?php echo $this->RiferimentoNormativo; ?></RiferimentoNormativo>
			<?php } ?>
		</DatiRiepilogo>
<?php
		return ob_get_clean();
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
	public function getImponibileImporto()
	{
		return $this->ImponibileImporto;
	}

	/**
	 * @param mixed $ImponibileImporto
	 */
	public function setImponibileImporto($ImponibileImporto)
	{
		$this->ImponibileImporto = $ImponibileImporto;
	}

	/**
	 * @return mixed
	 */
	public function getImposta()
	{
		return $this->Imposta;
	}

	/**
	 * @param mixed $Imposta
	 */
	public function setImposta($Imposta)
	{
		$this->Imposta = $Imposta;
	}

	/**
	 * @return mixed
	 */
	public function getEsigibilitaIVA()
	{
		return $this->EsigibilitaIVA;
	}

	/**
	 * @param mixed $EsigibilitaIVA
	 */
	public function setEsigibilitaIVA($EsigibilitaIVA)
	{
		$this->EsigibilitaIVA = $EsigibilitaIVA;
	}

	/**
	 * @return mixed
	 */
	public function getRiepilogoNormativo()
	{
		return $this->RiepilogoNormativo;
	}

	/**
	 * @param mixed $RiepilogoNormativo
	 */
	public function setRiepilogoNormativo($RiepilogoNormativo)
	{
		$this->RiepilogoNormativo = $RiepilogoNormativo;
	}
}
