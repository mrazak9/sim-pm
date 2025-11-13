# 🚀 QUICK START GUIDE - Vibe Coding SIM-PM

## ⚡ Setup dalam 30 Menit

### 1. Install Tools (10 menit)
```bash
# Install Cursor AI
# Download dari: https://cursor.sh
# Sign up & activate Pro ($20/bulan)

# Verify installations
php -v        # Should be 8.1+
composer -v   # Composer installed
node -v       # Should be 18+
psql --version # PostgreSQL 14+
```

### 2. Create Project (5 menit)
```bash
# Create Laravel project
composer create-project laravel/laravel sim-pm
cd sim-pm

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sim_pm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Install Packages (10 menit)
```bash
# Backend essentials
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require intervention/image

# Dev dependencies
composer require --dev laravel/pint
composer require --dev barryvdh/laravel-debugbar

# Frontend
npm install
npm install vue@3 vue-router@4 pinia
npm install @headlessui/vue @heroicons/vue
npm install chart.js vue-chartjs
npm install axios dayjs
npm install vee-validate yup
```

### 4. Setup Cursor Rules (5 menit)
```bash
# Copy .cursorrules to project root
cp /path/to/.cursorrules .cursorrules

# Create basic docs
echo "# SIM Penjaminan Mutu" > README.md
```

## 🎯 Your First Feature in 2 Hours

### Step 1: Setup Authentication (45 min)

**Prompt to Cursor:**
```
Setup Laravel Sanctum authentication system with the following:

1. Install and configure Sanctum
2. Create authentication API endpoints:
   - POST /api/auth/register
   - POST /api/auth/login
   - POST /api/auth/logout
   - GET /api/auth/user

3. Create AuthController with proper validation
4. Use Form Requests for validation
5. Return consistent API responses
6. Add rate limiting middleware
7. Include feature tests

