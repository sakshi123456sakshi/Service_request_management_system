-- =====================================================================
-- Seed data: categories + test login credentials
-- Password for ALL seeded accounts is:  Password@123
-- (hashed below with PHP password_hash / BCRYPT)
-- =====================================================================
USE service_request_system;

INSERT INTO categories (name) VALUES
('Hardware'), ('Software'), ('Network'), ('Installation'), ('Maintenance'), ('Other');

-- password_hash("Password@123", PASSWORD_BCRYPT) -- valid bcrypt hash, works with PHP's password_verify()
INSERT INTO users (name, email, password_hash, role, phone) VALUES
('System Admin',    'admin@srms.com',      '$2b$12$aDXy/tXP7/kS8pAer207bOJGaRngyMbWsgznI69gMuStbvEaNtJzC', 'admin',      '9000000001'),
('Ravi Technician',  'technician@srms.com','$2b$12$aDXy/tXP7/kS8pAer207bOJGaRngyMbWsgznI69gMuStbvEaNtJzC', 'technician', '9000000002'),
('Priya Technician', 'technician2@srms.com','$2b$12$aDXy/tXP7/kS8pAer207bOJGaRngyMbWsgznI69gMuStbvEaNtJzC', 'technician', '9000000003'),
('Test User',        'user@srms.com',      '$2b$12$aDXy/tXP7/kS8pAer207bOJGaRngyMbWsgznI69gMuStbvEaNtJzC', 'user',       '9000000004');

-- NOTE: All four seeded accounts share the password  Password@123
-- The hash was generated with bcrypt (cost 12) and verifies correctly with PHP's
-- password_verify("Password@123", $hash) since PHP's PASSWORD_BCRYPT uses the
-- same $2y$/$2b$ bcrypt format. If you ever want to regenerate it yourself, run:
--   php -r "echo password_hash('Password@123', PASSWORD_BCRYPT), PHP_EOL;"
-- and replace the hash strings above.
