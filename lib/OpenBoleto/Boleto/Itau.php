<?php

namespace OpenBoleto\Boleto;

use OpenBoleto\BoletoFactory;
use OpenBoleto\Boleto\AbstractBoleto;
use OpenBoleto\Layout\Itau as LayoutItau;

class Itau extends AbstractBoleto {
	
	public function __construct(LayoutItau $layout) {
		parent::__construct();
		
		$this->setLayout($layout);
	}
	
	public function init() {
		parent::init();
		
		$rendererCodigoBeneficiario = function($value, LayoutItau $layout) {
			return sprintf('%s/%s-%d', $layout->get('agencia'), 
                $layout->get('conta'), $layout->get('dacConta')
            );
		};
        $rendererNossoNumero = function($value, LayoutItau $layout) {
            $carteira    = $layout->get('carteira');
            $nossoNumero = str_pad($layout->get('nossoNumero'), 8, '0', STR_PAD_LEFT);
            $agencia     = str_pad($layout->get('agencia'), 4, '0', STR_PAD_LEFT);
            $conta       = str_pad($layout->get('conta'), 5, '0', STR_PAD_LEFT);
            
            if (in_array($carteira, array(126, 131, 146, 150, 168))) {
                $dacAgenciaContaCarteiraNossoNumero = LayoutItau::modulo10($carteira.$nossoNumero);
            }
            else {
                $dacAgenciaContaCarteiraNossoNumero = LayoutItau::modulo10($agencia.$conta.$carteira.$nossoNumero);
            }
            
			return sprintf('%s/%s-%d', $layout->get('carteira'), 
                $layout->get('nossoNumero'), $dacAgenciaContaCarteiraNossoNumero
            );
		};

		$logoFile = BoletoFactory::getRootDir() . '/Layout/Itau/logo.png';
        $this->getField('reciboEntregaLogoBanco')
			->setValue($logoFile);
		$this->getField('reciboLogoBanco')
			->setValue($logoFile);
		$this->getField('fichaLogoBanco')
			->setValue($logoFile);
        
        $this->getField('reciboNomeBanco')
            ->setValue('Banco Itaú S.A.');
        $this->getField('reciboEntregaNomeBanco')
            ->setValue('Banco Itaú S.A.');
        $this->getField('fichaNomeBanco')
            ->setValue('Banco Itaú S.A.');
        
        $this->getField('fichaLocalPagamento')
            ->setMultiline(true);
        
		$this->getField('reciboEntregaNossoNumero')
			->setRenderer($rendererNossoNumero);
        $this->getField('reciboNossoNumero')
			->setRenderer($rendererNossoNumero);
		$this->getField('fichaNossoNumero')
			->setRenderer($rendererNossoNumero);
		
		$this->getField('reciboEntregaCodigoBeneficiario')
			->setRenderer($rendererCodigoBeneficiario);
        $this->getField('reciboCodigoBeneficiario')
			->setRenderer($rendererCodigoBeneficiario);
		$this->getField('fichaCodigoBeneficiario')
			->setRenderer($rendererCodigoBeneficiario);
		
        $this->getField('fichaInstrucao')
            ->setLabel('Instruções de responsabilidade do BENEFICIÁRIO.'
                . ' Qualquer dúvida sobre este boleto, contate o BENEFICIÁRIO.');
	}
	
	public function draw() {
		parent::draw();
	}
}