<?php
$adServidor = $argv[1];
$stTempo    = $argv[2];

if (trim($adServidor) != "" && trim($stTempo) != "" ) { 

include_once 'lib/bd.php';
include_once'lib/phpmailer/class.phpmailer.php';

$mail             = new PHPMailer();
$mail->IsSMTP(); 

$mail->SMTPAuth   = true;                  
$mail->SMTPSecure = "tls";                 
$mail->Host       = "";
$mail->Port       = 0;
$mail->Username   = "";
$mail->Password   = "";

$mail->SetFrom('mail_de_origem', 'Remetente');

$mail->AddReplyTo("responder_para","");



$stHost=$adServidor;
$inPorta=13306;
$i=0;
	$con = conecta($stHost,$stUsuario, $stSenha,$stBanco,$inPorta); 
	if ($con ) { 
		$endereco1 = "endereco@email.com";
		$endereco2 = "outro_endereco@email";
		while ( $i <=(int) $stTempo ) { 
			$stQuery = $con->prepare('show slave status');
			$stQuery->execute();

			while ($rs = $stQuery->fetch(PDO::FETCH_OBJ)) {

			    $stRetorno = "Monitorando o IP ".$stHost."\n\n";

        		    $stRetorno .=  "Hora: ".date("d/m/Y H:i:s")."\n\n";
			    $stRetorno .= "Master Host: ".$rs->Master_Host."\n";
			    $stRetorno .= "Ultimo Status: ".$rs->Slave_SQL_Running_State."\n";
			    $stRetorno .= "Slave IO Running: ".$rs->Slave_IO_Running."\n";
			    $stRetorno .= "Slave SQL Running: ".$rs->Slave_SQL_Running."\n";
			    $stRetorno .= "Seconds Behind Master: ".$rs->Seconds_Behind_Master."\n";
			    $stRetorno .= " ------------------------------------------------- \n";
			
			    $stArquivo = $stHost.'_'.date('d-m-Y').'.txt';

		    	    if ($i == 1 ) { 				
					$mail->Subject    = "Monitoramento - Início em ".date("d/m/Y H:i:s");
					$mail->Body = $stRetorno;

					
					$mail->AddAddress($endereco1, "Fulano");
					$mail->AddAddress($endereco2, "Ciclano");

					if(!$mail->Send()) {
					  echo "( Start Replicação) Email Erro: " . $mail->ErrorInfo;
					} else {
					  echo "Mensagem enviada!";
					}

				}

				if ($rs->Seconds_Behind_Master > 0) { 

		 			    $stRetorno .= "Anomalia detectada monitorando o ".$stHost."\n\n";
					    $stRetorno .=  "Hora: ".date("d/m/Y H:i:s")."\n\n";
					    $stRetorno .= "Distância do master em segundos: ".$rs->Seconds_Behind_Master."\n";
					    $stRetorno .= " ------------------------------------------------- \n";

					    $mail->Subject    = "Anomalia monitorando ".$stHost;
					    $mail->Body = $stRetorno;
					    
					   			
						$mail->AddAddress($endereco1, "Fulano");
						$mail->AddAddress($endereco2, "Ciclano");

					    if(!$mail->Send()) {
					        echo "( Replicação) Email Erro: " . $mail->ErrorInfo;
					    } else {
					        echo "Erro na replicação ( behind ). Mensagem enviada!";
					    }

				}

				if ($rs->Slave_IO_Running == "No" or  $rs->Slave_SQL_Running == "No") { 

		 			    $stRetorno .= "Monitorando o ".$stHost."\n\n";
					    $stRetorno .=  "Hora: ".date("d/m/Y H:i:s")."\n\n";
					    $stRetorno .= "Erro: ".$rs->Slave_SQL_Running_State."\n";
					    $stRetorno .= " AVISO: O programa de monitoramento será encerrado! \n";
					    $stRetorno .= " ------------------------------------------------- \n";

					    $mail->Subject    = "Monitoramento - Erro";
					    $mail->Body = $stRetorno;
				  			
						$mail->AddAddress($endereco1, "Fulano");
						$mail->AddAddress($endereco2, "Ciclano");

					    if(!$mail->Send()) {
					        echo "( Replicação) Email Erro: " . $mail->ErrorInfo;
					    } else {
					        echo "Erro na replicação. Mensagem enviada!";
					    }


				}


		    $fp = fopen($stArquivo, "a+");
		    fwrite($fp, $stRetorno); 
		    fclose($fp);    

		    // Encerrando monitoramento

		if ($i  == (int) $stTempo) { 

		 			    $stRetorno .= " Monitorando o ".$stHost."\n\n";
					    $stRetorno .= " Encerrado em ".date("d/m/Y H:i:s")."\n\n";
					    $stRetorno .= " Status: ".$rs->Slave_SQL_Running_State."\n";
					    $stRetorno .= " AVISO: O programa de monitoramento será encerrado! \n";
					    $stRetorno .= " ------------------------------------------------- \n";

					    $mail->Subject    = "Monitoramento - Encerramento";
					    $mail->Body = $stRetorno;
					    
						$mail->AddAddress($endereco1, "Fulano");
						$mail->AddAddress($endereco2, "Ciclano");
					    $mail->AddAttachment($stArquivo, $stArquivo); 

					    if(!$mail->Send()) {
					        echo "( Encerramento ) Email Erro: " . $mail->ErrorInfo;
					    } else {
					        echo "Encerramento. Mensagem enviada!";
					    }

					   //die;


				}

			}

			sleep(30);
			$i++;

		}
	} 	
} else 
{
echo "Preencher IP e tempo!"; 
die;
}
?>