#!/usr/bin/env bash
# Migrate psihotest business data from SQLite to MySQL on the server.
# Run from the psihotest project root.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

DRY_RUN=false

for arg in "$@"; do
    case "${arg}" in
        --dry-run)
            DRY_RUN=true
            ;;
        -h|--help)
            cat <<'EOF'
Usage: ./deploy/migrate-sqlite-to-mysql.sh [--dry-run]

Environment variables (required unless set in .env):
  MYSQL_DATABASE      Target database name
  MYSQL_USERNAME      MySQL user
  MYSQL_PASSWORD      MySQL password

Optional:
  MYSQL_HOST          Default: 127.0.0.1
  MYSQL_PORT          Default: 3306
  SQLITE_SOURCE       Default: database/database.sqlite

Example:
  MYSQL_DATABASE=psihotest MYSQL_USERNAME=psihotest MYSQL_PASSWORD=secret \
    ./deploy/migrate-sqlite-to-mysql.sh
EOF
            exit 0
            ;;
        *)
            echo "Unknown argument: ${arg}" >&2
            exit 1
            ;;
    esac
done

load_env_var() {
    local key="$1"
    local default="${2:-}"

    if [[ -n "${!key:-}" ]]; then
        return
    fi

    if [[ -f .env ]]; then
        local value
        value="$(grep -E "^${key}=" .env | tail -n 1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//")"
        if [[ -n "${value}" ]]; then
            export "${key}=${value}"
            return
        fi
    fi

    if [[ -n "${default}" ]]; then
        export "${key}=${default}"
    fi
}

load_env_var MYSQL_HOST "127.0.0.1"
load_env_var MYSQL_PORT "3306"
load_env_var SQLITE_SOURCE "database/database.sqlite"

if [[ -z "${MYSQL_DATABASE:-}" ]]; then
    echo "MYSQL_DATABASE is required." >&2
    exit 1
fi

if [[ -z "${MYSQL_USERNAME:-}" ]]; then
    echo "MYSQL_USERNAME is required." >&2
    exit 1
fi

MYSQL_PASSWORD="${MYSQL_PASSWORD:-}"

if [[ ! -f "${SQLITE_SOURCE}" ]]; then
    echo "SQLite file not found: ${SQLITE_SOURCE}" >&2
    exit 1
fi

if ! command -v php >/dev/null 2>&1; then
    echo "php is required but not found in PATH." >&2
    exit 1
fi

if ! command -v mysql >/dev/null 2>&1; then
    echo "mysql client is required but not found in PATH." >&2
    exit 1
fi

SQLITE_SOURCE_ABS="$(cd "$(dirname "${SQLITE_SOURCE}")" && pwd)/$(basename "${SQLITE_SOURCE}")"

echo "==> SQLite source: ${SQLITE_SOURCE_ABS}"
echo "==> MySQL target: ${MYSQL_USERNAME}@${MYSQL_HOST}:${MYSQL_PORT}/${MYSQL_DATABASE}"

if [[ "${DRY_RUN}" == true ]]; then
    echo "==> Dry run only"
    DB_CONNECTION=mysql \
    DB_HOST="${MYSQL_HOST}" \
    DB_PORT="${MYSQL_PORT}" \
    DB_DATABASE="${MYSQL_DATABASE}" \
    DB_USERNAME="${MYSQL_USERNAME}" \
    DB_PASSWORD="${MYSQL_PASSWORD}" \
    SQLITE_SOURCE_DATABASE="${SQLITE_SOURCE_ABS}" \
    php artisan db:migrate-sqlite-to-mysql --dry-run

    exit 0
fi

BACKUP_PATH="${SQLITE_SOURCE_ABS}.bak.$(date +%Y%m%d_%H%M%S)"
cp "${SQLITE_SOURCE_ABS}" "${BACKUP_PATH}"
echo "==> SQLite backup: ${BACKUP_PATH}"

php artisan down || true

MYSQL_ADMIN_ARGS=(
    -h "${MYSQL_HOST}"
    -P "${MYSQL_PORT}"
    -u "${MYSQL_USERNAME}"
)

if [[ -n "${MYSQL_PASSWORD}" ]]; then
    MYSQL_PWD="${MYSQL_PASSWORD}" mysql "${MYSQL_ADMIN_ARGS[@]}" \
        -e "CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
