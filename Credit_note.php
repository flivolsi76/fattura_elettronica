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
 * http://www.pneusgroup.it/einvoice/credit_note/create
 *
 * http://pneusgroup.it.localhost/einvoice/invoice/print_orders
 * http://pneusgroup.it.localhost//einvoice/invoice/show/[order_id]/[show{0,1}]
 */
class Credit_note extends Abstract_einvoice {

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
	 * @return bool
	 */
	public function show( $order_id, $show = false ) {
		$order = $this->order->read_by_order_id( $order_id );
		$account_id = $order['account_id'];

		try {
			$result = $this->get_credit_note_xml( $order_id );
		} catch ( Exception $e ) {
			_pre( $e->getMessage() );

			return false;
		}
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
		$result = $this->validate_and_create_einvoice( $order['order_id'], self::CREDIT_NOTE );
		if ( ! $result ) {
			echo json_encode( array( 'result' => 0 ) );
		}
		echo json_encode( $result );
	}

	/**
	 * CREATE E-CREDITNOTE
	 */
	public function create() {
		$year = isset( $_GET['year'] ) ? (int) $_GET['year'] : date( "Y" );
		if ( isset( $_GET['force'] )
			&& ( $_GET['force'] == 'true' || $_GET['force'] == 'false' )
		) {
			$this->setCreateWithoutSdiOrPec( $_GET['force'] );
		}

		$orders = $this->order->read_all_limit(
			99999, 1, " AND order_id in (select order_id from credit_note where YEAR(date_credit_note) = ? )", " order by date_delivery desc", array(
				$year,
			)
		);

		$baseDirCreditNotes = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR . $this->baseDirCreditNotes . "-" . $year;
		$this->delete_path( $baseDirCreditNotes );

		$result_array = null;
		try {
			$result_array = $this->create_xml_files( $orders, $baseDirCreditNotes, self::CREDIT_NOTE );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		if ( null !== $result_array ) {
			$this->load->model( 'mail_model', 'mail' );
			$attachment_path = $result_array[1];
			$attachment_url = $result_array[2];

			$result = $this->mail->sendMail( SENDER_MAIL, 'magikboo23@gmail.com', self::CREDIT_NOTE, 'In allegato file note di credito in formato zip ' . $year . ' Url:' . $attachment_url, $attachment_path );
			echo "<br>Sending email...... {$result}";
		}

		echo "<br>DONE";
	}

	/**
	 * Create per day
	 */
	public function create_per_day( $send_email = false ) {
		$today = date( 'Y-m-d' );
		$date = isset( $_GET['date'] ) ? date( 'Y-m-d', strtotime( $_GET['date'] ) ) : $today;
		$orders = $this->order->read_all_limit( 99999, 1, " AND order_id in (select order_id from credit_note where DATE(date_credit_note) = ?)", " order by date_delivery desc", array( $date ) );

		$dir = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$dir = $dir . '_temp' . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$baseDirCreditNotes = $dir . $this->baseDirCreditNotes . "_" . $date . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $baseDirCreditNotes ) ) {
			mkdir( $baseDirCreditNotes, 0755 );
		}

		$this->_delete_dir( $baseDirCreditNotes );

		$result_array = null;
		try {
			$result_array = $this->create_xml_files( $orders, $baseDirCreditNotes, self::CREDIT_NOTE );
		} catch ( Exception $e ) {
			$this->delete_path( $baseDirCreditNotes );
			echo json_encode( array( 'result' => 0, 'message' => $e->getMessage() ) );
			die;
		}

		if ( $send_email && null !== $result_array ) {
			$this->load->model( 'mail_model', 'mail' );
			$attachment_path = $result_array[1];
			$attachment_url = $result_array[2];

			$result = $this->mail->sendMail( SENDER_MAIL, 'magikboo23@gmail.com', self::CREDIT_NOTE, 'In allegato file note di credito in formato zip ' . $date . ' Url:' . $attachment_url, $attachment_path );
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
			99999, 1, " AND order_id in (select order_id from credit_note where DATE(date_credit_note) >= ? AND DATE(date_credit_note) <= ?)", " order by date_delivery desc", array(
			$da,
			$a,
		)
		);

		$dir = FCPATH . $this->baseDir . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$dir = $dir . '_temp' . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755 );
		}

		$baseDirCreditNotes = $dir . $this->baseDirCreditNotes . "_" . $da . "--" . "$a" . DIRECTORY_SEPARATOR;
		if ( ! file_exists( $baseDirCreditNotes ) ) {
			mkdir( $baseDirCreditNotes, 0755 );
		}

		$this->_delete_dir( $baseDirCreditNotes );

		$result_array = null;
		try {
			$result_array = $this->create_xml_files( $orders, $baseDirCreditNotes, self::CREDIT_NOTE );
		} catch ( Exception $e ) {
			$this->delete_path( $baseDirCreditNotes );
			echo json_encode( array( 'result' => 0, 'message' => $e->getMessage() ) );
			die;
		}

		if ( $send_email && null !== $result_array ) {
			$this->load->model( 'mail_model', 'mail' );
			$attachment_path = $result_array[1];
			$attachment_url = $result_array[2];

			$result = $this->mail->sendMail( SENDER_MAIL, 'magikboo23@gmail.com', self::CREDIT_NOTE, 'In allegato file note di credito in formato zip ' . $date . ' Url:' . $attachment_url, $attachment_path );
		}

		echo json_encode( array( 'result' => $result_array[0], 'url' => $result_array[1] ) );
	}
}