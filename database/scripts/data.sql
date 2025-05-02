-- Script de datos de muestra para el sistema de gestión de nichos
-- Incluye al menos 10 registros para las tablas principales y datos especiales para consultas

USE cemetery_management;

-- Datos de muestra: Secciones del cementerio
INSERT INTO cemetery_sections (name, description, created_at, updated_at) VALUES 
('Sección A', 'Área antigua del cementerio', NOW(), NOW()),
('Sección B', 'Área central del cementerio', NOW(), NOW()),
('Sección C', 'Área nueva del cementerio', NOW(), NOW()),
('Sección D', 'Área de mausoleos familiares', NOW(), NOW()),
('Sección E', 'Área de nichos para niños', NOW(), NOW());

-- Datos de muestra: Bloques del cementerio
INSERT INTO cemetery_blocks (section_id, name, description, created_at, updated_at) VALUES 
(1, 'Bloque A1', 'Primer bloque de la sección A', NOW(), NOW()),
(1, 'Bloque A2', 'Segundo bloque de la sección A', NOW(), NOW()),
(2, 'Bloque B1', 'Primer bloque de la sección B', NOW(), NOW()),
(2, 'Bloque B2', 'Segundo bloque de la sección B', NOW(), NOW()),
(3, 'Bloque C1', 'Primer bloque de la sección C', NOW(), NOW()),
(3, 'Bloque C2', 'Segundo bloque de la sección C', NOW(), NOW()),
(4, 'Bloque D1', 'Primer bloque de la sección D', NOW(), NOW()),
(5, 'Bloque E1', 'Bloque infantil de la sección E', NOW(), NOW());

-- Datos de muestra: Calles del cementerio
INSERT INTO cemetery_streets (block_id, name, street_number, created_at, updated_at) VALUES 
(1, 'Calle 1A1', '1', NOW(), NOW()),
(1, 'Calle 2A1', '2', NOW(), NOW()),
(2, 'Calle 1A2', '1', NOW(), NOW()),
(2, 'Calle 2A2', '2', NOW(), NOW()),
(3, 'Calle 1B1', '1', NOW(), NOW()),
(3, 'Calle 2B1', '2', NOW(), NOW()),
(4, 'Calle 1B2', '1', NOW(), NOW()),
(5, 'Calle 1C1', '1', NOW(), NOW()),
(6, 'Calle 1C2', '1', NOW(), NOW()),
(7, 'Calle 1D1', '1', NOW(), NOW()),
(8, 'Calle 1E1', '1', NOW(), NOW());

-- Datos de muestra: Avenidas del cementerio
INSERT INTO cemetery_avenues (block_id, name, avenue_number, created_at, updated_at) VALUES 
(1, 'Avenida 1A1', '1', NOW(), NOW()),
(1, 'Avenida 2A1', '2', NOW(), NOW()),
(2, 'Avenida 1A2', '1', NOW(), NOW()),
(2, 'Avenida 2A2', '2', NOW(), NOW()),
(3, 'Avenida 1B1', '1', NOW(), NOW()),
(3, 'Avenida 2B1', '2', NOW(), NOW()),
(4, 'Avenida 1B2', '1', NOW(), NOW()),
(5, 'Avenida 1C1', '1', NOW(), NOW()),
(6, 'Avenida 1C2', '1', NOW(), NOW()),
(7, 'Avenida 1D1', '1', NOW(), NOW()),
(8, 'Avenida 1E1', '1', NOW(), NOW());

-- Datos de muestra: Causas de muerte
INSERT INTO death_causes (name, description, created_at, updated_at) VALUES 
('Causas Naturales', 'Fallecimiento por vejez o causas naturales', NOW(), NOW()),
('Enfermedad Cardíaca', 'Fallecimiento por problemas del corazón', NOW(), NOW()),
('Cáncer', 'Fallecimiento por diferentes tipos de cáncer', NOW(), NOW()),
('Accidente de Tránsito', 'Fallecimiento por accidente automovilístico', NOW(), NOW()),
('Neumonía', 'Fallecimiento por complicaciones respiratorias', NOW(), NOW()),
('COVID-19', 'Fallecimiento por coronavirus', NOW(), NOW()),
('Diabetes', 'Fallecimiento por complicaciones de diabetes', NOW(), NOW()),
('Derrame Cerebral', 'Fallecimiento por ACV', NOW(), NOW()),
('Insuficiencia Renal', 'Fallecimiento por problemas renales', NOW(), NOW()),
('Otra', 'Otras causas de fallecimiento', NOW(), NOW());