else
    mysql "${MYSQL_ADMIN_ARGS[@]}" \
        -e "CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

echo "==> Running Laravel migrations on MySQL..."
DB_CONNECTION=mysql \
DB_HOST="${MYSQL_HOST}" \
DB_PORT="${MYSQL_PORT}" \
DB_DATABASE="${MYSQL_DATABASE}" \
DB_USERNAME="${MYSQL_USERNAME}" \
DB_PASSWORD="${MYSQL_PASSWORD}" \
SQLITE_SOURCE_DATABASE="${SQLITE_SOURCE_ABS}" \
php artisan migrate --force

echo "==> Copying business data from SQLite to MySQL..."
DB_CONNECTION=mysql \
DB_HOST="${MYSQL_HOST}" \
DB_PORT="${MYSQL_PORT}" \
DB_DATABASE="${MYSQL_DATABASE}" \
DB_USERNAME="${MYSQL_USERNAME}" \
DB_PASSWORD="${MYSQL_PASSWORD}" \
SQLITE_SOURCE_DATABASE="${SQLITE_SOURCE_ABS}" \
php artisan db:migrate-sqlite-to-mysql

echo "==> Syncing default roles and permissions..."
DB_CONNECTION=mysql \
DB_HOST="${MYSQL_HOST}" \
DB_PORT="${MYSQL_PORT}" \
DB_DATABASE="${MYSQL_DATABASE}" \
DB_USERNAME="${MYSQL_USERNAME}" \
DB_PASSWORD="${MYSQL_PASSWORD}" \
php artisan roles:sync-defaults

if [[ -f .env ]]; then
    echo "==> Updating .env for MySQL..."
    ENV_FILE=".env"
    TMP_FILE="$(mktemp)"

    awk -v host="${MYSQL_HOST}" -v port="${MYSQL_PORT}" -v database="${MYSQL_DATABASE}" -v username="${MYSQL_USERNAME}" -v password="${MYSQL_PASSWORD}" '
        BEGIN {
            seen_connection = 0
            seen_host = 0
            seen_port = 0
            seen_database = 0
            seen_username = 0
            seen_password = 0
        }
        /^DB_CONNECTION=/ {
            print "DB_CONNECTION=mysql"
            seen_connection = 1
            next
        }
        /^# ?DB_CONNECTION=/ {
            print "DB_CONNECTION=mysql"
            seen_connection = 1
            next
        }
        /^DB_HOST=/ || /^# ?DB_HOST=/ {
            print "DB_HOST=" host
            seen_host = 1
            next
        }
        /^DB_PORT=/ || /^# ?DB_PORT=/ {
            print "DB_PORT=" port
            seen_port = 1
            next
        }
        /^DB_DATABASE=/ || /^# ?DB_DATABASE=/ {
            print "DB_DATABASE=" database
            seen_database = 1
            next
        }
        /^DB_USERNAME=/ || /^# ?DB_USERNAME=/ {
            print "DB_USERNAME=" username
            seen_username = 1
            next
        }
        /^DB_PASSWORD=/ || /^# ?DB_PASSWORD=/ {
            print "DB_PASSWORD=" password
            seen_password = 1
            next
        }
        { print }
        END {
            if (!seen_connection) print "DB_CONNECTION=mysql"
            if (!seen_host) print "DB_HOST=" host
            if (!seen_port) print "DB_PORT=" port
            if (!seen_database) print "DB_DATABASE=" database
            if (!seen_username) print "DB_USERNAME=" username
            if (!seen_password) print "DB_PASSWORD=" password
        }
    ' "${ENV_FILE}" > "${TMP_FILE}"

    mv "${TMP_FILE}" "${ENV_FILE}"
fi

php artisan optimize:clear
php artisan config:cache
php artisan up

cat <<EOF

Migration finished.

Next checks:
  1. php artisan tinker --execute="echo DB::table('users')->count();"
  2. Log in to the admin panel.
  3. Open exams/applicants and verify one completed attempt + PDF report.

Rollback:
  - Restore .env with DB_CONNECTION=sqlite
  - Use backup: ${BACKUP_PATH}
EOF
