<?php 
session_start(); 
include 'db.php';  

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lnbpres') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get counts
$count_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM projects";
$count_result = mysqli_query($conn, $count_query);
$counts = mysqli_fetch_assoc($count_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LNB President - Projects with Filtering</title>
    <style>
        /* Copy all the styles from the previous code */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f7f0; padding: 20px; }
        .dashboard-container { max-width: 1400px; margin: 0 auto; }
        h2 { text-align: center; margin-bottom: 20px; color: #2e7d32; font-size: 28px; }
        .logout-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #dc3545; color: #fff; text-decoration: none; border-radius: 8px; }
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 15px; padding: 20px; text-align: center; cursor: pointer; transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .card .number { font-size: 36px; font-weight: bold; }
        .card.total .number { color: #1976d2; }
        .card.pending .number { color: #ff9800; }
        .card.approved .number { color: #4caf50; }
        .card.rejected .number { color: #f44336; }
        .filter-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .search-box input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; }
        .filter-buttons { display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .filter-btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; background: #f5f5f5; color: #666; font-weight: bold; }
        .filter-btn.active { background: #4caf50; color: white; }
        .table-container { overflow-x: auto; background: white; border-radius: 10px; padding: 10px; min-height: 400px; }
        table { width: 100%; border-collapse: collapse; min-width: 1200px; }
        th, td { border: 1px solid #c8e6c9; padding: 12px; text-align: center; }
        th { background: #4caf50; color: white; }
        .btn { display: inline-block; padding: 7px 12px; border-radius: 5px; color: #fff; font-size: 13px; cursor: pointer; border: none; }
        .approve { background: #2e7d32; }
        .reject { background: #c62828; }
        .view { background: #66bb6a; text-decoration: none; }
        .status-approved { color: #2e7d32; font-weight: bold; background: #e8f5e9; padding: 4px 8px; border-radius: 4px; }
        .status-rejected { color: #c62828; font-weight: bold; background: #ffebee; padding: 4px 8px; border-radius: 4px; }
        .status-pending { color: #ff8f00; font-weight: bold; background: #fff3e0; padding: 4px 8px; border-radius: 4px; }
        .loading { text-align: center; padding: 60px; font-size: 18px; color: #666; }
        .no-results { text-align: center; padding: 60px; font-size: 18px; color: #999; }
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .pagination button { padding: 8px 15px; background: #4caf50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .page-number { padding: 8px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; }
        .page-number.active { background: #4caf50; color: white; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>🏛️ LNB President - Barangay Project Portal</h2>
    
    <!-- Summary Cards - Click to Filter -->
    <div class="summary-cards">
        <div class="card total" data-filter="all">
            <h3>Total Projects</h3>
            <div class="number"><?php echo $counts['total']; ?></div>
        </div>
        <div class="card pending" data-filter="pending">
            <h3>Pending</h3>
            <div class="number"><?php echo $counts['pending']; ?></div>
        </div>
        <div class="card approved" data-filter="approved">
            <h3>Approved</h3>
            <div class="number"><?php echo $counts['approved']; ?></div>
        </div>
        <div class="card rejected" data-filter="rejected">
            <h3>Rejected</h3>
            <div class="number"><?php echo $counts['rejected']; ?></div>
        </div>
    </div>
    
    <!-- Search and Filter Section -->
    <div class="filter-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Search by project title, barangay, or description..." autocomplete="off">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">📊 All Requests</button>
            <button class="filter-btn" data-filter="pending">⏳ Pending Only</button>
            <button class="filter-btn" data-filter="approved">✅ Approved Only</button>
            <button class="filter-btn" data-filter="rejected">❌ Rejected Only</button>
        </div>
    </div>
    
    <!-- Table Container -->
    <div class="table-container">
        <div id="loadingIndicator" class="loading">
            Loading projects...
        </div>
        <div id="tableContent" style="display: none;"></div>
        <div id="paginationContainer" style="display: none;"></div>
    </div>
</div>

<a class="logout-btn" href="?logout=1" onclick="return confirm('Logout?')">🚪 Logout</a>

<script>
let currentPage = 1;
let currentSearch = '';
let currentFilter = 'all';
let isLoading = false;

function fetchProjects(page = 1, search = '', status = 'all') {
    if (isLoading) return;
    
    isLoading = true;
    currentPage = page;
    currentSearch = search;
    currentFilter = status;
    
    const loadingIndicator = document.getElementById('loadingIndicator');
    const tableContent = document.getElementById('tableContent');
    const paginationContainer = document.getElementById('paginationContainer');
    
    loadingIndicator.style.display = 'block';
    tableContent.style.display = 'none';
    paginationContainer.style.display = 'none';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_projects_ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            loadingIndicator.style.display = 'none';
            tableContent.innerHTML = response.html;
            tableContent.style.display = 'block';
            
            if (response.pagination) {
                paginationContainer.innerHTML = response.pagination;
                paginationContainer.style.display = 'flex';
            }
            
            isLoading = false;
        }
    };
    
    xhr.send('page=' + page + '&search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status));
}

function updateCounts() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_counts_ajax.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const counts = JSON.parse(xhr.responseText);
            document.querySelector('.card.total .number').textContent = counts.total;
            document.querySelector('.card.pending .number').textContent = counts.pending;
            document.querySelector('.card.approved .number').textContent = counts.approved;
            document.querySelector('.card.rejected .number').textContent = counts.rejected;
        }
    };
    xhr.send();
}

function updateStatus(projectId, action) {
    if (!confirm(`Are you sure you want to ${action} this project?`)) return;
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `update_status_ajax.php?id=${projectId}&action=${action}`, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                fetchProjects(currentPage, currentSearch, currentFilter);
                updateCounts();
            }
        }
    };
    xhr.send();
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    fetchProjects(1, '', 'all');
    
    const searchInput = document.getElementById('searchInput');
    const debouncedSearch = debounce(function() {
        fetchProjects(1, this.value, currentFilter);
    }, 500);
    searchInput.addEventListener('keyup', debouncedSearch);
    
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            fetchProjects(1, currentSearch, this.getAttribute('data-filter'));
        });
    });
    
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            const targetBtn = Array.from(filterBtns).find(btn => btn.getAttribute('data-filter') === filter);
            if (targetBtn) targetBtn.click();
        });
    });
});

function goToPage(page) {
    fetchProjects(page, currentSearch, currentFilter);
}
</script>

</body>
</html>