<?php
$title = "Dashboard";
require_once "header.php";
require_once "db.php";

$is_private = ($_SESSION["subsidiary"] == 'ITECSOL');
$table_name = $is_private ? 'private_customers' : 'commercial_customers';
$message = '';
$message_type = '';

// Handle Quick Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'quick_payment') {
    $customer_full = $_POST['customer_id'];
    list($customer_type, $customer_id) = explode('-', $customer_full);
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_date = $_POST['payment_date'];
    $months_covered = implode(', ', $_POST['months'] ?? ['N/A']);
    $user_id = $_SESSION['id'];

    // Insert into payments table
    $stmt = $conn->prepare("INSERT INTO payments (customer_id, customer_type, amount_paid, payment_date, months_covered, entry_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdssi", $customer_id, $customer_type, $amount_paid, $payment_date, $months_covered, $user_id);
    
    if ($stmt->execute()) {
        // Update customer balance
        if ($customer_type == 'private') {
            $conn->query("UPDATE private_customers SET outstanding_balance = outstanding_balance - $amount_paid WHERE id = $customer_id");
        } else {
            $conn->query("UPDATE commercial_customers SET amount_paid = amount_paid + $amount_paid WHERE id = $customer_id");
        }
        $message = "Payment of ₦" . number_format($amount_paid) . " recorded successfully!";
        $message_type = 'success';
    } else {
        $message = "Error recording payment: " . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}


// Fetch stats
$total_customers = $conn->query("SELECT COUNT(id) as count FROM $table_name")->fetch_assoc()['count'];
$paid_bills = $conn->query("SELECT COUNT(id) as count FROM $table_name WHERE bill_status = 'paid'")->fetch_assoc()['count'];

if($is_private) {
    $total_outstanding = $conn->query("SELECT SUM(total_due) as total FROM $table_name")->fetch_assoc()['total'];
    $compliant_customers = $conn->query("SELECT COUNT(id) as count FROM $table_name WHERE compliance_status = 'C'")->fetch_assoc()['count'];
    $debtors_over_100k = $conn->query("SELECT COUNT(id) as count FROM $table_name WHERE total_due > 100000")->fetch_assoc()['count'];
} else {
    $total_outstanding = $conn->query("SELECT SUM(balance) as total FROM $table_name")->fetch_assoc()['total'];
    $high_value_clients = $conn->query("SELECT COUNT(id) as count FROM $table_name WHERE is_high_value = 1")->fetch_assoc()['count'];
}

// Fetch all customers for payment modal
$private_customers_res = $conn->query("SELECT id, customer_name FROM private_customers ORDER BY customer_name");
$commercial_customers_res = $conn->query("SELECT id, company_name FROM commercial_customers ORDER BY company_name");


function format_currency($amount) {
    return '₦' . number_format($amount, 2);
}
?>

