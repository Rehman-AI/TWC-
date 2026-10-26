# TWC Content Studio 🎬

> Complete YouTube Automation Department Control Center & Workflow Studio.

TWC Content Studio manages YouTube and multi-platform content production pipelines: Channels, Idea Brainstorming, Multi-Language Channel Management, Roles & Granular Permissions, Task Assignment, Script Writing & Auto-Saving, Clip Generation, Quality Assurance, and Publishing Schedules.

---

## 🚀 Quick Start for Developers

### Prerequisites
- **PHP** >= 8.2
- **Composer**
- **MySQL** / **MariaDB** or **SQLite**
- **Node.js** & **NPM** (optional, for asset building)

### Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Rehman-AI/TWC-.git
   cd TWC-
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit `.env` to configure your database connection (`mysql` or `sqlite`).*

4. **Run database migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```
   This will set up all tables, predefined roles (Boss, Manager, YouTube Head, Script Writer, Video Editor, QA), permissions, and sample channels.

5. **Start the local server:**
   ```bash
   php artisan serve
   ```
   Visit: `http://127.0.0.1:8000/admin`

---

## 🔑 Default Accounts (from Seeder)

| Role | Email | Password |
|------|-------|----------|
| **Boss / Superadmin** | `boss@twc.com` | `password` |
| **Manager** | `manager@twc.com` | `password` |
| **Script Writer** | `writer@twc.com` | `password` |
| **Video Editor** | `editor@twc.com` | `password` |

---

## 🌟 Key Features

1. **Full Boss / Admin Control**:
   - Create, edit, and delete channels with custom languages (all languages supported) and platform targets (YouTube Shorts, Long-form, TikTok, Instagram Reels, Facebook).
   - Create, edit, and delete team members and assign custom roles.
   - Granular permission matrix with checkboxes to define exactly what each role can do (e.g. YouTube Video Upload, Script Creation, Asset Upload, Review, Publish).
   - Daily progress & task completion tracking (daily quotas, completed vs pending, automatic rollover of unfinished tasks).

2. **Manager Pipeline**:
   - Assign video creation tasks to scriptwriters, voiceover artists, video editors, and QA reviewers.
   - Configure duration (from 1 min up to 1-2 hours) and target upload platforms.

3. **Script Studio**:
   - Dedicated script writing interface with no character/word limits.
   - Unlimited custom text blocks/sections (Intro, Hook, Body, Call to Action, Outro, Visual Prompts).
   - Auto-save and version history.

4. **Deployment & Hosting Ready**:
   - Supports standard hosting (VPS, Cloud, cPanel, Nginx, Apache).
   - Web-based migration runner included (`public/migrate.php`) for shared hosting environments.
   - Root `.htaccess` for automatic redirect to `/public` on shared hosts.

---

## 📚 Documentation
Additional architecture, flow diagrams, and feature scopes are located in the `docs/` directory.