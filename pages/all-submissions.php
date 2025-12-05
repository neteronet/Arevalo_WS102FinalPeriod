<?php
include '../includes/connection.php';
include '../includes/header.php';

$submissions = [];

if (isset($conn)) {
    $sql = "SELECT id, title, authors, year, department, abstract, category
            FROM capstones
            WHERE status = 'approved'
            ORDER BY date_submitted DESC";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
    }
}
?>

<div class="bg-sac-blue text-white py-10">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold">All Approved Submissions</h2>
        <p class="text-sac-gold">Browse all published research and capstone documents.</p>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-10 min-h-[50vh]">
    <div class="space-y-4">
        <?php if (count($submissions) > 0): ?>
            <?php foreach ($submissions as $doc): ?>
                <div class="bg-white p-6 rounded-lg shadow border border-gray-100 hover:border-sac-gold transition duration-300">
                    <div class="flex flex-col md:flex-row justify-between md:items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <?php if (!empty($doc['category'])): ?>
                                    <span class="inline-block bg-sac-blue text-sac-gold text-xs px-3 py-1 rounded-full font-semibold">
                                        <?php echo htmlspecialchars($doc['category']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($doc['department'])): ?>
                                    <span class="inline-block bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded-full font-semibold">
                                        <?php echo htmlspecialchars($doc['department']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h4 class="text-xl font-bold text-sac-blue hover:underline">
                                <a href="view-details.php?id=<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['title']); ?></a>
                            </h4>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="font-semibold">Authors:</span> <?php echo htmlspecialchars($doc['authors']); ?> &bull;
                                <span class="font-semibold">Year:</span> <?php echo htmlspecialchars($doc['year']); ?>
                            </p>
                            <p class="text-gray-600 mt-2 line-clamp-2">
                                <?php echo htmlspecialchars($doc['abstract']); ?>
                            </p>
                            <div class="mt-3">
                                <a href="view-details.php?id=<?php echo $doc['id']; ?>" class="text-sac-blue text-sm font-medium hover:text-sac-gold transition">
                                    View Details &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-12 bg-gray-100 rounded border border-dashed border-gray-300">
                <p class="text-gray-500 text-lg">No approved submissions are currently available in the repository.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php 
if (isset($conn)) $conn->close();
include '../includes/footer.php'; 
?>


