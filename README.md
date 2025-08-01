# 🌱 Farm Management System

A comprehensive web-based farm management application built with PHP, Apache, and MySQL. This system helps farmers efficiently manage agricultural operations through real-time data tracking, analytics, and reporting.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-2CA5E0?style=flat&logo=docker&logoColor=white)](https://www.docker.com/)
[![PHP](https://img.shields.io/badge/PHP-8.1-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)

---

## 🚀 Features

### 📊 Interactive Dashboard
- Real-time KPIs and statistics
- Weather forecasts and alerts
- Charts and quick actions

### 🌾 Crop Management
- Full lifecycle tracking
- Field mapping and yield analytics

### 🐄 Livestock Management
- Animal profiles and medical records
- Breeding and inventory management

### 💰 Financial Management
- Income, expenses, and budget tracking
- Financial reports and tax tools

### 🌱 Soil & Field Management
- Soil tests and productivity analytics
- Sustainability metrics

### ☀️ Weather Integration
- Historical and live data
- Growing Degree Days (GDD)

---

## 🛠 Technology Stack

| Component       | Technology                          |
|----------------|--------------------------------------|
| **Backend**     | PHP 8.1                              |
| **Frontend**    | HTML5, CSS3, JavaScript, Bootstrap 5.3 |
| **Charts**      | Chart.js 3.9                         |
| **Database**    | MySQL 8.0                            |
| **Web Server**  | Apache 2.4                           |
| **Icons**       | Font Awesome 6.4                     |
| **Container**   | Docker & Docker Compose              |

---

## ⚡ Quick Start

### ✅ Prerequisites
- Docker 20.10+
- Docker Compose 2.0+
- Git (optional)

---

### 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/farm-management.git
   cd farm-management
   ```

2. **Set environment variables**
   ```bash
   cp .env.example .env
   ```

3. **Build and run**
   ```bash
   docker-compose up -d --build
   ```

4. **Access the app**
   - 🌐 Web App: [http://localhost:8080](http://localhost:8080)
   - 🛠 phpMyAdmin: [http://localhost:8081](http://localhost:8081)  
     - Username: `farm_user`  
     - Password: `farm_password`

5. **Initial Setup**
   - Database is auto-initialized with sample data
   - Log in using default admin credentials (if applicable)

---

## 💻 Running on VirtualBox

If you're using **VirtualBox** to run this project inside a virtual machine:

### 1. Ensure Port Forwarding is Enabled
In your VM settings:
- Go to **Network > Adapter 1 > Advanced > Port Forwarding**
- Add rules like:
  - `Host Port: 8080` → `Guest Port: 8080`
  - `Host Port: 8081` → `Guest Port: 8081`

### 2. Use Host IP Instead of `localhost`
Access the app in your host browser using:
- Web App: `http://<your_vm_ip>:8080`
- phpMyAdmin: `http://<your_vm_ip>:8081`

You can find your VM's IP by running:
```bash
ip a | grep inet
```

### 3. Optional: Use Bridged Adapter
Alternatively, switch your network to "Bridged Adapter" for direct LAN access without port forwarding.

---

## 📁 Project Structure

```
farm-management/
├── docker/                  # Docker configs
│   ├── php/                 # PHP settings
│   └── mysql/               # MySQL settings
├── src/                     # Main application
│   ├── config/              # Config files
│   ├── includes/            # Reusable modules
│   ├── assets/              # CSS/JS/Images
│   └── views/               # UI templates
├── database/                # SQL scripts
│   └── init.sql             # Schema + demo data
├── .env.example             # Env variables template
├── docker-compose.yml       # Docker Compose setup
├── Dockerfile               # Container definition
└── README.md                # Project documentation
```

---

## 🔧 Common Tasks

### Running Migrations
```bash
docker-compose exec app php migrations/migrate.php
```

### Viewing Logs
```bash
# Application logs
docker-compose logs -f app

# Database logs
docker-compose logs -f db
```

### Backing Up the Database
```bash
docker-compose exec db mysqldump -u farm_user -p"farm_password" farm_management > backup_$(date +%Y%m%d).sql
```

# Troubleshooting

Database Connection Issues

If you encounter problems connecting to the database:

- Verify the database container/service is running
Run:
```bash
docker-compose ps
```

# Make sure the db service is up and healthy.

- Restart MySQL service (for local or VM installs without Docker)

- To start or restart MySQL, run:
```bash
sudo systemctl restart mysql
```
- Check MySQL logs

```bash
sudo journalctl -u mysql.service -f
```
Check Docker logs (if using Docker)
```bash
docker-compose logs -f db
```

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to your branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/)
- [Chart.js](https://www.chartjs.org/)
- [Font Awesome](https://fontawesome.com/)
- [Docker](https://www.docker.com/)
- [DIO (Digital Innovation One)](https://www.dio.me/)
- [Denilson Bonatti – Tech Lead, DIO](https://www.linkedin.com/in/denilsonbonatti/)