-- Personas (incluyendo fallecidos, responsables y usuarios)
INSERT INTO people (cui, first_name, last_name, gender_id, email, phone, created_at, updated_at) VALUES 
-- Administradores y personal (1-5)
('1234567890123', 'Administrador', 'Principal', 1, 'admin@cementerio.com', '55123456', NOW(), NOW()),
('2345678901234', 'Ayudante', 'Sistema', 2, 'ayudante@cementerio.com', '55234567', NOW(), NOW()),
('3456789012345', 'Auditor', 'Financiero', 1, 'auditor@cementerio.com', '55345678', NOW(), NOW()),
('4567890123456', 'Usuario', 'Consulta', 2, 'consulta@cementerio.com', '55456789', NOW(), NOW()),
('5678901234567', 'Segundo', 'Administrador', 1, 'admin2@cementerio.com', '55567890', NOW(), NOW()),

-- Fallecidos (6-15)
('6789012345678', 'Juan', 'Pérez', 1, 'juan.perez@ejemplo.com', '55678901', NOW(), NOW()),
('7890123456789', 'María', 'López', 2, 'maria.lopez@ejemplo.com', '55789012', NOW(), NOW()),
('8901234567890', 'Carlos', 'González', 1, 'carlos.gonzalez@ejemplo.com', '55890123', NOW(), NOW()),
('9012345678901', 'Ana', 'Martínez', 2, 'ana.martinez@ejemplo.com', '55901234', NOW(), NOW()),
('0123456789012', 'Pedro', 'Rodríguez', 1, 'pedro.rodriguez@ejemplo.com', '55012345', NOW(), NOW()),
('1234567890124', 'Lucía', 'Hernández', 2, 'lucia.hernandez@ejemplo.com', '55123457', NOW(), NOW()),
('2345678901235', 'Miguel', 'Díaz', 1, 'miguel.diaz@ejemplo.com', '55234568', NOW(), NOW()),
('3456789012346', 'Sofía', 'Sánchez', 2, 'sofia.sanchez@ejemplo.com', '55345679', NOW(), NOW()),
('4567890123457', 'Roberto', 'Torres', 1, 'roberto.torres@ejemplo.com', '55456780', NOW(), NOW()),
('5678901234568', 'Elena', 'Flores', 2, 'elena.flores@ejemplo.com', '55567891', NOW(), NOW()),

-- Personajes históricos (16-18)
('6789012345679', 'Benito', 'Juárez', 1, NULL, NULL, NOW(), NOW()),
('7890123456780', 'Rigoberta', 'Menchú', 2, NULL, NULL, NOW(), NOW()),
('8901234567891', 'Miguel Ángel', 'Asturias', 1, NULL, NULL, NOW(), NOW()),

-- Responsables de contratos (19-28)
('9012345678902', 'Francisco', 'Gómez', 1, 'francisco.gomez@ejemplo.com', '55901235', NOW(), NOW()),
('0123456789013', 'Laura', 'Castillo', 2, 'laura.castillo@ejemplo.com', '55012346', NOW(), NOW()),
('1234567890125', 'Jorge', 'Vargas', 1, 'jorge.vargas@ejemplo.com', '55123458', NOW(), NOW()),
('2345678901236', 'Carla', 'Morales', 2, 'carla.morales@ejemplo.com', '55234569', NOW(), NOW()),
('3456789012347', 'Daniel', 'Rivas', 1, 'daniel.rivas@ejemplo.com', '55345670', NOW(), NOW()),
('4567890123458', 'Gabriela', 'Mendoza', 2, 'gabriela.mendoza@ejemplo.com', '55456781', NOW(), NOW()),
('5678901234569', 'Héctor', 'Ortega', 1, 'hector.ortega@ejemplo.com', '55567892', NOW(), NOW()),
('6789012345670', 'Patricia', 'Cruz', 2, 'patricia.cruz@ejemplo.com', '55678902', NOW(), NOW()),
('7890123456781', 'Raúl', 'Medina', 1, 'raul.medina@ejemplo.com', '55789013', NOW(), NOW()),
('8901234567892', 'Verónica', 'Cortés', 2, 'veronica.cortes@ejemplo.com', '55890124', NOW(), NOW());

