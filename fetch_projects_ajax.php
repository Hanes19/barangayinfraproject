<?php
session_start();
include 'db.php';

// Role protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lnbpres') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get and sanitize inputs
$page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'all';
$limit = 20; // Number of records per page
$offset = ($page - 1) * $limit;

// Build WHERE clause with prepared statements for safety
$where_conditions = ["1=1"];
$params = [];
$types = "";

if ($search !== '') {
    $where_conditions[] = "(title LIKE ? OR location_barangay LIKE ? OR description LIKE ? OR punong_barangay LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if ($status !== 'all') {
    $where_conditions[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM projects WHERE $where_clause";
$count_stmt = mysqli_prepare($conn, $count_query);

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch data with limit
$query = "SELECT * FROM projects WHERE $where_clause ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Generate HTML for table
ob_start();
?>

<?php if ($result && mysqli_num_rows($result) > 0): ?>
<table class="projects-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Project Title</th>
            <th>Type of Request</th>
            <th>Barangay</th>
            <th>Location Details</th>
            <th>Source of Fund</th>
            <th>Punong Barangay</th>
            <th>Description</th>
            <th>Budget</th>
            <th>Document</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr data-id="<?php echo htmlspecialchars($row['id']); ?>">
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['type_of_request']); ?></td>
            <td><?php echo htmlspecialchars($row['location_barangay']); ?></td>
            <td><?php echo htmlspecialchars($row['location_details']); ?></td>
            <td><?php echo htmlspecialchars($row['source_of_fund']); ?></td>
            <td><?php echo htmlspecialchars($row['punong_barangay']); ?></td>
            <td><?php 
                $desc = htmlspecialchars($row['description']);
                echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
            ?></td>
            <td>₱<?php echo number_format((float)$row['budget'], 2); ?></td>
            <td>
                <?php if (!empty($row['application_file'])): ?>
                    <a class="btn view" href="uploads/docs/<?php echo urlencode($row['application_file']); ?>" target="_blank">View File</a>
                <?php else: ?>
                    No File
                <?php endif; ?>
            </td>
            <td>
                <span class="small-text">
                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                </span>
            </td>
            <td>
                <button class="btn approve" onclick="updateStatus(<?php echo $row['id']; ?>, 'approve')">Approve</button>
                <button class="btn reject" onclick="updateStatus(<?php echo $row['id']; ?>, 'reject')">Reject</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<div class="no-results">
    <p>📭 No project records found.</p>
</div>
<?php endif; ?>

<?php
$table_html = ob_get_clean();

// Generate pagination HTML
ob_start();
if ($total_pages > 1):
?>
<div class="pagination">
    <button onclick="goToPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
        &laquo; Previous
    </button>
    <div class="page-numbers">
        <?php
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
            echo '<button class="page-number" onclick="goToPage(1)">1</button>';
            if ($start_page > 2) echo '<span class="ellipsis">...</span>';
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = ($i == $page) ? 'active' : '';
            echo "<button class='page-number $active_class' onclick='goToPage($i)'>$i</button>";
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) echo '<span class="ellipsis">...</span>';
            echo "<button class='page-number' onclick='goToPage($total_pages)'>$total_pages</button>";
        }
        ?>
    </div>
    <button onclick="goToPage(<?php echo $page + 1; ?>)" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>
        Next &raquo;
    </button>
</div>
<?php
endif;
$pagination_html = ob_get_clean();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'html' => $table_html,
    'pagination' => $pagination_html,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_records' => $total_rows
]);
?>