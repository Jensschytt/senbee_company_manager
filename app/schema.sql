CREATE TABLE IF NOT EXISTS companies (
id INTEGER PRIMARY KEY AUTOINCREMENT,
cvr_number TEXT NOT NULL UNIQUE,
name TEXT,
phone TEXT,
email TEXT,
address TEXT
);


CREATE INDEX IF NOT EXISTS idx_companies_cvr ON companies (cvr_number);