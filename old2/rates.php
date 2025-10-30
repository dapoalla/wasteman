<?php
$title = "Global Rates Management";
require_once "header.php";
require_once "db.php";

// Admin-only page
if ($_SESSION['role'] !== 'admin') {
    header("location: dashboard.php");
    exit;
}

// Handle POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new rate
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $stmt = $conn->prepare("INSERT INTO global_rates (rate_name, rate_value, customer_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $_POST['rate_name'], $_POST['rate_value'], $_POST['customer_type']);
        $stmt->execute();
    }
    // Delete rate
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $stmt = $conn->prepare("DELETE FROM global_rates WHERE id = ?");
        $stmt->bind_param("i", $_POST['rate_id']);
        $stmt->execute();
    }
    header("location: rates.php"); // Redirect to prevent form resubmission
    exit;
}

$rates = $conn->query("SELECT * FROM global_rates ORDER BY customer_type, rate_name");
?>
<div class="container mx-auto max-w-4xl">
    <h2 class="text-3xl font-bold text-white mb-6">Global Rates Management</h2>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-8">
        <h3 class="text-lg font-semibold text-white mb-4">Add New Rate</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="action" value="add">
            <div class="md:col-span-2">
                <label for="rate_name" class="block mb-2 text-sm font-medium text-gray-300">Rate Name</label>
                <input type="text" name="rate_name" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
            </div>
            <div>
                <label for="rate_value" class="block mb-2 text-sm font-medium text-gray-300">Rate Value (₦)</label>
                <input type="number" step="0.01" name="rate_value" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
            </div>
             <div>
                <label for="customer_type" class="block mb-2 text-sm font-medium text-gray-300">Customer Type</label>
                <select name="customer_type" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                    <option value="private">Private</option>
                    <option value="commercial">Commercial</option>
                </select>
            </div>
            <div class="md:col-start-4">
                 <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5">Add Rate</button>
            </div>
        </form>
    </div>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full text-sm text-left text-gray-300">
            <thead class="text-xs text-gray-400 uppercase bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3">Rate Name</th>
                    <th class="px-6 py-3">Customer Type</th>
                    <th class="px-6 py-3">Value</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $rates->fetch_assoc()): ?>
                <tr class="border-b border-gray-700">
                    <td class="px-6 py-4 font-medium text-white"><?php echo htmlspecialchars($row['rate_name']); ?></td>
                    <td class="px-6 py-4 capitalize"><?php echo htmlspecialchars($row['customer_type']); ?></td>
                    <td class="px-6 py-4">₦<?php echo number_format($row['rate_value'], 2); ?></td>
                    <td class="px-6 py-4">
                        <form method="POST" onsubmit="return confirm('Delete this rate?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="rate_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="text-red-400 hover:text-red-200">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$conn->close();
require_once "footer.php";
?>