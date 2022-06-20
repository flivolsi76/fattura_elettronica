<?php

/**
 * Class DatiPagamento
 */
class DatiPagamento extends BaseEntity {

    //CondizioniPagamentoType
    const PAGAMENTO_A_RATE = 'TP01';
    const PAGAMENTO_COMPLETO = 'TP02';

    //ModalitaPagamentoType
    const CONTANTI = 'MP01';
    const ASSEGNO = 'MP02';
    const BONIFICO = 'MP05';
    const CARTA_DI_PAGAMENTO = 'MP08';
    const RID = 'MP09';
    const RIBA = 'MP12';
    const DOMICILIAZIONE_BANCARIA = 'MP16';
    const TRATTENUTA_SU_SOMME_GIA_RISCOSSE = 'MP22';


	/**
	 * allowed values:
	 * [TP01]: Payment by instalments
	 * [TP02]: full payment
	 * [TP03]: advance payment
	 *
	 * @var string
	 */
	private $CondizioniPagamento;
	/**
	 * MP01        cash
	 * MP02        cheque
	 * MP03        banker's draft
	 * MP04        cash at Treasury
	 * MP05        bank transfer
	 * MP06        money order
	 * MP07        pre-compiled bank payment slip
	 * MP08        payment card
	 * MP09        direct debit
	 * MP10        utilities direct debit
	 * MP11        fast direct debit
	 * MP12        collection order
	 * MP13        payment by notice
	 * MP14        tax office quittance
	 * MP15        transfer on special accounting accounts
	 * MP16        order for direct payment from bank account
	 * MP17        order for direct payment from post office account
	 * MP18        bulletin postal account
	 * MP19        SEPA Direct Debit
	 * MP20        SEPA Direct Debit CORE
	 * MP21        SEPA Direct Debit B2B
	 * MP22        Deduction on sums already collected
	 *
	 * @var string
	 */
	private $ModalitaPagamento;
	/**
	 * @var date {Y-m-d}
	 */
	private $DataScadenzaPagamento;
	/**
	 * @var float
	 */
	private $ImportoPagamento;


	/**
	 * DatiPagamento constructor.
	 *
	 * @param $CondizioniPagamento
	 * @param $ModalitaPagamento
	 * @param $DataScadenzaPagamento
	 * @param $ImportoPagamento
	 */
	public function __construct( $CondizioniPagamento, $ModalitaPagamento, $DataScadenzaPagamento, $ImportoPagamento ) {
		$this->CondizioniPagamento = $CondizioniPagamento;
		$this->ModalitaPagamento = $ModalitaPagamento;
		$this->DataScadenzaPagamento = $DataScadenzaPagamento;
		$this->ImportoPagamento = $ImportoPagamento;
	}

	public function getXML() {
		ob_start();
		?>
        <DatiPagamento>
            <CondizioniPagamento><?php echo $this->CondizioniPagamento; ?></CondizioniPagamento>
            <DettaglioPagamento>
                <ModalitaPagamento><?php echo $this->ModalitaPagamento; ?></ModalitaPagamento>
                <DataScadenzaPagamento><?php echo $this->DataScadenzaPagamento; ?></DataScadenzaPagamento>
                <ImportoPagamento><?php echo $this->ImportoPagamento; ?></ImportoPagamento>
            </DettaglioPagamento>
        </DatiPagamento>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public function getCondizioniPagamento() {
		return $this->CondizioniPagamento;
	}

	/**
	 * @param mixed $CondizioniPagamento
	 */
	public function setCondizioniPagamento( $CondizioniPagamento ) {
		$this->CondizioniPagamento = $CondizioniPagamento;
	}

	/**
	 * @return mixed
	 */
	public function getModalitaPagamento() {
		return $this->ModalitaPagamento;
	}

	/**
	 * @param mixed $ModalitaPagamento
	 */
	public function setModalitaPagamento( $ModalitaPagamento ) {
		$this->ModalitaPagamento = $ModalitaPagamento;
	}

	/**
	 * @return mixed
	 */
	public function getDataScadenzaPagamento() {
		return $this->DataScadenzaPagamento;
	}

	/**
	 * @param mixed $DataScadenzaPagamento
	 */
	public function setDataScadenzaPagamento( $DataScadenzaPagamento ) {
		$this->DataScadenzaPagamento = $DataScadenzaPagamento;
	}

	/**
	 * @return mixed
	 */
	public function getImportoPagamento() {
		return $this->ImportoPagamento;
	}

	/**
	 * @param mixed $ImportoPagamento
	 */
	public function setImportoPagamento( $ImportoPagamento ) {
		$this->ImportoPagamento = $ImportoPagamento;
	}
}