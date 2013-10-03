<?php

namespace OpenBoleto\Boleto;

use OpenBoleto\Layout;
use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\Field\Barcode as BarcodeField;
use OpenBoleto\Boleto\Field\LogoBanco as LogoBancoField;
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
			throw new Exception(sprintf('Campo %s já existe', $fieldName));
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
	 * @return OpenBoleto_Boleto_Field
	 */
	public function getField($fieldName) 
	{
		if (array_key_exists($fieldName, $this->_fields)) {
			return $this->_fields[$fieldName];
		}	
		
		throw new Exception(sprintf('Campo %s inexistente', $fieldName));
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
			->setLineWidth(0.75)
			->setLineColor(new Color\GrayScale(0))
			->setLineDashingPattern(Page::LINE_DASHING_SOLID)
			->drawLine(40, self::translateYPosition(20), 570, self::translateYPosition(20))
			->drawText('RECIBO DO PAGADOR', 485, self::translateYPosition(30));
			
		// linha de corte do recibo
		$this->setFont($fontNormal, 7)
			->drawText('Corte na linha pontilhada', 490, self::translateYPosition(290))
			->setLineDashingPattern(array(3, 2), 0)
			->drawLine(40, self::translateYPosition(292), 570, self::translateYPosition(292));

		// linha de corte da ficha de compensação
		$this->setFont($fontNormal, 7)
			->drawText('Corte na linha pontilhada', 490, self::translateYPosition(630))
			->setLineDashingPattern(array(3, 2), 0)
			->drawLine(40, self::translateYPosition(634), 570, self::translateYPosition(634));
			
		$this->setFont($fontBold, 8)
			->drawText('Ficha de Compensação', 480, self::translateYPosition($this->getField('fichaCodigoBarra')->getY()+5))
			->setFont($fontNormal, 7)
			->drawText('Autenticação Mecânica', 380, self::translateYPosition($this->getField('fichaCodigoBarra')->getY()+5))
			->drawText('Autenticação Mecânica', 490, self::translateYPosition($this->getField('reciboDemonstrativo')->getY()+8));
			
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
			return number_format($value, 2, ',', '');
		};
		
		// Recibo
		$this->addField(new LogoBancoField('reciboLogoBanco', array(
			'x' => 40,
			'y' => 120,
			'height' => 20,
			'width' => 120
		)));
		$this->addField(new CodigoBancoField('reciboCodigoBanco', array(
			'x' => 160,
			'y' => 120,
			'height' => 20,
			'width' => 50
		)));
		$this->addField(new LinhaDigitavelField('reciboLinhaDigitavel', array(
			'x' => 210,
			'y' => 120,
			'height' => 20,
			'width' => 360
		)));
		$this->addField(new Field('reciboCedente', array(
			'label' => 'Cedente',
			'dataProperty' => 'cedente',
			'x' => 40,
			'y' => 140,
			'width' => 240,
			'height' => 20
		)));
		$this->addField(new Field('reciboCodigoCedente', array(
			'label' => 'Agência/Código do cedente',
			'x' => 280,
			'y' => 140,
			'width' => 110,
			'height' => 20
		)));
		$this->addField(new Field('reciboEspecieMoeda', array(
			'label' => 'Espécie',
			'dataProperty' => 'especieMoeda',
			'x' => 390,
			'y' => 140,
			'width' => 30,
			'height' => 20
		)));
		$this->addField(new Field('reciboQuantidade', array(
			'label' => 'Quantidade',
			'dataProperty' => 'quantidade',
			'x' => 420,
			'y' => 140,
			'width' => 50,
			'height' => 20
		)));
		$this->addField(new Field('reciboNossoNumero', array(
			'label' => 'Nosso número',
			'dataProperty' => 'nossoNumero',
			'x' => 470,
			'y' => 140,
			'width' => 100,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('reciboNumeroDocumento', array(
			'label' => 'Número do documento',
			'dataProperty' => 'numeroDocumento',
			'x' => 40,
			'y' => 160,
			'width' => 160,
			'height' => 20
		)));
		$this->addField(new Field('reciboCpfCnpj', array(
			'label' => 'CPF/CNPJ',
			'dataProperty' => 'cpfCnpjCedente',
			'x' => 200,
			'y' => 160,
			'width' => 110,
			'height' => 20
		)));
		$this->addField(new Field('reciboDataVencimento', array(
			'label' => 'Vencimento',
			'dataProperty' => 'dataVencimento',
			'x' => 310,
			'y' => 160,
			'width' => 110,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('reciboValorDocumento', array(
			'label' => 'Valor documento',
			'dataProperty' => 'valorDocumento',
			'x' => 420,
			'y' => 160,
			'width' => 150,
			'height' => 20,
			'align' => 'right',
			'renderer' => $currencyRenderer
		)));
		$this->addField(new Field('reciboValorAbatimento', array(
			'label' => '(-) Descontos/Abatimentos',
			'x' => 40,
			'y' => 180,
			'width' => 95,
			'height' => 20
		)));
		$this->addField(new Field('reciboValorDeducao', array(
			'label' => '(-) Outras deduções',
			'x' => 135,
			'y' => 180,
			'width' => 95,
			'height' => 20
		)));
		$this->addField(new Field('reciboValorMulta', array(
			'label' => '(+) Mora/Multa',
			'x' => 230,
			'y' => 180,
			'width' => 95,
			'height' => 20
		)));
		$this->addField(new Field('reciboValorAcrescimo', array(
			'label' => '(+) Outros acréscimos',
			'x' => 325,
			'y' => 180,
			'width' => 95,
			'height' => 20
		)));
		$this->addField(new Field('reciboValorCobrado', array(
			'label' => '(=) Valor cobrado',
			'x' => 420,
			'y' => 180,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('reciboNomeSacado', array(
			'label' => 'Pagador',
			'dataProperty' => 'nomeSacado',
			'x' => 40,
			'y' => 200,
			'width' => 530,
			'height' => 20
		)));
		$this->addField(new Field('reciboDemonstrativo', array(
			'label' => 'Demonstrativo',
			'dataProperty' => 'demonstrativo',
			'multiline' => true,
			'x' => 40,
			'y' => 220,
			'width' => 530,
			'height' => 60,
			'border' => false
		)));
		
		// ficha de compensação
		$this->addField(new LogoBancoField('fichaLogoBanco', array(
			'x' => 40,
			'y' => 320,
			'height' => 20,
			'width' => 120
		)));
		$this->addField(new CodigoBancoField('fichaCodigoBanco', array(
			'x' => 160,
			'y' => 320,
			'height' => 20,
			'width' => 50
		)));
		$this->addField(new LinhaDigitavelField('fichaLinhaDigitavel', array(
			'x' => 210,
			'y' => 320,
			'height' => 20,
			'width' => 360
		)));
		$this->addField(new Field('fichaLocalPagamento', array(
			'label' => 'Local de pagamento',
			'dataProperty' => 'localPagamento',
			'x' => 40,
			'y' => 340,
			'width' => 380,
			'height' => 20
		)));
		$this->addField(new Field('fichaDataVencimento', array(
			'label' => 'Vencimento',
			'dataProperty' => 'dataVencimento',
			'x' => 420,
			'y' => 340,
			'width' => 150,
			'height' => 20,
			'align' => 'right',
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('fichaCedente', array(
			'label' => 'Cedente',
			'dataProperty' => 'cedente',
			'x' => 40,
			'y' => 360,
			'width' => 380,
			'height' => 20
		)));
		$this->addField(new Field('fichaCodigoCedente', array(
			'label' => 'Agência/Código do cedente',
			'x' => 420,
			'y' => 360,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaDataDocumento', array(
			'label' => 'Data do documento',
			'dataProperty' => 'dataDocumento',
			'x' => 40,
			'y' => 380,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('fichaNumeroDocumento', array(
			'label' => 'Nº do documento',
			'dataProperty' => 'numeroDocumento',
			'x' => 130,
			'y' => 380,
			'width' => 90,
			'height' => 20
		)));
		$this->addField(new Field('fichaEspecieDocumento', array(
			'label' => 'Espécie doc.',
			'dataProperty' => 'especieDocumento',
			'x' => 220,
			'y' => 380,
			'width' => 55,
			'height' => 20
		)));
		$this->addField(new Field('fichaAceite', array(
			'label' => 'Aceite',
			'dataProperty' => 'aceite',
			'x' => 275,
			'y' => 380,
			'width' => 55,
			'height' => 20
		)));
		$this->addField(new Field('fichaDataProcessamento', array(
			'label' => 'Data processamento',
			'dataProperty' => 'dataProcessamento',
			'x' => 330,
			'y' => 380,
			'width' => 90,
			'height' => 20,
			'renderer' => $dateRenderer
		)));
		$this->addField(new Field('fichaNossoNumero', array(
			'label' => 'Nosso número',
			'dataProperty' => 'nossoNumero',
			'x' => 420,
			'y' => 380,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		
		$this->addField(new Field('fichaCarteira', array(
			'label' => 'Carteira',
			'dataProperty' => 'carteira',
			'x' => 40,
			'y' => 400,
			'width' => 180,
			'height' => 20
		)));
		$this->addField(new Field('fichaEspecieMoeda', array(
			'label' => 'Espécie Moeda',
			'dataProperty' => 'especieMoeda',
			'x' => 220,
			'y' => 400,
			'width' => 55,
			'height' => 20
		)));
		$this->addField(new Field('fichaQuantidade', array(
			'label' => 'Quantidade',
			'dataProperty' => 'quantidade',
			'x' => 275,
			'y' => 400,
			'width' => 55,
			'height' => 20
		)));
		$this->addField(new Field('fichaValorDocumento2', array(
			'label' => '(x) Valor',
			'x' => 330,
			'y' => 400,
			'width' => 90,
			'height' => 20
		)));
		$this->addField(new Field('fichaValorDocumento', array(
			'label' => '(=) Valor documento',
			'dataProperty' => 'valorDocumento',
			'x' => 420,
			'y' => 400,
			'width' => 150,
			'height' => 20,
			'align' => 'right',
			'renderer' => $currencyRenderer
		)));
		
		$this->addField(new Field('fichaInstrucao', array(
			'label' => 'Instruções (Texto de responsabilidade do cedente)',
			'dataProperty' => 'instrucao',
			'multiline' => true,
			'x' => 40,
			'y' => 420,
			'width' => 380,
			'height' => 100
		)));
		
		$this->addField(new Field('fichaValorAbatimento', array(
			'label' => '(-) Descontos/Abatimentos',
			'x' => 420,
			'y' => 420,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaValorDeducao', array(
			'label' => '(-) Outras deduções',
			'x' => 420,
			'y' => 440,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaValorMulta', array(
			'label' => '(+) Mora/Multa',
			'x' => 420,
			'y' => 460,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaAcrescimos', array(
			'label' => '(+) Outros acréscimos',
			'x' => 420,
			'y' => 480,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		$this->addField(new Field('fichaValorCobrado', array(
			'label' => '(=) Valor cobrado',
			'x' => 420,
			'y' => 500,
			'width' => 150,
			'height' => 20,
			'align' => 'right'
		)));
		
		$this->addField(new Field('fichaSacado', array(
			'label' => 'Sacado',
			'dataProperty' => 'nomeSacado',
			'multiline' => true,
			'x' => 40,
			'y' => 520,
			'width' => 530,
			'height' => 60
		)));
		$this->addField(new Field('fichaSacadorAvalista', array(
			'label' => 'Sacador/Avalista',
			'dataProperty' => 'sacadorAvalista',
			'multiline' => true,
			'x' => 40,
			'y' => 560,
			'width' => 530,
			'height' => 20,
			'border' => false
		)));
		$this->addField(new BarcodeField('fichaCodigoBarra', array(
			'x' => 40,
			'y' => 583
		)));
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
				->drawText($y, 1, self::translateYPosition($y)+1);
			$y += 20;
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