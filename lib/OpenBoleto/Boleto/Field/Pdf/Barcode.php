<?php

namespace OpenBoleto\Boleto\Field\Pdf;

use OpenBoleto\Boleto\Field\Pdf as PdfField;
use OpenBoleto\Boleto\Pdf as BoletoPdf;
use Zend\Barcode\Barcode as ZendBarcode;
use ZendPdf\Image;

class Barcode extends PdfField
{
	public function draw() 
	{
		$x1 = $this->_x+2;
		$y2 = $this->_y+2;
		
		$barcode = $this->_container->getLayout()->getCodigoBarra();
		$imageResource = ZendBarcode::draw(
			'code25interleaved', 'image', array(
				'text' => $barcode,
				'drawText' => false,
				'withQuietZones' => false,
				'mandatoryQuietZones' => false
			), array(
				'imageType' => 'jpeg',
				'height' => 52,
				'width' => 405
			)
		);
		
		$tempFileName = sprintf('/tmp/%s.jpeg', $barcode);
		if (false === imagejpeg($imageResource, $tempFileName)) {
			throw new \Exception(sprintf("Não foi possível criar o arquivo %s", $tempFileName));
		}
		
		$pdfImageResource = Image::imageWithPath($tempFileName);
		$barcodeImageHeight = ($pdfImageResource->getPixelHeight()/96) * 72; // pixels to points at 96dpi
		$barcodeImageWidth = ($pdfImageResource->getPixelWidth()/96) * 72; // pixels to points at 96dpi
		
		$x2 = $x1+$barcodeImageWidth;
		$y1 = BoletoPdf::translateYPosition($y2+$barcodeImageHeight);
		$y2 = BoletoPdf::translateYPosition($y2);
		
		$this->_container->drawImage($pdfImageResource, $x1, $y1, $x2, $y2);
	}
}
