CREATE DATABASE IF NOT EXISTS HPT;
CREATE USER IF NOT EXISTS 'HPT'@'%' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON HPT.* TO 'HPT'@'%';
FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS products (
    sku         VARCHAR(50)  PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT         NULL,
    price_cents INT          NOT NULL
);

CREATE TABLE IF NOT EXISTS carts (
    id         VARCHAR(36) PRIMARY KEY,
    created_at DATETIME    NOT NULL
);

CREATE TABLE IF NOT EXISTS cart_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    cart_id     VARCHAR(36)  NOT NULL,
    sku         VARCHAR(50)  NOT NULL,
    name        VARCHAR(255) NOT NULL,
    description TEXT         NULL,
    price_cents INT          NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_sku (cart_id, sku)
);

CREATE TABLE IF NOT EXISTS orders (
    id               VARCHAR(36)  PRIMARY KEY,
    shipping_address TEXT         NOT NULL,
    total_cents      INT          NOT NULL,
    geo_location     VARCHAR(100) NULL,
    created_at       DATETIME     NOT NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    VARCHAR(36)  NOT NULL,
    sku         VARCHAR(50)  NOT NULL,
    name        VARCHAR(255) NOT NULL,
    price_cents INT          NOT NULL,
    quantity    INT          NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT IGNORE INTO products (sku, name, description, price_cents) VALUES
    ('LAPTOP-01',   'Laptop Pro 15',                  'High-performance 15" laptop',         4599900),
    ('MOUSE-01',    'Wireless Mouse',                  'Ergonomic wireless mouse',              49900),
    ('KB-01',       'Mechanical Keyboard',             'Full-size mechanical keyboard',        129900),
    ('MONITOR-01',  '27" 4K Monitor',                 '4K IPS display, 144Hz',               899900),
    ('HEADPH-01',   'Noise Cancelling Headphones',    'Over-ear ANC headphones',             249900);
