<?php
require "funcoes.php";
// Verificar se o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Recuperar dados
$nome = $_POST['primeiro_nome'];
$apelido = $_POST['apelido'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$cargo = $_POST['cargo'];
$empresa = $_POST['empresa'];
$categoria = $_POST['categoria'];
$cidade = $_POST['cidade'];

// Validar upload da imagem
    
$caminhoimagem = $_FILES['foto']['tmp_name'];

//Salvar dados
salvarcontacto(
$nome,
 $apelido,
 $email,
 $telefone,
 $cargo,
 $empresa,
$categoria,
$cidade,
$caminhoimagem
);
}
// Redirecionar o usuario na Página Inicial
header("Location: /Laboratorio");

// #### Eliminar contacto ####
POST /eliminar-contacto.php
?>