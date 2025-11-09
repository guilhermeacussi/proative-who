<?php
require __DIR__ . '/../init.php'; // Ajustado: sobe um nível para acessar src/init.php
header('Content-Type: application/json'); // Resposta JSON

$me = current_user($pdo);
if (!$me) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit;
}

$upload_dir = __DIR__ . '/../../uploads/'; // Ajustado: sobe dois níveis para acessar uploads/ no diretório raiz
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$bio = trim($_POST['bio'] ?? '');
$pgp_key = trim($_POST['pgp_key'] ?? '');
$profile_image_field = trim($_POST['profile_image'] ?? '');
$profile_image_to_save = $me['profile_image'] ?? '';

// Processa Cloudinary
if (!empty($profile_image_field) && filter_var($profile_image_field, FILTER_VALIDATE_URL)) {
    $profile_image_to_save = $profile_image_field;
}

// Processa upload local
if (!empty($_FILES['profile_image_file']['name']) && $_FILES['profile_image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (in_array($ext, $allowed)) {
        $newName = 'avatar_' . uniqid() . '.' . $ext;
        $dest = $upload_dir . $newName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $profile_image_to_save = 'uploads/' . $newName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo local.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido.']);
        exit;
    }
}

// Atualiza DB
try {
    $stmt = $pdo->prepare('UPDATE users SET bio = ?, pgp_key = ?, profile_image = ? WHERE id = ?');
    $stmt->execute([$bio, $pgp_key, $profile_image_to_save, $me['id']]);
    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>
