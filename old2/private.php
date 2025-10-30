<?php
$title = "Private Customers (ITECSOL)";
require_once "header.php";
require_once "db.php";

// Redirect if not ITECSOL user
if ($_SESSION['subsidiary'] !== 'ITECSOL') {
    header("location: dashboard.php");
    exit;
}

$message = '';
$message_type = ''; // 'success' or 'error'

// Handle POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['customer_id'])) {
    $customer_id = intval($_POST['customer_id']);
    $action = $_POST['action'] ?? '';
    
    // --- Admin Actions ---
    if ($_SESSION['role'] == 'admin') {
        if ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM private_customers WHERE id = ?");
            $stmt->bind_param("i", $customer_id);
            if ($stmt->execute()) {
                $message = "Customer record deleted."; $message_type = 'success';
            }
        } elseif ($action == 'toggle_high_value') {
            $stmt = $conn->prepare("UPDATE private_customers SET is_high_value = NOT is_high_value WHERE id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        }
    }

    // --- Bill Status Action ---
    if (isset($_POST['status'])) {
        $status = $_POST['status'];
        if ($status == 'paid') {
            $sql = "UPDATE private_customers SET outstanding_balance = total_due, current_due = 0, bill_status = 'paid' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            $sql = "UPDATE private_customers SET bill_status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $customer_id);
        }
        if ($stmt->execute()) {
            $message = "Bill status updated."; $message_type = 'success';
        }
    }
}


// Fetch customer data
$result = $conn->query("SELECT * FROM private_customers ORDER BY id DESC");

function getStatusBadge($status) {
    switch ($status) {
        case 'sent': return '<span class="px-2 py-1 text-xs font-medium text-blue-300 bg-blue-900/50 rounded-full">Sent</span>';
        case 'paid': return '<span class="px-2 py-1 text-xs font-medium text-green-300 bg-green-900/50 rounded-full">Paid</span>';
        default: return '<span class="px-2 py-1 text-xs font-medium text-yellow-300 bg-yellow-900/50 rounded-full">Pending</span>';
    }
}
?>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-white">Private Customers</h2>
        <div>
            <a href="import.php" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded-lg flex items-center_ inline-flex items-center"><span class="material-symbols-outlined mr-2">upload</span> Import</a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="add_edit_customer.php?type=private" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg ml-4 inline-flex items-center"><span class="material-symbols-outlined mr-2">add</span> Add Customer</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="mb-4 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3">Customer Name</th>
                        <th class="px-6 py-3">Address</th>
                        <th class="px-6 py-3">Outstanding</th>
                        <th class="px-6 py-3">Total Due</th>
                        <th class="px-6 py-3">Bill Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <tr class="border-b border-gray-700 hover:bg-gray-700/40">
                        <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                            <a href="view_customer.php?type=private&id=<?php echo $row['id']; ?>" class="hover:underline <?php echo $theme_color_class; ?>">
                                <?php echo htmlspecialchars($row['customer_name']); ?>
                            </a>
                            <?php if ($row['is_high_value']): ?>
                                <span class="material-symbols-outlined text-amber-400 text-base" style="font-size: 16px;" title="High Value Client">workspace_premium</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['house_flat_number'] . ' ' . $row['street_address']); ?></td>
                        <td class="px-6 py-4">₦<?php echo number_format($row['outstanding_balance'], 2); ?></td>
                        <td class="px-6 py-4 font-bold text-cyan-400">₦<?php echo number_format($row['total_due'], 2); ?></td>
                        <td class="px-6 py-4"><?php echo getStatusBadge($row['bill_status']); ?></td>
                        <td class="px-6 py-4 flex items-center gap-1">
                            <form method="POST" class="inline"><input type="hidden" name="customer_id" value="<?php echo $row['id']; ?>"><input type="hidden" name="status" value="sent"><button type="submit" title="Mark as Sent" class="p-2 text-blue-400 hover:text-white hover:bg-blue-500/30 rounded-full"><span class="material-symbols-outlined">send</span></button></form>
                            <form method="POST" class="inline"><input type="hidden" name="customer_id" value="<?php echo $row['id']; ?>"><input type="hidden" name="status" value="paid"><button type="submit" title="Mark as Paid" class="p-2 text-green-400 hover:text-white hover:bg-green-500/30 rounded-full"><span class="material-symbols-outlined">price_check</span></button></form>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <a href="add_edit_customer.php?type=private&id=<?php echo $row['id']; ?>" title="Edit" class="p-2 text-gray-400 hover:text-white hover:bg-gray-500/30 rounded-full"><span class="material-symbols-outlined">edit</span></a>
                                <form method="POST" class="inline"><input type="hidden" name="customer_id" value="<?php echo $row['id']; ?>"><input type="hidden" name="action" value="toggle_high_value"><button type="submit" title="Toggle High Value" class="p-2 text-amber-400 hover:text-white hover:bg-amber-500/30 rounded-full"><span class="material-symbols-outlined">workspace_premium</span></button></form>
                                <form method="POST" onsubmit="return confirm('Delete this record?');" class="inline"><input type="hidden" name="customer_id" value="<?php echo $row['id']; ?>"><input type="hidden" name="action" value="delete"><button type="submit" title="Delete" class="p-2 text-red-400 hover:text-white hover:bg-red-500/30 rounded-full"><span class="material-symbols-outlined">delete</span></button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center py-8">No private customer data found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

// ... existing private.php code ...

// Fetch dropdown options
function getDropdownOptions($conn, $category) {
    $options = [];
    $stmt = $conn->prepare("SELECT value FROM dropdown_options WHERE category = ? ORDER BY display_order, value");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $options[] = $row['value'];
    }
    return $options;
}

$bin_types = getDropdownOptions($conn, 'bin_types');
$customer_types = getDropdownOptions($conn, 'customer_types');
$property_types = getDropdownOptions($conn, 'property_types');
$compliance_options = getDropdownOptions($conn, 'compliance_status');

// ... rest of the code ...

<?php 
$conn->close();
require_once "footer.php"; 
?>
