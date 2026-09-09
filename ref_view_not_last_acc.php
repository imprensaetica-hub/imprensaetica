<?php require_once('Connections/conn_pwr.php');

if(isset($_GET['id_mat'])) {
    $id_materia = $_GET['id_mat'];
    $sql_secundarias = "SELECT id_mat, mat_tit 
						FROM (
    						SELECT id_mat, mat_tit, mat_acc 
    						FROM pwr_not 
    						WHERE id_mat != '$id_materia' 
        					AND mat_status = 1 
    						ORDER BY id_mat DESC 
    						LIMIT 20
						) AS recentes
						ORDER BY mat_acc DESC 
						LIMIT 5;";
    
    $resultado = $conn_pwr->query($sql_secundarias); // Mudei o nome da variável aqui

    echo '<div class="sessao-assunto"><ul class="lista-secundaria"><li class="linhas-box-lateral">Mais acessadas</font></li>';
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($mat = $resultado->fetch_assoc()) { // Agora usando $resultado para fetch
            $sec_tit = isset($mat['mat_tit']) ? $mat['mat_tit'] : 'Sem título';
            $hour_reg = substr($mat['id_mat'], 8, 2);
            $min_reg = substr($mat['id_mat'], 10, 2);

            echo '<li><a href="view_not.php?id_mat=' . $mat['id_mat'] . '">' . $sec_tit . '</a></li>';
        }
    }
    echo '<li class="mais-noticias"><a href="listagem.php?assunto=' . $id_subj . '">Mais notícias</b>...</a></li></ul></div>';
}
?>