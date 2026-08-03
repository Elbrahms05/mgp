# Environnement de développement Docker — PACIPA-MGP

Stack de développement local du back-office Laravel (`mgp-office/`).

## Services

| Service | Rôle | Port hôte |
|---|---|---|
| `db`    | PostgreSQL 16 | 5432 |
| `redis` | Redis 7 (cache, files, sessions) | 6379 |
| `app`   | PHP-FPM 8.4 (extensions : pdo_pgsql, sodium, gd, zip, bcmath, intl, redis) | — |
| `web`   | Nginx (sert `mgp-office/public`) | 8080 |

> ODK Central n'est pas inclus (stack Compose dédiée volumineuse — voir `claude-deploy.md`).
> Le pont ODK côté back-office (`odk:sync`) se configure via les variables `ODK_*` du `.env`.

## Démarrage

```bash
# Depuis la racine du dépôt
docker compose -f docker/docker-compose.yml up -d --build

# Première initialisation
docker compose -f docker/docker-compose.yml exec app composer install
docker compose -f docker/docker-compose.yml exec app cp .env.example .env
docker compose -f docker/docker-compose.yml exec app php artisan key:generate
docker compose -f docker/docker-compose.yml exec app php artisan migrate --seed

# Clé de chiffrement VBG (obligatoire — 32 octets base64)
docker compose -f docker/docker-compose.yml exec app \
  php -r 'echo "VBG_ENCRYPTION_KEY=".base64_encode(random_bytes(32)).PHP_EOL;' >> mgp-office/.env
```

Back-office disponible sur http://localhost:8080

## Tests

```bash
docker compose -f docker/docker-compose.yml exec app php artisan test
```

Les tests utilisent SQLite en mémoire (voir `phpunit.xml`) — aucun accès à PostgreSQL requis.
