<?php
// ============================================
// Upload Client Profile Photo
// ============================================
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
requireClient();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['photo'])) {
    echo json_encode(['ok' => false, 'error' => 'No file uploaded']);
    exit();
}

$file = $_FILES['photo'];
$clientId = $_SESSION['user_id'];

// Validate
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    echo json_encode(['ok' => false, 'error' => 'Only JPEG, PNG, GIF, and WebP images are allowed.']);
    exit();
}
if ($file['size'] > 3 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'Image must be under 3 MB.']);
    exit();
}

// Save to uploads/profiles/
$uploadDir = __DIR__ . '/../../assets/images/profiles/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mime];
$filename = 'avatar_' . $clientId . '_' . time() . '.' . $ext;
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save image. Check server permissions.']);
    exit();
}

// Remove old photo if exists
$old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT profile_photo FROM users WHERE id = $clientId"));
if ($old && $old['profile_photo']) {
    $oldPath = $uploadDir . basename($old['profile_photo']);
    if (file_exists($oldPath)) @unlink($oldPath);
}

// Update DB
$photoPath = '/storehub/assets/images/profiles/' . $filename;
$stmt = mysqli_prepare($conn, "UPDATE users SET profile_photo = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $photoPath, $clientId);
mysqli_stmt_execute($stmt);

echo json_encode(['ok' => true, 'url' => $photoPath]);
exit();
?>
