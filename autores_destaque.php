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

// Buscar os 3 autores distintos mais recentes com matérias de 'análise' ou 'artigo'
$sql_autores = "SELECT DISTINCT usr_nm as autor_nome 
                FROM pwr_not 
                WHERE mat_status = 1 
                AND (id_subj = 23 OR id_subj = 24 OR id_subj = 7) 
                ORDER BY id_mat DESC 
                LIMIT 3";
                
$result_autores = $conn_pwr->query($sql_autores);
$autores = array();

if (!$result_autores) {
    echo "<p style='color: red;'>Erro na query: " . $conn_pwr->error . "</p>";
    exit;
} 

if ($result_autores->num_rows == 0) {
    echo "<p style='color: orange;'>Nenhum autor encontrado.</p>";
} else {
    // Coletar os nomes dos autores
    while ($row = $result_autores->fetch_assoc()) {
        $autores[$row['autor_nome']] = array(
            'autor_nome' => $row['autor_nome'],
            'materia' => array() // Agora armazena apenas uma matéria
        );
    }
}

// Para cada autor, buscar sua matéria mais recente
if (!empty($autores)) {
    foreach ($autores as $autor_nome => $dados_autor) {
        $autor_escaped = $conn_pwr->real_escape_string($autor_nome);
        
        $sql_materia = "SELECT id_mat, mat_tit 
                         FROM pwr_not
                         WHERE usr_nm = '$autor_escaped' 
                         AND mat_status = 1
                         AND (id_subj = 23 OR id_subj = 24 OR id_subj = 7) 
                         ORDER BY id_mat DESC
                         LIMIT 1";
                         
        $result_materia = $conn_pwr->query($sql_materia);
        
        if ($result_materia && $result_materia->num_rows > 0) {
            $autores[$autor_nome]['materia'] = $result_materia->fetch_assoc();
        }
    }
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

.titulo-secao-autores {
    display: block;
    padding: 15px;
    background: #f5f5f5;
    color: #A00;
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 28px;
    font-weight: bold;
    text-decoration: none;
    transition: background 0.2s;
    border-bottom: 1px solid #e0e0e0;
    text-align: center;
    margin-bottom: 15px;
}

.colunas-autores {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
}

.coluna-autor {
    flex: 1;
    min-width: 250px;
    margin: 10px;
    padding: 12px;
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
}

.materia-autor {
    padding: 5px;
}

.materia-autor a {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 14px;
    color: #333;
    text-decoration: none;
    line-height: 1.3;
    display: block;
    padding: 5px;
    transition: all 0.2s;
}

.materia-autor a:hover {
    color: #D00;
    background-color: #f5f5f5;
    padding-left: 10px;
}

/* RESPONSIVIDADE */
@media (max-width: 768px) {
    .colunas-autores {
        flex-direction: column;
    }
    
    .coluna-autor {
        width: 100%;
        margin: 5px 0;
        padding: 10px;
    }
    
    .titulo-secao-autores {
        font-size: 22px;
        padding: 12px 10px;
    }
}
</style>

<div class="secao-autores">
     <div class="linha-assunto-titulo">Análises e Artigos</div>
    
    <?php if (!empty($autores) && count($autores) > 0): ?>
    <div class="colunas-autores">
        <?php foreach ($autores as $autor): ?>
        <div class="coluna-autor">
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
                
                <?php if (!empty($autor['materia'])): ?>
                <div class="materia-autor">
                    <a href="view_not.php?id_mat=<?php echo $autor['materia']['id_mat']; ?>">
                        <?php echo $autor['materia']['mat_tit']; ?>
                    </a>
                </div>
                <?php else: ?>
                <p style="padding: 10px; color: #777; font-style: italic;">Nenhuma matéria disponível.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="text-align: center; padding: 20px;">Nenhum autor com análises ou artigos disponível no momento.</p>
    <?php endif; ?>
</div>