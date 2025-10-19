# Simple App Store

A full-featured, dynamic app store web application powered by PHP and MySQL. This project includes a user-friendly frontend and a powerful admin panel that allows for the management of nearly all website content and settings. It serves as an ideal platform for hosting and distributing games, software, or any type of digital file.

![App Store Screenshot](https://i.ibb.co/yBNt8d2/app-store-home.png)
*(Feel free to replace this with a screenshot of your own website. The image above is just an example.)*

---

## 🌟 Key Features

The project is divided into two main parts: the frontend for users and a comprehensive admin panel for management.

### Frontend Features

*   **Fully Responsive Design:** Beautifully optimized for all devices, including mobile, tablets, and desktops.
*   **Dynamic Homepage:** Displays all apps, games, and software uploaded from the admin panel.
*   **Category-Based Filtering:** Allows users to easily browse apps in specific categories (e.g., Android Apps, PC Games).
*   **Powerful Search Functionality:** Users can search for any app by its title or description.
*   **Unique Code for Sharing & Searching:** Every app has a unique code, making it easy to find and share specific apps.
*   **Detailed App View (Modal):** Clicking on an app opens a clean pop-up modal with its description, images, embedded YouTube video, and download link.
*   **"Show More" for Descriptions:** For long descriptions, users can click a "Show More" button to read the full text.
*   **"Load More" Functionality:** The homepage initially loads a set number of apps, and users can load more by clicking the "Show More" button, ensuring fast initial page loads.
*   **Dynamic Footer:** Contact details and copyright text in the footer can be managed directly from the admin panel.
*   **Floating WhatsApp Button:** A persistent floating WhatsApp icon allows users to easily get in touch.

### Admin Panel Features

*   **Secure Login System:** A robust and secure authentication system for administrators.
*   **Owner & Admin Roles:** Features two user roles: 'Owner' for full control and 'Admin' for limited access.
*   **First-Time Owner Registration:** The first user to register automatically becomes the 'Owner' of the site.
*   **Security Codes for Recovery:** The Owner account is provided with 5 unique security codes for password recovery.
*   **Invitation System:** The Owner can generate invitation codes to add new Admins.
*   **App Management:**
    *   Full CRUD (Create, Read, Update, Delete) functionality for apps.
    *   **FTP Support for Large Files:** Supports direct uploads for small files and an FTP-based selection method for large files (like games).
    *   **Upload Progress Bar:** A sleek progress bar is displayed during direct file uploads.
*   **Category Management:** Easily create and delete app categories.
*   **User Management:** The Owner can manage and delete other Admin accounts.
*   **Site Settings:** Allows changing the site name and logo.
*   **Footer & Contact Management:** Full control over footer contact links, copyright text, and the WhatsApp button's visibility and number.

---

## 🛠️ Technology Stack

*   **Frontend:** HTML5, CSS3, JavaScript (AJAX, Fetch API)
*   **Backend:** PHP
*   **Database:** MySQL / MariaDB
*   **Web Server:** Apache (XAMPP/WAMP/Live Server)
*   **Icons:** Font Awesome

---

## 🚀 Installation & Setup

Follow these steps to set up the project on your local machine or a live server.

### Prerequisites

*   A web server (e.g., [XAMPP](https://www.apachefriends.org/index.html) or WAMP).
*   Support for PHP and MySQL.
*   A web browser.

### Steps

**1. Download Project Files:**
   Clone this repository or download it as a ZIP file.
   ```bash
   git clone https://github.com/your-username/simple-app-store.git
   ```
   Place the files in a new folder (e.g., `app-store`) inside your web server's root directory (`htdocs` for XAMPP).

**2. Create the Database:**
   *   Navigate to `http://localhost/phpmyadmin` in your browser.
   *   Create a new database named `app_store_db` (use `utf8mb4_general_ci` collation).
   *   Select the newly created database, go to the "SQL" tab, and execute the complete SQL code provided below.

   ```sql
   -- Copy the entire SQL code from this box
   CREATE DATABASE IF NOT EXISTS `app_store_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   USE `app_store_db`;

   CREATE TABLE `admins` ( `id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(50) NOT NULL, `password` varchar(255) NOT NULL, `role` enum('owner','admin') NOT NULL DEFAULT 'admin', PRIMARY KEY (`id`), UNIQUE KEY `username` (`username`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   CREATE TABLE `categories` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   INSERT INTO `categories` (`id`, `name`) VALUES (1, 'Android Games'), (2, 'Android Apps'), (3, 'PC Games'), (4, 'PC Apps');
   CREATE TABLE `apps` ( `id` int(11) NOT NULL AUTO_INCREMENT, `unique_code` varchar(20) DEFAULT NULL, `category_id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `description` text NOT NULL, `image1` varchar(255) DEFAULT NULL, `image2` varchar(255) DEFAULT NULL, `image3` varchar(255) DEFAULT NULL, `youtube_link` varchar(255) DEFAULT NULL, `file_path` varchar(255) NOT NULL, `upload_date` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`), UNIQUE KEY `unique_code` (`unique_code`), KEY `category_id` (`category_id`), CONSTRAINT `apps_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   CREATE TABLE `contact_details` ( `id` int(11) NOT NULL AUTO_INCREMENT, `icon_class` varchar(100) NOT NULL, `link_url` varchar(255) NOT NULL, `display_text` varchar(255) NOT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   CREATE TABLE `invitation_codes` ( `id` int(11) NOT NULL AUTO_INCREMENT, `code` varchar(255) NOT NULL, `is_used` tinyint(1) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), UNIQUE KEY `code` (`code`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   CREATE TABLE `security_codes` ( `id` int(11) NOT NULL AUTO_INCREMENT, `admin_id` int(11) NOT NULL, `code` varchar(10) NOT NULL, PRIMARY KEY (`id`), KEY `admin_id` (`admin_id`), CONSTRAINT `security_codes_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   CREATE TABLE `settings` ( `setting_key` varchar(50) NOT NULL, `setting_value` text NOT NULL, PRIMARY KEY (`setting_key`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('copyright_text', '© 2025 Your App Store. All Rights Reserved.'), ('site_logo', 'assets/images/default_logo.png'), ('site_name', 'My App Store'), ('whatsapp_enabled', '1'), ('whatsapp_number', '');
   ```

**3. Configure Database Connection:**
   *   Open the `config/db.php` file.
   *   Update the `$username` and `$password` variables with your database credentials. (Default for XAMPP is `root` and an empty password `""`).

**4. First-Time Admin Setup:**
   *   Navigate to `http://localhost/app-store/admin/` in your browser.
   *   Since no admin exists, you will be automatically redirected to the owner registration page.
   *   Create your Owner account with a username and password.
   *   **Important:** On the next screen, you will be shown 5 security codes. **Save these codes in a safe place**, as they are required for password recovery.

---

## 📖 Usage

### Uploading Large Files (via FTP)

To bypass server upload limits on free hosting or other restricted environments, you can use the FTP upload method.

1.  Log in to your server using an FTP client like FileZilla.
2.  Navigate to the `uploads/files/` directory in your project's root and create a new folder named `ftp`.
3.  Upload your large files (e.g., `MyGame.zip`) into this `ftp` directory.
4.  Now, go to the "Add New App" page in your admin panel, select the "Select from FTP" option, and choose your uploaded file from the dropdown menu.

---

## 📂 Project Structure

```
/simple-app-store/
|-- admin/                  # All admin panel files
|   |-- add_app.php
|   |-- footer_settings.php
|   |-- manage_apps.php
|   |-- ... (etc.)
|
|-- api/                    # Provides data to the frontend
|   |-- get_apps.php
|
|-- assets/                 # CSS, JS, and images
|   |-- css/style.css
|   |-- js/script.js
|
|-- config/                 # Database configuration
|   |-- db.php
|
|-- uploads/                # User-uploaded files
|   |-- files/
|   |   |-- ftp/            # Place large files here for FTP upload
|   |-- images/
|
|-- index.php               # Main app store homepage
|-- README.md               # This file```

---

## 🤝 Contributing

Contributions are welcome! If you find a bug or want to add a new feature, please open an issue or submit a pull request.

---

## 📜 License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