-- Crear usuarios con contraseñas hasheadas (admin123, ayudante123, auditor123, consulta123)
INSERT INTO users (name, email, password, cui, role_id, is_active, created_at, updated_at) VALUES 
('Administrador', 'admin@cementerio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890123', 1, 1, NOW(), NOW()),
('Ayudante', 'ayudante@cementerio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2345678901234', 2, 1, NOW(), NOW()),
('Auditor', 'auditor@cementerio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3456789012345', 3, 1, NOW(), NOW()),
('Usuario Consulta', 'consulta@cementerio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '4567890123456', 4, 1, NOW(), NOW()),
('Admin2', 'admin2@cementerio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5678901234567', 1, 1, NOW(), NOW());

-- Nota: La contraseña hasheada corresponde a 'password' en Laravel

-- Direcciones de los responsables
INSERT INTO addresses (cui, department_id, address_line, reference, is_primary, created_at, updated_at) VALUES 
('9012345678902', 9, 'Zona 1, Calle 5, Casa 10', 'Cerca de la plaza central', 1, NOW(), NOW()),
('0123456789013', 9, 'Zona 3, Avenida 8, Casa 15', 'Frente al parque', 1, NOW(), NOW()),
('1234567890125', 9, 'Zona 2, Calle 7, Casa 22', 'A la par de la iglesia', 1, NOW(), NOW()),
('2345678901236', 9, 'Zona 4, Avenida 3, Casa 5', 'Esquina con tienda', 1, NOW(), NOW()),
('3456789012347', 9, 'Zona 1, Calle 9, Casa 18', 'Edificio azul', 1, NOW(), NOW()),
('4567890123458', 13, 'Zona 1, Avenida 2, Casa 7', 'Frente al mercado', 1, NOW(), NOW()),
('5678901234569', 13, 'Zona 5, Calle 1, Casa 30', 'Casa amarilla', 1, NOW(), NOW()),
('6789012345670', 10, 'Zona 2, Avenida 5, Casa 12', 'Segundo nivel', 1, NOW(), NOW()),
('7890123456781', 12, 'Zona 3, Calle 4, Casa 9', 'Esquina con farmacia', 1, NOW(), NOW()),
('8901234567892', 9, 'Zona 6, Avenida 7, Casa 25', 'Casa con portón negro', 1, NOW(), NOW());

-- Registrar a los fallecidos
INSERT INTO deceased (cui, death_date, death_cause_id, origin, notes, created_at, updated_at) VALUES 
('6789012345678', DATE_SUB(NOW(), INTERVAL 2 YEAR), 1, 'Quetzaltenango', 'Fallecimiento en su domicilio', NOW(), NOW()),
('7890123456789', DATE_SUB(NOW(), INTERVAL 3 YEAR), 2, 'Quetzaltenango', 'Fallecimiento en hospital', NOW(), NOW()),
('8901234567890', DATE_SUB(NOW(), INTERVAL 1 YEAR), 3, 'Guatemala', 'Fallecimiento durante tratamiento', NOW(), NOW()),
('9012345678901', DATE_SUB(NOW(), INTERVAL 5 YEAR), 4, 'Quetzaltenango', 'Fallecimiento instantáneo', NOW(), NOW()),
('0123456789012', DATE_SUB(NOW(), INTERVAL 6 MONTH), 5, 'Huehuetenango', 'Fallecimiento hospitalario', NOW(), NOW()),
('1234567890124', DATE_SUB(NOW(), INTERVAL 4 YEAR), 6, 'Quetzaltenango', 'Fallecimiento durante la pandemia', NOW(), NOW()),
('2345678901235', DATE_SUB(NOW(), INTERVAL 2 YEAR), 7, 'Guatemala', 'Complicaciones por diabetes', NOW(), NOW()),
('3456789012346', DATE_SUB(NOW(), INTERVAL 1 YEAR), 8, 'Quetzaltenango', 'Fallecimiento repentino', NOW(), NOW()),
('4567890123457', DATE_SUB(NOW(), INTERVAL 8 MONTH), 9, 'San Marcos', 'Después de tratamiento', NOW(), NOW()),
('5678901234568', DATE_SUB(NOW(), INTERVAL 3 YEAR), 10, 'Quetzaltenango', 'Causas diversas', NOW(), NOW()),
('6789012345679', DATE_SUB(NOW(), INTERVAL 100 YEAR), 1, 'Oaxaca, México', 'Personaje histórico', NOW(), NOW()),
('7890123456780', DATE_SUB(NOW(), INTERVAL 0 YEAR), 1, 'Quetzaltenango', 'Personaje histórico vivo', NOW(), NOW()),
('8901234567891', DATE_SUB(NOW(), INTERVAL 50 YEAR), 1, 'Guatemala', 'Premio Nobel de Literatura', NOW(), NOW());

-- Registrar personajes históricos
INSERT INTO historical_figures (cui, historical_first_name, historical_last_name, historical_reason, declaration_date, created_at, updated_at) VALUES 
('6789012345679', NULL, NULL, 'Presidente de México y reformador. Símbolo de la resistencia indígena y republicana.', DATE_SUB(NOW(), INTERVAL 50 YEAR), NOW(), NOW()),
('7890123456780', NULL, NULL, 'Premio Nobel de la Paz, defensora de los derechos humanos y los pueblos indígenas.', DATE_SUB(NOW(), INTERVAL 5 YEAR), NOW(), NOW()),
('8901234567891', NULL, NULL, 'Premio Nobel de Literatura, autor de "El Señor Presidente" y otras obras importantes.', DATE_SUB(NOW(), INTERVAL 30 YEAR), NOW(), NOW());

-- Crear nichos
INSERT INTO niches (code, street_id, avenue_id, location_reference, niche_type_id, niche_status_id, historical_figure_id, created_at, updated_at) VALUES 
-- Nichos adultos disponibles
('A-001', 1, 1, 'Primera fila', 1, 1, NULL, NOW(), NOW()),
('A-002', 1, 1, 'Primera fila', 1, 1, NULL, NOW(), NOW()),
('A-003', 1, 1, 'Primera fila', 1, 1, NULL, NOW(), NOW()),
('A-004', 1, 2, 'Segunda fila', 1, 1, NULL, NOW(), NOW()),
('A-005', 1, 2, 'Segunda fila', 1, 1, NULL, NOW(), NOW()),

-- Nichos adultos ocupados
('A-006', 2, 1, 'Primera fila', 1, 2, NULL, NOW(), NOW()),
('A-007', 2, 1, 'Primera fila', 1, 2, NULL, NOW(), NOW()),
('A-008', 2, 2, 'Segunda fila', 1, 2, NULL, NOW(), NOW()),
('A-009', 2, 2, 'Segunda fila', 1, 2, NULL, NOW(), NOW()),
('A-010', 3, 1, 'Primera fila', 1, 2, NULL, NOW(), NOW()),

-- Nichos de niños
('N-001', 11, 11, 'Área infantil', 2, 1, NULL, NOW(), NOW()),
('N-002', 11, 11, 'Área infantil', 2, 1, NULL, NOW(), NOW()),
('N-003', 11, 11, 'Área infantil', 2, 2, NULL, NOW(), NOW()),
('N-004', 11, 11, 'Área infantil', 2, 2, NULL, NOW(), NOW()),
('N-005', 11, 11, 'Área infantil', 2, 1, NULL, NOW(), NOW()),

-- Nicho en proceso de exhumación
('A-011', 3, 1, 'Primera fila', 1, 3, NULL, NOW(), NOW()),

-- Nichos de personajes históricos
('H-001', 7, 7, 'Área de personajes históricos', 1, 2, 1, NOW(), NOW()),
('H-002', 7, 7, 'Área de personajes históricos', 1, 2, 2, NOW(), NOW()),
('H-003', 7, 7, 'Área de personajes históricos', 1, 2, 3, NOW(), NOW());

-- Contratos
-- Nota: Incluimos diferentes tipos de contratos para las consultas:
-- - Contratos que vencen en los próximos 30 días
-- - Contratos que ya vencieron (en gracia)
-- - Contratos regulares vigentes
-- - Contratos finalizados

INSERT INTO contracts (niche_id, deceased_id, responsible_cui, start_date, end_date, grace_date, contract_status_id, notes, created_at, updated_at) VALUES 
-- Contratos que vencen en los próximos 30 días
(6, 1, '9012345678902', DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 1 YEAR), 1, 'Contrato próximo a vencer', NOW(), NOW()),
(7, 2, '0123456789013', DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_ADD(NOW(), INTERVAL 25 DAY), DATE_ADD(NOW(), INTERVAL 1 YEAR), 1, 'Contrato próximo a vencer', NOW(), NOW()),

