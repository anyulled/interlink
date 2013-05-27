<?php
if(isset($_POST["string"]))
{
	$string = $_POST["string"];
	echo $string." ".md5($string);
}
?>
<form method="post">
<input name="string" type="text"/>
<input type="submit" name="enviar"/>
</form>
<?php
phpinfo();
?>