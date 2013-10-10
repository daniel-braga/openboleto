<?php

namespace OpenBoleto;

abstract class Layout {
	
	/**
	 * Código do banco 
	 * 
	 * @var string
	 */
	protected $codigoBanco = '';
	
	/**
	 * 	
	 * Enter description here ...
	 * 
	 * @var string
	 */
	protected $codigoMoeda = '9';
	
	/**
	 * 
	 * Nosso número
	 * 
	 * @var string
	 */
	protected $nossoNumero = '';
	
	/**
	 *  
	 * Número do documento
	 * 
	 * @var string
	 */
	protected $numeroDocumento = '';
	
	/**
	 *  
	 * Local de Pagamento
	 * 
	 * @var string
	 */
	protected $localPagamento = 'PAGÁVEL EM QUALQUER BANCO ATÉ O VENCIMENTO';
	
	/**
	 * 
	 * Data de vencimento
	 * 
	 * @var DateTime
	 */
	protected $dataVencimento;
	
	/**
	 * 
	 * Data do documento
	 * 
	 * @var DateTime
	 */
	protected $dataDocumento;
	
	/**
	 * 
	 * Data de processamento
	 * 
	 * @var DateTime
	 */
	protected $dataProcessamento;
	
	/**
	 * 
	 * Valor do Boleto
	 * 
	 * @param float
	 */
	protected $valorDocumento = 0;
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $nomePagador = '';
    
    /**
     *
     * @var string 
     */
    protected $cpfCnpjPagador = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $enderecoPagador = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $demonstrativo = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $instrucao = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var float
	 */	
	protected $quantidade = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var float
	 */
	protected $valorUnitario = '';

	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $aceite = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $especieMoeda = 'R$';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $especieDocumento = 'DM';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string	 
	 */
	protected $carteira = '';

	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $identificacao = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $codigoBeneficiario = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $cpfCnpjBeneficiario = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $enderecoBeneficiario = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $cidadeBeneficiario = '';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $estadoBeneficiario = '';
	
	/**
	 * 
	 * Nome do Beneficiário
	 * @var string
	 */
	protected $beneficiario = '';
	
	/**
	 * 
	 * Sacador Avalista
	 * @var string
	 */
	protected $nomeSacadorAvalista = '';
    
    /**
	 * 
	 * CNPJ Sacador Avalista
	 * @var string
	 */
	protected $cnpjSacadorAvalista = '';
    
    /**
     *
     * @var boolean
     */
    protected $gerarReciboEntrega = false;
	
	/**
	 * 
	 * Construtor
	 * 
	 * @param array $params
	 */
	public function __construct(array $params = array(), $gerarReciboEntrega = false) {
        
        $this->gerarReciboEntrega = $gerarReciboEntrega;
        
		foreach ($params as $paramName => $paramValue) {
			if (property_exists($this, $paramName))	{
				$this->$paramName = $paramValue;
			}
		}
	}
	
	/**
	 * 
	 * Método para acessar as propriedades
	 * 
	 * @param string $property
	 * @throws Exception
	 * @return mixed
	 */
	public function get($property) {
		if (property_exists($this, $property))	{
			return $this->$property;
		}
		
		throw new \Exception(sprintf('Propriedade %s não definida em %s', $property, __CLASS__));
	}
	
	/**
	 * 
	 * Calcula o dígito verificador de um número, usando mod10
	 * 
	 * @param string $numero
	 * @return int
	 */
	public static function modulo10($numero) { 
		$soma = 0;
        $fator = 2;

        for ($i = strlen($numero); $i > 0; $i--) {
            $numeros[$i] = substr($numero, $i-1, 1);
            $numeros[$i] = $numeros[$i] * $fator; 

            if ($numeros[$i] == 10) {
            	$soma += 1;
            }
            elseif ($numeros[$i] < 10) {
            	$soma += $numeros[$i];
            }
            else {
            	$soma += floor($numeros[$i]/10) + floor($numeros[$i]%10);
            }
            
            $fator = $fator == 2 ? 1 : 2;
        }

        $resto = $soma % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }
		
        return $digito;
	}
	
	/**
	 * 
	 * Calcula o dígito verificador de um número, usando mod11
	 * 
	 * @param string $numero
	 * @param int $base
	 * @param boolean $resto
	 * @return int
	 */
	public static function modulo11($numero, $base = 9, $resto = false)  {
		$soma = 0;
	    $fator = 2;
	
	    for ($i = strlen($numero); $i > 0; $i--) {
	        $numeros[$i] = substr($numero, $i-1, 1);
	        $parcial[$i] = $numeros[$i] * $fator;
	        $soma += $parcial[$i];
	        if ($fator == $base) {
	            $fator = 1;
	        }
	        $fator++;
	    }
	
	    if ($resto) {
	        $resto = $soma % 11;
	        return $resto;
	    } 
	    else {
	        $soma *= 10;
	        $digito = $soma % 11;
	        if ($digito == 10) {
	            $digito = 0;
	        }
	        return $digito;
	    }
	}

	/**
	 * 
	 * Calcula o dígito verificador de um número, usando mod11 invertido (base 2 até 9)
	 * 
	 * @param string $numero
	 * @return int
	 */
	function modulo11Invertido($numero)  {
	    $fatorInicial = 2;
		$fator = $fatorFinal = 9;
	    $soma = 0;
		
	    for ($i = strlen($numero); $i > 0; $i--) {
			$soma += substr($numero, $i-1, 1) * $fator;
			if(--$fator < $fatorInicial) $fator = $fatorFinal;
	    }
		
	    $digito = $soma % 11;
		if($digito > 9) $digito = 0;
		
		return $digito;
	}
	
	/**
	 * 
	 * Gera o dígito verificador do campo Nosso Número
	 * 
	 * @param string $numero
	 * @return int
	 */
	public static function getDigitoVerificadorNossoNumero($numero) {
		$resto = self::modulo11($numero, 9, true);
		
		$dv = 11 - $resto;
		if ($dv == 10 || $dv == 11) {
			$dv = 0;
		}
		return $dv;
	}
	
	/**
	 * 
	 * Calcula o dígito verificador do código de barras
	 * 
	 * @param string $numero
	 * @return int
	 */
	public static function getDigitoVerificadorBarra($numero) {
		$resto = self::modulo11($numero, 9, true);
		
		$dv = 11 - $resto;
		if ($resto == 0 || $resto == 1 || $resto == 10) {
			$dv = 1;
		}
		return $dv;
	}
	
	/**
	 * 
	 * Calcula o fator de vencimento
	 * 
	 * @param DateTime $dataVencimento
	 * @retun int
	 */
	public static function getFatorVencimento(\DateTime $dataVencimento) {
		$dataBase = new \DateTime('1997-10-07');
		$intervalo = $dataBase->diff($dataVencimento);
		
		return abs($intervalo->format('%a'));
	}
	
	/**
	 * 
	 * Calcula o dígito verificador do código do banco e retorna uma string formatada
	 * 
	 * @return string
	 */
	public function getCodigoBancoComDv() {
		$numero = substr($this->codigoBanco, 0, 3);
		$dv = self::modulo11($numero);
		return sprintf('%s-%s', $numero, $dv);
	}
}