<div class="container mx-auto">
    <h2 class="text-3xl font-bold text-white mb-6">Dashboard</h2>
    
    <?php if ($message): ?>
    <div class="mb-4 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 flex items-center">
             <div class="p-3 rounded-full <?php echo $bg_theme_color_class; ?>/20 mr-4"><span class="material-symbols-outlined text-3xl <?php echo $theme_color_class; ?>">groups</span></div>
            <div><p class="text-sm text-gray-400">Total Customers</p><p class="text-2xl font-bold text-white"><?php echo $total_customers; ?></p></div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 flex items-center">
             <div class="p-3 rounded-full <?php echo $bg_theme_color_class; ?>/20 mr-4"><span class="material-symbols-outlined text-3xl <?php echo $theme_color_class; ?>">account_balance_wallet</span></div>
            <div><p class="text-sm text-gray-400">Total Outstanding</p><p class="text-2xl font-bold text-white"><?php echo format_currency($total_outstanding); ?></p></div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 flex items-center">
             <div class="p-3 rounded-full <?php echo $bg_theme_color_class; ?>/20 mr-4"><span class="material-symbols-outlined text-3xl <?php echo $theme_color_class; ?>"><?php echo $is_private ? 'verified' : 'workspace_premium'; ?></span></div>
            <div><p class="text-sm text-gray-400"><?php echo $is_private ? 'Compliant Customers' : 'High Value Clients'; ?></p><p class="text-2xl font-bold text-white"><?php echo $is_private ? $compliant_customers : $high_value_clients; ?></p></div>
        </div>
        
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 flex items-center">
             <div class="p-3 rounded-full <?php echo $bg_theme_color_class; ?>/20 mr-4"><span class="material-symbols-outlined text-3xl <?php echo $theme_color_class; ?>">price_check</span></div>
            <div><p class="text-sm text-gray-400">Bills Paid (Current Cycle)</p><p class="text-2xl font-bold text-white"><?php echo $paid_bills; ?></p></div>
        </div>

        <?php if ($is_private): ?>
        <div class="bg-red-900/30 border border-red-700 rounded-xl p-6 flex items-center">
             <div class="p-3 rounded-full bg-red-500/20 mr-4"><span class="material-symbols-outlined text-3xl text-red-400">dangerous</span></div>
            <div><p class="text-sm text-red-300">Debtors > ₦100k</p><p class="text-2xl font-bold text-white"><?php echo $debtors_over_100k; ?></p></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h3 class="text-xl font-semibold text-white mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="<?php echo $is_private ? 'private.php' : 'commercial.php'; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center"><span class="material-symbols-outlined mr-2">visibility</span> View Customers</a>
            <button id="quick-payment-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center"><span class="material-symbols-outlined mr-2">add_card</span> Quick Payment Entry</button>
        </div>
    </div>
</div>

<div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden items-center justify-center">
    <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-lg p-8 w-full max-w-md">
        <h3 class="text-xl font-semibold text-white mb-6">Record a Payment</h3>
        <form method="POST">
            <input type="hidden" name="action" value="quick_payment">
            <div class="mb-4">
                <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-300">Customer</label>
                <select name="customer_id" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                    <option value="">-- Select Customer --</option>
                    <optgroup label="Private Customers">
                        <?php while($c = $private_customers_res->fetch_assoc()): ?>
                        <option value="private-<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['customer_name']); ?></option>
                        <?php endwhile; ?>
                    </optgroup>
                    <optgroup label="Commercial Customers">
                         <?php while($c = $commercial_customers_res->fetch_assoc()): ?>
                        <option value="commercial-<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['company_name']); ?></option>
                        <?php endwhile; ?>
                    </optgroup>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="amount_paid" class="block mb-2 text-sm font-medium text-gray-300">Amount Paid (₦)</label>
                    <input type="number" name="amount_paid" step="0.01" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                     <label for="payment_date" class="block mb-2 text-sm font-medium text-gray-300">Payment Date</label>
                    <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
            </div>
             <div class="mb-6">
                <label class="block mb-2 text-sm font-medium text-gray-300">Months Covered</label>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <?php for ($i = -3; $i <= 3; $i++): $month = date('M Y', strtotime("$i month")); ?>
                    <label class="flex items-center space-x-2 bg-gray-700/50 p-2 rounded-md"><input type="checkbox" name="months[]" value="<?php echo $month; ?>" class="rounded bg-gray-900 border-gray-600 text-indigo-500 focus:ring-indigo-600"><span><?php echo $month; ?></span></label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="flex justify-end gap-4">
                <button type="button" id="close-modal-btn" class="text-gray-300 hover:text-white font-medium rounded-lg text-sm px-5 py-2.5">Cancel</button>
                <button type="submit" class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-sm px-5 py-2.5">Record Payment</button>
            </div>
        </form>
    </div>
</div>
<script>
    const modal = document.getElementById('payment-modal');
    const openBtn = document.getElementById('quick-payment-btn');
    const closeBtn = document.getElementById('close-modal-btn');
    openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    window.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });
</script>

<?php 
$conn->close();
require_once "footer.php"; 
?>