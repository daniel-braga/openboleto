<?php
include '../vendor/autoload.php';

use OpenBoleto\Boleto;

$dataVencimento = DateTime::createFromFormat('d/m/Y', '20/05/2011');

$layoutParams = array(
	'especieDocumento' => '02',
	'nossoNumero' => '0000009500024',
	'numeroDocumento' => '65',
	'dataVencimento' => $dataVencimento,
	'dataDocumento' => DateTime::createFromFormat('d/m/Y', '09/05/2011'),
	'dataProcessamento' => DateTime::createFromFormat('d/m/Y', '09/05/2011'),
	'valorDocumento' => 938.50,
	'nomeSacado' => 'RACA TRANSPORTES LTDA',
	'enderecoSacado' => "RODOVIA ANHANGUERA SN KM 24 05276-000",
	'demonstrativo' => "",
	'instrucao' => "COMISSAO PERMANENCIA AO DIA R$ 1,56",
	'codigoCedente' => '4533763',
	'pontoVenda' => '4562',
	'identificacao' => '',
	'cpfCnpjCedente' => '',
	'enderecoCedente' => '',
	'cidadeCedente' => '',
	'estadoCedente' => '',
	'carteira' => '101',
	'cedente' => 'OBJECT SISTEMAS MULTIMIDIA LTDA'
	
);

$boleto = Boleto::factory('pdf', 'santander', $layoutParams);
$boleto->output();