<?php
include 'db.php';

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: auto;'>";
echo "<h2 style='color: #14532d;'>Database Seeder for Barangay Projects (Expanded Dataset)</h2>";
echo "<p>Injecting 26 realistic projects into the tracking pipeline...</p>";

$sample_projects = [
    // ==========================================
    // 1. CPDC REVIEW (Status: pending, CEO: pending)
    // ==========================================
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Daycare Center Fencing', 'City inspection', 'Poblacion', 'Purok 5', 'Barangay fund', 'Juan Dela Cruz', 120000.00, 'Fence construction for kids safety.', 'pending', 'pending', NOW())",
     
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Barangay Patrol Vehicle Request', 'City inspection', 'Lumbo', 'Barangay Hall', 'City aid', 'Maria Santos', 1500000.00, 'Requesting a new multi-cab for nightly patrols.', 'pending', 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY))",
     
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Public Market Water Line', 'Program of works', 'Batangan', 'Market Area', 'Others', 'Pedro Penduko', 350000.00, 'Installing new water pipes for the wet market section.', 'pending', 'pending', DATE_SUB(NOW(), INTERVAL 4 DAY))",

    // ==========================================
    // 2. CEO REVIEW (Status: pending, CEO: transmitted)
    // ==========================================
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Health Center Expansion', 'Program of works', 'Mailag', 'Zone 3', 'City aid', 'Jose Rizal', 850000.00, 'Adding a new maternity ward for the local health center.', 'pending', 'transmitted', DATE_SUB(NOW(), INTERVAL 8 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Purok 2 Pathway Concreting', 'Program of works', 'Bagontaas', 'Purok 2', 'Barangay fund', 'Ana Reyes', 250000.00, 'Concreting of the muddy pathway leading to the elementary school.', 'pending', 'transmitted', DATE_SUB(NOW(), INTERVAL 12 DAY))",

    // ==========================================
    // 3. ONGOING IMPLEMENTATION (Status: ongoing, CEO: approved)
    // ==========================================
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Covered Court Repainting', 'Program of works', 'Bagontaas', 'Plaza', 'Barangay fund', 'Ana Reyes', 85000.00, 'Repainting the basketball court and replacing the hoops.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 20 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Farm to Market Road Concreting', 'Program of works', 'Batangan', 'Sitio 1', 'City aid', 'Pedro Penduko', 2500000.00, 'Concreting of 500m rough road to help local farmers.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 60 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Drainage System Upgrade Phase 1', 'Program of works', 'Poblacion', 'Main Street', 'City aid', 'Juan Dela Cruz', 1200000.00, 'Upgrading main drainage to prevent floods.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 190 DAY))", // Will trigger High Priority AI Flag

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Barangay Outpost Construction', 'City inspection', 'Mailag', 'Highway Crossing', 'Barangay fund', 'Jose Rizal', 300000.00, 'Building a permanent outpost for Tanods.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 150 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Solar Water Pump Installation', 'Program of works', 'Lumbo', 'Sitio 5', 'Others', 'Maria Santos', 450000.00, 'Installing solar pumps for agricultural irrigation.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 45 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at) 
     VALUES ('Plaza Landscaping', 'City inspection', 'Poblacion', 'Central Plaza', 'Barangay fund', 'Juan Dela Cruz', 150000.00, 'Beautification and planting of trees in the main plaza.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 35 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, monitoring_status) 
     VALUES ('Evacuation Center Roofing', 'Program of works', 'Bagontaas', 'Zone 4', 'City aid', 'Ana Reyes', 900000.00, 'Replacing the damaged roof of the main evacuation center.', 'ongoing', 'approved', DATE_SUB(NOW(), INTERVAL 80 DAY), 'inspection_requested')",

    // ==========================================
    // 4. COMPLETED PROJECTS (Status: completed, CEO: approved)
    // ==========================================
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Street Lighting Installation', 'City inspection', 'Lumbo', 'Purok 4', 'Barangay fund', 'Maria Santos', 150000.00, 'Install solar street lights along the main highway.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY))", // Triggers Low Priority "Kick-off Review"

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Barangay Hall Renovation', 'Program of works', 'Poblacion', 'Zone 1', 'City aid', 'Juan Dela Cruz', 500000.00, 'Renovation of the main hall roof and repainting of walls.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 120 DAY), DATE_SUB(NOW(), INTERVAL 95 DAY))", // Triggers Medium Priority

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Creek Dredging Project', 'City inspection', 'Mailag', 'Boundary Creek', 'Others', 'Jose Rizal', 250000.00, 'Clearing debris and dredging the creek to prevent overflow.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 210 DAY), DATE_SUB(NOW(), INTERVAL 150 DAY))", // Triggers Medium/High Maintenance

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('School Pedestrian Lane', 'Program of works', 'Batangan', 'Elem. School Gate', 'Barangay fund', 'Pedro Penduko', 40000.00, 'Painting of pedestrian lanes and installation of warning signs.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Garbage Segregation Bins', 'City inspection', 'Bagontaas', 'All Puroks', 'Barangay fund', 'Ana Reyes', 120000.00, 'Distribution of color-coded trash bins to every purok.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 300 DAY), DATE_SUB(NOW(), INTERVAL 280 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Deep Well Construction', 'Program of works', 'Lumbo', 'Sitio 2', 'City aid', 'Maria Santos', 600000.00, 'Constructing a new deep well for potable water supply.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 180 DAY), DATE_SUB(NOW(), INTERVAL 100 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Basketball Court Roofing', 'Program of works', 'Poblacion', 'Purok 3', 'Others', 'Juan Dela Cruz', 1800000.00, 'Putting a roof over the open basketball court.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 150 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('CCTV Installation Phase 1', 'City inspection', 'Mailag', 'Major Intersections', 'Barangay fund', 'Jose Rizal', 350000.00, 'Installing HD CCTV cameras at 5 major intersections.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 110 DAY), DATE_SUB(NOW(), INTERVAL 80 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Vegetable Nursery Hub', 'Program of works', 'Batangan', 'Zone 5', 'City aid', 'Pedro Penduko', 180000.00, 'Building a greenhouse nursery for local farmers.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 250 DAY), DATE_SUB(NOW(), INTERVAL 200 DAY))",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, completed_at) 
     VALUES ('Senior Citizen Center Rehab', 'Program of works', 'Bagontaas', 'Beside Brgy Hall', 'Others', 'Ana Reyes', 450000.00, 'Rehabilitation of the Senior Citizen building and adding ramps.', 'completed', 'approved', DATE_SUB(NOW(), INTERVAL 95 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY))",

    // ==========================================
    // 5. DECLINED / REJECTED PROJECTS
    // ==========================================
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, remarks) 
     VALUES ('Waiting Shed Construction', 'Program of works', 'Lumbo', 'Highway', 'Others', 'Maria Santos', 45000.00, 'New waiting shed for commuters.', 'declined', 'declined', DATE_SUB(NOW(), INTERVAL 15 DAY), 'Budget exceeds the allowable limit for this type of structure. Please revise.')",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, remarks) 
     VALUES ('Luxury Office Chairs for Officials', 'City inspection', 'Poblacion', 'Barangay Hall', 'Barangay fund', 'Juan Dela Cruz', 250000.00, 'Purchasing high-end executive chairs.', 'rejected', 'declined', DATE_SUB(NOW(), INTERVAL 40 DAY), 'Denied. Not a valid infrastructure project. Re-allocate funds to community services.')",

    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, remarks) 
     VALUES ('Private Driveway Paving', 'Program of works', 'Mailag', 'Purok 1', 'City aid', 'Jose Rizal', 150000.00, 'Paving the driveway leading to the subdivision.', 'declined', 'declined', DATE_SUB(NOW(), INTERVAL 70 DAY), 'Cannot use public City Aid funds for a private subdivision driveway.')",
     
    "INSERT INTO projects (title, type_of_request, location_barangay, location_details, source_of_fund, punong_barangay, budget, description, status, ceo_status, created_at, remarks) 
     VALUES ('Welcome Arch Reconstruction', 'Program of works', 'Batangan', 'Highway Boundary', 'Barangay fund', 'Pedro Penduko', 800000.00, 'Rebuilding the boundary arch with concrete and steel.', 'declined', 'pending', DATE_SUB(NOW(), INTERVAL 5 DAY), 'CPDC Review: Requires clearance from DPWH before we can proceed. Hold for now.')"
];

$success_count = 0;
$error_count = 0;

foreach ($sample_projects as $sql) {
    if (mysqli_query($conn, $sql)) {
        $success_count++;
    } else {
        echo "<div style='background: #fee2e2; border-left: 4px solid #ef4444; padding: 10px; margin-bottom: 10px;'>";
        echo "<strong>Error:</strong> " . mysqli_error($conn) . "<br>";
        echo "<code style='font-size: 12px;'>" . htmlspecialchars($sql) . "</code>";
        echo "</div>";
        $error_count++;
    }
}

echo "<div style='background: #dcfce7; border: 1px solid #22c55e; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
echo "<h3 style='margin-top: 0; color: #166534;'><i class='fas fa-check-circle'></i> Seeding Complete!</h3>";
echo "<p style='font-size: 18px;'>Successfully inserted <strong>$success_count</strong> sample projects.</p>";
if ($error_count > 0) {
    echo "<p style='color: #ef4444;'>Failed to insert <strong>$error_count</strong> projects.</p>";
}
echo "</div>";

echo "<div style='margin-top: 30px;'>";
echo "<a href='admin_dashboard.php' style='padding: 12px 24px; background: #14532d; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>Go to Dashboard Analytics ➔</a>";
echo "</div>";
echo "</div>";
?>