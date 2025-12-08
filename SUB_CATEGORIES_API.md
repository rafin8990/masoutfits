# Sub Categories API Documentation

This document describes the API endpoints for managing sub categories in the system. Sub categories can be associated with multiple categories (many-to-many relationship).

## Base URL
```
/api
```

## Authentication
- **Public Endpoints**: No authentication required
- **Protected Endpoints**: Requires authentication via Sanctum token and admin role

---

## Endpoints

### 1. Get All Sub Categories
Retrieve a list of all sub categories with optional filtering.

**Endpoint:** `GET /sub-categories`

**Authentication:** Not required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `category` | integer | No | Filter by category ID |
| `name` | string | No | Filter by category name |
| `subCategory` | string | No | Filter by exact sub category name |
| `searchTerm` | string | No | Search sub categories by name (partial match) |

**Example Request:**
```bash
GET /api/sub-categories?category=1&searchTerm=shirt
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "SubCategories fetched successfully",
  "data": [
    {
      "id": 1,
      "name": "T-Shirts",
      "image": "http://example.com/uploads/subcategory/image.jpg",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "categories": [
        {
          "id": 1,
          "name": "Men's Wear",
          "image": "http://example.com/uploads/category/image.jpg",
          "created_at": "2025-01-01T00:00:00.000000Z",
          "updated_at": "2025-01-01T00:00:00.000000Z"
        },
        {
          "id": 2,
          "name": "Casual Wear",
          "image": "http://example.com/uploads/category/image2.jpg",
          "created_at": "2025-01-01T00:00:00.000000Z",
          "updated_at": "2025-01-01T00:00:00.000000Z"
        }
      ]
    }
  ]
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Something went wrong while fetching the subcategories.",
  "error": "Error message"
}
```

---

### 2. Get Sub Category by ID
Retrieve a specific sub category by its ID.

**Endpoint:** `GET /sub-categories/{id}`

**Authentication:** Not required

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Sub category ID |

**Example Request:**
```bash
GET /api/sub-categories/1
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "SubCategory fetched successfully",
  "data": {
    "id": 1,
    "name": "T-Shirts",
    "image": "http://example.com/uploads/subcategory/image.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "categories": [
      {
        "id": 1,
        "name": "Men's Wear",
        "image": "http://example.com/uploads/category/image.jpg",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "SubCategory not found"
}
```

---

### 3. Create Sub Category
Create a new sub category with one or more associated categories.

**Endpoint:** `POST /sub-categories`

**Authentication:** Required (Admin role)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Sub category name (max 255 characters) |
| `image` | file | No | Image file (jpeg, png, jpg, gif, max 2MB) |
| `category_ids` | array | Yes | Array of category IDs (minimum 1 category required) |
| `category_ids.*` | integer | Yes | Each category ID must exist in categories table |

**Example Request (cURL):**
```bash
curl -X POST http://example.com/api/sub-categories \
  -H "Authorization: Bearer {token}" \
  -F "name=T-Shirts" \
  -F "image=@/path/to/image.jpg" \
  -F "category_ids[]=1" \
  -F "category_ids[]=2"
```

**Example Request (JSON with FormData):**
```javascript
const formData = new FormData();
formData.append('name', 'T-Shirts');
formData.append('image', imageFile);
formData.append('category_ids[]', 1);
formData.append('category_ids[]', 2);

fetch('/api/sub-categories', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer {token}'
  },
  body: formData
});
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "SubCategory created successfully",
  "data": {
    "id": 1,
    "name": "T-Shirts",
    "image": "http://example.com/public/uploads/subcategory/1234567890_abc123.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "categories": [
      {
        "id": 1,
        "name": "Men's Wear",
        "image": "http://example.com/uploads/category/image.jpg",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      },
      {
        "id": 2,
        "name": "Casual Wear",
        "image": "http://example.com/uploads/category/image2.jpg",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

**Error Response (403 - Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Error Response (422 - Validation Failed):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "category_ids": ["The category ids field is required."],
    "category_ids.0": ["The selected category_ids.0 is invalid."]
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Something went wrong.",
  "error": "Error message"
}
```

---

### 4. Update Sub Category
Update an existing sub category and its associated categories.

**Endpoint:** `POST /sub-categories/{id}`

**Authentication:** Required (Admin role)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Sub category ID |

**Request Body (Form Data):**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Sub category name (max 255 characters) |
| `image` | file | No | Image file (jpeg, png, jpg, gif, max 2MB). If provided, old image will be deleted. |
| `category_ids` | array | Yes | Array of category IDs (minimum 1 category required) |
| `category_ids.*` | integer | Yes | Each category ID must exist in categories table |

