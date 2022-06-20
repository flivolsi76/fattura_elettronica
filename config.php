<?php
//Elettronic-Invoice
define( "IdPaese", "IT" );
define( "IdCodice", "03780080366" );
define( "CodiceFiscale", "03780080366" );
define( "Denominazione", "Pneus Group S.r.l." );
define( "RegimeFiscale", "RF01" );
define( "Indirizzo", "VIA MAZZINI 182/F" );
define( "ECAP", "41049" );
define( "Comune", "SASSUOLO" );
define( "Provincia", "MO" );
define( "Nazione", "IT" );
define( "Telefono", "0536-948605" );
define( "Email", "info@pneusgroupsrl.it" );

if ( ! defined( "CREDIT_NOTE_PREFIX" ) ) {
	define( "CREDIT_NOTE_PREFIX", 100000 );
}

/**
 * @param $tipoPagamentoOrdine
 * @param $dateInvoice
 *
 * @return array
 */
function getPagamentoInfo( $tipoPagamentoOrdine, $dateInvoice ) {
	switch ( $tipoPagamentoOrdine ) {
		default:
			$modalita_pagamento = DatiPagamento::BONIFICO;
			break;
	}
	$dataScadenzaPagamento = $dateInvoice;

	return array( $modalita_pagamento, $dataScadenzaPagamento );
}