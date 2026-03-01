# 🔍 Global Search, Filter & Pagination System

## Overview
This document explains the unified search, filter, and pagination system implemented across the application.

## Components

### 1. **Trait: `HasIndexFilters`**
Location: `app/Traits/HasIndexFilters.php`

A reusable trait that provides search and filter functionality for any controller.

#### Methods:

##### `applyIndexFilters()`
Applies search and filters to a query builder.

**Parameters:**
- `$query` (Builder): The Eloquent query builder
- `$request` (Request): The HTTP request object
- `$searchable` (array): Columns to search in (supports relations with dot notation)
- `$filters` (array): Direct column filters `['query_param' => 'column_name']`
- `$relationFilters` (array): Relation-based filters `['query_param' => ['relation', 'column']]`

**Example:**
```php
$this->applyIndexFilters(
    $query,
    $request,
    ['name', 'email', 'patient.name'],  // Searchable columns
    ['status' => 'status'],              // Direct filters
    ['department_id' => ['employee', 'department_id']]  // Relation filters
);
```

##### `getPerPage()`
Returns the pagination size from request, defaulting to 15.

**Parameters:**
- `$request` (Request): The HTTP request object
- `$default` (int): Default per page value (default: 15)

**Returns:** `int` - One of: 10, 15, 25, 50, 100

---

### 2. **Blade Component: `<x-index-filters>`**
Location: `resources/views/components/index-filters.blade.php`

A reusable UI component for search, filters, and pagination controls.

#### Props:
- `action` (optional): Form action URL (defaults to current URL)
- `searchPlaceholder` (optional): Placeholder text for search input

#### Usage:

**Basic Usage:**
```blade
<x-index-filters>
    {{-- Add custom filters here --}}
</x-index-filters>
```

**With Custom Filters:**
```blade
<x-index-filters :searchPlaceholder="'Search patients...'">
    <div class="md:col-span-3">
        <label>Payment Type</label>
        <select name="payment_type">
            <option value="">All</option>
            <option value="cash">Cash</option>
            <option value="insurance">Insurance</option>
        </select>
    </div>
</x-index-filters>
```

#### Features:
- ✅ Search input
- ✅ Custom filter slots
- ✅ Per-page selector (10, 15, 25, 50)
- ✅ Search button with icon
- ✅ Reset button (auto-shows when filters are active)
- ✅ Responsive grid layout
- ✅ Bilingual support (Arabic/English)

---

## Controller Implementation

### Step 1: Add Trait
```php
use App\Traits\HasIndexFilters;

class YourController extends Controller
{
    use HasIndexFilters;
}
```

### Step 2: Implement Index Method
```php
public function index(Request $request)
{
    $query = YourModel::query();
    
    $this->applyIndexFilters(
        $query,
        $request,
        ['column1', 'column2', 'relation.column'],  // Searchable
        ['filter_key' => 'column_name']              // Filters
    );
    
    $items = $query->latest()
        ->paginate($this->getPerPage($request))
        ->withQueryString();  // Preserve query params in pagination
    
    return view('your.index', compact('items'));
}
```

---

## View Implementation

### Basic Template:
```blade
@extends('layouts.app')

@section('content')
    <h2>Your List</h2>
    
    {{-- Search & Filters --}}
    <x-index-filters :searchPlaceholder="'Search...'">
        {{-- Add custom filters here --}}
        <div class="md:col-span-3">
            <label>Filter Name</label>
            <select name="filter_key">
                <option value="">All</option>
                <option value="value1">Option 1</option>
            </select>
        </div>
    </x-index-filters>
    
    {{-- Results Table --}}
    <div class="bg-white rounded-lg shadow">
        <table>
            {{-- Your table content --}}
        </table>
        
        {{-- Pagination --}}
        @if($items->hasPages())
            <div class="p-4">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection
```

---

## Query String Parameters

The system uses the following query parameters:

| Parameter | Description | Example |
|-----------|-------------|---------|
| `search` | Search term | `?search=john` |
| `per_page` | Items per page | `?per_page=25` |
| `page` | Current page | `?page=2` |
| Custom filters | Your filter keys | `?payment_type=cash` |

