# CRM Platform

A Laravel-based customer relationship management platform for the first vertical slice of the business workflow: lead capture, assignment, qualification, conversion to customer/opportunity, quotation approval, and ERP order generation.

## Overview

This project implements a modular CRM domain with:

- leads and lifecycle tracking
- customer 360 views
- sales opportunities with stage progression
- quotation creation and approval
- ERP order conversion records
- audit logging for workflow events
- REST API endpoints for the end-to-end flow

## Current milestone

The first milestone is the lead-to-order workflow:

1. create lead
2. assign sales owner
3. log activity
4. qualify lead
5. convert lead to customer + opportunity
6. move opportunity to proposal stage
7. create quotation
8. approve quotation
9. convert quotation to ERP order
10. verify the customer record contains the related history

## Tech stack

- Laravel 13
- PHP 8.4
- SQLite for local development
- Eloquent ORM
- REST API routing
- Blade views

## Project structure

- `app/Http/Controllers` — API and request handling
- `app/Models` — CRM domain models
- `database/migrations` — schema for core CRM tables
- `resources/views` — UI for customer details
- `routes/api.php` — API endpoints
- `routes/web.php` — web routes
- `tests/Feature/LeadToOrderWorkflowTest.php` — acceptance coverage for the first workflow

## Local setup

```bash
cd "c:\Users\kahya\Downloads\Programs\CRM"
"C:\php\x64\php.exe" artisan migrate
"C:\php\x64\php.exe" artisan serve --host 127.0.0.1 --port 8000
```

Then open:

- http://127.0.0.1:8000

## API endpoints

```http
POST /api/v1/leads
POST /api/v1/leads/{lead}/assign
POST /api/v1/leads/{lead}/qualify
POST /api/v1/leads/{lead}/convert
POST /api/v1/activities
POST /api/v1/opportunities/{opportunity}/stage
POST /api/v1/quotations
POST /api/v1/quotations/{quotation}/approve
POST /api/v1/quotations/{quotation}/convert
```

## Validation

The workflow is validated with Laravel feature tests, including the lead-to-ERP-order path in:

- `tests/Feature/LeadToOrderWorkflowTest.php`

Run it with:

```bash
"C:\php\x64\php.exe" artisan test --filter=LeadToOrderWorkflowTest
```

## Notes

This README reflects the actual CRM implementation in the current workspace and the first working milestone described in the project specification.
