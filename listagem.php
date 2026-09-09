<?php require_once('Connections/conn_pwr.php'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Imprensa &Eacute;tica - Observat&oacute;rio Jornal&iacute;stico</title>
<link href="models.css" rel="stylesheet" type="text/css">

<?php

// Verifica se foi realizada uma busca por texto
if(isset($_GET['text_search'])) {
$text_search = $_GET['text_search'];

// Escapar a string para evitar SQL injection
$text_search = $conn_pwr->real_escape_string($text_search);
$sql = "SELECT id_mat, mat_tit, usr_nm, id_subj FROM pwr_not WHERE (mat_body LIKE '%" . $text_search . "%' OR usr_nm LIKE '%" . $text_search . "%') AND mat_status = 1 ORDER BY id_mat DESC";
$result = $conn_pwr->query($sql);
$nome_assunto = $text_search; // Para o título

} else if(isset($_GET['assunto'])) {
    $assunto = $_GET['assunto'];
    $sql = "SELECT id_mat, mat_tit, usr_nm, id_subj FROM pwr_not WHERE id_subj = '$assunto' AND mat_status = 1 ORDER BY id_mat DESC";
    $result = $conn_pwr->query($sql);
    
    // Buscar o nome do assunto
    $sql_subj = "SELECT subj FROM pwr_subj WHERE id_subj = '$assunto'";
    $result_subj = $conn_pwr->query($sql_subj);
    $subj_row = $result_subj->fetch_assoc();
    $nome_assunto = $subj_row['subj'];
	
} else {
	$sql = "SELECT id_mat, mat_tit, usr_nm, id_subj FROM pwr_not WHERE mat_status = 1 ORDER BY id_mat DESC";
    $result = $conn_pwr->query($sql);
	$nome_assunto = 'Hist&oacute;rico de not&iacute;cias';
}
?>

</head>
<body>

<?php 
include 'header.php';
echo '<div class="linha-titulo-view-not">Resultado da busca</div>
<div class="sessao-assunto">
<div class="conteudo-assunto">
<div class="coluna-principal">
<div class="linha-assunto-titulo">' . $nome_assunto . '</div>';
// Verificar se há resultados
if ($result->num_rows > 0) {
    while($materia = $result->fetch_assoc()) {
        $year_reg = substr($materia['id_mat'], 0, 4);
        $month_reg = substr($materia['id_mat'], 4, 2);
        $day_reg = substr($materia['id_mat'], 6, 2);
        
        echo '<ul class="lista-secundaria"><li><a href="view_not.php?id_mat=' . $materia['id_mat'] . '"><b>' . $materia['mat_tit'] . '</b>&nbsp;-&nbsp;(' . $materia['usr_nm'] . ', ' . $day_reg . '/' . $month_reg . '/' . $year_reg . ')</a></li></ul>';
    }
} else {
    echo 'Nenhuma matéria encontrada para este assunto.';
}
	echo '<br /></div><div class="coluna-secundaria">';
	include 'ref_view_not_last_gen.php';
	include 'ref_view_group_subj.php';
	echo '</div></div>';
include '1bann_hor300.php';
include '7bann_vert160.php';
include 'footer.php'; 
include '0above_body.php';; 
?>

</body>
</html>