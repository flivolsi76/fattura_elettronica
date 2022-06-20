<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'config.php';
require APPPATH . '/controllers/admin/Abstract_admin.php';

/**
 * Class Abstract_einvoice
 */
class Abstract_einvoice extends Abstract_admin
{

	protected $baseDir = "archivio";
	protected $baseDirInvoices = "e-invoices";
	protected $baseDirCreditNotes = "e-credit-notes";

	const INVOICE = 'invoice';

	const CREDIT_NOTE = 'credit_note';

	const IVA = 22.00;

	protected $account_id;
	protected $parent_id;
	protected $level;

	/** @var boolean */
	protected $create_without_sdi_or_pec = false;

	/**
	 * Abstract_einvoice constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return mixed
	 */
	public function getCreateWithoutSdiOrPec()
	{
		return $this->create_without_sdi_or_pec;
	}

	/**
	 * @param mixed $create_without_sdi_or_pec
	 */
	public function setCreateWithoutSdiOrPec($create_without_sdi_or_pec)
	{
		$this->create_without_sdi_or_pec = $create_without_sdi_or_pec;
	}

	/**
	 * @param $order_id
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function get_invoice_xml($order_id)
	{
		$db_order = $this->order->read_by_order_id($order_id);
		if (empty($db_order)) {
			throw new Exception("No order by {$order_id}");
		}

		if (!isset($db_order['date_delivery'])) {
			throw new Exception('Error Date Order is null');
		}

		if (strtotime($db_order['date_delivery']) >= strtotime('2021-01-01 00:00:00')) {
			$default_contribute_pfu_txt = "Contr.Amb.Cat.  %s Decreto Ministeriale 182/2019";
		} else {
			$default_contribute_pfu_txt = CONTRIBUTE_TXT;
		}

		$IVA = isset($db_order['percentage_vat']) ? $db_order['percentage_vat'] : number_format(self::IVA, 2, '.', '');
		$sellerNation = !empty($db_order['nation']) ? $db_order['nation'] : (!empty($db_order['billing_nation']) ? $db_order['billing_nation'] : 'IT');
		if ($sellerNation != 'IT') {
			$IVA = '0.00';
		}

		$invoiceNumber = isset($db_order['invoice_number']) ? $db_order['invoice_number'] : $db_order['number'];
		$db_account = $this->user->read($db_order['account_id']);

		$has_codice_destinatario = isset($db_account['codice_destinatario']) && !empty($db_account['codice_destinatario']);
		$has_pec = isset($db_account['pec']) && !empty($db_account['pec']);
		if (
			!$this->create_without_sdi_or_pec
			&& !$has_codice_destinatario
			&& !$has_pec
		) {
			throw new Exception('Codice_Destinatario or PEC at least must be set');
		}

		if ($sellerNation != 'IT') {
			$codiceDestinatario = 'XXXXXXX';
		} else {
			//I need to put one or the other not both of CodiceDestinatario and PECDestinatario as well
			$codiceDestinatario = $has_codice_destinatario ? strtoupper($db_account['codice_destinatario']) : '0000000';
		}
		$PECDestinatario = $has_codice_destinatario ? null : $db_account['pec'];

		if (is_subscription_order($db_order)) {
			$deliveryPrice = 0;
			$total = $db_order['billing_subtotal'];
			$contributeTot = 0;

			$products[] = array(
				'NumeroLinea' => 1,
				'CodiceTipo' => 'PROPRIETARIO',
				'CodiceValore' => 'SUBSCRY1',
				'Descrizione' => 'Iscrizione Annuale ' . date('Y', strtotime($db_order['date_order'])),
				'Quantita' => 1,
				'UnitaMisura' => 'PZ',
				'PrezzoUnitario' => $total,
				'PrezzoTotale' => $total,
				'AliquotaIVA' => $IVA,
			);
		} else {

			$db_products = $this->suborder->read_all_by_order_id($order_id);
			if (empty($db_products)) {
				throw new Exception("No products for {$order_id}");
			}

			$preTotalWithDiscount = 0;
			$preTotalWithoutDiscount = 0;
			$preTotalQuantityWithoutDiscount = 0;
			foreach ($db_products as $db_product) {
				$subTotal = $db_product['price_product'] * $db_product['qty'];
				if ($db_product['discount'] > 0) {
					$preTotalWithDiscount += $subTotal;
				} else {
					$preTotalWithoutDiscount += $subTotal;
					$preTotalQuantityWithoutDiscount += $db_product['qty'];
				}
			}

			$delivery_cost = 0;
			$client_discount = $db_order['discount'];
			$client_min_qty = $db_order['min_qty'];
			$not_disc_qty = 0;
			$tot_with_disc = 0;
			$tot_without_disc = 0;
			$contributeArray = array();
			$contributeTot = 0;
			$singlePfuTot = 0;
			$products = array();
			$line = 1;
			$totalQty = 0;
			$ImponibileImporto = 0.00;

			$has_client_discount = false;
			if (
				$preTotalQuantityWithoutDiscount >= $client_min_qty
				&& isset($client_discount) && $client_discount > 0
			) {
				$has_client_discount = true;
			}

			foreach ($db_products as $db_product) {
				$qty = $db_product['qty'];
				$totalQty += $qty;
				$productPrice = $db_product['price_product'];

				if (!isset($db_product['product_id'])) {

					$single_delivery_cost = 0;
					$single_total_delivery_cost = 0;
					$delivery_cost += $single_total_delivery_cost;

					if (isset($_GET['debug'])) {
						_pre("originale:" . $productPrice);
					}
					//Client discount applied to single Line
					if ($has_client_discount && $db_product['discount'] <= 0) {
						$discount = round(($client_discount / 100) * $productPrice, 2);
						$productPrice -= $discount;
						if (isset($_GET['debug'])) {
							_pre("Sconto: " . ($client_discount));
							_pre("Tot Sconto: " . $discount);
						}
					}
					if (isset($_GET['debug'])) {
						_pre("Prezzo scontato:" . $productPrice);
					}
					$subTotal = $productPrice * $qty;
					if (isset($_GET['debug'])) {
						_pre("subTotal:" . $subTotal);
					}
					if ($db_product['discount'] > 0) {
						$tot_with_disc += $subTotal;
					} else {
						$tot_without_disc += $subTotal;
						$not_disc_qty += $db_product['qty'];
					}
					$stringPfu = 0;
					$singlePfu = 0;
					$singlePfuTot = 0;
					$contributeTot += $singlePfuTot;
					$productArray = array(
						'NumeroLinea' => $line,
						'CodiceTipo' => 'PROPRIETARIO',
						'CodiceValore' => $invoiceNumber . "." . $db_product['suborder_id'] . "." . $db_product['suborder_id'],
						'Descrizione' => $db_product['description'],
						'Quantita' => $qty,
						'UnitaMisura' => 'PZ',
						'PrezzoUnitario' => $productPrice,
						'PrezzoTotale' => $subTotal,
						'AliquotaIVA' => $IVA,
					);
					if ($sellerNation != 'IT') {
						$productArray['Natura'] = 'N3.2';
					}
				} else {

					$product = get_product_id($db_product['product_id']);
					$single_delivery_cost = get_delivery_price_by_product($product);
					$single_total_delivery_cost = get_real_delivery_cost_by_qty($single_delivery_cost, $db_product);
					$delivery_cost += $single_total_delivery_cost;

					if (isset($_GET['debug'])) {
						_pre("originale:" . $productPrice);
					}
					//Client discount applied to single Line
					if ($has_client_discount && $db_product['discount'] <= 0) {
						$discount = round(($client_discount / 100) * $productPrice, 2);
						$productPrice -= $discount;
						if (isset($_GET['debug'])) {
							_pre("Sconto: " . ($client_discount));
							_pre("Tot Sconto: " . $discount);
						}
					}
					if (isset($_GET['debug'])) {
						_pre("Prezzo scontato:" . $productPrice);
					}
					$subTotal = $productPrice * $qty;
					if (isset($_GET['debug'])) {
						_pre("subTotal:" . $subTotal);
					}
					if ($db_product['discount'] > 0) {
						$tot_with_disc += $subTotal;
					} else {
						$tot_without_disc += $subTotal;
						$not_disc_qty += $db_product['qty'];
					}
					$stringPfu = isset($db_product['string_pfu']) ? $db_product['string_pfu'] : sprintf($default_contribute_pfu_txt, 'P3');
					$singlePfu = isset($db_product['single_pfu']) ? $db_product['single_pfu'] : get_pfu_by_product($db_product, $db_order);
					$singlePfuTot = $db_product['qty'] * $singlePfu;
					$contributeTot += $singlePfuTot;
					$productArray = array(
						'NumeroLinea' => $line,
						'CodiceTipo' => 'PROPRIETARIO',
						'CodiceValore' => $invoiceNumber . "." . $db_product['suborder_id'] . "." . $db_product['suborder_id'],
						'Descrizione' => $db_product['description'],
						'Quantita' => $qty,
						'UnitaMisura' => 'PZ',
						'PrezzoUnitario' => $productPrice,
						'PrezzoTotale' => $subTotal,
						'AliquotaIVA' => $IVA,
					);
					if ($sellerNation != 'IT') {
						$productArray['Natura'] = 'N3.2';
					}
				}

				$products[] = $productArray;
				$line++;

				if ($singlePfuTot > 0) {
					$productArray = array(
						'NumeroLinea' => $line,
						'CodiceTipo' => 'PROPRIETARIO',
						'CodiceValore' => 'PFU',
						'Descrizione' => $stringPfu,
						'Quantita' => $db_product['qty'],
						'UnitaMisura' => 'PZ',
						'PrezzoUnitario' => $singlePfu,
						'PrezzoTotale' => $singlePfuTot,
						'AliquotaIVA' => $IVA,
					);
					if ($sellerNation != 'IT') {
						$productArray['Natura'] = 'N3.2';
					}
					$products[] = $productArray;
					$line++;
				}
			}
			$total = $tot_with_disc + $tot_without_disc;
			if (isset($_GET['debug'])) {
				_pre("tot con sconto:" . $tot_with_disc);
				_pre("tot senza sconto:" . $tot_without_disc);
				_pre("PrezzoTotale:" . $total);
			}
			if (empty($products)) {
				throw new Exception('No products');
			}

			/*
			$has_client_discount = false;
			$totalDiscount = 0;
			$newtot_without_disc = 0;
			if ( $not_disc_qty >= $client_min_qty && isset( $client_discount ) && $client_discount > 0 ) {
				$has_client_discount = true;
				$totalDiscount = $tot_without_disc * ( $client_discount / 100 );
				$newtot_without_disc = $tot_without_disc - $totalDiscount;
				$total = $tot_with_disc + $newtot_without_disc;
			}
			$scontoMaggiorazione = null;
			if ( $has_client_discount ) {
				$scontoMaggiorazione = new ScontoMaggiorazione( 'SC', number_format( $client_discount, 2, '.', '' ), number_format( $totalDiscount, 2, '.', '' ) );
			}
			*/

