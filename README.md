# Colorless Mana - Magic The Gathering Collection Database

### Requirements
* PHP 8.1
* pnpm
* Redis (prod)
* MySQL/MariaDB

### Installation
```bash
git clone git@github.com:ThauEx/colorless-mana.git
composer install
bin/console doctrine:migrations:migrate
pnpm install
```

### Building assets
```bash
pnpm run build
pnpm run buildw
```
