<?php
require_once('Connections/conn_pwr.php');

// Habilitar exibição de erros para debugging (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para decodificar entidades HTML
function decode_entities($text) {
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

// Inicializar variáveis
$materia = null;
$subj = null;
$imagem_url = '';
$cards_data = array();

if(isset($_GET['id_mat'])) {
    $id_materia = $_GET['id_mat'];
    
    // Buscar matéria principal
    $sql = "SELECT n.id_mat, n.mat_tit, n.mat_subt, n.mat_foto, n.usr_nm, n.id_subj, n.mat_body, n.mat_acc, s.subj 
            FROM pwr_not n
            LEFT JOIN pwr_subj s ON n.id_subj = s.id_subj
            WHERE n.id_mat = '$id_materia'";
    $result = $conn_pwr->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $materia = $result->fetch_assoc();
        
        // Extrair data do ID
        $year_reg = substr($materia['id_mat'], 0, 4);
        $month_reg = substr($materia['id_mat'], 4, 2);
        $day_reg = substr($materia['id_mat'], 6, 2);
        
        // Atualizar contador de acessos
        $mat_acc = $materia['mat_acc'] ?: 0;
        $mat_acc++;
        $sql_code = "UPDATE pwr_not SET mat_acc = '$mat_acc' WHERE id_mat = '$id_materia'";
        $conn_pwr->query($sql_code);
        
        // URL absoluta da imagem
        $imagem_url = $materia['mat_foto'];
        
        // Buscar matérias para os cards (excluindo a atual)
        $sql_cards = "SELECT n.id_mat, n.mat_tit, n.mat_foto, n.mat_acc, s.subj, s.id_subj 
                      FROM pwr_not n
                      INNER JOIN pwr_subj s ON n.id_subj = s.id_subj
                      WHERE n.id_mat != '$id_materia'
                      ORDER BY n.id_mat DESC, n.mat_acc DESC 
                      LIMIT 15";
        
        $result_cards = $conn_pwr->query($sql_cards);
        
        if ($result_cards && $result_cards->num_rows > 0) {
            while($row = $result_cards->fetch_assoc()) {
                $cards_data[] = $row;
            }
        }
    }
}

// Preparar dados para compartilhamento
if($materia) {
    $titulo_compartilhar = htmlspecialchars(decode_entities($materia['mat_tit']));
    $subtitulo_compartilhar = htmlspecialchars(decode_entities($materia['mat_subt']));
    $url_completa = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // Texto formatado para WhatsApp
    $texto_whatsapp = urlencode("*" . $titulo_compartilhar . "*\n\n_" . $subtitulo_compartilhar . "_\n\n - Imprensa Ética\n\n" . $url_completa);
    
    // URLs de compartilhamento
    $whatsapp_url = "https://wa.me/?text=" . $texto_whatsapp;
    $facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url_completa);
    $twitter_url = "https://twitter.com/intent/tweet?url=" . urlencode($url_completa) . "&text=" . urlencode($titulo_compartilhar);
    $telegram_url = "https://t.me/share/url?url=" . urlencode($url_completa) . "&text=" . urlencode($titulo_compartilhar . " - Imprensa Ética");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Imprensa &Eacute;tica - Observat&oacute;rio Jornal&iacute;stico</title>
<style>
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
    height: 280px;
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

/* ESTILOS PARA BOTÕES DE COMPARTILHAMENTO */
.compartilhamento-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
    padding: 15px;
    background: #f8f8f8;
    border-radius: 8px;
    border-left: 4px solid #A00;
}

.compartilhamento-titulo {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    font-size: 16px;
    color: #333;
    margin-right: 10px;
    font-weight: bold;
}

.botao-compartilhar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.botao-compartilhar:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
}

.botao-compartilhar:active {
    transform: translateY(-1px);
}

.botao-compartilhar svg {
    width: 24px;
    height: 24px;
}

