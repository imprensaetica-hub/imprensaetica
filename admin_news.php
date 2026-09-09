<?php
require_once('Connections/conn_pwr.php');
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Obter informações do usuário da sessão
$user_email = $_SESSION['email'];
$user_name = $_SESSION['name'];
$user_ed_perm = $_SESSION['ed_perm'];

// Buscar dados completos do usuário no banco de dados
$query_user = "SELECT usr_nm, usr_ed_perm FROM pwr_usr WHERE usr_mail = ?";
$stmt_user = $conn_pwr->prepare($query_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$stmt_user->bind_result($db_user_name, $db_user_ed_perm);
$stmt_user->fetch();
$stmt_user->close();

// Buscar assuntos da tabela pwr_subj
$query_subj = "SELECT id_subj, subj FROM pwr_subj ORDER BY subj ASC";
$result_subj = $conn_pwr->query($query_subj);
$subjects = array();
while ($row = $result_subj->fetch_assoc()) {
    $subjects[$row['id_subj']] = $row['subj'];
}

// Inicializar variáveis
$error = '';
$success = '';
$id_subj = $usr_nm = $mat_tit = $mat_subt = $mat_body = $mat_foto = '';
$mat_status = 0;

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se é o botão Voltar
    if (isset($_POST['voltar'])) {
        // Usar o nome do usuário da sessão (valor inicial) para passar como parâmetro
        header("Location: admin.php?usr_nm=" . urlencode($_SESSION['name']));
        exit;
    }
    
    // Gerar id_mat no formato AAAAMMDDhhmmss
    $id_mat = date('YmdHis');
    
    // Coletar dados do formulário
    $id_subj = isset($_POST['id_subj']) ? $_POST['id_subj'] : '';
    $usr_nm = isset($_POST['usr_nm']) ? $_POST['usr_nm'] : '';
    $mat_tit = isset($_POST['mat_tit']) ? $_POST['mat_tit'] : '';
    $mat_subt = isset($_POST['mat_subt']) ? $_POST['mat_subt'] : '';
    $mat_body = isset($_POST['mat_body']) ? $_POST['mat_body'] : '';
    $mat_foto = isset($_POST['mat_foto']) ? $_POST['mat_foto'] : '';
    $mat_status = isset($_POST['mat_status']) ? 1 : 0;
    
    // Validar campos obrigatórios
    if (!empty($mat_tit) && !empty($mat_subt) && !empty($mat_body)) {
        // Preparar a query de inserção
        $query = "INSERT INTO pwr_not (id_mat, id_subj, usr_nm, mat_tit, mat_subt, mat_body, mat_foto, mat_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn_pwr->prepare($query)) {
            $stmt->bind_param("sisssssi", $id_mat, $id_subj, $usr_nm, $mat_tit, $mat_subt, $mat_body, $mat_foto, $mat_status);
            
            if ($stmt->execute()) {
                $success = htmlspecialchars($usr_nm ? $usr_nm : $db_user_name) . ": Matéria cadastrada com sucesso! ID: " . $id_mat;
                // Limpar os campos do formulário após o sucesso
                $id_subj = $usr_nm = $mat_tit = $mat_subt = $mat_body = $mat_foto = '';
                $mat_status = 0;
            } else {
                $error = "Erro ao cadastrar a matéria: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Erro na preparação da consulta: " . $conn_pwr->error;
        }
    } else {
        $error = "Por favor, " . htmlspecialchars($usr_nm ? $usr_nm : $db_user_name) . ", preencha todos os campos obrigatórios.";
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>É-Press - Nova Matéria</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif;
    }
    
    body {
        background-color: #f5f5f5;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 20px;
        text-align: center;
    }
    
    .logo-container {
        margin-bottom: 30px;
    }
    
    .news-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 800px;
    }
    
    .news-form {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 30px 0;
    }
    
    .news-form h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
        font-size: 24px;
    }
    
    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-size: 14px;
        font-weight: bold;
    }
    
    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-group pre {
        background-color: #f8f9fa;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: monospace;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .btn-submit {
        width: 100%;
        padding: 12px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .btn-submit:hover {
        background-color: #45a049;
    }
    
    .btn-voltar {
        width: 100%;
        padding: 12px;
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }
    
    .btn-voltar:hover {
        background-color: #5a6268;
    }
    
    .error-message {
        color: #d9534f;
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        padding: 10px;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
    }
    
    .success-message {
        color: #28a745;
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        padding: 10px;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
    }
    
    .checkbox-group input {
        width: auto;
        margin-right: 10px;
    }
    
    /* Estilos para dispositivos móveis */
    @media (max-width: 768px) {
        body {
            font-size: 10px;
        }
        
        .news-form {
            padding: 20px;
        }
        
        .news-form h2 {
            font-size: 20px;
        }
        
        .form-group label {
            font-size: 10px;
        }
        
        .form-group input, .form-group select, .form-group textarea, .btn-submit, .btn-voltar {
            font-size: 14px;
            padding: 10px;
        }
    }
    
    /* Estilos para computadores */
    @media (min-width: 769px) {
        body {
            font-size: 14px;
        }
        
        .form-group label {
            font-size: 14px;
        }
    }
</style>
</head>

<body>
    <div class="news-container">
        <div class="logo-container">
            <img src="img/logo_compact.bc.fw.png" width="79" height="130" alt="É-Press Logo" />
        </div>
        
        <div class="news-form">
            <h2>Criar nova matéria</h2>
            
            <!-- Botão Voltar - Formulário separado para evitar validação -->
            <form method="post" action="" id="voltarForm" style="margin-bottom: 20px;">
                <input type="hidden" name="voltar" value="1">
                <button type="submit" class="btn-voltar">Voltar ao menu</button>
            </form>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" action="" id="newsForm">
                <!-- Campo Assunto (id_subj) -->
                <div class="form-group">
                    <label for="id_subj">Assunto*:</label>
                    <select id="id_subj" name="id_subj" required>
                        <option value="">Selecione um assunto</option>
                        <?php foreach ($subjects as $id => $subject): ?>
                            <option value="<?php echo $id; ?>" <?php echo ($id_subj == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Campo Editor (usr_nm) -->
                <div class="form-group">
                    <label for="usr_nm">Editor:</label>
                    <?php if ($db_user_ed_perm == 1): ?>
                        <input type="text" id="usr_nm" name="usr_nm" value="<?php echo htmlspecialchars($usr_nm ? $usr_nm : $db_user_name); ?>" required>
                    <?php else: ?>
                        <input type="text" id="usr_nm" name="usr_nm" value="<?php echo htmlspecialchars($db_user_name); ?>" readonly>
                    <?php endif; ?>
                </div>
                
                <!-- Campo Título (mat_tit) -->
                <div class="form-group">
                    <label for="mat_tit">Título da Matéria*:</label>
                    <pre><input type="text" id="mat_tit" name="mat_tit" maxlength="100" value="<?php echo htmlspecialchars($mat_tit); ?>" required></pre>
                </div>
                
                <!-- Campo Linha Fina (mat_subt) -->
                <div class="form-group">
                    <label for="mat_subt">Linha fina*:</label>
                    <pre><textarea id="mat_subt" name="mat_subt" maxlength="5000" rows="5" required><?php echo htmlspecialchars($mat_subt); ?></textarea></pre>
                </div>
                
                <!-- Campo Corpo da Matéria (mat_body) -->
                <div class="form-group">
                    <label for="mat_body">Corpo da Matéria*:</label>
                    <pre><textarea id="mat_body" name="mat_body" maxlength="4294967295" rows="25" required><?php echo htmlspecialchars($mat_body); ?></textarea></pre>
                </div>
                
                <!-- Campo Foto (mat_foto) -->
                <div class="form-group">
                    <label for="mat_foto">Foto da Matéria (copie o link de alguma foto relativa à matéria do Google):</label>
                    <input type="text" id="mat_foto" name="mat_foto" maxlength="500" value="<?php echo htmlspecialchars($mat_foto); ?>">
                </div>
                
                <!-- Campo Publicar (mat_status) -->
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="mat_status" name="mat_status" value="1" <?php echo ($mat_status == 1) ? 'checked' : ''; ?>>
                    <label for="mat_status">Publicar?</label>
                </div>
                
                <button type="submit" class="btn-submit">Enviar</button>
            </form>
        </div>
        
        <div class="logo-container">
            <img src="img/logo_princ.fw.png" width="159" height="40" alt="É-Press" />

        </div>
    </div>

    <script>
        // Adicionar validação básica apenas ao formulário de notícias
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            var titulo = document.getElementById('mat_tit').value;
            var linhaFina = document.getElementById('mat_subt').value;
            var corpo = document.getElementById('mat_body').value;
            var assunto = document.getElementById('id_subj').value;
            
            if (!assunto || !titulo || !linhaFina || !corpo) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }
            
            if (titulo.length > 100) {
                e.preventDefault();
                alert('O título não pode ter mais de 100 caracteres.');
                return false;
            }
            
            if (linhaFina.length > 5000) {
                e.preventDefault();
                alert('A linha fina não pode ter mais de 5000 caracteres.');
                return false;
            }
            
            if (corpo.length > 4294967295) {
                e.preventDefault();
                alert('O corpo da matéria não pode ter mais de 4294967295 caracteres.');
                return false;
            }
        });
        
        // Garantir que o formulário de voltar não seja validado
        document.getElementById('voltarForm').addEventListener('submit', function(e) {
            // Não fazer nenhuma validação, permitir o envio imediato
            return true;
        });
    </script>
</body>
</html>