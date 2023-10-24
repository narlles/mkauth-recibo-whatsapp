<html lang="pt-br">
<head>
<meta charset="utf-8" />
<title>Brlink</title>
</head>
<body>
<br>
<br>
<br>
<div align=center>
<form name="login" method="post" >
<input type="hidden" name="acao" value="nada" /> 
<table >
<tr>
<td>
<font color='black'><strong>Digite o numero do titulo ou boleto:</strong></font>
</td>
</tr>
<tr>
<td>
<input type="text" name="titulo" size="16" maxlength="16" value=""/>
</td>
<td>
</tr>
<tr>
<td>
<font color='black'><strong>Digite a chave de segurança:</strong></font>
</td>
</tr>
<tr>
<td>
<input type="text" name="chave" size="16" maxlength="16" value=""/>
</td>
<td>
</tr>
<tr>
<td>
<button type="submit">Enviar comprovante</button>
</td>
</tr>
</table>
</form>    
<center>
</div>
</body>
</html>


<?php
if(isset($_POST["titulo"])){
$titulo=$_POST["titulo"];
if(isset($_POST["chave"])){
$chave=$_POST["chave"];}
if($chave == 'chave-segurança'){
# CONEXÃO COM O BANCO DE DADOS DO MK-AUTH
$host = "localhost";
$usuario = "root";
$senha = "vertrigo";
$db = "mkradius";
$mysqli = new mysqli($host, $usuario, $senha, $db);
if($mysqli->connect_errno)
echo "Falha na conexão: (".$mysqli->connect_errno.") ".$mysqli->connect_error;
$con = mysqli_connect("$host","$usuario","$senha");
mysqli_select_db($con,"$db");

# PUXAR OS DADOS DO CLIENTE
$boleto="SELECT datavenc, datapag, valor, valorpag, coletor, formapag, login FROM sis_lanc where id = $titulo";
$res= mysqli_query($con,$boleto);
#$lin=mysqli_num_rows($res);
while($vreg=mysqli_fetch_row($res)){
 $datavenc = $vreg[0]; echo '<br>';
 $datapag = $vreg[1]; echo '<br>';
 $valor = $vreg[2]; echo '<br>';
 $valorpag = $vreg[3]; echo '<br>';
 $coletor = $vreg[4]; echo '<br>';
 $formapag = $vreg[5]; echo '<br>';
 $login = $vreg[6]; echo '<br>';
}

# PUXAR OS DADOS DO CLIENTE
$boleto="SELECT celular FROM sis_cliente where login = '$login'";
$res= mysqli_query($con,$boleto);
#$lin=mysqli_num_rows($res);
while($vreg=mysqli_fetch_row($res)){
 $celular = $vreg[0]; echo '<br>';
}


// URL da EvolutionAPI
$apiUrl = 'http://{{baseURL}}/message/sendText/{{instance}}'; # DIGITE A URL AQUI

// Dados 
$data = array(
    "number" => "$celular",
    "options" => array(
        "delay" => 1200,
        "presence" => "composing",
        "linkPreview" => false
    ),
    "textMessage" => array(
        "text" => "
Mensagem Automatica de Recebimento de Pagamento
Pagamento recebido em: *$datapag*
Fatura com vencimento em: $datavenc
Valor da fatura: R$ $valor
Valor do pagamento: R$ $valorpag
Pagamento recebido por: $coletor
Forma de pagamento: $formapag

Para segunda via e comprovantes dos pagamentos acesse:
https://brlink.org/cliente ( Coloque o CPF do titular )
"
		)
);
// Converte JSON
$jsonData = json_encode($data);

// Inicializa o cURL 
$ch = curl_init($apiUrl);

// Configura cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'apikey: digite-seu-token' # DIGITE SEU TOKEN AQUI
));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

// Executa
$response = curl_exec($ch);

// Verifica erro
if (curl_errno($ch)) {
    echo 'Erro ao chamar a API: ' . curl_error($ch);
} else {
    # echo 'Resposta da API: ' . $response;
      echo '<center>Menssagem enviada com sucesso!</center>';
}

// Feche seu cURL
curl_close($ch);
}}
?>