# 🌐 Cloudflare DNS Hub

A powerful and developer-friendly application to manage Cloudflare DNS, zones, and users from a single dashboard.

---

## 🚀 Overview

**Cloudflare DNS Hub** is a complete management system that allows you to handle multiple domains (zones), manage DNS records, and control user access — all in one place.

This tool is designed for developers, system administrators, and SaaS platforms যারা Cloudflare automation করতে চান।

---

## ✨ Features

### 🌍 Multiple Domain (Zone) Management

* Add unlimited Cloudflare zones
* Edit zone configurations
* Delete zones easily
* Centralized domain control panel

---

### 📡 DNS Management

* Add DNS records (A, AAAA, CNAME, TXT, MX, etc.)
* Update existing DNS records
* Delete DNS records
* Real-time DNS control via Cloudflare API

---

### 👥 User Management

* Create and manage multiple users
* Role-based access (optional extendable)
* Secure authentication system

---

### 🔐 Authentication & Security

* User login system
* Password change functionality
* Secure credential handling

---

### ⚙️ Cloudflare Integration

* Cloudflare API integration
* Dynamic DNS updates
* Zone-based API operations

---

## 📦 Installation

```bash
git clone https://github.com/sadi-tanvir/cloudflare-dns-hub.git
cd cloudflare-dns-hub
```

```bash
composer install
cp .env.example .env
```

Update your `.env` with:

```
CLOUDFLARE_API_TOKEN=api-token
DB_HOST=hostname
DB_NAME=database-name
DB_USER=database-user
DB_PASS=database-password
```

---

## 📈 Future Improvements

* 🔄 Auto DNS sync
* 📊 Analytics dashboard
* 🔐 Role & permission system (advanced)
* 🌍 Multi-tenant SaaS support
* 🧾 Activity logs