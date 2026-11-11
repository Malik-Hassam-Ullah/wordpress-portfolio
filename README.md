# Malik Hassam Ullah — Modern WordPress Portfolio

A professional, high-performance, and elegant portfolio website for **Malik Hassam Ullah** (Computer Science Student, 7th Semester @ IST, KICSIT). Built on **WordPress** powered by the **Astra Theme**, **Elementor Website Builder**, and a custom companion plugin **Hassam Portfolio Core**.

---

## 🚀 Live Demo & Screenshots

* **Localhost URL:** `http://localhost/portfolio/`
* **WordPress Admin:** `http://localhost/portfolio/wp-admin/`
  * **Default Admin Username:** `admin`
  * **Default Admin Password:** `password`

---

## 🛠️ Tech Stack & Architecture

* **CMS:** WordPress 6.8+
* **Theme:** Astra Theme (Clean, responsive layout with custom design tokens)
* **Page Builder:** Elementor Website Builder (Drag & Drop visual editing)
* **Custom Companion Plugin:** `hassam-portfolio-core`
* **Database:** MySQL 8.0 / MariaDB
* **Languages & Standards:** PHP 8.0+, HTML5, CSS3, JavaScript (ES6+), FontAwesome 6
* **Typography:** Space Grotesk & Outfit Google Fonts

---

## ✨ Key Features

1. **Clean & Modern WordPress UI:**
   * No heavy particle backgrounds or distracting animations — clean white and slate aesthetic with Astra's signature royal blue accents.
   * Responsive across Desktop, Tablet, and Mobile devices.

2. **Hero Section:**
   * Headline: *Hi, I'm Malik Hassam Ullah*
   * Subtitle: *I am a Computer Science Student* (7th Semester @ IST, KICSIT).
   * Call to action buttons: *Explore Projects*, *Contact Me*, and *GitHub Profile*.

3. **Stats Counter:**
   * 9 Major Projects Built
   * 12 Tech Stack Mastered
   * 7th Semester @ IST KICSIT
   * ZRAB WordPress Certified

4. **About Me:**
   * Detailed professional profile and contact chips.
   * 4 engineering pillars: *Modular Architecture*, *AI & NLP Integration*, *3D Graphics & Game Loops*, and *Full-Stack & Mobile Ecosystems*.

5. **Top 3 Skills & Technologies:**
   * **Python & Django** (Advanced 90%) — Full-stack, ORM, AI models, Pygame/OpenGL.
   * **Web & WordPress** (Expert 92%) — Elementor, HTML5/CSS3, responsive UI.
   * **Android & Systems** (Proficient 85%) — Android Studio, Firebase, C# .NET, C++ OOP.

6. **Top 3 Featured Projects:**
   * **3D Tetris Game** (Python, Pygame, OpenGL, VPython)
   * **Smart Notes — Android App** (Android, Firebase, Retrofit, Kotlin/Java)
   * **Django Web Application** (Python, Django, MySQL, Bootstrap)

7. **WordPress Dashboard Management ("Portfolio Hub"):**
   * Dedicated **Portfolio Hub** dashboard panel (`wp-admin/admin.php?page=portfolio-hub`) providing an admin overview with stats, skills, projects, and live frontend preview.
   * **Custom Post Type — Projects:** Manage, edit, and add new projects directly from WordPress Admin.
   * **Inquiries / Messages Inbox:** Contact form submissions are automatically saved and displayed inside WordPress Admin (`Messages / Inquiries`).

---

## 📦 Setup & Installation Instructions

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) (Apache + MySQL with PHP 8.0+)
* Git

### Step-by-Step Setup
1. **Clone this repository into your XAMPP htdocs folder:**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/Malik-Hassam-Ullah/wordpress-portfolio.git portfolio
   ```

2. **Create MySQL Database & Import Dump:**
   * Open phpMyAdmin (`http://localhost/phpmyadmin/`) or MySQL CLI.
   * Create a database named `portfolio`:
     ```sql
     CREATE DATABASE portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   * Import the included `portfolio_database.sql` file:
     ```bash
     C:\xampp\mysql\bin\mysql.exe -u root portfolio < C:\xampp\htdocs\portfolio\portfolio_database.sql
     ```

3. **Verify Database Credentials:**
   * Check `wp-config.php`:
     ```php
     define( 'DB_NAME', 'portfolio' );
     define( 'DB_USER', 'root' );
     define( 'DB_PASSWORD', '' );
     define( 'DB_HOST', 'localhost' );
     ```

4. **Run the Project:**
   * Start Apache and MySQL from XAMPP Control Panel.
   * Open your browser and navigate to:
     * **Website:** `http://localhost/portfolio/`
     * **Admin Panel:** `http://localhost/portfolio/wp-login.php`

---

## 👨‍💻 Author & Connect

* **Author:** Malik Hassam Ullah
* **GitHub:** [@Malik-Hassam-Ullah](https://github.com/Malik-Hassam-Ullah)
* **LinkedIn:** [Malik Hassam](https://linkedin.com/in/malik-hassam-6383b1287)
* **Email:** hassammalik638@gmail.com
* **Phone / WhatsApp:** +92 306 5520204
