<?
function getUserImageById(PDO $pdo, int $user_id): string {
    try {
        $stmt = $pdo->prepare('SELECT profile_image, avatar FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return getUserImage($user);
        }
        
        return '../../uploads/default.png';
    } catch (PDOException $e) {
        error_log('Erro ao buscar imagem de usuário: ' . $e->getMessage());
        return '../../uploads/default.png';
    }
}

/**
 * Busca a imagem de um usuário com verificação de arquivo assíncrona (JavaScript).
 * Retorna um objeto com informações para o frontend.
 *
 * @param array $user Array com os dados do usuário
 * @return array Array com 'url' (caminho da imagem) e 'fallback' (imagem padrão)
 */
function getUserImageData(array $user): array {
    return [
        'url' => getUserImage($user),
        'fallback' => '../../uploads/default.png'
    ];
}
?>