<?php

namespace OpenBoleto\Layout;

use OpenBoleto\Layout as BaseLayout;

class Itau extends BaseLayout {
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $codigoBanco = '341';
    
    protected $agencia = '';
    
    protected $conta = '';
    
    protected $dacConta = '';
    
    protected $localPagamento = "ATÉ O VENCIMENTO PAGUE PREFERENCIALMENTE NO ITAÚ\nAPÓS O VENCIMENTO PAGUE SOMENTE NO ITAÚ";

    public function __construct(array $params = array(), $gerarReciboEntrega = false) {
		parent::__construct($params, $gerarReciboEntrega);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $s
	 */
	public function getLinhaDigitavel() {
		$codigoBanco     = $this->get('codigoBanco');
		$codigoMoeda     = $this->get('codigoMoeda');
        $carteira        = $this->get('carteira');
		$nossoNumero     = str_pad($this->get('nossoNumero'), 8, '0', STR_PAD_LEFT);
		$fatorVencimento = str_pad($this->getFatorVencimento($this->get('dataVencimento')), 4, '0', STR_PAD_LEFT);
		$valor           = str_pad(number_format($this->get('valorDocumento'), 2, '', ''), 10, '0', STR_PAD_LEFT);
        $agencia         = str_pad($this->get('agencia'), 4, '0', STR_PAD_LEFT);
        $conta           = str_pad($this->get('conta'), 5, '0', STR_PAD_LEFT);
        $dacConta        = $this->get('dacConta');
		$codigoBarra     = $this->getCodigoBarra();
		
		$c1a = sprintf('%s%s%s%s', $codigoBanco, $codigoMoeda, $carteira, substr($nossoNumero, 0, 2));
		$c1b = sprintf('%s%s', $c1a, self::modulo10($c1a));
  		$c1 = sprintf('%s.%s', substr($c1b, 0, 5), substr($c1b, 5));
        
        $dacAgContaCarteiraNossoNumero = self::modulo10($agencia.$conta.$carteira.$nossoNumero);
		$c2a = sprintf('%s%s%s', 
            substr($nossoNumero, -6), 
            $dacAgContaCarteiraNossoNumero,
            substr($agencia, 0, 3)
        );
        $c2b = sprintf('%s%s', $c2a, self::modulo10($c2a));
  		$c2 = sprintf('%s.%s', substr($c2b, 0, 5), substr($c2b, 5));
  		
  		$c3a = sprintf('%s%s%d000', substr($agencia, -1), $conta, $dacConta);
		$c3b = sprintf('%s%s', $c3a, self::modulo10($c3a));
  		$c3 = sprintf('%s.%s', substr($c3b, 0, 5), substr($c3b, 5));
  		
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
		$codigoBanco        = $this->get('codigoBanco');
		$numeroMoeda        = $this->get('codigoMoeda');
        $carteira           = $this->get('carteira');
        $nossoNumero        = str_pad($this->get('nossoNumero'), 8, '0', STR_PAD_LEFT);
		$fatorVencimento    = $this->getFatorVencimento($this->get('dataVencimento'));
		$valor              = str_pad(number_format($this->get('valorDocumento'), 2, '', ''), 10, '0', STR_PAD_LEFT);
        $agencia            = str_pad($this->get('agencia'), 4, '0', STR_PAD_LEFT);
        $conta              = str_pad($this->get('conta'), 5, '0', STR_PAD_LEFT);
		
        if (in_array($carteira, array(126, 131, 146, 150, 168))) {
            $dacAgenciaContaCarteiraNossoNumero = self::modulo10($carteira.$nossoNumero);
        }
        else {
            $dacAgenciaContaCarteiraNossoNumero = self::modulo10($agencia.$conta.$carteira.$nossoNumero);
        }
        
        $dacAgenciaConta = self::modulo10($agencia.$conta);
		
		$_barra = sprintf('%s%s%s%s%s%s%s%s%s%s000', $codigoBanco, $numeroMoeda, 
            $fatorVencimento, $valor, $carteira, $nossoNumero, $dacAgenciaContaCarteiraNossoNumero,
            $agencia, $conta, $dacAgenciaConta);
		$_barraDv = self::getDigitoVerificadorBarra($_barra);
		
		$barra = sprintf('%s%d%s', substr($_barra, 0, 4), $_barraDv, substr($_barra, 4));
		
		return $barra;
	}
}