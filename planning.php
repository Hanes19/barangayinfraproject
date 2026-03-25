<?php
session_start();
include 'db.php';

// Check if user is CPDC (you can add your own check here)

// Fetch latest projects
$recent_projects_query = "SELECT * FROM projects ORDER BY created_at DESC LIMIT 10";
$recent_projects_result = mysqli_query($conn, $recent_projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planning Dashboard — CPDC</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  :root {
    --g900: #052e16;
    --g800: #14532d;
    --g700: #166534;
    --g600: #15803d;
    --g500: #16a34a;
    --g400: #22c55e;
    --g300: #4ade80;
    --g100: #dcfce7;
    --g50:  #f0fdf4;
    --white: #ffffff;
    --gray50:  #f8fafc;
    --gray100: #f1f5f9;
    --gray200: #e2e8f0;
    --gray400: #94a3b8;
    --gray600: #475569;
    --gray800: #1e293b;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.07), 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.05);
    --shadow-lg: 0 12px 40px rgba(0,0,0,0.1), 0 4px 12px rgba(0,0,0,0.06);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 18px;
    --font-display: 'Fraunces', Georgia, serif;
    --font-body:    'Plus Jakarta Sans', sans-serif;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    min-height: 100vh;
    font-family: var(--font-body);
    background: var(--gray50);
    color: var(--gray800);
  }

  /* Left accent stripe */
  body::before {
    content: '';
    position: fixed;
    top: 0; left: 0;
    width: 4px; height: 100vh;
    background: linear-gradient(180deg, var(--g400) 0%, var(--g700) 100%);
    z-index: 100;
  }

  /* ── Top Nav ── */
  .topbar {
    position: sticky; top: 0; z-index: 50;
    background: var(--white);
    border-bottom: 1px solid var(--gray200);
    box-shadow: var(--shadow-sm);
    height: 64px;
    padding: 0 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: slideDown 0.5s ease both;
  }
  .topbar-brand {
    display: flex; align-items: center; gap: 12px;
  }
  .brand-icon {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, var(--g500) 0%, var(--g800) 100%);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 16px;
    box-shadow: 0 2px 10px rgba(22,163,74,0.3);
  }
  .brand-name {
    font-family: var(--font-display);
    font-size: 17px;
    font-weight: 700;
    color: var(--g900);
    letter-spacing: -0.01em;
  }
  .brand-name span { color: var(--g500); }
  .topbar-right { display: flex; align-items: center; gap: 10px; }
  .nav-chip {
    display: flex; align-items: center; gap: 7px;
    background: var(--g50);
    border: 1px solid var(--g100);
    color: var(--g700);
    font-size: 12px; font-weight: 600;
    letter-spacing: 0.05em; text-transform: uppercase;
    padding: 6px 14px; border-radius: 100px;
  }
  .nav-chip i { font-size: 11px; color: var(--g500); }
  .live-dot {
    width: 7px; height: 7px;
    background: var(--g400); border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(34,197,94,0.2);
    animation: blink 2s infinite;
  }

  /* ── Page wrap ── */
  .page-wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 44px 40px 80px;
  }

  /* ── Page header ── */
  .page-header {
    display: flex; align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 30px; gap: 16px; flex-wrap: wrap;
    animation: fadeUp 0.5s 0.1s ease both;
  }
  .eyebrow {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--g500); margin-bottom: 10px;
  }
  .eyebrow-line {
    width: 22px; height: 2px;
    background: var(--g400); border-radius: 2px;
  }
  .page-title {
    font-family: var(--font-display);
    font-size: clamp(1.75rem, 3.5vw, 2.6rem);
    font-weight: 700;
    color: var(--g900);
    line-height: 1.1;
    letter-spacing: -0.02em;
  }
  .page-title em { font-style: italic; color: var(--g600); }
  .page-sub {
    margin-top: 8px;
    font-size: 14px; color: var(--gray400); font-weight: 400;
  }

  /* ── Stats ── */
  .stats-row {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-bottom: 28px;
    animation: fadeUp 0.5s 0.18s ease both;
  }
  .stat-card {
    flex: 1; min-width: 120px;
    background: var(--white);
    border: 1px solid var(--gray200);
    border-radius: var(--radius-md);
    padding: 18px 22px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s, transform 0.2s;
  }
  .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
  .stat-label {
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--gray400); margin-bottom: 6px;
  }
  .stat-value {
    font-family: var(--font-display);
    font-size: 2rem; font-weight: 700; line-height: 1;
    color: var(--gray800);
  }
  .stat-card.s-green .stat-value { color: var(--g600); }
  .stat-card.s-amber .stat-value { color: #b45309; }
  .stat-card.s-red   .stat-value { color: #dc2626; }
  .stat-indicator {
    display: inline-block;
    width: 8px; height: 8px; border-radius: 50%;
    margin-right: 4px; vertical-align: middle;
  }
  .s-green .stat-indicator { background: var(--g400); }
  .s-amber .stat-indicator { background: #f59e0b; }
  .s-red   .stat-indicator { background: #ef4444; }

  /* ── Main Card ── */
  .main-card {
    background: var(--white);
    border: 1px solid var(--gray200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    animation: fadeUp 0.5s 0.26s ease both;
  }
  .card-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px;
    border-bottom: 1px solid var(--gray100);
    flex-wrap: wrap; gap: 12px;
  }
  .card-topbar-left { display: flex; align-items: center; gap: 12px; }
  .card-icon {
    width: 36px; height: 36px;
    background: var(--g50);
    border: 1px solid var(--g100);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    color: var(--g600); font-size: 14px;
  }
  .card-title { font-size: 15px; font-weight: 600; color: var(--gray800); }
  .card-sub   { font-size: 12px; color: var(--gray400); margin-top: 1px; }
  .count-pill {
    background: var(--g50); border: 1px solid var(--g100);
    color: var(--g700); font-size: 12px; font-weight: 700;
    padding: 4px 14px; border-radius: 100px;
  }

  /* ── Table ── */
  .proj-table { width: 100%; border-collapse: collapse; }
  .proj-table thead tr { background: var(--gray50); }
  .proj-table thead th {
    padding: 13px 20px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.13em; text-transform: uppercase;
    color: var(--gray400); text-align: left;
    border-bottom: 1px solid var(--gray200);
    white-space: nowrap;
  }
  .proj-table thead th:first-child { padding-left: 28px; }
  .proj-table thead th:last-child  { padding-right: 28px; }
  .proj-table tbody tr {
    border-bottom: 1px solid var(--gray100);
    transition: background 0.15s;
  }
  .proj-table tbody tr:last-child { border-bottom: none; }
  .proj-table tbody tr:hover { background: var(--g50); }
  .proj-table td {
    padding: 16px 20px;
    vertical-align: middle;
    font-size: 14px; color: var(--gray600);
  }
  .proj-table td:first-child { padding-left: 28px; }
  .proj-table td:last-child  { padding-right: 28px; }

  /* ID */
  .id-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 34px; height: 34px; padding: 0 8px;
    border-radius: 8px;
    background: var(--g50); border: 1px solid var(--g100);
    color: var(--g700); font-size: 12px; font-weight: 700;
  }

  /* Proj name */
  .proj-name { font-weight: 600; color: var(--gray800); }

  /* Status */
  .status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 11px; border-radius: 100px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.05em; text-transform: capitalize;
  }
  .s-dot {
    width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
  }
  .status-badge.approved { background: var(--g50); color: var(--g700); border: 1px solid var(--g100); }
  .status-badge.approved .s-dot { background: var(--g500); }
  .status-badge.rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
  .status-badge.rejected .s-dot { background: #ef4444; }
  .status-badge.pending  { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
  .status-badge.pending  .s-dot { background: #f59e0b; animation: blink 1.8s infinite; }

  /* View file */
  .btn-view-file {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 13px; border-radius: var(--radius-sm);
    font-size: 12px; font-weight: 600;
    text-decoration: none;
    background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
    transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
    white-space: nowrap;
  }
  .btn-view-file:hover {
    background: #dbeafe; color: #1e40af;
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(29,78,216,0.12);
  }
  .no-file { font-size: 12px; color: var(--gray400); display: flex; align-items: center; gap: 6px; }

  /* Action form */
  .action-form { display: flex; flex-direction: column; gap: 9px; }
  .upload-input {
    width: 100%; padding: 7px 10px;
    font-size: 12px; font-family: var(--font-body);
    background: var(--gray50);
    border: 1px dashed var(--gray200);
    border-radius: var(--radius-sm);
    color: var(--gray600); cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
  }
  .upload-input:hover { border-color: var(--g400); background: var(--g50); }
  .upload-input::file-selector-button {
    background: var(--g50); border: 1px solid var(--g100);
    color: var(--g700); font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 5px;
    cursor: pointer; margin-right: 8px;
    font-family: var(--font-body);
  }

  .action-btns { display: flex; gap: 8px; flex-wrap: wrap; }
  .btn-action {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 15px; border-radius: var(--radius-sm);
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.03em;
    border: none; cursor: pointer;
    font-family: var(--font-body);
    transition: transform 0.15s, box-shadow 0.15s;
    white-space: nowrap;
  }
  .btn-action:hover  { transform: translateY(-1px); }
  .btn-action:active { transform: scale(0.97); }
  .btn-approve {
    background: linear-gradient(135deg, var(--g500) 0%, var(--g700) 100%);
    color: white;
    box-shadow: 0 3px 12px rgba(22,163,74,0.28);
  }
  .btn-approve:hover { box-shadow: 0 5px 18px rgba(22,163,74,0.38); }
  .btn-reject {
    background: white; color: #dc2626;
    border: 1.5px solid #fecaca;
    box-shadow: var(--shadow-sm);
  }
  .btn-reject:hover { background: #fef2f2; }

  /* Empty */
  .empty-state { padding: 72px 20px; text-align: center; }
  .empty-icon {
    width: 62px; height: 62px;
    background: var(--g50); border: 1px solid var(--g100);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px; font-size: 22px; color: var(--g400);
  }
  .empty-state p { font-size: 14px; color: var(--gray400); font-weight: 500; }

  /* Footer */
  .page-footer {
    margin-top: 32px; text-align: center;
    font-size: 11px; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--gray400);
    display: flex; align-items: center; justify-content: center; gap: 12px;
    animation: fadeUp 0.5s 0.4s ease both;
  }
  .page-footer::before, .page-footer::after {
    content: ''; width: 40px; height: 1px;
    background: var(--gray200); display: inline-block;
  }

  /* Animations */
  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-12px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes blink {
    0%,100% { opacity: 1; }
    50%      { opacity: 0.3; }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .page-wrap { padding: 28px 20px 60px; }
    .topbar { padding: 0 20px; }
    .stats-row { gap: 8px; }
    .proj-table thead { display: none; }
    .proj-table tbody tr { display: block; padding: 16px 20px; border-bottom: 1px solid var(--gray100); }
    .proj-table td { display: flex; align-items: flex-start; flex-wrap: wrap; gap: 8px; padding: 6px 0; }
    .proj-table td::before {
      content: attr(data-label);
      font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
      text-transform: uppercase; color: var(--gray400);
      min-width: 80px; padding-top: 2px;
    }
    .proj-table td:first-child, .proj-table td:last-child { padding-left: 0; padding-right: 0; }
  }
</style>
</head>
<body>

<!-- Top nav -->
<nav class="topbar">
  <div class="topbar-brand">
    <div class="brand-icon"><i class="fas fa-city"></i></div>
    <div class="brand-name">CPDC <span>Portal</span></div>
  </div>
  <div class="topbar-right">
    <div class="nav-chip"><div class="live-dot"></div> Administrator</div>
    <div class="nav-chip"><i class="fas fa-calendar-days"></i> <?php echo date('M d, Y'); ?></div>
  </div>
</nav>

<div class="page-wrap">

  <!-- Header -->
  <div class="page-header">
    <div>
      <div class="eyebrow"><div class="eyebrow-line"></div> Project Management</div>
      <h1 class="page-title">Recent <em>Submissions</em></h1>
      <p class="page-sub">Review, approve, or reject the latest project applications.</p>
    </div>
  </div>

  <!-- Stats -->
  <?php
    $all_rows = []; $total = $approved = $rejected = $pending = 0;
    if ($recent_projects_result && mysqli_num_rows($recent_projects_result) > 0) {
      while ($r = mysqli_fetch_assoc($recent_projects_result)) {
        $all_rows[] = $r; $total++;
        $s = strtolower($r['status']);
        if ($s === 'approved') $approved++;
        elseif ($s === 'rejected') $rejected++;
        else $pending++;
      }
    }
  ?>
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total</div>
      <div class="stat-value"><?php echo $total; ?></div>
    </div>
    <div class="stat-card s-green">
      <div class="stat-label"><span class="stat-indicator"></span>Approved</div>
      <div class="stat-value"><?php echo $approved; ?></div>
    </div>
    <div class="stat-card s-amber">
      <div class="stat-label"><span class="stat-indicator"></span>Pending</div>
      <div class="stat-value"><?php echo $pending; ?></div>
    </div>
    <div class="stat-card s-red">
      <div class="stat-label"><span class="stat-indicator"></span>Rejected</div>
      <div class="stat-value"><?php echo $rejected; ?></div>
    </div>
  </div>

  <!-- Table card -->
  <div class="main-card">
    <div class="card-topbar">
      <div class="card-topbar-left">
        <div class="card-icon"><i class="fas fa-folder-open"></i></div>
        <div>
          <div class="card-title">Project Applications</div>
          <div class="card-sub">CPDC Administrator View</div>
        </div>
      </div>
      <div class="count-pill"><?php echo $total . ' record' . ($total !== 1 ? 's' : ''); ?></div>
    </div>

    <div style="overflow-x:auto;">
      <table class="proj-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Project Name</th>
            <th>Document</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($all_rows)): ?>
            <?php foreach ($all_rows as $project): ?>
              <?php
                $doc_path   = !empty($project['application_file']) ? "uploads/docs/" . htmlspecialchars($project['application_file']) : "";
                $status_raw = strtolower(htmlspecialchars($project['status']));
                $status_lbl = htmlspecialchars(ucfirst($project['status']));
              ?>
              <tr>
                <td data-label="ID"><span class="id-badge"><?php echo $project['id']; ?></span></td>
                <td data-label="Project"><span class="proj-name"><?php echo htmlspecialchars($project['title']); ?></span></td>
                <td data-label="Document">
                  <?php if ($doc_path): ?>
                    <a href="<?php echo $doc_path; ?>" target="_blank" class="btn-view-file">
                      <i class="fas fa-file-pdf"></i> View File
                    </a>
                  <?php else: ?>
                    <span class="no-file"><i class="fas fa-minus-circle"></i> No File</span>
                  <?php endif; ?>
                </td>
                <td data-label="Status">
                  <span class="status-badge <?php echo $status_raw; ?>">
                    <span class="s-dot"></span><?php echo $status_lbl; ?>
                  </span>
                </td>
                <td data-label="Actions">
                  <form action="update_status.php" method="POST" enctype="multipart/form-data" class="action-form">
                    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                    <input type="file" name="signed_document" class="upload-input"
                           accept=".pdf,.doc,.docx,.jpg,.png" title="Upload signed document">
                    <div class="action-btns">
                      <button type="submit" name="status" value="approved" class="btn-action btn-approve">
                        <i class="fas fa-check"></i> Approve
                      </button>
                      <button type="submit" name="status" value="rejected" class="btn-action btn-reject"
                              onclick="return confirm('Are you sure you want to reject this project?');">
                        <i class="fas fa-times"></i> Reject
                      </button>
                    </div>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                  <p>No projects found at this time.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="page-footer">City Planning &amp; Development Council &nbsp;·&nbsp; Secure Administrator Portal</div>

</div>
</body>
</html>
