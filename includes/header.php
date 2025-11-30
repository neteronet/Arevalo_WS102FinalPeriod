<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAC Cyberian Repository</title>
    
    <!-- Tailwind CSS (CDN for ease of use) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Configure Tailwind Theme Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sac-blue': '#0A3D62', // Deep Blue
                        'sac-gold': '#FBC531', // Accent Gold
                        'cyber-dark': '#1f1f2e',
                    }
                }
            }
        }
    </script>

    <!-- Internal CSS for Custom Overrides -->
    <style>
        /* Custom Scrollbar for a "Cyber" feel */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #0A3D62; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #FBC531; }
        
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800">

    <!-- Navigation -->
    <nav class="bg-sac-blue shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-sac-gold text-2xl font-bold tracking-wide">SAC Cyberian</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-white hover:text-sac-gold transition duration-300">Home</a>
                    <a href="search.php" class="text-white hover:text-sac-gold transition duration-300">Browse</a>
                    <a href="#" class="px-4 py-2 bg-sac-gold text-sac-blue font-bold rounded hover:bg-yellow-400 transition">Login</a>
                </div>
            </div>
        </div>
    </nav>