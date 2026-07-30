# RIZO — DigitalOcean Droplet Setup Spec

Handoff document for the developer provisioning and configuring the production server. Covers droplet sizing, OS, required software, environment configuration, deployment process, and security checklist.

---

## 1. Droplet

| Setting | Value |
|---|---|
| Provider | DigitalOcean |
| Image | Ubuntu 22.04 LTS (or 24.04 LTS) x64 |
| Plan | Basic (Regular/Premium AMD or Intel) — no GPU/high-CPU tier needed |
| Size | **Minimum: 2 vCPU / 4 GB RAM / 80 GB SSD.** Recommended starting point: **4 vCPU / 8 GB RAM / 160 GB SSD** |
| Region | Nearest to primary user base |
| Backups | Enable DigitalOcean's droplet backup add-on (weekly snapshots) in addition to the DB backup strategy in §7 |
| Networking | Enable "Private Networking" if a separate managed DB is used later |

**Sizing rationale**: MySQL and the Next.js app run on the same box. MySQL needs real RAM for its buffer pool; Next.js/Node under concurrent admin traffic plus PM2 plus Nginx adds up. 4GB is a hard floor, 8GB gives headroom as attendance/payroll data volume grows.

---

## 2. Base OS setup

```bash
apt update && apt upgrade -y
apt install -y ufw fail2ban git curl build-essential
timedatectl set-timezone <your-timezone>   # e.g. Asia/Kolkata

# Create a non-root deploy user (do not run the app as root)
adduser deploy
usermod -aG sudo deploy
```

Firewall — only open what's needed:

```bash
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

**MySQL's port 3306 must never be exposed to `ufw allow` or any public interface.** The app connects to `localhost`/`127.0.0.1` only.

---

## 3. Software to install

| Component | Version | Install |
|---|---|---|
| Node.js | **20.x or 22.x LTS** (not a non-LTS release) | Via NodeSource: `curl -fsSL https://deb.nodesource.com/setup_22.x \| bash - && apt install -y nodejs` |
| MySQL Server | 8.0.x | `apt install -y mysql-server` then `mysql_secure_installation` |
| Nginx | latest from Ubuntu repo | `apt install -y nginx` |
| PM2 | latest | `npm install -g pm2` |
| Certbot | latest | `apt install -y certbot python3-certbot-nginx` |

Verify versions after install:
```bash
node -v      # v20.x.x or v22.x.x
npm -v
mysql --version
nginx -v
pm2 -v
```

---

## 4. MySQL setup

The app uses a **control database** plus **one database per company/tenant**. Create the control DB and its user:

```sql
CREATE DATABASE mypayrol_control_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rizo_app'@'localhost' IDENTIFIED BY '<strong-random-password>';
GRANT ALL PRIVILEGES ON mypayrol_control_db.* TO 'rizo_app'@'localhost';
-- Grant on each company DB as they're created, e.g.:
-- GRANT ALL PRIVILEGES ON mypayrol_<company_code>.* TO 'rizo_app'@'localhost';
FLUSH PRIVILEGES;
```

Import the schema/seed data (provided separately by the team) into `mypayrol_control_db` and each company DB.

**Do not use the MySQL `root` user for the app's runtime connection** — the dev environment uses `root` for convenience only; production must use a dedicated least-privilege user as above (`GRANT` scoped to only the DBs this app needs, not global).

---

## 5. Application deployment

```bash
su - deploy
git clone <repo-url> rizo-app
cd rizo-app/rizo
npm ci
```

Create `.env.local` (or `.env.production`) in `rizo/` — **do not commit this file**:

```bash
# Control Database
CONTROL_DB_HOST=localhost
CONTROL_DB_USER=rizo_app
CONTROL_DB_PASSWORD=<the strong password created in §4>
CONTROL_DB_NAME=mypayrol_control_db

# Company databases use the same host; credentials come from central_control at runtime.
COMPANY_DB_HOST=localhost

# NextAuth — generate a real secret, do NOT reuse the dev placeholder
# openssl rand -base64 32
NEXTAUTH_SECRET=<generate-a-real-32+char-secret>
NEXTAUTH_URL=https://<your-domain>

# File uploads
UPLOAD_DIR=./public/uploads

NODE_ENV=production
```

Build and start under PM2:

```bash
npm run build
pm2 start npm --name rizo -- start
pm2 save
pm2 startup    # follow the printed command to enable boot persistence
```

**Redeploy process** (for future updates):
```bash
cd rizo-app/rizo
git pull
npm ci
npm run build
pm2 restart rizo
```

**Uploads directory**: `UPLOAD_DIR` (`./public/uploads` by default) stores employee photos/documents/generated PDFs on local disk. Ensure this directory:
- Persists across deploys (it's not wiped by `git pull`/`npm ci`)
- Is included in the backup routine (§7) — it is **not** in the database
- Has write permission for the `deploy` user only

---

## 6. Nginx + SSL

`/etc/nginx/sites-available/rizo`:

```nginx
server {
    listen 80;
    server_name <your-domain>;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    client_max_body_size 20M;   # increase if document uploads exceed 20MB
}
```

```bash
ln -s /etc/nginx/sites-available/rizo /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

# SSL — run after DNS for <your-domain> already points at this droplet
certbot --nginx -d <your-domain>
```

Certbot sets up auto-renewal automatically (`systemctl status certbot.timer` to confirm).

---

## 7. Backups (critical — this app holds payroll/PII data)

Two things must be backed up, on a schedule, to storage **off this droplet** (e.g., DigitalOcean Spaces, or another remote target):

1. **MySQL** — all databases (control + every company DB):
   ```bash
   mysqldump --all-databases -u rizo_app -p | gzip > /backup/mysql-$(date +%F).sql.gz
   ```
   Schedule via cron (daily minimum), and copy the output off-box (`s3cmd`/`rclone` to Spaces, or `rsync` to another server).

2. **`UPLOAD_DIR`** (`rizo/public/uploads`) — employee photos/documents/generated slips. Not in the database; back up separately (e.g. `rsync`/`tar` to the same remote target).

Recommended: also enable DigitalOcean's droplet-level backup/snapshot add-on as a second layer, but don't rely on it alone — it's a whole-disk snapshot, not a queryable/restorable DB backup on its own.

---

## 8. Post-setup verification checklist

- [ ] `pm2 status` shows `rizo` as `online`
- [ ] `https://<your-domain>` loads the login page over a valid SSL cert (no browser warning)
- [ ] Can log in with a real admin account and reach `/dashboard`
- [ ] `ufw status` shows only 22/80/443 open, port 3306 NOT publicly reachable (`nmap <droplet-ip> -p 3306` from outside should show filtered/closed)
- [ ] `mysqldump` backup cron job runs successfully once, manually triggered
- [ ] `UPLOAD_DIR` backup job runs successfully once, manually triggered
- [ ] Server reboot test: `reboot`, then confirm `pm2` and the app come back up automatically (`pm2 startup` was configured correctly)
- [ ] `.env.local` is NOT committed to git and is not world-readable (`chmod 600 .env.local`)

---

## 9. Security notes

- Never expose MySQL (3306) publicly — the app always connects via `localhost`.
- Use a dedicated, least-privilege MySQL user for the app (§4) — never `root` in production.
- Rotate `NEXTAUTH_SECRET` to a real random value; the dev placeholder in this repo must not reach production.
- Disable SSH password auth once key-based access is confirmed working (`PasswordAuthentication no` in `/etc/ssh/sshd_config`, then `systemctl restart sshd`).
- Keep the OS patched: `apt update && apt upgrade` on a regular schedule, or enable `unattended-upgrades` for security patches.
