<?php
require_once 'config/db.php';

// Fetch site settings
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$site_name = $settings['site_name'] ?? 'App Store';
$site_logo = $settings['site_logo'] ?? '';
$copyright_text = $settings['copyright_text'] ?? '© ' . date("Y") . ' All Rights Reserved.';
$whatsapp_number = $settings['whatsapp_number'] ?? '';
$whatsapp_enabled = $settings['whatsapp_enabled'] ?? '0';

// Fetch contact details
$contacts_result = $conn->query("SELECT * FROM contact_details");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome CDN যোগ করা হয়েছে আইকন দেখানোর জন্য -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="background-gradient"></div>
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($site_name); ?></h1>
            <div class="search-container">
                <?php if (!empty($site_logo)): ?>
                    <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" class="search-bar-logo">
                <?php endif; ?>
                <input type="text" id="searchInput" placeholder="Search for apps and games...">
            </div>
        </header>

        <nav id="categoryTabs"></nav>
        <main id="appGrid"></main>
    </div>

    <!-- নতুন Footer অংশ যোগ করা হয়েছে -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="contact-info">
                <h3>Contact Us</h3>
                <ul>
                    <?php while($contact = $contacts_result->fetch_assoc()): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($contact['link_url']); ?>" target="_blank">
                                <i class="<?php echo htmlspecialchars($contact['icon_class']); ?>"></i> <?php echo htmlspecialchars($contact['display_text']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="copyright-info">
                <p><?php echo htmlspecialchars($copyright_text); ?></p>
            </div>
        </div>
    </footer>

    <!-- Modal for App Details -->
    <div id="appModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div id="modalBody"></div>
        </div>
    </div>

    <!-- নতুন WhatsApp বাটন যোগ করা হয়েছে -->
    <?php if ($whatsapp_enabled == '1' && !empty($whatsapp_number)): ?>
        <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_number); ?>" class="whatsapp-float" target="_blank">
            <i class="fab fa-whatsapp"></i>
        </a>
    <?php endif; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>