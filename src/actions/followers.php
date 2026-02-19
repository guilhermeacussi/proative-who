<?php
// followers.php - Funções para gerenciar seguidores

/**
 * Verifica se um usuário segue outro.
 * @param PDO $pdo Conexão com o banco.
 * @param int $follower_id ID do seguidor.
 * @param int $followed_id ID do seguido.
 * @return bool True se segue, false caso contrário.
 */
function isFollowing(PDO $pdo, int $follower_id, int $followed_id): bool {
    if ($follower_id === $followed_id) return false; // Não pode seguir a si mesmo
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM followers WHERE follower_id = ? AND followed_id = ? LIMIT 1');
        $stmt->execute([$follower_id, $followed_id]);
        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        error_log('Erro em isFollowing: ' . $e->getMessage());
        return false;
    }
}

/**
 * Faz um usuário seguir outro.
 * @param PDO $pdo Conexão com o banco.
 * @param int $follower_id ID do seguidor.
 * @param int $followed_id ID do seguido.
 * @return bool True se sucesso, false se erro ou já segue.
 */
function followUser(PDO $pdo, int $follower_id, int $followed_id): bool {
    if ($follower_id === $followed_id || isFollowing($pdo, $follower_id, $followed_id)) return false;
    try {
        $stmt = $pdo->prepare('INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)');
        return $stmt->execute([$follower_id, $followed_id]);
    } catch (PDOException $e) {
        error_log('Erro em followUser: ' . $e->getMessage());
        return false;
    }
}

/**
 * Faz um usuário deixar de seguir outro.
 * @param PDO $pdo Conexão com o banco.
 * @param int $follower_id ID do seguidor.
 * @param int $followed_id ID do seguido.
 * @return bool True se sucesso, false se erro ou não seguia.
 */
function unfollowUser(PDO $pdo, int $follower_id, int $followed_id): bool {
    if (!isFollowing($pdo, $follower_id, $followed_id)) return false;
    try {
        $stmt = $pdo->prepare('DELETE FROM followers WHERE follower_id = ? AND followed_id = ?');
        return $stmt->execute([$follower_id, $followed_id]);
    } catch (PDOException $e) {
        error_log('Erro em unfollowUser: ' . $e->getMessage());
        return false;
    }
}

/**
 * Conta os seguidores de um usuário.
 * @param PDO $pdo Conexão com o banco.
 * @param int $user_id ID do usuário.
 * @return int Número de seguidores.
 */
function countFollowers(PDO $pdo, int $user_id): int {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM followers WHERE followed_id = ?');
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erro em countFollowers: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Conta quantos usuários um usuário segue.
 * @param PDO $pdo Conexão com o banco.
 * @param int $user_id ID do usuário.
 * @return int Número de usuários seguidos.
 */
function countFollowing(PDO $pdo, int $user_id): int {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM followers WHERE follower_id = ?');
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Erro em countFollowing: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Lista os seguidores de um usuário (IDs e nomes).
 * @param PDO $pdo Conexão com o banco.
 * @param int $user_id ID do usuário.
 * @return array Lista de arrays ['id', 'nome'].
 */
function getFollowers(PDO $pdo, int $user_id): array {
    try {
        $stmt = $pdo->prepare('
            SELECT u.id, u.nome 
            FROM followers f 
            JOIN users u ON f.follower_id = u.id 
            WHERE f.followed_id = ? 
            ORDER BY f.created_at DESC
        ');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erro em getFollowers: ' . $e->getMessage());
        return [];
    }
}

/**
 * Lista os usuários que um usuário segue (IDs e nomes).
 * @param PDO $pdo Conexão com o banco.
 * @param int $user_id ID do usuário.
 * @return array Lista de arrays ['id', 'nome'].
 */
function getFollowing(PDO $pdo, int $user_id): array {
    try {
        $stmt = $pdo->prepare('
            SELECT u.id, u.nome 
            FROM followers f 
            JOIN users u ON f.followed_id = u.id 
            WHERE f.follower_id = ? 
            ORDER BY f.created_at DESC
        ');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erro em getFollowing: ' . $e->getMessage());
        return [];
    }
}
