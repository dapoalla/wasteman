<?php
// ... existing header code ...

// Add this meta tag for proper mobile rendering
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $title ?? 'Waste Management Portal'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
        /* Improved mobile responsiveness */
        @media (max-width: 768px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            .btn-responsive {
                padding: 0.5rem;
                font-size: 0.8rem;
            }
        }
        
        /* Custom scrollbar for webkit browsers */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a202c; }
        ::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #718096; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 22px; }
    </style>
</head>
<body class="bg-gray-900 text-gray-200 font-sans">
    <div id="app" class="flex h-screen">
        <!-- ... rest of header remains the same ... -->