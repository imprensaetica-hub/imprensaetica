<?php 
require_once('Connections/conn_pwr.php');

// Habilitar exibição de erros para debugging (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
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

echo '<div class="container-principal">';
    
// Lista de assuntos prioritários
$assuntos_prioritarios = array('America Latina','Politica', 'Economia', 'Internacional', 'Cultura', 'Cidades', 'Internet', 'America do Sul', 'America do Norte', 'Europa', 'Oriente Medio', 'Africa', 'Leste Europeu', 'Oeste Asiatico', 'China', 'Leste Asiatico', 'Oceania', 'Tecnologia', 'Movimentos');

// Codificar para HTML entities para compatibilidade
$assuntos_prioritarios_html = array_map('htmlentities', $assuntos_prioritarios);

$assuntos_selecionados = array();

// Buscar assuntos prioritários com pelo menos 5 matérias, ordenados pela mais recente
if (!empty($assuntos_prioritarios_html)) {
    // Criar placeholders para a consulta
    $placeholders = implode(',', array_fill(0, count($assuntos_prioritarios_html), '?'));
    
    // Preparar a consulta
    $sql = "SELECT s.id_subj, s.subj, MAX(n.id_mat) as mais_recente
            FROM pwr_subj s
            INNER JOIN pwr_not n ON s.id_subj = n.id_subj
            WHERE s.subj IN ($placeholders) 
            AND n.mat_status = 1
            GROUP BY s.id_subj, s.subj
            HAVING COUNT(n.id_mat) >= 5
            ORDER BY mais_recente DESC
            LIMIT 4";
    
    if ($stmt = $conn_pwr->prepare($sql)) {
        // Montar tipos de parâmetros (todos strings)
        $types = str_repeat('s', count($assuntos_prioritarios_html));
        
        // Vincular parâmetros
        $params = array($types);
        foreach ($assuntos_prioritarios_html as $key => $value) {
            $params[] = &$assuntos_prioritarios_html[$key];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $params);
        
        // Executar
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (count($assuntos_selecionados) < 4) {
                    $assuntos_selecionados[] = array(
                        'id_subj' => $row['id_subj'],
                        'nome' => $row['subj']
                    );
                }
            }
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conn_pwr->error;
    }
}

// Adicionar Editorial como 5º assunto se disponível
$sql_editorial = "SELECT s.id_subj, s.subj 
                FROM pwr_subj s
                INNER JOIN pwr_not n ON s.id_subj = n.id_subj
                WHERE s.subj = 'Editorial'
                AND n.mat_status = 1
                GROUP BY s.id_subj
                HAVING COUNT(n.id_mat) >= 5
                LIMIT 1";

$result_editorial = $conn_pwr->query($sql_editorial);
if ($result_editorial && $result_editorial->num_rows > 0) {
    $row_edit = $result_editorial->fetch_assoc();
    $assuntos_selecionados[] = array(
        'id_subj' => $row_edit['id_subj'],
        'nome' => $row_edit['subj']
    );
}

// Renderizar as sessões de assunto
if (count($assuntos_selecionados) > 0) {
    foreach ($assuntos_selecionados as $assunto) {
        $id_subj = $assunto['id_subj'];
        $nome_assunto = $assunto['nome'];
        
        $sql_principal = "SELECT id_mat, mat_foto, mat_tit, mat_subt 
                         FROM pwr_not 
                         WHERE id_subj = $id_subj 
                         AND mat_status = 1 
                         ORDER BY id_mat DESC 
                         LIMIT 1";
        
        $result_principal = $conn_pwr->query($sql_principal);
        
        if ($result_principal && $result_principal->num_rows > 0) {
            $principal = $result_principal->fetch_assoc();
            
            // Verificar se campos existem
            $mat_foto = isset($principal['mat_foto']) ? $principal['mat_foto'] : 'img/sem-imagem.jpg';
            $mat_tit = isset($principal['mat_tit']) ? $principal['mat_tit'] : 'Sem título';
            $mat_subt = isset($principal['mat_subt']) ? $principal['mat_subt'] : '';
            
            $sql_secundarias = "SELECT id_mat, mat_tit 
                               FROM pwr_not 
                               WHERE id_subj = $id_subj 
                               AND id_mat != " . $principal['id_mat'] . "
                               AND mat_status = 1 
                               ORDER BY id_mat DESC 
                               LIMIT 4";
            
            $result_secundarias = $conn_pwr->query($sql_secundarias);
            
            echo '<div class="sessao-assunto">
                    <div class="linha-assunto-titulo">' . htmlspecialchars($nome_assunto) . '</div>
                    <div class="linha-titulo-principal">
                        <a href="view_not.php?id_mat=' . $principal['id_mat'] . '">' . htmlspecialchars($mat_tit) . '</a>
                    </div>
                    <div class="conteudo-assunto">
                        <div class="coluna-principal">
                            <a href="view_not.php?id_mat=' . $principal['id_mat'] . '">
                                <div class="news-container" style="background-image: url(\'' . htmlspecialchars($mat_foto) . '\')">';
            
            if (!empty($mat_subt)) {
                echo '<div class="linha-fina">' . htmlspecialchars($mat_subt) . '</div>';
            }
            
            echo '              </div>
                            </a>
                        </div>
                        <div class="coluna-secundaria">
                            <ul class="lista-secundaria">';
            
            if ($result_secundarias && $result_secundarias->num_rows > 0) {
                while ($secundaria = $result_secundarias->fetch_assoc()) {
                    $sec_tit = isset($secundaria['mat_tit']) ? $secundaria['mat_tit'] : 'Sem título';
                    echo '<li><a href="view_not.php?id_mat=' . $secundaria['id_mat'] . '">' . htmlspecialchars($sec_tit) . '</a></li>';
                }
            }
            
            echo '              <li class="mais-noticias">
                                    <a href="listagem.php?assunto=' . $id_subj . '">
                                        Mais notícias de <b>' . htmlspecialchars($nome_assunto) . '</b>...
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>';
				include '1bann_hor300.php';
				include '1native_banner.php';
        }
    }
} else {
    echo '<div class="sem-resultados"><p>Nenhuma notícia disponível no momento.</p></div>';
}

echo '</div>';
include '1native_banner.php';
include '7bann_vert160.php';
include 'footer.php'; 
include '0above_body.php';
?>
</body>
</html>