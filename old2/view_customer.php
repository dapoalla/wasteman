<?php
require_once "header.php";
require_once "db.php";

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header("location: dashboard.php");
    exit;
}

$customer_id = intval($_GET['id']);
$type = $_GET['type'] === 'commercial' ? 'commercial' : 'private';
$table = $type . '_customers';

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    // Process form data and update customer
    $update_fields = [];
    $update_values = [];
    $types = '';
    
    foreach ($_POST as $field => $value) {
        if ($field !== 'customer_id' && $field !== 'update_customer') {
            $update_fields[] = "$field = ?";
            $update_values[] = $value;
            $types .= 's';
        }
    }
    
    $update_values[] = $customer_id;
    $types .= 'i';
    
    if (!empty($update_fields)) {
        $sql = "UPDATE $table SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$update_values);
        $stmt->execute();
        
        // Refresh customer data
        header("Location: view_customer.php?type=$type&id=$customer_id&updated=1");
        exit;
    }
}

// Fetch customer data
$stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Customer not found.");
}
$customer = $result->fetch_assoc();
$stmt->close();

// Fetch dropdown options
$bin_types = getDropdownOptions($conn, 'bin_types');
$customer_types = getDropdownOptions($conn, 'customer_types');
$property_types = getDropdownOptions($conn, 'property_types');
$compliance_options = getDropdownOptions($conn, 'compliance_status');

$title = "View Customer: " . ($type === 'private' ? $customer['customer_name'] : $customer['company_name']);

// Fetch payment history
$payments_result = $conn->query("SELECT p.*, u.username FROM payments p JOIN users u ON p.entry_by_user_id = u.id WHERE p.customer_id = $customer_id AND p.customer_type = '$type' ORDER BY p.payment_date DESC");

// Calculate total due
$total_due = ($customer['outstanding_balance'] ?? 0) + ($customer['current_due'] ?? 0);
?>

<div class="container mx-auto px-4">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h2 class="text-2xl md:text-3xl font-bold text-white"><?php echo $title; ?></h2>
        <div class="flex flex-wrap gap-2">
            <a href="generate_bill.php?type=<?php echo $type; ?>&id=<?php echo $customer_id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center btn-responsive">
                <span class="material-symbols-outlined mr-2">receipt</span> Generate Bill
            </a>
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="add_edit_customer.php?type=<?php echo $type; ?>&id=<?php echo $customer_id; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center btn-responsive">
                <span class="material-symbols-outlined mr-2">edit</span> Edit Customer
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div class="mb-4 p-4 rounded-lg bg-green-500/20 text-green-300">
        Customer details updated successfully.
    </div>
    <?php endif; ?>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-white">Customer Details</h3>
            <div class="text-2xl font-bold text-amber-400">
                Total Due: ₦<?php echo number_format($total_due, 2); ?>
            </div>
        </div>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            
            <?php foreach ($customer as $key => $value): 
                $editable = $_SESSION['role'] === 'admin';
                $field_value = htmlspecialchars($value ?? '');
            ?>
            <div class="bg-gray-700/30 p-3 rounded-lg">
                <p class="text-xs text-gray-400 capitalize"><?php echo str_replace('_', ' ', $key); ?></p>
                
                <?php if ($editable): ?>
                    <?php if ($key === 'compliance_status'): ?>
                    <select name="<?php echo $key; ?>" class="bg-gray-600 border border-gray-500 text-white text-sm rounded block w-full p-1 mt-1">
                        <?php foreach ($compliance_options as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($value == $option) echo 'selected'; ?>>
                            <?php echo $option; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php elseif ($key === 'customer_type'): ?>
                    <select name="<?php echo $key; ?>" class="bg-gray-600 border border-gray-500 text-white text-sm rounded block w-full p-1 mt-1">
                        <?php foreach ($customer_types as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($value == $option) echo 'selected'; ?>>
                            <?php echo $option; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php elseif ($key === 'property_type'): ?>
                    <select name="<?php echo $key; ?>" class="bg-gray-600 border border-gray-500 text-white text-sm rounded block w-full p-1 mt-1">
                        <?php foreach ($property_types as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($value == $option) echo 'selected'; ?>>
                            <?php echo $option; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php else: ?>
                    <input type="text" name="<?php echo $key; ?>" value="<?php echo $field_value; ?>" 
                           class="bg-gray-600 border border-gray-500 text-white text-sm rounded block w-full p-1 mt-1">
                    <?php endif; ?>
                    
                <?php else: ?>
                    <p class="font-medium text-white"><?php echo $field_value; ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="md:col-span-2 lg:col-span-3 flex justify-end mt-4">
                <button type="submit" name="update_customer" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm px-5 py-2.5">
                    Save Changes
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Payment history section remains the same -->
</div>

<?php
$conn->close();
require_once "footer.php";
?>