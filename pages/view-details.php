<?php
session_start();
include '../includes/connection.php';
include '../includes/header.php';

$view_error = '';
$capstone = null;

// Get submission ID
$capstone_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($capstone_id <= 0) {
    $view_error = 'Invalid submission ID.';
} else {
    // Fetch approved submission (public access)
    $sql = "SELECT id, title, authors, year, department, category, adviser, keywords, abstract, pdf_path, date_submitted, updated_at
            FROM capstones
            WHERE id = ? AND status = 'approved'";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $view_error = 'Database error while fetching submission.';
    } else {
        $stmt->bind_param("i", $capstone_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $capstone = $result->fetch_assoc();
        } else {
            $view_error = 'Submission not found or not available.';
        }

        $stmt->close();
    }
}
?>

<main class="max-w-4xl mx-auto px-4 py-10 min-h-[50vh]">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-sac-blue">Submission Details</h1>
            <a href="search.php" class="text-sm text-gray-600 hover:text-sac-blue transition">&larr; Back to Browse</a>
        </div>

        <?php if (!empty($view_error)): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                <p class="font-semibold text-sm"><?php echo htmlspecialchars($view_error); ?></p>
            </div>
        <?php elseif (!empty($capstone)): ?>
            <div class="space-y-6">
                <!-- Title and Basic Info -->
                <div class="border-b border-gray-200 pb-4">
                    <h2 class="text-xl md:text-2xl font-bold text-sac-blue mb-3">
                        <?php echo htmlspecialchars($capstone['title']); ?>
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                        <?php if (!empty($capstone['category'])): ?>
                            <span class="inline-block bg-sac-blue text-white text-xs px-3 py-1 rounded-full font-semibold">
                                <?php echo htmlspecialchars($capstone['category']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($capstone['year'])): ?>
                            <span><strong>Year:</strong> <?php echo htmlspecialchars($capstone['year']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($capstone['department'])): ?>
                            <span>&bull; <strong>Department:</strong> <?php echo htmlspecialchars($capstone['department']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Authors and Adviser -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (!empty($capstone['authors'])): ?>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Authors</h3>
                            <p class="text-sm text-gray-800"><?php echo htmlspecialchars($capstone['authors']); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($capstone['adviser'])): ?>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Adviser</h3>
                            <p class="text-sm text-gray-800"><?php echo htmlspecialchars($capstone['adviser']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Abstract -->
                <?php if (!empty($capstone['abstract'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Abstract</h3>
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                            <?php echo nl2br(htmlspecialchars($capstone['abstract'])); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Keywords -->
                <?php if (!empty($capstone['keywords'])): ?>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Keywords</h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($capstone['keywords']); ?></p>
                    </div>
                <?php endif; ?>

                <!-- PDF Access -->
                <?php if (!empty($capstone['pdf_path'])): ?>
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Document</h3>
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <a href="../<?php echo htmlspecialchars($capstone['pdf_path']); ?>" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-sac-blue text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View PDF
                            </a>
                        <?php else: ?>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-gray-700 mb-3">To view the full PDF document, please log in to your account.</p>
                                <a href="role-selection.php" class="inline-flex items-center px-4 py-2 bg-sac-gold text-sac-blue font-semibold rounded-lg hover:bg-yellow-400 transition">
                                    Login to View PDF
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Submission Info -->
                <div class="pt-4 border-t border-gray-200 text-xs text-gray-500">
                    <p>
                        <span class="font-semibold">Submitted:</span>
                        <?php
                        $submitted = new DateTime($capstone['date_submitted']);
                        echo $submitted->format('F d, Y');
                        ?>
                        <?php if (!empty($capstone['updated_at'])): ?>
                            &middot; <span class="font-semibold">Last updated:</span>
                            <?php
                            $updated = new DateTime($capstone['updated_at']);
                            echo $updated->format('F d, Y');
                            ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php 
if (isset($conn)) $conn->close();
include '../includes/footer.php'; 
?>

