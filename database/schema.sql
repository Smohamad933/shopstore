-- ---------------------------------------------------------------------------
-- SazehShop — ساختار دیتابیس (SQLite)
-- این فایل به صورت خودکار در اولین اجرای برنامه روی هاست اجرا می‌شود.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT    NOT NULL,
    email         TEXT    NOT NULL UNIQUE,
    phone         TEXT,
    password_hash TEXT    NOT NULL,
    role          TEXT    NOT NULL DEFAULT 'customer',   -- customer | admin
    is_active     INTEGER NOT NULL DEFAULT 1,
    last_login_at TEXT,
    created_at    TEXT    NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

CREATE TABLE IF NOT EXISTS categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    slug        TEXT    NOT NULL UNIQUE,
    parent_id   INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    icon        TEXT,
    image       TEXT,
    description TEXT,
    position    INTEGER NOT NULL DEFAULT 0,
    is_active   INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS products (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    name           TEXT    NOT NULL,
    slug           TEXT    NOT NULL UNIQUE,
    sku            TEXT,
    category_id    INTEGER NOT NULL REFERENCES categories(id),
    brand          TEXT,
    price          INTEGER NOT NULL DEFAULT 0,
    sale_price     INTEGER NOT NULL DEFAULT 0,
    stock          INTEGER NOT NULL DEFAULT 0,
    image          TEXT,
    gallery        TEXT DEFAULT '[]',
    short_desc     TEXT,
    description    TEXT,
    specs          TEXT DEFAULT '[]',
    rating         REAL    NOT NULL DEFAULT 0,
    rating_count   INTEGER NOT NULL DEFAULT 0,
    is_featured    INTEGER NOT NULL DEFAULT 0,
    is_special     INTEGER NOT NULL DEFAULT 0,
    special_ends_at TEXT,
    is_active      INTEGER NOT NULL DEFAULT 1,
    created_at     TEXT    NOT NULL,
    updated_at     TEXT
);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id, is_active);
CREATE INDEX IF NOT EXISTS idx_products_flags ON products(is_featured, is_special, is_active);

CREATE TABLE IF NOT EXISTS product_reviews (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    user_id    INTEGER REFERENCES users(id) ON DELETE SET NULL,
    name       TEXT    NOT NULL,
    rating     INTEGER NOT NULL DEFAULT 5,
    comment    TEXT,
    is_approved INTEGER NOT NULL DEFAULT 1,
    created_at TEXT    NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_reviews_product ON product_reviews(product_id, is_approved);

CREATE TABLE IF NOT EXISTS coupons (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    code       TEXT    NOT NULL UNIQUE,
    type       TEXT    NOT NULL DEFAULT 'percent',  -- percent | fixed
    amount     INTEGER NOT NULL DEFAULT 0,
    min_order  INTEGER NOT NULL DEFAULT 0,
    max_uses   INTEGER NOT NULL DEFAULT 0,
    used_count INTEGER NOT NULL DEFAULT 0,
    expires_at TEXT,
    is_active  INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS addresses (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title       TEXT,
    receiver    TEXT,
    phone       TEXT,
    province    TEXT,
    city        TEXT,
    address     TEXT,
    postal_code TEXT,
    is_default  INTEGER NOT NULL DEFAULT 0,
    created_at  TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    code            TEXT    NOT NULL UNIQUE,
    user_id         INTEGER REFERENCES users(id) ON DELETE SET NULL,
    customer_name   TEXT    NOT NULL,
    customer_phone  TEXT    NOT NULL,
    customer_email  TEXT,
    province        TEXT,
    city            TEXT,
    address         TEXT,
    postal_code     TEXT,
    note            TEXT,
    items_count     INTEGER NOT NULL DEFAULT 0,
    subtotal        INTEGER NOT NULL DEFAULT 0,
    discount        INTEGER NOT NULL DEFAULT 0,
    coupon_code     TEXT,
    shipping        INTEGER NOT NULL DEFAULT 0,
    shipping_method TEXT    NOT NULL DEFAULT 'post',
    total           INTEGER NOT NULL DEFAULT 0,
    payment_method  TEXT    NOT NULL DEFAULT 'online',   -- online | cod
    payment_status  TEXT    NOT NULL DEFAULT 'unpaid',   -- unpaid | paid | failed | refunded
    payment_ref     TEXT,
    status          TEXT    NOT NULL DEFAULT 'pending',
    tracking_code   TEXT,
    admin_note      TEXT,
    created_at      TEXT    NOT NULL,
    updated_at      TEXT
);
CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id, created_at);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);

CREATE TABLE IF NOT EXISTS order_items (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id   INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE SET NULL,
    name       TEXT    NOT NULL,
    slug       TEXT,
    image      TEXT,
    brand      TEXT,
    price      INTEGER NOT NULL DEFAULT 0,
    qty        INTEGER NOT NULL DEFAULT 1
);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    phone      TEXT,
    email      TEXT,
    subject    TEXT,
    message    TEXT NOT NULL,
    is_read    INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
);
