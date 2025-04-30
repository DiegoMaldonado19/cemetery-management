-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS cemetery_management;
USE cemetery_management;

CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE niche_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE niche_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE death_causes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE genders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE payment_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE contract_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE exhumation_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE cemetery_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE cemetery_blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_blocks_section FOREIGN KEY (section_id) REFERENCES cemetery_sections(id)
);

CREATE TABLE cemetery_streets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    street_number VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_streets_block FOREIGN KEY (block_id) REFERENCES cemetery_blocks(id)
);

CREATE TABLE cemetery_avenues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    avenue_number VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_avenues_block FOREIGN KEY (block_id) REFERENCES cemetery_blocks(id)
);

CREATE TABLE people (
    cui VARCHAR(13) PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender_id BIGINT UNSIGNED NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_people_gender FOREIGN KEY (gender_id) REFERENCES genders(id)
);

CREATE TABLE addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cui VARCHAR(13) NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    address_line VARCHAR(255) NOT NULL,
    reference TEXT NULL,
    is_primary BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_addresses_person FOREIGN KEY (cui) REFERENCES people(cui),
    CONSTRAINT fk_addresses_department FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE historical_figures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cui VARCHAR(13) NULL,
    historical_first_name VARCHAR(100) NULL,
    historical_last_name VARCHAR(100) NULL,
    historical_reason TEXT NOT NULL,
    declaration_date DATE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_historical_figures_person FOREIGN KEY (cui) REFERENCES people(cui),
    CONSTRAINT chk_historical_figures_info CHECK (
        cui IS NOT NULL OR 
        (historical_first_name IS NOT NULL AND historical_last_name IS NOT NULL)
    )
);

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    cui VARCHAR(13) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT uk_users_email UNIQUE (email),
    CONSTRAINT fk_users_person FOREIGN KEY (cui) REFERENCES people(cui),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE niches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    street_id BIGINT UNSIGNED NOT NULL,
    avenue_id BIGINT UNSIGNED NOT NULL,
    location_reference TEXT NULL,
    niche_type_id BIGINT UNSIGNED NOT NULL,
    niche_status_id BIGINT UNSIGNED NOT NULL,
    historical_figure_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT uk_niches_code UNIQUE (code),
    CONSTRAINT fk_niches_street FOREIGN KEY (street_id) REFERENCES cemetery_streets(id),
    CONSTRAINT fk_niches_avenue FOREIGN KEY (avenue_id) REFERENCES cemetery_avenues(id),
    CONSTRAINT fk_niches_type FOREIGN KEY (niche_type_id) REFERENCES niche_types(id),
    CONSTRAINT fk_niches_status FOREIGN KEY (niche_status_id) REFERENCES niche_statuses(id),
    CONSTRAINT fk_niches_historical_figure FOREIGN KEY (historical_figure_id) REFERENCES historical_figures(id)
);

CREATE TABLE deceased (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cui VARCHAR(13) NOT NULL,
    death_date DATE NOT NULL,
    death_cause_id BIGINT UNSIGNED NOT NULL,
    origin VARCHAR(100) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_deceased_person FOREIGN KEY (cui) REFERENCES people(cui),
    CONSTRAINT fk_deceased_cause FOREIGN KEY (death_cause_id) REFERENCES death_causes(id)
);

CREATE TABLE contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    niche_id BIGINT UNSIGNED NOT NULL,
    deceased_id BIGINT UNSIGNED NOT NULL,
    responsible_cui VARCHAR(13) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    grace_date DATE NOT NULL,
    contract_status_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_contracts_niche FOREIGN KEY (niche_id) REFERENCES niches(id),
    CONSTRAINT fk_contracts_deceased FOREIGN KEY (deceased_id) REFERENCES deceased(id),
    CONSTRAINT fk_contracts_responsible FOREIGN KEY (responsible_cui) REFERENCES people(cui),
    CONSTRAINT fk_contracts_status FOREIGN KEY (contract_status_id) REFERENCES contract_statuses(id)
);

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id BIGINT UNSIGNED NOT NULL,
    receipt_number VARCHAR(20) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    issue_date DATE NOT NULL,
    payment_date DATE NULL,
    payment_status_id BIGINT UNSIGNED NOT NULL,
    receipt_file_path VARCHAR(255) NULL,
    notes TEXT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT uk_payments_receipt_number UNIQUE (receipt_number),
    CONSTRAINT fk_payments_contract FOREIGN KEY (contract_id) REFERENCES contracts(id),
    CONSTRAINT fk_payments_status FOREIGN KEY (payment_status_id) REFERENCES payment_statuses(id),
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE exhumations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id BIGINT UNSIGNED NOT NULL,
    requester_cui VARCHAR(13) NOT NULL,
    request_date DATE NOT NULL,
    exhumation_date DATE NULL,
    reason TEXT NOT NULL,
    agreement_file_path VARCHAR(255) NOT NULL,
    exhumation_status_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_exhumations_contract FOREIGN KEY (contract_id) REFERENCES contracts(id),
    CONSTRAINT fk_exhumations_requester FOREIGN KEY (requester_cui) REFERENCES people(cui),
    CONSTRAINT fk_exhumations_status FOREIGN KEY (exhumation_status_id) REFERENCES exhumation_statuses(id),
    CONSTRAINT fk_exhumations_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id BIGINT UNSIGNED NOT NULL,
    sent_at TIMESTAMP NOT NULL,
    message TEXT NOT NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_notifications_contract FOREIGN KEY (contract_id) REFERENCES contracts(id)
);

