# Task Management System

A Laravel-based task management system with role-based access control, task dependencies, and RESTful API endpoints.

## Features

- **Authentication**: Secure API authentication using Laravel Sanctum
- **Role-based Access Control**: Manager and User roles with different permissions
- **Task Management**: Create, read, update, and delete tasks
- **Task Dependencies**: Add dependencies between tasks with circular dependency prevention
- **Filtering**: Filter tasks by status, assigned user, and due date range
- **RESTful API**: Clean and intuitive API endpoints

## Requirements

- PHP 8.1 or higher
- Composer
- SQLite (included) or MySQL/PostgreSQL
- Laravel 11.x

## Installation

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

5. **Start the development server**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

## API Endpoints

### Authentication
- `POST /api/login` - Login user

### Tasks
- `GET /api/tasks` - Get all tasks (with filtering)
- `POST /api/tasks` - Create new task (Manager only)
- `GET /api/tasks/{id}` - Get specific task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task

### Task Dependencies
- `POST /api/tasks/{id}/dependencies` - Add task dependency (Manager only)
- `DELETE /api/tasks/{id}/dependencies/{dependencyId}` - Remove task dependency (Manager only)

## User Roles

### Manager
- Create and update tasks
- Assign tasks to users
- Manage task dependencies
- View all tasks
- Filter tasks by any criteria

### User
- View only assigned tasks
- Update task status only
- Cannot create tasks or manage dependencies

## API Usage Examples

### 1. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "manager@example.com",
    "password": "password"
  }'
```

### 2. Get All Tasks (Manager)
```bash
curl -X GET http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 3. Create Task (Manager)
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Task",
    "description": "Task description",
    "assigned_to": 2,
    "due_date": "2024-12-31"
  }'
```

### 4. Update Task Status (User)
```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

### 5. Add Task Dependency (Manager)
```bash
curl -X POST http://localhost:8000/api/tasks/3/dependencies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "depends_on_task_id": 1
  }'
```

## Filtering Tasks

You can filter tasks using query parameters:

- `status`: Filter by task status (pending, in_progress, completed, cancelled)
- `assigned_user`: Filter by assigned user ID
- `due_date_from`: Filter tasks due from this date
- `due_date_to`: Filter tasks due until this date

Example:
```
GET /api/tasks?status=pending&assigned_user=2&due_date_from=2024-01-01&due_date_to=2024-12-31
```

## Testing with Postman

1. Import the Postman collection from `public/TaskManagementAPI.postman_collection.json`
2. Set the `base_url` variable to `http://localhost:8000`
3. Run the "Login Manager" request to get a manager token
4. Run the "Login Regular User" request to get a user token
5. Use the tokens in subsequent requests

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User name
- `email` - User email
- `password` - Hashed password
- `is_manager` - Boolean flag for manager role
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Tasks Table
- `id` - Primary key
- `title` - Task title
- `description` - Task description
- `status` - Task status (pending, in_progress, completed, cancelled)
- `assigned_to` - Foreign key to users table
- `created_by` - Foreign key to users table
- `due_date` - Task due date
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Task Dependencies Table
- `id` - Primary key
- `task_id` - Foreign key to tasks table
- `depends_on_task_id` - Foreign key to tasks table
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

## Default Users

The system comes with pre-seeded users:

- **Manager**: manager@example.com / password
- **User 1**: user1@example.com / password
- **User 2**: user2@example.com / password
- **User 3**: user3@example.com / password

## Error Handling

The API returns appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `401` - Unauthenticated
- `403` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Security Features

- Laravel Sanctum for API authentication
- Role-based access control
- Input validation and sanitization
- SQL injection prevention
- CSRF protection
- Password hashing

## Troubleshooting

### Common Issues

1. **Migration fails**: Ensure database is properly configured in `.env`
2. **Authentication fails**: Check if Sanctum is properly installed and configured
3. **Permission denied**: Verify user has correct role for the operation
4. **Circular dependency**: The system prevents circular dependencies automatically

### Debug Mode

Enable debug mode in `.env`:
```
APP_DEBUG=true
LOG_LEVEL=debug
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).