**Example Request (cURL):**
```bash
curl -X POST http://example.com/api/sub-categories/1 \
  -H "Authorization: Bearer {token}" \
  -F "name=Updated T-Shirts" \
  -F "image=@/path/to/new-image.jpg" \
  -F "category_ids[]=1" \
  -F "category_ids[]=3"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "SubCategory updated successfully",
  "data": {
    "id": 1,
    "name": "Updated T-Shirts",
    "image": "http://example.com/public/uploads/subcategory/1234567890_xyz789.jpg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T12:00:00.000000Z",
    "categories": [
      {
        "id": 1,
        "name": "Men's Wear",
        "image": "http://example.com/uploads/category/image.jpg",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      },
      {
        "id": 3,
        "name": "Formal Wear",
        "image": "http://example.com/uploads/category/image3.jpg",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

**Error Response (403 - Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Error Response (404 - Not Found):**
```json
{
  "success": false,
  "message": "SubCategory not found"
}
```

**Error Response (422 - Validation Failed):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "category_ids": ["The category ids field is required."]
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Something went wrong.",
  "error": "Error message"
}
```

---

### 5. Delete Sub Category
Delete a sub category and its associated image file.

**Endpoint:** `DELETE /sub-categories/{id}`

**Authentication:** Required (Admin role)

**Headers:**
```
Authorization: Bearer {token}
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Sub category ID |

**Example Request:**
```bash
curl -X DELETE http://example.com/api/sub-categories/1 \
  -H "Authorization: Bearer {token}"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "SubCategory deleted successfully"
}
```

**Error Response (403 - Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Error Response (404 - Not Found):**
```json
{
  "success": false,
  "message": "SubCategory not found"
}
```

---

## Data Models

### Sub Category Object
```json
{
  "id": 1,
  "name": "T-Shirts",
  "image": "http://example.com/uploads/subcategory/image.jpg",
  "created_at": "2025-01-01T00:00:00.000000Z",
  "updated_at": "2025-01-01T00:00:00.000000Z",
  "categories": [
    {
      "id": 1,
      "name": "Men's Wear",
      "image": "http://example.com/uploads/category/image.jpg",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Category Object (included in sub category response)
```json
{
  "id": 1,
  "name": "Men's Wear",
  "image": "http://example.com/uploads/category/image.jpg",
  "created_at": "2025-01-01T00:00:00.000000Z",
  "updated_at": "2025-01-01T00:00:00.000000Z"
}
```

---

## Validation Rules

### Create/Update Sub Category
- **name**: Required, string, maximum 255 characters
- **image**: Optional, must be an image file (jpeg, png, jpg, gif), maximum 2MB
- **category_ids**: Required, must be an array with at least 1 element
- **category_ids.***: Each category ID must exist in the categories table

---

## Notes

1. **Many-to-Many Relationship**: Sub categories can be associated with multiple categories. When creating or updating, you must provide at least one category ID in the `category_ids` array.

2. **Image Handling**: 
   - Images are stored in `public/uploads/subcategory/`
   - When updating with a new image, the old image is automatically deleted
   - When deleting a sub category, its associated image is also deleted

3. **Authentication**: All create, update, and delete operations require:
   - Valid Sanctum authentication token
   - User must have `admin` role

4. **Query Filtering**: The `getAllSubCategories` endpoint supports multiple query parameters that can be combined:
   - Filter by category ID: `?category=1`
   - Filter by category name: `?name=Men's Wear`
   - Filter by exact sub category name: `?subCategory=T-Shirts`
   - Search by partial name: `?searchTerm=shirt`

5. **Response Format**: All responses follow a consistent format:
   - `success`: Boolean indicating if the operation was successful
   - `message`: Human-readable message
   - `data`: The actual data (for successful operations)
   - `errors`: Validation errors (for validation failures)
   - `error`: Error message (for server errors)

---

## Example Use Cases

### Use Case 1: Get all sub categories for a specific category
```bash
GET /api/sub-categories?category=1
```

### Use Case 2: Search sub categories by name
```bash
GET /api/sub-categories?searchTerm=shirt
```

### Use Case 3: Create a sub category with multiple categories
```bash
POST /api/sub-categories
Content-Type: multipart/form-data
Authorization: Bearer {token}

name=Summer Collection
image=@summer.jpg
category_ids[]=1
category_ids[]=2
category_ids[]=3
```

### Use Case 4: Update sub category categories only
```bash
POST /api/sub-categories/1
Content-Type: multipart/form-data
Authorization: Bearer {token}

name=Summer Collection
category_ids[]=2
category_ids[]=4
```

---

## Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 403 | Unauthorized (requires admin role) |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

