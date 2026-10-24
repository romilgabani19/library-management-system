<?php
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . BASE_URL . $path); exit; }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf_token'] ?? '';
        if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
            http_response_code(419); exit('Your form expired. Refresh the page and try again.');
        }
    }
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if (!current_user()) { $_SESSION['flash'] = ['type'=>'warning','text'=>'Please log in to continue.']; redirect('/login.php'); } }
function require_role(array $roles): void {
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) { http_response_code(403); exit('403 — You do not have permission to view this page.'); }
}
function flash(string $type, string $text): void { $_SESSION['flash'] = compact('type','text'); }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['text']) . '</div>';
    }
}
function money(float|int|string $amount): string { return '₹' . number_format((float)$amount, 2); }
function fine_for(string $dueDate, ?string $returnDate = null): float {
    $end = new DateTimeImmutable($returnDate ?: date('Y-m-d'));
    $due = new DateTimeImmutable($dueDate);
    $days = max(0, (int)$due->diff($end)->format('%r%a'));
    return $days * 5.0; // ₹5 per overdue day; change to suit your institution.
}
function cover_url(?string $cover): string {
    return $cover ? 'uploads/covers/' . rawurlencode(basename($cover)) : 'assets/img/book-placeholder.svg';
}
