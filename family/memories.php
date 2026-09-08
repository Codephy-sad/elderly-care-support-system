<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$connections = getFamilyConnections($pdo, $user_id);

if (empty($connections)) {
    header('Location: index.php');
    exit;
}

$selected_elderly_id = isset($_GET['elderly_id']) ? (int)$_GET['elderly_id'] : (int)$connections[0]['elderly_profile_id'];
$active_connection = null;
foreach ($connections as $conn) {
    if ((int)$conn['elderly_profile_id'] === $selected_elderly_id) {
        $active_connection = $conn;
        break;
    }
}
if (!$active_connection) {
    $active_connection = $connections[0];
    $selected_elderly_id = (int)$active_connection['elderly_profile_id'];
}
$elderly_name = $active_connection['elderly_name'];

$success_msg = '';
$error_msg = '';

// Handle Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_memory') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $error_msg = "Please provide a title for the memory.";
    } elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = "Please select a valid image file to upload.";
    } else {
        $upload_dir = '../assets/uploads/memories/';
        // Ensure directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed extensions
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error_msg = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            // Generate unique filename
            $new_file_name = uniqid('memory_') . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $db_path = 'assets/uploads/memories/' . $new_file_name;
                
                $stmt = $pdo->prepare("INSERT INTO memories (elderly_profile_id, uploaded_by, title, description, file_path) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$selected_elderly_id, $user_id, $title, $description, $db_path])) {
                    $success_msg = "Memory uploaded successfully!";
                } else {
                    $error_msg = "Database error while saving memory.";
                }
            } else {
                $error_msg = "Failed to move uploaded file.";
            }
        }
    }
}

// Handle Delete Memory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_memory') {
    $memory_id = (int)$_POST['memory_id'];
    
    // Check if user owns this memory
    $stmt = $pdo->prepare("SELECT file_path FROM memories WHERE id = ? AND uploaded_by = ?");
    $stmt->execute([$memory_id, $user_id]);
    $memory = $stmt->fetch();
    
    if ($memory) {
        // Delete file
        $file_to_delete = '../' . $memory['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        
        // Delete record
        $stmt = $pdo->prepare("DELETE FROM memories WHERE id = ?");
        $stmt->execute([$memory_id]);
        $success_msg = "Memory deleted.";
    } else {
        $error_msg = "You do not have permission to delete this memory.";
    }
}

// Fetch all memories for this elderly person
$stmt = $pdo->prepare("
    SELECT m.*, u.name as uploader_name, u.role_id as uploader_role
    FROM memories m
    LEFT JOIN users u ON u.id = m.uploaded_by
    WHERE m.elderly_profile_id = ?
    ORDER BY m.upload_date DESC
");
$stmt->execute([$selected_elderly_id]);
$memories = $stmt->fetchAll();

$pageTitle = 'Photos & Memories - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Photos & Memories</h1>
        <p class="text-muted mb-0">Shared moments with <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#uploadMemoryModal">
            <i class="bi bi-cloud-upload me-1"></i> Upload Photo
        </button>
        <a href="index.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success border-0 shadow-sm"><?php echo sanitize($success_msg); ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?php echo sanitize($error_msg); ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php if (count($memories) > 0): ?>
        <?php foreach ($memories as $memory): ?>
            <div class="col-sm-6 col-md-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm overflow-hidden memory-card">
                    <img src="../<?php echo sanitize($memory['file_path']); ?>" class="card-img-top" alt="<?php echo sanitize($memory['title']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title h6 fw-bold mb-1"><?php echo sanitize($memory['title']); ?></h5>
                        <p class="text-muted small mb-2">
                            By <?php echo sanitize($memory['uploader_name'] ?? 'Unknown'); ?> on <?php echo date('M j, Y', strtotime($memory['upload_date'])); ?>
                        </p>
                        <?php if (!empty($memory['description'])): ?>
                            <p class="card-text small mb-0"><?php echo sanitize($memory['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($memory['uploaded_by'] == $user_id): ?>
                        <div class="card-footer bg-white border-top-0 pt-0 text-end">
                            <form method="POST" onsubmit="return confirm('Delete this memory?');">
                                <input type="hidden" name="action" value="delete_memory">
                                <input type="hidden" name="memory_id" value="<?php echo $memory['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <h4 class="text-muted fw-normal mb-3"><i class="bi bi-image fs-1 d-block mb-2 text-light-brand"></i> No Memories Yet</h4>
                    <p class="text-muted mb-0">Upload the first photo to start building a memory album for <?php echo sanitize($elderly_name); ?>.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Memory Modal -->
<div class="modal fade" id="uploadMemoryModal" tabindex="-1" aria-labelledby="uploadMemoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-light border-bottom-0">
            <h5 class="modal-title h6 fw-bold" id="uploadMemoryModalLabel">Upload a Memory</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="action" value="upload_memory">
            
            <div class="mb-3">
                <label for="photo" class="form-label fw-bold">Select Photo <span class="text-danger">*</span></label>
                <input class="form-control bg-light border-0" type="file" id="photo" name="photo" accept="image/png, image/jpeg, image/gif" required>
            </div>
            
            <div class="mb-3">
                <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-light border-0" id="title" name="title" required maxlength="100" placeholder="e.g. Birthday Celebration">
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description (Optional)</label>
                <textarea class="form-control bg-light border-0" id="description" name="description" rows="3" placeholder="Add a little story or context..."></textarea>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-brand">Upload</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
