<?php
 
 date_default_timezone_set('America/Lima');
require __DIR__ . '/ticket/autoload.php'; //Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
 
/*
	Este ejemplo imprime un hola mundo en una impresora de tickets
	en Windows.
	La impresora debe estar instalada como genérica y debe estar
	compartida
*/
 
/*
	Conectamos con la impresora
*/
 
 
/*
	Aquí, en lugar de "POS-58" (que es el nombre de mi impresora)
	escribe el nombre de la tuya. Recuerda que debes compartirla
	desde el panel de control
*/
 
$nombre_impresora = "SAT15TUS2"; 
 
 
$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);
 
/*
	Imprimimos un mensaje. Podemos usar
	el salto de línea o llamar muchas
	veces a $printer->text()
*/


$printer->text(" ------------------------------ \n");
try{
	$logo = EscposImage::load("logo.png", false);
    $printer->bitImage($logo);
}catch(Exception $e){}

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("\n ¡Gracias por tu pedido!\n ");
$printer->text("\n Tu pedido es el: " . $_GET["pedido"] ." \n ");
$printer->text("\n Valor a pagar: $ " . number_format($_GET["valor"], 0, ',', '.') ." \n ");

try{
		$contador_numeros_pedido = strlen($_GET["pedido"]);

		$ceros_faltantes = 7 - $contador_numeros_pedido;

		$numeros_pedido = "";

		for($i=1; $i<=$ceros_faltantes; $i++){
			$numeros_pedido .= "*";
		}

		$numeros_pedido .= $_GET["pedido"] . "*";

		echo $numeros_pedido;

		$ch = curl_init('http://localhost/barcode/barcode.php?f=png&s=upc-e&d=' . $numeros_pedido . '&w=400&h=150&pl=0&pr=0&ts=10&th=20&pl=30&pr=30');
		$fp = fopen('barimg/aa.png', 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

	$codigo_barras = EscposImage::load("barimg/aa.png", false);
    $printer->bitImage($codigo_barras);
}catch(Exception $e){}

$printer->text("\n" . date("d-m-Y H:i:s") . "\n");
$printer->text(" ------------------------------ \n \n \n");

/*
	Hacemos que el papel salga. Es como 
	dejar muchos saltos de línea sin escribir nada
*/
$printer->feed();
 
/*
	Cortamos el papel. Si nuestra impresora
	no tiene soporte para ello, no generará
	ningún error
*/
$printer->cut();
 
/*
	Por medio de la impresora mandamos un pulso.
	Esto es útil cuando la tenemos conectada
	por ejemplo a un cajón
*/
$printer->pulse();
 
/*
	Para imprimir realmente, tenemos que "cerrar"
	la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
*/
$printer->close();
?>