<?php
include '../../includes/connection.php';
include 'header.php';

$signup_error = '';
$signup_success = '';

$signup_error = '';
$signup_success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($student_id) || empty($email) || empty($username) || empty($password)) {
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
        // Check if username or email already exists in the users table
        // Uses column names consistent with other parts of the app (login & role helpers):
        // id, username, email, password, role, status, student_id, ...
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);

        if ($check_stmt === false) {
            $signup_error = 'Database error while checking existing users.';
        } else {
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
                
                // Insert new student user into users table
                $insert_sql = "INSERT INTO users (first_name, last_name, student_id, email, username, password, role, status) 
                               VALUES (?, ?, ?, ?, ?, ?, 'student', 'active')";
                $insert_stmt = $conn->prepare($insert_sql);

                if ($insert_stmt === false) {
                    $signup_error = 'Database error while creating your account.';
                } else {
                    $insert_stmt->bind_param("ssssss", $first_name, $last_name, $student_id, $email, $username, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        $signup_success = 'Account created successfully! You can now <a href="login.php" class="text-sac-blue font-semibold hover:underline">login here</a>.';
                    } else {
                        $signup_error = 'An error occurred while creating your account. Please try again.';
                    }
                    $insert_stmt->close();
                }
            }
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
                html: 'Your student account has been created successfully.',
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-sac-blue mb-2">Create Student Account</h2>
            <p class="text-gray-600">Join the SAC Cyberian Repository</p>
            <p class="text-sm text-gray-500 mt-2">Register to browse and search capstone projects</p>
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

            <!-- Student ID Field -->
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
                <input type="text" id="student_id" name="student_id" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Enter your student ID"
                       value="<?php echo isset($student_id) ? htmlspecialchars($student_id) : ''; ?>">
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
                    Create Student Account
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
