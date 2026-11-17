# Village Foods — Project Documentation

Village Foods is a modern, full-stack food delivery and rapid pickup platform designed for hyper-local ecosystems. It connects Customers, Local Shops (Vendors), and Delivery Partners through a centralized management system.

## 🚀 Modules & Dashboards

### 1. Customer Frontend
Accessible via the root directory (e.g., `index.php`).
- **Discovery**: Browse shops by category or distance.
- **Ordering**: Unified cart system with checkout and payment flows.
- **Tracking**: Real-time order status tracking (`track-order.php`).
- **Personalization**: User profiles, address management, and wishlists.

### 2. Admin Control Center (`/admin`)
The command center for platform administrators.
- **Analytics**: Real-time revenue, orders, and growth metrics using ApexCharts.
- **Order Management**: Comprehensive oversight of active, pending, and completed orders.
- **Product Catalog**: Manage inventory with **Active/Inactive** status toggles and category assignments.
- **Delivery Management**: Strategic assignment of delivery partners based on location (City/Area) and real-time status (Online/Busy/Offline).
- **Withdrawal System**: Oversight of delivery partner earning payouts.

### 3. Delivery Partner Portal (`/delivery`)
A mobile-first dashboard for partners on the move.
- **Job Management**: Accept and process food orders and Rapid Pickup requests.
- **Status Control**: Toggle between "Online" (Ready), "Busy" (On Delivery), and "Offline".
- **Earnings & Wallet**: Track daily earnings and request withdrawals.
- **Communication**: Integrated "Call Customer" functionality with automatic contact number fallback.

### 4. Rapid Pickup Service (`pickup-drop.php`)
A specialized peer-to-peer delivery service for transporting packages, documents, or personal items quickly within the city.

---

## 🛠️ Technical Stack

- **Backend**: PHP 8.x (RESTful API architecture in `/api`).
- **Database**: MySQL 8.x (Relational schema with optimized indexing).
- **Frontend Logic**: ES6+ Vanilla JavaScript (Modular design).
- **Styling**: Modern CSS with Glassmorphism, Premium Dark/Light modes, and Responsive Grid systems.
- **Icons & Visualization**: Lucide Icon library and ApexCharts.
- **PWA Support**: Offline capabilities and service worker integration.

---

## 📁 Key Directory Structure

- `/api`: Contains all backend logic separated by module (admin, delivery, products, shops).
- `/assets`: Global JavaScript (`/js`), CSS (`/css`), and multimedia resources.
- `/includes`: Shared core files like `db.php` and layout components.
- `/admin/layouts`: Modular UI components for the admin dashboard.
- `/uploads`: Storage for dynamic content like product images.

---

## ✨ Recent Enhancements

- **Intelligent Assignment**: Admins can now see delivery partner locations (City/Area) in assignment dropdowns to reduce delivery latency.
- **Status Color Coding**: Real-time indicators (**🟢 Green**, **🟡 Yellow**, **⚪ White**) for partner availability.
- **Product Visibility**: Introduced an `Active/Inactive` system that allows vendors to hide products from the frontend without deleting data.
- **Contact Fallback**: Enhanced customer calling logic to ensure partners can reach customers even if primary profile numbers are missing (via address fallback).

---

© 2026 Village Foods — Advanced Food Delivery Solutions.