-- Contratos en periodo de gracia
(8, 3, '1234567890125', DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_ADD(NOW(), INTERVAL 10 MONTH), 3, 'Contrato en periodo de gracia', NOW(), NOW()),
(9, 4, '2345678901236', DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_SUB(NOW(), INTERVAL 3 MONTH), DATE_ADD(NOW(), INTERVAL 9 MONTH), 3, 'Contrato en periodo de gracia', NOW(), NOW()),

-- Contratos vigentes normales
(10, 5, '3456789012347', DATE_SUB(NOW(), INTERVAL 2 YEAR), DATE_ADD(NOW(), INTERVAL 4 YEAR), DATE_ADD(NOW(), INTERVAL 5 YEAR), 1, 'Contrato vigente normal', NOW(), NOW()),
(13, 6, '4567890123458', DATE_SUB(NOW(), INTERVAL 3 YEAR), DATE_ADD(NOW(), INTERVAL 3 YEAR), DATE_ADD(NOW(), INTERVAL 4 YEAR), 1, 'Contrato vigente normal', NOW(), NOW()),
(14, 7, '5678901234569', DATE_SUB(NOW(), INTERVAL 1 YEAR), DATE_ADD(NOW(), INTERVAL 5 YEAR), DATE_ADD(NOW(), INTERVAL 6 YEAR), 1, 'Contrato vigente normal', NOW(), NOW()),