.botao-facebook {
    background: linear-gradient(135deg, #4267B2, #3b5998);
}

.botao-facebook:hover {
    background: linear-gradient(135deg, #3b5998, #4267B2);
}

.botao-twitter {
    background: linear-gradient(135deg, #000000, #1DA1F2);
}

.botao-twitter:hover {
    background: linear-gradient(135deg, #1DA1F2, #000000);
}

.botao-whatsapp {
    background: linear-gradient(135deg, #25D366, #128C7E);
}

.botao-whatsapp:hover {
    background: linear-gradient(135deg, #128C7E, #25D366);
}

.botao-telegram {
    background: linear-gradient(135deg, #0088cc, #2AABEE);
}

.botao-telegram:hover {
    background: linear-gradient(135deg, #2AABEE, #0088cc);
}

/* RESPONSIVIDADE PARA CARDS */
@media (max-width: 992px) {
    .card {
        width: calc(50% - 20px);
    }
}

@media (max-width: 768px) {
    .card {
        width: calc(100% - 20px);
    }
    
    .compartilhamento-container {
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    
    .compartilhamento-titulo {
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }
}

@media (max-width: 480px) {
    .card {
        height: 250px;
    }
    
    .titulo-card a {
        font-size: 16px;
    }
    
    .botao-compartilhar {
        width: 45px;
        height: 45px;
    }
    
    .botao-compartilhar svg {
        width: 20px;
        height: 20px;
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

<?php if($materia): ?>
<!-- Meta Tags para WhatsApp -->
<meta property="og:title" content="<?= htmlspecialchars($materia['mat_tit']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($materia['mat_subt']) ?>">
<meta property="og:image" content="<?= $imagem_url ?>">
<meta property="og:url" content="https://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="Imprensa Ética">
<?php endif; ?>

</head>
<body>
<?php 
include 'header.php';

if($materia): 
    // Exibir matéria principal
    echo '<div class="linha-titulo-view-not">' . htmlspecialchars(decode_entities($materia['mat_tit'])) . '</div>';
    echo '<div class="sessao-assunto">';
    echo '<div class="conteudo-assunto">';
    echo '<div class="coluna-principal">';
    echo '<div class="linha-fina-view-not">' . htmlspecialchars(decode_entities($materia['mat_subt'])) . '</div>';
    echo '<div class="linha-assunto-titulo">' . htmlspecialchars(decode_entities($materia['subj'])) . '</div>';
    
    if ($imagem_url != "") {
        echo '<div class="news-container" style="background-image: url(\'' . htmlspecialchars($imagem_url) . '\')">';
    } else {
        echo '<hr>';
    }
    
    echo '</div>';
    
    if ($imagem_url != "") {
        echo '<div style="padding: 20px">';
        echo '<font face="Trebuchet MS, Arial, Helvetica, sans-serif" size="2">';
        echo '<i>Foto: ' . htmlspecialchars($imagem_url) . '</i>';
        echo '</font>';
        echo '</div>';
    }
    
    // BOTÕES DE COMPARTILHAMENTO
    echo '<div class="compartilhamento-container">';
    echo '<div class="compartilhamento-titulo">Compartilhar:</div>';
    
    // Botão Facebook
    echo '<a href="' . $facebook_url . '" target="_blank" class="botao-compartilhar botao-facebook" title="Compartilhar no Facebook">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="white" d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>';
    echo '</a>';
    
    // Botão Twitter/X
    echo '<a href="' . $twitter_url . '" target="_blank" class="botao-compartilhar botao-twitter" title="Compartilhar no X (Twitter)">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="white" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>';
    echo '</a>';
    
    // Botão WhatsApp
    echo '<a href="' . $whatsapp_url . '" target="_blank" class="botao-compartilhar botao-whatsapp" title="Compartilhar no WhatsApp">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="white" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>';
    echo '</a>';
    
    // Botão Telegram
    echo '<a href="' . $telegram_url . '" target="_blank" class="botao-compartilhar botao-telegram" title="Compartilhar no Telegram">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><path fill="white" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"/></svg>';
    echo '</a>';
    
    echo '</div>'; // Fim compartilhamento-container
    
    include '2bann_hor300.php';
    echo '<div class="txt_autor">&#9632;&nbsp;&nbsp;&nbsp;' . htmlspecialchars($materia['usr_nm']) . ', ' . $day_reg . '/' . $month_reg . '/' . $year_reg . '</div>';
    echo '<div class="texto_materia">' . decode_entities($materia['mat_body']) . '&#9632;</div>';
    include '2bann_hor300.php';
    echo '</div>';
    
    echo '<div class="coluna-secundaria">';
    include 'ref_view_not_last_subj.php';
    include '4bann_vert160.php';
    include 'ref_view_not_last_gen.php';
    include '4bann_vert160.php';
    include 'ref_view_not_last_acc.php';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    include '1native_banner.php';
    
    // SEÇÃO DE CARDS DE NOTÍCIAS
    if (!empty($cards_data)) {
        echo '<div class="container-cards">';
        echo '<h2 class="titulo-secao-cards">Mais Notícias</h2>';
        
        // Organizar em 5 linhas de 3 cards
        $total_cards = count($cards_data);
        $cards_por_linha = 3;
        $linhas = ceil($total_cards / $cards_por_linha);
        
        for ($linha = 0; $linha < $linhas && $linha < 5; $linha++) {
            echo '<div class="linha-cards">';
            
            for ($coluna = 0; $coluna < $cards_por_linha; $coluna++) {
                $indice = ($linha * $cards_por_linha) + $coluna;
                
                if (isset($cards_data[$indice])) {
                    $card = $cards_data[$indice];
                    $card_image = !empty($card['mat_foto']) ? $card['mat_foto'] : '';
                    $card_link = 'view_not.php?id_mat=' . $card['id_mat'];
                    $card_title = htmlspecialchars(decode_entities($card['mat_tit']));
                    $card_editoria = htmlspecialchars(decode_entities($card['subj']));
                    $editoria_link = 'listagem.php?assunto=' . $card['id_subj'];
                    
                    echo '<div class="card">';
                    echo '<div class="card-imagem" style="background-image: url(\'' . htmlspecialchars($card_image) . '\')">';
                    
                    // Editoria no topo à direita
                    echo '<div class="editoria-card">';
                    echo '<a href="' . $editoria_link . '">' . $card_editoria . '</a>';
                    echo '</div>';
                    
                    // Título na margem inferior
                    echo '<div class="titulo-card">';
                    echo '<a href="' . $card_link . '">' . $card_title . '</a>';
                    echo '</div>';
                    
                    echo '</div>'; // Fim card-imagem
                    echo '</div>'; // Fim card
                }
            }
            
            echo '</div>'; // Fim linha-cards
            
            // Adicionar linha separadora entre grupos (exceto após a última linha)
            if ($linha < min($linhas, 5) - 1) {
                echo '<div class="linha-separadora"></div>';
            }
        }
        
        echo '</div>'; // Fim container-cards
    }
    echo '</div>';
endif;

include '7bann_vert160.php';

include 'footer.php';
include '0above_body.php';
?>
</body>
</html>