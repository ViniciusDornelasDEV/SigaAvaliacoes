<?php 
mb_internal_encoding("iso-8859-1");
$conecta = mysql_connect("mysql01.timesistemas1.hospedagemdesites.ws", "timesistemas1", "sqlyt4da51241") or print (mysql_error()); 
mysql_select_db("timesistemas1", $conecta) or print(mysql_error()); 
//$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
//mysql_select_db("bd_siga", $conecta) or print(mysql_error()); 



$sql = 'SELECT * FROM tb_empresa';
$empresas = mysql_query($sql, $conecta);

while ($empresa = mysql_fetch_array($empresas)) {
    $sql = 'SELECT * FROM tb_campo WHERE id > 308 AND id < 344';
    $campos = mysql_query($sql, $conecta);
    while ($campo = mysql_fetch_array($campos)) {
        $sql = 'INSERT INTO tb_campo_empresa (aba, campo, empresa, categoria_questao, aparecer, obrigatorio, label) VALUES (
            '.$campo['aba'].',
            '.$campo['id'].',
            '.$empresa['id'].',
            '.$campo['categoria_questao'].',
            "S",
            "S",
            "'.$campo['label'].'"
        );';

        mysql_query($sql);
    }
    echo 'Empresa '.$empresa['id']."\n";
}

/*$sql = 'SELECT * FROM tb_empresa WHERE ativo = "S"';
$empresas = mysql_query($sql, $conecta);
while ($empresa = mysql_fetch_array($empresas)) {
    for ($i=309; $i <= 343; $i++) { 
        $sql = 'INSERT INTO ';
    }
}*/

/*
//CRIAR MÈDICOS

//pesquisar todos os médicos
$sql = 'SELECT m.*, e.nome_empresa 
        FROM tb_medicos AS m
        INNER JOIN tb_empresa AS e ON e.id = m.empresa
        ORDER BY m.empresa';
$medicos = mysql_query($sql, $conecta);
mysql_query("START TRANSACTION;");
//percorrer médicos
$idEmpresa = 0;
while ($medico = mysql_fetch_array($medicos)) {
    if($idEmpresa != $medico['empresa']){
        if($idEmpresa != 0){
            fclose($arquivo);
        }
        $arquivo = fopen('public/medicos/'.$medico['nome_empresa'].'.txt', 'w');
        $idEmpresa = $medico['empresa'];
    }
    //receber nome em vetor
    $letras = array('as', 'po', 'wq', 'uy', 'rt', 'bo', 'qz', 'nb', 'xy', 'zv', 'mn', 'nm', 'lk');
    $nomeCompleto = trim($medico['nome_medico']);
    $nome = explode(' ', $medico['nome_medico']);
    $senha = $letras[rand(0, 12)].rand(10, 999).$letras[rand(0, 12)].date('s');
    $senha_bcrypt = password_hash($senha, PASSWORD_BCRYPT);
    //$senha = '123';
    //$senha_bcrypt = '123';
    
    //tentar inserir com 1º nome
    $sql = gerarSql($medico['nome_medico'], $nome[0], $medico['id'], $senha_bcrypt);
    if(mysql_query($sql)){
       //escrever no arquivo
        fwrite($arquivo, 'Nome: '.$nomeCompleto."\r\n".'Login: '.$nome[0]."\r\n".'Senha: '.$senha."\r\n"."\r\n");
    }else{
        //se não conseguir tentar 1º+2º nome
        $sql = gerarSql($medico['nome_medico'], $nome[0].$nome[1], $medico['id'], $senha_bcrypt);
        if(mysql_query($sql)){
            //escrever no arquivo
            fwrite($arquivo, 'Nome: '.$nomeCompleto."\r\n".'Login: '.$nome[0].$nome[1]."\r\n".'Senha: '.$senha."\r\n"."\r\n");
        }else{
            $erro = true;
            $contador = 0;
            while ($erro == true) {
                $contador++;
                $sql = gerarSql($medico['nome_medico'], $nome[0].$nome[1].$contador, $medico['id'], $senha_bcrypt);
                if(mysql_query($sql)){
                   $erro = false;
                   //escrever no arquivo
                   fwrite($arquivo, 'Nome: '.$nomeCompleto."\r\n".'Login: '.$nome[0].$nome[1].$contador."\r\n".'Senha: '.$senha."\r\n"."\r\n");
                }
            }    
        }
    }
}
fclose($arquivo);       

mysql_query("COMMIT;");

die('FIM!');

function gerarSql($nome, $login, $medico, $senha){
    $sql = 'INSERT INTO tb_usuario (nome, login, senha, id_usuario_tipo, medico) VALUES 
            (
                "'.$nome.'",
                "'.$login.'",
                "'.$senha.'",
                9,
                '.$medico.'
            );';
    return $sql;
}
*/



    //CRIAR AVALIAÇÕES PERSONALIZADAS
  /*  $sql = 'SELECT * FROM tb_empresa'; 
    $empresas = mysql_query($sql, $conecta);  

    $sql = 'SELECT * FROM tb_campo WHERE aba = 8';
    $camposBase = mysql_query($sql, $conecta);
    $campos = array();
    while ($arrayCampos = mysql_fetch_array($camposBase)) {
        $campos[] = $arrayCampos;
    }
    
    $naoObrigatorio = array('meta_agendamento', 'meta_agendamento_mensal', 'meta_agendamento_mensal_dias', 'meta_mensal', 'dias_meta');
    while ($empresa = mysql_fetch_array($empresas)) {
        $cont = 0;
        foreach ($campos as $campo) {

            if(in_array($campo['nome_campo'], $naoObrigatorio)){
                $obrigatorio = 'N';
            }else{
                $obrigatorio = 'S';
            }

            $sqlInsert = 'INSERT INTO tb_campo_empresa (aba, campo, empresa, categoria_questao, aparecer, obrigatorio, label) 
                        VALUES ('.$campo['aba'].', '.$campo['id'].', '.$empresa['id'].', '.$campo['categoria_questao'].', "S", "'.$obrigatorio.'", "'.$campo['label'].'");';
            if(mysql_query($sqlInsert)){
                $cont++;
            }else{
                echo 'ERRO: '.mysql_error()."\n";
                die('ERRO AO INSERIR REGISTRO NO BANCO! empresa: '.$empresa['id']);
            }
        }   
        //$sqlInsert = 'INSERT INTO tb_campo_empresa (aba, campo, empresa, categoria_questao, aparecer, obrigatorio, label) 
         //           VALUES (8, 129, '.$empresa['id'].', 28, "S", "N", "'.utf8_decode("Meta mensal de Agendamento RM").'");';


        
        echo 'Inseri '.$cont.' campos para a empresa: '.$empresa['id'].' - empresa personalizada!'."\n";
        $cont = 0;
    }*/

