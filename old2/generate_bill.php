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

// Calculate total due
$total_due = ($customer['outstanding_balance'] ?? 0) + ($customer['current_due'] ?? 0);

// Handle bill generation and WhatsApp message
$whatsapp_message = "";
$whatsapp_url = "";

if ($customer['phone_number_whatsapp'] || $customer['phone_number']) {
    $phone = $customer['phone_number_whatsapp'] ?? $customer['phone_number'];
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    $customer_name = $customer['customer_name'] ?? $customer['company_name'];
    $whatsapp_message = "Hello " . $customer_name . ", here is your waste management bill. Total amount due: ₦" . number_format($total_due, 2) . ". Thank you for your business!";
    $encoded_message = urlencode($whatsapp_message);
    $whatsapp_url = "https://wa.me/" . $phone . "?text=" . $encoded_message;
}

// Handle background image upload
$background_path = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bill_background'])) {
    $upload_dir = "uploads/backgrounds/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['bill_background']['name']);
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['bill_background']['tmp_name'], $target_path)) {
        $background_path = $target_path;
        $_SESSION['bill_background'] = $background_path;
    }
} elseif (isset($_SESSION['bill_background'])) {
    $background_path = $_SESSION['bill_background'];
}

$title = "Generate Bill for " . ($type === 'private' ? $customer['customer_name'] : $customer['company_name']);
?>

<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold text-white mb-6"><?php echo $title; ?></h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bill Preview -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-4">Bill Preview</h3>
            
            <div id="bill-preview" class="bg-white text-black p-6 rounded-lg relative min-h-[400px]">
                <?php if ($background_path): ?>
                <img src="<?php echo $background_path; ?>" class="absolute inset-0 w-full h-full object-cover opacity-20 z-0">
                <?php endif; ?>
                
                <div class="relative z-10">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold">INVOICE</h2>
                        <p class="text-gray-600">#INV-<?php echo date('Ymd'); ?>-<?php echo $customer_id; ?></p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <h3 class="font-semibold">From:</h3>
                            <p>Waste Management Services</p>
                            <p>123 Service Road, Lagos</p>
                            <p>Phone: +234 800 123 4567</p>
                        </div>
                        
                        <div>
                            <h3 class="font-semibold">To:</h3>
                            <p><?php echo htmlspecialchars($customer['customer_name'] ?? $customer['company_name']); ?></p>
                            <p><?php echo htmlspecialchars($customer['street_address'] ?? $customer['company_address']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <table class="w-full mb-6">
                        <thead>
                            <tr class="border-b-2 border-gray-800">
                                <th class="text-left py-2">Description</th>
                                <th class="text-right py-2">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($customer['outstanding_balance'] > 0): ?>
                            <tr>
                                <td class="py-2">Outstanding Balance</td>
                                <td class="text-right py-2">₦<?php echo number_format($customer['outstanding_balance'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($customer['current_due'] > 0): ?>
                            <tr>
                                <td class="py-2">Current Charges</td>
                                <td class="text-right py-2">₦<?php echo number_format($customer['current_due'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            
                            <tr class="border-t-2 border-gray-800 font-bold">
                                <td class="py-2">TOTAL DUE</td>
                                <td class="text-right py-2">₦<?php echo number_format($total_due, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="text-center text-sm text-gray-600 mt-10">
                        <p>Thank you for your business!</p>
                        <p>Please make payment by <?php echo date('F j, Y', strtotime('+15 days')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex flex-wrap gap-2">
                <button onclick="printBill()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg flex items-center btn-responsive">
                    <span class="material-symbols-outlined mr-2">print</span> Print Bill
                </button>
                
                <button onclick="downloadPDF()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center btn-responsive">
                    <span class="material-symbols-outlined mr-2">download</span> Download PDF
                </button>
                
                <?php if ($whatsapp_url): ?>
                <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center btn-responsive">
                    <span class="material-symbols-outlined mr-2">send</span> Send via WhatsApp
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Background Upload & Customer Info -->
        <div class="space-y-6">
            <!-- Background Upload -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Customize Bill</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Upload Background Image</label>
                        <input type="file" name="bill_background" accept="image/jpeg,image/jpg" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm px-5 py-2.5">
                        Apply Background
                    </button>
                </form>
            </div>
            
            <!-- Customer Information -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Customer Information</h3>
                <div class="space-y-2 text-sm">
                    <p><span class="text-gray-400">Name:</span> <span class="text-white"><?php echo htmlspecialchars($customer['customer_name'] ?? $customer['company_name']); ?></span></p>
                    <p><span class="text-gray-400">Address:</span> <span class="text-white"><?php echo htmlspecialchars($customer['street_address'] ?? $customer['company_address']); ?></span></p>
                    <p><span class="text-gray-400">Phone:</span> <span class="text-white"><?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></span></p>
                    <p><span class="text-gray-400">WhatsApp:</span> <span class="text-white"><?php echo htmlspecialchars($customer['phone_number_whatsapp'] ?? 'N/A'); ?></span></p>
                    <p><span class="text-gray-400">Outstanding Balance:</span> <span class="text-amber-400">₦<?php echo number_format($customer['outstanding_balance'] ?? 0, 2); ?></span></p>
                    <p><span class="text-gray-400">Current Due:</span> <span class="text-amber-400">₦<?php echo number_format($customer['current_due'] ?? 0, 2); ?></span></p>
                    <p><span class="text-gray-400">Total Due:</span> <span class="text-red-400 font-bold">₦<?php echo number_format($total_due, 2); ?></span></p>
                </div>
            </div>
            
            <!-- Mark as Sent -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Bill Status</h3>
                <form method="POST" action="update_bill_status.php">
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                    <input type="hidden" name="customer_type" value="<?php echo $type; ?>">
                    <input type="hidden" name="status" value="sent">
                    
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center">
                        <span class="material-symbols-outlined mr-2">check_circle</span> Mark Bill as Sent
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function printBill() {
    const printContent = document.getElementById('bill-preview').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

function downloadPDF() {
    // This would use a PDF generation library in a real implementation
    alert("PDF download functionality would be implemented here with a library like jsPDF");
}
</script>

<?php
require_once "footer.php";
?>