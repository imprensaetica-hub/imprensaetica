<?php
require_once('Connections/conn_pwr.php');
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Processar logout se solicitado
if (isset($_GET['logout'])) {
    // Destruir todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para a página inicial
    header("Location: index.php");
    exit;
}

// Obter informações do usuário da sessão
$user_email = $_SESSION['email'];
$user_name = $_SESSION['name']; // Nome do usuário da sessão

// Buscar dados do usuário no banco de dados - apenas senha e permissão de edição
$query = "SELECT usr_passw, usr_ed_perm FROM pwr_usr WHERE usr_mail = ?";
$stmt = $conn_pwr->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_password, $user_ed_perm);
$stmt->fetch();
$stmt->close();

// Verificar se é primeiro acesso (senha = "primeiroacesso")
$is_first_access = ($user_password === "primeiroacesso" || md5("primeiroacesso") === $user_password);

// Processar mudança de senha se for primeiro acesso
if ($is_first_access && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_portfolio = trim($_POST['user_portfolio']); // Novo campo
    
    // Validar campo portfólio
    if (empty($user_portfolio)) {
        $error_message = "Por favor, preencha como você se define para o público.";
    } elseif ($new_password === $confirm_password) {
        // Atualizar a senha e portfólio no banco de dados
        $hashed_password = function_exists('password_hash') ? 
            password_hash($new_password, PASSWORD_DEFAULT) : md5($new_password);
            
        $update_query = "UPDATE pwr_usr SET usr_passw = ?, usr_ptf = ? WHERE usr_mail = ?";
        $update_stmt = $conn_pwr->prepare($update_query);
        $update_stmt->bind_param("sss", $hashed_password, $user_portfolio, $user_email);
        
        if ($update_stmt->execute()) {
            $is_first_access = false;
            $success_message = "Senha alterada e perfil atualizado com sucesso!";
            $user_password = $hashed_password;
        } else {
            $error_message = "Erro ao atualizar os dados. Tente novamente.";
        }
        $update_stmt->close();
    } else {
        $error_message = "As senhas não coincidem.";
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>É-Press - Admin</title>
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
    
    .admin-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 600px;
    }
    
    .welcome-message {
        margin-bottom: 30px;
        font-size: 18px;
        color: #333;
    }
    
    .password-form, .admin-menu {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 20px 0;
    }
    
    .password-form h2, .admin-menu h2 {
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
    }
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
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
        margin-bottom: 15px;
    }
    
    .btn-submit:hover {
        background-color: #45a049;
    }
    
    .menu-item {
        display: block;
        width: 100%;
        padding: 15px;
        margin: 10px 0;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #495057;
        text-decoration: none;
        font-size: 16px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .menu-item:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
        text-decoration: none;
        color: #495057;
    }
    
    .logout-btn {
        background-color: #f44336;
        color: white;
        border: none;
        margin-top: 20px;
    }
    
    .logout-btn:hover {
        background-color: #d32f2f;
        color: white;
    }
    
    .error-message {
        color: #d9534f;
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }
    
    .success-message {
        color: #28a745;
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }
    
    /* Estilos para dispositivos móveis */
    @media (max-width: 768px) {
        body {
            font-size: 10px;
        }
        
        .password-form, .admin-menu {
            padding: 20px;
        }
        
        .password-form h2, .admin-menu h2 {
            font-size: 20px;
        }
        
        .form-group label {
            font-size: 10px;
        }
        
        .form-group input, .form-group textarea, .btn-submit, .menu-item {
            font-size: 14px;
            padding: 10px;
        }
        
        .welcome-message {
            font-size: 14px;
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
        
        .welcome-message {
            font-size: 18px;
        }
    }
</style>
</head>

<body>
    <div class="admin-container">
        <div class="logo-container">
            <img src="img/logo_compact.bc.fw.png" width="79" height="130" alt="É-Press Logo" />
        </div>
        
        <div class="welcome-message">
            Bem-vindo, <?php echo htmlspecialchars($user_name); ?>!
        </div>
        
        <?php if ($is_first_access): ?>
        <div class="password-form">
            <h2>Primeiro Acesso</h2>
            <p>Por favor, defina uma nova senha e complete seu perfil.</p>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="new_password">Nova Senha</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nova Senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-group">
                    <label for="user_portfolio">Como você quer se definir para o público?</label>
                    <textarea 
                        id="user_portfolio" 
                        name="user_portfolio" 
                        rows="4" 
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;" 
                        required
                    ></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Salvar Alterações</button>
            </form>
            
            <!-- Botão de Logout para Primeiro Acesso -->
            <button class="menu-item logout-btn" onclick="confirmLogout()">Sair do Sistema</button>
        </div>
        <?php else: ?>
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <div class="admin-menu">
                <h2>Menu Administrativo</h2>
                
                <form method="post" action="admin_news.php">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                    <input type="submit" class="menu-item" value="Nova Matéria">
                </form>
                
                <form method="post" action="admin_edit_news.php">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                    <input type="hidden" name="usr_ed_perm" value="<?php echo htmlspecialchars($user_ed_perm); ?>">
                    <input type="submit" class="menu-item" value="Editar Matéria Publicada">
                </form>
                
                <form method="post" action="admin_config.php">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                    <input type="submit" class="menu-item" value="Configurações da Conta">
                </form>
                
                <?php if ($user_ed_perm == 1): ?>
                    <form method="post" action="admin_usr.php">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">
                        <input type="submit" class="menu-item" value="Administrar Contas">
                    </form>
                <?php endif; ?>
                
                <!-- Botão de Logout para Menu Administrativo -->
                <button class="menu-item logout-btn" onclick="confirmLogout()">Sair do Sistema</button>
            </div>
        <?php endif; ?>
        
        <div class="logo-container">
            <img src="img/logo_princ.fw.png" width="159" height="40" alt="É-Press" />
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Tem certeza que deseja sair do sistema?")) {
                window.location.href = "?logout=1";
            }
        }
    </script>
</body>
</html>