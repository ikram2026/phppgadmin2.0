# phpPgAdmin 2.0 (Modernized) 🐘⚡

A modernized, community-driven reboot of the classic phpPgAdmin web-based administration tool for PostgreSQL. 

The original phpPgAdmin project has been unmaintained for years and fails to run on modern infrastructure. This **2.0 Fork** explicitly addresses compatibility gaps to support modern PHP environments, modern Linux distributions, and the latest major releases of PostgreSQL.

---

## 🚀 Key Improvements in 2.0
* **Modern PostgreSQL Support:** Fully compatible with PostgreSQL 13, 14, 15, 16, and 17+. (Fixed the legacy double-digit version parsing errors).
* **Modern PHP Compatibility:** Rewritten to natively support PHP 8.1, 8.2, and 8.3+. (Removed deprecated functions, strict type warnings, and `each()` loops).
* **Modern OS Deployment:** Built to deploy seamlessly on current Linux environments (Ubuntu 24.04 LTS, Debian 12, RHEL 9+).

---

## 📋 Requirements
* **PHP:** 8.1 or higher (with `php-pgsql` and `php-mbstring` extensions)
* **PostgreSQL:** 13.x to 17.x+
* **Web Server:** Apache, Nginx, or PHP's built-in development server

---

## 🛠️ Quick Start Installation

### 1. Clone the Repository
```bash
git clone https://github.com
cd phppgadmin2.0
```

### 2. Configure Database Connection
Copy the sample configuration file and update your server parameters:
```bash
cp conf/config.inc.php-dist conf/config.inc.php
```
Open `conf/config.inc.php` in your editor to add your PostgreSQL server IP and settings.

### 3. Run Locally (Testing)
You can quickly spin up the application using PHP's built-in web server:
```bash
php -S localhost:8000
```
Open your browser and navigate to `http://localhost:8000` to log in.

---

## 🤝 Contributing
Contributions are highly welcome! If you encounter specific bugs with modern PostgreSQL features or PHP 8+ strict modes, please:
1. Fork this repository.
2. Create a feature branch (`git checkout -b feature/AmazingFeature`).
3. Commit your changes (`git commit -m 'Fix: Resolve connection error on PG17'`).
4. Push to the branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

---

## 📄 License
This project is licensed under the GNU General Public License (GPL) - see the `LICENSE` file for details.
