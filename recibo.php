<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>BrLink - Recibo de Pagamento</title>
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    label {
      margin-bottom: 10px;
    }

    input {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <form name="login" method="post">
    <input type="hidden" name="acao" value="nada">
    
    <label for="titulo">Digite o número do título ou boleto:</label>
    <input type="text" name="titulo" id="titulo" size="16" maxlength="16" required>
    
    <label for="chave">Digite a chave de segurança:</label>
    <input type="text" name="chave" id="chave" size="16" maxlength="16" required>
    
    <button type="submit">Enviar comprovante</button>
  </form>
</body>
</html>

<?php
if(isset($_POST["titulo"])){
$titulo=$_POST["titulo"];
if(isset($_POST["chave"])){
$chave=$_POST["chave"];}
if($chave == 'chave-segurança'){ # <---- COLOQUE AQUI SUA CHAVE DE SEGURANÇA
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

# PUXAR OS DADOS DO BOLETO NO BANCO DO MK-AUTH
$boleto="SELECT datavenc, datapag, valor, valorpag, coletor, formapag, login FROM sis_lanc where id = $titulo";
$res= mysqli_query($con,$boleto);
while($vreg=mysqli_fetch_row($res)){
# PODE PEGAR QUALQUER DADOS QUE QUISEREM, ESTES FOI OS QUE O CIRO QUIS PEGAR
 $datavenc = $vreg[0];
 $datapag = $vreg[1];
 $valor = $vreg[2];
 $valorpag = $vreg[3];
 $coletor = $vreg[4];
 $formapag = $vreg[5];
 $login = $vreg[6];
}

# PUXAR O NUMERO DO CLIENTE, QUE PODE PUCHAR MAS DADOS, COMO NA MINHA OPINIÃO, TERIA QUE PEGAR O NOME
$boleto="SELECT celular FROM sis_cliente where login = '$login'";
$res= mysqli_query($con,$boleto);
#$lin=mysqli_num_rows($res);
while($vreg=mysqli_fetch_row($res)){
 $celular = $vreg[0]; echo '<br>';
}

# URL DA EvolutionAPI
$apiUrl = 'http://{{baseURL}}/message/sendText/{{instance}}'; # DIGITE A URL AQUI

# DADOS 
$data = array(
    "number" => "$celular",
    "options" => array(
        "delay" => 1200,
        "presence" => "composing",
        "linkPreview" => false
    ),
    "textMessage" => array( # MENSSAGEM QUE É ENVIADA PARA O CLIENTE NO WHATSAPP.
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
" # LEMBRAR DE COLOCAR A MENSSAGEM DENTRO DAS ASPAS, DETALHE, ACENTOS NÃO FUNCIONA, E QUANDO VOCÊ COPIA UM TEXTO E COLA, ELE TAMBÉM NÃO FUNCIONA, TEM QUE DIGITAR
		)
);
# Converte JSON
$jsonData = json_encode($data);

# Inicializa o cURL 
$ch = curl_init($apiUrl);

# Configura cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'apikey: digite-seu-token' # DIGITE SEU TOKEN AQUI
));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

# Executa
$response = curl_exec($ch);

# Verifica erro
if (curl_errno($ch)) {
    echo 'Erro ao chamar a API: ' . curl_error($ch);
} else {
# echo 'Resposta da API: ' . $response;
      echo '<center>Menssagem enviada com sucesso!</center>';
}

# Feche seu cURL
curl_close($ch);
}}
?>