-- Contratos vencidos
(11, 8, '6789012345670', DATE_SUB(NOW(), INTERVAL 7 YEAR), DATE_SUB(NOW(), INTERVAL 1 YEAR), DATE_SUB(NOW(), INTERVAL 1 MONTH), 2, 'Contrato vencido', NOW(), NOW()),

-- Contrato en proceso de exhumación
(16, 9, '7890123456781', DATE_SUB(NOW(), INTERVAL 3 YEAR), DATE_ADD(NOW(), INTERVAL 3 YEAR), DATE_ADD(NOW(), INTERVAL 4 YEAR), 1, 'Contrato con exhumación solicitada', NOW(), NOW()),

-- Contratos para personajes históricos
(17, 11, '8901234567892', DATE_SUB(NOW(), INTERVAL 30 YEAR), DATE_ADD(NOW(), INTERVAL 70 YEAR), DATE_ADD(NOW(), INTERVAL 71 YEAR), 1, 'Contrato de personaje histórico', NOW(), NOW()),
(18, 12, '9012345678902', DATE_SUB(NOW(), INTERVAL 5 YEAR), DATE_ADD(NOW(), INTERVAL 95 YEAR), DATE_ADD(NOW(), INTERVAL 96 YEAR), 1, 'Contrato de personaje histórico', NOW(), NOW()),
(19, 13, '0123456789013', DATE_SUB(NOW(), INTERVAL 20 YEAR), DATE_ADD(NOW(), INTERVAL 80 YEAR), DATE_ADD(NOW(), INTERVAL 81 YEAR), 1, 'Contrato de personaje histórico', NOW(), NOW());

-- Pagos
INSERT INTO payments (contract_id, receipt_number, amount, issue_date, payment_date, payment_status_id, receipt_file_path, notes, user_id, created_at, updated_at) VALUES 
-- Pagos para contratos que vencen pronto
(1, 'REC-000001', 600.00, DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_SUB(NOW(), INTERVAL 6 YEAR), 1, NULL, 'Pago inicial', 1, NOW(), NOW()),
(1, 'REC-000002', 600.00, DATE_SUB(NOW(), INTERVAL 15 DAY), NULL, 2, NULL, 'Pago de renovación', 1, NOW(), NOW()),
(2, 'REC-000003', 600.00, DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_SUB(NOW(), INTERVAL 6 YEAR), 1, NULL, 'Pago inicial', 1, NOW(), NOW()),
(2, 'REC-000004', 600.00, DATE_SUB(NOW(), INTERVAL 20 DAY), NULL, 2, NULL, 'Pago de renovación', 2, NOW(), NOW()),

-- Pagos para contratos en gracia
(3, 'REC-000005', 600.00, DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_SUB(NOW(), INTERVAL 6 YEAR), 1, NULL, 'Pago inicial', 1, NOW(), NOW()),
(3, 'REC-000006', 600.00, DATE_SUB(NOW(), INTERVAL 2 MONTH), NULL, 2, NULL, 'Pago de renovación', 1, NOW(), NOW()),
(4, 'REC-000007', 600.00, DATE_SUB(NOW(), INTERVAL 6 YEAR), DATE_SUB(NOW(), INTERVAL 6 YEAR), 1, NULL, 'Pago inicial', 2, NOW(), NOW()),
(4, 'REC-000008', 600.00, DATE_SUB(NOW(), INTERVAL 3 MONTH), NULL, 2, NULL, 'Pago de renovación', 2, NOW(), NOW()),

