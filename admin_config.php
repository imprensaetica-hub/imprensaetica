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
$query_user = "SELECT usr_nm, usr_mail, usr_ptf, usr_foto FROM pwr_usr WHERE usr_mail = ?";
$stmt_user = $conn_pwr->prepare($query_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$stmt_user->bind_result($db_user_name, $db_user_mail, $db_user_ptf, $db_user_foto);
$stmt_user->fetch();
$stmt_user->close();

// Inicializar variáveis
$error = '';
$success = '';

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se é o botão Voltar
    if (isset($_POST['voltar'])) {
        header("Location: admin.php?usr_nm=" . urlencode($db_user_name));
        exit;
    }
    
    // Verificar se é o botão de redefinir senha
    if (isset($_POST['reset_password'])) {
        $new_password = "primeiroacesso";
        $query_reset = "UPDATE pwr_usr SET usr_passw = ? WHERE usr_mail = ?";
        
        if ($stmt_reset = $conn_pwr->prepare($query_reset)) {
            $stmt_reset->bind_param("ss", $new_password, $user_email);
            
            if ($stmt_reset->execute()) {
                $success = "Senha redefinida com sucesso para 'primeiroacesso'";
            } else {
                $error = "Erro ao redefinir a senha: " . $stmt_reset->error;
            }
            $stmt_reset->close();
        } else {
            $error = "Erro na preparação da consulta: " . $conn_pwr->error;
        }
    }
    
    // Verificar se é o botão Atualizar
    if (isset($_POST['update'])) {
        // Coletar dados do formulário
        $usr_mail = isset($_POST['usr_mail']) ? $_POST['usr_mail'] : '';
        $usr_ptf = isset($_POST['usr_ptf']) ? $_POST['usr_ptf'] : '';
        $usr_foto = isset($_POST['usr_foto']) ? $_POST['usr_foto'] : '';
        
        // Validar campos obrigatórios
        if (!empty($usr_mail)) {
            // Preparar a query de atualização
            $query = "UPDATE pwr_usr 
                      SET usr_mail = ?, usr_ptf = ?, usr_foto = ?
                      WHERE usr_nm = ?";
            
            if ($stmt = $conn_pwr->prepare($query)) {
                $stmt->bind_param("ssss", $usr_mail, $usr_ptf, $usr_foto, $db_user_name);
                
                if ($stmt->execute()) {
                    $success = "Perfil atualizado com sucesso!";
                    // Atualizar e-mail na sessão se foi alterado
                    if ($usr_mail != $user_email) {
                        $_SESSION['email'] = $usr_mail;
                    }
                    // Recarregar dados do usuário
                    $db_user_mail = $usr_mail;
                    $db_user_ptf = $usr_ptf;
                    $db_user_foto = $usr_foto;
                } else {
                    $error = "Erro ao atualizar o perfil: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Erro na preparação da consulta: " . $conn_pwr->error;
            }
        } else {
            $error = "Por favor, preencha o campo de e-mail.";
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>É-Press - Configurações do Editor</title>
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
    
    .config-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 800px;
    }
    
    .config-form {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 30px 0;
    }
    
    .config-form h2 {
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
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .form-group textarea {
        min-height: 200px;
        resize: vertical;
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
    
    .btn-reset {
        width: 100%;
        padding: 12px;
        background-color: #f0ad4e;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .btn-reset:hover {
        background-color: #ec971f;
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
    
    /* Estilos para dispositivos móveis */
    @media (max-width: 768px) {
        body {
            font-size: 10px;
        }
        
        .config-form {
            padding: 20px;
        }
        
        .config-form h2 {
            font-size: 20px;
        }
        
        .form-group label {
            font-size: 10px;
        }
        
        .form-group input, .form-group textarea, .btn-submit, .btn-reset, .btn-voltar {
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
    <div class="config-container">
        <div class="logo-container">
            <img src="img/logo_compact.bc.fw.png" width="79" height="130" alt="É-Press Logo" />
        </div>
        
        <div class="config-form">
            <h2>Configurações do Editor</h2>
            
            <!-- Botão Voltar -->
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
            
            <form method="post" action="" id="configForm">
                <!-- Campo Nome (usr_nm) - Somente leitura -->
                <div class="form-group">
                    <label for="usr_nm">Nome do Editor:</label>
                    <input type="text" id="usr_nm" name="usr_nm" value="<?php echo htmlspecialchars($db_user_name); ?>" readonly>
                </div>
                
                <!-- Campo E-mail (usr_mail) -->
                <div class="form-group">
                    <label for="usr_mail">E-mail*:</label>
                    <input type="email" id="usr_mail" name="usr_mail" value="<?php echo htmlspecialchars($db_user_mail); ?>" required>
                </div>
                
                <!-- Campo Perfil (usr_ptf) -->
                <div class="form-group">
                    <label for="usr_ptf">Perfil/Histórico:</label>
                    <textarea id="usr_ptf" name="usr_ptf" maxlength="50000"><?php echo htmlspecialchars($db_user_ptf); ?></textarea>
                </div>
                
                <!-- Campo Foto (usr_foto) -->
                <div class="form-group">
                    <label for="usr_foto">Foto do Editor (URL):</label>
                    <input type="text" id="usr_foto" name="usr_foto" maxlength="500" value="<?php echo htmlspecialchars($db_user_foto); ?>">
                </div>
                
                <button type="submit" name="update" class="btn-submit">Atualizar</button>
            </form>
            
            <!-- Formulário separado para redefinir senha -->
            <form method="post" action="" id="resetForm" style="margin-top: 20px;">
                <button type="submit" name="reset_password" class="btn-reset">Redefinir senha</button>
            </form>
        </div>
        
        <div class="logo-container">
            <img src="img/logo_princ.fw.png" width="159" height="40" alt="É-Press" />
        </div>
    </div>

    <script>
        // Adicionar validação básica ao formulário de configurações
        document.getElementById('configForm').addEventListener('submit', function(e) {
            var email = document.getElementById('usr_mail').value;
            
            if (!email) {
                e.preventDefault();
                alert('Por favor, preencha o campo de e-mail.');
                return false;
            }
            
            // Validação básica de e-mail
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('Por favor, insira um endereço de e-mail válido.');
                return false;
            }
        });
        
        // Confirmação para redefinir senha
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja redefinir a senha para "primeiroacesso"?\n\nO usuário precisará usar esta senha no próximo login.')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Garantir que o formulário de voltar não seja validado
        document.getElementById('voltarForm').addEventListener('submit', function(e) {
            return true;
        });
    </script>
</body>
</html>