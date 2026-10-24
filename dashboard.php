<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
if (time() - (int)($_SESSION['last_activity'] ?? time()) > 1800) {
    $_SESSION = [];
    session_destroy();
    session_start();
    flash('warning', 'Your session expired. Please log in again.');
    redirect('/login.php');
}
$_SESSION['last_activity'] = time();
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
$role = $user['role'];
?>
<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <div class="side-label">Workspace</div><a class="side-link active" href="dashboard.php"><i class="bi bi-grid"></i> Overview</a><a class="side-link" href="catalog.php"><i class="bi bi-book"></i> Book catalog</a>
        <?php if (in_array($role, ['admin', 'librarian'], true)): ?><div class="side-label mt-2">Library operations</div><a class="side-link" href="books.php"><i class="bi bi-journal-plus"></i> Manage books</a><a class="side-link" href="loans.php"><i class="bi bi-arrow-left-right"></i> Issue & return</a><?php endif; ?>
        <?php if ($role === 'admin'): ?><a class="side-link" href="members.php"><i class="bi bi-people"></i> Members</a><a class="side-link" href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a><?php endif; ?>
        <?php if ($role === 'student'): ?><div class="side-label mt-2">My library</div><a class="side-link" href="my-loans.php"><i class="bi bi-clock-history"></i> My borrowing</a><a class="side-link" href="reservations.php"><i class="bi bi-bookmark"></i> Reservations</a><?php endif; ?>
    </aside>
    <section class="dashboard-main">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="text-primary fw-bold small text-uppercase mb-1"><?= e($role) ?> workspace</p>
                <h1 class="page-title mb-1">Hello, <?= e($user['name']) ?> 👋</h1>
                <p class="muted mb-0">Here’s what’s happening in your library today.</p>
            </div><a href="catalog.php" class="btn btn-primary"><i class="bi bi-search me-1"></i> Find a book</a>
        </div>
        <?php if ($role === 'admin' || $role === 'librarian'):
            $stats = [];
            $stats['books'] = (int)db()->query('SELECT COALESCE(SUM(total_quantity),0) FROM books WHERE is_active=1')->fetchColumn();
            $stats['students'] = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetchColumn();
            $stats['issued'] = (int)db()->query("SELECT COUNT(*) FROM issues WHERE status='issued'")->fetchColumn();
            $stats['overdue'] = (int)db()->query("SELECT COUNT(*) FROM issues WHERE status='issued' AND due_date<CURDATE()")->fetchColumn();
            $stats['fines'] = (float)db()->query("SELECT COALESCE(SUM(amount-paid_amount),0) FROM fines")->fetchColumn();
        ?>
            <div class="row g-3 mb-4"><?php foreach ([['Total copies', $stats['books'], 'bi-book-half'], ['Students', $stats['students'], 'bi-people'], ['Currently issued', $stats['issued'], 'bi-arrow-left-right'], ['Overdue loans', $stats['overdue'], 'bi-alarm'], ['Outstanding fines', money($stats['fines']), 'bi-cash-coin']] as $s): ?><div class="col-6 col-xl">
                        <div class="stat-card">
                            <div class="stat-icon mb-3"><i class="bi <?= e($s[2]) ?>"></i></div>
                            <div class="stat-value"><?= e((string)$s[1]) ?></div>
                            <div class="muted small mt-1"><?= e($s[0]) ?></div>
                        </div>
                    </div><?php endforeach; ?></div>
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="surface p-4 h-100">
                        <div class="d-flex justify-content-between mb-3">
                            <h2 class="h5 fw-bold">Loan overview</h2><span class="badge-soft">Live data</span>
                        </div><?php $total = max(1, $stats['issued'] + $stats['overdue']);
                                foreach ([['On-time loans', $stats['issued'] - $stats['overdue']], ['Overdue loans', $stats['overdue']]] as $item): ?><div class="d-flex justify-content-between small mb-2 mt-3"><span><?= e($item[0]) ?></span><strong><?= max(0, (int)$item[1]) ?></strong></div>
                            <div class="chart-bar"><span style="width:<?= min(100, round(max(0, $item[1]) / $total * 100)) ?>%"></span></div><?php endforeach; ?><p class="muted small mt-4 mb-0">Quick actions: manage the catalog, process returns, or review member accounts.</p>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="surface p-4 h-100">
                        <h2 class="h5 fw-bold mb-3">Quick actions</h2>
                        <div class="d-grid gap-2"><a class="btn btn-outline-primary text-start" href="books.php?action=new"><i class="bi bi-plus-circle me-2"></i>Add a new book</a><a class="btn btn-outline-primary text-start" href="loans.php"><i class="bi bi-arrow-left-right me-2"></i>Issue or return a book</a><?php if ($role === 'admin'): ?><a class="btn btn-outline-primary text-start" href="members.php"><i class="bi bi-people me-2"></i>Manage members</a><a class="btn btn-outline-primary text-start" href="reports.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>View reports</a><?php endif; ?></div>
                    </div>
                </div>
            </div>
        <?php else:
            $stmt = db()->prepare("SELECT i.*,b.title,b.author,b.cover_image,f.amount,f.paid_amount FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN fines f ON f.issue_id=i.id WHERE i.user_id=? AND i.status='issued' ORDER BY i.due_date ASC LIMIT 5");
            $stmt->execute([$user['id']]);
            $loans = $stmt->fetchAll();
            $fq = db()->prepare("SELECT COALESCE(SUM(amount-paid_amount),0) FROM fines f JOIN issues i ON i.id=f.issue_id WHERE i.user_id=?");
            $fq->execute([$user['id']]);
            $pending = (float)$fq->fetchColumn();
        ?>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon mb-3"><i class="bi bi-book"></i></div>
                        <div class="stat-value"><?= count($loans) ?></div>
                        <div class="muted">Books currently borrowed</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon mb-3"><i class="bi bi-cash-coin"></i></div>
                        <div class="stat-value"><?= money($pending) ?></div>
                        <div class="muted">Outstanding fines</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon mb-3"><i class="bi bi-bookmark-check"></i></div>
                        <div class="stat-value"><?= (int)db()->query("SELECT COUNT(*) FROM reservations WHERE user_id=" . (int)$user['id'] . " AND status='pending'")->fetchColumn() ?></div>
                        <div class="muted">Pending reservations</div>
                    </div>
                </div>
            </div>
            <div class="surface p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-bold mb-0">Your current loans</h2><a href="my-loans.php" class="text-decoration-none">View history</a>
                </div><?php if (!$loans): ?><div class="empty-state"><i class="bi bi-book d-block mb-2"></i>
                        <h5>No active loans</h5>
                        <p>Explore the catalog and find something interesting to read.</p><a href="catalog.php" class="btn btn-primary">Browse books</a>
                    </div><?php else: ?><div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Due date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($loans as $l): ?><tr>
                                        <td><strong><?= e($l['title']) ?></strong>
                                            <div class="small muted"><?= e($l['author']) ?></div>
                                        </td>
                                        <td><?= e($l['due_date']) ?></td>
                                        <td><?= $l['due_date'] < date('Y-m-d') ? '<span class="badge-soft badge-unavailable">Overdue</span>' : '<span class="badge-soft badge-available">On time</span>' ?></td>
                                    </tr><?php endforeach; ?></tbody>
                        </table>
                    </div><?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>