Follow our .cursorrules standards.
```

**Test with Postman:**
```json
// POST /api/auth/register
{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Step 2: User Management CRUD (45 min)

**Prompt to Cursor:**
```
Create User management CRUD API with the following:

1. UserController with RESTful methods:
   - index (list with pagination)
   - show (single user)
   - store (create)
   - update
   - destroy (soft delete)

2. Create Form Requests:
   - StoreUserRequest
   - UpdateUserRequest

3. Create UserResource for API responses

4. Add routes in api.php with auth middleware

5. Include filtering, sorting, and search

6. Write feature tests for all endpoints

Follow repository pattern and service layer.
```

### Step 3: Basic Dashboard (30 min)

**Prompt to Cursor:**
```
Create basic dashboard with Vue 3:

1. Dashboard layout component with sidebar
2. Stats cards showing:
   - Total Users
   - Active Sessions
   - Recent Activities

3. Use Composition API with script setup
4. Style with Tailwind CSS
5. Make it responsive
6. Add loading states
7. Fetch data from API
```

## 📋 Daily Vibe Coding Workflow

### Morning (2-3 hours)
1. ✅ **Plan**: Break feature into small tasks (15 min)
2. ✅ **Generate**: Use AI to create base code (30 min)
3. ✅ **Review**: Read & understand generated code (20 min)
4. ✅ **Test**: Run and test the code (25 min)
5. ✅ **Commit**: Git commit working code (10 min)
6. ✅ **Repeat**: 2-3 features per session

### Afternoon (2-3 hours)
1. ✅ **Integration**: Connect frontend with backend
2. ✅ **Refinement**: Improve code quality
3. ✅ **Testing**: Write comprehensive tests
4. ✅ **Documentation**: Update docs
5. ✅ **Review**: Code review (if team)

## 🔥 Power Prompts

### Generate Migration
```
Create migration for [table_name] table with fields:
- id (bigIncrements)
- [field_name] ([type], [nullable/required])
- [relationship]_id (foreignId with constraint)
- timestamps and soft deletes

Add indexes for foreign keys and [searchable_fields].
Follow our database naming conventions.
```

### Generate Model
```
Create [ModelName] model with:
- Relationships: belongsTo/hasMany [related models]
- Fillable properties: [fields]
- Casts: [field:type]
- Scopes: [scope methods]
- Validation rules (as property)

Add PHPDoc comments.
Follow our coding standards.
```

### Generate Controller
```
Create [ControllerName] with RESTful CRUD operations:
- Use Form Requests for validation
- Use API Resources for responses  
- Inject [Service/Repository] dependency
- Implement try-catch error handling
- Return proper HTTP status codes
- Add pagination for index method
- Include PHPDoc comments

Follow repository pattern.
```

### Generate Vue Component
```
Create [ComponentName] Vue component:
- Use Composition API with script setup
- Props: [props with types]
- Emits: [events]
- Style with Tailwind CSS
- Make responsive
- Add loading and error states
- Fetch data from [API endpoint]
- Handle form submission
```

### Generate Test
```
Create feature test for [feature]:

Test cases:
1. [Success case]
2. [Validation failure]
3. [Unauthorized access]
4. [Not found scenario]

Use factories for test data.
Assert response structure and status codes.
Mock external services if needed.
```

## 🎨 Component Patterns

### API Controller Pattern
```php
class ResourceController extends Controller
{
    public function __construct(
        private ResourceService $service
    ) {}
    
    public function index(Request $request)
    {
        try {
            $resources = $this->service->paginate(
                $request->all()
            );
            
            return ResourceResource::collection($resources);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch resources'
            ], 500);
        }
    }
}
```

### Vue Component Pattern
```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useResourceStore } from '@/stores/resource'

const store = useResourceStore()
const loading = ref(false)
const error = ref(null)

onMounted(async () => {
  await fetchData()
})

const fetchData = async () => {
  loading.value = true
  try {
    await store.fetchResources()
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
```

## ⚠️ Common Pitfalls

### ❌ DON'T
- Blind copy-paste without review
- Skip testing
- Ignore validation
- Hardcode values
- Skip error handling
- Use SELECT *
- Create N+1 queries
- Store passwords in plain text

### ✅ DO
- Review all generated code
- Write tests alongside features
- Validate all inputs
- Use environment variables
- Handle errors gracefully
- Select specific columns
- Use eager loading
- Hash passwords with bcrypt

## 📊 Progress Tracking

### Week 1-2: Foundation
- [ ] Authentication & Authorization
- [ ] User Management
- [ ] Master Data (Referensi)
- [ ] Basic Dashboard
- [ ] Layout Components

### Week 3-4: Core Modules Start
- [ ] Document Management Basic
- [ ] IKU Structure & CRUD
- [ ] Akreditasi Period Management

### Week 5-8: Core Modules Complete
- [ ] Document Management Complete
- [ ] IKU Tracking & Dashboard
- [ ] Akreditasi Full Features

### Week 9-12: Advanced Features
- [ ] Audit Module
- [ ] Kuesioner Module
- [ ] SPMI Module

## 🆘 Getting Help

### AI Assistant
```
# For explaining code
"Explain this code in simple terms"

# For debugging
"This code has a bug. Find and fix it."

# For refactoring
"Refactor this code following SOLID principles"

# For optimization
"Optimize this code for better performance"
```

### Documentation
- Laravel: https://laravel.com/docs
- Vue.js: https://vuejs.org
- Tailwind: https://tailwindcss.com
- Cursor: https://cursor.sh/docs

### Community
- Laravel Discord
- Vue Discord  
- Stack Overflow
- GitHub Issues

## 🎉 Success Metrics

### Daily Goals
- ✅ 2-3 features completed
- ✅ All tests passing
- ✅ Code committed to Git
- ✅ Documentation updated

### Weekly Goals
- ✅ 1 complete module
- ✅ Code reviewed
- ✅ UAT with users
- ✅ Performance optimized

## 💡 Pro Tips

1. **Start Small**: Begin with simple features
2. **Iterate**: Improve code in small steps
3. **Test Everything**: Don't skip tests
4. **Review AI Code**: Always understand before accepting
5. **Document**: Keep docs updated
6. **Commit Often**: Small, focused commits
7. **Learn**: Study patterns from generated code
8. **Ask AI**: Use AI to explain complex code

---

## 🚀 Ready to Start?

```bash
# Open Cursor AI
cursor .

# Start with your first prompt
# Cmd/Ctrl + K to open AI panel

# Happy Vibe Coding! 🎉
```

**Remember**: AI is your coding partner. Review, test, and understand everything!

**Goal**: Working SIM-PM in 3-6 months with vibe coding! 💪
