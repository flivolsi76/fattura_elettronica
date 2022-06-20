<?php
/**
 * Fattura Elettronica 1.0 by Francesco Li Volsi flivolsi76 (et) gmail.com
 */
require "autoload.php";

/**
 * @param SimpleXMLElement $xml
 * @return void
 */
function prettyPrintXmlToBrowser(SimpleXMLElement $xml)
{
    $domXml = new DOMDocument('1.0');
    $domXml->preserveWhiteSpace = false;
    $domXml->formatOutput = true;
    $domXml->loadXML($xml->asXML());
    $xmlString = $domXml->saveXML();
    echo nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($xmlString)));
}

/**
 * @param $value
 * @return void
 */
function _pre($value) {
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

//Elettronic-Invoice
define( "IdPaese", "IT" );
define( "IdCodice", "12345678901" );
define( "CodiceFiscale", "12345678901" );
define( "Denominazione", "XXXXXXXX" );
define( "RegimeFiscale", "RF01" );
define( "Indirizzo", "XXXXXXXXXXXXX" );
define( "ECAP", "12345" );
define( "Comune", "XXXXXXXX" );
define( "Provincia", "XX" );
define( "Nazione", "IT" );
define( "Telefono", "XXXXXXXXX" );
define( "Email", "info@seller.it" );

$IVA = '22.00';

//Estero XXXXXXX
//Italia ABCDEFG
$codiceDestinatario = 'XXXXXXX';
$PECDestinatario = "pec_destinatario@pec.it";
$productArray = array(
    'NumeroLinea' => 1,
    'CodiceTipo' => 'PROPRIETARIO',
    'CodiceValore' => 'Numero fattura 01',
    'Descrizione' => 'Descrizione prd',
    'Quantita' => 2,
    'UnitaMisura' => 'PZ',
    'PrezzoUnitario' => '100.00',
    'PrezzoTotale' => '200.00',
    'AliquotaIVA' => $IVA,
);
//Per estero
//$productArray['Natura'] = 'N3.2';

$products[] = $productArray;

$dateInvoice = date("Y-m-d");

$ImponibileImportoIvaEsclusa = 200.00;
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
//Se Estero
//$summaryData['Natura'] = 'N3.2';
//$summaryData['RiferimentoNormativo'] = "N.I. art. 41 D.L. 331/93";

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

$sellerNation = 'IT';
$vatOnlyNumbers = '12345678901';
$codiceFiscale = 'XXXXXX0XXXXXX';
$sellerName = 'XXXXXXXXXXXX';
$sellerAddress = 'XXX XXXXXXXXXX';
$sellerCap = '12345';
$sellerCity = 'XXXXXXXX';
$sellerPrv = 'XX';
$buyer = array(
    'IdPaese' => $sellerNation,
    'IdCodice' => $vatOnlyNumbers,
    'CodiceFiscale' => $codiceFiscale,
    'Denominazione' => $sellerName,
    'Indirizzo' => $sellerAddress,
    'CAP' => $sellerCap,
    'Comune' => $sellerCity,
    'Provincia' => $sellerPrv,
    'Nazione' => $sellerNation,
    'Telefono' => '',
    'Email' => '',
);

$invoiceNumber = "1";

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

$paymentData = array(
    'CondizioniPagamento' => DatiPagamento::PAGAMENTO_COMPLETO,
    'ModalitaPagamento' => DatiPagamento::BONIFICO,
    'DataScadenzaPagamento' => date('Y-m-d', strtotime('+30 days')),
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

//Validation
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($fatturaElettronica->getXML());
libxml_use_internal_errors(true);
if (!$dom->schemaValidate("Schema_del_file_xml_FatturaPA_versione_1.2.1.xsd")) {
    //print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
    $errors = libxml_get_errors();
    _pre($errors);
    echo "<pre>" . $dom->saveXML() . "</pre>";
}
$validatedXml = $dom->saveXML();

$xml = simplexml_load_string($fatturaElettronica->getXML());
prettyPrintXmlToBrowser($xml);