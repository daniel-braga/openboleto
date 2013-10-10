<?php

namespace OpenBoleto\Layout;

use OpenBoleto\Layout as BaseLayout;

class Santander extends BaseLayout {
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $codigoBanco = '033';
	
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @var unknown_type
	 */
	protected $carteira = '102';
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $pontoVenda = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $descricaoCarteira = array (
		'101' => 'COBRANCA SIMPLES ECR',
		'102' => 'COBRANÇA SIMPLES CSR'
	);
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $ios = '0';
	
	public function __construct(array $params = array()) {
		parent::__construct($params);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $s
	 */
	public function getLinhaDigitavel() {
		$codigoBanco     = $this->get('codigoBanco');
		$codigoMoeda     = $this->get('codigoMoeda');
		$codigoBeneficiario   = str_pad($this->get('codigoBeneficiario'), 7, '0', STR_PAD_LEFT);
		$nossoNumero     = str_pad($this->get('nossoNumero'), 13, '0', STR_PAD_LEFT);
		$ios             = $this->get('ios');
		$carteira        = $this->get('carteira');
		$fatorVencimento = str_pad($this->getFatorVencimento($this->get('dataVencimento')), 4, '0', STR_PAD_LEFT);
		$valor           = str_pad(number_format($this->get('valorDocumento'), 2, '', ''), 10, '0', STR_PAD_LEFT);
		
		$codigoBarra     = $this->getCodigoBarra();
		
		$c1 = sprintf('%s%s%s%s', $codigoBanco, $codigoMoeda, 9, substr($codigoBeneficiario, 0, 4));
		$c1 = sprintf('%s%s', $c1, self::modulo10($c1));
  		$c1 = sprintf('%s.%s', substr($c1, 0, 5), substr($c1, 5));
  		
  		$c2 = sprintf('%s%s', substr($codigoBeneficiario, -3), substr($nossoNumero, 0, 7));
		$c2 = sprintf('%s%s', $c2, self::modulo10($c2));
  		$c2 = sprintf('%s.%s', substr($c2, 0, 5), substr($c2, 5));
  		
  		$c3 = substr($s, 34, 10);
  		$c3 = sprintf('%s%s%s', substr($nossoNumero, -6), $ios, $carteira);
		$c3 = sprintf('%s%s', $c3, self::modulo10($c3));
  		$c3 = sprintf('%s.%s', substr($c3, 0, 5), substr($c3, 5));
  		
  		$c4 = substr($codigoBarra, 4, 1);
  		
  		$c5 = sprintf('%s%s', $fatorVencimento, $valor);
  		
  		$linha = sprintf('%s %s %s %s %s', $c1, $c2, $c3, $c4, $c5); 
  		
  		return $linha;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getCodigoBarra() 
	{
		$codigoBanco     = $this->get('codigoBanco');
		$numeroMoeda     = $this->get('codigoMoeda');
		$fatorVencimento = $this->getFatorVencimento($this->get('dataVencimento'));
		$valor           = str_pad(number_format($this->get('valorDocumento'), 2, '', ''), 10, '0', STR_PAD_LEFT);
		$codigoBeneficiario   = str_pad($this->get('codigoBeneficiario'), 7, '0', STR_PAD_LEFT);
		$nossoNumero     = str_pad($this->get('nossoNumero'), 13, '0', STR_PAD_LEFT);
        //$nossoNumero = $this->get('nossoNumero');
		$ios             = $this->get('ios');
		$carteira        = $this->get('carteira');
		
		$_barra = sprintf('%s%s%s%s%s%s%s%s%s', $codigoBanco, $numeroMoeda, $fatorVencimento, $valor, 9, $codigoBeneficiario, $nossoNumero, $ios, $carteira);
		$_barraDv = self::getDigitoVerificadorBarra($_barra);
		
		$barra = sprintf('%s%d%s', substr($_barra, 0, 4), $_barraDv, substr($_barra, 4));
		
		return $barra;
	}
}