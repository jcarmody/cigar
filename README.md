# My Humidor Journal

A lightweight, private PHP + SQLite web app for tracking your cigar inventory (humidor) and detailed tasting reviews — inspired by the Android/iOS app "My Humidor - Cigar Journal".

## Features

- **Inventory / Humidor tracking**
  - Multiple humidors supported
  - Brand, name, vitola, wrapper, origin, strength, quantity, purchase date, price, notes, photo
  - Automatic aging (days) calculation
  - Search, filter by strength, sort by name / qty / age

- **Detailed Reviews / Journal**
  - Structured stage notes: cold draw → first / second / final third
  - Multi-attribute ratings (overall, appearance, construction, burn, draw, flavor, aroma) on 0–5 scale
  - Pairings, location, free notes, photo
  - Optional link to inventory item + automatic quantity decrement when you smoke one
  - Search across notes, sort by date / rating / name

- **Dashboard** with quick stats and recent items
- Fully self-contained: SQLite database + local photo uploads
- Clean dark “cigar lounge” UI, mobile-friendly
- No external accounts or cloud required

## Requirements

- PHP 8.0+ with PDO SQLite extension (almost always present)
- Write permissions for `data/` and `uploads/` directories

## Installation

1. Upload the entire `cigar-journal` folder to your web server (or place it under your site root).
2. Ensure the web server can write to `data/` and `uploads/`.
3. Point your browser at `index.php`.
4. The SQLite database (`data/humidor.db`) is created automatically on first visit.

Optional: protect the whole app with HTTP Basic Auth or put it behind your existing site login.

## File structure

```
cigar-journal/
├── index.php          # Dashboard
├── inventory.php      # List / filter inventory
├── add_cigar.php
├── edit_cigar.php
├── view_cigar.php
├── reviews.php
├── add_review.php
├── edit_review.php
├── view_review.php
├── includes/
│   ├── db.php         # SQLite connection + helpers
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── data/              # SQLite DB (auto-created, protected)
├── uploads/           # Photos
└── README.md
```

## Security notes

- `data/` is blocked from direct web access via `.htaccess`.
- Photo uploads are restricted to common image types and size-limited.
- All output is escaped.
- For public-facing sites, add authentication.

Enjoy tracking your sticks.
