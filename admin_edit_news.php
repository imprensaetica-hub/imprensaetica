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

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se é o botão Voltar
    if (isset($_POST['voltar'])) {
        // Usar o nome do usuário da sessão (valor inicial) para passar como parâmetro
        header("Location: admin.php?usr_nm=" . urlencode($_SESSION['name']));
        exit;
    }
    
    // Verificar se é uma atualização de acessos
    if (isset($_POST['atualizar_acessos'])) {
        $id_mat = $_POST['id_mat'];
        $novo_acesso = $_POST['mat_acc'];
        
        // Validar que o número de acessos é um número inteiro positivo
        if (is_numeric($novo_acesso) && $novo_acesso >= 0) {
            $query_update = "UPDATE pwr_not SET mat_acc = ? WHERE id_mat = ?";
            $stmt_update = $conn_pwr->prepare($query_update);
            $stmt_update->bind_param("is", $novo_acesso, $id_mat);
            $stmt_update->execute();
            $stmt_update->close();
            
            // Recarregar a página para refletir a alteração
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    // Verificar se é uma exclusão de matéria
    if (isset($_POST['excluir_materia'])) {
        $id_mat = $_POST['id_mat'];
        
        // Verificar permissão para exclusão
        if ($db_user_ed_perm == 1) {
            // Permissão total: pode excluir qualquer matéria
            $query_delete = "DELETE FROM pwr_not WHERE id_mat = ?";
            $stmt_delete = $conn_pwr->prepare($query_delete);
            $stmt_delete->bind_param("s", $id_mat);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // Recarregar a página para refletir a exclusão
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Permissão limitada: só pode excluir suas próprias matérias
            $query_delete = "DELETE FROM pwr_not WHERE id_mat = ? AND usr_nm = ?";
            $stmt_delete = $conn_pwr->prepare($query_delete);
            $stmt_delete->bind_param("ss", $id_mat, $db_user_name);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // Recarregar a página para refletir a exclusão
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    // Verificar se é uma publicação de matéria
    if (isset($_POST['publicar_materia'])) {
        $id_mat = $_POST['id_mat'];
        
        // Verificar permissão para publicação
        if ($db_user_ed_perm == 1) {
            // Permissão total: pode publicar qualquer matéria
            $query_publish = "UPDATE pwr_not SET mat_status = 1 WHERE id_mat = ?";
            $stmt_publish = $conn_pwr->prepare($query_publish);
            $stmt_publish->bind_param("s", $id_mat);
            $stmt_publish->execute();
            $stmt_publish->close();
        } else {
            // Permissão limitada: só pode publicar suas próprias matérias
            $query_publish = "UPDATE pwr_not SET mat_status = 1 WHERE id_mat = ? AND usr_nm = ?";
            $stmt_publish = $conn_pwr->prepare($query_publish);
            $stmt_publish->bind_param("ss", $id_mat, $db_user_name);
            $stmt_publish->execute();
            $stmt_publish->close();
        }
        
        // Recarregar a página para refletir a publicação
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Buscar matérias com base na permissão do usuário
if ($db_user_ed_perm == 1) {
    // Permissão total: buscar todas as matérias
    $query_news = "SELECT n.id_mat, n.id_subj, n.mat_tit, n.mat_status, n.mat_acc, s.subj, n.usr_nm 
                   FROM pwr_not n 
                   JOIN pwr_subj s ON n.id_subj = s.id_subj 
                   ORDER BY n.id_mat DESC";
    $stmt_news = $conn_pwr->prepare($query_news);
} else {
    // Permissão limitada: buscar apenas matérias do usuário atual
    $query_news = "SELECT n.id_mat, n.id_subj, n.mat_tit, n.mat_status, n.mat_acc, s.subj, n.usr_nm 
                   FROM pwr_not n 
                   JOIN pwr_subj s ON n.id_subj = s.id_subj 
                   WHERE n.usr_nm = ? 
                   ORDER BY n.id_mat DESC";
    $stmt_news = $conn_pwr->prepare($query_news);
    $stmt_news->bind_param("s", $db_user_name);
}

$stmt_news->execute();
$result_news = $stmt_news->get_result();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>É-Press - Editar Matérias</title>
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
    
    .edit-news-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 1100px;
    }
    
    .edit-news-form {
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        margin: 30px 0;
    }
    
    .edit-news-form h2 {
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
    
    .news-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .news-table th, .news-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .news-table th {
        background-color: #f8f9fa;
        font-weight: bold;
        color: #495057;
    }
    
    .news-table tr:hover {
        background-color: #f5f5f5;
    }
    
    .news-link {
        color: #007bff;
        text-decoration: none;
    }
    
    .news-link:hover {
        text-decoration: underline;
    }
    
    .status-published {
        color: #28a745;
        font-weight: bold;
    }
    
    .status-draft {
        color: #dc3545;
        font-weight: bold;
    }
    
    .user-column {
        display: <?php echo ($db_user_ed_perm == 1) ? 'table-cell' : 'none'; ?>;
    }
    
    .access-input {
        width: 80px;
        padding: 6px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .btn-update {
        padding: 6px 12px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        min-width: 32px;
    }
    
    .btn-update:hover {
        background-color: #0069d9;
    }
    
    .btn-delete {
        padding: 6px 12px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        min-width: 32px;
    }
    
    .btn-delete:hover {
        background-color: #c82333;
    }
    
    .btn-publish {
        padding: 6px 12px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        min-width: 32px;
    }
    
    .btn-publish:hover {
        background-color: #218838;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: nowrap;
    }
    
    /* Estilos para dispositivos móveis */
    @media (max-width: 768px) {
        body {
            font-size: 10px;
        }
        
        .edit-news-form {
            padding: 20px;
        }
        
        .edit-news-form h2 {
            font-size: 20px;
        }
        
        .btn-voltar {
            font-size: 14px;
            padding: 10px;
        }
        
        .news-table th, .news-table td {
            padding: 8px;
            font-size: 12px;
        }
        
        .access-input {
            width: 60px;
            padding: 4px;
            font-size: 12px;
        }
        
        .btn-update, .btn-delete, .btn-publish {
            padding: 4px 8px;
            font-size: 12px;
            min-width: 28px;
        }
        
        .action-buttons {
            flex-direction: row;
            gap: 3px;
        }
    }
    
    /* Estilos para computadores */
    @media (min-width: 769px) {
        body {
            font-size: 14px;
        }
    }
</style>
</head>

<body>
    <div class="edit-news-container">
        <div class="logo-container">
            <img src="img/logo_compact.bc.fw.png" width="79" height="130" alt="É-Press Logo" />
        </div>
        
        <div class="edit-news-form">
            <h2>Selecione a matéria para editar:</h2>
            <form method="post" action="" id="voltarForm">
                <input type="hidden" name="voltar" value="1">
                <button type="submit" class="btn-voltar">Voltar ao menu</button>
            </form>
            
            <?php if ($result_news->num_rows > 0): ?>
                <table class="news-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Título</th>
                            <?php if ($db_user_ed_perm == 1): ?>
                                <th class="user-column">Autor</th>
                            <?php endif; ?>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Acessos</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_news->fetch_assoc()): 
                            // Converter id_mat (AAAAMMDDHHMMSS) para formato DD/MM/AAAA
                            $id_mat = $row['id_mat'];
                            $year = substr($id_mat, 0, 4);
                            $month = substr($id_mat, 4, 2);
                            $day = substr($id_mat, 6, 2);
                            $formatted_date = $day . '/' . $month . '/' . $year;
                            $is_draft = ($row['mat_status'] == 0);
                        ?>
                            <tr>
                                <td><?php echo $formatted_date; ?></td>
                                <td>
                                    <a href="admin_edit.php?id_mat=<?php echo $id_mat; ?>" class="news-link">
                                        <?php echo htmlspecialchars($row['mat_tit']); ?>
                                    </a>
                                </td>
                                <?php if ($db_user_ed_perm == 1): ?>
                                    <td class="user-column"><?php echo htmlspecialchars($row['usr_nm']); ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($row['subj']); ?></td>
                                <td>
                                    <?php if ($row['mat_status'] == 1): ?>
                                        <span class="status-published">Publicada</span>
                                    <?php else: ?>
                                        <span class="status-draft">Rascunho</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="" style="display: flex; align-items: center; gap: 5px;">
                                        <input type="hidden" name="id_mat" value="<?php echo $id_mat; ?>">
                                        <input type="number" name="mat_acc" value="<?php echo $row['mat_acc']; ?>" class="access-input" min="0">
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="submit" name="atualizar_acessos" class="btn-update" title="Atualizar acessos">A</button>
                                    </form>
                                        
                                    <form method="post" action="" onsubmit="return confirm('Tem certeza que deseja excluir esta matéria? Esta ação não pode ser desfeita.');" style="display: inline;">
                                        <input type="hidden" name="id_mat" value="<?php echo $id_mat; ?>">
                                        <button type="submit" name="excluir_materia" class="btn-delete" title="Excluir matéria">X</button>
                                    </form>
                                    
                                    <?php if ($is_draft): ?>
                                    <form method="post" action="" onsubmit="return confirm('Publicar esta matéria? Ela ficará visível para todos os usuários.');" style="display: inline;">
                                        <input type="hidden" name="id_mat" value="<?php echo $id_mat; ?>">
                                        <button type="submit" name="publicar_materia" class="btn-publish" title="Publicar matéria">P</button>
                                    </form>
                                    <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhuma matéria encontrada para edição.</p>
            <?php endif; ?>
        </div>
        
        <div class="logo-container">
            <img src="img/logo_princ.fw.png" width="159" height="40" alt="É-Press" />
      </div>
    </div>

    <script>
        // Garantir que o formulário de voltar não seja validado
        document.getElementById('voltarForm').addEventListener('submit', function(e) {
            // Não fazer nenhuma validação, permitir o envio imediato
            return true;
        });
    </script>
</body>
</html>
<?php
$stmt_news->close();
?>