<?php
// Arquivo: autores_destaque.php
// Conectar ao banco de dados
require_once('Connections/conn_pwr.php');

// Função para decodificar entidades HTML
if (!function_exists('decode_entities')) {
    function decode_entities($text) {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }
}

// Verificar conexão com o banco
if (!$conn_pwr) {
    echo "<p style='color: red;'>Erro de conexão com o banco de dados.</p>";
    exit;
}

// Primeiro, buscar os IDs das matérias mais recentes para cada autor
$sql_autores = "SELECT DISTINCT usr_nm as autor_nome 
                FROM pwr_not 
                WHERE mat_status = 1 
                AND (id_subj = 23 OR id_subj = 24 OR id_subj = 7)
                ORDER BY usr_nm";
                
$result_autores = $conn_pwr->query($sql_autores);
$autores = array();

if (!$result_autores) {
    echo "<p style='color: red;'>Erro na query: " . $conn_pwr->error . "</p>";
    exit;
} 

if ($result_autores->num_rows == 0) {
    echo "<p style='color: orange;'>Nenhum autor encontrado.</p>";
} else {
    // Para cada autor, buscar suas 3 matérias mais recentes
    while ($row = $result_autores->fetch_assoc()) {
        $autor_nome = $row['autor_nome'];
        
        $sql_materias = "SELECT id_mat, mat_tit 
                         FROM pwr_not 
                         WHERE usr_nm = '" . $conn_pwr->real_escape_string($autor_nome) . "' 
                         AND mat_status = 1 
                         AND (id_subj = 23 OR id_subj = 24 OR id_subj = 7)
                         ORDER BY id_mat DESC 
                         LIMIT 3";
                         
        $result_materias = $conn_pwr->query($sql_materias);
        $materias = array();
        
        if ($result_materias && $result_materias->num_rows > 0) {
            while ($materia = $result_materias->fetch_assoc()) {
                $materias[] = array(
                    'id_mat' => $materia['id_mat'],
                    'mat_tit' => $materia['mat_tit']
                );
            }
        }
        
        $autores[] = array(
            'autor_nome' => $autor_nome,
            'materias' => $materias
        );
    }
}

// Ordenar autores pelo ID da matéria mais recente (se houver matérias)
// Substituição da função array_column() para compatibilidade
usort($autores, function($a, $b) {
    $a_max_id = 0;
    if (!empty($a['materias'])) {
        foreach ($a['materias'] as $materia) {
            if ($materia['id_mat'] > $a_max_id) {
                $a_max_id = $materia['id_mat'];
            }
        }
    }
    
    $b_max_id = 0;
    if (!empty($b['materias'])) {
        foreach ($b['materias'] as $materia) {
            if ($materia['id_mat'] > $b_max_id) {
                $b_max_id = $materia['id_mat'];
            }
        }
    }
    
    return $b_max_id - $a_max_id;
});

// Dividir autores em três colunas (preenchendo horizontalmente)
$num_autores = count($autores);
$autores_colunas = array(array(), array(), array());

for ($i = 0; $i < $num_autores; $i++) {
    $coluna = $i % 3;
    $autores_colunas[$coluna][] = $autores[$i];
}
?>

<style>
/* ESTILOS PARA A SEÇÃO DE AUTORES */
.secao-autores {
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
    background: white;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5), inset 2px 2px 5px rgba(255, 255, 255, 0.8);
    padding: 10px;
}

.colunas-autores {
    display: table;
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.coluna-autor {
    display: table-cell;
    vertical-align: top;
    padding: 12px;
}

.coluna-autor-1 {
    width: 33.33%;
    border-right: 2px dashed #ccc;
}

.coluna-autor-2 {
    width: 33.33%;
    border-right: 2px dashed #ccc;
}

.coluna-autor-3 {
    width: 33.33%;
}

.autor-container {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.autor-container:last-child {
    border-bottom: none;
}

.autor-cabecalho {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.autor-info {
    flex: 1;
}

.autor-nome {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 18px;
    font-weight: bold;
    color: #A00;
    margin: 0 0 5px 0;
    padding: 10px;
    background-color: #f9f9f9;
    border-left: 4px solid #A00;
}

.autor-nome a {
    color: #A00;
    text-decoration: none;
}

.autor-nome a:hover {
    color: #C00;
    text-decoration: none;
}

.lista-materias-autor {
    list-style: none;
    padding: 0;
    margin: 0;
}

.lista-materias-autor li {
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px dotted #ddd;
}

.lista-materias-autor li:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.lista-materias-autor a {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 14px;
    color: #333;
    text-decoration: none;
    line-height: 1.3;
    display: block;
    padding: 5px;
    transition: all 0.2s;
}

.lista-materias-autor a:hover {
    color: #D00;
    background-color: #f5f5f5;
    padding-left: 10px;
}

/* RESPONSIVIDADE */
@media (max-width: 768px) {
    .colunas-autores {
        display: block;
    }
    
    .coluna-autor {
        display: block;
        width: 100% !important;
        padding: 10px;
    }
    
    .coluna-autor-1, .coluna-autor-2 {
        border-right: none;
        border-bottom: 2px dashed #ccc;
    }
    
}
</style>

<div class="secao-autores">
    <div class="linha-assunto-titulo">Análises e Artigos</div>
    
    <?php if (!empty($autores) && count($autores) > 0): ?>
    <div class="colunas-autores">
        <?php foreach ($autores_colunas as $col_index => $coluna_autores): ?>
        <div class="coluna-autor coluna-autor-<?php echo $col_index + 1; ?>">
            <?php foreach ($coluna_autores as $autor): ?>
            <div class="autor-container">
                <div class="autor-cabecalho">
                    <div class="autor-info">
                        <h3 class="autor-nome">
                            <?php
                            $autor_url = urlencode($autor['autor_nome']);
                            $autor_url = str_replace('%20', '+', $autor_url);
                            echo '<a href="listagem.php?search_type=text&text_search=' . $autor_url . '">' . $autor['autor_nome'] . '</a>';
                            ?>
                        </h3>
                    </div>
                </div>
                
                <?php if (!empty($autor['materias'])): ?>
                <ul class="lista-materias-autor">
                    <?php foreach ($autor['materias'] as $materia): ?>
                    <li>
                        <a href="view_not.php?id_mat=<?php echo $materia['id_mat']; ?>">
                            <?php echo $materia['mat_tit']; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p style="padding: 10px; color: #777; font-style: italic;">Nenhuma matéria disponível.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="text-align: center; padding: 20px;">Nenhum autor com análises ou artigos disponível no momento.</p>
    <?php endif; ?>
</div>