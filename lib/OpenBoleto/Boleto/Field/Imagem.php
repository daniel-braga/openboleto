<?php

namespace OpenBoleto\Boleto\Field;

use OpenBoleto\Boleto\Field;
use OpenBoleto\Boleto\AbstractBoleto;
use ZendPdf\Image;

class Imagem extends Field
{
	public function draw() 
	{
		$x1 = $this->_x+1;
		$y2 = $this->_y;
		
		$value = $this->_dataProperty ? $this->getDataPropertyValue() : $this->getValue();
		
		$pdfImageResource = Image::imageWithPath($value);
		$barcodeImageHeight = ($pdfImageResource->getPixelHeight()/96) * 72; // pixels to points at 96dpi
		$barcodeImageWidth = ($pdfImageResource->getPixelWidth()/96) * 72; // pixels to points at 96dpi
		
		$x2 = $x1+$barcodeImageWidth;
		$y1 = AbstractBoleto::translateYPosition($y2+$barcodeImageHeight);
		$y2 = AbstractBoleto::translateYPosition($y2);

		$this->_boleto->drawImage($pdfImageResource, $x1, $y1, $x2, $y2);
	}
}
