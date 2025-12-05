<?php
include '../includes/connection.php';
include '../includes/header.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search_field = isset($_GET['field']) ? $_GET['field'] : 'all';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$results = [];

if (isset($conn)) {
    // Base query: only approved capstones
    $sql = "SELECT * FROM capstones WHERE status = 'approved'";
    $params = [];
    $types  = '';

    // Optional category filter (from sidebar subjects)
    if ($selected_category !== 'all' && $selected_category !== '') {
        $sql .= " AND category = ?";
        $params[] = $selected_category;
        $types   .= 's';
    }

    // Optional search filter based on selected field
    if ($search_query !== '') {
        $like = '%' . $search_query . '%';
        
        if ($search_field === 'all') {
            // Search in all fields
            $sql .= " AND (title LIKE ? OR authors LIKE ? OR keywords LIKE ? OR adviser LIKE ? OR department LIKE ? OR abstract LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types   .= 'ssssss';
        } else {
            // Search in specific field
            $field_map = [
                'title' => 'title',
                'keywords' => 'keywords',
                'category' => 'category',
                'authors' => 'authors',
                'adviser' => 'adviser',
                'department' => 'department',
                'year' => 'year',
                'abstract' => 'abstract'
            ];
            
            if (isset($field_map[$search_field])) {
                $field = $field_map[$search_field];
                if ($field === 'year') {
                    // For year, use exact match or LIKE for partial year
                    $sql .= " AND year LIKE ?";
                    $params[] = $like;
                    $types   .= 's';
                } else {
                    $sql .= " AND " . $field . " LIKE ?";
                    $params[] = $like;
                    $types   .= 's';
                }
            }
        }
    }

    // Sort options
    $sort_options = [
        'newest' => 'date_submitted DESC',
        'oldest' => 'date_submitted ASC',
        'title_asc' => 'title ASC',
        'title_desc' => 'title DESC',
        'year_newest' => 'year DESC, date_submitted DESC',
        'year_oldest' => 'year ASC, date_submitted ASC'
    ];
    
    $order_by = isset($sort_options[$sort_by]) ? $sort_options[$sort_by] : $sort_options['newest'];
    $sql .= " ORDER BY " . $order_by;

    // Prepare and execute
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        $stmt->close();
    }
}
?>

