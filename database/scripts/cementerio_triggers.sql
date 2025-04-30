USE cemetery_management;

-- Trigger para verificar si el nicho tiene un personaje histórico antes de aprobar una exhumación
DELIMITER //
CREATE TRIGGER before_update_exhumations
BEFORE UPDATE ON exhumations
FOR EACH ROW
BEGIN
    DECLARE niche_id BIGINT UNSIGNED;
    DECLARE is_historical INT;
    
    -- Obtener el id del nicho asociado al contrato
    SELECT niche_id INTO niche_id
    FROM contracts
    WHERE id = NEW.contract_id;
    
    -- Verificar si el nicho está asociado a un personaje histórico
    SELECT COUNT(*) INTO is_historical
    FROM niches
    WHERE id = niche_id AND historical_figure_id IS NOT NULL;
    
    -- Si es histórico y se intenta aprobar, lanzar error
    IF is_historical > 0 AND NEW.exhumation_status_id = (SELECT id FROM exhumation_statuses WHERE name = 'Aprobada') THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No se puede exhumar a un personaje histórico.';
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar el estado del nicho cuando se aprueba una exhumación
DELIMITER //
CREATE TRIGGER after_update_exhumations_status
AFTER UPDATE ON exhumations
FOR EACH ROW
BEGIN
    DECLARE niche_id BIGINT UNSIGNED;
    DECLARE process_status_id BIGINT UNSIGNED;
    
    -- Obtener el id del nicho asociado al contrato
    SELECT niche_id INTO niche_id
    FROM contracts
    WHERE id = NEW.contract_id;
    
    -- Obtener el id del estado "En proceso de exhumación"
    SELECT id INTO process_status_id
    FROM niche_statuses
    WHERE name = 'Proceso de Exhumación';
    
    -- Si la exhumación fue aprobada, cambiar el estado del nicho
    IF NEW.exhumation_status_id = (SELECT id FROM exhumation_statuses WHERE name = 'Aprobada') THEN
        UPDATE niches
        SET niche_status_id = process_status_id,
            updated_at = NOW()
        WHERE id = niche_id;
    END IF;
    
    -- Si la exhumación fue completada, cambiar el estado del nicho a disponible y finalizar el contrato
    IF NEW.exhumation_status_id = (SELECT id FROM exhumation_statuses WHERE name = 'Completada') THEN
        UPDATE niches
        SET niche_status_id = (SELECT id FROM niche_statuses WHERE name = 'Disponible'),
            updated_at = NOW()
        WHERE id = niche_id;
        
        UPDATE contracts
        SET contract_status_id = (SELECT id FROM contract_statuses WHERE name = 'Finalizado'),
            updated_at = NOW()
        WHERE id = NEW.contract_id;
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cambios en exhumaciones
DELIMITER //
CREATE TRIGGER after_update_exhumations_audit
AFTER UPDATE ON exhumations
FOR EACH ROW
BEGIN
    -- Registrar cambio de estado de exhumación
    IF OLD.exhumation_status_id <> NEW.exhumation_status_id THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
        VALUES ('exhumations', NEW.id, 'estado_de_exhumación', 
                OLD.exhumation_status_id, NEW.exhumation_status_id, NEW.user_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en la fecha de exhumación
    IF (OLD.exhumation_date IS NULL AND NEW.exhumation_date IS NOT NULL) OR
       (OLD.exhumation_date IS NOT NULL AND NEW.exhumation_date IS NULL) OR
       (OLD.exhumation_date IS NOT NULL AND NEW.exhumation_date IS NOT NULL AND OLD.exhumation_date <> NEW.exhumation_date) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
        VALUES ('exhumations', NEW.id, 'fecha_de_exhumación', 
                CASE WHEN OLD.exhumation_date IS NULL THEN 'No definida' ELSE CAST(OLD.exhumation_date AS CHAR) END,
                CASE WHEN NEW.exhumation_date IS NULL THEN 'No definida' ELSE CAST(NEW.exhumation_date AS CHAR) END,
                NEW.user_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en el motivo
    IF OLD.reason <> NEW.reason THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
        VALUES ('exhumations', NEW.id, 'motivo', 
                OLD.reason, NEW.reason, NEW.user_id, NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar el estado del nicho cuando se crea un nuevo contrato
DELIMITER //
CREATE TRIGGER after_insert_contracts
AFTER INSERT ON contracts
FOR EACH ROW
BEGIN
    -- Actualizar el estado del nicho a ocupado
    UPDATE niches
    SET niche_status_id = (SELECT id FROM niche_statuses WHERE name = 'Ocupado'),
        updated_at = NOW()
    WHERE id = NEW.niche_id;
    
    -- Registrar la creación del contrato en el histórico
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
    VALUES ('contracts', NEW.id, 'creación', 'Ninguno', 'Nuevo contrato creado', NOW(), NOW());
END //
DELIMITER ;

-- Trigger para registrar cambios importantes en contratos
DELIMITER //
CREATE TRIGGER after_update_contracts
AFTER UPDATE ON contracts
FOR EACH ROW
BEGIN
    -- Registrar cambio de estado de contrato
    IF OLD.contract_status_id <> NEW.contract_status_id THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('contracts', NEW.id, 'estado_del_contrato', OLD.contract_status_id, NEW.contract_status_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio de fecha de fin
    IF OLD.end_date <> NEW.end_date THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('contracts', NEW.id, 'fecha_fin', OLD.end_date, NEW.end_date, NOW(), NOW());
    END IF;
    
    -- Registrar cambio de fecha de gracia
    IF OLD.grace_date <> NEW.grace_date THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('contracts', NEW.id, 'fecha_gracia', OLD.grace_date, NEW.grace_date, NOW(), NOW());
    END IF;
    
    -- Registrar cambio de responsable
    IF OLD.responsible_cui <> NEW.responsible_cui THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('contracts', NEW.id, 'cui_responsable', OLD.responsible_cui, NEW.responsible_cui, NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar el estado del contrato según fechas
DELIMITER //
CREATE TRIGGER before_update_contracts
BEFORE UPDATE ON contracts
FOR EACH ROW
BEGIN
    DECLARE active_status_id BIGINT UNSIGNED;
    DECLARE expired_status_id BIGINT UNSIGNED;
    DECLARE grace_status_id BIGINT UNSIGNED;
    DECLARE today DATE;
    
    -- Obtener ids de los estados
    SELECT id INTO active_status_id FROM contract_statuses WHERE name = 'Vigente';
    SELECT id INTO expired_status_id FROM contract_statuses WHERE name = 'Vencido';
    SELECT id INTO grace_status_id FROM contract_statuses WHERE name = 'En Gracia';
    
    SET today = CURDATE();
    
    -- Si es una renovación (fecha_fin cambia), actualizar también fecha_gracia
    IF OLD.end_date <> NEW.end_date THEN
        SET NEW.grace_date = DATE_ADD(NEW.end_date, INTERVAL 1 YEAR);
        SET NEW.updated_at = NOW();
    END IF;
END //
DELIMITER ;

-- Trigger para actualizar pagos y registrarlos
DELIMITER //
CREATE TRIGGER after_update_payments_renewal
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    -- Si el pago cambia a pagado y es una renovación (monto = 600), actualizar fecha de fin del contrato
    IF OLD.payment_status_id <> NEW.payment_status_id 
       AND NEW.payment_status_id = (SELECT id FROM payment_statuses WHERE name = 'Pagado')
       AND NEW.amount = 600.00 THEN
        
        UPDATE contracts
        SET end_date = DATE_ADD(GREATEST(CURDATE(), end_date), INTERVAL 6 YEAR),
            grace_date = DATE_ADD(GREATEST(CURDATE(), end_date), INTERVAL 7 YEAR),
            contract_status_id = (SELECT id FROM contract_statuses WHERE name = 'Vigente'),
            updated_at = NOW()
        WHERE id = NEW.contract_id;
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cambios en pagos
DELIMITER //
CREATE TRIGGER after_update_payments_audit
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    -- Registrar cambio de estado de pago
    IF OLD.payment_status_id <> NEW.payment_status_id THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
        VALUES ('payments', NEW.id, 'estado_de_pago', OLD.payment_status_id, NEW.payment_status_id, NEW.user_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en la fecha de pago
    IF (OLD.payment_date IS NULL AND NEW.payment_date IS NOT NULL) OR
       (OLD.payment_date IS NOT NULL AND NEW.payment_date IS NULL) OR
       (OLD.payment_date IS NOT NULL AND NEW.payment_date IS NOT NULL AND OLD.payment_date <> NEW.payment_date) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
        VALUES ('payments', NEW.id, 'fecha_de_pago', 
                CASE WHEN OLD.payment_date IS NULL THEN 'No definida' ELSE CAST(OLD.payment_date AS CHAR) END,
                CASE WHEN NEW.payment_date IS NULL THEN 'No definida' ELSE CAST(NEW.payment_date AS CHAR) END,
                NEW.user_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en la ruta del comprobante
    IF (OLD.receipt_file_path IS NULL AND NEW.receipt_file_path IS NOT NULL) OR
       (OLD.receipt_file_path IS NOT NULL AND NEW.receipt_file_path IS NULL) OR
       (OLD.receipt_file_path IS NOT NULL AND NEW.receipt_file_path IS NOT NULL AND OLD.receipt_file_path <> NEW.receipt_file_path) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
        VALUES ('payments', NEW.id, 'ruta_comprobante', 
                CASE WHEN OLD.receipt_file_path IS NULL THEN 'No definida' ELSE OLD.receipt_file_path END,
                CASE WHEN NEW.receipt_file_path IS NULL THEN 'No definida' ELSE NEW.receipt_file_path END,
                NEW.user_id, NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cambios en nichos
DELIMITER //
CREATE TRIGGER after_update_niches
AFTER UPDATE ON niches
FOR EACH ROW
BEGIN
    -- Registrar cambio de estado
    IF OLD.niche_status_id <> NEW.niche_status_id THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('niches', NEW.id, 'estado_del_nicho', OLD.niche_status_id, NEW.niche_status_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en la asociación con personaje histórico
    IF (OLD.historical_figure_id IS NULL AND NEW.historical_figure_id IS NOT NULL) OR
       (OLD.historical_figure_id IS NOT NULL AND NEW.historical_figure_id IS NULL) OR
       (OLD.historical_figure_id IS NOT NULL AND NEW.historical_figure_id IS NOT NULL AND 
        OLD.historical_figure_id <> NEW.historical_figure_id) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('niches', NEW.id, 'personaje_histórico', 
                CASE WHEN OLD.historical_figure_id IS NULL THEN 'Ninguno' ELSE CAST(OLD.historical_figure_id AS CHAR) END, 
                CASE WHEN NEW.historical_figure_id IS NULL THEN 'Ninguno' ELSE CAST(NEW.historical_figure_id AS CHAR) END,
                NOW(), NOW());
    END IF;
    
    -- Registrar cambio en el tipo de nicho
    IF OLD.niche_type_id <> NEW.niche_type_id THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('niches', NEW.id, 'tipo_de_nicho', OLD.niche_type_id, NEW.niche_type_id, NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cambios en personas
DELIMITER //
CREATE TRIGGER after_update_people
AFTER UPDATE ON people
FOR EACH ROW
BEGIN
    -- Registrar cambios en datos personales
    IF OLD.first_name <> NEW.first_name THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('people', NEW.cui, 'nombre', OLD.first_name, NEW.first_name, NOW(), NOW());
    END IF;
    
    IF OLD.last_name <> NEW.last_name THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('people', NEW.cui, 'apellidos', OLD.last_name, NEW.last_name, NOW(), NOW());
    END IF;
    
    IF (OLD.phone IS NULL AND NEW.phone IS NOT NULL) OR
       (OLD.phone IS NOT NULL AND NEW.phone IS NULL) OR
       (OLD.phone IS NOT NULL AND NEW.phone IS NOT NULL AND OLD.phone <> NEW.phone) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('people', NEW.cui, 'teléfono', 
                CASE WHEN OLD.phone IS NULL THEN 'No definido' ELSE OLD.phone END,
                CASE WHEN NEW.phone IS NULL THEN 'No definido' ELSE NEW.phone END,
                NOW(), NOW());
    END IF;
    
    IF (OLD.email IS NULL AND NEW.email IS NOT NULL) OR
       (OLD.email IS NOT NULL AND NEW.email IS NULL) OR
       (OLD.email IS NOT NULL AND NEW.email IS NOT NULL AND OLD.email <> NEW.email) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('people', NEW.cui, 'correo_electrónico', 
                CASE WHEN OLD.email IS NULL THEN 'No definido' ELSE OLD.email END,
                CASE WHEN NEW.email IS NULL THEN 'No definido' ELSE NEW.email END,
                NOW(), NOW());
    END IF;
    
    IF (OLD.gender_id IS NULL AND NEW.gender_id IS NOT NULL) OR
       (OLD.gender_id IS NOT NULL AND NEW.gender_id IS NULL) OR
       (OLD.gender_id IS NOT NULL AND NEW.gender_id IS NOT NULL AND OLD.gender_id <> NEW.gender_id) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('people', NEW.cui, 'género', 
                CASE WHEN OLD.gender_id IS NULL THEN 'No definido' ELSE CAST(OLD.gender_id AS CHAR) END,
                CASE WHEN NEW.gender_id IS NULL THEN 'No definido' ELSE CAST(NEW.gender_id AS CHAR) END,
                NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cambios en personajes históricos
DELIMITER //
CREATE TRIGGER after_update_historical_figures
AFTER UPDATE ON historical_figures
FOR EACH ROW
BEGIN
    -- Registrar cambios en datos del personaje histórico
    IF (OLD.cui IS NULL AND NEW.cui IS NOT NULL) OR
       (OLD.cui IS NOT NULL AND NEW.cui IS NULL) OR
       (OLD.cui IS NOT NULL AND NEW.cui IS NOT NULL AND OLD.cui <> NEW.cui) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('historical_figures', NEW.id, 'cui', 
                CASE WHEN OLD.cui IS NULL THEN 'No definido' ELSE OLD.cui END,
                CASE WHEN NEW.cui IS NULL THEN 'No definido' ELSE NEW.cui END,
                NOW(), NOW());
    END IF;
    
    IF (OLD.historical_first_name IS NULL AND NEW.historical_first_name IS NOT NULL) OR
       (OLD.historical_first_name IS NOT NULL AND NEW.historical_first_name IS NULL) OR
       (OLD.historical_first_name IS NOT NULL AND NEW.historical_first_name IS NOT NULL AND OLD.historical_first_name <> NEW.historical_first_name) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('historical_figures', NEW.id, 'nombre_histórico', 
                CASE WHEN OLD.historical_first_name IS NULL THEN 'No definido' ELSE OLD.historical_first_name END,
                CASE WHEN NEW.historical_first_name IS NULL THEN 'No definido' ELSE NEW.historical_first_name END,
                NOW(), NOW());
    END IF;
    
    IF (OLD.historical_last_name IS NULL AND NEW.historical_last_name IS NOT NULL) OR
       (OLD.historical_last_name IS NOT NULL AND NEW.historical_last_name IS NULL) OR
       (OLD.historical_last_name IS NOT NULL AND NEW.historical_last_name IS NOT NULL AND OLD.historical_last_name <> NEW.historical_last_name) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('historical_figures', NEW.id, 'apellido_histórico', 
                CASE WHEN OLD.historical_last_name IS NULL THEN 'No definido' ELSE OLD.historical_last_name END,
                CASE WHEN NEW.historical_last_name IS NULL THEN 'No definido' ELSE NEW.historical_last_name END,
                NOW(), NOW());
    END IF;
    
    IF OLD.historical_reason <> NEW.historical_reason THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('historical_figures', NEW.id, 'motivo_histórico', OLD.historical_reason, NEW.historical_reason, NOW(), NOW());
    END IF;
    
    IF OLD.declaration_date <> NEW.declaration_date THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('historical_figures', NEW.id, 'fecha_de_declaración', OLD.declaration_date, NEW.declaration_date, NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cuando se crea un personaje histórico
DELIMITER //
CREATE TRIGGER after_insert_historical_figures
AFTER INSERT ON historical_figures
FOR EACH ROW
BEGIN
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
    VALUES ('historical_figures', NEW.id, 'creación', 'Ninguno', 'Nuevo personaje histórico registrado', NOW(), NOW());
END //
DELIMITER ;

-- Trigger para registrar cuando se crea una persona
DELIMITER //
CREATE TRIGGER after_insert_people
AFTER INSERT ON people
FOR EACH ROW
BEGIN
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
    VALUES ('people', NEW.cui, 'creación', 'Ninguno', 'Nueva persona registrada', NOW(), NOW());
END //
DELIMITER ;

-- Trigger para registrar cuando se crea un nicho
DELIMITER //
CREATE TRIGGER after_insert_niches
AFTER INSERT ON niches
FOR EACH ROW
BEGIN
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
    VALUES ('niches', NEW.id, 'creación', 'Ninguno', 'Nuevo nicho registrado', NOW(), NOW());
END //
DELIMITER ;

-- Trigger para registrar cuando se crea un pago
DELIMITER //
CREATE TRIGGER after_insert_payments
AFTER INSERT ON payments
FOR EACH ROW
BEGIN
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
    VALUES ('payments', NEW.id, 'creación', 'Ninguno', CONCAT('Nuevo pago registrado por monto: Q', NEW.amount), NEW.user_id, NOW(), NOW());
END //
DELIMITER ;

-- Trigger para registrar cuando se crea una exhumación
DELIMITER //
CREATE TRIGGER after_insert_exhumations
AFTER INSERT ON exhumations
FOR EACH ROW
BEGIN
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, user_id, created_at, updated_at)
    VALUES ('exhumations', NEW.id, 'creación', 'Ninguno', 'Nueva solicitud de exhumación registrada', NEW.user_id, NOW(), NOW());
END //
DELIMITER ;

-- Trigger para registrar cambios en direcciones
DELIMITER //
CREATE TRIGGER after_update_addresses
AFTER UPDATE ON addresses
FOR EACH ROW
BEGIN
    -- Registrar cambio en la dirección
    IF OLD.address_line <> NEW.address_line THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('addresses', NEW.id, 'dirección', OLD.address_line, NEW.address_line, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en el departamento
    IF OLD.department_id <> NEW.department_id THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('addresses', NEW.id, 'departamento', OLD.department_id, NEW.department_id, NOW(), NOW());
    END IF;
    
    -- Registrar cambio en la referencia
    IF (OLD.reference IS NULL AND NEW.reference IS NOT NULL) OR
       (OLD.reference IS NOT NULL AND NEW.reference IS NULL) OR
       (OLD.reference IS NOT NULL AND NEW.reference IS NOT NULL AND OLD.reference <> NEW.reference) THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('addresses', NEW.id, 'referencia', 
                CASE WHEN OLD.reference IS NULL THEN 'No definida' ELSE OLD.reference END,
                CASE WHEN NEW.reference IS NULL THEN 'No definida' ELSE NEW.reference END,
                NOW(), NOW());
    END IF;
    
    -- Registrar cambio en la prioridad
    IF OLD.is_primary <> NEW.is_primary THEN
        INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
        VALUES ('addresses', NEW.id, 'es_principal', 
                CASE WHEN OLD.is_primary = 1 THEN 'Sí' ELSE 'No' END,
                CASE WHEN NEW.is_primary = 1 THEN 'Sí' ELSE 'No' END,
                NOW(), NOW());
    END IF;
END //
DELIMITER ;

-- Trigger para registrar cuando se crea una dirección
DELIMITER //
CREATE TRIGGER after_insert_addresses
AFTER INSERT ON addresses
FOR EACH ROW
BEGIN
    INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, created_at, updated_at)
    VALUES ('addresses', NEW.id, 'creación', 'Ninguno', 'Nueva dirección registrada', NOW(), NOW());
END //
DELIMITER ;

-- Evento para actualizar el estado de contratos basado en la fecha actual (ejecutar diariamente)
DELIMITER //
CREATE EVENT IF NOT EXISTS update_contract_statuses
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DECLARE active_status_id BIGINT UNSIGNED;
    DECLARE expired_status_id BIGINT UNSIGNED;
    DECLARE grace_status_id BIGINT UNSIGNED;
    
    -- Obtener ids de los estados
    SELECT id INTO active_status_id FROM contract_statuses WHERE name = 'Vigente';
    SELECT id INTO expired_status_id FROM contract_statuses WHERE name = 'Vencido';
    SELECT id INTO grace_status_id FROM contract_statuses WHERE name = 'En Gracia';
    
    -- Actualizar contratos que han expirado (pasó fecha_fin)
    UPDATE contracts
    SET contract_status_id = grace_status_id,
        updated_at = NOW()
    WHERE contract_status_id = active_status_id
    AND end_date < CURDATE()
    AND grace_date >= CURDATE();
    
    -- Actualizar contratos que han pasado el período de gracia
    UPDATE contracts
    SET contract_status_id = expired_status_id,
        updated_at = NOW()
    WHERE contract_status_id = grace_status_id
    AND grace_date < CURDATE();
END //
DELIMITER ;