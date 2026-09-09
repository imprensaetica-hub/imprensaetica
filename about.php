<?php require_once('Connections/conn_pwr.php'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Imprensa &Eacute;tica - Observat&oacute;rio Jornal&iacute;stico</title>
<link href="models.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
    include 'header.php';
    
    // Buscar integrantes da equipe
    $sql_equipe = "SELECT usr_nm FROM pwr_usr ORDER BY usr_nm";
    $result_equipe = $conn_pwr->query($sql_equipe);
?>

<div class="container-principal">
    <div class="sessao-assunto">
        <div class="linha-assunto-titulo">Sobre o Imprensa Ética</div>
        <div class="conteudo-assunto">
            <div class="coluna-principal">
                <div class="texto_materia">
                    <p>O <strong>Imprensa Ética</strong> é um observatório jornalístico independente dedicado a promover a integridade, transparência e qualidade no jornalismo brasileiro e internacional. Nossa missão é:</p>
                    
                    <ul>
                        <li>Monitorar e analisar a cobertura jornalística nos principais veículos de comunicação;</li>
                        <li>Identificar e destacar boas práticas jornalísticas;</li>
                        <li>Alertar sobre possíveis violações éticas no exercício da profissão;</li>
                        <li>Promover o debate sobre os desafios e responsabilidades do jornalismo contemporâneo;</li>
                        <li>Defender a liberdade de imprensa e o direito à informação de qualidade;</li>
                        <li>Denunciar corriqueirismo dentro de práticas desleais de publicidade disfarçadas de informação.</li>
                    </ul>
                    
                    <p>Acreditamos que um jornalismo ético, responsável e independente é fundamental para uma sociedade democrática e justa. Nossa equipe é composta por profissionais e especialistas comprometidos com esses valores.</p>
                    
                    <p>Se você compartilha desses ideais e quer contribuir diretamente com o nosso trabalho, <a href="contato.php">entre em contato com a nossa equipe</a>.</p>
                </div>
            </div>
            
            <div class="coluna-secundaria">
                <div class="linhas-box-lateral">Nossa Equipe de Colaboradores</div>
                <ul class="lista-secundaria">
                    <?php
                    if ($result_equipe->num_rows > 0) {
                        while($row = $result_equipe->fetch_assoc()) {
                            $nome_integrante = htmlspecialchars($row['usr_nm']);
                            echo "<li><a href='integrante.php?usr_nm=" . urlencode($row['usr_nm']) . "'>$nome_integrante</a></li>";
                        }
                    } else {
                        echo "<li>Nenhum integrante encontrado</li>";
                    }
                    ?>
                </ul>
                
                <div class="linhas-box-lateral">Expediente</div>
                <ul class="lista-secundaria" style="font-family:'Palatino Linotype', 'Book Antiqua', Palatino, serif; color:#900">
                <li><b>ADMINISTRAÇÃO E EDITOR-CHEFE</b></li><li>
Bernardo Cahue</li>
                <HR>
                <li><b>EDITORIAS</b></li>
                <li><b>Internacional, Política, Cidades e Análises</b>: <br>
Bernardo Cahue</li>
                <li><b>Política e Tecnologia</b>: <br>
Eduardo Lima</li>
                </ul>
                
                <div class="mais-noticias">
                    <a href="login.php" target="_blank">Área do Editor →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '1bann_hor300.php';
include '7bann_vert160.php';
include 'footer.php'; 
include '0above_body.php';
?>
</body>
</html>