<?php

namespace OpenBoleto\Boleto;

use OpenBoleto\Layout;
use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\Field\Barcode as BarcodeField;
use OpenBoleto\Boleto\Field\LogoBanco as LogoBancoField;
use OpenBoleto\Boleto\Field\NomeBanco as NomeBancoField;
use OpenBoleto\Boleto\Field\CodigoBanco as CodigoBancoField;
use OpenBoleto\Boleto\Field\LinhaDigitavel as LinhaDigitavelField;

use ZendPdf\PdfDocument;
use ZendPdf\Page;
use ZendPdf\Font;
use ZendPdf\Color;
use ZendPdf\Resource\Font\Simple\AbstractSimple as AbstractSimpleFont;

abstract class AbstractBoleto extends Page 
{
	/**
	 * 
	 * Layout utilizado para o boleto
	 * 
	 * @var OpenBoleto_Layout
	 */
	protected $_layout;
	
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @var array
	 */
	protected $_fields = array();
	
	/**
	 * 
	 * Construtor
	 * 
	 */
	public function __construct() 
	{
		parent::__construct(Page::SIZE_A4);
	}
	
	/** 
	* Returns the total width in points of the string using the specified font and 
	* size. 
	* 
	* This is not the most efficient way to perform this calculation. I'm 
	* concentrating optimization efforts on the upcoming layout manager class. 
	* Similar calculations exist inside the layout manager class, but widths are 
	* generally calculated only after determining line fragments. 
	* 
	* @param string $string 
	* @param AbstractSimpleFont $font 
	* @param float $fontSize Font size in points 
	* @return float 
	*/ 
	public static function getTextWidth($text, AbstractSimpleFont $font, $font_size) 
	{
		$drawing_text = iconv('', 'UTF-16BE', $text);
		$characters    = array();
		for ($i = 0; $i < strlen($drawing_text); $i++) {
			$characters[] = (ord($drawing_text[$i++]) << 8) | ord ($drawing_text[$i]);
		}
		$glyphs        = $font->glyphNumbersForCharacters($characters);
		$widths        = $font->widthsForGlyphs($glyphs);
		$text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
		return $text_width;	
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param int $y
	 */
	public static function translateYPosition($y) 
	{
		return 842 - intval($y);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @param OpenBoleto_Layout $layout
	 * @return OpenBoleto_Boleto_Pdf
	 */
	public function setLayout(Layout $layout)
	{
		$this->_layout = $layout;
		
		return $this;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * 
	 * @return OpenBoleto_Layout
	 */
	public function getLayout()
	{
		return $this->_layout;
	}
	
	/**
	 * 
	 * Adiciona um campo
	 * 
	 * @param OpenBoleto_Boleto_Field $field
	 */
	public function addField(Field $field) 
	{
		$fieldName = $field->getName();
		if (array_key_exists($fieldName, $this->_fields)) {
			throw new \Exception(sprintf('Campo %s já existe', $fieldName));
		}
		
		$field->setBoleto($this);
		$this->_fields[$fieldName] = $field;
	}
	
	/**
	 * 
	 * Remove um campo
	 * 
	 * @param string $fieldName
	 * @throws Exception
	 */
	public function removeField($fieldName) 
	{
		if (array_key_exists($fieldName, $this->_fields)) {
			unset($this->_fields[$fieldName]);
		}
		
		throw new Exception(sprintf('Campo %s inexistente', $fieldName));
	}

	/**
	 * 
	 * Retorna um campo pelo nome
	 * 
	 * @param string $fieldName
	 * @throws Exception
	 * @return \OpenBoleto\Boleto\Field
	 */
	public function getField($fieldName) 
	{
		if (array_key_exists($fieldName, $this->_fields)) {
			return $this->_fields[$fieldName];
		}	
		
		throw new \Exception(sprintf('Campo %s inexistente', $fieldName));
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function draw() 
	{
		$fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
		$fontNormal = Font::fontWithName(Font::FONT_HELVETICA);
		
		//$this->drawGrid();

		// linha do Recibo do sacado
		$this->setFont($fontBold, 8)
			->drawText('Recibo do Pagador', 490, self::translateYPosition(
                $this->getField('reciboLinhaDigitavel')->getY()-5
            ));
		
        // linha de corte do recibo
        $posCut1 = self::translateYPosition(
            $this->getField('reciboLinhaDigitavel')->getY()-15
        );
        $this->setLineDashingPattern(array(3, 2), 0)
			->drawLine(30, $posCut1, 570, $posCut1);
		
        // linha de corte da ficha
        $posCut2 = self::translateYPosition(
            $this->getField('fichaLinhaDigitavel')->getY()-15
        );
		$this->setLineDashingPattern(array(3, 2), 0)
			->drawLine(30, $posCut2, 570, $posCut2);
			
		$this->setFont($fontBold, 8)
			->drawText('Ficha de Compensação', 480, self::translateYPosition($this->getField('fichaCodigoBarra')->getY()+5))
			->setFont($fontNormal, 7)
			->drawText('Autenticação Mecânica', 380, self::translateYPosition(
                $this->getField('fichaCodigoBarra')->getY()+5)
            );
        
		foreach($this->_fields as $field) {
			$field->draw();
		}
	}
	
	public function init()
	{
		$dateRenderer = function($date, $layout) {
			if ($date instanceof \DateTime) {
				return $date->format('d/m/Y');
			}
			return $date;
		};
		$currencyRenderer = function($value, $layout) {
			return number_format($value, 2, ',', '.');
		};
        
        if ($this->getLayout()->get('gerarReciboEntrega')) {
            $this->initReciboEntrega();
        }
		
		// Recibo
		$this->addField(new LogoBancoField('reciboLogoBanco', array(
			'x' => 30,
			'y' => 359,
			'height' => 30,
			'width' => 30
		)));
        $this->addField(new NomeBancoField('reciboNomeBanco', array(
			'x' => 60,
			'y' => 369,
			'height' => 30,
			'width' => 80
		)));
		$this->addField(new CodigoBancoField('reciboCodigoBanco', array(
			'x' => 150,
			'y' => 369,
			'height' => 20,
			'width' => 50
		)));
		$this->addField(new LinhaDigitavelField('reciboLinhaDigitavel', array(
			'x' => 200,
			'y' => 369,
			'height' => 20,
			'width' => 370
		)));
		$this->addField(new Field('reciboBeneficiario', array(
			'label' => 'Beneficiário',
			'dataProperty' => 'beneficiario',
			'x' => 30,
			'y' => 394,
			'width' => 200,
			'height' => 20,
            'border' => Field::BORDER_LEFT | Field::BORDER_TOP | Field::BORDER_BOTTOM
		)));
		$this->addField(new Field('reciboCpfCnpj', array(
			'label' => 'CNPJ/CPF',
			'dataProperty' => 'cpfCnpjBeneficiario',
			'x' => 230,
			'y' => 394,
			'width' => 90,
			'height' => 20,
            'border' => Field::BORDER_RIGHT | Field::BORDER_TOP | Field::BORDER_BOTTOM
		)));
        $this->addField(new Field('reciboNomeSacadorAvalista', array(
			'label' => 'Sacador Avalista',
			'dataProperty' => 'nomeSacadorAvalista',
			'x' => 320,
			'y' => 394,
			'width' => 150,
			'height' => 20
		)));
		$this->addField(new Field('reciboDataVencimento', array(
			'label' => 'Vencimento',
			'dataProperty' => 'dataVencimento',
			'x' => 470,
			'y' => 394,
			'width' => 100,
			'height' => 20,
			'renderer' => $dateRenderer,
            'align' => 'center'
		)));
        
        $this->addField(new Field('reciboEnderecoBeneficiario', array(
			'label' => 'Endereço Beneficiário/Sacador Avalista',
			'dataProperty' => 'enderecoBeneficiario',
			'x' => 30,
			'y' => 414,
			'width' => 540,
			'height' => 20
		)));
        
		$this->addField(new Field('reciboNossoNumero', array(
			'label' => 'Nosso Número',
			'dataProperty' => 'nossoNumero',
			'x' => 30,
			'y' => 434,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboCarteira', array(
			'label' => 'Carteira',
			'dataProperty' => 'carteira',
			'x' => 120,
			'y' => 434,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboEspecieDocumento', array(
			'label' => 'Espécie Documento',
			'dataProperty' => 'especieDocumento',
			'x' => 210,
			'y' => 434,
			'width' => 80,
			'height' => 20
		)));
		$this->addField(new Field('reciboQuantidade', array(
			'label' => 'Quantidade',
			'dataProperty' => 'quantidade',
			'x' => 290,
			'y' => 434,
			'width' => 85,
			'height' => 20
		)));
		$this->addField(new Field('reciboValorUnitario', array(
			'label' => 'Valor',
			'dataProperty' => 'valorUnitario',
			'x' => 375,
			'y' => 434,
			'width' => 85,
			'height' => 20
		)));
        $this->addField(new Field('reciboCodigoBeneficiario', array(
			'label' => 'Agência/Código do Beneficiário',
			'x' => 460,
			'y' => 434,
			'width' => 110,
			'height' => 20,
            'align' => 'right'
		)));
        
        $this->addField(new Field('reciboDataDocumento', array(
			'label' => 'Data do Documento',
			'dataProperty' => 'dataDocumento',
			'x' => 30,
			'y' => 454,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('reciboNumeroDocumento', array(
			'label' => 'Número do Documento',
			'dataProperty' => 'numeroDocumento',
			'x' => 120,
			'y' => 454,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboEspecieMoeda', array(
			'label' => 'Espécie',
			'dataProperty' => 'especieMoeda',
			'x' => 210,
			'y' => 454,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboAceite', array(
			'label' => 'Aceite',
			'dataProperty' => 'aceite',
			'x' => 300,
			'y' => 454,
			'width' => 50,
			'height' => 20
		)));
        $this->addField(new Field('reciboDataProcessamento', array(
			'label' => 'Data de Processamento',
			'dataProperty' => 'dataProcessamento',
			'x' => 350,
			'y' => 454,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('reciboValorDocumento', array(
			'label' => 'Valor do Documento',
			'dataProperty' => 'valorDocumento',
			'x' => 440,
			'y' => 454,
			'width' => 130,
			'height' => 20,
			'align' => 'right',
			'renderer' => $currencyRenderer
		)));
        $this->addField(new Field('reciboAutenticacaoMecanica', array(
			'label' => 'Autenticação Mecânica',
			'x' => 370,
			'y' => 479,
			'width' => 200,
			'height' => 17,
            'border' => Field::BORDER_LEFT | Field::BORDER_TOP | Field::BORDER_RIGHT
		)));
		
		// ficha de compensação
        $this->addField(new LogoBancoField('fichaLogoBanco', array(
			'x' => 30,
			'y' => 519,
			'height' => 30,
			'width' => 30
		)));
        $this->addField(new NomeBancoField('fichaNomeBanco', array(
			'x' => 60,
			'y' => 529,
			'height' => 30,
			'width' => 80
		)));
		$this->addField(new CodigoBancoField('fichaCodigoBanco', array(
			'x' => 150,
			'y' => 529,
			'height' => 20,
			'width' => 50
		)));
		$this->addField(new LinhaDigitavelField('fichaLinhaDigitavel', array(
			'x' => 200,
			'y' => 529,
			'height' => 20,
			'width' => 370
		)));
        
		$this->addField(new Field('fichaLocalPagamento', array(
			'label' => 'Local de Pagamento',
			'dataProperty' => 'localPagamento',
			'x' => 30,
			'y' => 554,
			'width' => 400,
			'height' => 30
		)));
		$this->addField(new Field('fichaDataVencimento', array(
			'label' => 'Vencimento',
			'dataProperty' => 'dataVencimento',
			'x' => 430,
			'y' => 554,
			'width' => 140,
			'height' => 30,
			'align' => 'center',
			'renderer' => $dateRenderer
		)));
        
		$this->addField(new Field('fichaBeneficiario', array(
			'label' => 'Beneficiário',
			'dataProperty' => 'beneficiario',
			'x' => 30,
			'y' => 584,
			'width' => 270,
			'height' => 20,
            'border' => Field::BORDER_LEFT | Field::BORDER_TOP | Field::BORDER_BOTTOM
		)));
        $this->addField(new Field('fichaCpfCnpjBeneficiario', array(
			'label' => 'CNPJ/CPF',
			'dataProperty' => 'cpfCnpjBeneficiario',
			'x' => 300,
			'y' => 584,
			'width' => 130,
			'height' => 20,
            'border' => Field::BORDER_RIGHT | Field::BORDER_TOP | Field::BORDER_BOTTOM
		)));
		$this->addField(new Field('fichaCodigoBeneficiario', array(
			'label' => 'Agência/Código do Beneficiário',
			'x' => 430,
			'y' => 584,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
        
		$this->addField(new Field('fichaDataDocumento', array(
			'label' => 'Data do Documento',
			'dataProperty' => 'dataDocumento',
			'x' => 30,
			'y' => 604,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('fichaNumeroDocumento', array(
			'label' => 'Número do Documento',
			'dataProperty' => 'numeroDocumento',
			'x' => 120,
			'y' => 604,
			'width' => 120,
			'height' => 20
		)));
		$this->addField(new Field('fichaEspecieDocumento', array(
			'label' => 'Espécie Documento',
			'dataProperty' => 'especieDocumento',
			'x' => 240,
			'y' => 604,
			'width' => 70,
			'height' => 20
		)));
		$this->addField(new Field('fichaAceite', array(
			'label' => 'Aceite',
			'dataProperty' => 'aceite',
			'x' => 310,
			'y' => 604,
			'width' => 30,
			'height' => 20
		)));
		$this->addField(new Field('fichaDataProcessamento', array(
			'label' => 'Data Processamento',
			'dataProperty' => 'dataProcessamento',
			'x' => 340,
			'y' => 604,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('fichaNossoNumero', array(
			'label' => 'Nosso Número',
			'dataProperty' => 'nossoNumero',
			'x' => 430,
			'y' => 604,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
		
        $this->addField(new Field('fichaUsoBanco', array(
			'label' => 'Uso do Banco',
			'x' => 30,
			'y' => 624,
			'width' => 90,
			'height' => 20
		)));
		$this->addField(new Field('fichaCarteira', array(
			'label' => 'Carteira',
			'dataProperty' => 'carteira',
			'x' => 120,
			'y' => 624,
			'width' => 90,
			'height' => 20
		)));
		$this->addField(new Field('fichaEspecieMoeda', array(
			'label' => 'Espécie',
			'dataProperty' => 'especieMoeda',
			'x' => 210,
			'y' => 624,
			'width' => 30,
			'height' => 20
		)));
		$this->addField(new Field('fichaQuantidade', array(
			'label' => 'Quantidade',
			'dataProperty' => 'quantidade',
			'x' => 240,
			'y' => 624,
			'width' => 100,
			'height' => 20
		)));
		$this->addField(new Field('fichaValorUnitario', array(
			'label' => 'Valor',
			'x' => 340,
			'y' => 624,
			'width' => 90,
			'height' => 20
		)));
		$this->addField(new Field('fichaValorDocumento', array(
			'label' => '(=) Valor documento',
			'dataProperty' => 'valorDocumento',
			'x' => 430,
			'y' => 624,
			'width' => 140,
			'height' => 20,
			'align' => 'right',
			'renderer' => $currencyRenderer
		)));
		
		$this->addField(new Field('fichaInstrucao', array(
			'label' => 'Instruções (Texto de responsabilidade do BENEFICIÁRIO)',
			'dataProperty' => 'instrucao',
			'multiline' => true,
			'x' => 30,
			'y' => 644,
			'width' => 400,
			'height' => 100
		)));
		
		$this->addField(new Field('fichaValorAbatimento', array(
			'label' => '(-) Descontos/Abatimentos',
			'x' => 430,
			'y' => 644,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaValorDeducao', array(
			'x' => 430,
			'y' => 664,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaValorMulta', array(
			'label' => '(+) Mora/Multa',
			'x' => 430,
			'y' => 684,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaAcrescimos', array(
			'x' => 430,
			'y' => 704,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaValorCobrado', array(
			'label' => '(=) Valor cobrado',
			'x' => 430,
			'y' => 724,
			'width' => 140,
			'height' => 20,
			'align' => 'right'
		)));
		
		$this->addField(new Field('fichaNomePagador', array(
			'label' => 'Pagador:',
            'labelWidth' => 40,
            'labelPosition' => 'left',
			'dataProperty' => 'nomePagador',
			'x' => 30,
			'y' => 744,
			'width' => 280,
			'height' => 12,
            'border' => Field::BORDER_LEFT,
		)));
        $this->addField(new Field('fichaCnpjCpfPagador', array(
			'label' => 'CNPJ/CPF:',
            'labelWidth' => 40,
            'labelPosition' => 'left',
			'dataProperty' => 'cpfCnpjPagador',
			'x' => 310,
			'y' => 744,
			'width' => 260,
			'height' => 12,
            'border' => Field::BORDER_RIGHT,
		)));
        $this->addField(new Field('fichaEnderecoPagador', array(
			'label' => 'Endereço:',
            'labelWidth' => 40,
            'labelPosition' => 'left',
			'dataProperty' => 'enderecoPagador',
			'x' => 30,
			'y' => 756,
			'width' => 540,
			'height' => 12,
            'border' => Field::BORDER_LEFT | Field::BORDER_RIGHT,
		)));
        
		$this->addField(new Field('fichaSacadorAvalista', array(
			'label' => 'Sacador Avalista:',
            'labelPosition' => 'left',
			'dataProperty' => 'nomeSacadorAvalista',
			'x' => 30,
			'y' => 768,
			'width' => 280,
			'height' => 12,
			'border' => Field::BORDER_LEFT | Field::BORDER_BOTTOM
		)));
        $this->addField(new Field('fichaCnpjSacadorAvalista', array(
			'label' => 'CNPJ:',
            'labelPosition' => 'left',
			'dataProperty' => 'cnpjSacadorAvalista',
			'x' => 310,
			'y' => 768,
			'width' => 150,
			'height' => 12,
			'border' => Field::BORDER_BOTTOM
		)));
        $this->addField(new Field('fichaCodigoBaixa', array(
			'label' => 'Código de baixa:',
            'labelPosition' => 'left',
			'x' => 440,
			'y' => 768,
			'width' => 130,
			'height' => 12,
			'border' => Field::BORDER_RIGHT | Field::BORDER_BOTTOM
		)));
        
        // 458
		$this->addField(new BarcodeField('fichaCodigoBarra', array(
			'x' => 30,
			'y' => 782
		)));
	}
    
    
    private function initReciboEntrega()
    {
        $dateRenderer = function($date, $layout) {
			if ($date instanceof \DateTime) {
				return $date->format('d/m/Y');
			}
			return $date;
		};
		$currencyRenderer = function($value, $layout) {
			return number_format($value, 2, ',', '.');
		};
        
		// Recibo de Entrega
		$this->addField(new LogoBancoField('reciboEntregaLogoBanco', array(
			'x' => 30,
			'y' => 20,
			'height' => 30,
			'width' => 30
		)));
        $this->addField(new NomeBancoField('reciboEntregaNomeBanco', array(
			'x' => 60,
			'y' => 30,
			'height' => 30,
			'width' => 80
		)));
		$this->addField(new CodigoBancoField('reciboEntregaCodigoBanco', array(
			'x' => 150,
			'y' => 30,
			'height' => 20,
			'width' => 50
		)));

		$this->addField(new Field('reciboEntregaBeneficiario', array(
			'label' => 'Beneficiário',
			'dataProperty' => 'beneficiario',
			'x' => 30,
			'y' => 60,
			'width' => 200,
			'height' => 20,
            'border' => Field::BORDER_LEFT | Field::BORDER_TOP | Field::BORDER_BOTTOM
		)));
		$this->addField(new Field('reciboEntregaCpfCnpj', array(
			'label' => 'CNPJ/CPF',
			'dataProperty' => 'cpfCnpjBeneficiario',
			'x' => 230,
			'y' => 60,
			'width' => 90,
			'height' => 20,
            'border' => Field::BORDER_RIGHT | Field::BORDER_TOP | Field::BORDER_BOTTOM
		)));
        $this->addField(new Field('reciboEntregaNomeSacadorAvalista', array(
			'label' => 'Sacador Avalista',
			'dataProperty' => 'nomeSacadorAvalista',
			'x' => 320,
			'y' => 60,
			'width' => 150,
			'height' => 20
		)));
		$this->addField(new Field('reciboEntregaDataVencimento', array(
			'label' => 'Vencimento',
			'dataProperty' => 'dataVencimento',
			'x' => 470,
			'y' => 60,
			'width' => 100,
			'height' => 20,
			'renderer' => $dateRenderer,
            'align' => 'center'
		)));
        
        $this->addField(new Field('reciboEntregaEnderecoBeneficiario', array(
			'label' => 'Endereço Beneficiário/Sacador Avalista',
			'dataProperty' => 'enderecoBeneficiario',
			'x' => 30,
			'y' => 80,
			'width' => 540,
			'height' => 20
		)));
        
		$this->addField(new Field('reciboEntregaNossoNumero', array(
			'label' => 'Nosso Número',
			'dataProperty' => 'nossoNumero',
			'x' => 30,
			'y' => 100,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaCarteira', array(
			'label' => 'Carteira',
			'dataProperty' => 'carteira',
			'x' => 120,
			'y' => 100,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaEspecieDocumento', array(
			'label' => 'Espécie Documento',
			'dataProperty' => 'especieDocumento',
			'x' => 210,
			'y' => 100,
			'width' => 80,
			'height' => 20
		)));
		$this->addField(new Field('reciboEntregaQuantidade', array(
			'label' => 'Quantidade',
			'dataProperty' => 'quantidade',
			'x' => 290,
			'y' => 100,
			'width' => 85,
			'height' => 20
		)));
		$this->addField(new Field('reciboEntregaValorUnitario', array(
			'label' => 'Valor',
			'dataProperty' => 'valorUnitario',
			'x' => 375,
			'y' => 100,
			'width' => 85,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaCodigoBeneficiario', array(
			'label' => 'Agência/Código do Beneficiário',
			'x' => 460,
			'y' => 100,
			'width' => 110,
			'height' => 20,
            'align' => 'right'
		)));
        
        $this->addField(new Field('reciboEntregaDataDocumento', array(
			'label' => 'Data do Documento',
			'dataProperty' => 'dataDocumento',
			'x' => 30,
			'y' => 120,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('reciboEntregaNumeroDocumento', array(
			'label' => 'Número do Documento',
			'dataProperty' => 'numeroDocumento',
			'x' => 120,
			'y' => 120,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaEspecieMoeda', array(
			'label' => 'Espécie',
			'dataProperty' => 'especieMoeda',
			'x' => 210,
			'y' => 120,
			'width' => 90,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaAceite', array(
			'label' => 'Aceite',
			'dataProperty' => 'aceite',
			'x' => 300,
			'y' => 120,
			'width' => 50,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaDataProcessamento', array(
			'label' => 'Data de Processamento',
			'dataProperty' => 'dataProcessamento',
			'x' => 350,
			'y' => 120,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('reciboEntregaValorDocumento', array(
			'label' => 'Valor do Documento',
			'dataProperty' => 'valorDocumento',
			'x' => 440,
			'y' => 120,
			'width' => 130,
			'height' => 20,
			'align' => 'right',
			'renderer' => $currencyRenderer
		)));
        
        $this->addField(new Field('reciboEntregaNomeRecebedor', array(
			'label' => 'Nome do Recebedor',
			'x' => 30,
			'y' => 140,
			'width' => 440,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaDataEntrega', array(
			'label' => 'Data da Entrega',
			'x' => 470,
			'y' => 140,
			'width' => 100,
			'height' => 20
		)));
        $this->addField(new Field('reciboEntregaAssinatura', array(
			'label' => 'Assinatura do Recebedor',
			'x' => 30,
			'y' => 165,
			'width' => 540,
			'height' => 40
		)));
        
        $fontBold = Font::fontWithName(Font::FONT_HELVETICA_BOLD);
        $this->setFont($fontBold, 10)
			->drawText('Recibo de Entrega', 480, self::translateYPosition(
                $this->getField('reciboEntregaDataVencimento')->getY()-15
            ));
    }

	/**
	 * 
	 * Desenha um grid
	 */
	public function drawGrid() 
	{
		$font = Font::fontWithName(Font::FONT_HELVETICA);
		
		$this->setLineColor(new Color\GrayScale(0.8))
			->setLineDashingPattern(Page::LINE_DASHING_SOLID);
			
		$x = 0;
		while ($x <= 595) {
			$this->setFont($font, 8)
				->drawLine($x, 0, $x, 842)
				->drawText($x, $x+1, 830);
			$x += 20;
		}
		
		$y = 0;
		while ($y <= 842) {
			$this->setFont($font, 8)
				->drawLine(0, self::translateYPosition($y), 595, self::translateYPosition($y))
				->drawText($y, 15, self::translateYPosition($y)+1);
			$y += 10;
		}
	}
    
    public function output(array $options = array())
    {
        $this->init();
		$this->draw();
        
        $pdf = new PdfDocument();
		$pdf->pages[] = $this;
        
        $pdf->save('/Users/daniel/output.pdf');
    }
}