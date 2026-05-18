<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to a specific URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display flash message
 */
function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d M, Y', strtotime($date));
}

/**
 * Count full overdue days. A fine starts only after the due date has passed.
 */
function getOverdueDays($dueDate, $checkDate = null) {
    if (empty($dueDate)) {
        return 0;
    }

    $due = new DateTimeImmutable(date('Y-m-d', strtotime($dueDate)));
    $check = new DateTimeImmutable(date('Y-m-d', strtotime($checkDate ?: 'today')));

    if ($check <= $due) {
        return 0;
    }

    return (int)$due->diff($check)->days;
}

function calculateFine($dueDate, $fineRate, $checkDate = null) {
    return getOverdueDays($dueDate, $checkDate) * (float)$fineRate;
}

function getIssueFineRate($issue, $defaultRate = 10) {
    if (isset($issue['fine_per_day']) && $issue['fine_per_day'] !== null && $issue['fine_per_day'] !== '') {
        return (float)$issue['fine_per_day'];
    }

    return (float)$defaultRate;
}

function getIssueDisplayStatus($issue) {
    if (($issue['status'] ?? '') === 'returned') {
        return 'returned';
    }

    return getOverdueDays($issue['due_date'] ?? null) > 0 ? 'overdue' : 'issued';
}
?>
