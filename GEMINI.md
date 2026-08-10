# Triangle Cart

## Stack

Laravel 12
Filament 5.2
Next.js frontend
Headless CMS

## Rules

- Never introduce duplicate business logic.
- Services contain business logic.
- Controllers remain thin.
- Filament Resources should delegate to services.
- Prefer Actions for complex workflows.
- Use existing models before creating new ones.
- Respect current naming conventions.
- Maintain backward compatibility unless instructed.
- Keep commits focused on the requested feature.

## Database

- Prefix: tc_
- utf8mb4_0900_ai_ci

## Architecture

Frontend
    Next.js

↓

API

↓

Laravel

↓

Services

↓

Repositories (where applicable)

↓

Models

## Before Coding

1. Inspect related code.
2. Reuse existing services.
3. Minimize changes.
4. Follow current architecture.
5. Update only affected files.
6. Avoid unnecessary dependencies.