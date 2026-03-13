<?php
require_once 'config/database.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Profile Picture Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));

    $allowed = array('jpg', 'jpeg', 'png', 'webp');

    if (in_array($file_ext, $allowed)) {
        if ($file_error === 0) {
            if ($file_size <= 2097152) { // 2MB limit
                $file_name_new = "profile_" . $user_id . "_" . time() . "." . $file_ext;
                $file_destination = 'assets/uploads/profile_pictures/' . $file_name_new;

                if (move_uploaded_file($file_tmp, $file_destination)) {
                    // Update database
                    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $file_name_new, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['profile_picture'] = $file_name_new;
                        $message = "Profile picture updated successfully!";
                    } else {
                        $error = "Database update failed.";
                    }
                    $stmt->close();
                } else {
                    $error = "There was an error uploading your file.";
                }
            } else {
                $error = "Your file is too big! Max 2MB.";
            }
        } else {
            $error = "There was an error uploading your file.";
        }
    } else {
        $error = "You cannot upload files of this type. Allowed: jpg, jpeg, png, webp";
    }
}

// Fetch User Info
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="fw-bold mb-4">My Profile</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card bg-dark border-secondary shadow p-4">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <?php
                        $profile_pic = !empty($user['profile_picture'])
                            ? 'assets/uploads/profile_pictures/' . $user['profile_picture']
                            : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=random&size=200';
                    ?>
                    <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="rounded-circle img-thumbnail mb-3" style="width: 200px; height: 200px; object-fit: cover; border: 4px solid var(--accent-color); background-color: var(--card-bg);">
                </div>
                <div class="col-md-8">
                    <h3 class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p class="text-muted"><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>

                    <hr class="border-secondary">

                    <form action="profile.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_pic" class="form-label">Update Profile Picture</label>
                            <input class="form-control bg-dark text-white border-secondary" type="file" id="profile_pic" name="profile_pic" required>
                            <div class="form-text text-muted">Max size: 2MB. Format: JPG, PNG, WEBP.</div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i> Upload Picture</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="stats.php" class="btn btn-outline-light me-2"><i class="fas fa-chart-bar me-2"></i> View My Stats</a>
            <a href="watchlist.php" class="btn btn-outline-light"><i class="fas fa-list me-2"></i> My Watchlist</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
