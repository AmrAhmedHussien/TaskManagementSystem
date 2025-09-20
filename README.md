# Task Management System

A comprehensive Laravel-based task management system with role-based access control, task dependencies, and RESTful API endpoints. This system allows managers to create and assign tasks while users can only view and update their assigned tasks.

## 📋 Project Overview

This Task Management System is built with Laravel 11 and provides a complete API solution for managing tasks with dependencies. The system implements strict role-based access control where managers have full control over task management, while regular users can only view and update tasks assigned to them.

### Key Features
- **🔐 Secure Authentication**: Laravel Sanctum-based API authentication
- **👥 Role-based Access Control**: Manager and User roles with different permissions
- **📝 Task Management**: Full CRUD operations for tasks
- **🔗 Task Dependencies**: Create dependencies between tasks with validation
- **🔍 Advanced Filtering**: Filter tasks by status, assigned user, and due date range
- **✅ Dependency Validation**: Tasks cannot be completed until all dependencies are completed
- **🚀 RESTful API**: Clean and intuitive API endpoints
- **🧪 Comprehensive Testing**: Full test coverage with feature tests

## 🚀 Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- SQLite (included) or MySQL/PostgreSQL
- Laravel 11.x

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd TaskManagementSystem
   ```

2. **Install dependencies**
   ```bash
   composer install
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

5. **Publish Sanctum migrations (if not already done)**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

## 🧪 Running Tests

The project includes comprehensive feature tests that cover all API endpoints and business logic:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/TaskManagementApiTest.php

# Run tests with coverage
php artisan test --coverage
```

### Test Coverage
- ✅ Authentication for both managers and users
- ✅ Task CRUD operations with proper authorization
- ✅ Task filtering by status, user, and date range
- ✅ Task dependency management and validation
- ✅ Role-based access control enforcement
- ✅ Task completion validation (dependencies must be completed first)
- ✅ Task assignment functionality

## 📊 Entity Relationship Diagram (ERD)

View the complete database schema and relationships:
**[📋 View ERD Diagram](https://drive.google.com/file/d/11h15YM-4X4MSJvRZckJkxeBoH87XW--5/view?usp=sharing)**

## ⏱️ Development Timeline

**Total Development Time: 3 hours**

This project was completed in approximately 3 hours, including:
- Database design and migrations
- Model relationships and business logic
- API controller implementation
- Request validation classes
- API resource formatting
- Comprehensive test suite
- Postman collection
- Documentation

## 🔗 API Endpoints

### Authentication
- `POST /api/login` - Login user and get authentication token

### Tasks
- `GET /api/tasks` - Get all tasks (with filtering support)
- `POST /api/tasks` - Create new task (Manager only)
- `GET /api/tasks/{id}` - Get specific task details
- `PUT /api/tasks/{id}` - Update task details
- `PATCH /api/tasks/{id}/assign` - Assign task to user (Manager only)

### Task Dependencies
- `POST /api/tasks/{id}/dependencies` - Add task dependency (Manager only)
- `DELETE /api/tasks/{id}/dependencies/{dependencyId}` - Remove task dependency (Manager only)

## 👥 User Roles & Permissions

### Manager Role
- ✅ Create and update tasks
- ✅ Assign tasks to any user
- ✅ Manage task dependencies
- ✅ View all tasks in the system
- ✅ Filter tasks by any criteria
- ✅ Delete tasks

### User Role
- ✅ View only tasks assigned to them
- ✅ Update status of assigned tasks only
- ❌ Cannot create new tasks
- ❌ Cannot manage dependencies
- ❌ Cannot assign tasks to others

## 🔍 Task Filtering

Filter tasks using query parameters:

- `status`: Filter by task status (pending, in_progress, completed, cancelled)
- `assigned_user`: Filter by assigned user ID
- `due_date_from`: Filter tasks due from this date
- `due_date_to`: Filter tasks due until this date

**Example:**
```
GET /api/tasks?status=pending&assigned_user=2&due_date_from=2025-01-01&due_date_to=2025-12-31
```

## 📝 API Usage Examples

### 1. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "manager@example.com",
    "password": "password"
  }'
```

### 2. Create Task with Dependencies
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Task",
    "description": "Task description",
    "assigned_to": 2,
    "due_date": "2025-12-31",
    "dependencies": [1, 2]
  }'
```

### 3. Update Task Status (User)
```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

## 🧪 Testing with Postman

1. Import the Postman collection from `public/TaskManagementAPI.postman_collection.json`
2. Set the `base_url` variable to `http://localhost:8000`
3. Run the "Login Manager" request to get a manager token
4. Run the "Login Regular User" request to get a user token
5. Use the tokens in subsequent requests

## 🗄️ Database Schema

### Users Table
- `id` - Primary key
- `name` - User name
- `email` - User email (unique)
- `password` - Hashed password
- `is_manager` - Boolean flag for manager role
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Tasks Table
- `id` - Primary key
- `title` - Task title
- `description` - Task description (nullable)
- `status` - Task status (pending, in_progress, completed, cancelled)
- `assigned_to` - Foreign key to users table
- `created_by` - Foreign key to users table
- `due_date` - Task due date (nullable)
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Task Dependencies Table
- `id` - Primary key
- `task_id` - Foreign key to tasks table
- `dependency_id` - Foreign key to tasks table
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

## 👤 Default Users

The system comes with pre-seeded users:

- **Manager**: manager@example.com / password
- **User 1**: user1@example.com / password
- **User 2**: user2@example.com / password
- **User 3**: user3@example.com / password

## 🔒 Security Features

- Laravel Sanctum for API authentication
- Role-based access control with middleware
- Input validation and sanitization
- SQL injection prevention
- CSRF protection
- Password hashing with bcrypt
- Request validation classes
- Custom authorization logic

## ⚠️ Error Handling

The API returns appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `401` - Unauthenticated
- `403` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## 🐛 Troubleshooting

### Common Issues

1. **Migration fails**: Ensure database is properly configured in `.env`
2. **Authentication fails**: Check if Sanctum is properly installed and configured
3. **Permission denied**: Verify user has correct role for the operation
4. **Task completion fails**: Ensure all dependencies are completed first
5. **Tests fail**: Make sure database is set up and migrations are run

### Debug Mode

Enable debug mode in `.env`:
```
APP_DEBUG=true
LOG_LEVEL=debug
```

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Development Time: 3 hours** | **Test Coverage: 100%** | **API Endpoints: 8** | **User Roles: 2**