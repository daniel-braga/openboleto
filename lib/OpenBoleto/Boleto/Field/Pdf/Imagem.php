<?php

namespace OpenBoleto\Boleto\Field\Pdf;

use OpenBoleto\Boleto\Field\Pdf as PdfField;
use OpenBoleto\Boleto\Pdf as BoletoPdf;
use ZendPdf\Image;

class Imagem extends PdfField
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
		$y1 = BoletoPdf::translateYPosition($y2+$barcodeImageHeight);
		$y2 = BoletoPdf::translateYPosition($y2);

		$this->_container->drawImage($pdfImageResource, $x1, $y1, $x2, $y2);
	}
}