			$extraCost = isset($db_order['extra_cost']) ? $db_order['extra_cost'] : 0;
			if ($extraCost > 0) {
				$productArray = array(
					'NumeroLinea' => $line,
					'CodiceTipo' => 'PROPRIETARIO',
					'CodiceValore' => 'SPZ',
					'Descrizione' => 'Spese di Trattamento',
					'Quantita' => 1,
					'UnitaMisura' => 'PZ',
					'PrezzoUnitario' => $extraCost,
					'PrezzoTotale' => $extraCost,
					'AliquotaIVA' => $IVA,
				);
				if ($sellerNation != 'IT') {
					$productArray['Natura'] = 'N3.2';
				}
				$products[] = $productArray;
				$line++;
			}

			$deliveryPrice = isset($db_order['delivery_cost']) ? $db_order['delivery_cost'] : $delivery_cost;
			if ($deliveryPrice > 0) {
				$productArray = array(
					'NumeroLinea' => $line,
					'CodiceTipo' => 'PROPRIETARIO',
					'CodiceValore' => 'SPZ',
					'Descrizione' => 'Spese di Spedizione',
					'Quantita' => 1,
					'UnitaMisura' => 'PZ',
					'PrezzoUnitario' => $deliveryPrice,
					'PrezzoTotale' => $deliveryPrice,
					'AliquotaIVA' => $IVA,
				);
				if ($sellerNation != 'IT') {
					$productArray['Natura'] = 'N3.2';
				}
				$products[] = $productArray;
				$line++;
			}
		}

		$has_date_einvoice = isset($db_order['date_einvoice']) && $db_order['date_einvoice'] != '0000-00-00 00:00:00';
		if ($has_date_einvoice) {
			$db_date_order = $db_order['date_einvoice'];
		} else {
			$db_date_order = isset($db_order['date_delivery']) && $db_order['date_delivery'] != '0000-00-00 00:00:00' ? $db_order['date_delivery'] : $db_order['date_order'];
		}
		$dateInvoice = date("Y-m-d", strtotime($db_date_order));

		$ImponibileImportoIvaEsclusa = $total + $contributeTot + $deliveryPrice + $extraCost;
		$Imposta = ($ImponibileImportoIvaEsclusa / 100) * $IVA;
		//$ImponibileImporto = $ImponibileImportoIvaEsclusa + $Imposta;
		$ImponibileImporto = bcadd(number_format($ImponibileImportoIvaEsclusa, 2, '.', ''), number_format($Imposta, 2, '.', ''), 2);
		$ImponibileImporto = number_format($ImponibileImporto, 2, '.', '');
		$Imposta = number_format($Imposta, 2, '.', '');
		$ImponibileImportoIvaEsclusa = number_format($ImponibileImportoIvaEsclusa, 2, '.', '');
		$summaryData = array(
			'AliquotaIVA' => $IVA,
			'ImponibileImporto' => $ImponibileImportoIvaEsclusa,
			'Imposta' => $Imposta,
			'EsigibilitaIVA' => 'I',
		);
		$summaryData['RiferimentoNormativo'] = null;
		if ($sellerNation != 'IT') {
			$summaryData['Natura'] = 'N3.2';
			$summaryData['RiferimentoNormativo'] = "N.I. art. 41 D.L. 331/93";
		}

		$seller = array(
			'IdPaese' => IdPaese,
			'IdCodice' => IdCodice,
			'CodiceFiscale' => CodiceFiscale,
			'Denominazione' => Denominazione,
			'RegimeFiscale' => RegimeFiscale,
			'Indirizzo' => Indirizzo,
			'CAP' => ECAP,
			'Comune' => Comune,
			'Provincia' => Provincia,
			'Nazione' => Nazione,
			'Telefono' => Telefono,
			'Email' => Email,
		);

		$vatOnlyNumbers = preg_replace('/[^0-9]/', '', (isset($db_order['billing_piva']) ? $db_order['billing_piva'] : $db_account['piva']));
		if ($sellerNation != 'IT') {
			$vatOnlyNumbers = (isset($db_order['billing_piva']) ? $db_order['billing_piva'] : $db_account['piva']);
			/*
			if ($sellerNation == 'ES' && substr($vatOnlyNumbers, 0, 2) == 'ES')
				$vatOnlyNumbers = substr($vatOnlyNumbers, 2, strlen($vatOnlyNumbers));
			if ($sellerNation == 'FR' && substr($vatOnlyNumbers, 0, 2) == 'FR')
				$vatOnlyNumbers = preg_replace('/[^0-9]/', '', $vatOnlyNumbers);
			*/
		}
		$isPrivate = $vatOnlyNumbers == '00000000000' || $vatOnlyNumbers == 'XXXXXXXXXXX' || empty($vatOnlyNumbers);
		if ($isPrivate) {
			$vatOnlyNumbers = null;
		}
		//$codiceFiscale = isset( $db_order['billing_cf'] ) ? $db_order['billing_piva'] : $db_account['cf'];
		//$codiceFiscale = ! empty( $codiceFiscale ) ? strtoupper( $codiceFiscale ) : $vatOnlyNumbers;
		$codiceFiscale = $isPrivate ? @$db_order['billing_cf'] : null;
		$order_address = isset($db_order['billing_address']) ? $db_order['billing_address'] : $db_account['address'];

		/*
		$order_address = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $order_address);
		$order_address = iconv('UTF-8', 'ISO-8859-1//IGNORE', $order_address);
		$order_address = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $order_address);
		*/
		$order_address = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $order_address);

		$buyer = array(
			'IdPaese' => $sellerNation,
			'IdCodice' => $vatOnlyNumbers,
			'CodiceFiscale' => $codiceFiscale,
			'Denominazione' => isset($db_order['billing_company']) ? $db_order['billing_company'] : $db_account['company'],
			'Indirizzo' => $order_address,
			'CAP' => isset($db_order['billing_cap']) ? $db_order['billing_cap'] : $db_account['cap'],
			'Comune' => isset($db_order['billing_city']) ? $db_order['billing_city'] : $db_account['country'],
			'Provincia' => $this->get_correct_provincia((isset($db_order['billing_province']) ? $db_order['billing_province'] : $db_account['province'])),
			'Nazione' => $sellerNation,
			'Telefono' => '',
			'Email' => '',
		);

		$transmissionData = array(
			'ProgressivoInvio' => $invoiceNumber,
			'FormatoTrasmissione' => DatiTrasmissione::PRIVATE_PARTIES_CODE,
			'CodiceDestinatario' => $codiceDestinatario,
			'PECDestinatario' => $PECDestinatario,
		);
		$invoiceData = array(
			'IdPaese' => IdPaese,
			'IdCodice' => IdCodice,
			'TipoDocumento' => DatiGenerali::FATTURA,
			'Divisa' => 'EUR',
			'Data' => $dateInvoice,
			'Numero' => $invoiceNumber,
			'ImportoTotaleDocumento' => $ImponibileImporto,
		);

		$tipoPagamentoOrdine = $db_order['request_payment'];
		list($modalita_pagamento, $dataScadenzaPagamento) = getPagamentoInfo($tipoPagamentoOrdine, $dateInvoice);
		$paymentData = array(
			'CondizioniPagamento' => DatiPagamento::PAGAMENTO_COMPLETO,
			'ModalitaPagamento' => $modalita_pagamento,
			'DataScadenzaPagamento' => $dataScadenzaPagamento,
			'ImportoPagamento' => $ImponibileImporto,
		);

		//##########################################################
		// FATTURA HEADER
		//##########################################################
		$datiTrasmissione = new DatiTrasmissione($invoiceData['IdPaese'], $invoiceData['IdCodice'], $transmissionData['ProgressivoInvio'], $transmissionData['FormatoTrasmissione'], $transmissionData['CodiceDestinatario'], $transmissionData['PECDestinatario']);
		$sellerDatiAnagrafici = new DatiAnagrafici($seller['IdPaese'], $seller['IdCodice'], $seller['CodiceFiscale'], $seller['Denominazione'], $seller['RegimeFiscale']);
		$sellerSede = new Sede($seller['Indirizzo'], $seller['CAP'], $seller['Comune'], $seller['Provincia'], $seller['Nazione']);
		$sellerContatti = new Contatti($seller['Telefono'], $seller['Email']);
		$cedentePrestatore = new CedentePrestatore($sellerDatiAnagrafici, $sellerSede, $sellerContatti);
		$buyerDatiAnagrafici = new DatiAnagrafici($buyer['IdPaese'], $buyer['IdCodice'], $buyer['CodiceFiscale'], $buyer['Denominazione']);
		$buyerSede = new Sede($buyer['Indirizzo'], $buyer['CAP'], $buyer['Comune'], $buyer['Provincia'], $buyer['Nazione']);
		$buyerContatti = new Contatti($buyer['Telefono'], $buyer['Email']);
		$cessionearioCommittente = new CessionarioCommittente($buyerDatiAnagrafici, $buyerSede, $buyerContatti);
		$fatturaElettronicaHeader = new FatturaElettronicaHeader($datiTrasmissione, $cedentePrestatore, $cessionearioCommittente);

		//##########################################################
		// FATTURA BODY
		//##########################################################
		$datiGenerali = new DatiGenerali($invoiceData['TipoDocumento'], $invoiceData['Divisa'], $invoiceData['Data'], $invoiceData['Numero'], $invoiceData['ImportoTotaleDocumento']);
		$datiRiepilogo = new DatiRiepilogo($summaryData['AliquotaIVA'], $summaryData['ImponibileImporto'], $summaryData['Imposta'], $summaryData['EsigibilitaIVA'], @$summaryData['Natura'], $summaryData['RiferimentoNormativo']);
		$arrayDettaglioLinee = array();
		foreach ($products as $num => $product) {
			$dettaglioLinea = new DettaglioLinea($product['NumeroLinea'], $product['CodiceTipo'], $product['CodiceValore'], $product['Descrizione'], $product['Quantita'], $product['UnitaMisura'], $product['PrezzoUnitario'], $product['PrezzoTotale'], $product['AliquotaIVA'], @$product['Natura']);
			$arrayDettaglioLinee[] = $dettaglioLinea;
		}
		$dettaglioLinee = new DettaglioLinee($arrayDettaglioLinee);
		$datiBeniServizi = new DatiBeniServizi($dettaglioLinee, $datiRiepilogo);
		$datiPagamento = new DatiPagamento($paymentData['CondizioniPagamento'], $paymentData['ModalitaPagamento'], $paymentData['DataScadenzaPagamento'], $paymentData['ImportoPagamento']);
		$fatturaElettronicaBody = new FatturaElettronicaBody($datiGenerali, $datiBeniServizi, $datiPagamento);
		$fatturaElettronica = new FatturaElettronica($fatturaElettronicaHeader, $fatturaElettronicaBody);

		return array(
			'xml' => $fatturaElettronica->getXML(),
			'account_id' => $db_order['account_id'],
			'invoice_number' => $invoiceNumber,
			'uniquecode' => $db_order['uniquecode'],
			'date_order' => $db_date_order,
			'codice_destinatario' => $codiceDestinatario,
		);
	}

	/**
	 * @param $order_id
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function get_credit_note_xml($order_id)
	{
		$db_order = $this->order->read_by_order_id($order_id);
		if (empty($db_order)) {
			throw new Exception("No order by {$order_id}");
		}

		$db_credit_note = $this->credit_note->read_by_order_id($order_id);
		if (!isset($db_order['date_delivery'])) {
			throw new Exception("Error there is no Credit Note for this order {$order_id}");
		}

		if (!isset($db_order['date_delivery'])) {
			throw new Exception('Error Date Order is null');
		}

		if (strtotime($db_order['date_delivery']) >= strtotime('2021-01-01 00:00:00')) {
			$default_contribute_pfu_txt = "Contr.Amb.Cat. %s Decreto Ministeriale 182/2019";
		} else {
			$default_contribute_pfu_txt = CONTRIBUTE_TXT;
		}

		$IVA = isset($db_order['percentage_vat']) ? $db_order['percentage_vat'] : number_format(self::IVA, 2, '.', '');
		$sellerNation = !empty($db_order['nation']) ? $db_order['nation'] : (!empty($db_order['billing_nation']) ? $db_order['billing_nation'] : 'IT');
		if ($sellerNation != 'IT') {
			$IVA = '0.00';
		}

		$invoiceNumber = isset($db_order['invoice_number']) ? $db_order['invoice_number'] : $db_order['number'];
		$__year = isset($db_credit_note['date_credit_note']) ? (int) strftime("%Y", strtotime($db_credit_note['date_credit_note'])) : 0;
		$creditNoteNumber = (defined("CREDIT_NOTE_PREFIX")) ? CREDIT_NOTE_PREFIX + $db_credit_note['number'] : $db_credit_note['number'];
		$db_account = $this->user->read($db_order['account_id']);

		$has_codice_destinatario = isset($db_account['codice_destinatario']) && !empty($db_account['codice_destinatario']);
		$has_pec = isset($db_account['pec']) && !empty($db_account['pec']);
		if (
			!$this->create_without_sdi_or_pec
			&& !$has_codice_destinatario
			&& !$has_pec
		) {
			throw new Exception('Codice_Destinatario or PEC at least must be set');
		}

		if ($sellerNation != 'IT') {
			$codiceDestinatario = 'XXXXXXX';
		} else {
			//I need to put one or the other not both of CodiceDestinatario and PECDestinatario as well
			$codiceDestinatario = $has_codice_destinatario ? strtoupper($db_account['codice_destinatario']) : '0000000';
		}
		$PECDestinatario = $has_codice_destinatario ? null : $db_account['pec'];

		if (is_subscription_order($db_order)) {
			$deliveryPrice = 0;
			$total = $db_order['billing_subtotal'];
			$contributeTot = 0;

			$products[] = array(
				'NumeroLinea' => 1,
				'CodiceTipo' => 'PROPRIETARIO',
				'CodiceValore' => 'SUBSCRY1',
				'Descrizione' => 'Iscrizione Annuale ' . date('Y', strtotime($db_order['date_order'])),
				'Quantita' => 1,
				'UnitaMisura' => 'PZ',
				'PrezzoUnitario' => $total,
				'PrezzoTotale' => $total,
				'AliquotaIVA' => $IVA,
			);
		} else {

			$db_products = $this->suborder->read_all_by_order_id($order_id);
			if (empty($db_products)) {
				throw new Exception("No products for {$order_id}");
			}

			$preTotalWithDiscount = 0;
			$preTotalWithoutDiscount = 0;
			$preTotalQuantityWithoutDiscount = 0;
			foreach ($db_products as $db_product) {
				$subTotal = $db_product['price_product'] * $db_product['qty'];
				if ($db_product['discount'] > 0) {
					$preTotalWithDiscount += $subTotal;
				} else {
					$preTotalWithoutDiscount += $subTotal;
					$preTotalQuantityWithoutDiscount += $db_product['qty'];
				}
			}

			$delivery_cost = 0;
			$client_discount = $db_order['discount'];
			$client_min_qty = $db_order['min_qty'];
			$not_disc_qty = 0;
			$tot_with_disc = 0;
			$tot_without_disc = 0;
			$contributeArray = array();
			$contributeTot = 0;
			$singlePfuTot = 0;
			$products = array();
			$line = 1;
			$totalQty = 0;
			$ImponibileImporto = 0.00;

			$has_client_discount = false;
			if (
				$preTotalQuantityWithoutDiscount >= $client_min_qty
				&& isset($client_discount) && $client_discount > 0
			) {
				$has_client_discount = true;
			}

			foreach ($db_products as $db_product) {

				if (!isset($db_product['product_id'])) {

					$qty = $db_product['qty'];
					$totalQty += $qty;
					$productPrice = $db_product['price_product'];

					$single_delivery_cost = 0;
					$single_total_delivery_cost = 0;
					$delivery_cost += $single_total_delivery_cost;

					//Client discount applied to single Line
					if ($has_client_discount && $db_product['discount'] <= 0) {
						$discount = round(($client_discount / 100) * $productPrice, 2);
						$productPrice -= $discount;
					}
					$subTotal = $productPrice * $qty;
					if ($db_product['discount'] > 0) {
						$tot_with_disc += $subTotal;
					} else {
						$tot_without_disc += $subTotal;
						$not_disc_qty += $db_product['qty'];
					}
					$stringPfu = 0;
					$singlePfu = 0;
					$singlePfuTot = $db_product['qty'] * $singlePfu;
					$contributeTot += $singlePfuTot;
					$productArray = array(
						'NumeroLinea' => $line,
						'CodiceTipo' => 'PROPRIETARIO',
						'CodiceValore' => $creditNoteNumber . "." . $invoiceNumber . "." . $db_product['suborder_id'] . "." . $db_product['suborder_id'],
						'Descrizione' => $db_product['description'],
						'Quantita' => $qty,
						'UnitaMisura' => 'PZ',
						'PrezzoUnitario' => $productPrice,
						'PrezzoTotale' => $subTotal,
						'AliquotaIVA' => $IVA,
					);
					if ($sellerNation != 'IT') {
						$productArray['Natura'] = 'N3.2';
					}
					$products[] = $productArray;
					$line++;
				} else {

					if ($db_product['finished'] == 0 && $db_order['status'] != ORDER_CANCELLED) {
						continue;
					}
					$qty = $db_product['qty'];
					$totalQty += $qty;
					$productPrice = $db_product['price_product'];

					$product = get_product_id($db_product['product_id']);
					$single_delivery_cost = get_delivery_price_by_product($product);
					$single_total_delivery_cost = get_real_delivery_cost_by_qty($single_delivery_cost, $db_product);
					$delivery_cost += $single_total_delivery_cost;

					//Client discount applied to single Line
					if ($has_client_discount && $db_product['discount'] <= 0) {
						$discount = round(($client_discount / 100) * $productPrice, 2);
						$productPrice -= $discount;
					}
					$subTotal = $productPrice * $qty;
					if ($db_product['discount'] > 0) {
						$tot_with_disc += $subTotal;
					} else {
						$tot_without_disc += $subTotal;
						$not_disc_qty += $db_product['qty'];
					}
					$stringPfu = isset($db_product['string_pfu']) ? $db_product['string_pfu'] : sprintf($default_contribute_pfu_txt, 'P3');
					$singlePfu = isset($db_product['single_pfu']) ? $db_product['single_pfu'] : get_pfu_by_product($db_product, $db_order);
					$singlePfuTot = $db_product['qty'] * $singlePfu;
					$contributeTot += $singlePfuTot;
					$productArray = array(
						'NumeroLinea' => $line,
						'CodiceTipo' => 'PROPRIETARIO',
						'CodiceValore' => $creditNoteNumber . "." . $invoiceNumber . "." . $db_product['suborder_id'] . "." . $db_product['suborder_id'],
						'Descrizione' => $db_product['description'],
						'Quantita' => $qty,
						'UnitaMisura' => 'PZ',
						'PrezzoUnitario' => $productPrice,
						'PrezzoTotale' => $subTotal,
						'AliquotaIVA' => $IVA,
					);
					if ($sellerNation != 'IT') {
						$productArray['Natura'] = 'N3.2';
					}
					$products[] = $productArray;
					$line++;

					if ($singlePfuTot > 0) {
						$productArray = array(
							'NumeroLinea' => $line,
							'CodiceTipo' => 'PROPRIETARIO',
							'CodiceValore' => 'PFU',
							'Descrizione' => $stringPfu,
							'Quantita' => $db_product['qty'],
							'UnitaMisura' => 'PZ',
							'PrezzoUnitario' => $singlePfu,
							'PrezzoTotale' => $singlePfuTot,
							'AliquotaIVA' => $IVA,
						);
						if ($sellerNation != 'IT') {
							$productArray['Natura'] = 'N3.2';
						}
						$products[] = $productArray;
						$line++;
					}
				}
			}
			$total = $tot_with_disc + $tot_without_disc;
			if (
				empty($products)
				&& (int) $db_credit_note['delivery_cost'] == 0
				&& (int) $db_order['delivery_cost'] == 0
			) {
				throw new Exception('No products');
			}

			/*
			$has_client_discount = false;
			$totalDiscount = 0;
			$newtot_without_disc = 0;
			if ( $not_disc_qty >= $client_min_qty && isset( $client_discount ) && $client_discount > 0 ) {
				$has_client_discount = true;
				$totalDiscount = $tot_without_disc * ( $client_discount / 100 );
				$newtot_without_disc = $tot_without_disc - $totalDiscount;
				$total = $tot_with_disc + $newtot_without_disc;
			}
			$scontoMaggiorazione = null;
			if ( $has_client_discount ) {
				$scontoMaggiorazione = new ScontoMaggiorazione( 'SC', number_format( $client_discount, 2, '.', '' ), number_format( $totalDiscount, 2, '.', '' ) );
			}
			*/

			if (isset($db_credit_note['delivery_cost'])) {
				$deliveryPrice = $db_credit_note['delivery_cost'];
			} else {
				$deliveryPrice = isset($db_order['delivery_cost']) ? $db_order['delivery_cost'] : $delivery_cost;
			}
			if ($deliveryPrice != 0) {
				$deliveryPriceToShow = number_format($deliveryPrice, 2, '.', '');
				$productArray = array(
					'NumeroLinea' => $line,
					'CodiceTipo' => 'PROPRIETARIO',
					'CodiceValore' => 'SPZ',
					'Descrizione' => 'Spese di Spedizione',
					'Quantita' => 1,
					'UnitaMisura' => 'PZ',
					'PrezzoUnitario' => $deliveryPriceToShow,
					'PrezzoTotale' => $deliveryPriceToShow,
					'AliquotaIVA' => $IVA,
				);
				if ($sellerNation != 'IT') {
					$productArray['Natura'] = 'N3.2';
				}
				$products[] = $productArray;
				$line++;
			}
		}

		$has_date_einvoice = isset($db_order['date_einvoice']) && $db_order['date_einvoice'] != '0000-00-00 00:00:00';
		if ($has_date_einvoice) {
			$db_date_order = $db_order['date_einvoice'];
		} else {
			$db_date_order = isset($db_order['date_delivery']) && $db_order['date_delivery'] != '0000-00-00 00:00:00' ? $db_order['date_delivery'] : $db_order['date_order'];
		}
		$dateInvoice = date("Y-m-d", strtotime($db_date_order));

		$compensationPrice = 0;
		if (isset($db_credit_note['compensation_cost']) && $db_credit_note['compensation_cost'] > 0) {
			$compensationPrice = $db_credit_note['compensation_cost'];
			$compensationPriceToShow = number_format($db_credit_note['compensation_cost'], 2, '.', '');
			$productArray = array(
				'NumeroLinea' => $line,
				'CodiceTipo' => 'PROPRIETARIO',
				'CodiceValore' => 'SPSPZ01',
				'Descrizione' => 'Risarcimento spese di spedizione e amministrative',
				'Quantita' => 1,
				'UnitaMisura' => 'PZ',
				'PrezzoUnitario' => -1 * $db_credit_note['compensation_cost'],
				'PrezzoTotale' => -1 * $db_credit_note['compensation_cost'],
				'AliquotaIVA' => $IVA,
				'TipoCessionePrestazione' => 'AC',
			);
			if ($sellerNation != 'IT') {
				$productArray['Natura'] = 'N3.2';
			}
			$products[] = $productArray;
			$line++;
		}

		$ImponibileImportoIvaEsclusa = $total + $contributeTot + $deliveryPrice - $compensationPrice;
		$Imposta = ($ImponibileImportoIvaEsclusa / 100) * $IVA;
		$ImponibileImporto = bcadd(number_format($ImponibileImportoIvaEsclusa, 2, '.', ''), number_format($Imposta, 2, '.', ''), 2);
		$Imposta = number_format($Imposta, 2, '.', '');
		$ImponibileImportoIvaEsclusa = number_format($ImponibileImportoIvaEsclusa, 2, '.', '');
		$summaryData = array(
			'AliquotaIVA' => $IVA,
			'ImponibileImporto' => $ImponibileImportoIvaEsclusa,
			'Imposta' => $Imposta,
			'EsigibilitaIVA' => 'I',
		);
		$summaryData['RiferimentoNormativo'] = null;
		if ($sellerNation != 'IT') {
			$summaryData['Natura'] = 'N3.2';
			$summaryData['RiferimentoNormativo'] = "N.I. art. 41 D.L. 331/93";
		}

		$seller = array(
			'IdPaese' => IdPaese,
			'IdCodice' => IdCodice,
			'CodiceFiscale' => CodiceFiscale,
			'Denominazione' => Denominazione,
			'RegimeFiscale' => RegimeFiscale,
			'Indirizzo' => Indirizzo,
			'CAP' => ECAP,
			'Comune' => Comune,
			'Provincia' => Provincia,
			'Nazione' => Nazione,
			'Telefono' => Telefono,
			'Email' => Email,
		);

        $vatOnlyNumbers = preg_replace('/[^0-9]/', '', (isset($db_order['billing_piva']) ? $db_order['billing_piva'] : $db_account['piva']));
        if ($sellerNation != 'IT') {
            $vatOnlyNumbers = (isset($db_order['billing_piva']) ? $db_order['billing_piva'] : $db_account['piva']);
            /*
            if ($sellerNation == 'ES' && substr($vatOnlyNumbers, 0, 2) == 'ES')
                $vatOnlyNumbers = substr($vatOnlyNumbers, 2, strlen($vatOnlyNumbers));
            if ($sellerNation == 'FR' && substr($vatOnlyNumbers, 0, 2) == 'FR')
                $vatOnlyNumbers = preg_replace('/[^0-9]/', '', $vatOnlyNumbers);
            */
        }
        $isPrivate = $vatOnlyNumbers == '00000000000' || $vatOnlyNumbers == 'XXXXXXXXXXX' || empty($vatOnlyNumbers);
        if ($isPrivate) {
            $vatOnlyNumbers = null;
        }
		//$codiceFiscale = isset( $db_order['billing_cf'] ) ? $db_order['billing_piva'] : $db_account['cf'];
		//$codiceFiscale = ! empty( $codiceFiscale ) ? strtoupper( $codiceFiscale ) : $vatOnlyNumbers;
		$codiceFiscale = null;

		$order_address = isset($db_order['billing_address']) ? $db_order['billing_address'] : $db_account['address'];
		$order_address = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $order_address);

		$buyer = array(
			'IdPaese' => $sellerNation,
			'IdCodice' => $vatOnlyNumbers,
			'CodiceFiscale' => $codiceFiscale,
			'Denominazione' => isset($db_order['billing_company']) ? $db_order['billing_company'] : $db_account['company'],
			'Indirizzo' => $order_address,
			'CAP' => isset($db_order['billing_cap']) ? $db_order['billing_cap'] : $db_account['cap'],
			'Comune' => isset($db_order['billing_city']) ? $db_order['billing_city'] : $db_account['country'],
			'Provincia' => $this->get_correct_provincia((isset($db_order['billing_province']) ? $db_order['billing_province'] : $db_account['province'])),
			'Nazione' => $sellerNation,
			'Telefono' => '',
			'Email' => '',
		);

		$transmissionData = array(
			//'ProgressivoInvio' => $creditNoteNumber . "." . $invoiceNumber,
			//'ProgressivoInvio' => $invoiceNumber,
			'ProgressivoInvio' => $creditNoteNumber,
			'FormatoTrasmissione' => DatiTrasmissione::PRIVATE_PARTIES_CODE,
			'CodiceDestinatario' => $codiceDestinatario,
			'PECDestinatario' => $PECDestinatario,
		);

		$dateCreditNote = date("Y-m-d", strtotime($db_credit_note['date_credit_note']));
		$invoiceData = array(
			'IdPaese' => IdPaese,
			'IdCodice' => IdCodice,
			'TipoDocumento' => DatiGenerali::NOTA_DI_CREDITO,
			'Divisa' => 'EUR',
			'Data' => $dateCreditNote,
			'Numero' => $creditNoteNumber,
			'ImportoTotaleDocumento' => $ImponibileImporto,
		);

		$tipoPagamentoOrdine = $db_order['request_payment'];
		list($modalita_pagamento, $dataScadenzaPagamento) = getPagamentoInfo($tipoPagamentoOrdine, $dateInvoice);
		$paymentData = array(
			'CondizioniPagamento' => DatiPagamento::PAGAMENTO_COMPLETO,
			'ModalitaPagamento' => $modalita_pagamento,
			'DataScadenzaPagamento' => $dataScadenzaPagamento,
			'ImportoPagamento' => $ImponibileImporto,
		);

		//##########################################################
		// NOTA DI CREDITO HEADER
		//##########################################################
		$datiTrasmissione = new DatiTrasmissione($invoiceData['IdPaese'], $invoiceData['IdCodice'], $transmissionData['ProgressivoInvio'], $transmissionData['FormatoTrasmissione'], $transmissionData['CodiceDestinatario'], $transmissionData['PECDestinatario']);
		if (empty($seller['IdCodice'])) {
			$seller['IdCodice'] = $seller['CodiceFiscale'];
		}
		$sellerDatiAnagrafici = new DatiAnagrafici($seller['IdPaese'], $seller['IdCodice'], $seller['CodiceFiscale'], $seller['Denominazione'], $seller['RegimeFiscale']);
		$sellerSede = new Sede($seller['Indirizzo'], $seller['CAP'], $seller['Comune'], $seller['Provincia'], $seller['Nazione']);
		$sellerContatti = new Contatti($seller['Telefono'], $seller['Email']);
		$cedentePrestatore = new CedentePrestatore($sellerDatiAnagrafici, $sellerSede, $sellerContatti);
		$buyerDatiAnagrafici = new DatiAnagrafici($buyer['IdPaese'], $buyer['IdCodice'], $buyer['CodiceFiscale'], $buyer['Denominazione']);
		$buyerSede = new Sede($buyer['Indirizzo'], $buyer['CAP'], $buyer['Comune'], $buyer['Provincia'], $buyer['Nazione']);
		$buyerContatti = new Contatti($buyer['Telefono'], $buyer['Email']);
		$cessionearioCommittente = new CessionarioCommittente($buyerDatiAnagrafici, $buyerSede, $buyerContatti);
		$fatturaElettronicaHeader = new FatturaElettronicaHeader($datiTrasmissione, $cedentePrestatore, $cessionearioCommittente);

		//##########################################################
		// FATTURA BODY
		//##########################################################
		$datiGenerali = new DatiGenerali($invoiceData['TipoDocumento'], $invoiceData['Divisa'], $invoiceData['Data'], $invoiceData['Numero'], $invoiceData['ImportoTotaleDocumento']);
		$datiRiepilogo = new DatiRiepilogo($summaryData['AliquotaIVA'], $summaryData['ImponibileImporto'], $summaryData['Imposta'], $summaryData['EsigibilitaIVA'], @$summaryData['Natura'], $summaryData['RiferimentoNormativo']);
		$arrayDettaglioLinee = array();
		foreach ($products as $num => $product) {
			$dettaglioLinea = new DettaglioLinea($product['NumeroLinea'], $product['CodiceTipo'], $product['CodiceValore'], $product['Descrizione'], $product['Quantita'], $product['UnitaMisura'], $product['PrezzoUnitario'], $product['PrezzoTotale'], $product['AliquotaIVA'], @$product['Natura']);
			$arrayDettaglioLinee[] = $dettaglioLinea;
		}
		$dettaglioLinee = new DettaglioLinee($arrayDettaglioLinee);
		$datiBeniServizi = new DatiBeniServizi($dettaglioLinee, $datiRiepilogo);
		$datiPagamento = new DatiPagamento($paymentData['CondizioniPagamento'], $paymentData['ModalitaPagamento'], $paymentData['DataScadenzaPagamento'], $paymentData['ImportoPagamento']);
		$fatturaElettronicaBody = new FatturaElettronicaBody($datiGenerali, $datiBeniServizi, $datiPagamento);
		$fatturaElettronica = new FatturaElettronica($fatturaElettronicaHeader, $fatturaElettronicaBody);

		return array(
			'xml' => $fatturaElettronica->getXML(),
			'account_id' => $db_order['account_id'],
			'invoice_number' => $creditNoteNumber,
			'uniquecode' => $db_order['uniquecode'],
			'date_order' => $db_date_order,
			'codice_destinatario' => $codiceDestinatario,
		);
	}

	/**
	 * @param $prv
	 *
	 * @return bool|string
	 */
	protected function get_correct_provincia($prv)
	{
		$provincia = strtoupper($prv);
		$short2full = get_province_array();
		$full2short = array_flip($short2full);
		if (isset($short2full[$provincia])) {
			return $provincia;
		}
		$provincia = ucfirst(strtolower($prv));
		if (isset($full2short[$provincia])) {
			return $full2short[$provincia];
		}

		return substr($prv, 0, 2);
	}

	/**
	 * @param $order_id
	 * @param $what
	 * @param null $where
	 *
	 * @return array|false
	 */
	public function validate_and_create_einvoice($order_id, $what, $where = null, $show_error = true)
	{
		$order = $this->order->read_by_order_id($order_id);
		$account_id = $order['account_id'];
		$year = isset($_GET['year']) ? (int) $_GET['year'] : date("Y");
		//$file_name = $this->get_filename_path_by_order( $account_id, $order[ 'uniquecode' ], $order[ 'invoice_number' ], $order[ 'date_delivery' ] );
		$time = strtotime($order['date_delivery']);
		$date_order = date('Y-m-d', $time);
		$invoice_number = isset($order['invoice_number']) ? $order['invoice_number'] : $order['number'];

		$baseDir = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR;
		if (!file_exists($baseDir)) {
			mkdir($baseDir, 0755);
		}
		switch ($what) {
			case self::INVOICE:
				if (null !== $where) {
					$file_name = $where . "Fatt{$year}_{$invoice_number}.xml";
					$url = str_replace(FCPATH, base_url(), $file_name);
					$url = str_replace('\\', '/', $url);
				} else {
					$dir = $baseDir . $this->baseDirInvoices . "-" . $year . DIRECTORY_SEPARATOR;
					if (!file_exists($dir)) {
						mkdir($dir, 0755);
					}
					$file_name = $dir . "Fatt{$year}_{$invoice_number}.xml";
					$url = base_url() . $this->baseDir . "/" . $this->baseDirInvoices . "-" . $year . "/" . "Fatt{$year}_{$invoice_number}.xml";
				}
				break;
			case self::CREDIT_NOTE:
				$credit_note = $this->credit_note->read_by_order_id($order_id);
				$__year = isset($credit_note['date_credit_note']) ? (int) strftime("%Y", strtotime($credit_note['date_credit_note'])) : 0;
				$credit_note_number = $credit_note['number'];
				if (null !== $where) {
					$file_name = $where . "CNote{$year}_{$credit_note_number}.xml";
					$url = str_replace(FCPATH, base_url(), $file_name);
					$url = str_replace('\\', '/', $url);
				} else {
					$dir = $baseDir . $this->baseDirCreditNotes . "-" . $year . DIRECTORY_SEPARATOR;
					if (!file_exists($dir)) {
						mkdir($dir, 0755);
					}
					$file_name = $dir . "CNote{$year}_{$credit_note_number}.xml";
					$url = base_url() . $this->baseDir . "/" . $this->baseDirCreditNotes . "-" . $year . "/" . "CNote{$year}_{$credit_note_number}.xml";
				}
				break;
			default:
				die('Error');
		}

		try {
			$result = $this->{"get_{$what}_xml"}($order_id);
		} catch (Exception $e) {
			if ($show_error) {
				_pre($e->getMessage());
			}

			/*return array(
				'result' => 0,
				'message' => $e->getMessage(),
			);*/

			return false;
		}

		$xml = $result['xml'];
		$xml = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xml);

		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);
		libxml_use_internal_errors(true);
		if (!$dom->schemaValidate(FCPATH . "Schema_del_file_xml_FatturaPA_versione_1.2.1.xsd")) {
			//print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
			$errors = libxml_get_errors();
			if ($show_error) {
				_pre($errors);
				_pre($order);
				echo "<pre>" . $dom->saveXML() . "</pre>";
			}

			/*return array(
				'result' => 0,
				'message' => $dom->saveXML(),
				'error' => json_encode( $errors ),
				'order' => json_encode( $order ),
			);*/

			return false;
		}
		$validatedXml = $dom->saveXML();
		$result = file_put_contents($file_name, $validatedXml);

		return array('result' => 1, 'order_id' => $order_id, 'file_name' => $file_name, 'url' => $url);
	}

	/**
	 * @param $account_id
	 * @param $uniquecode
	 * @param $invoice_number
	 * @param $date_order
	 *
	 * @return string
	 */
	public function get_filename_path_by_order($account_id, $uniquecode, $invoice_number, $date_order)
	{
		$time = strtotime($date_order);
		$date_order = date('Y-m-d', $time);
		$year = isset($_GET['year']) ? (int) $_GET['year'] : date("Y");
		$baseEInvoiceDir = FCPATH . "e-invoices";
		if (!file_exists($baseEInvoiceDir)) {
			mkdir($baseEInvoiceDir, 0755);
		}
		$baseEInvoiceDir .= DIRECTORY_SEPARATOR . $year;
		if (!file_exists($baseEInvoiceDir)) {
			mkdir($baseEInvoiceDir, 0755);
		}
		$baseEInvoiceAccountDir = $baseEInvoiceDir . DIRECTORY_SEPARATOR . $account_id;
		if (!file_exists($baseEInvoiceAccountDir)) {
			mkdir($baseEInvoiceAccountDir, 0755);
		}
		$filenameEInvoicePath = $baseEInvoiceAccountDir . DIRECTORY_SEPARATOR . "{$invoice_number}-{$uniquecode}-{$date_order}.xml";

		return $filenameEInvoicePath;
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	protected function delete_path($path)
	{
		if (!file_exists($path)) {
			return false;
		}
		$dir = opendir($path);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				$full = $path . '/' . $file;
				if (is_dir($full)) {
					rrmdir($full);
				} else {
					unlink($full);
				}
			}
		}
		closedir($dir);
		rmdir($path);
	}

	/**
	 * @param type $dir
	 *
	 * @return array|type
	 */
	protected function _zip_dir($dir)
	{
		$this->load->library('zip');
		$time = time();
		$this->zip->read_dir($dir, false);
		if (ob_get_contents()) {
			ob_end_clean();
		}
		$pathFileZip = $dir . 'backup_' . $time . '.zip';
		$urlFileZip = str_replace(FCPATH, base_url(), $pathFileZip);
		$urlFileZip = str_replace('\\', '/', $urlFileZip);

		return array($this->zip->archive($pathFileZip), $pathFileZip, $urlFileZip);
	}

	/**
	 * @param $folder
	 */
	protected function _delete_dir($folder)
	{
		$files = glob($folder . '/*');
		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
	}

	/**
	 * @param $orders
	 * @param $target_path
	 * @param $what
	 *
	 * @return array
	 * @throws Exception
	 */
	public function create_xml_files($orders, $target_path, $what)
	{
		if (
			$what !== self::INVOICE
			&& $what !== self::CREDIT_NOTE
		) {
			throw new Exception("Fatal Error what parameter");
		}

		if (count($orders['results']) <= 0) {
			throw new Exception("No result for this query");
		}

		$i = 1;
		foreach ($orders['results'] as $order) {
			$result = $this->validate_and_create_einvoice($order['order_id'], $what, $target_path, false);
			if (!$result) {
				throw new Exception("Error in the Elettronic Invoice N. {$order['invoice_number']}");
			}
			$result['count'] = $i;
			$i++;
		}

		$result_array = $this->_zip_dir($target_path);

		return $result_array;
	}
}
