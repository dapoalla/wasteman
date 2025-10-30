<?php
$title = "Payment Review";
require_once "header.php";
require_once "db.php";

// Build filter query
$where_conditions = [];
$query_params = [];
$types = "";

if (!empty($_GET['search'])) {
    $search = "%" . $conn->real_escape_string($_GET['search']) . "%";
    $where_conditions[] = "(pc.customer_name LIKE ? OR cc.company_name LIKE ? OR p.amount_paid LIKE ? OR u.username LIKE ?)";
    array_push($query_params, $search, $search, $search, $search);
    $types .= "ssss";
}

if (!empty($_GET['customer_type'])) {
    $where_conditions[] = "p.customer_type = ?";
    $query_params[] = $_GET['customer_type'];
    $types .= "s";
}

if (!empty($_GET['start_date'])) {
    $where_conditions[] = "p.payment_date >= ?";
    $query_params[] = $_GET['start_date'];
    $types .= "s";
}

if (!empty($_GET['end_date'])) {
    $where_conditions[] = "p.payment_date <= ?";
    $query_params[] = $_GET['end_date'];
    $types .= "s";
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build and execute query
$sql = "
    SELECT p.id, p.payment_date, p.amount_paid, p.months_covered, u.username,
           p.customer_type,
           CASE
               WHEN p.customer_type = 'private' THEN pc.customer_name
               WHEN p.customer_type = 'commercial' THEN cc.company_name
           END AS customer_name,
           CASE
               WHEN p.customer_type = 'private' THEN pc.street_address
               WHEN p.customer_type = 'commercial' THEN cc.company_address
           END AS address,
           CASE
               WHEN p.customer_type = 'private' THEN pc.phone_number
               WHEN p.customer_type = 'commercial' THEN ''
           END AS phone
    FROM payments p
    LEFT JOIN private_customers pc ON p.customer_id = pc.id AND p.customer_type = 'private'
    LEFT JOIN commercial_customers cc ON p.customer_id = cc.id AND p.customer_type = 'commercial'
    JOIN users u ON p.entry_by_user_id = u.id
    $where_clause
    ORDER BY p.payment_date DESC
    LIMIT 200
";

$stmt = $conn->prepare($sql);
if (!empty($query_params)) {
    $stmt->bind_param($types, ...$query_params);
}
$stmt->execute();
$payments = $stmt->get_result();

// Get total for filtered results
$total_sql = "SELECT SUM(p.amount_paid) as total FROM payments p $where_clause";
$total_stmt = $conn->prepare($total_sql);
if (!empty($query_params)) {
    $total_stmt->bind_param($types, ...$query_params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_amount = $total_result['total'] ?? 0;
?>

<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold text-white mb-6">Payment Review</h2>

    <!-- Filters -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Filter Payments</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Customer, amount, or user..." class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Customer Type</label>
                <select name="customer_type" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                    <option value="">All Types</option>
                    <option value="private" <?php if (($_GET['customer_type'] ?? '') === 'private') echo 'selected'; ?>>Private</option>
                    <option value="commercial" <?php if (($_GET['customer_type'] ?? '') === 'commercial') echo 'selected'; ?>>Commercial</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
            </div>
            
            <div class="md:col-span-2 lg:col-span-4 flex justify-end gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm px-5 py-2.5">
                    Apply Filters
                </button>
                <a href="payment_review.php" class="bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg text-sm px-5 py-2.5">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="bg-green-900/30 border border-green-700 rounded-xl p-4 mb-6">
        <div class="flex justify-between items-center">
            <span class="text-green-300">Total Filtered Amount:</span>
            <span class="text-2xl font-bold text-green-400">