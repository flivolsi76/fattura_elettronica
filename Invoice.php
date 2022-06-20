<?php
if ( ! defined( 'BASEPATH' ) ) {
	exit( 'No direct script access allowed' );
}

require __DIR__ . DIRECTORY_SEPARATOR . "Entities" . DIRECTORY_SEPARATOR . "autoload.php";
require 'Abstract_einvoice.php';

/**
 * Class E-Invoice
 *
 * Create and Send email:
 * http://www.pneusgroup.it/einvoice/invoice/create
 *
 * http://pneusgroup.it.localhost/einvoice/invoice/print_orders
 * http://pneusgroup.it.localhost//einvoice/invoice/show/[order_id]/[show{0,1}]
 */
class Invoice extends Abstract_einvoice {

	/**
	 * B2b constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @param $order_id
	 * @param bool $show
	 *
	 * @throws Exception
	 */
	public function show( $order_id, $show = false ) {
		$order = $this->order->read_by_order_id( $order_id );
		$account_id = $order['account_id'];

		$result = $this->get_invoice_xml( $order_id );
		$xml = $result['xml'];
		$xml = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $xml );

		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML( $xml );

		libxml_use_internal_errors( true );
		if ( ! $dom->schemaValidate( FCPATH . DIRECTORY_SEPARATOR . "Schema_del_file_xml_FatturaPA_versione_1.2.1.xsd" ) ) {
			print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
			$errors = libxml_get_errors();
			_pre( $errors );
			echo "<pre>XML</pre>";
			echo "<pre>" . $dom->saveXML() . "</pre>";
			die;
		}

		$validatedXml = $dom->saveXML();
		$data['xml'] = $validatedXml;
		$data['showOnScreen'] = $show;

		$this->load->view( THEME . '/einvoice/xml', $data );
	}

	/**
	 * @param $order_id
	 */
	public function download( $order_id ) {
		$order = $this->order->read_by_order_id( $order_id );
		$result = $this->validate_and_create_einvoice( $order['order_id'], self::INVOICE );
		if ( ! $result ) {
			echo json_encode( array( 'result' => 0 ) );
		}
		echo json_encode( $result );
	}

	/**
	 * CREATE E-INVOICES
	 */
	public function create() {
		$year = isset( $_GET['year'] ) ? (int) $_GET['year'] : date( "Y" );

		if ( isset( $_GET['force'] )
			&& ( $_GET['force'] == 'true' || $_GET['force'] == 'false' )
		) {
			$this->setCreateWithoutSdiOrPec( $_GET['force'] );
		}

		//status in ('" . DELIVERY_IN_PROGRESS . "','" . DELIVERY_ACCOMPLISHED . "')
		$invoice_number_condition = $year < 2018 ? "AND invoice_number is null" : "AND invoice_number is not null";
		$order_condition = $year < 2018 ? " order by date_delivery desc " : " order by invoice_number desc ";
		$orders = $this->order->read_all_limit(
			99999, 1, " AND YEAR(date_delivery) = ? {$invoice_number_condition}", $order_condition, array(
				$year,
			)
		);

		$baseEInvoiceDir = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR . $this->baseDirInvoices . "-" . $year;
		$this->delete_path( $baseEInvoiceDir );

		$result_array = null;
		try {
			$result_array = $this->create_xml_files( $orders, $baseEInvoiceDir, self::INVOICE );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		if ( null !== $result_array ) {
			$this->load->model( 'mail_model', 'mail' );
			$attachment_path = $result_array[1];
			$attachment_url = $result_array[2];

			$result = $this->mail->sendMail( SENDER_MAIL, 'magikboo23@gmail.com', self::INVOICE, 'In allegato file fatture in formato zip ' . $year . ' Url:' . $attachment_url, $attachment_path );
			echo "<br>Sending email...... {$result}";
		}

		echo "<br>DONE";
	}

	/**
	 * @param bool $send_email
	 */
	public function create_per_day( $send_email = false ) {
		$today = date( 'Y-m-d' );
		$date = isset( $_GET['date'] ) ? date( 'Y-m-d', strtotime( $_GET['date'] ) ) : $today;

		$orders = $this->order->read_all_limit( 99999, 1, " AND DATE(date_delivery) = ? AND invoice_number is not null ", " order by invoice_number asc", array( $date ) );

		if ( ! isset( $orders ) || count( $orders['results'] ) === 0 ) {
			die( 'No orders for ' . $date );
		}

		$dir = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$dir = $dir . '_temp' . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$baseDirInvoices = $dir . $this->baseDirInvoices . "_" . $date . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $baseDirInvoices ) ) {
			mkdir( $baseDirInvoices, 0755 );
		}

		$this->_delete_dir( $baseDirInvoices );

		$result_array = null;
		try {
			$result_array = $this->create_xml_files( $orders, $baseDirInvoices, self::INVOICE );
		} catch ( Exception $e ) {
			$this->delete_path( $baseDirInvoices );
			echo json_encode( array( 'result' => 0, 'message' => $e->getMessage() ) );
			die;
		}

		if ( $send_email && null !== $result_array ) {
			$this->load->model( 'mail_model', 'mail' );
			$attachment_path = $result_array[1];
			$attachment_url = $result_array[2];

			$result = $this->mail->sendMail( SENDER_MAIL, 'magikboo23@gmail.com', self::INVOICE, 'In allegato file fatture in formato zip ' . $date . ' Url:' . $attachment_url, $attachment_path );
		}

		echo json_encode( array( 'result' => $result_array[0], 'url' => $result_array[1] ) );
	}

	/**
	 * @param bool $send_email
	 */
	public function create_by_day( $send_email = false ) {
		$today = date( 'Y-m-d' );
		$da = isset( $_GET['da'] ) ? date( 'Y-m-d', strtotime( $_GET['da'] ) ) : $today;
		$a = isset( $_GET['a'] ) ? date( 'Y-m-d', strtotime( $_GET['a'] ) ) : $today;

		$orders = $this->order->read_all_limit(
			99999, 1, " AND DATE(date_delivery) >= ? AND DATE(date_delivery) <= ? AND invoice_number is not null ", " order by invoice_number asc", array(
			$da,
			$a,
		)
		);

		if ( ! isset( $orders ) || count( $orders['results'] ) === 0 ) {
			die( 'No orders for ' . $da . " - " . $a );
		}

		$dir = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$dir = $dir . '_temp' . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$baseDirInvoices = $dir . $this->baseDirInvoices . "_" . $da . "--" . $a . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $baseDirInvoices ) ) {
			mkdir( $baseDirInvoices, 0755 );
		}

		$this->_delete_dir( $baseDirInvoices );

		$result_array = null;
		try {
			$result_array = $this->create_xml_files( $orders, $baseDirInvoices, self::INVOICE );
		} catch ( Exception $e ) {
			$this->delete_path( $baseDirInvoices );
			echo json_encode( array( 'result' => 0, 'message' => $e->getMessage() ) );
			die;
		}

		if ( $send_email && null !== $result_array ) {
			$this->load->model( 'mail_model', 'mail' );
			$attachment_path = $result_array[1];
			$attachment_url = $result_array[2];

			$result = $this->mail->sendMail( SENDER_MAIL, 'magikboo23@gmail.com', self::INVOICE, 'In allegato file fatture in formato zip ' . $date . ' Url:' . $attachment_url, $attachment_path );
		}

		echo json_encode( array( 'result' => $result_array[0], 'url' => $result_array[1] ) );
	}
}