-- Pagos para contratos vigentes
(5, 'REC-000009', 600.00, DATE_SUB(NOW(), INTERVAL 2 YEAR), DATE_SUB(NOW(), INTERVAL 2 YEAR), 1, NULL, 'Pago inicial', 1, NOW(), NOW()),
(6, 'REC-000010', 600.00, DATE_SUB(NOW(), INTERVAL 3 YEAR), DATE_SUB(NOW(), INTERVAL 3 YEAR), 1, NULL, 'Pago inicial', 2, NOW(), NOW()),
(7, 'REC-000011', 600.00, DATE_SUB(NOW(), INTERVAL 1 YEAR), DATE_SUB(NOW(), INTERVAL 1 YEAR), 1, NULL, 'Pago inicial', 1, NOW(), NOW()),

-- Pagos para contrato vencido
(8, 'REC-000012', 600.00, DATE_SUB(NOW(), INTERVAL 7 YEAR), DATE_SUB(NOW(), INTERVAL 7 YEAR), 1, NULL, 'Pago inicial', 2, NOW(), NOW()),

-- Pagos para contrato en exhumación
(9, 'REC-000013', 600.00, DATE_SUB(NOW(), INTERVAL 3 YEAR), DATE_SUB(NOW(), INTERVAL 3 YEAR), 1, NULL, 'Pago inicial', 1, NOW(), NOW()),

-- Pagos para personajes históricos
(10, 'REC-000014', 600.00, DATE_SUB(NOW(), INTERVAL 30 YEAR), DATE_SUB(NOW(), INTERVAL 30 YEAR), 1, NULL, 'Pago inicial personaje histórico', 1, NOW(), NOW()),
(11, 'REC-000015', 600.00, DATE_SUB(NOW(), INTERVAL 5 YEAR), DATE_SUB(NOW(), INTERVAL 5 YEAR), 1, NULL, 'Pago inicial personaje histórico', 2, NOW(), NOW()),
(12, 'REC-000016', 600.00, DATE_SUB(NOW(), INTERVAL 20 YEAR), DATE_SUB(NOW(), INTERVAL 20 YEAR), 1, NULL, 'Pago inicial personaje histórico', 1, NOW(), NOW());

-- Exhumaciones
INSERT INTO exhumations (contract_id, requester_cui, request_date, exhumation_date, reason, agreement_file_path, exhumation_status_id, user_id, notes, created_at, updated_at) VALUES 
-- Exhumación solicitada (pendiente)
(9, '7890123456781', DATE_SUB(NOW(), INTERVAL 15 DAY), NULL, 'Traslado a otro cementerio', 'exhumation_agreements/acuerdo1.pdf', 1, 1, 'Documentación completa', NOW(), NOW()),

-- Exhumación aprobada (en proceso)
(8, '6789012345670', DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_ADD(NOW(), INTERVAL 15 DAY), 'Finalización de contrato sin renovación', 'exhumation_agreements/acuerdo2.pdf', 2, 2, 'Aprobada, pendiente de realizar', NOW(), NOW()),

-- Exhumación rechazada (intento en personaje histórico)
(10, '8901234567892', DATE_SUB(NOW(), INTERVAL 1 MONTH), NULL, 'Solicitud de familiar', 'exhumation_agreements/acuerdo3.pdf', 3, 1, 'Rechazada por tratarse de personaje histórico', NOW(), NOW()),

-- Exhumación completada
(4, '2345678901236', DATE_SUB(NOW(), INTERVAL 6 MONTH), DATE_SUB(NOW(), INTERVAL 5 MONTH), 'Traslado internacional', 'exhumation_agreements/acuerdo4.pdf', 4, 2, 'Proceso completado con éxito', NOW(), NOW());

