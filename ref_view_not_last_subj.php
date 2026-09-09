<?php require_once('Connections/conn_pwr.php');

if(isset($_GET['id_mat'])) {
    $id_materia = $_GET['id_mat'];
	$sql = "SELECT id_mat, mat_tit, mat_subt, mat_foto, usr_nm, id_subj, mat_body FROM pwr_not WHERE id_mat = '$id_materia'";
    $result = $conn_pwr->query($sql);
    $materia = $result->fetch_assoc();
	$id_subj = $materia['id_subj'];
	$sql = "SELECT subj FROM pwr_subj WHERE id_subj = " . $materia['id_subj'];
	$result = $conn_pwr->query($sql);
	$subj = $result->fetch_assoc();
	$nome_assunto = $subj['subj'];
	$sql_secundarias = "SELECT id_mat, mat_tit 
                               FROM pwr_not 
                               WHERE id_subj = $id_subj 
                               AND id_mat != " . $materia['id_mat'] . "
                               AND mat_status = 1 
                               ORDER BY id_mat DESC 
                               LIMIT 5";
            
            $result_secundarias = $conn_pwr->query($sql_secundarias);
			
			echo '<div class="sessao-assunto"><ul class="lista-secundaria"><li class="linhas-box-lateral">Últimas de ' . $nome_assunto . '</font></li>';
	
                            if ($result_secundarias && $result_secundarias->num_rows > 0) {
                                while ($secundaria = $result_secundarias->fetch_assoc()) {
                                    $sec_tit = isset($secundaria['mat_tit']) ? $secundaria['mat_tit'] : 'Sem título';
                                    echo '<li><a href="view_not.php?id_mat=' . $secundaria['id_mat'] . '">' . $sec_tit . '</a></li>';
                                }
                            }
                            echo '<li class="mais-noticias"><a href="listagem.php?assunto=' . $id_subj . '">Mais notícias de <b>' . $nome_assunto . '</b>...</a></li></ul></div>';
							
}

?>