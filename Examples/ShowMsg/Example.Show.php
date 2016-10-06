<?php
use NVCH\HideIT\HideIT;
require_once("../../lib/HideIT.class.php");

$HideIT_obj = new HideIT();

try
{
	$HideIT_obj->LoadImage("./css2.png");
}catch(Exception $e)
{
	die($e);
}

try
{
	echo $HideIT_obj->ShowIT();
}catch(Exception $e)
{
	die($e);
}
?>