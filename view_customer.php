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

$title = "View Customer: " . ($type === 'private' ? $customer['customer_name'] : $customer['company_name']);

// Fetch payment history
$payments_result = $conn->query("SELECT p.*, u.username FROM payments p JOIN users u ON p.entry_by_user_id = u.id WHERE p.customer_id = $customer_id AND p.customer_type = '$type' ORDER BY p.payment_date DESC");

?>
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-white"><?php echo $title; ?></h2>
        <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="add_edit_customer.php?type=<?php echo $type; ?>&id=<?php echo $customer_id; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
            <span class="material-symbols-outlined mr-2">edit</span> Edit Customer
        </a>
        <?php endif; ?>
    </div>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-8">
        <h3 class="text-xl font-semibold text-white mb-4">Customer Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <?php foreach ($customer as $key => $value): ?>
                <div class="bg-gray-700/30 p-3 rounded-lg">
                    <p class="text-xs text-gray-400 capitalize"><?php echo str_replace('_', ' ', $key); ?></p>
                    <p class="font-medium text-white"><?php echo htmlspecialchars($value); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl">
         <h3 class="text-xl font-semibold text-white mb-4 p-6 border-b border-gray-700">Payment History</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Amount Paid</th>
                        <th scope="col" class="px-6 py-3">Months Covered</th>
                        <th scope="col" class="px-6 py-3">Entered By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments_result->num_rows > 0): ?>
                        <?php while($row = $payments_result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-700 hover:bg-gray-700/40">
                            <td class="px-6 py-4"><?php echo date("M j, Y", strtotime($row['payment_date'])); ?></td>
                            <td class="px-6 py-4 font-medium text-green-400">₦<?php echo number_format($row['amount_paid'], 2); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['months_covered']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['username']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-8">No payment records found for this customer.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$conn->close();
require_once "footer.php";
?>