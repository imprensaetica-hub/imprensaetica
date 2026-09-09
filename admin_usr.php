<?php
require_once('Connections/conn_pwr.php');
session_start();

// Verificar se o usuário está logado e tem permissão de administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['ed_perm'] != 1) {
    header("Location: login.php");
    exit;
}

// Obter informações do usuário da sessão
$user_email = $_SESSION['email'];
$user_name = $_SESSION['name'];

// Buscar dados completos do usuário no banco de dados
$query_user = "SELECT usr_nm, usr_ed_perm FROM pwr_usr WHERE usr_mail = ?";
$stmt_user = $conn_pwr->prepare($query_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$stmt_user->bind_result($db_user_name, $db_user_ed_perm);
$stmt_user->fetch();
$stmt_user->close();

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se é o botão Voltar
    if (isset($_POST['voltar'])) {
        header("Location: admin.php?usr_nm=" . urlencode($_SESSION['name']));
        exit;
    }
    
    // Verificar se é para atualizar um membro
    if (isset($_POST['atualizar'])) {
        $usr_mail = $_POST['usr_mail'];
        $usr_ed_perm = $_POST['usr_ed_perm'];
        $usr_ptf = $_POST['usr_ptf'];
        $usr_foto = $_POST['usr_foto'];
        
        $query_update = "UPDATE pwr_usr SET usr_ed_perm = ?, usr_ptf = ?, usr_foto = ? WHERE usr_mail = ?";
        $stmt_update = $conn_pwr->prepare($query_update);
        $stmt_update->bind_param("isss", $usr_ed_perm, $usr_ptf, $usr_foto, $usr_mail);
        $stmt_update->execute();
        $stmt_update->close();
        
        // Recarregar a página para mostrar as alterações
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Verificar se é para excluir um membro
    if (isset($_POST['excluir'])) {
        $usr_mail = $_POST['usr_mail'];
        
        // Não permitir que o usuário exclua a si mesmo
        if ($usr_mail != $_SESSION['email']) {
            $query_delete = "DELETE FROM pwr_usr WHERE usr_mail = ?";
            $stmt_delete = $conn_pwr->prepare($query_delete);
            $stmt_delete->bind_param("s", $usr_mail);
            $stmt_delete->execute();
            $stmt_delete->close();
        } else {
            // Mensagem de erro - não pode excluir a si mesmo
            $erro = "Você não pode excluir sua própria conta.";
        }
        
        // Recarregar a página para mostrar as alterações
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Verificar se é para criar um novo membro
    if (isset($_POST['criar'])) {
        $novo_usr_mail = $_POST['novo_usr_mail'];
        $novo_usr_nm = $_POST['novo_usr_nm'];
        $novo_usr_ed_perm = $_POST['novo_usr_ed_perm'];
        $senha_default = "primeiroacesso";
        
        // Verificar se o e-mail já existe
        $query_check = "SELECT usr_mail FROM pwr_usr WHERE usr_mail = ?";
        $stmt_check = $conn_pwr->prepare($query_check);
        $stmt_check->bind_param("s", $novo_usr_mail);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows == 0) {
            // Inserir novo usuário
            $query_insert = "INSERT INTO pwr_usr (usr_mail, usr_nm, usr_ed_perm, usr_passw) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn_pwr->prepare($query_insert);
            $stmt_insert->bind_param("ssis", $novo_usr_mail, $novo_usr_nm, $novo_usr_ed_perm, $senha_default);
            $stmt_insert->execute();
            $stmt_insert->close();
            
            // Recarregar a página
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        
        $stmt_check->close();
    }
}

// Buscar todos os membros
$query_members = "SELECT usr_mail, usr_nm, usr_ed_perm, usr_ptf, usr_foto FROM pwr_usr ORDER BY usr_nm";
$stmt_members = $conn_pwr->prepare($query_members);
$stmt_members->execute();
$result_members = $stmt_members->get_result();

// Definir a opção padrão (editar membro)
$opcao_selecionada = isset($_POST['opcao']) ? $_POST['opcao'] : 'editar';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>É-Press - Administração de Membros</title>
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
        max-width: 1000px;
    }
    
    .admin-form {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 30px 0;
    }
    
    .admin-form h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
        font-size: 24px;
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
        margin-bottom: 20px;
    }
    
    .btn-voltar:hover {
        background-color: #5a6268;
    }
    
    .opcoes-container {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
        gap: 20px;
    }
    
    .opcao-label {
        padding: 10px 15px;
        background-color: #e9ecef;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .opcao-label:hover {
        background-color: #dee2e6;
    }
    
    input[type="radio"]:checked + .opcao-label {
        background-color: #007bff;
        color: white;
    }
    
    input[type="radio"] {
        display: none;
    }
    
    .form-section {
        display: none;
    }
    
    #editar-section:target,
    #criar-section:target {
        display: block;
    }
    
    .members-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .members-table th, .members-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .members-table th {
        background-color: #f8f9fa;
        font-weight: bold;
        color: #495057;
    }
    
    .members-table tr.member-row:hover {
        background-color: #f5f5f5;
    }
    
    .profile-row {
        background-color: #f9f9f9;
    }
    
    .profile-cell {
        padding: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
        text-align: left;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .form-group textarea {
        height: 100px;
        resize: vertical;
    }
    
    .btn-submit {
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        margin-right: 5px;
    }
    
    .btn-submit:hover {
        background-color: #0069d9;
    }
    
    .btn-delete {
        padding: 10px 15px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .btn-delete:hover {
        background-color: #c82333;
    }
    
    .actions-cell {
        display: flex;
        gap: 5px;
    }
    
    .error-message {
        color: #dc3545;
        padding: 10px;
        margin-bottom: 15px;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
    }
    
    .member-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: white;
    }
    
    .member-separator {
        height: 1px;
        background-color: #ddd;
        margin: 20px 0;
        width: 100%;
    }
    
    /* Estilos para dispositivos móveis */
    @media (max-width: 768px) {
        body {
            font-size: 14px;
        }
        
        .admin-form {
            padding: 15px;
        }
        
        .admin-form h2 {
            font-size: 20px;
        }
        
        .btn-voltar {
            font-size: 14px;
            padding: 10px;
        }
        
        .members-table {
            display: none;
        }
        
        .mobile-view {
            display: block;
        }
        
        .opcoes-container {
            flex-direction: column;
            gap: 10px;
        }
        
        .member-card .form-group {
            margin-bottom: 10px;
        }
        
        .member-card .form-group label {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .mobile-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: space-between;
        }
        
        .mobile-actions button {
            flex: 1;
        }
    }
    
    /* Estilos para computadores */
    @media (min-width: 769px) {
        body {
            font-size: 14px;
        }
        
        .mobile-view {
            display: none;
        }
    }
</style>
</head>

<body>
    <div class="admin-container">
        <div class="logo-container">
            <img src="img/logo_compact.bc.fw.png" width="79" height="130" alt="É-Press Logo" />
        </div>
        
        <div class="admin-form">
            <h2>Administração de Membros</h2>
            
            <?php if (isset($erro)): ?>
                <div class="error-message"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form method="post" action="" id="voltarForm">
                <input type="hidden" name="voltar" value="1">
                <button type="submit" class="btn-voltar">Voltar ao menu</button>
            </form>
            
            <form method="post" action="" id="opcoesForm">
                <div class="opcoes-container">
                    <input type="radio" name="opcao" value="editar" id="editar" <?php echo $opcao_selecionada == 'editar' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label for="editar" class="opcao-label">Editar Membro</label>
                    
                    <input type="radio" name="opcao" value="criar" id="criar" <?php echo $opcao_selecionada == 'criar' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label for="criar" class="opcao-label">Criar Novo Membro</label>
                </div>
            </form>
            
            <?php if ($opcao_selecionada == 'editar'): ?>
                <div id="editar-section">
                    <h3>Editar Membros Existentes</h3>
                    <?php if ($result_members->num_rows > 0): ?>
                        <!-- Visualização Desktop (Tabela) -->
                        <table class="members-table">
                            <thead>
                                <tr>
                                    <th>E-mail</th>
                                    <th>Nome</th>
                                    <th>Permissão</th>
                                    <th>Foto</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($member = $result_members->fetch_assoc()): ?>
                                    <?php
                                    // Salvar os dados do membro para usar na visualização mobile também
                                    $members_data[] = $member;
                                    ?>
                                    <form method="post" action="">
                                        <tr class="member-row">
                                            <td>
                                                <div class="form-group">
                                                    <input type="email" name="usr_mail" value="<?php echo htmlspecialchars($member['usr_mail']); ?>" readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <?php echo htmlspecialchars($member['usr_nm']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <select name="usr_ed_perm">
                                                        <option value="1" <?php echo $member['usr_ed_perm'] == 1 ? 'selected' : ''; ?>>Administrador</option>
                                                        <option value="2" <?php echo $member['usr_ed_perm'] == 2 ? 'selected' : ''; ?>>Editor</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" name="usr_foto" value="<?php echo htmlspecialchars($member['usr_foto']); ?>">
                                                </div>
                                            </td>
                                            <td class="actions-cell">
                                                <button type="submit" name="atualizar" class="btn-submit">Atualizar</button>
                                                <button type="submit" name="excluir" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este membro?')">Excluir</button>
                                            </td>
                                        </tr>
                                        <tr class="profile-row">
                                            <td colspan="5" class="profile-cell">
                                                <div class="form-group">
                                                    <label>Perfil:</label>
                                                    <textarea name="usr_ptf"><?php echo htmlspecialchars($member['usr_ptf']); ?></textarea>
                                                </div>
                                            </td>
                                        </tr>
                                    </form>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <!-- Visualização Mobile (Cards) -->
                        <div class="mobile-view">
                            <?php 
                            // Reiniciar o ponteiro do resultado para percorrer novamente
                            $result_members->data_seek(0);
                            $first = true;
                            ?>
                            <?php while ($member = $result_members->fetch_assoc()): ?>
                                <?php if (!$first): ?>
                                    <hr class="member-separator">
                                <?php endif; ?>
                                <?php $first = false; ?>
                                
                                <div class="member-card">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label>E-mail:</label>
                                            <input type="email" name="usr_mail" value="<?php echo htmlspecialchars($member['usr_mail']); ?>" readonly>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Nome:</label>
                                            <div><?php echo htmlspecialchars($member['usr_nm']); ?></div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Permissão:</label>
                                            <select name="usr_ed_perm">
                                                <option value="1" <?php echo $member['usr_ed_perm'] == 1 ? 'selected' : ''; ?>>Administrador</option>
                                                <option value="2" <?php echo $member['usr_ed_perm'] == 2 ? 'selected' : ''; ?>>Editor</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Foto (URL):</label>
                                            <input type="text" name="usr_foto" value="<?php echo htmlspecialchars($member['usr_foto']); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Perfil:</label>
                                            <textarea name="usr_ptf"><?php echo htmlspecialchars($member['usr_ptf']); ?></textarea>
                                        </div>
                                        
                                        <div class="mobile-actions">
                                            <button type="submit" name="atualizar" class="btn-submit">Atualizar</button>
                                            <button type="submit" name="excluir" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este membro?')">Excluir</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>Nenhum membro encontrado.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div id="criar-section">
                    <h3>Criar Novo Membro</h3>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="novo_usr_mail">E-mail:</label>
                            <input type="email" id="novo_usr_mail" name="novo_usr_mail" required>
                        </div>
                        <div class="form-group">
                            <label for="novo_usr_nm">Nome:</label>
                            <input type="text" id="novo_usr_nm" name="novo_usr_nm" required>
                        </div>
                        <div class="form-group">
                            <label for="novo_usr_ed_perm">Permissão:</label>
                            <select id="novo_usr_ed_perm" name="novo_usr_ed_perm" required>
                                <option value="1">Administrador</option>
                                <option value="2">Editor</option>
                            </select>
                        </div>
                        <button type="submit" name="criar" class="btn-submit">Criar Membro</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="logo-container">
            <img src="img/logo_princ.fw.png" width="159" height="40" alt="É-Press" />
        </div>
    </div>

    <script>
        // Garantir que o formulário de voltar não seja validado
        document.getElementById('voltarForm').addEventListener('submit', function(e) {
            return true;
        });
        
        // Adicionar confirmação para exclusão
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja excluir este membro?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
$stmt_members->close();
?>