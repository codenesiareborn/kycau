# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Filament v4 admin panel for managing sales data. The application handles sales transactions with customers, products, and includes Excel import functionality for bulk data processing. It uses Indonesian city data from the `laravolt/indonesia` package.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Admin Panel**: Filament v4
- **Frontend**: Livewire, Volt, Flux, Vite, Tailwind CSS 4
- **Database**: SQLite (default)
- **Testing**: Pest PHP
- **Code Quality**: Laravel Pint
- **Excel Processing**: Maatwebsite/Excel

## Development Commands

### Running the Application

```bash
# Development server with concurrent processes (server, queue, logs, vite)
composer dev

# Or run services individually:
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing

```bash
# Run all tests
composer test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run specific test method
php artisan test --filter test_method_name

# Run tests with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check formatting without fixing
./vendor/bin/pint --test
```

### Database

```bash
# Run migrations
php artisan migrate

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name
```

### Asset Building

```bash
# Build assets for production
npm run build

# Development with hot reload
npm run dev
```

### Filament

```bash
# Create new Filament resource
php artisan make:filament-resource ModelName

# Create Filament widget
php artisan make:filament-widget WidgetName

# Upgrade Filament (runs automatically after composer update)
php artisan filament:upgrade
```

## Architecture

### Data Model Relationships

- **Sale** → has many **SaleItem** → belongs to **Product**
- **Sale** → belongs to **Customer** → belongs to **City** (from laravolt/indonesia)
- Sales store: month, sale_date, sale_number, customer_id, total_amount
- SaleItems store: quantity, line_total for each product in a sale

### Filament Structure (Non-Standard Pattern)

This project uses a **custom organization pattern** for Filament resources instead of the default structure:

```
app/Filament/Resources/
├── Sales/
│   ├── SaleResource.php         # Main resource class
│   ├── Schemas/SaleForm.php     # Form schema (extracted)
│   ├── Tables/SalesTable.php    # Table configuration (extracted)
│   └── Pages/                   # Page classes
├── Customers/
│   ├── CustomerResource.php
│   ├── Schemas/CustomerForm.php
│   ├── Tables/CustomersTable.php
│   └── Pages/
└── Products/
    ├── ProductResource.php
    ├── Schemas/ProductForm.php
    ├── Tables/ProductsTable.php
    └── Pages/
```

**Key Pattern**: Forms and tables are extracted into separate `Schemas/` and `Tables/` subdirectories instead of being defined inline in the Resource class. When creating new resources, follow this pattern:
- Create a `Schemas/ModelNameForm.php` with a static `configure(Schema $schema)` method
- Create a `Tables/ModelNameTable.php` with a static `configure(Table $table)` method

### Dashboard Architecture

The dashboard ([app/Filament/Pages/Dashboard.php](app/Filament/Pages/Dashboard.php)) uses:
- **HasFiltersForm** trait for global filtering
- **InteractsWithPageFilters** trait in widgets to access filters
- Filters: date_from, date_to, product_id, city_id
- All widgets receive and apply these filters via `$this->pageFilters`

Widgets include:
- `DashboardOverview` - Summary stats
- `SalesChart` - Time series sales visualization
- `ProductSalesChart` - Sales by product
- `CitySalesChart` - Sales by city
- `SalesDataTable` - Detailed sales table with filters
- `UploadHistoryTable` - File upload history

### Excel Import System

The [SalesImport](app/Imports/SalesImport.php) class handles complex Excel file imports:
- Supports flexible column mapping with synonyms (Indonesian/English)
- Handles month values in multiple formats: numeric, Roman numerals (I-XII), or month names
- Processes date values from both numeric and Excel date formats
- Auto-creates customers and products during import
- Handles multi-line product cells (one sale can have multiple products)
- Splits line totals across multiple products when needed
- Uses transactions for data integrity
- Implements chunking (500 rows) and batch inserts for performance
- Integrates with laravolt/indonesia for city lookup and matching

**Expected Excel format**: Headers like BLN/TGL/NO/NAMA/NO. HP/EMAIL/ALAMAT/KOTA/PRODUK/QTY/TOTAL PEMBELIAN (supports synonyms)

### Indonesian Location Data

The application uses `laravolt/indonesia` for Indonesian administrative data:
- Tables: `indonesia_provinces`, `indonesia_cities`, `indonesia_districts`, `indonesia_villages`
- Customer model has `city_id` relationship to `indonesia_cities`
- City names are normalized by removing "KOTA " and "KABUPATEN " prefixes in display
- City search uses `Indonesia::search()` facade for fuzzy matching

## Important Implementation Notes

### When Creating New Resources

1. Follow the custom Schemas/Tables pattern described above
2. Create separate files for form schemas and table configurations
3. Use `InteractsWithPageFilters` trait if the resource needs dashboard filters
4. Eager load relationships in the resource's `table()` method via `modifyQueryUsing()`

### When Creating New Widgets

1. Add `use InteractsWithPageFilters;` to access dashboard filters
2. Access filters via `$this->pageFilters` array
3. Apply filters in query methods (see [SalesDataTable.php](app/Filament/Widgets/SalesDataTable.php) for reference)
4. Set appropriate `$sort` property for widget ordering
5. Set `$columnSpan` for layout control

### Working with Money/Decimals

- Sales use `decimal:2` cast for `total_amount`
- Format display as: `'Rp ' . number_format($state, 0, ',', '.')`
- Import max: 9999999999999.99
- Quantity max: 2147483647

### Database Seeding

Uses SQLite by default. The database file is created at `database/database.sqlite` during setup.
