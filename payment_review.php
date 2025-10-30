<?php
$title = "Payment Review";
require_once "header.php";
require_once "db.php";

$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = '';

if (!empty($search_term)) {
    $where_clause = "WHERE pc.customer_name LIKE '%$search_term%' OR cc.company_name LIKE '%$search_term%'";
}

$sql = "
    SELECT p.id, p.payment_date, p.amount_paid, p.months_covered, u.username,
           p.customer_type,
           CASE
               WHEN p.customer_type = 'private' THEN pc.customer_name
               WHEN p.customer_type = 'commercial' THEN cc.company_name
           END AS customer_name
    FROM payments p
    LEFT JOIN private_customers pc ON p.customer_id = pc.id AND p.customer_type = 'private'
    LEFT JOIN commercial_customers cc ON p.customer_id = cc.id AND p.customer_type = 'commercial'
    JOIN users u ON p.entry_by_user_id = u.id
    $where_clause
    ORDER BY p.payment_date DESC
    LIMIT 100
";

$payments = $conn->query($sql);

?>
<div class="container mx-auto">
    <h2 class="text-3xl font-bold text-white mb-6">Payment Review</h2>

    <div class="mb-6">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" placeholder="Search by Customer Name..." value="<?php echo htmlspecialchars($search_term); ?>" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full md:w-1/3 p-2.5">
            <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5">Search</button>
        </form>
    </div>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Customer Name</th>
                        <th scope="col" class="px-6 py-3">Type</th>
                        <th scope="col" class="px-6 py-3">Amount Paid</th>
                        <th scope="col" class="px-6 py-3">Entered By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments && $payments->num_rows > 0): ?>
                        <?php while($row = $payments->fetch_assoc()): ?>
                        <tr class="border-b border-gray-700 hover:bg-gray-700/40">
                            <td class="px-6 py-4"><?php echo date("M j, Y", strtotime($row['payment_date'])); ?></td>
                            <td class="px-6 py-4 font-medium text-white"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td class="px-6 py-4 capitalize"><?php echo htmlspecialchars($row['customer_type']); ?></td>
                            <td class="px-6 py-4 text-green-400">₦<?php echo number_format($row['amount_paid'], 2); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['username']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-8">No payment records found.</td>
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