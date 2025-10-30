<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About the Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 48; }
    </style>
</head>
<body class="bg-gray-900 text-gray-300 font-sans">
    <div class="container mx-auto max-w-5xl px-4 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-3">Waste Management Portal</h1>
            <p class="text-lg text-gray-400">An all-in-one solution for tracking customer data, managing payments, and streamlining operations for both private and commercial waste collection services.</p>
        </div>

        <!-- Features Section -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-white text-center mb-8">Core Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
                    <span class="material-symbols-outlined text-cyan-400 mb-4">dashboard</span>
                    <h3 class="text-xl font-semibold text-white mb-2">Analytics Dashboard</h3>
                    <p class="text-gray-400">Get a high-level overview of your operations with key stats like total customers, outstanding balances, and payment statuses.</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
                    <span class="material-symbols-outlined text-cyan-400 mb-4">groups</span>
                    <h3 class="text-xl font-semibold text-white mb-2">Customer Management</h3>
                    <p class="text-gray-400">Easily add, view, edit, and delete customer records. Separate portals for Private (ITECSOL) and Commercial (KONGI) clients.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
                    <span class="material-symbols-outlined text-cyan-400 mb-4">upload_file</span>
                    <h3 class="text-xl font-semibold text-white mb-2">Bulk Data Import</h3>
                    <p class="text-gray-400">Quickly populate your database by importing customer data from a CSV file, with sample templates provided.</p>
                </div>
                 <!-- Feature 4 -->
                <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
                    <span class="material-symbols-outlined text-cyan-400 mb-4">receipt_long</span>
                    <h3 class="text-xl font-semibold text-white mb-2">Payment Tracking</h3>
                    <p class="text-gray-400">Record payments with the "Quick Entry" tool, review payment history per customer, and filter through all transactions.</p>
                </div>
                 <!-- Feature 5 -->
                <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
                    <span class="material-symbols-outlined text-cyan-400 mb-4">admin_panel_settings</span>
                    <h3 class="text-xl font-semibold text-white mb-2">Admin Controls</h3>
                    <p class="text-gray-400">Admins can manage global billing rates, edit the internal user guide, and toggle "High Value" status for key clients.</p>
                </div>
                 <!-- Feature 6 -->
                <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
                    <span class="material-symbols-outlined text-cyan-400 mb-4">manage_accounts</span>
                    <h3 class="text-xl font-semibold text-white mb-2">Role-Based Access</h3>
                    <p class="text-gray-400">Different user roles (Admin, Operator) ensure that staff members only have access to the features they need.</p>
                </div>
            </div>
        </div>

        <!-- How-To Section -->
        <div>
             <h2 class="text-3xl font-bold text-white text-center mb-8">How to Perform Basic Tasks</h2>
             <div class="bg-gray-800/50 p-8 rounded-xl border border-gray-700 space-y-6">
                <div>
                    <h3 class="text-xl font-semibold text-white">1. How to Import Customer Data</h3>
                    <ol class="list-decimal list-inside text-gray-400 mt-2 space-y-1">
                        <li>Navigate to the <span class="font-semibold text-cyan-400">"Data Import"</span> page from the sidebar.</li>
                        <li>Download the sample CSV template (Private or Commercial) to see the required format.</li>
                        <li>Fill your CSV file with the customer data.</li>
                        <li>Select the correct "Import Type" on the page.</li>
                        <li>Upload your completed CSV file and click <span class="font-semibold text-white">"Start Import"</span>.</li>
                    </ol>
                </div>
                 <div>
                    <h3 class="text-xl font-semibold text-white">2. How to Record a Payment</h3>
                    <ol class="list-decimal list-inside text-gray-400 mt-2 space-y-1">
                        <li>From the <span class="font-semibold text-cyan-400">"Dashboard"</span>, click the <span class="font-semibold text-green-400">"Quick Payment Entry"</span> button.</li>
                        <li>A modal window will appear. Select the customer from the dropdown list.</li>
                        <li>Enter the amount paid and the date of the payment.</li>
                        <li>(Optional) Tick the checkboxes for the months the payment covers.</li>
                        <li>Click <span class="font-semibold text-white">"Record Payment"</span> to save.</li>
                    </ol>
                </div>
                 <div>
                    <h3 class="text-xl font-semibold text-white">3. How to View a Customer's History (Admin)</h3>
                    <ol class="list-decimal list-inside text-gray-400 mt-2 space-y-1">
                        <li>Go to the <span class="font-semibold text-cyan-400">"Private"</span> or <span class="font-semibold text-amber-400">"Commercial"</span> customer list page.</li>
                        <li>Click on the name of the customer you wish to view.</li>
                        <li>You will be taken to a detailed page showing all their information and a full payment history at the bottom.</li>
                        <li>From here, you can also click the <span class="font-semibold text-white">"Edit Customer"</span> button to update their details.</li>
                    </ol>
                </div>
             </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <a href="login.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                Proceed to Portal
            </a>
        </div>
    </div>
</body>
</html>
