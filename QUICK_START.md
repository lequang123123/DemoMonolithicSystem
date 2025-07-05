# Quick Start Guide - Laravel E-commerce Monolithic

## 🚀 Bắt đầu nhanh

### Prerequisites
- Docker và Docker Compose
- Git
- Port 80, 3306, 6379, 9200 available

### 1. Clone và Setup
```bash
git clone https://github.com/your-repo/laravel-ecommerce-monolithic.git
cd laravel-ecommerce-monolithic
```

### 2. Environment Setup
```bash
cp .env.example .env
# Chỉnh sửa .env nếu cần
```

### 3. Start Services
```bash
# Build và start all services
docker-compose up -d --build

# Chờ services khởi động (30-60 giây)
docker-compose logs -f app
```

### 4. Install Dependencies
```bash
docker-compose exec app composer install
```

### 5. Application Setup
```bash
# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database với data mẫu
docker-compose exec app php artisan db:seed

# Create storage link
docker-compose exec app php artisan storage:link
```

### 6. Access Application
- **Frontend**: http://localhost
- **Admin Panel**: http://localhost/admin
- **API Docs**: http://localhost/api/docs

## 🔧 Commands hữu ích

### Development
```bash
# Xem logs
docker-compose logs -f app

# Access container
docker-compose exec app bash

# Run tests
docker-compose exec app php artisan test

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

### Database
```bash
# Fresh migration
docker-compose exec app php artisan migrate:fresh --seed

# Backup database
docker-compose exec db mysqldump -u laravel -psecret ecommerce > backup.sql

# Restore database
docker-compose exec -T db mysql -u laravel -psecret ecommerce < backup.sql
```

### Queue & Cache
```bash
# Clear queue
docker-compose exec app php artisan queue:clear

# Clear cache
docker-compose exec app php artisan cache:clear

# Restart queue worker
docker-compose restart queue
```

## 🗂️ Cấu trúc dự án

```
laravel-ecommerce-monolithic/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Eloquent Models
│   ├── Services/            # Business Logic
│   └── ...
├── database/
│   ├── migrations/          # Database Schema
│   └── seeders/            # Sample Data
├── docker/                  # Docker Configuration
├── resources/
│   ├── views/              # Blade Templates
│   └── js/                 # Frontend Assets
├── routes/
│   ├── web.php             # Web Routes
│   └── api.php             # API Routes
├── docker-compose.yml       # Docker Services
└── README.md               # Documentation
```

## 📊 Default Accounts

### Admin Account
- Email: admin@example.com
- Password: password

### Customer Account
- Email: customer@example.com
- Password: password

## 🔍 Troubleshooting

### Common Issues:

1. **Port already in use**
   ```bash
   # Stop conflicting services
   sudo service apache2 stop
   sudo service mysql stop
   ```

2. **Permission issues**
   ```bash
   # Fix permissions
   sudo chown -R $USER:$USER .
   docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
   ```

3. **Database connection failed**
   ```bash
   # Wait for MySQL to start
   docker-compose exec db mysql -u laravel -psecret -e "SELECT 1"
   ```

4. **Elasticsearch not starting**
   ```bash
   # Increase VM memory
   sudo sysctl -w vm.max_map_count=262144
   ```

### Reset Everything
```bash
# Stop và remove containers
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Fresh start
docker-compose up -d --build
```

## 🌟 Features Demo

### Test Features:
1. **Product Catalog**: Browse products at http://localhost/products
2. **Shopping Cart**: Add items to cart
3. **User Registration**: Create account
4. **Order Management**: Place and track orders
5. **Admin Panel**: Manage products, orders, users

### API Endpoints:
- `GET /api/products` - List products
- `POST /api/cart/add` - Add to cart
- `GET /api/orders` - List orders
- `POST /api/auth/login` - Login

## 📝 Next Steps

1. **Customize Design**: Edit views in `resources/views/`
2. **Add Features**: Extend models and controllers
3. **Configure Payment**: Setup Stripe/PayPal
4. **Deploy Production**: Follow AWS deployment guide
5. **Performance Optimization**: Add caching, optimize queries

## 🆘 Support

- **Documentation**: Check README.md
- **Issues**: Create GitHub issue
- **Email**: support@example.com

---

**Happy Coding!** 🎉 