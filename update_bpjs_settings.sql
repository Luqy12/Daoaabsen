/**
 * Update database settings for BPJS Ketenagakerjaan branding
 */

UPDATE settings SET setting_value = 'BPJS Ketenagakerjaan' WHERE setting_key = 'company_name';
UPDATE settings SET setting_value = 'Jl. Jend. Gatot Subroto Kav. 38, Jakarta Selatan' WHERE setting_key = 'company_address';

-- Add new settings if needed
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('company_tagline', 'Melindungi Pekerja Indonesia', 'string', 'Company tagline'),
('company_phone', '1500910', 'string', 'Company phone number'),
('company_website', 'www.bpjsketenagakerjaan.go.id', 'string', 'Company website')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
