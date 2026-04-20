# Star Ratings - Contensio Plugin

Let visitors rate any post or page 1–5 stars. Displays the average rating and total count. Users can update their rating at any time. Admins can reset ratings per content item.

---

## Features

- **1–5 star ratings** on any content item
- **Live updates** - the average and count update instantly after rating via Alpine.js + `fetch`, no page reload
- **Change rating** - clicking a different star updates the existing rating (no duplicates)
- **Guest ratings** - IP-based by default; one rating per IP per content item
- **Logged-in user ratings** - enforced by a unique database constraint on `(content_id, user_id)`
- **Admin overview** - lists all rated content with average, count, and a reset button
- **Admin reset** - delete all ratings for any content item in one click
- **Embeddable** - drop the widget into any theme partial with one line

---

## How it works

1. A theme includes the rating widget partial for a content item.
2. A visitor hovers over the stars (preview) and clicks to submit their rating via `fetch`.
3. The widget updates instantly: new average, new count, and a "Thanks for your rating!" confirmation.
4. If the visitor returns and clicks a different star, their rating is updated (not duplicated).
5. If the visitor revisits the page, their existing rating is highlighted automatically.

### Deduplication

- **Logged-in users** - unique database constraint on `(content_id, user_id)`. Submitting again triggers an `UPDATE` on the existing row.
- **Guests** - checked by IP address before inserting. If a row already exists for that IP + content item, it is updated.

---

## Installation

### Via admin panel

Go to **Plugins** in your Contensio admin, find **Star Ratings**, and click **Install**.

### Via Composer

```bash
composer require contensio/plugin-ratings
```

The plugin is auto-discovered. Go to **Plugins** in the admin and enable it. The migration runs automatically on first enable.

---

## Embedding the widget

```blade
@include('ratings::partials.rating-widget', ['contentId' => $content->id])
```

Replace `$content->id` with the ID of the content item you want to display ratings for. The widget is fully self-contained - it reads the current average, count, and the current user's existing rating on page load, and handles all interaction without any additional setup.

**Requirements for the embed to work:**

- Alpine.js must be loaded on the page (included in all Contensio default themes).
- A `<meta name="csrf-token">` tag must be present in the page `<head>` (included in all Contensio default themes).

---

## Admin

### Ratings list (`/account/ratings`)

Shows all content items that have at least one rating, ordered by most ratings first. Each row displays:

- Content title and type
- Visual star display (filled stars based on the floor of the average)
- Numeric average (1 decimal place)
- Total number of individual ratings
- **Reset** button - deletes all ratings for that content item after a confirmation prompt

---

## Routes

| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/account/ratings` | Admin ratings list |
| `DELETE` | `/account/ratings/{contentId}/reset` | Reset all ratings for a content item |
| `POST` | `/ratings/{contentId}` | Submit or update a rating (JSON) |
| `GET` | `/ratings/{contentId}` | Get current rating summary (JSON) |

---

## API

### POST `/ratings/{contentId}`

Submit or update the current user's rating.

**Request body:**
```json
{ "rating": 4 }
```

**Response:**
```json
{
    "success": true,
    "average": 4.2,
    "count": 143,
    "your_rating": 4
}
```

### GET `/ratings/{contentId}`

Get the current rating summary without submitting.

**Response:**
```json
{
    "average": 4.2,
    "count": 143,
    "your_rating": null
}
```

`your_rating` is `null` if the current user/IP has not rated this item.

---

## Database

Creates one table: `content_ratings`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `content_id` | bigint | ID of the rated content item |
| `user_id` | bigint | Logged-in user ID (nullable for guests) |
| `ip_address` | varchar(45) | Voter IP address |
| `rating` | tinyint | Rating value (1–5) |
| `created_at` | timestamp | When the rating was first submitted |
| `updated_at` | timestamp | When the rating was last changed |

Unique constraint on `(content_id, user_id)` prevents duplicate rows for logged-in users at the database level.

---

## Requirements

- PHP 8.2+
- Contensio 2.0+
- Alpine.js (included in all Contensio default themes)

---

## License

AGPL-3.0-or-later - see [LICENSE](LICENSE).
