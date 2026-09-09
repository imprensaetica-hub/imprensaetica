<?php
require_once('Connections/conn_pwr.php');
session_start();

// Inicializar variáveis
$error = '';
$email = '';

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validar entrada
    if (!empty($email) && !empty($password)) {
        // Consultar o banco de dados - incluindo o campo usr_nm
        $query = "SELECT usr_mail, usr_passw, usr_nm, usr_ed_perm FROM pwr_usr WHERE usr_mail = ?";
        
        if ($stmt = $conn_pwr->prepare($query)) {
            $stmt->bind_param("s", $email);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                // Verificar se o email existe
                if ($stmt->num_rows == 1) {
                    // Bind dos campos: usr_mail, usr_passw, usr_nm, usr_ed_perm
                    $stmt->bind_result($usr_mail, $hashed_password, $usr_nm, $usr_ed_perm);
                    if ($stmt->fetch()) {
                        // Verificar a senha (solução alternativa se password_verify não estiver disponível)
                        if ($hashed_password === md5($password) || $hashed_password === $password) {
                            // Iniciar a sessão
                            $_SESSION['loggedin'] = true;
                            $_SESSION['email'] = $usr_mail;
                            $_SESSION['name'] = $usr_nm; // Armazenando o nome do usuário
                            $_SESSION['ed_perm'] = $usr_ed_perm;
                            
                            // Redirecionar para a página admin
                            header("Location: admin.php");
                            exit;
                        } else {
                            $error = "Senha incorreta.";
                        }
                    }
                } else {
                    $error = "Nenhuma conta encontrada com esse email.";
                }
            } else {
                $error = "Oops! Algo deu errado. Tente novamente mais tarde.";
            }
            $stmt->close();
        } else {
            $error = "Erro na preparação da consulta.";
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>É-Press - Login</title>
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
    
    .login-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 500px;
    }
    
    .login-form {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 30px 0;
    }
    
    .login-form h2 {
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
    
    .form-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .btn-login {
        width: 100%;
        padding: 12px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    
    .btn-login:hover {
        background-color: #45a049;
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
    
    /* Estilos para dispositivos móveis */
    @media (max-width: 768px) {
        body {
            font-size: 10px;
        }
        
        .login-form {
            padding: 20px;
        }
        
        .login-form h2 {
            font-size: 20px;
        }
        
        .form-group label {
            font-size: 10px;
        }
        
        .form-group input, .btn-login {
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
    <div class="login-container">
        <div class="logo-container">
            <img src="img/logo_compact.bc.fw.png" width="79" height="130" alt="É-Press Logo" />
        </div>
        
        <div class="login-form">
            <h2>Login</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Entrar</button>
            </form>
        </div>
        
        <div class="logo-container">
            <img src="img/logo_princ.fw.png" width="159" height="40" alt="É-Press" />
        </div>
    </div>

    <script>
        // Adicionar validação básica do formulário com JavaScript
        document.querySelector('form').addEventListener('submit', function(e) {
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }
            
            // Validar formato de email
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, insira um email válido.');
                return false;
            }
        });
    </script>
</body>
</html>