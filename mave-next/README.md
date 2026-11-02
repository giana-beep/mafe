# Mave — Next.js migration

## Run locally
1. Install Node.js 20+ (https://nodejs.org).
2. Install MySQL via XAMPP and start MySQL.
3. Open terminal in this folder and run `npm install` then `npm run dev`.
4. Visit http://localhost:3000.

## Migration status
- Next.js App Router scaffold and responsive landing page are set up.
- Existing public CSS/JS assets copied from source project.
- Ingredient/product/routine/auth/community/admin workflows still need conversion from Express + EJS to Next.js Server Actions/API routes and React components.
- Database credentials should be placed in `.env.local` (never commit credentials).
