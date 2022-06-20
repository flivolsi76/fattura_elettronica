<?php

/**
 * Class DettaglioLinee
 */
class DettaglioLinee extends BaseEntity {

	/**
	 * @var array
	 */
	private $ArrayDettaglioLinea;

	/**
	 * DettaglioLinee constructor.
	 *
	 * @param array $ArrayDettaglioLinea
	 */
	public function __construct( $ArrayDettaglioLinea ) {
		$this->ArrayDettaglioLinea = $ArrayDettaglioLinea;
	}

	/**
	 * @return DatiRiepilogo
	 */
	public function DatiRiepilogo() {
		$AliquotaIVA = 22.00;
		$ImponibileImporto = 0;
		$Imposta = 0;
		$EsigibilitaIVA = 'I';

		$count = 0;
		foreach ( $this->ArrayDettaglioLinea as $dettaglioLinea ) {
			if ( $count === 0 ) {
				$AliquotaIVA = $dettaglioLinea->getAliquotaIVA();
			}
			$ImponibileImporto += $dettaglioLinea->getPrezzoTotale();
		}
		$Imposta = round( $ImponibileImporto, 2 ) * round( $AliquotaIVA / 100, 2 );
		$Imposta = number_format( $Imposta, 2, '.', '' );
		$AliquotaIVA = number_format( $AliquotaIVA, 2, '.', '' );

		return new DatiRiepilogo( $AliquotaIVA, $ImponibileImporto, $Imposta, $EsigibilitaIVA );
	}

	/**
	 * @return false|string
	 */
	public function getXML() {
		$xml = "";
		foreach ( $this->ArrayDettaglioLinea as $dettaglioLinea ) {
			$xml .= $dettaglioLinea->getXML();
		}

		return $xml;
	}
}