<?php
require_once('Connections/conn_pwr.php');

// Habilitar exibição de erros para debugging (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para decodificar entidades HTML
function decode_entities($text) {
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

// Calcular data/hora das últimas 24 horas no formato YYYYMMDDhhmmss
$data_24h_atras = date('YmdHis', strtotime('-24 hours'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Imprensa &Eacute;tica - Observat&oacute;rio Jornal&iacute;stico</title>
<style>
/* ESTILOS PARA A PÁGINA DE DESTAQUES */
.container-destaques {
    width: 90%;
    max-width: 1200px;
    margin: 10px auto 40px;
    background: white;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5), inset 2px 2px 5px rgba(255, 255, 255, 0.8);
    padding: 10px;
}

.linha-titulo-view-not {
    display: block;
    padding-top: 10px;
    padding-bottom: 7px;
    background: #f5f5f5;
    color: #A00;
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 32px;
    font-weight: bold;
    text-decoration: none;
    transition: background 0.2s;
    border-bottom: 1px solid #e0e0e0;
    text-align: center;
    margin-bottom: 15px;
}

.linha-titulo-view-not a {
    color: #A00;
    text-decoration: none;
}

.linha-titulo-view-not a:hover {
    color: #D00;
}

.colunas-destaques {
    display: table;
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.coluna {
    display: table-cell;
    vertical-align: top;
    padding: 12px;
}

.coluna-1 {
    width: 40%;
    border-right: 2px dashed #ccc;
}

.coluna-2 {
    width: 30%;
    border-right: 2px dashed #ccc;
}

.coluna-3 {
    width: 30%;
}

.news-container-photo {
    height: 350px;
    background-size: cover;
    background-position: center;
    position: relative;
    margin-bottom: 15px;
    padding: 0px 10px 0px 0px;
}

.subt-linha-fina {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.6);
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    font-style: italic;
    color: white;
    padding: 20px 15px;
    font-size: 11px;
    line-height: 1.4;
    z-index: 20;
}

.editoria-link {
    text-align: center;
    margin-top: 10px;
}

.editoria-link a {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 14px;
    color: #A00;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
}

.editoria-link a:hover {
    color: #D00;
}

.materia-destaque {
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.materia-destaque:last-child {
    border-bottom: none;
}

.materia-destaque h3 {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 18px;
    margin-bottom: 2px;
}

.materia-destaque h3 a {
    color: #A00;
    text-decoration: none;
}

.materia-destaque h3 a:hover {
    color: #D00;
}

.materia-destaque p {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #444;
    line-height: 1.4;
    font-style: italic;
    margin-top: 5px;
}

.materia-lista {
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dotted #ddd;
}

.materia-lista:last-child {
    border-bottom: none;
}

.materia-lista .editoria {
    display: block;
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 9px;
    color: #777; /* Cinza escuro conforme solicitado */
    text-transform: uppercase;
    margin-bottom: 0; /* Removido o padding/margin */
    padding: 0; /* Removido o padding */
}

.materia-lista .editoria a {
    color: #777; /* Cinza escuro conforme solicitado */
    text-decoration: none;
    font-size: 10px; /* Mesmo tamanho da editoria */
}

.materia-lista .editoria a:hover {
    color: #D00;
}

.materia-lista a {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 15px;
    font-weight: bold;
    color: #A00;
    text-decoration: none;
    line-height: 1.3;
}

.materia-lista a:hover {
    color: #D00;
}

/* Novos estilos para editoria na coluna do meio */
.editoria-topo {
    text-align: right;
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 12px;
    color: #777;
    text-transform: uppercase;
    margin-bottom: 2px;
    padding: 0px 10px 0px 0px;
}

.editoria-topo a {
    color: #777;
    text-decoration: none;
}

.editoria-topo a:hover {
    color: #D00;
}

.editoria-destaque {
    font-style: normal;
    text-transform: uppercase;
    font-size: 14px;
}

.editoria-destaque a {
    color: white;
    text-decoration: none;
}

.editoria-destaque a:hover {
    color: #D00;
}

/* ESTILOS PARA OS CARDS DE NOTÍCIAS */
.container-cards {
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
    background: white;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5), inset 2px 2px 5px rgba(255, 255, 255, 0.8);
    padding: 20px;
}

.titulo-secao-cards {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 28px;
    color: #A00;
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.linha-cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin: 0 -10px;
}

.card {
    width: calc(33.333% - 20px);
    margin: 0 10px 25px;
    position: relative;
    overflow: hidden;
    border-radius: 4px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 280px; /* Altura ligeiramente reduzida para melhor visualização em 5 linhas */
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.card-imagem {
    height: 100%;
    background-size: cover;
    background-position: center;
    position: relative;
}

.card-imagem::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60%;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
    z-index: 1;
}

.editoria-card {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.9);
    padding: 5px 10px;
    border-radius: 3px;
    z-index: 3;
}

.editoria-card a {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 11px;
    color: #777;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
}

.editoria-card a:hover {
    color: #D00;
}

.titulo-card {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px 15px;
    z-index: 2;
}

.titulo-card a {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 18px;
    font-weight: bold;
    color: white;
    text-decoration: none;
    line-height: 1.3;
    display: block;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.titulo-card a:hover {
    color: #FFD700;
}

/* RESPONSIVIDADE */
@media (max-width: 992px) {
    .card {
        width: calc(50% - 20px);
    }
}

@media (max-width: 768px) {
    .colunas-destaques {
        display: block;
    }
    
    .coluna {
        display: block;
        width: 100% !important;
        padding: 10px;
    }
    
    .coluna-1, .coluna-2 {
        border-right: none;
        border-bottom: 2px dashed #ccc;
    }
    
    .linha-titulo-view-not {
        font-size: 24px;
        padding: 8px 10px;
    }
    
    .news-container-photo {
        height: 350px;
        padding-right: 10px;
    }
    
    .card {

        width: calc(100% - 20px);
    }
    
    .container-cards {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .card {
        height: 250px;
    }
    
    .titulo-card a {
        font-size: 16px;
    }
}

/* Estilo para linha separadora entre grupos de cards */
.linha-separadora {
    width: 100%;
    height: 1px;
    background: #eee;
    margin: 15px 0;
    clear: both;
}
</style>
<link href="models.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
include 'header.php';

// Página de destaques - nova lógica de seleção das 8 matérias
echo '<div class="container-destaques">';

// Primeiro, buscar as 8 matérias mais recentes para verificar a diferença de tempo
$sql_recentes = "SELECT n.id_mat, n.mat_tit, n.mat_subt, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                 FROM pwr_not n
                 INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                 WHERE n.mat_status = 1
                 ORDER BY n.id_mat DESC 
                 LIMIT 8";

$result_recentes = $conn_pwr->query($sql_recentes);
$materias_recentes = array();

if ($result_recentes && $result_recentes->num_rows > 0) {
    while ($row = $result_recentes->fetch_assoc()) {
        $materias_recentes[] = $row;
    }
    
    // Verificar se temos pelo menos 2 matérias para comparar
    if (count($materias_recentes) >= 2) {
        // Converter id_mat para timestamp para calcular diferença
        $primeira_mat = strtotime($materias_recentes[0]['id_mat']);
        $ultima_mat = strtotime($materias_recentes[count($materias_recentes)-1]['id_mat']);
        
        // Calcular diferença em horas
        $diferenca_horas = abs($primeira_mat - $ultima_mat) / 3600;
        
        if ($diferenca_horas > 6) {
            // Se diferença > 6 horas, usar as 8 mais recentes ordenadas por acesso
            $materias = $materias_recentes;
            usort($materias, function($a, $b) {
                return $b['mat_acc'] - $a['mat_acc'];
            });
        } else {
            // Se diferença <= 6 horas, buscar matérias das últimas 6 horas (a partir da mais recente)
            $data_mais_recente = $materias_recentes[0]['id_mat'];
            $data_6h_atras = date('YmdHis', strtotime(substr($data_mais_recente, 0, 8) . ' ' . 
                                                     substr($data_mais_recente, 8, 2) . ':' . 
                                                     substr($data_mais_recente, 10, 2) . ':' . 
                                                     substr($data_mais_recente, 12, 2) . ' -6 hours'));
            
            $sql_periodo = "SELECT n.id_mat, n.mat_tit, n.mat_subt, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                           FROM pwr_not n
                           INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                           WHERE n.mat_status = 1
                           AND n.id_mat >= '$data_6h_atras'
                           ORDER BY n.mat_acc DESC 
                           LIMIT 8";
            
            $result_periodo = $conn_pwr->query($sql_periodo);
            $materias = array();
            
            if ($result_periodo && $result_periodo->num_rows > 0) {
                while ($row = $result_periodo->fetch_assoc()) {
                    $materias[] = $row;
                }
            }
            
            // Se não encontrou 8 matérias no período de 6h, completar com as mais antigas
            $total_materias = count($materias);
            
            if ($total_materias < 8) {
                $limite_necessario = 8 - $total_materias;
                
                // Buscar matérias mais antigas (fora das 6 horas)
                $sql_antigas = "SELECT n.id_mat, n.mat_tit, n.mat_subt, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                               FROM pwr_not n
                               INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                               WHERE n.mat_status = 1
                               AND n.id_mat < '$data_6h_atras'";
                
                // Excluir matérias que já estão na lista
                if ($total_materias > 0) {
                    $ids_excluir = array();
                    foreach ($materias as $mat) {
                        $ids_excluir[] = $mat['id_mat'];
                    }
                    $sql_antigas .= " AND n.id_mat NOT IN ('" . implode("','", $ids_excluir) . "')";
                }
                
                $sql_antigas .= " ORDER BY n.mat_acc DESC 
                                 LIMIT $limite_necessario";
                
                $result_antigas = $conn_pwr->query($sql_antigas);
                
                if ($result_antigas && $result_antigas->num_rows > 0) {
                    while ($row = $result_antigas->fetch_assoc()) {
                        $materias[] = $row;
                    }
                }
                
                // Se ainda não tem 8, continuar buscando matérias mais antigas
                $dias_atras = 1;
                while (count($materias) < 8 && $dias_atras < 30) {
                    $data_limite = date('YmdHis', strtotime('-' . ($dias_atras + 1) . ' days'));
                    
                    $limite_necessario = 8 - count($materias);
                    
                    $sql_mais_antigas = "SELECT n.id_mat, n.mat_tit, n.mat_subt, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                                        FROM pwr_not n
                                        INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                                        WHERE n.mat_status = 1";
                    
                    // Excluir matérias que já estão na lista
                    if (count($materias) > 0) {
                        $ids_excluir = array();
                        foreach ($materias as $mat) {
                            $ids_excluir[] = $mat['id_mat'];
                        }
                        $sql_mais_antigas .= " AND n.id_mat NOT IN ('" . implode("','", $ids_excluir) . "')";
                    }
                    
                    $sql_mais_antigas .= " AND n.id_mat < '$data_limite'
                                         ORDER BY n.mat_acc DESC 
                                         LIMIT $limite_necessario";
                    
                    $result_mais_antigas = $conn_pwr->query($sql_mais_antigas);
                    
                    if ($result_mais_antigas && $result_mais_antigas->num_rows > 0) {
                        while ($row = $result_mais_antigas->fetch_assoc()) {
                            $materias[] = $row;
                        }
                    }
                    
                    $dias_atras++;
                }
            }
            
            // Garantir que temos no máximo 8 matérias
            $materias = array_slice($materias, 0, 8);
            
            // Ordenar por acesso (mais acessadas primeiro)
            usort($materias, function($a, $b) {
                return $b['mat_acc'] - $a['mat_acc'];
            });
        }
    } else {
        // Se tiver menos de 2 matérias, usar as disponíveis ordenadas por acesso
        $materias = $materias_recentes;
        usort($materias, function($a, $b) {
            return $b['mat_acc'] - $a['mat_acc'];
        });
    }
    
    // A primeira matéria (mais acessada) será o destaque
    $mais_acessada = $materias[0];
    
    // Exibir a matéria mais acessada com destaque
    echo '<div class="linha-titulo-view-not">
            <a href="view_not.php?id_mat=' . $mais_acessada['id_mat'] . '">' . 
             htmlspecialchars(decode_entities($mais_acessada['mat_tit'])) . '</a>
          </div>';
    
    // Três colunas de conteúdo
    echo '<div class="colunas-destaques">';
    
    // Primeira coluna: foto da matéria mais acessada com subtítulo e editoria
    echo '<div class="coluna coluna-1">';
    echo '<div class="news-container-photo" style="background-image: url(\'' . 
         htmlspecialchars($mais_acessada['mat_foto'] ?: 'img/sem-imagem.jpg') . '\')">';
    if (!empty($mais_acessada['mat_subt'])) {
        echo '<div class="subt-linha-fina">' . 
             htmlspecialchars(decode_entities($mais_acessada['mat_subt'])) . 
             '<p align="right"><span class="editoria-destaque"><a href="listagem.php?assunto=' . $mais_acessada['id_subj'] . '">' . 
             htmlspecialchars(decode_entities($mais_acessada['subj'])) . '</a></span></p></div>';
    } else {
        echo '<div class="subt-linha-fina"><span class="editoria-destaque"><a href="listagem.php?assunto=' . $mais_acessada['id_subj'] . '">' . 
             htmlspecialchars(decode_entities($mais_acessada['subj'])) . '</a></span></div>';
    }
    echo '</div>';
    echo '</div>';
    
    // Segunda coluna: segunda e terceira mais acessadas entre as 8
    echo '<div class="coluna coluna-2">';
    
    // Pegar a segunda e terceira matérias (índices 1 e 2)
    for ($i = 1; $i <= 2 && $i < count($materias); $i++) {
        $materia = $materias[$i];
        echo '<div class="materia-destaque">';
        echo '<div class="editoria-topo"><a href="listagem.php?assunto=' . $materia['id_subj'] . '">' . 
             htmlspecialchars(decode_entities($materia['subj'])) . '</a></div>';
        echo '<h3><a href="view_not.php?id_mat=' . $materia['id_mat'] . '">' . 
             htmlspecialchars(decode_entities($materia['mat_tit'])) . '</a></h3>';
        if (!empty($materia['mat_subt'])) {
            echo '<p>' . htmlspecialchars(decode_entities($materia['mat_subt'])) . '</p>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    
    // Terceira coluna: demais matérias (restantes 5 entre as 8)
    echo '<div class="coluna coluna-3">';
    
    // Pegar as matérias restantes (índices 3 a 7)
    for ($i = 3; $i < count($materias) && $i < 8; $i++) {
        $materia = $materias[$i];
        echo '<div class="materia-lista">';
        echo '<span class="editoria"><a href="listagem.php?assunto=' . $materia['id_subj'] . '">' . 
             htmlspecialchars(decode_entities($materia['subj'])) . '</a></span>';
        echo '<a href="view_not.php?id_mat=' . $materia['id_mat'] . '">' . 
             htmlspecialchars(decode_entities($materia['mat_tit'])) . '</a>';
        echo '</div>';
    }
    
    echo '</div>';
    
    echo '</div>'; // Fim das colunas-destaques
    
} else {
    echo '<p>Nenhuma matéria disponível no momento.</p>';
}
include '2bann_hor300.php';
include 'autores_destaque.php';
echo '</div>'; // Fim do container-destaques

include '1native_banner.php';
include '7bann_vert160.php';

// SEÇÃO DE CARDS DE NOTÍCIAS - 15 MATÉRIAS (5 LINHAS)
echo '<div class="container-cards">';
echo '<h2 class="titulo-secao-cards">Mais Notícias</h2>';

// Primeiro, tentar buscar 15 matérias das últimas 24 horas (excluindo a primeira da capa)
$sql_cards = "SELECT n.id_mat, n.mat_tit, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
              FROM pwr_not n
              INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
              WHERE n.mat_status = 1 
              AND n.id_mat != '" . $mais_acessada['id_mat'] . "'
              AND n.id_mat >= '$data_24h_atras'
              ORDER BY n.mat_acc DESC 
              LIMIT 15";

$result_cards = $conn_pwr->query($sql_cards);
$cards = array();

if ($result_cards && $result_cards->num_rows > 0) {
    while ($row = $result_cards->fetch_assoc()) {
        $cards[] = $row;
    }
}

// Se não encontrou 15 matérias nas últimas 24 horas, buscar do dia anterior e assim por diante
if (count($cards) < 15) {
    $limite_necessario = 15 - count($cards);
    
    // Calcular data de ontem (24 horas antes da data de 24 horas atrás)
    $data_48h_atras = date('YmdHis', strtotime('-48 hours'));
    
    // Buscar matérias mais antigas (exceto a primeira da capa)
    $sql_mais_antigas = "SELECT n.id_mat, n.mat_tit, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                         FROM pwr_not n

                         INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                         WHERE n.mat_status = 1 
                         AND n.id_mat != '" . $mais_acessada['id_mat'] . "'
                         AND n.id_mat < '$data_24h_atras'
                         AND n.id_mat >= '$data_48h_atras'
                         ORDER BY n.mat_acc DESC 
                         LIMIT $limite_necessario";
    
    $result_mais_antigas = $conn_pwr->query($sql_mais_antigas);
    
    if ($result_mais_antigas && $result_mais_antigas->num_rows > 0) {
        while ($row = $result_mais_antigas->fetch_assoc()) {
            $cards[] = $row;
        }
    }
    
    // Se ainda não tem 15, continuar buscando em dias anteriores até completar
    $dias_atras = 2;
    while (count($cards) < 15 && $dias_atras < 30) { // Limite de 30 dias para busca
        $data_inicio = date('YmdHis', strtotime("-" . ($dias_atras + 1) . " days"));
        $data_fim = date('YmdHis', strtotime("-$dias_atras days"));
        
        $limite_necessario = 15 - count($cards);
        
        $sql_dias_anteriores = "SELECT n.id_mat, n.mat_tit, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                                FROM pwr_not n
                                INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                                WHERE n.mat_status = 1 
                                AND n.id_mat != '" . $mais_acessada['id_mat'] . "'
                                AND n.id_mat >= '$data_inicio'
                                AND n.id_mat < '$data_fim'
                                ORDER BY n.mat_acc DESC 
                                LIMIT $limite_necessario";
        
        $result_dias_anteriores = $conn_pwr->query($sql_dias_anteriores);
        
        if ($result_dias_anteriores && $result_dias_anteriores->num_rows > 0) {
            while ($row = $result_dias_anteriores->fetch_assoc()) {
                $cards[] = $row;
            }
        }
        
        $dias_atras++;
    }
}

if (count($cards) > 0) {
    echo '<div class="linha-cards">';
    
    foreach ($cards as $index => $card) {
        // Adicionar linha separadora após cada 3 cards (após cada linha)
        if ($index > 0 && $index % 3 === 0) {
            echo '</div>'; // Fechar linha anterior
            echo '<div class="linha-separadora"></div>'; // Linha separadora visual
            echo '<div class="linha-cards">'; // Nova linha
        }
        
        echo '<div class="card">';
        echo '<div class="card-imagem" style="background-image: url(\'' . 
             htmlspecialchars($card['mat_foto'] ?: 'img/sem-imagem.jpg') . '\')">';
        
        // Editoria no topo à direita
        echo '<div class="editoria-card">';
        echo '<a href="listagem.php?assunto=' . $card['id_subj'] . '">' . 
             htmlspecialchars(decode_entities($card['subj'])) . '</a>';
        echo '</div>';
        
        // Título na margem inferior
        echo '<div class="titulo-card">';
        echo '<a href="view_not.php?id_mat=' . $card['id_mat'] . '">' . 
             htmlspecialchars(decode_entities($card['mat_tit'])) . '</a>';
        echo '</div>';
        
        echo '</div>'; // Fim card-imagem
        echo '</div>'; // Fim card
        
        // Se for o último item da linha (a cada 3 cards), mas não o último geral
        if (($index + 1) % 3 === 0 && ($index + 1) < count($cards)) {
            include '1native_banner.php'; 
            // A linha será fechada e uma nova será aberta no próximo loop
        }
    }
    
    echo '</div>'; // Fim última linha-cards
} else {
    echo '<p>Nenhuma matéria adicional disponível no momento.</p>';
}
echo '</div>'; // Fim container-cards

include '7bann_vert160.php';

include 'footer.php'; 
include '0above_body.php';
?>
</body>
</html>