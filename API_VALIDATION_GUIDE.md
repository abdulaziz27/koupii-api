# API Validation Guide for Frontend

## Register Endpoint: `POST /api/auth/register`

### Required Fields & Validation Rules

#### 1. **Name** (`name`)
- **Required**: Yes
- **Type**: String
- **Max Length**: 255 characters
- **Example**: `"Abdul Aziz"`

#### 2. **Email** (`email`)
- **Required**: Yes
- **Type**: Valid email address
- **Unique**: Must not exist in database
- **Example**: `"user@example.com"`

#### 3. **Password** (`password`)
- **Required**: Yes
- **Min Length**: 8 characters
- **Requirements**: Must contain:
  - At least 1 lowercase letter (a-z)
  - At least 1 uppercase letter (A-Z)
  - At least 1 number (0-9)
  - At least 1 special character (@$!%*?&)
- **Valid Examples**:
  - `"Str0ng@Pass1"`
  - `"MySecure123!"`
  - `"Complex&Pass2024"`

#### 4. **Role** (`role`)
- **Required**: Yes
- **Valid Values**: `"teacher"`, `"student"`, `"admin"`
- **Case Sensitive**: Must be lowercase
- **Example**: `"student"`

### Example Valid Request

```json
{
  "name": "Abdul Aziz",
  "email": "abdul@example.com",
  "password": "Str0ng@Pass1",
  "role": "teacher"
}
```

### Common 422 Validation Errors

#### Invalid Email
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["Email must be a valid email address"]
  }
}
```

#### Weak Password
```json
{
  "message": "Validation failed", 
  "errors": {
    "password": [
      "Password must be at least 8 characters",
      "The password field format is invalid."
    ]
  }
}
```

#### Invalid Role
```json
{
  "message": "Validation failed",
  "errors": {
    "role": ["Role must be teacher, student, or admin"]
  }
}
```

#### Email Already Exists
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["Email already exists"]
  }
}
```

### Frontend Implementation Tips

#### 1. **Password Validation on Frontend**
```javascript
const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

function validatePassword(password) {
  return passwordRegex.test(password);
}
```

#### 2. **Handle API Response**
```javascript
try {
  const response = await fetch('/api/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(userData)
  });

  if (response.status === 422) {
    const errorData = await response.json();
    // Handle validation errors
    console.log('Validation errors:', errorData.errors);
  } else if (response.status === 201) {
    const successData = await response.json();
    // Handle success
    console.log('Token:', successData.token);
  }
} catch (error) {
  console.error('Network error:', error);
}
```

#### 3. **Display Error Messages**
```javascript
function displayErrors(errors) {
  Object.keys(errors).forEach(field => {
    const errorMessages = errors[field];
    errorMessages.forEach(message => {
      // Show error message for specific field
      showFieldError(field, message);
    });
  });
}
```

### Password Requirements UI

Recommend showing password requirements to users:

- ✅ At least 8 characters
- ✅ Contains uppercase letter (A-Z)
- ✅ Contains lowercase letter (a-z)
- ✅ Contains number (0-9)
- ✅ Contains special character (@$!%*?&)

### CORS Headers

Make sure to include proper headers in your requests:

```javascript
headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Origin': 'https://koupii.magercoding.com' // Your frontend domain
}
```

### Success Response (201)

```json
{
  "message": "Registered successfully",
  "token": "44|4gVLo2U3n8LGz8AFBnVBFV8hBOMOebmo8hT7x0POb159886e"
}
```

Store the token for subsequent API requests:

```javascript
// Store token
localStorage.setItem('auth_token', response.token);

// Use token in subsequent requests
headers: {
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```
