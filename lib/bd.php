<?php
function conecta($host,$usuario, $senha,$banco,$porta) { 
	
	$pdo = new PDO(
	    'mysql:host='.$host.';port='.$porta.'dbname='.$banco.'',
	    ''.$usuario.'',
	    ''.$senha.''
	);

return $pdo;
}
?>
