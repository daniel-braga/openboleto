<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field\Imagem as ImagemField;
use OpenBoleto\Boleto\AbstractBoleto;
use ZendPdf\Page;
use ZendPdf\Color;

class LogoBanco extends ImagemField
{
	public function draw() 
	{
		parent::draw();
	}
}
