<?php require_once('Connections/conn_pwr.php'); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contato - Imprensa &Eacute;tica</title>
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
    .contact-title {
        font-size: 24px;
        margin-bottom: 20px;
        text-align: center;
        color: #900;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }
    input[type="text"], 
    input[type="email"], 
    select, 
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    }
    textarea {
        min-height: 120px;
        resize: vertical;
    }
    .button-group {
        display: flex;
        gap: 10px;
    }
    button {
        flex: 1;
        background-color: #A00;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    }
    button[type="reset"] {
        background-color: #666;
    }
    button:hover {
        opacity: 0.9;
    }
    .error-message {
        color: #A00;
        margin-top: 5px;
        display: none;
    }
    .success-message {
        color: #090;
        margin-top: 20px;
        padding: 10px;
        background-color: #f0fff0;
        border-radius: 4px;
        display: none;
        text-align: center;
    }
</style>
</head>
<body>

<?php 
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = "https://script.google.com/macros/s/AKfycbwrLAMFgTOjeXBRejWgwft_EMNWUeO06RmeIwGf-3FY128KQKDipItPHwh4cv1fUW86Cw/exec";

    // Coletar e sanitizar os dados do formulário
    $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $assunto = filter_var($_POST['assunto'], FILTER_SANITIZE_STRING);
    $mensagem = filter_var($_POST['mensagem'], FILTER_SANITIZE_STRING);
    $tel = filter_var($_POST['tel'], FILTER_SANITIZE_STRING);
    
    $dados = array(
        'agora' => date('d-m-Y H:i:s'),
        'nome' => $nome,
        'email' => $email,
        'AnuncioTipo' => 'IE - ' . $assunto,
        'desc_anuncio' => $mensagem,
        'tel' => $tel
    );

    // Usar cURL como na segunda página
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $resultado = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($resultado === FALSE || $httpCode != 200) {
        $envioSucesso = false;
        $erroEnvio = "Ocorreu um erro ao enviar sua mensagem. Por favor, tente novamente mais tarde.";
    } else {
        $envioSucesso = true;
    }

    curl_close($ch);
}
?>

<div class="form-container">
    <div class="contact-title">Envie uma mensagem para a equipe do Imprensa Ética</div>
    
    <?php if (isset($envioSucesso) && $envioSucesso): ?>
        <div class="success-message" id="success-message" style="display: block;">
            Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.
        </div>
    <?php elseif (isset($erroEnvio)): ?>
        <div class="error-message" id="error-message" style="display: block;">
            <?php echo $erroEnvio; ?>
        </div>
    <?php endif; ?>
    
    <form id="contact-form" method="post" action="">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
            <div class="error-message" id="nome-error"></div>
        </div>
        
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
            <div class="error-message" id="email-error"></div>
        </div>
        
        <div class="form-group">
            <label for="assunto">Assunto:</label>
            <select id="assunto" name="assunto" required>
                <option value="">Selecione um assunto</option>
                <option value="Carta de Leitor">Carta de Leitor</option>
                <option value="Anunciante">Anunciante</option>
                <option value="Sugestão de Pauta">Sugestão de Pauta</option>
                <option value="Crítica">Crítica</option>
                <option value="Pergunta">Pergunta</option>
            </select>
            <div class="error-message" id="assunto-error"></div>
        </div>
        
        <div class="form-group">
            <label for="mensagem">Mensagem:</label>
            <textarea id="mensagem" name="mensagem" required></textarea>
            <div class="error-message" id="mensagem-error"></div>
        </div>
        
        <div class="form-group">
            <label for="tel">Telefone / WhatsApp:</label>
            <input type="text" id="tel" name="tel">
        </div>
        
        <div class="button-group">
            <button type="submit">Enviar</button>
            <button type="reset">Limpar</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('contact-form').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar nome
        const nome = document.getElementById('nome');
        if (!nome.value.trim()) {
            document.getElementById('nome-error').textContent = 'Por favor, informe seu nome.';
            document.getElementById('nome-error').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('nome-error').style.display = 'none';
        }
        
        // Validar email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim() || !emailRegex.test(email.value)) {
            document.getElementById('email-error').textContent = 'Por favor, informe um e-mail válido.';
            document.getElementById('email-error').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('email-error').style.display = 'none';
        }
        
        // Validar assunto
        const assunto = document.getElementById('assunto');
        if (!assunto.value) {
            document.getElementById('assunto-error').textContent = 'Por favor, selecione um assunto.';
            document.getElementById('assunto-error').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('assunto-error').style.display = 'none';
        }
        
        // Validar mensagem
        const mensagem = document.getElementById('mensagem');
        if (!mensagem.value.trim()) {
            document.getElementById('mensagem-error').textContent = 'Por favor, escreva sua mensagem.';
            document.getElementById('mensagem-error').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('mensagem-error').style.display = 'none';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
</script>

<?php
include '1bann_hor300.php';
include '7bann_vert160.php';
include 'footer.php'; 
include '0above_body.php';
?>

</body>
</html>