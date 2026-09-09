<?php require_once('Connections/conn_pwr.php'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Busca - Imprensa &Eacute;tica</title>
<link href="models.css" rel="stylesheet" type="text/css">
<style>
    .form-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        font-family:"Palatino Linotype", "Book Antiqua", Palatino, serif;
        color:#900;
    }
    .search-options {
        margin-bottom: 20px;
    }
    .search-option {
        margin-right: 15px;
    }
    .search-field {
        display: none;
        margin-bottom: 20px;
    }
    .search-field.active {
        display: block;
    }
    input[type="text"], select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    button {
        background-color: #A00;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }
    button:hover {
        background-color: #1a252f;
    }
    .error-message {
        color: #A00;
        margin-top: 10px;
        display: none;
    }
</style>
<script>
    function toggleSearchFields() {
        var searchType = document.querySelector('input[name="search_type"]:checked').value;
        
        // Esconder todos os campos
        document.getElementById('text-search-field').classList.remove('active');
        document.getElementById('subject-search-field').classList.remove('active');
        
        // Mostrar apenas o campo selecionado
        if (searchType === 'text') {
            document.getElementById('text-search-field').classList.add('active');
        } else {
            document.getElementById('subject-search-field').classList.add('active');
        }
        
        // Esconder mensagem de erro
        document.getElementById('error-message').style.display = 'none';
    }
    
    function validateForm() {
        var searchType = document.querySelector('input[name="search_type"]:checked').value;
        var errorMessage = document.getElementById('error-message');
        
        if (searchType === 'text') {
            var textValue = document.querySelector('input[name="text_search"]').value.trim();
            if (textValue === '') {
                errorMessage.textContent = 'Por favor, digite um texto para buscar.';
                errorMessage.style.display = 'block';
                return false;
            }
        } else {
            var subjectValue = document.querySelector('select[name="assunto"]').value;
            if (subjectValue === '') {
                errorMessage.textContent = 'Por favor, selecione um assunto.';
                errorMessage.style.display = 'block';
                return false;
            }
        }
        
        return true;
    }
    
    function prepareForm() {
        var searchType = document.querySelector('input[name="search_type"]:checked').value;
        var form = document.getElementById('search-form');
        
        if (searchType === 'text') {
            // Remove o parâmetro de assunto se existir
            form.action = form.action.split('?')[0]; // Remove qualquer query string existente
            // Mantém apenas o text_search
            document.querySelector('select[name="assunto"]').disabled = true;
        } else {
            // Remove o parâmetro de texto se existir
            form.action = form.action.split('?')[0]; // Remove qualquer query string existente
            // Mantém apenas o assunto
            document.querySelector('input[name="text_search"]').disabled = true;
        }
        
        return validateForm();
    }
    
    // Inicializar quando a página carregar
    window.onload = function() {
        toggleSearchFields();
    };
</script>
</head>
<body>

<?php 
include 'header.php';

// Buscar apenas assuntos que possuem notícias na tabela pwr_not
$sql_assuntos = "SELECT DISTINCT s.id_subj, s.subj 
                 FROM pwr_subj s
                 INNER JOIN pwr_not n ON s.id_subj = n.id_subj
                 ORDER BY s.subj";
$result_assuntos = $conn_pwr->query($sql_assuntos);
?>

<div class="form-container">
    <div class="linha-assunto-titulo">Buscar Notícias</div>
    <form id="search-form" action="listagem.php" method="get" onsubmit="return prepareForm()">
        <div class="search-options"><br /><br />
            <label class="search-option">
                <input type="radio" name="search_type" value="text" checked onchange="toggleSearchFields()">
                Buscar por texto
            </label>
            <label class="search-option">
                <input type="radio" name="search_type" value="subject" onchange="toggleSearchFields()">
                Buscar por assunto
            </label>
        </div>
        
        <div id="text-search-field" class="search-field active">
            <input type="text" name="text_search" placeholder="Digite o texto para buscar...">
        </div>
        
        <div id="subject-search-field" class="search-field">
            <select name="assunto">
                <option value="">Selecione um assunto</option>
                <?php
                if ($result_assuntos->num_rows > 0) {
                    while($assunto = $result_assuntos->fetch_assoc()) {
                        echo '<option value="' . $assunto['id_subj'] . '">' . $assunto['subj'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div id="error-message" class="error-message"></div>
        
        <button type="submit">Buscar</button>
    </form>
</div>

<?php
include '1bann_hor300.php';
include 'footer.php'; 
include '0above_body.php';
?>

</body>
</html>