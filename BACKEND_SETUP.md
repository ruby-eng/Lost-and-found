# Lost and Found Backend Setup Guide

## Overview
This is a complete Lost and Found web application backend built with PHP and MySQL. Users can report lost/found items, claim items, and track claims.

## Project Structure

```
Lost-and-found-main/
├── config/
│   └── db.php                 # Database connection
├── auth/
│   ├── login.php             # User login
│   ├── register.php          # User registration
│   └── logout.php            # User logout
├── api/
│   ├── items.php             # Items API (lost/found browsing, search)
│   ├── claims.php            # Claims API (manage claims)
│   ├── notifications.php     # Notifications API
│   └── admin.php             # Admin API (statistics, broadcasts)
├── report_page/
│   ├── report-lost.php       # Submit lost item report
│   ├── report-found.php      # Submit found item report
│   └── report-lost.html      # Frontend form
├── claim_page/
│   ├── claim_item.php        # Submit item claim
│   └── claim.html            # Frontend form
├── uploads/                   # Uploaded images
├── index.php                  # Main dashboard
└── database.sql              # Database schema
```

## Prerequisites

1. **XAMPP/WAMP/LAMP** - Local PHP server
2. **MySQL** - Database server
3. **PHP 7.4+** - Server-side language
4. **Modern Web Browser** - For frontend

## Installation Steps

### Step 1: Set Up Database

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Create a new database named `lost_and_found`
3. Import the `database.sql` file:
   - Click on your `lost_and_found` database
   - Go to Import tab
   - Select `database.sql` file
   - Click Import

Or run via command line:
```bash
mysql -u root -p < database.sql
```

### Step 2: Configure Database Connection

Edit `config/db.php` and update these credentials if needed:
```php
$host = 'localhost';
$db = 'lost_and_found';
$user = 'root';        // Your MySQL username
$password = '';        // Your MySQL password
```

### Step 3: Place Project in Web Root

- **XAMPP**: Copy to `C:\xampp\htdocs\Lost-and-found-main\`
- **WAMP**: Copy to `C:\wamp64\www\Lost-and-found-main\`
- **LAMP**: Copy to `/var/www/html/Lost-and-found-main/`

### Step 4: Create Uploads Directory

Make sure the uploads directory is writable:
```bash
chmod 777 uploads/
```

## Usage

### For Users

1. **Register**: Go to `http://localhost/Lost-and-found-main/auth/register.php`
2. **Login**: Go to `http://localhost/Lost-and-found-main/auth/login.php`
3. **Report Items**: Use the Report Lost/Found buttons on the dashboard
4. **Claim Items**: Click on an item and submit a claim
5. **View Claims**: Check your claims and claims on your items

### For Admins

- Make a user admin by updating their record:
```sql
UPDATE users SET is_admin = TRUE WHERE email = 'admin@example.com';
```

- Access admin features:
  - View all claims at `api/admin.php?action=get_all_claims`
  - Send broadcasts at `api/admin.php?action=broadcast_message`
  - View statistics at `api/admin.php?action=get_statistics`

## API Endpoints

### Items API (`api/items.php`)
- `?action=get_lost_items` - Get all open lost items
- `?action=get_found_items` - Get all available found items
- `?action=get_item_details&item_id=ID&item_type=lost/found` - Get item details
- `?action=search_items&q=QUERY&type=lost/found/all` - Search items

### Claims API (`api/claims.php`)
- `?action=get_my_claims` - Get claims made by current user
- `?action=get_claims_on_my_items` - Get claims on user's items
- `POST: action=update_claim_status` - Verify/reject claims

### Notifications API (`api/notifications.php`)
- `?action=get_notifications` - Get user notifications
- `?action=get_unread_count` - Get unread notification count
- `POST: action=mark_as_read` - Mark notification as read

### Admin API (`api/admin.php`)
- `?action=get_all_claims` - Get all claims (admin only)
- `?action=get_statistics` - Get system statistics (admin only)
- `POST: action=broadcast_message` - Send broadcast to all users (admin only)

## Database Tables

### users
- id, email, password, name, phone, is_admin, created_at, updated_at

### lost_items
- id, user_id, description, color, object_type, location_lost, date_lost, image_path, status, created_at, updated_at

### found_items
- id, user_id, description, color, object_type, location_found, date_found, image_path, status, created_at, updated_at

### claims
- id, item_id, item_type, claimer_id, status, details, verification_notes, verified_by, verified_at, created_at, updated_at

### notifications
- id, user_id, sender_id, type, title, message, related_item_id, is_read, created_at

## Security Features

✅ Password hashing with bcrypt
✅ Session management
✅ SQL injection prevention (prepared statements)
✅ User authentication checks
✅ Admin role verification
✅ User ownership verification on claims

## Features Implemented

✅ User Authentication (Register/Login/Logout)
✅ Report Lost Items
✅ Report Found Items
✅ Claim Lost/Found Items
✅ Item Search & Filtering
✅ Claim Verification by Item Owner
✅ Notification System
✅ Admin Dashboard & Statistics
✅ Broadcast Messaging
✅ Image Upload
✅ Session Management

## Next Steps

1. Create browse pages (`browse_lost.php`, `browse_found.php`)
2. Create claims management pages (`my_claims.php`, `review_claims.php`)
3. Add frontend JavaScript for better UX
4. Implement email notifications
5. Add location mapping (Google Maps integration)
6. Add rating/review system

## Troubleshooting

**Database Connection Error**
- Check MySQL is running
- Verify credentials in `config/db.php`
- Ensure database `lost_and_found` exists

**File Upload Not Working**
- Check `uploads/` folder is writable (`chmod 777`)
- Check PHP `upload_max_filesize` setting

**Login Issues**
- Clear browser cookies
- Check session.save_path in php.ini
- Verify user exists in database

## Support

For issues or questions, check the frontend forms in `report_page/` and `claim_page/` directories for integration details.
