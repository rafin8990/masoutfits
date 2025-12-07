# Media Coverage API Documentation

Simple CRUD operations documentation for Media Coverage endpoints.

## Base URL
```
/api
```

## Authentication
- **Public Endpoints**: GET requests (no authentication required)
- **Protected Endpoints**: POST, PUT, DELETE requests require authentication via Sanctum token
- **Admin Only**: Create, Update, and Delete operations require admin role

---

## Endpoints

### 1. Get All Media Coverage
Retrieve all media coverage entries.

**Endpoint:** `GET /media-coverage`

**Authentication:** Not required

**Response:**
```json
{
  "success": true,
  "message": "Media coverage fetched successfully",
  "data": [
    {
      "id": 1,
      "title": "Fashion Week Coverage",
      "description": "Our latest collection featured in Fashion Week 2024",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "title": "Magazine Feature",
      "description": "Featured in Vogue magazine",
      "created_at": "2024-01-14T09:20:00.000000Z",
      "updated_at": "2024-01-14T09:20:00.000000Z"
    }
  ]
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Something went wrong.",
  "error": "Error message here"
}
```

---

### 2. Get Media Coverage by ID
Retrieve a specific media coverage entry by its ID.

**Endpoint:** `GET /media-coverage/{id}`

**Authentication:** Not required

**URL Parameters:**
- `id` (integer, required) - The ID of the media coverage entry

**Response:**
```json
{
  "success": true,
  "message": "Media coverage fetched successfully",
  "data": {
    "id": 1,
    "title": "Fashion Week Coverage",
    "description": "Our latest collection featured in Fashion Week 2024",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Response (Not Found):**
```json
{
  "success": false,
  "message": "Media coverage not found"
}
```

---

### 3. Create Media Coverage
Create a new media coverage entry.

**Endpoint:** `POST /media-coverage`

**Authentication:** Required (Sanctum token with admin role)

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Fashion Week Coverage",
  "description": "Our latest collection featured in Fashion Week 2024"
}
```

**Request Parameters:**
- `title` (string, nullable) - Title of the media coverage
- `description` (string, nullable) - Description of the media coverage

**Response:**
```json
{
  "success": true,
  "message": "Media coverage created successfully",
  "data": {
    "id": 1,
    "title": "Fashion Week Coverage",
    "description": "Our latest collection featured in Fashion Week 2024",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Responses:**

*Unauthorized (403):*
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

*Validation Error (422):*
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title must be a string."]
  }
}
```

---

### 4. Update Media Coverage
Update an existing media coverage entry.

**Endpoint:** `PUT /media-coverage/{id}`

**Authentication:** Required (Sanctum token with admin role)

**URL Parameters:**
- `id` (integer, required) - The ID of the media coverage entry to update

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Updated Fashion Week Coverage",
  "description": "Updated description of the media coverage"
}
```

**Request Parameters:**
- `title` (string, nullable) - Title of the media coverage
- `description` (string, nullable) - Description of the media coverage

**Response:**
```json
{
  "success": true,
  "message": "Media coverage updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Fashion Week Coverage",
    "description": "Updated description of the media coverage",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T11:45:00.000000Z"
  }
}
```

**Error Responses:**

*Unauthorized (403):*
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

*Not Found (404):*
```json
{
  "success": false,
  "message": "Media coverage not found"
}
```

*Validation Error (422):*
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "description": ["The description must be a string."]
  }
}
```

---

### 5. Delete Media Coverage
Delete a media coverage entry.

**Endpoint:** `DELETE /media-coverage/{id}`

**Authentication:** Required (Sanctum token with admin role)

**URL Parameters:**
- `id` (integer, required) - The ID of the media coverage entry to delete

**Request Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Media coverage deleted successfully"
}
```

**Error Responses:**

*Unauthorized (403):*
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

*Not Found (404):*
```json
{
  "success": false,
  "message": "Media coverage not found"
}
```

---

## Example cURL Requests

### Get All Media Coverage
```bash
curl -X GET http://localhost:8000/api/media-coverage
```

### Get Media Coverage by ID
```bash
curl -X GET http://localhost:8000/api/media-coverage/1
```

### Create Media Coverage
```bash
curl -X POST http://localhost:8000/api/media-coverage \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Fashion Week Coverage",
    "description": "Our latest collection featured in Fashion Week 2024"
  }'
```

### Update Media Coverage
```bash
curl -X PUT http://localhost:8000/api/media-coverage/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "description": "Updated description"
  }'
```

### Delete Media Coverage
```bash
curl -X DELETE http://localhost:8000/api/media-coverage/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Notes

- Both `title` and `description` fields are **nullable**, meaning they can be omitted or set to `null` in requests
- All timestamps are returned in ISO 8601 format
- The API returns data ordered by `created_at` in descending order (newest first) for the "Get All" endpoint
- Admin authentication is required for create, update, and delete operations
- All error responses include appropriate HTTP status codes (403, 404, 422, 500)

