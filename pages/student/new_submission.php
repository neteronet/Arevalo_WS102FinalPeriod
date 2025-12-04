<?php
session_start();
include '../../includes/connection.php';
include 'dashboard-header.php';

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] ?? '') !== 'student') {
    header("Location: login.php");
    exit;
}

$submission_error = '';
$submission_success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
    $authors = isset($_POST['authors']) ? trim($_POST['authors']) : '';

    if (empty($title) || empty($authors)) {
        $submission_error = 'Title and authors are required.';
    } else {
        $student_id = $_SESSION['user_id']; // capstones table uses student_id referencing users.id in dashboard

        $sql_insert = "INSERT INTO capstones (title, authors, student_id, status) 
                       VALUES (?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql_insert);

        if ($stmt === false) {
            $submission_error = 'Database error while creating submission.';
        } else {
            $stmt->bind_param("ssi", $title, $authors, $student_id);

            if ($stmt->execute()) {
                $submission_success = 'Your submission has been created successfully.';
            } else {
                $submission_error = 'An error occurred while saving your submission. Please try again.';
            }

            $stmt->close();
        }
    }
}
?>

<?php if (!empty($submission_success)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Submission Created',
                html: 'Your capstone submission has been created and is now pending review.',
                showCancelButton: true,
                confirmButtonText: 'Go to Dashboard',
                cancelButtonText: 'Create Another',
                confirmButtonColor: '#0A3D62'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'dashboard.php';
                }
            });
        });
    </script>
<?php endif; ?>

<!-- New Submission Form -->
<main class="flex-grow max-w-3xl mx-auto w-full px-4 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
        <h1 class="text-3xl font-bold text-sac-blue mb-2">New Capstone Submission</h1>
        <p class="text-gray-600 mb-6">Provide the basic details of your capstone project.</p>

        <?php if (!empty($submission_error)): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-md" role="alert">
                <p class="font-bold text-base">Submission Failed</p>
                <p class="text-sm mt-1"><?php echo htmlspecialchars($submission_error); ?></p>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Project Title</label>
                <input type="text" id="title" name="title" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="Enter the capstone title"
                       value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
            </div>

            <div>
                <label for="authors" class="block text-sm font-medium text-gray-700 mb-1">Authors</label>
                <input type="text" id="authors" name="authors" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                       placeholder="List the author(s) separated by commas"
                       value="<?php echo isset($authors) ? htmlspecialchars($authors) : ''; ?>">
            </div>

            <div class="flex items-center justify-between">
                <a href="dashboard.php" class="text-sm text-gray-600 hover:text-gray-800">← Back to Dashboard</a>
                <button type="submit"
                        class="px-6 py-3 bg-sac-blue text-white font-semibold rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-sac-gold focus:ring-offset-2 transition duration-200">
                    Submit Project
                </button>
            </div>
        </form>
    </div>
</main>

<footer class="bg-cyber-dark text-white mt-auto py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p>&copy; <?php echo date("Y"); ?> WS102 Final Period - BSIT 4</p>
        <p class="text-gray-400 text-sm mt-2">Preserving Institutional Excellence.</p>
    </div>
</footer>

<?php
if (isset($conn)) $conn->close();
?>
</body>
</html>


