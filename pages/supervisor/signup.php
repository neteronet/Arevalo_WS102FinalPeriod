<?php
include '../../includes/connection.php';

$signup_error = '';
$signup_success = '';
?>

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

    <!-- External CSS Stylesheet -->
    <link rel="stylesheet" href="../../css/styles.css">

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
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #0A3D62;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #FBC531;
        }

        body {
            font-family: 'Roboto', sans-serif;
        }

        /* Mobile Menu Toggle */
        .mobile-menu {
            display: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .mobile-menu.active {
            display: block;
            max-height: 500px;
        }

        .hamburger-menu {
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 8px;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
        }

        .hamburger-menu span {
            display: block;
            width: 22px;
            height: 2.5px;
            background-color: white;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
            transform-origin: center;
        }
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800">
    <!-- Navigation -->
    <nav class="bg-sac-blue shadow-lg sticky top-0 z-50">
        <div class="w-full">
            <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex justify-between items-center h-14 sm:h-16 md:h-16">
                    <div class="flex items-center min-w-0 flex-1">
                        <span class="text-sac-gold text-base sm:text-lg md:text-2xl font-bold tracking-wide truncate">SAC Cyberian</span>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-4 lg:space-x-8">
                        <a href="../../index.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Home</a>
                        <a href="../search.php" class="text-white hover:text-sac-gold transition duration-300 text-sm lg:text-base whitespace-nowrap">Browse</a>
                    </div>
                    

                    <!-- Mobile Hamburger Menu - Right Side -->
                    <div class="md:hidden ml-auto">
                        <div class="hamburger-menu" id="hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu - Dropdown -->
                <div class="mobile-menu" id="mobileMenu">
                    <div class="bg-sac-blue border-t border-blue-500 md:hidden px-2 sm:px-4 py-2 sm:py-3">
                        <a href="../../index.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-1">Home</a>
                        <a href="../search.php" class="block text-white hover:text-sac-gold hover:bg-blue-700 transition duration-300 px-3 sm:px-4 py-3 text-sm sm:text-base border-b border-blue-500 active:bg-blue-700 rounded mb-3 sm:mb-4">Browse</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile Menu Toggle
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');

        if (hamburger) {
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                mobileMenu.classList.toggle('active');
            });

            // Close menu when a link is clicked
            const mobileMenuLinks = mobileMenu.querySelectorAll('a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    hamburger.classList.remove('active');
                    mobileMenu.classList.remove('active');
                });
            });
        }
    </script>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $supervisor_id = isset($_POST['supervisor_id']) ? trim($_POST['supervisor_id']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($supervisor_id) || empty($email) || empty($username) || empty($department) || empty($password)) {
        $signup_error = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $signup_error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $signup_error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_error = 'Please enter a valid email address.';
    } elseif (strlen($username) < 4) {
        $signup_error = 'Username must be at least 4 characters long.';
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $signup_error = 'Username or email already exists. Please choose another.';
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new supervisor user
            $insert_sql = "INSERT INTO users (first_name, last_name, supervisor_id, email, username, department, password, role, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'supervisor', 'active')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sssssss", $first_name, $last_name, $supervisor_id, $email, $username, $department, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $signup_success = 'Account created successfully!';
            } else {
                $signup_error = 'An error occurred while creating your account. Please try again.';
            }
            $insert_stmt->close();
        }
    }
}
?>

<!-- SweetAlert2 success popup -->
<?php if (!empty($signup_success)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful',
                html: 'Your supervisor account has been created successfully.',
                confirmButtonText: 'Proceed to Login',
                confirmButtonColor: '#0A3D62'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
        });
    </script>
<?php endif; ?>

<!-- Sign Up Form Section -->
<main class="flex-grow flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 border border-gray-200">
        <div class="text-center mb-8">
            <div class="mb-4 flex justify-center">
                <svg class="w-16 h-16 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-sac-blue mb-2">Create Supervisor Account</h2>
            <p class="text-gray-600">Join the SAC Cyberian Repository</p>
            <p class="text-sm text-gray-500 mt-2">Register to review and manage student submissions</p>
        </div>

        <!-- Success Message Display -->
        <?php if (!empty($signup_success)): ?>
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg shadow-md" role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold text-base">Success!</p>
                        <p class="text-sm mt-1"><?php echo $signup_success; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message Display -->
        <?php if (!empty($signup_error)): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-md" role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold text-base">Registration Failed</p>
                        <p class="text-sm mt-1"><?php echo htmlspecialchars($signup_error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sign Up Form -->
        <form action="" method="POST" class="space-y-4">
            <!-- First Name and Last Name Row -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                           placeholder="First name"
                           value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                           placeholder="Last name"
                           value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                </div>
            </div>

            <!-- Supervisor ID Field -->
            <div>
                <label for="supervisor_id" class="block text-sm font-medium text-gray-700 mb-1">Supervisor ID</label>
                <input type="text" id="supervisor_id" name="supervisor_id" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Enter your supervisor ID"
                       value="<?php echo isset($supervisor_id) ? htmlspecialchars($supervisor_id) : ''; ?>">
            </div>

            <!-- Department Field -->
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <input type="text" id="department" name="department" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Enter your department"
                       value="<?php echo isset($department) ? htmlspecialchars($department) : ''; ?>">
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Enter your email"
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>

            <!-- Username Field -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Create a username (min. 4 characters)"
                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Enter password (min. 8 characters)">
            </div>

            <!-- Confirm Password Field -->
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Confirm your password">
            </div>

            <!-- Terms and Conditions -->
            <div class="flex items-start">
                <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-sac-blue focus:ring-sac-gold border-gray-300 rounded mt-1" required>
                <label for="terms" class="ml-2 block text-sm text-gray-600">I agree to the <a href="#" class="text-sac-blue hover:text-sac-gold font-medium">Terms and Conditions</a></label>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="w-full bg-sac-blue text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-sac-gold focus:ring-offset-2 transition duration-200 font-semibold">
                    Create Supervisor Account
                </button>
            </div>
        </form>

        <!-- Login Link -->
        <div class="text-center mt-6 space-y-2">
            <p class="text-sm text-gray-600">Already have an account? <a href="login.php" class="text-sac-blue hover:text-sac-gold font-medium">Login here</a></p>
            <p class="text-sm text-gray-600">
                <a href="../../index.php" class="text-gray-500 hover:text-gray-700">← Back to Home</a>
            </p>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-cyber-dark text-white mt-auto py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p>&copy; <?php echo date("Y"); ?> WS102 Final Period - BSIT 4</p>
        <p class="text-gray-400 text-sm mt-2">Preserving Institutional Excellence.</p>
    </div>
</footer>