All parameters are preserved across pagination using `->withQueryString()`.

---

## Real Examples

### Example 1: Patients List
```php
// Controller
public function index(Request $request)
{
    $query = Patient::with(['insuranceCompany', 'charityEntity']);
    
    $this->applyIndexFilters(
        $query,
        $request,
        ['name', 'name_ar', 'file_number', 'id_number', 'phone'],
        ['payment_type' => 'payment_type']
    );
    
    $patients = $query->latest()
        ->paginate($this->getPerPage($request))
        ->withQueryString();
    
    return view('patients.index', compact('patients'));
}
```

```blade
{{-- View --}}
<x-index-filters :searchPlaceholder="'Name, file no, ID, phone...'">
    <div class="md:col-span-3">
        <label>Payment Type</label>
        <select name="payment_type">
            <option value="">All</option>
            <option value="cash">Cash</option>
            <option value="insurance">Insurance</option>
            <option value="charity">Charity</option>
        </select>
    </div>
</x-index-filters>
```

### Example 2: Users List with Relation Filter
```php
// Controller
public function index(Request $request)
{
    $query = User::with('employee.department');
    
    $this->applyIndexFilters(
        $query,
        $request,
        ['username', 'email', 'employee.name', 'employee.name_ar'],
        [],
        ['department_id' => ['employee', 'department_id']]
    );
    
    $users = $query->orderBy('username')
        ->paginate($this->getPerPage($request))
        ->withQueryString();
    
    $departments = Department::orderBy('name')->get();
    return view('users.index', compact('users', 'departments'));
}
```

```blade
{{-- View --}}
<x-index-filters :searchPlaceholder="'Username, email, name...'">
    <div class="md:col-span-3">
        <label>Department</label>
        <select name="department_id">
            <option value="">All</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
</x-index-filters>
```

---

## Benefits

✅ **DRY (Don't Repeat Yourself)** - Write once, use everywhere  
✅ **Consistency** - Same UX across all list pages  
✅ **Maintainability** - Update once, affects all pages  
✅ **Flexibility** - Easy to add custom filters  
✅ **Performance** - Efficient query building  
✅ **UX** - Preserved filters in pagination  
✅ **Bilingual** - Arabic & English support built-in  
✅ **Laravel Best Practices** - Following framework conventions

---

## Advanced Usage

### Custom Query Modifications
```php
$this->applyIndexFilters($query, $request, $searchable, $filters);

// Add custom conditions after filters
$query->where('is_active', true)
      ->whereNotNull('verified_at');
```

### Conditional Filters
```php
$filters = [];

if ($section === 'followup') {
    $filters['payment_type'] = 'payment_type';
}

$this->applyIndexFilters($query, $request, $searchable, $filters);
```

### Complex Searches
```php
// The trait supports relation searches with dot notation
$this->applyIndexFilters(
    $query,
    $request,
    [
        'invoice_number',
        'patient.name',           // Searches in relation
        'patient.file_number',
        'patient.phone'
    ],
    []
);
```

---

## Pagination Configuration

Pagination is configured globally in `AppServiceProvider`:

```php
use Illuminate\Pagination\Paginator;

public function boot()
{
    Paginator::useTailwind();  // Use Tailwind CSS for pagination
}
```

---

## Troubleshooting

**Q: Filters not working?**  
A: Make sure you're using `->withQueryString()` on pagination.

**Q: Search not finding results?**  
A: Check column names in the searchable array match your database schema.

**Q: Reset button not showing?**  
A: The component auto-detects active filters from the request.

**Q: Custom filter not persisting?**  
A: Ensure the filter name attribute matches the query parameter.

---

## Future Enhancements

- [ ] Debounced search with Alpine.js
- [ ] Saved filter presets per user
- [ ] Export to Excel with same filters
- [ ] Policy-based filter visibility
- [ ] Live search without page reload
- [ ] Advanced date range filters

---

**Last Updated:** {{ now()->format('Y-m-d') }}  
**Author:** Development Team
