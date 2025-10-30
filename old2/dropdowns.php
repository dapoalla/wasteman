<?php
$title = "Dropdown Management";
require_once "header.php";
require_once "db.php";

// Admin-only page
if ($_SESSION['role'] !== 'admin') {
    header("location: dashboard.php");
    exit;
}

$categories = [
    'bin_types' => 'Bin Types',
    'customer_types' => 'Customer Types', 
    'property_types' => 'Property Types',
    'compliance_status' => 'Compliance Status'
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_option'])) {
        $category = $_POST['category'];
        $value = trim($_POST['value']);
        
        if (!empty($category) && !empty($value)) {
            $stmt = $conn->prepare("INSERT INTO dropdown_options (category, value) VALUES (?, ?)");
            $stmt->bind_param("ss", $category, $value);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete_option'])) {
        $id = intval($_POST['option_id']);
        $stmt = $conn->prepare("DELETE FROM dropdown_options WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Get all options
$options_result = $conn->query("SELECT * FROM dropdown_options ORDER BY category, display_order, value");
$options_by_category = [];
while ($row = $options_result->fetch_assoc()) {
    $options_by_category[$row['category']][] = $row;
}
?>

<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold text-white mb-6">Dropdown Management</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Add New Option Form -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-4">Add New Option</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                    <select name="category" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Value</label>
                    <input type="text" name="value" required class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                <button type="submit" name="add_option" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm px-5 py-2.5">
                    Add Option
                </button>
            </form>
        </div>

        <!-- Existing Options -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-4">Current Options</h3>
            <div class="space-y-4">
                <?php foreach ($categories as $category_key => $category_label): ?>
                <div>
                    <h4 class="font-medium text-white mb-2"><?php echo $category_label; ?></h4>
                    <ul class="bg-gray-700/50 rounded-lg p-2">
                        <?php if (isset($options_by_category[$category_key])): ?>
                            <?php foreach ($options_by_category[$category_key] as $option): ?>
                            <li class="flex justify-between items-center py-1 px-2">
                                <span class="text-gray-300"><?php echo htmlspecialchars($option['value']); ?></span>
                                <form method="POST" onsubmit="return confirm('Delete this option?');">
                                    <input type="hidden" name="option_id" value="<?php echo $option['id']; ?>">
                                    <button type="submit" name="delete_option" class="text-red-400 hover:text-red-300">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-400 py-2 px-2">No options defined</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once "footer.php";
?>