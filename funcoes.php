<?php
function salvarimagem(string $origemimagem, string $destinoimagens): void {
    move_uploaded_file($origemimagem, $destinoimagens);
}

function salvarcontacto(
    string $nome, string $apelido, string $email, string $telefone,
    string $cargo, string $empresa, string $categoria, string $cidade,
    string $origemimagem
): void {
    $id              = random_int(999, 999999);
    $nomeimagem      = "$id.jpg";
    $caminhoficheiro = "data/contactos.csv";
    $conteudo        = "$id;$nome;$apelido;$email;$telefone;$cargo;$empresa;$categoria;$cidade;$nomeimagem" . PHP_EOL;
    file_put_contents($caminhoficheiro, $conteudo, FILE_APPEND);
    salvarimagem($origemimagem, "uploads/$nomeimagem");
}

function editarContacto(
    string $id, string $nome, string $apelido, string $email,
    string $telefone, string $cargo, string $empresa,
    string $categoria, string $cidade
): void {
    $registosExistentes = listarContactos();
    $conteudo = "";

    foreach ($registosExistentes as $contacto) {
        if ($contacto["id"] === $id) {
            $nomeimagem = trim($contacto["foto"]);
            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] === UPLOAD_ERR_OK &&
                $_FILES['foto']['size'] > 0
            ) {
                $nomeimagem = "$id.jpg";
                salvarimagem($_FILES['foto']['tmp_name'], "uploads/$nomeimagem");
            }
            $conteudo .= "$id;$nome;$apelido;$email;$telefone;$cargo;$empresa;$categoria;$cidade;$nomeimagem" . PHP_EOL;
        } else {
            $cid  = $contacto["id"];
            $cn   = $contacto["nome"];
            $cap  = $contacto["apelido"];
            $cem  = $contacto["email"];
            $ctel = $contacto["telefone"];
            $ccar = $contacto["cargo"];
            $cemp = $contacto["empresa"];
            $ccat = $contacto["categoria"];
            $cci  = $contacto["cidade"];
            $cfot = trim($contacto["foto"]);
            $conteudo .= "$cid;$cn;$cap;$cem;$ctel;$ccar;$cemp;$ccat;$cci;$cfot" . PHP_EOL;
        }
    }
    file_put_contents("data/contactos.csv", $conteudo);
}

function listarContactos(): array {
    $caminhoficheiro = "data/contactos.csv";

    if (!file_exists($caminhoficheiro)) return [];

    $conteudo = file_get_contents($caminhoficheiro);
    $resultado = [];

    foreach (explode("\n", $conteudo) as $linha) {
        if (trim($linha) === "") continue;
        $c = explode(";", $linha);
        $resultado[] = [
            "id"        => $c[0]  ?? "",
            "nome"      => $c[1]  ?? "",
            "apelido"   => $c[2]  ?? "",
            "email"     => $c[3]  ?? "",
            "telefone"  => $c[4]  ?? "",
            "cargo"     => $c[5]  ?? "",
            "empresa"   => $c[6]  ?? "",
            "categoria" => $c[7]  ?? "",
            "cidade"    => $c[8]  ?? "",
            "foto"      => trim($c[9] ?? ""),
        ];
    }
    return $resultado;
}

/**
 * Pesquisa melhorada:
 * - Parcial: encontra "ana" dentro de "Anastácia"
 * - Case-insensitive e accent-insensitive (via normalização)
 * - Pesquisa em TODOS os campos: nome, apelido, email, telefone, cargo, empresa, cidade, categoria
 * - Suporte a múltiplas palavras (AND): "ana luanda" mostra só quem tem "ana" E "luanda"
 */
function pesquisarContactos(string $valor): array {
    if (trim($valor) === "") return listarContactos();

    $todos     = listarContactos();
    $filtrados = [];

    // Dividir a pesquisa em tokens (palavras)
    $tokens = array_filter(array_map('trim', explode(' ', $valor)));

    foreach ($todos as $r) {
        // Concatenar todos os campos num único texto para pesquisar
        $haystack = implode(' ', [
            $r['nome'], $r['apelido'], $r['email'],
            $r['telefone'], $r['cargo'], $r['empresa'],
            $r['cidade'], $r['categoria'],
        ]);

        $haystackNorm = normalizarTexto($haystack);

        // Verificar se TODOS os tokens estão presentes (pesquisa AND)
        $match = true;
        foreach ($tokens as $token) {
            if (strpos($haystackNorm, normalizarTexto($token)) === false) {
                $match = false;
                break;
            }
        }

        if ($match) {
            $filtrados[] = $r;
        }
    }

    return $filtrados;
}

/**
 * Normaliza texto para comparação:
 * - Minúsculas
 * - Remove acentos (transliteração UTF-8 → ASCII)
 */
function normalizarTexto(string $texto): string {
    // Converter para minúsculas
    $texto = mb_strtolower($texto, 'UTF-8');

    // Mapa de substituição de caracteres acentuados
    $de  = ['á','à','â','ã','ä','å','é','è','ê','ë','í','ì','î','ï',
             'ó','ò','ô','õ','ö','ú','ù','û','ü','ý','ÿ','ç','ñ',
             'ã','õ','â','ê','ô','à'];
    $para = ['a','a','a','a','a','a','e','e','e','e','i','i','i','i',
              'o','o','o','o','o','u','u','u','u','y','y','c','n',
              'a','o','a','e','o','a'];

    return str_replace($de, $para, $texto);
}
?>
