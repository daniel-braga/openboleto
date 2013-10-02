<?php

namespace OpenBoleto\Boleto\Santander;

use OpenBoleto\Boleto;
use OpenBoleto\Boleto\Pdf as BasePdf;
use OpenBoleto\Layout\Santander as LayoutSantander;

class Pdf extends BasePdf {
	
	public function __construct(LayoutSantander $layout) {
		parent::__construct();
		
		$this->setLayout($layout);
	}
	
	public function init() {
		parent::init();
		
		$rendererCodigoCedente = function($value, LayoutSantander $layout) {
			return sprintf('%s / %s', $layout->get('pontoVenda'), $layout->get('codigoCedente'));
		};

		$logoFile = Boleto::getRootDir() . '/Layout/Santander/logo.jpg';
		$this->getField('reciboLogoBanco')
			->setValue($logoFile);
		$this->getField('fichaLogoBanco')
			->setValue($logoFile);
		
		$this->getField('reciboCodigoCedente')
			->setRenderer($rendererCodigoCedente);
		$this->getField('fichaCodigoCedente')
			->setRenderer($rendererCodigoCedente);
			
		$this->getField('fichaCarteira')
			->setRenderer(function($value, LayoutSantander $layout) {
				$arDescricao = $layout->get('descricaoCarteira'); 
				return sprintf('%s - %s', $layout->get('carteira'), $arDescricao[$layout->get('carteira')]);
			});
		
		$this->getField('fichaSacado')
			->setMultiline(true)
			->setRenderer(function($value, LayoutSantander $layout) {
				return sprintf("%s\n%s", $layout->get('nomeSacado'), $layout->get('enderecoSacado'));
			});
	}
	
	public function draw() {
		parent::draw();
	}
}