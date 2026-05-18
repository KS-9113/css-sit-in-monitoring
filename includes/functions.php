<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function isLoggedInStudent(): bool
{
    return !empty($_SESSION['student_id']);
}

function isLoggedInAdmin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function requireStudent(): void
{
    if (!isLoggedInStudent()) {
        redirect('/login.php');
    }
}

function requireAdmin(): void
{
    if (!isLoggedInAdmin()) {
        redirect('/admin/login.php');
    }
}

function getStudentById(int $id): ?array
{
    $stmt = getDB()->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getStudentFullName(array $student): string
{
    $middle = trim($student['middle_name'] ?? '');
    return trim($student['first_name'] . ($middle ? ' ' . $middle . ' ' : ' ') . $student['last_name']);
}

function generateSitInNo(): string
{
    return 'SI-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function studentHasActiveReservation(int $studentId): bool
{
    $stmt = getDB()->prepare("
        SELECT COUNT(*) FROM sit_in_records
        WHERE student_id = ?
        AND status IN ('Reserved', 'Approved', 'On Going')
    ");
    $stmt->execute([$studentId]);
    return (int) $stmt->fetchColumn() > 0;
}

function isPcOccupied(int $labId, int $pcNumber, ?int $excludeRecordId = null): bool
{
    $sql = "
        SELECT COUNT(*) FROM sit_in_records
        WHERE laboratory_id = ? AND pc_number = ?
        AND status = 'On Going'
    ";
    $params = [$labId, $pcNumber];
    if ($excludeRecordId) {
        $sql .= ' AND id != ?';
        $params[] = $excludeRecordId;
    }
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function isPcBooked(int $labId, int $pcNumber, string $date, ?int $excludeRecordId = null): bool
{
    $sql = "
        SELECT COUNT(*) FROM sit_in_records
        WHERE laboratory_id = ? AND pc_number = ? AND scheduled_date = ?
        AND status IN ('Reserved', 'Approved', 'On Going')
    ";
    $params = [$labId, $pcNumber, $date];
    if ($excludeRecordId) {
        $sql .= ' AND id != ?';
        $params[] = $excludeRecordId;
    }
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function deductSessionIfNeeded(int $recordId): void
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM sit_in_records WHERE id = ?');
    $stmt->execute([$recordId]);
    $record = $stmt->fetch();
    if (!$record || $record['session_deducted'] || $record['status'] !== 'Completed') {
        return;
    }
    $upd = $db->prepare('UPDATE students SET remaining_sessions = GREATEST(remaining_sessions - 1, 0) WHERE id = ?');
    $upd->execute([$record['student_id']]);
    $db->prepare('UPDATE sit_in_records SET session_deducted = 1 WHERE id = ?')->execute([$recordId]);
}

function computeDuration(?string $timeIn, ?string $timeOut): ?int
{
    if (!$timeIn || !$timeOut) {
        return null;
    }
    $start = strtotime($timeIn);
    $end = strtotime($timeOut);
    if ($end <= $start) {
        return 0;
    }
    return (int) round(($end - $start) / 60);
}

function formatDuration(?int $minutes): string
{
    if ($minutes === null) {
        return '—';
    }
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    if ($h > 0) {
        return sprintf('%dh %dm', $h, $m);
    }
    return sprintf('%dm', $m);
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'Approved' => 'bg-success',
        'On Going' => 'bg-primary',
        'Completed' => 'bg-secondary',
        'Rejected' => 'bg-danger',
        'User Cancelled' => 'bg-warning text-dark',
        'Reserved' => 'bg-info text-dark',
        default => 'bg-light text-dark',
    };
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function uploadProfilePicture(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed, true)) {
        return null;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = 'profile_' . uniqid() . '.' . strtolower($ext);
    $dest = __DIR__ . '/../assets/uploads/profiles/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return null;
}

function getProfilePictureUrl(?string $filename): string
{
    $file = $filename ?: 'default-avatar.png';
    $path = __DIR__ . '/../assets/uploads/profiles/' . $file;
    if (!file_exists($path)) {
        return BASE_URL . '/assets/img/default-avatar.svg';
    }
    return BASE_URL . '/assets/uploads/profiles/' . $file;
}
