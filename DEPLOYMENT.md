# Koupii API - Automatic Deployment Setup

## 🚀 Overview

This project now has **automatic deployment** configured using GitHub Actions. Every time you push to the `main` branch or merge a pull request, the code will automatically deploy to the VPS.

## 📋 Setup Requirements

### 1. GitHub Repository Secrets

You need to add the following secrets to your GitHub repository:

1. Go to GitHub repository → Settings → Secrets and variables → Actions
2. Add these Repository secrets:

| Secret Name   | Value               | Description                        |
| ------------- | ------------------- | ---------------------------------- |
| `VPS_HOST`    | `31.97.187.17`      | VPS IP address                     |
| `VPS_USER`    | `root`              | SSH username                       |
| `VPS_PORT`    | `22`                | SSH port                           |
| `VPS_SSH_KEY` | `[SSH Private Key]` | SSH private key for authentication |

**SSH Private Key:** Use the private key that was generated and displayed above (the content between `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`).

### 2. VPS Setup

The VPS is already configured with:

-   ✅ SSH public key added to `~/.ssh/authorized_keys`
-   ✅ Deployment script created at `/srv/scripts/deploy.sh`
-   ✅ Docker Compose setup in `/srv/apps/koupii-api/`
-   ✅ Git repository in `/srv/apps/koupii-api/releases/initial/`

## 🔄 How Auto-Deployment Works

### Triggers

-   **Push to `main` branch**: Automatically deploys
-   **Merge Pull Request to `main`**: Automatically deploys

### Deployment Process

1. **GitHub Actions** detects push/merge to main
2. **SSH into VPS** using stored credentials
3. **Pull latest code** from GitHub
4. **Restart backend container**
5. **Clear Laravel caches** and rebuild
6. **Health check** API endpoint
7. **Report deployment status**

### Deployment Steps Detail

```bash
# Navigate to project
cd /srv/apps/koupii-api/releases/initial

# Pull latest changes
git fetch origin
git reset --hard origin/main

# Restart services
cd /srv/apps/koupii-api
docker compose restart backend

# Clear caches
docker compose exec -T backend php artisan config:clear
docker compose exec -T backend php artisan route:clear
docker compose exec -T backend php artisan cache:clear
docker compose exec -T backend php artisan config:cache

# Health check
curl -s https://api-koupii.magercoding.com/api/health
```

## 📊 Monitoring

### API Endpoints

-   **Health Check**: `GET /api/health`
-   **Version Info**: `GET /api/version` (shows current commit, environment, timestamp)

### Deployment Logs

-   **GitHub Actions**: Check Actions tab in GitHub repository
-   **VPS Logs**: `/srv/scripts/deploy.log`

### Manual Deployment

If you need to deploy manually:

```bash
# SSH to VPS
ssh root@31.97.187.17

# Run deployment script
/srv/scripts/deploy.sh

# Or step by step
cd /srv/apps/koupii-api/releases/initial
git pull origin main
cd /srv/apps/koupii-api
docker compose restart backend
```

## 🔧 Troubleshooting

### Common Issues

1. **Deployment fails with SSH error**

    - Check if `VPS_SSH_KEY` secret is correctly set
    - Verify the private key format (include BEGIN/END lines)

2. **Git pull fails**

    - Check if VPS has internet access
    - Verify GitHub repository is accessible

3. **Container restart fails**

    - Check Docker Compose logs: `docker compose logs backend`
    - Verify container health: `docker compose ps`

4. **Health check fails**
    - Check Nginx configuration
    - Verify Laravel application is running
    - Check database connection

### Debug Commands

```bash
# Check current commit on VPS
ssh root@31.97.187.17 'cd /srv/apps/koupii-api/releases/initial && git rev-parse --short HEAD'

# Check container status
ssh root@31.97.187.17 'cd /srv/apps/koupii-api && docker compose ps'

# Check deployment logs
ssh root@31.97.187.17 'tail -20 /srv/scripts/deploy.log'

# Manual health check
curl -s https://api-koupii.magercoding.com/api/health
```

## 🎯 Benefits

-   ✅ **Zero-downtime deployment** (container restart)
-   ✅ **Automatic cache clearing** (Laravel optimization)
-   ✅ **Health checks** (verify deployment success)
-   ✅ **Deployment logs** (troubleshooting)
-   ✅ **Version tracking** (commit monitoring)
-   ✅ **Error handling** (rollback on failure)

## 📝 Development Workflow

1. **Make changes** in local development
2. **Commit and push** to main branch
3. **GitHub Actions** automatically deploys
4. **Monitor deployment** in Actions tab
5. **Verify** using health/version endpoints

No more manual deployment needed! 🎉
