<?php
include '../../includes/connection.php';
include '../../includes/header.php';

$signup_error = '';
$signup_success = '';

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
                $signup_success = 'Account created successfully! You can now <a href="login.php" class="text-sac-blue font-semibold hover:underline">login here</a>.';
            } else {
                $signup_error = 'An error occurred while creating your account. Please try again.';
            }
            $insert_stmt->close();
        }
    }
}
?>

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
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert">
                <p class="font-bold">Success!</p>
                <p class="text-sm"><?php echo $signup_success; ?></p>
            </div>
        <?php endif; ?>

        <!-- Error Message Display -->
        <?php if (!empty($signup_error)): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
                <p class="font-bold">Registration Failed</p>
                <p class="text-sm"><?php echo htmlspecialchars($signup_error); ?></p>
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

<?php
if (isset($conn)) $conn->close();
include '../../includes/footer.php'; 
?>
