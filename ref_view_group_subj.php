<?php require_once('Connections/conn_pwr.php');

$sql = "SELECT id_subj, subj FROM pwr_subj ORDER BY subj";
$result = $conn_pwr->query($sql);
	
echo '<div class="sessao-assunto"><ul class="lista-secundaria"><li class="linhas-box-lateral">Editorias</li>';
	
if ($result && $result->num_rows > 0) {
    while ($editoria = $result->fetch_assoc()) {
        $id_subj = $editoria['id_subj'];
        // Consulta para contar notícias nesta editoria
        $sql_contagem = "SELECT COUNT(*) as total FROM pwr_not WHERE id_subj = " . $id_subj;
        $result_contagem = $conn_pwr->query($sql_contagem);
        $row_contagem = $result_contagem->fetch_assoc();
        $total = $row_contagem['total'];
        
        if ($total > 0) {
            echo '<li><a href="listagem.php?assunto=' . $editoria['id_subj'] . '">' . $editoria['subj'] . '</a></li>';
        }
    }
}
echo '</ul></div>';
?>