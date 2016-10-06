<?php
use NVCH\HideIT\HideIT;
require_once("../../lib/HideIT.class.php");

$HideIT_obj = new HideIT();

try
{
	$HideIT_obj->LoadImage("./css.jpg");
}catch(Exception $e)
{
	die($e);
}

$msg = "Lorem ipsum dolor sit amet, eam dolor consulatu cu. Duo et iuvaret expetenda intellegat, in quodsi senserit imperdiet vix. Fugit munere melius at qui, pri eripuit liberavisse id. Sit ei sumo platonem.

Ius ne tation sensibus, tation eligendi vel at. Persius debitis et eam, te aperiri fierent eos. Id posse tantas sensibus pri. Est utroque praesent repudiandae ei. Ei salutandi deseruisse mea, ocurreret evertitur at eos. Cu zril deleniti verterem mea, semper suavitate qui ei. Consul aeterno platonem sit ei.

Qui habeo nostrud aliquid ad. Aperiri impedit persequeris eu pro. Mei accusata sadipscing ne. Mel voluptaria expetendis ad, ad sale tation rationibus mel.

Saperet dolorum admodum ea qui. In saepe saperet consequat vix. Cu tota definitionem pri, facete maiestatis eu eum. No modo lobortis disputationi vix, sed ullum mucius interesset ut, vix et dicta ceteros oporteat. Quot invidunt voluptatibus ea eam, id quo case erant consequat, pri in soluta petentium definitiones.

Ne mea veri nulla disputationi, ex brute alterum insolens vim, nam eligendi apeirian democritum no. Mei id vivendum iracundia, odio mediocritatem ei mea. Nec id tale justo, usu nihil equidem at, quod suas primis te quo. Ex feugiat philosophia mei.";

if (strlen($msg)*8 < $HideIT_obj->MaxSpace)
{
	$HideIT_obj->HideIT($msg);
}

try
{
	$HideIT_obj->SaveIT('png',"./css2.png");
}catch(Exception $e)
{
	die($e);
}
?>