-- Notificaciones
INSERT INTO notifications (contract_id, sent_at, message, is_sent, read_at, created_at, updated_at) VALUES 
-- Notificaciones para contratos a vencer
(1, DATE_SUB(NOW(), INTERVAL 30 DAY), 'Su contrato vence en 45 días. Por favor, considere la renovación.', 1, NULL, NOW(), NOW()),
(1, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Recordatorio: Su contrato vence en 30 días.', 1, DATE_SUB(NOW(), INTERVAL 14 DAY), NOW(), NOW()),
(2, DATE_SUB(NOW(), INTERVAL 30 DAY), 'Su contrato vence en 55 días. Por favor, considere la renovación.', 1, DATE_SUB(NOW(), INTERVAL 29 DAY), NOW(), NOW()),

-- Notificaciones para contratos en gracia
(3, DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Su contrato ha vencido. Está en periodo de gracia por 1 año.', 1, DATE_SUB(NOW(), INTERVAL 2 MONTH), NOW(), NOW()),
(4, DATE_SUB(NOW(), INTERVAL 3 MONTH), 'Su contrato ha vencido. Está en periodo de gracia por 1 año.', 1, NULL, NOW(), NOW()),

-- Notificaciones para exhumaciones
(9, DATE_SUB(NOW(), INTERVAL 10 DAY), 'Su solicitud de exhumación ha sido recibida y está siendo revisada.', 1, DATE_SUB(NOW(), INTERVAL 9 DAY), NOW(), NOW()),
(8, DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Su solicitud de exhumación ha sido aprobada.', 1, DATE_SUB(NOW(), INTERVAL 25 DAY), NOW(), NOW()),
(10, DATE_SUB(NOW(), INTERVAL 3 WEEK), 'Su solicitud de exhumación ha sido rechazada por tratarse de un personaje histórico.', 1, NULL, NOW(), NOW()),

-- Notificaciones de pagos
(3, DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Recordatorio de pago pendiente para renovación de contrato.', 1, NULL, NOW(), NOW()),
(4, DATE_SUB(NOW(), INTERVAL 2 MONTH), 'Recordatorio de pago pendiente para renovación de contrato.', 1, DATE_SUB(NOW(), INTERVAL 1 MONTH), NOW(), NOW());

-- Registros de cambios (auditoría)
INSERT INTO change_logs (table_name, record_id, changed_field, old_value, new_value, changed_at, user_id, created_at, updated_at) VALUES
-- Cambios en contratos
('contracts', '1', 'creación', 'Ninguno', 'Nuevo contrato creado', DATE_SUB(NOW(), INTERVAL 6 YEAR), 1, NOW(), NOW()),
('contracts', '2', 'creación', 'Ninguno', 'Nuevo contrato creado', DATE_SUB(NOW(), INTERVAL 6 YEAR), 2, NOW(), NOW()),

-- Cambios en estado de contratos
('contracts', '3', 'estado_del_contrato', '1', '3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1, NOW(), NOW()),
('contracts', '4', 'estado_del_contrato', '1', '3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 2, NOW(), NOW()),

-- Cambios en exhumaciones
('exhumations', '2', 'estado_de_exhumación', '1', '2', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, NOW(), NOW()),
('exhumations', '3', 'estado_de_exhumación', '1', '3', DATE_SUB(NOW(), INTERVAL 3 WEEK), 1, NOW(), NOW()),
('exhumations', '4', 'estado_de_exhumación', '2', '4', DATE_SUB(NOW(), INTERVAL 5 MONTH), 2, NOW(), NOW()),

-- Cambios en pagos
('payments', '2', 'creación', 'Ninguno', 'Nuevo pago registrado por monto: Q600.00', DATE_SUB(NOW(), INTERVAL 15 DAY), 1, NOW(), NOW()),
('payments', '4', 'creación', 'Ninguno', 'Nuevo pago registrado por monto: Q600.00', DATE_SUB(NOW(), INTERVAL 20 DAY), 2, NOW(), NOW()),
('payments', '1', 'estado_de_pago', '2', '1', DATE_SUB(NOW(), INTERVAL 6 YEAR), 1, NOW(), NOW()),

-- Cambios en nichos
('niches', '16', 'estado_del_nicho', '2', '3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, NOW(), NOW()),
('niches', '17', 'creación', 'Ninguno', 'Nuevo nicho registrado para personaje histórico', DATE_SUB(NOW(), INTERVAL 30 YEAR), 1, NOW(), NOW()),

-- Cambios en personajes históricos
('historical_figures', '1', 'creación', 'Ninguno', 'Nuevo personaje histórico registrado', DATE_SUB(NOW(), INTERVAL 50 YEAR), 1, NOW(), NOW()),
('historical_figures', '2', 'creación', 'Ninguno', 'Nuevo personaje histórico registrado', DATE_SUB(NOW(), INTERVAL 5 YEAR), 1, NOW(), NOW()),
('historical_figures', '3', 'creación', 'Ninguno', 'Nuevo personaje histórico registrado', DATE_SUB(NOW(), INTERVAL 30 YEAR), 1, NOW(), NOW());

-- 1. Creación de 3 nuevos nichos
INSERT INTO niches (code, street_id, avenue_id, location_reference, niche_type_id, niche_status_id, historical_figure_id, created_at, updated_at) VALUES 
('A-012', 4, 3, 'Tercera fila', 1, 2, NULL, NOW(), NOW()),
('A-013', 4, 3, 'Tercera fila', 1, 2, NULL, NOW(), NOW()),
('A-014', 5, 4, 'Cuarta fila', 1, 2, NULL, NOW(), NOW());

-- 2. Creación de 3 contratos asociados al usuario consulta (CUI: 4567890123456)
INSERT INTO contracts (niche_id, deceased_id, responsible_cui, start_date, end_date, grace_date, contract_status_id, notes, created_at, updated_at) VALUES 
-- Contrato vigente reciente
(20, 8, '4567890123456', DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_ADD(NOW(), INTERVAL 71 MONTH), DATE_ADD(NOW(), INTERVAL 83 MONTH), 1, 'Contrato del usuario de consulta - vigente', NOW(), NOW()),
-- Contrato próximo a vencer
(21, 9, '4567890123456', DATE_SUB(NOW(), INTERVAL 71 MONTH), DATE_ADD(NOW(), INTERVAL 1 MONTH), DATE_ADD(NOW(), INTERVAL 13 MONTH), 1, 'Contrato del usuario de consulta - próximo a vencer', NOW(), NOW()),
-- Contrato en gracia
(22, 10, '4567890123456', DATE_SUB(NOW(), INTERVAL 73 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_ADD(NOW(), INTERVAL 11 MONTH), 3, 'Contrato del usuario de consulta - en período de gracia', NOW(), NOW());

-- 3. Creación de pagos para estos contratos
INSERT INTO payments (contract_id, receipt_number, amount, issue_date, payment_date, payment_status_id, receipt_file_path, notes, user_id, created_at, updated_at) VALUES 
-- Pago del primer contrato (vigente reciente)
(13, 'REC-000017', 600.00, DATE_SUB(NOW(), INTERVAL 1 MONTH), DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, NULL, 'Pago inicial de contrato del usuario de consulta', 1, NOW(), NOW()),
-- Pago del segundo contrato (próximo a vencer)
(14, 'REC-000018', 600.00, DATE_SUB(NOW(), INTERVAL 71 MONTH), DATE_SUB(NOW(), INTERVAL 71 MONTH), 1, NULL, 'Pago inicial de contrato del usuario de consulta', 1, NOW(), NOW()),
-- Pago pendiente para renovación del segundo contrato
(14, 'REC-000019', 600.00, DATE_SUB(NOW(), INTERVAL 10 DAY), NULL, 2, NULL, 'Pago pendiente para renovación', 1, NOW(), NOW()),
-- Pago del tercer contrato (en período de gracia)
(15, 'REC-000020', 600.00, DATE_SUB(NOW(), INTERVAL 73 MONTH), DATE_SUB(NOW(), INTERVAL 73 MONTH), 1, NULL, 'Pago inicial de contrato del usuario de consulta', 1, NOW(), NOW()),
-- Pago pendiente para renovación del tercer contrato
(15, 'REC-000021', 600.00, DATE_SUB(NOW(), INTERVAL 1 MONTH), NULL, 2, NULL, 'Pago pendiente para renovación', 1, NOW(), NOW());

-- 4. Crear notificaciones para estos contratos
INSERT INTO notifications (contract_id, sent_at, message, is_sent, read_at, created_at, updated_at) VALUES 
-- Notificación para contrato próximo a vencer
(14, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Su contrato vence en aproximadamente 1 mes. Por favor, considere la renovación.', 1, NULL, NOW(), NOW()),
-- Notificación para contrato en gracia
(15, DATE_SUB(NOW(), INTERVAL 1 MONTH), 'Su contrato ha vencido. Está en período de gracia por 1 año.', 1, NULL, NOW(), NOW());