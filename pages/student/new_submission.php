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
    $title      = isset($_POST['title']) ? trim($_POST['title']) : '';
    $authors    = isset($_POST['authors']) ? trim($_POST['authors']) : '';
    $year       = isset($_POST['year']) ? trim($_POST['year']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $category   = isset($_POST['category']) ? trim($_POST['category']) : '';
    $adviser    = isset($_POST['adviser']) ? trim($_POST['adviser']) : '';
    $keywords   = isset($_POST['keywords']) ? trim($_POST['keywords']) : '';
    $abstract   = isset($_POST['abstract']) ? trim($_POST['abstract']) : '';

    // Basic validation aligned with capstones table
    if (
        empty($title) ||
        empty($authors) ||
        empty($year) ||
        empty($department) ||
        empty($category) ||
        empty($abstract)
    ) {
        $submission_error = 'Please fill in all required fields (Title, Authors, Year, Department, Category, Abstract).';
    } elseif (!preg_match('/^\d{4}$/', $year)) {
        $submission_error = 'Please enter a valid 4-digit year (e.g., 2025).';
    } else {
        $student_id = $_SESSION['user_id']; // capstones.student_id references users.id

        // Handle PDF upload (optional but recommended)
        $pdf_path = null;
        if (isset($_FILES['project_pdf']) && $_FILES['project_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['project_pdf']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['application/pdf'];
                if (!in_array(mime_content_type($_FILES['project_pdf']['tmp_name']), $allowed_types)) {
                    $submission_error = 'Only PDF files are allowed.';
                } else {
                    $upload_dir = '../../uploads/capstones/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $safe_filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['project_pdf']['name']));
                    $unique_name = time() . '_' . $safe_filename;
                    $target_path = $upload_dir . $unique_name;

                    if (move_uploaded_file($_FILES['project_pdf']['tmp_name'], $target_path)) {
                        // Store relative path from project root for easier use
                        $pdf_path = 'uploads/capstones/' . $unique_name;
                    } else {
                        $submission_error = 'Failed to upload the PDF file.';
                    }
                }
            } else {
                $submission_error = 'Error uploading PDF file.';
            }
        }

        if (empty($submission_error)) {
            $sql_insert = "INSERT INTO capstones 
                (title, authors, student_id, year, department, category, adviser, keywords, abstract, pdf_path, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql_insert);

            if ($stmt === false) {
                $submission_error = 'Database error while creating submission.';
            } else {
                $stmt->bind_param(
                    "ssisssssss",
                    $title,
                    $authors,
                    $student_id,
                    $year,
                    $department,
                    $category,
                    $adviser,
                    $keywords,
                    $abstract,
                    $pdf_path
                );

                if ($stmt->execute()) {
                    $submission_success = 'Your submission has been created successfully.';
                } else {
                    $submission_error = 'An error occurred while saving your submission. Please try again.';
                }

                $stmt->close();
            }
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

        <form action="" method="POST" class="space-y-6" enctype="multipart/form-data">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <input type="number" id="year" name="year" required min="2000" max="<?php echo date('Y') + 1; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                           placeholder="e.g., 2025"
                           value="<?php echo isset($year) ? htmlspecialchars($year) : ''; ?>">
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <input type="text" id="department" name="department" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                           placeholder="e.g., BSIT"
                           value="<?php echo isset($department) ? htmlspecialchars($department) : ''; ?>">
                </div>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select id="category" name="category" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200">
                    <option value="">Select category</option>
                    <?php
                    $categories = [
                        'IoT & Hardware',
                        'Software Development',
                        'Artificial Intelligence',
                        'Networking & Security',
                        'Game Development',
                        'Management Information Systems'
                    ];
                    foreach ($categories as $cat):
                    ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($category) && $category === $cat) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="adviser" class="block text-sm font-medium text-gray-700 mb-1">Adviser (optional)</label>
                    <input type="text" id="adviser" name="adviser"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                           placeholder="Name of adviser"
                           value="<?php echo isset($adviser) ? htmlspecialchars($adviser) : ''; ?>">
                </div>
                <div>
                    <label for="keywords" class="block text-sm font-medium text-gray-700 mb-1">Keywords (optional)</label>
                    <input type="text" id="keywords" name="keywords"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                           placeholder="Keywords separated by commas"
                           value="<?php echo isset($keywords) ? htmlspecialchars($keywords) : ''; ?>">
                </div>
            </div>

            <div>
                <label for="abstract" class="block text-sm font-medium text-gray-700 mb-1">Abstract</label>
                <textarea id="abstract" name="abstract" rows="5" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                          placeholder="Provide a brief summary of your capstone project"><?php echo isset($abstract) ? htmlspecialchars($abstract) : ''; ?></textarea>
            </div>

            <div>
                <label for="project_pdf" class="block text-sm font-medium text-gray-700 mb-1">Project PDF (optional, PDF only)</label>
                <input type="file" id="project_pdf" name="project_pdf" accept="application/pdf"
                       class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Upload the final capstone document in PDF format.</p>
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


