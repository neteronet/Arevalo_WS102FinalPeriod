<?php
include '../../includes/login_process.php';
include '../../includes/connection.php';
include 'header.php';
?>

<!-- Login Form Section -->
    <main class="flex-grow flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 border border-gray-200">
            <div class="text-center mb-8">
                <div class="mb-4 flex justify-center">
                    <svg class="w-16 h-16 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-sac-blue mb-2">Student Login</h2>
                <p class="text-gray-600">Access the SAC Cyberian Repository as a Student</p>
                <p class="text-sm text-gray-500 mt-2">Browse and search capstone projects</p>
            </div>

            <!-- Error Message Display -->
            <?php if (!empty($login_error)): ?>
                <div id="error-alert" class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-md" role="alert">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-bold text-base">Login Failed</p>
                            <p class="text-sm mt-1"><?php echo htmlspecialchars($login_error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- The form now uses action="" to post back to this same file -->
            <form action="" method="POST" class="space-y-6">
                <!-- Username/Email Field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Student ID or Email</label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                        placeholder="Enter your student ID or email"
                        value="<?php echo isset($username_or_email) ? htmlspecialchars($username_or_email) : ''; ?>">
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                        placeholder="Enter your password">
                </div>

                <!-- Remember Me and Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-sac-blue focus:ring-sac-gold border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="text-sac-blue hover:text-sac-gold font-medium">Forgot password?</a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-sac-blue text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-sac-gold focus:ring-offset-2 transition duration-200 font-semibold">
                        Sign In as Student
                    </button>
                </div>
            </form>

            <!-- Role Selection and Sign Up Links -->
            <div class="text-center mt-6 space-y-3">
                <p class="text-sm text-gray-600">
                    <a href="../role-selection.php" class="text-sac-blue hover:text-sac-gold font-medium">Switch Role</a>
                </p>
                <p class="text-sm text-gray-600">Don't have an account? <a href="signup.php" class="text-sac-blue hover:text-sac-gold font-medium">Sign up here</a></p>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-4">
                <a href="../../index.php" class="text-sm text-gray-500 hover:text-gray-700">← Back to Home</a>
            </div>
        </div>
    </main>
    <!-- Footer -->
    <footer class="bg-cyber-dark text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; <?php echo date("Y"); ?> WS102 Final Period - BSIT 4</p>
            <p class="text-gray-400 text-sm mt-2">Preserving Institutional Excellence.</p>
        </div>
    </footer>
</body>

</html>