//CORRIGIR ERRO DE CARACTER
/*    var_dump($_SERVER);
    
    die($_SERVER['SERVER_ADDR']);

    mb_internal_encoding("UTF-8");
    $conecta = mysql_connect("mysql01.timesistemas1.hospedagemdesites.ws", "timesistemas1", "sqlyt4da51241") or print (mysql_error()); 
    mysql_select_db("timesistemas1", $conecta) or print(mysql_error()); 
    $sql = 'SELECT * FROM tb_empresa ORDER BY id'; 
    $empresas = mysql_query($sql, $conecta);  

    //Pesquisar arquivos
    $sql = 'SELECT * FROM tb_empresa_arquivos WHERE empresa = 1';
    $arquivos2 = mysql_query($sql, $conecta);
    $arquivos = array();
    while($arquivo = mysql_fetch_array($arquivos2)) { 
       $arquivos[] = $arquivo; 
    }

    while ($empresa = mysql_fetch_array($empresas)) {
        if($empresa['id'] == 1){
            continue;
        }
        
        //copiar arquivos
        $cont = 1;
        
        foreach ($arquivos as $arquivo) {
            $cont++;
            $novoEndereco = str_replace('/1/', '/'.$empresa['id'].'/', $arquivo['arquivo']);
            $nome = utf8_decode($arquivo['nome']);
            $sqlInsert = 'INSERT INTO tb_empresa_arquivos (nome, arquivo, empresa) VALUES ("'.$arquivo['nome'].'", "'.$novoEndereco.'", '.$empresa['id'].');';
            if(mysql_query($sqlInsert)){
                echo 'Inseri na base de dados!'."\n";
            }else{
                echo 'ERRO: '.mysql_error()."\n";
                die('ERRO AO INSERIR REGISTRO NO BANCO! empresa: '.$empresa['id']);
            }
        }
        
        echo $empresa['id'].' - empresa copiada,'.$cont.' arquivos!'."\n";;
    }

    die('fimMigracao');*/

// +++!!!! SCRIPT DE REPLICAÇÃO DE ARQUIVOS
/*    //DELETE FROM tb_empresa_arquivos WHERE empresa != 1;

    //migrar dados
    $conecta = mysql_connect("mysql01.timesistemas1.hospedagemdesites.ws", "timesistemas1", "sqlyt4da51241") or print (mysql_error()); 
    mysql_select_db("timesistemas1", $conecta) or print(mysql_error()); 
    $sql = 'SELECT * FROM tb_empresa ORDER BY id'; 
    $empresas = mysql_query($sql, $conecta);  

    //Pesquisar arquivos
    $sql = 'SELECT * FROM tb_empresa_arquivos WHERE empresa = 1';
    $arquivos2 = mysql_query($sql, $conecta);
    $arquivos = array();
    while($arquivo = mysql_fetch_array($arquivos2)) { 
       $arquivos[] = $arquivo; 
    }

    while ($empresa = mysql_fetch_array($empresas)) {
        if($empresa['id'] == 1){
            continue;
        }
        $dir = 'public/arquivos/'.$empresa['id'].'/arquivos';
        if(!file_exists($dir)){
            mkdir($dir);
        }

        //copiar arquivos
        $cont = 1;
        
        foreach ($arquivos as $arquivo) {
            $cont++;
            $novoEndereco = str_replace('/1/', '/'.$empresa['id'].'/', $arquivo['arquivo']);
            echo $arquivo['arquivo']."\n";
            echo '   NOVO   '.$novoEndereco."\n";
            $nome = utf8_encode($arquivo['nome']);
            $sqlInsert = 'INSERT INTO tb_empresa_arquivos (nome, arquivo, empresa) VALUES ("'.$nome.'", "'.$novoEndereco.'", '.$empresa['id'].');';
            if(mysql_query($sqlInsert)){
                echo 'Inseri na base de dados!'."\n";
            }else{
                echo 'ERRO: '.mysql_error()."\n";
                die('ERRO AO INSERIR REGISTRO NO BANCO! empresa: '.$empresa['id']);
            }
            //CASO EXISTA O ARQUIVO DELETAR
            if(file_exists($novoEndereco)){
                unlink($novoEndereco);
            }

            //COPIAR ARQUIVO
            if(copy($arquivo['arquivo'], $novoEndereco)){
                echo 'copiei arquivo!'."\n";
            }else{
                die('ERO AO COPIAR ARQUIVO! empresa: '.$empresa['id'].' arquivo: '.$arquivo['id']);
            }

        }
        echo $empresa['id'].' - empresa copiada,'.$cont.' arquivos!'."\n";;
    }

    die('fimMigracao');   */
?>