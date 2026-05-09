<?php
// api/auth/auth_helpers.php

function auth_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/api/auth$#', '', $scriptDir);
    return $scheme . '://' . $host . $basePath;
}

function ensure_auth_schema($db) {
    try {
        $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(120) DEFAULT NULL");
    } catch (PDOException $e) {}

    try {
        $db->exec("ALTER TABLE users ADD UNIQUE INDEX idx_users_google_id (google_id)");
    } catch (PDOException $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_password_resets_token_hash (token_hash),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
}

function find_user_by_email($db, $email) {
    $stmt = $db->prepare("SELECT id, full_name, phone_or_email, role FROM users WHERE phone_or_email = :email LIMIT 1");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function find_user_by_google_id($db, $googleId) {
    $stmt = $db->prepare("SELECT id, full_name, phone_or_email, role FROM users WHERE google_id = :google_id LIMIT 1");
    $stmt->bindValue(':google_id', $googleId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function upsert_google_user($db, $googleId, $email, $fullName) {
    $user = find_user_by_google_id($db, $googleId);
    if ($user) {
        return $user;
    }

    $user = find_user_by_email($db, $email);
    if ($user) {
        $stmt = $db->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
        $stmt->bindValue(':google_id', $googleId);
        $stmt->bindValue(':id', $user['id']);
        $stmt->execute();
        return find_user_by_google_id($db, $googleId);
    }

    $password = password_hash(bin2hex(random_bytes(24)), PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (full_name, phone_or_email, password, google_id, role)
                          VALUES (:full_name, :email, :password, :google_id, 'user')");
    $stmt->bindValue(':full_name', $fullName ?: $email);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':password', $password);
    $stmt->bindValue(':google_id', $googleId);
    $stmt->execute();

    return find_user_by_google_id($db, $googleId);
}

function start_user_session($user) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
}
?>
