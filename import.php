<?php
$title = "Data Import";
require_once "header.php";
require_once "db.php";

$message = '';
$message_type = ''; // 'success' or 'error'
$imported_rows = 0;
$failed_rows = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csvfile"])) {
    // Check if file was uploaded without errors
    if (isset($_FILES["csvfile"]) && $_FILES["csvfile"]["error"] == 0) {
        $filename = $_FILES["csvfile"]["tmp_name"];
        $import_type = $_POST['import_type'];
        
        if (($handle = fopen($filename, "r")) !== FALSE) {
            fgetcsv($handle); // Skip header row

            if ($import_type == 'private') {
                $sql = "INSERT INTO private_customers (psp_business_name, lga_lcda, ward, area, property_code, customer_name, phone_number, phone_number_whatsapp, customer_type, business_type, customer_description, house_flat_number, street_address, ward_coverage, property_type, property_unit, vacant, monthly_rate, wheeler_bin_organic_240l, wheeler_bin_recyclables_240l, mammoth_bin_1100l, compliance_status, outstanding_balance, current_due) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            } else {
                $sql = "INSERT INTO commercial_customers (company_name, company_address, category, monthly_tariff, outstanding_2019_2024, current_bill_2025, amount_paid) VALUES (?,?,?,?,?,?,?)";
            }
            
            $stmt = $conn->prepare($sql);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Sanitize and prepare data
                $data = array_map(function($value) {
                    return empty(trim($value)) ? null : trim($value);
                }, $data);

                try {
                    if ($import_type == 'private') {
                        $stmt->bind_param("ssssssssssssssisiissssss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13], $data[14], $data[15], $data[16], $data[17], $data[18], $data[19], $data[20], $data[21], $data[22], $data[23]);
                    } else {
                        $stmt->bind_param("sssdddd", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
                    }
                    
                    if ($stmt->execute()) {
                        $imported_rows++;
                    } else {
                        $failed_rows++;
                    }
                } catch (Exception $e) {
                    $failed_rows++;
                }
            }
            fclose($handle);
            $stmt->close();

            $message = "Import complete. Successfully imported: $imported_rows rows. Failed: $failed_rows rows.";
            $message_type = 'success';
        } else {
            $message = "Error opening the CSV file.";
            $message_type = 'error';
        }
    } else {
        $message = "Error uploading file. Please try again.";
        $message_type = 'error';
    }
}
?>

<div class="container mx-auto max-w-2xl">
    <h2 class="text-3xl font-bold text-white mb-6">Import Customer Data</h2>
    
    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-8">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="import_type" class="block mb-2 text-sm font-medium text-gray-300">Select Import Type</label>
                <select id="import_type" name="import_type" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    <option value="private" <?php echo ($_SESSION['subsidiary'] == 'ITECSOL') ? 'selected' : ''; ?>>Private (ITECSOL)</option>
                    <option value="commercial" <?php echo ($_SESSION['subsidiary'] == 'KONGI') ? 'selected' : ''; ?>>Commercial (KONGI)</option>
                </select>
            </div>
            
            <div class="mb-6">
                 <label for="csvfile" class="block mb-2 text-sm font-medium text-gray-300">Upload CSV File</label>
                 <input type="file" name="csvfile" id="csvfile" required class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                 <p class="mt-1 text-xs text-gray-400">File must be in CSV format.</p>
            </div>

            <div class="flex items-center justify-between mt-8">
                <div class="text-sm">
                    <p class="font-medium text-gray-300">Download sample templates:</p>
                    <div class="flex gap-4 mt-2">
                        <a href="sample_private.csv" download class="text-cyan-400 hover:underline">Private Sample</a>
                        <a href="sample_commercial.csv" download class="text-amber-400 hover:underline">Commercial Sample</a>
                    </div>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg flex items-center">
                    <span class="material-symbols-outlined mr-2">upload</span> Start Import
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$conn->close();
require_once "footer.php"; 
?>
