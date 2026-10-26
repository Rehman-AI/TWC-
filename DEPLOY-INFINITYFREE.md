# TWC Content Studio — InfinityFree Deployment

## Your InfinityFree Details
- **MySQL Host**: sql310.infinityfree.com
- **MySQL User**: if0_43274319
- **MySQL Password**: vhWBIoRfNa
- **MySQL Database**: if0_43274319_TwC

## Step-by-Step Deployment

### Step 1: Get Your FTP Credentials
1. Login to https://www.infinityfree.com
2. Go to your hosting account dashboard
3. Find your **FTP Details**:
   - FTP Host (usually `ftpupload.net`)
   - FTP Username (your hosting username)
   - FTP Password (your account password)

### Step 2: Download FileZilla (Free FTP Client)
1. Go to https://filezilla-project.org/download.php
2. Download and install FileZilla Client
3. Open FileZilla

### Step 3: Connect via FTP
1. In FileZilla, enter:
   - Host: `ftpupload.net`
   - Username: your InfinityFree username
   - Password: your InfinityFree password
   - Port: `21`
2. Click "Quick Connect"

### Step 4: Upload Files
1. In FileZilla, navigate to `/htdocs/` on the **remote** side (right panel)
2. Delete everything currently in `/htdocs/` (default files)
3. Extract the `TWC-Deploy.zip` file on your PC
4. Select ALL extracted files/folders and drag them into `/htdocs/`
5. Wait for upload to complete (this takes 10-30 minutes)

### Step 5: Set Permissions
After upload, right-click these folders on the remote side and set permissions to `755`:
- `storage/` (and all subfolders)
- `bootstrap/cache/`

### Step 6: Run Setup (Migrations + Seeding)
1. Open your browser
2. Go to: `https://YOUR-SUBDOMAIN.infinityfreeapp.com/migrate.php?token=twc_migrate_2026`
3. Wait for it to finish (you'll see green text output)
4. **IMPORTANT**: After success, delete `migrate.php` from `/htdocs/public/` via FileZilla

### Step 7: Login!
1. Go to: `https://YOUR-SUBDOMAIN.infinityfreeapp.com/admin`
2. Login as: **boss@twc.com** / **password**

## Logins
| Role | Email | Password |
|------|-------|----------|
| Boss (Owner) | boss@twc.com | password |
| Manager | manager@twc.com | password |
| Script Writer | ahmed@twc.com | password |
| Video Maker | sara@twc.com | password |
| YouTube Head | ali@twc.com | password |
| Facebook Head | fatima@twc.com | password |
| Instagram Head | hassan@twc.com | password |
| TikTok Head | zara@twc.com | password |

## Troubleshooting
- **500 Error**: Check `storage/logs/laravel.log` via FileZilla
- **DB Error**: Make sure migrate.php ran successfully
- **Blank Page**: Set `APP_DEBUG=true` temporarily in `.env` to see errors
- **Permissions**: storage/ and bootstrap/cache/ must be writable (755)