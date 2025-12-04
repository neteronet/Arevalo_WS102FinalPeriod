<?php
include '../includes/connection.php';
include '../includes/header.php';

$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$results = [];

if (isset($conn) && !empty($search_query)) {
    // SECURITY: Use Prepared Statements for search
    // We search across Title, Authors, and Keywords
    $sql = "SELECT * FROM capstones 
            WHERE status='approved' 
            AND (title LIKE ? OR authors LIKE ? OR keywords LIKE ?)";
    
    $stmt = $conn->prepare($sql);
    $param = "%" . $search_query . "%";
    $stmt->bind_param("sss", $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
?>

<div class="bg-sac-blue text-white py-10">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold">Search Repository</h2>
        <p class="text-sac-gold">Find intellectual output.</p>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-10 min-h-[50vh]">
    <!-- Search Form Re-entry -->
    <form action="search.php" method="GET" class="mb-10 flex gap-2">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" 
               class="flex-1 p-3 border border-gray-300 rounded focus:ring-2 focus:ring-sac-blue focus:outline-none"
               placeholder="Enter keywords...">
        <button type="submit" class="bg-sac-blue text-white px-6 py-3 rounded hover:bg-blue-800 font-bold">
            Refine Search
        </button>
    </form>

    <!-- Results -->
    <div class="space-y-6">
        <?php if (!empty($search_query)): ?>
            <p class="mb-4 text-gray-600">Found <strong><?php echo count($results); ?></strong> results for "<?php echo htmlspecialchars($search_query); ?>"</p>
            
            <?php foreach ($results as $row): ?>
                <div class="bg-white p-6 rounded shadow hover:shadow-md transition border-l-4 border-sac-gold">
                    <h3 class="text-xl font-bold text-sac-blue">
                        <a href="#" class="hover:underline"><?php echo htmlspecialchars($row['title']); ?></a>
                    </h3>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2">
                        <span><strong>Author(s):</strong> <?php echo htmlspecialchars($row['authors']); ?></span>
                        <span><strong>Year:</strong> <?php echo htmlspecialchars($row['year']); ?></span>
                        <span><strong>Adviser:</strong> <?php echo htmlspecialchars($row['adviser']); ?></span>
                    </div>
                    <p class="mt-3 text-gray-700 italic">"<?php echo substr(htmlspecialchars($row['abstract']), 0, 150) . '...'; ?>"</p>
                    <div class="mt-4">
                         <button onclick="Swal.fire('Access Restricted', 'Please login to download full PDF.', 'info')" class="text-sm text-sac-blue font-bold border border-sac-blue px-3 py-1 rounded hover:bg-sac-blue hover:text-white transition">
                            Request Access
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (count($results) === 0): ?>
                <div class="text-center py-12 bg-gray-100 rounded border border-dashed border-gray-300">
                    <p class="text-gray-500 text-lg">No documents found matching your criteria.</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-gray-500">Enter a keyword above to start searching.</p>
        <?php endif; ?>
    </div>
</main>

<?php 
if (isset($conn)) $conn->close();
include '../includes/footer.php'; 
?>