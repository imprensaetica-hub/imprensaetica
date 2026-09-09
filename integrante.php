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
    
    // Verificar se o parâmetro usr_nm foi passado
    if (!isset($_GET['usr_nm']) || empty($_GET['usr_nm'])) {
        echo "<script>window.location.href = 'sobre.php';</script>";
        exit;
    }
    
    // Buscar informações do integrante
    $usr_nm = $_GET['usr_nm'];
    $sql_integrante = "SELECT usr_nm, usr_foto, usr_ptf FROM pwr_usr WHERE usr_nm = ?";
    $stmt = $conn_pwr->prepare($sql_integrante);
    $stmt->bind_param("s", $usr_nm);
    $stmt->execute();
    $result_integrante = $stmt->get_result();
    
    if ($result_integrante->num_rows == 0) {
        echo "<script>window.location.href = 'sobre.php';</script>";
        exit;
    }
    
    $integrante = $result_integrante->fetch_assoc();
    $imagem_url = htmlspecialchars($integrante['usr_foto']);
    $portfolio = htmlspecialchars($integrante['usr_ptf']);
    
    // Buscar integrantes da equipe para a lista lateral
    $sql_equipe = "SELECT usr_nm FROM pwr_usr ORDER BY usr_nm";
    $result_equipe = $conn_pwr->query($sql_equipe);
?>

<div class="container-principal">
    <div class="sessao-assunto">
        <div class="linha-assunto-titulo"><?php echo $integrante['usr_nm']; ?></div>
        <div class="conteudo-assunto">
            <div class="coluna-principal">
                <?php
                if ($imagem_url != "") {
                    echo '<div class="news-container" style="background-image: url(\'' . $imagem_url . '\'); height: 300px; background-size: cover; background-position: center; margin-bottom: 20px;"></div>';
                } else {
                    echo '<hr>';
                }
                ?>
                
                <div class="texto_materia">
                    <?php echo nl2br($portfolio); ?>
                </div>
            </div>
            
            <div class="coluna-secundaria">
                <div class="linhas-box-lateral">Nossa Equipe de Colaboradores</div>
                <ul class="lista-secundaria">
                    <?php
                    if ($result_equipe->num_rows > 0) {
                        while($row = $result_equipe->fetch_assoc()) {
                            $nome_integrante = htmlspecialchars($row['usr_nm']);
                            $active_class = ($nome_integrante == $integrante['usr_nm']) ? 'class="active"' : '';
                            echo "<li $active_class><a href='integrante.php?usr_nm=" . urlencode($row['usr_nm']) . "'>$nome_integrante</a></li>";
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
                <li><b>Cultura</b>: <br>
Fábio ACM</li>
                <li><b>Movimentos e Comunicação</b>: <br>
Cacá Munista</li>
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