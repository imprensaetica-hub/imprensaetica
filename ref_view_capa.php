<?php require_once('Connections/conn_pwr.php');
// Página de destaques - seis matérias mais novas com a mais acessada em destaque
echo '<div class="container-destaques">';

// Buscar as seis matérias mais recentes
$sql_seis_materias = "SELECT n.id_mat, n.mat_tit, n.mat_subt, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                     FROM pwr_not n
                     INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                     WHERE n.mat_status = 1
                     ORDER BY n.id_mat DESC 
                     LIMIT 6";

$result_seis = $conn_pwr->query($sql_seis_materias);
$materias = array();

    // Encontrar a matéria mais acessada
    $mais_acessada = null;
    foreach ($materias as $materia) {
        if ($mais_acessada === null || $materia['mat_acc'] > $mais_acessada['mat_acc']) {
            $mais_acessada = $materia;
        }
    }
    
    // Exibir a matéria mais acessada com destaque
    if ($mais_acessada) {
        echo '<div class="linha-titulo-view-not">
                <a href="view_not.php?id_mat=' . $mais_acessada['id_mat'] . '">' . 
                 $mais_acessada['mat_tit'] . '</a>
              </div>';
    }
    
    // Três colunas de conteúdo
    echo '<div class="colunas-destaques">';
    
    // Primeira coluna: foto da matéria mais acessada com subtítulo e editoria
    echo '<div class="coluna coluna-1">';
    if ($mais_acessada) {
        echo '<div class="news-container" style="background-image: url(\'' . 
              ($mais_acessada['mat_foto'] ?: 'img/sem-imagem.jpg') . '\')">';
        if (!empty($mais_acessada['mat_subt'])) {
            echo '<div class="linha-fina">' .  $mais_acessada['mat_subt'] . '</div>';
        }
        echo '</div>';
        echo '<div class="editoria-link">
                <a href="listagem.php?assunto=' . $mais_acessada['id_subj'] . '">' . 
                  $mais_acessada['subj'] . '</a>
              </div>';
    }
    echo '</div>';
    
    // Segunda coluna: segunda e terceira mais acessadas
    echo '<div class="coluna coluna-2">';
    
    // Remover a mais acessada do array para processar as demais
    $outras_materias = array_filter($materias, function($m) use ($mais_acessada) {
        return $m['id_mat'] !== $mais_acessada['id_mat'];
    });
    
    // Ordenar as demais matérias por acesso (decrescente)
    usort($outras_materias, function($a, $b) {
        return $b['mat_acc'] - $a['mat_acc'];
    });
    
    // Pegar as duas mais acessadas após a primeira
    $segunda_terceira = array_slice($outras_materias, 0, 2);
    
    foreach ($segunda_terceira as $materia) {
        echo '<div class="materia-destaque">';
        echo '<h3><a href="view_not.php?id_mat=' . $materia['id_mat'] . '">' . 
              $materia['mat_tit'] . '</a></h3>';
        if (!empty($materia['mat_subt'])) {
            echo '<p>' .  $materia['mat_subt'] . '</p>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    
    // Terceira coluna: demais matérias (três seguintes)
    echo '<div class="coluna coluna-3">';
    
    $demais_materias = array_slice($outras_materias, 2, 3);
    
    foreach ($demais_materias as $materia) {
        echo '<div class="materia-lista">';
        echo '<span class="editoria">' .  $materia['subj'] . '</span>';
        echo '<a href="view_not.php?id_mat=' . $materia['id_mat'] . '">' . 
              $materia['mat_tit'] . '</a>';
        echo '</div>';
    }
    
    echo '</div>';
    
    echo '</div>'; // Fim das colunas-destaques
    
} else {
    echo '<p>Nenhuma matéria disponível no momento.</p>';
}

echo '</div>'; // Fim do container-destaques
?>