<div class="bg-sac-blue text-white py-12 border-b-4 border-sac-gold">
    <div class="max-w-7xl mx-auto px-4">
        <div class="max-w-2xl mb-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-2">Search Repository</h2>
            <p class="text-gray-200 text-sm md:text-base leading-relaxed">
            Find intellectual output.
            </p>
        </div>
        <!-- Search Bar with Field Dropdown -->
        <form action="search.php" method="GET" class="max-w-4xl">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 flex gap-2">
                    <input type="text" 
                           name="q" 
                           value="<?php echo htmlspecialchars($search_query); ?>" 
                           placeholder="Enter search term..." 
                           class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:ring-2 focus:ring-sac-gold focus:outline-none text-sm md:text-base">
                    <select name="field" 
                            class="px-4 py-3 rounded-lg text-gray-900 focus:ring-2 focus:ring-sac-gold focus:outline-none text-sm md:text-base bg-white border border-gray-300">
                        <option value="all" <?php echo $search_field === 'all' ? 'selected' : ''; ?>>All Fields</option>
                        <option value="title" <?php echo $search_field === 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="keywords" <?php echo $search_field === 'keywords' ? 'selected' : ''; ?>>Keywords</option>
                        <option value="category" <?php echo $search_field === 'category' ? 'selected' : ''; ?>>Category</option>
                        <option value="authors" <?php echo $search_field === 'authors' ? 'selected' : ''; ?>>Authors</option>
                        <option value="adviser" <?php echo $search_field === 'adviser' ? 'selected' : ''; ?>>Adviser</option>
                        <option value="department" <?php echo $search_field === 'department' ? 'selected' : ''; ?>>Department</option>
                        <option value="year" <?php echo $search_field === 'year' ? 'selected' : ''; ?>>Year</option>
                        <option value="abstract" <?php echo $search_field === 'abstract' ? 'selected' : ''; ?>>Abstract</option>
                    </select>
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-sac-gold text-sac-blue font-bold rounded-lg hover:bg-yellow-400 transition text-sm md:text-base whitespace-nowrap">
                    Search
                </button>
            </div>
            <?php if ($selected_category !== 'all'): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
            <?php endif; ?>
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
        </form>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 py-10 min-h-[50vh]">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Left Sidebar: Search + Subjects -->
        <aside class="md:col-span-1 space-y-6">
            <!-- Search Form -->
            <form action="search.php" method="GET" class="bg-white rounded-lg shadow-md border border-gray-200 p-4 space-y-3">
                <div>
                    <label for="search" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">
                        Search the repository
                    </label>
                    <input id="search" type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" 
                           class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sac-blue focus:outline-none text-sm"
                           placeholder="Search by title, author, adviser, or keywords...">
                </div>
                <input type="hidden" name="field" value="<?php echo htmlspecialchars($search_field); ?>">
                <?php if ($selected_category !== 'all'): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                <?php endif; ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                <button type="submit" class="w-full bg-sac-blue text-white px-4 py-2.5 rounded-lg hover:bg-blue-800 font-semibold text-sm shadow-sm">
                    Search
                </button>
            </form>

            <!-- Subjects / Categories -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-1">Subjects</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Browse categories of research and capstone documentation.
                </p>
                <ul class="space-y-1 text-sm text-gray-700">
                    <li>
                        <span class="font-semibold text-sac-blue text-xs uppercase tracking-wide">Information Technology</span>
                    </li>
                    <li class="mt-1">
                        <a href="?category=Software%20Development<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) . '&field=' . urlencode($search_field) : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" class="block py-1 hover:text-sac-gold">
                            Software Development
                        </a>
                    </li>
                    <li>
                        <a href="?category=Artificial%20Intelligence<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) . '&field=' . urlencode($search_field) : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" class="block py-1 hover:text-sac-gold">
                            Artificial Intelligence
                        </a>
                    </li>
                    <li>
                        <a href="?category=Networking%20%26%20Security<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) . '&field=' . urlencode($search_field) : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" class="block py-1 hover:text-sac-gold">
                            Networking &amp; Security
                        </a>
                    </li>
                    <li>
                        <a href="?category=IoT%20%26%20Hardware<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) . '&field=' . urlencode($search_field) : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" class="block py-1 hover:text-sac-gold">
                            IoT &amp; Hardware
                        </a>
                    </li>
                    <li>
                        <a href="?category=Game%20Development<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) . '&field=' . urlencode($search_field) : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" class="block py-1 hover:text-sac-gold">
                            Game Development
                        </a>
                    </li>
                    <li>
                        <a href="?category=Management%20Information%20Systems<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) . '&field=' . urlencode($search_field) : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" class="block py-1 hover:text-sac-gold">
                            Management Information Systems
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Right: Results -->
        <section class="md:col-span-3">
            <div class="space-y-6">
                <?php
                $total = count($results);
                ?>

                <!-- Sort and Results Count -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <p class="text-gray-600 text-sm">
                    <?php if (!empty($search_query) || $selected_category !== 'all'): ?>
                        Showing <strong><?php echo $total; ?></strong>
                        <?php echo $total === 1 ? 'result' : 'results'; ?>
                        <?php if (!empty($search_query)): ?>
                            for "<span class="font-semibold"><?php echo htmlspecialchars($search_query); ?></span>"
                        <?php endif; ?>
                        <?php if ($selected_category !== 'all'): ?>
                            in category "<span class="font-semibold"><?php echo htmlspecialchars($selected_category); ?></span>"
                        <?php endif; ?>
                    <?php else: ?>
                        Showing <strong><?php echo $total; ?></strong>
                        <?php echo $total === 1 ? 'latest submission' : 'latest submissions'; ?>
                        in the repository.
                    <?php endif; ?>
                    </p>
                    
                    <!-- Sort By Dropdown -->
                    <form method="GET" action="search.php" class="flex items-center gap-2">
                        <label for="sort" class="text-sm text-gray-600 font-medium whitespace-nowrap">Sort by:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-sac-blue focus:outline-none bg-white">
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="title_asc" <?php echo $sort_by === 'title_asc' ? 'selected' : ''; ?>>Title A-Z</option>
                            <option value="title_desc" <?php echo $sort_by === 'title_desc' ? 'selected' : ''; ?>>Title Z-A</option>
                            <option value="year_newest" <?php echo $sort_by === 'year_newest' ? 'selected' : ''; ?>>Year Newest</option>
                            <option value="year_oldest" <?php echo $sort_by === 'year_oldest' ? 'selected' : ''; ?>>Year Oldest</option>
                        </select>
                        <?php if (!empty($search_query)): ?>
                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        <?php endif; ?>
                        <?php if ($search_field !== 'all'): ?>
                            <input type="hidden" name="field" value="<?php echo htmlspecialchars($search_field); ?>">
                        <?php endif; ?>
                        <?php if ($selected_category !== 'all'): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                        <?php endif; ?>
                    </form>
                </div>

                <?php if ($total > 0): ?>
                    <?php foreach ($results as $row): ?>
                        <div class="bg-white p-6 rounded shadow hover:shadow-md transition border-l-4 border-sac-gold">
                            <h3 class="text-xl font-bold text-sac-blue">
                                <a href="view-details.php?id=<?php echo $row['id']; ?>" class="hover:underline"><?php echo htmlspecialchars($row['title']); ?></a>
                            </h3>
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 mt-2">
                                <span><strong>Author(s):</strong> <?php echo htmlspecialchars($row['authors']); ?></span>
                                <span><strong>Year:</strong> <?php echo htmlspecialchars($row['year']); ?></span>
                                <span><strong>Adviser:</strong> <?php echo htmlspecialchars($row['adviser']); ?></span>
                            </div>
                            <p class="mt-3 text-gray-700 italic">"<?php echo substr(htmlspecialchars($row['abstract']), 0, 150) . '...'; ?>"</p>
                            <div class="mt-4">
                                <a href="view-details.php?id=<?php echo $row['id']; ?>" class="text-sm text-sac-blue font-medium hover:text-sac-gold transition">
                                    View Details &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12 bg-gray-100 rounded border border-dashed border-gray-300">
                        <p class="text-gray-500 text-lg">No documents found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php 
if (isset($conn)) $conn->close();
include '../includes/footer.php'; 
?>