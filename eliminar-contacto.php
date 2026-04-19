<?php
print_r($_GET);

require "funcoes.php";

// 1. Buscar todos os registros
 $idcontacto = $_GET["id"];

 $registosExistentes = listarContactos();

 // 
 $registosfiltrados =[];
 foreach ($registosExistentes as $registo){
    if ($registo["id"] === $idcontacto) {
        continue;
    }
    $registosfiltrados[] = $registo;
 }
 $caminhoficheiro = "data/contactos.csv";
 $conteudo = "";

 foreach ($registosfiltrados as $contacto){
            
            $id = $contacto["id"];
            $nome = $contacto["nome"];
            $apelido = $contacto["apelido"];
            $email = $contacto["email"];
            $telefone = $contacto["telefone"];
            $cargo = $contacto["cargo"];
            $empresa = $contacto["empresa"];
            $categoria = $contacto["categoria"];
            $cidade = $contacto["cidade"];
            $nomeimagem = $contacto["foto"];

            $conteudo = $conteudo . "$id;$nome;$apelido;$email;$telefone;$cargo;$empresa;$categoria;$cidade;$nomeimagem" . PHP_EOL;

 }
 file_put_contents($caminhoficheiro, $conteudo);
 header("Location: /Laboratorio");
 

