# PerfumeShop - AI-Powered Perfume Store Management System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 🎯 Overview

PerfumeShop is a comprehensive AI-powered management system designed for perfume retail businesses. Built with Laravel 12.x, it integrates advanced AI agents to automate business operations, enhance customer experience, and optimize inventory management for both physical and online stores.

## ✨ Key Features

### 🤖 AI-Powered Intelligence
- **Multi-Agent Architecture**: Specialized AI agents for sales, inventory, reporting, and customer support
- **Natural Language Processing**: Intelligent chatbot for product consultation and custom perfume recommendations
- **Smart Classification**: Automatic routing of queries to appropriate AI agents
- **Vector Search**: Semantic search capabilities for enhanced product discovery

### 📦 Inventory Management
- **Real-time Stock Tracking**: Automated inventory updates across all sales channels
- **Smart Alerts**: Predictive low-stock warnings based on consumption patterns
- **Multi-location Support**: Manage inventory across multiple store locations
- **Movement History**: Complete audit trail of all inventory transactions

### 🛒 Order Management
- **Multi-channel Orders**: Unified management for online and offline orders
- **Custom Perfume Orders**: AI-assisted custom perfume creation with approval workflows
- **Order Status Tracking**: Complete lifecycle management from creation to delivery
- **Automated Pricing**: Dynamic pricing for custom formulations

### 👥 Customer Relationship Management
- **Customer Profiles**: Comprehensive customer data and purchase history
- **Automated Marketing**: Email campaigns and promotional notifications
- **Feedback Collection**: QR code-based feedback system
- **Customer Segmentation**: AI-driven customer grouping and targeting

### 📊 Analytics & Reporting
- **Real-time Dashboard**: KPI monitoring and business insights
- **Custom Reports**: Flexible reporting across all business metrics
- **Performance Analytics**: AI agent performance monitoring
- **Export Capabilities**: Excel/CSV export for external analysis

### 💰 Financial Management
- **Cash Flow Tracking**: Comprehensive financial transaction management
- **Revenue Analytics**: Multi-dimensional revenue analysis
- **Cost Management**: Detailed cost tracking and profit analysis
- **Voucher System**: Flexible discount and promotion management

## 🏗️ System Architecture

### AI Agents Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Chat Agent    │    │  Sales Agent    │    │Inventory Agent  │
│                 │    │                 │    │                 │
│ • NLP Processing│    │ • Order Mgmt    │    │ • Stock Alerts  │
│ • Product Rec   │    │ • Pricing       │    │ • Forecasting   │
│ • Consultation  │    │ • Custom Orders │    │ • Movement Track│
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌─────────────────┐
                    │  AI Coordinator │
                    │                 │
                    │ • Agent Routing │
                    │ • Context Mgmt  │
                    │ • Classification│
                    └─────────────────┘
```

### Technology Stack
- **Backend**: Laravel 12.x with PHP 8.2+
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **Frontend**: Blade templates with Tailwind CSS
- **AI/ML**: Custom AI agents with vector search capabilities
- **Excel Processing**: Maatwebsite Excel package
- **Build Tools**: Vite with modern JavaScript

## 🚀 Quick Start

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (or MySQL/PostgreSQL)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd PerfumeShop
   ```

2. **Install dependencies**
   ```bash
composer install
   npm install
   ```

3. **Environment setup**
   ```bash
cp .env.example .env
php artisan key:generate
   ```

4. **Database setup**
   ```bash
php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start the application**
   ```bash
php artisan serve
```

7. **Access the application**
- URL: http://localhost:8000
   - Default redirects to the product management dashboard

## 📁 Project Structure

```
PerfumeShop/
├── app/
│   ├── Http/Controllers/          # Web & API controllers
│   │   ├── Api/                   # API endpoints
│   │   ├── ProductController.php  # Product management
│   │   ├── OrderController.php    # Order processing
│   │   ├── InventoryController.php# Inventory management
│   │   └── OmniAIController.php   # AI chat interface
│   ├── Models/                    # Eloquent models
│   │   ├── Product.php           # Product & variants
│   │   ├── Order.php             # Order management
│   │   ├── Customer.php          # Customer data
│   │   └── InventoryMovement.php # Stock tracking
│   ├── Services/                  # AI Agents & Services
│   │   ├── AICoordinator.php     # AI agent coordinator
│   │   ├── ChatAgent.php         # Customer support AI
│   │   ├── SalesAgent.php        # Sales optimization AI
│   │   ├── InventoryAgent.php    # Inventory management AI
│   │   ├── ReportAgent.php       # Analytics AI
│   │   └── VectorSearchService.php# Semantic search
│   └── Helpers/                   # Utility classes
├── database/
│   ├── migrations/               # Database schema
│   └── seeders/                  # Sample data
├── resources/views/              # Blade templates
│   ├── layouts/                  # Layout templates
│   ├── products/                 # Product management views
│   ├── orders/                   # Order management views
│   ├── inventory/                # Inventory views
│   └── reports/                  # Analytics views
├── docs/                        # Documentation
│   ├── AI_Agents_Architecture_Report.md
│   ├── BRD.md                   # Business Requirements
│   └── sequenceuml/             # Sequence diagrams
└── scripts/                     # Utility scripts
```

## 🔧 Configuration

### Environment Variables
Key configuration options in `.env`:

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# AI Configuration
AI_PROVIDER=openai
AI_MODEL=gpt-3.5-turbo
AI_API_KEY=your_api_key_here

# Application
APP_NAME="PerfumeShop"
APP_ENV=local
APP_DEBUG=true
```

### AI Agent Configuration
Configure AI agents in `config/services.php`:

```php
'ai' => [
    'provider' => env('AI_PROVIDER', 'openai'),
    'model' => env('AI_MODEL', 'gpt-3.5-turbo'),
    'api_key' => env('AI_API_KEY'),
    'max_tokens' => 1000,
    'temperature' => 0.7,
],
```

## 📚 Documentation

- [AI Agents Architecture](docs/AI_Agents_Architecture_Report.md)
- [Business Requirements](docs/BRD.md)
- [Sequence Diagrams](docs/sequenceuml/)
- [Use Case Diagrams](docs/usecaseuml/)
- [Operational Procedures](docs/QuyTrinhVanHanh.md)

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test:unit
php artisan test:feature

# Run with coverage
php artisan test --coverage
```

## 📈 Performance

### Benchmarks
- **AI Response Time**: < 3 seconds
- **Dashboard Load Time**: < 5 seconds
- **Concurrent Users**: 100+ supported
- **Database Queries**: Optimized with eager loading

### Optimization Features
- Database query optimization
- Caching for frequently accessed data
- Lazy loading for large datasets
- Efficient AI agent routing

## 🔒 Security

- **Authentication**: Laravel's built-in authentication system
- **Authorization**: Role-based access control
- **Data Protection**: Encrypted sensitive data
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Eloquent ORM protection
- **XSS Protection**: Blade template escaping

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use conventional commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Developer**: Nam Hải
- **Email**: namhai.632003@example.com
- **Project**: [PerfumeShop Repository](https://github.com/username/PerfumeShop)

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation in the `docs/` folder
- Review the sequence diagrams for workflow understanding

## 🔮 Roadmap

### Upcoming Features
- [ ] Mobile app integration
- [ ] Advanced AI recommendations
- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] Third-party marketplace integration
- [ ] Automated email marketing campaigns

---

**PerfumeShop** - Revolutionizing perfume retail with AI-powered management solutions.