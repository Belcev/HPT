# Mini E-shop API

PHP 8.4 REST API pro správu košíku a objednávek malého e-shopu.

## Technologie

- **PHP 8.4** + Composer
- **Slim Framework 4** — HTTP routing
- **PHP-DI 7** — Dependency Injection
- **Doctrine DBAL 4** — databázová vrstva (MySQL 8)
- **Guzzle 7** — HTTP klient pro externí API
- **Nominatim (OpenStreetMap)** — geokódování dodací adresy (zdarma, bez API klíče)
- **PHPUnit 13** — unit testy
- **Docker** — cross-platform prostředí (Nginx, MySQL, phpMyAdmin)

---

## Spuštění

### Požadavky

- Docker + Docker Compose

### 1. Spustit kontejnery

```bash
docker compose up -d
```

MySQL při prvním spuštění automaticky provede `docker/mysql/init.sql` — vytvoří tabulky a naplní katalog produktů.

### 2. Nainstalovat závislosti

```bash
docker compose exec app composer install
```

API je dostupné na **http://localhost**.

### Zastavení

```bash
docker compose down
```

Reset databáze (smazání volume):

```bash
docker compose down -v
```

---

## Produkty v katalogu

| SKU | Název | Cena |
|---|---|---|
| `LAPTOP-01` | Laptop Pro 15 | 45 999 Kč |
| `MOUSE-01` | Wireless Mouse | 499 Kč |
| `KB-01` | Mechanical Keyboard | 1 299 Kč |
| `MONITOR-01` | 27" 4K Monitor | 8 999 Kč |
| `HEADPH-01` | Noise Cancelling Headphones | 2 499 Kč |

---

## API Reference

Základní URL: `http://localhost`

Všechny requesty s tělem posílají `Content-Type: application/json`.
Chyby vracejí `{"error": "popis"}` s příslušným HTTP statusem.

---


## Architektura

```
app/
├── Domain/           # čistá doménová vrstva bez závislostí na frameworku
│   ├── Cart/         # Cart (entita), CartItem (value object), CartRepositoryInterface
│   ├── Order/        # Order (readonly entita), OrderItem, OrderRepositoryInterface
│   ├── Product/      # Product (readonly VO), ProductRepositoryInterface
│   └── Exception/    # CartNotFoundException, ProductNotFoundException, ...
├── Application/
│   └── Service/      # CartService, OrderService — orchestrace doménové logiky
├── Infrastructure/
│   ├── ExternalApi/  # GeocoderInterface + NominatimGeocoder (Nominatim OSM)
│   └── Persistence/  # implementace repository přes Doctrine DBAL (MySQL)
└── Http/
    └── Controller/   # CartController, OrderController

bootstrap/container.php  # definice PHP-DI kontejneru
public/index.php          # vstupní bod, routing (Slim 4)

legacy/
├── legacy.php            # původní kód zadání (refaktoring)
└── refactored/           # refaktorovaná verze s DI, readonly modely, správné typy
```

## Poznámky

Ceny jsou interně uloženy jako **celá čísla v haléřích** (`int`). V API odpovědích jsou převedeny na `float` v korunách.  
Důvodem je, že `float` v PHP může způsobovat problémy s přesností při ukládání peněžních hodnot — použití celých čísel zaručuje přesnost a eliminuje chyby způsobené zaokrouhlováním.

V kódu by se dalo pokračovat například přidáním CI/CD pipeline, rozšířením testovacího pokrytí nebo obohacením API o další funkce (správa produktů, autentizace uživatelů apod.).

## Post Scriptum
Jsem si vědom toho, že snaha projít PHPStan na `level: max` bez jediného záznamu v `phpstan-baseline` je v praxi spíše akademické cvičení než nutnost — místy kód trochu znepřehledňuje, ale proč ne. :)
