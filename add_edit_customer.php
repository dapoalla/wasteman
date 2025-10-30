<?php
// This file handles both Add and Edit operations for customers.
// Mode is determined by the presence of an 'id' URL parameter.

require_once "header.php";
require_once "db.php";

// Admin-only page
if ($_SESSION['role'] !== 'admin') {
    header("location: dashboard.php");
    exit;
}

$type = isset($_GET['type']) && $_GET['type'] == 'commercial' ? 'commercial' : 'private';
$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit_mode = $customer_id > 0;

$page_title = ($is_edit_mode ? 'Edit' : 'Add') . ' ' . ucfirst($type) . ' Customer';
$title = $page_title;
$customer_data = [];

// Fetch existing data if in edit mode
if ($is_edit_mode) {
    $table = $type === 'commercial' ? 'commercial_customers' : 'private_customers';
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $customer_data = $result->fetch_assoc();
    } else {
        die("Customer not found.");
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Simplified processing logic for brevity. Add validation as needed.
    if ($type === 'private') {
        $table = 'private_customers';
        $customer_name = $_POST['customer_name'];
        $property_code = $_POST['property_code'];
        $street_address = $_POST['street_address'];
        $outstanding_balance = $_POST['outstanding_balance'];
        $current_due = $_POST['current_due'];

        if ($is_edit_mode) {
            $sql = "UPDATE $table SET customer_name=?, property_code=?, street_address=?, outstanding_balance=?, current_due=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssddi", $customer_name, $property_code, $street_address, $outstanding_balance, $current_due, $customer_id);
        } else {
            $sql = "INSERT INTO $table (customer_name, property_code, street_address, outstanding_balance, current_due) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdd", $customer_name, $property_code, $street_address, $outstanding_balance, $current_due);
        }
    } else { // Commercial
        $table = 'commercial_customers';
        $company_name = $_POST['company_name'];
        $company_address = $_POST['company_address'];
        $amount_paid = $_POST['amount_paid'];
        $balance_adjustment = $_POST['balance_adjustment']; // Special field to adjust balance

        if ($is_edit_mode) {
            // Adjust balance by updating the outstanding amount
            $new_outstanding = $customer_data['outstanding_2019_2024'] + $balance_adjustment;
            $sql = "UPDATE $table SET company_name=?, company_address=?, amount_paid=?, outstanding_2019_2024=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddi", $company_name, $company_address, $amount_paid, $new_outstanding, $customer_id);
        } else {
             $sql = "INSERT INTO $table (company_name, company_address, amount_paid) VALUES (?, ?, ?)";
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("ssd", $company_name, $company_address, $amount_paid);
        }
    }

    if ($stmt->execute()) {
        header("location: " . $type . ".php?message=success");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
    exit;
}

?>

<div class="container mx-auto max-w-4xl">
    <h2 class="text-3xl font-bold text-white mb-6"><?php echo $page_title; ?></h2>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-8">
        <form method="POST">
            <?php if ($type === 'private'): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="customer_name" class="block mb-2 text-sm font-medium text-gray-300">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer_data['customer_name'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5" required>
                </div>
                <div>
                    <label for="property_code" class="block mb-2 text-sm font-medium text-gray-300">Property Code</label>
                    <input type="text" id="property_code" name="property_code" value="<?php echo htmlspecialchars($customer_data['property_code'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5" required>
                </div>
                 <div>
                    <label for="street_address" class="block mb-2 text-sm font-medium text-gray-300">Street Address</label>
                    <input type="text" id="street_address" name="street_address" value="<?php echo htmlspecialchars($customer_data['street_address'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                 <div>
                    <label for="outstanding_balance" class="block mb-2 text-sm font-medium text-gray-300">Outstanding Balance (₦)</label>
                    <input type="number" step="0.01" id="outstanding_balance" name="outstanding_balance" value="<?php echo htmlspecialchars($customer_data['outstanding_balance'] ?? '0.00'); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                 <div>
                    <label for="current_due" class="block mb-2 text-sm font-medium text-gray-300">Current Due (₦)</label>
                    <input type="number" step="0.01" id="current_due" name="current_due" value="<?php echo htmlspecialchars($customer_data['current_due'] ?? '0.00'); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
            </div>
            <?php else: // Commercial Form ?>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="company_name" class="block mb-2 text-sm font-medium text-gray-300">Company Name</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($customer_data['company_name'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5" required>
                </div>
                 <div>
                    <label for="company_address" class="block mb-2 text-sm font-medium text-gray-300">Company Address</label>
                    <input type="text" id="company_address" name="company_address" value="<?php echo htmlspecialchars($customer_data['company_address'] ?? ''); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                 <div>
                    <label for="amount_paid" class="block mb-2 text-sm font-medium text-gray-300">Amount Paid (₦)</label>
                    <input type="number" step="0.01" id="amount_paid" name="amount_paid" value="<?php echo htmlspecialchars($customer_data['amount_paid'] ?? '0.00'); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                <?php if ($is_edit_mode): ?>
                <div>
                    <label for="balance_adjustment" class="block mb-2 text-sm font-medium text-gray-300">Balance Adjustment (₦)</label>
                    <input type="number" step="0.01" id="balance_adjustment" name="balance_adjustment" value="0.00" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                    <p class="mt-1 text-xs text-gray-400">Enter a value to adjust the balance. Use a negative number to reduce the amount owed.</p>
                </div>
                <?php endif; ?>
             </div>
            <?php endif; ?>

            <div class="mt-8 flex justify-end">
                <a href="<?php echo $type; ?>.php" class="text-white bg-gray-600 hover:bg-gray-700 rounded-lg px-5 py-2.5 mr-4">Cancel</a>
                <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5">
                    <?php echo $is_edit_mode ? 'Save Changes' : 'Add Customer'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once "footer.php";
?>