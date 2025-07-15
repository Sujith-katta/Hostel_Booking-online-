# Hostel_Booking-online-

## Project Overview
This is a comprehensive Hostel Booking System that allows both administrators and customers to manage hostel room bookings. The system includes features for room management, booking processing, payment handling, and user administration.

## Project Structure

hostel_booking/
├── admin/
│   ├── dashboard.php          - Admin dashboard with navigation
│   ├── index.php              - Admin login/registration
│   ├── manage_bookings.php    - Booking management interface
│   ├── manage_rooms.php       - Room type management
│   └── upload_upi.php         - UPI QR code upload for payments
├── customer/
│   ├── dashboard.php          - Customer dashboard
│   ├── index.php              - Customer login/registration
│   ├── payment.php            - Payment processing
│   └── bill.php               - Booking bill/receipt
├── includes/
│   ├── db_connect.php         - Database connection
│   ├── functions.php          - Common functions
│   └── logout.php             - Logout handler
├── css/
│   └── style.css              - Main stylesheet
├── index.php                  - Main landing page
└── database.sql               - Database schema and sample data

## Key Features

### Admin Features
1. **User Management**
   - Admin registration and login
   - Strict authentication checks

2. **Booking Management**
   - View all bookings
   - Accept/reject pending bookings
   - Verify payments
   - Track booking statuses

3. **Room Management**
   - View available room types
   - See room pricing details

4. **Payment System**
   - Upload UPI QR code
   - Set payment details
   - Verify customer payments

### Customer Features
1. **User Account**
   - Registration and login
   - Profile management

2. **Booking System**
   - View available room types
   - Book rooms with mess preferences
   - View booking history

3. **Payment Processing**
   - Make payments via UPI
   - Submit transaction IDs
   - View payment status

4. **Receipts**
   - Generate booking bills
   - Print receipts

## Technical Stack
- **Frontend**: HTML5, CSS3
- **Backend**: PHP
- **Database**: MySQL
- **Security**: Session-based authentication

## Database Schema
The system uses a relational database with the following tables:
- `users` - Stores admin and customer accounts
- `room_types` - Contains room categories and pricing
- `bookings` - Tracks all bookings and payments
- `upi_details` - Stores payment QR code information

## Workflow

1. **Admin Workflow**
   - Login → Dashboard → Manage bookings/rooms/payments

2. **Customer Workflow**
   - Register/Login → View rooms → Book room → Make payment → View receipt

## Setup Instructions

1. Create database using `database.sql`
2. Configure `db_connect.php` with your database credentials
3. Place files in your web server directory (e.g., htdocs)
4. Access the system via the main `index.php`

## Security Considerations
- The system currently uses plain text passwords (for demo purposes only)
- Session-based authentication with role checks
- Input sanitization for database queries

## Future Enhancements
- Password hashing implementation
- Email notifications
- Room availability calendar
- Advanced reporting

