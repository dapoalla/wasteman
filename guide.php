<?php
$title = "User Guide";
require_once "header.php";
require_once "db.php";

$message = '';
$is_admin = $_SESSION['role'] === 'admin';

// Handle form submission for admin
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    $stmt = $conn->prepare("UPDATE guides SET content = ? WHERE id = 1");
    $stmt->bind_param("s", $content);
    if ($stmt->execute()) {
        $message = "Guide updated successfully!";
    } else {
        $message = "Error updating guide.";
    }
    $stmt->close();
}

// Fetch guide content
$result = $conn->query("SELECT content FROM guides WHERE id = 1");
$guide = $result->fetch_assoc();
$content = $guide['content'] ?? 'No guide content has been set yet.';

// A simple markdown parser for display
function parse_markdown($text) {
    $text = htmlspecialchars($text);
    // Headers
    $text = preg_replace('/^## (.*)$/m', '<h3 class="text-xl font-semibold mt-4 mb-2 text-white">$1</h3>', $text);
    // Bold
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\\*(.*?)\\*/s', '<em>$1</em>', $text);
    // Lists
    $text = preg_replace('/^- (.*)$/m', '<li class="ml-6">$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul class="list-disc list-inside text-gray-300 mb-4">$1</ul>', $text);
    // Paragraphs
    $text = '<p>' . str_replace("\n\n", '</p><p>', $text) . '</p>';
    $text = str_replace("\n", '<br>', $text);
    $text = str_replace("<p><br>", "<p>", $text);
    $text = str_replace("<ul><br>", "<ul>", $text);
    return $text;
}
?>

<div class="container mx-auto max-w-4xl">
    <h2 class="text-3xl font-bold text-white mb-6">User Guide</h2>
    
    <?php if ($message): ?>
    <div class="mb-4 p-4 rounded-lg bg-green-500/20 text-green-300">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-8">
        <?php if ($is_admin): ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="content" class="block mb-2 text-sm font-medium text-gray-300">Guide Content (Markdown supported)</label>
                    <textarea id="content" name="content" rows="16" class="bg-gray-900 border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5 font-mono"><?php echo htmlspecialchars($content); ?></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-2.5">
                        Save Guide
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="prose prose-invert max-w-none">
                <?php echo parse_markdown($content); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once "footer.php";
?>