CREATE TABLE change_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id VARCHAR(50) NOT NULL,
    changed_field VARCHAR(50) NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_change_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Inserción de datos de parámetros básicos
-- Departamentos de Guatemala
INSERT INTO departments (name, code, created_at, updated_at) VALUES 
('Guatemala', '01', NOW(), NOW()),
('El Progreso', '02', NOW(), NOW()),
('Sacatepéquez', '03', NOW(), NOW()),
('Chimaltenango', '04', NOW(), NOW()),
('Escuintla', '05', NOW(), NOW()),
('Santa Rosa', '06', NOW(), NOW()),
('Sololá', '07', NOW(), NOW()),
('Totonicapán', '08', NOW(), NOW()),
('Quetzaltenango', '09', NOW(), NOW()),
('Suchitepéquez', '10', NOW(), NOW()),
('Retalhuleu', '11', NOW(), NOW()),
('San Marcos', '12', NOW(), NOW()),
('Huehuetenango', '13', NOW(), NOW()),
('Quiché', '14', NOW(), NOW()),
('Baja Verapaz', '15', NOW(), NOW()),
('Alta Verapaz', '16', NOW(), NOW()),
('Petén', '17', NOW(), NOW()),
('Izabal', '18', NOW(), NOW()),
('Zacapa', '19', NOW(), NOW()),
('Chiquimula', '20', NOW(), NOW()),
('Jalapa', '21', NOW(), NOW()),
('Jutiapa', '22', NOW(), NOW());

INSERT INTO niche_types (name, description, created_at, updated_at) VALUES 
('Adulto', 'Nicho para adultos', NOW(), NOW()),
('Niño', 'Nicho para niños', NOW(), NOW());

INSERT INTO niche_statuses (name, description, created_at, updated_at) VALUES 
('Disponible', 'Nicho disponible para ocupar', NOW(), NOW()),
('Ocupado', 'Nicho actualmente ocupado', NOW(), NOW()),
('Proceso de Exhumación', 'Nicho en proceso de exhumación', NOW(), NOW());

INSERT INTO payment_statuses (name, description, created_at, updated_at) VALUES 
('Pagado', 'Pago completado y verificado', NOW(), NOW()),
('No Pagado', 'Pago pendiente', NOW(), NOW());

INSERT INTO contract_statuses (name, description, created_at, updated_at) VALUES 
('Vigente', 'Contrato dentro del período acordado', NOW(), NOW()),
('Vencido', 'Contrato fuera del período acordado', NOW(), NOW()),
('En Gracia', 'Contrato en período de gracia', NOW(), NOW()),
('Finalizado', 'Contrato terminado por exhumación u otro motivo', NOW(), NOW());

INSERT INTO roles (name, description, created_at, updated_at) VALUES 
('Administrador', 'Acceso total al sistema', NOW(), NOW()),
('Ayudante', 'Apoyo en tareas operativas con acceso limitado', NOW(), NOW()),
('Auditor', 'Acceso de solo lectura para validar integridad de información', NOW(), NOW()),
('Usuario de Consulta', 'Acceso restringido para consultar información específica', NOW(), NOW());

INSERT INTO exhumation_statuses (name, description, created_at, updated_at) VALUES 
('Solicitada', 'Exhumación solicitada', NOW(), NOW()),
('Aprobada', 'Exhumación aprobada', NOW(), NOW()),
('Rechazada', 'Exhumación rechazada', NOW(), NOW()),
('Completada', 'Exhumación completada', NOW(), NOW());

INSERT INTO genders (name, created_at, updated_at) VALUES 
('Masculino', NOW(), NOW()),
('Femenino', NOW(), NOW()),
('Otro', NOW(), NOW());