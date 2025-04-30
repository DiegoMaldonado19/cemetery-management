USE cemetery_management;

-- 1. Cantidad total de nichos ocupados y disponibles
SELECT
    SUM(CASE WHEN ns.name = 'Disponible' THEN 1 ELSE 0 END) AS nichos_disponibles,
    SUM(CASE WHEN ns.name = 'Ocupado' THEN 1 ELSE 0 END) AS nichos_ocupados,
    SUM(CASE WHEN ns.name = 'Proceso de Exhumación' THEN 1 ELSE 0 END) AS nichos_en_exhumacion,
    COUNT(*) AS total_nichos,
    SUM(CASE WHEN n.historical_figure_id IS NOT NULL THEN 1 ELSE 0 END) AS nichos_personajes_historicos
FROM niches n
JOIN niche_statuses ns ON n.niche_status_id = ns.id;

-- 2. Número de exhumaciones realizadas en un período determinado (con fechas específicas)
SELECT
    COUNT(*) AS total_exhumaciones,
    SUM(CASE WHEN MONTH(exhumation_date) = 1 THEN 1 ELSE 0 END) AS enero,
    SUM(CASE WHEN MONTH(exhumation_date) = 2 THEN 1 ELSE 0 END) AS febrero,
    SUM(CASE WHEN MONTH(exhumation_date) = 3 THEN 1 ELSE 0 END) AS marzo,
    SUM(CASE WHEN MONTH(exhumation_date) = 4 THEN 1 ELSE 0 END) AS abril,
    SUM(CASE WHEN MONTH(exhumation_date) = 5 THEN 1 ELSE 0 END) AS mayo,
    SUM(CASE WHEN MONTH(exhumation_date) = 6 THEN 1 ELSE 0 END) AS junio,
    SUM(CASE WHEN MONTH(exhumation_date) = 7 THEN 1 ELSE 0 END) AS julio,
    SUM(CASE WHEN MONTH(exhumation_date) = 8 THEN 1 ELSE 0 END) AS agosto,
    SUM(CASE WHEN MONTH(exhumation_date) = 9 THEN 1 ELSE 0 END) AS septiembre,
    SUM(CASE WHEN MONTH(exhumation_date) = 10 THEN 1 ELSE 0 END) AS octubre,
    SUM(CASE WHEN MONTH(exhumation_date) = 11 THEN 1 ELSE 0 END) AS noviembre,
    SUM(CASE WHEN MONTH(exhumation_date) = 12 THEN 1 ELSE 0 END) AS diciembre
FROM exhumations
WHERE exhumation_date IS NOT NULL
AND exhumation_date BETWEEN '2024-01-01' AND '2024-12-31';  -- Fechas específicas en lugar de parámetros

-- 3. Nichos cuya fecha de contrato está próxima a vencer (próximos 90 días)
SELECT
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    nt.name AS tipo_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    pd.cui AS cui_difunto,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_responsable,
    COALESCE(pr.phone, 'No disponible') AS telefono_responsable,
    COALESCE(pr.email, 'No disponible') AS correo_responsable,
    c.start_date AS fecha_inicio,
    c.end_date AS fecha_fin,
    DATEDIFF(c.end_date, CURDATE()) AS dias_para_vencer,
    CASE WHEN n.historical_figure_id IS NOT NULL THEN 'Sí' ELSE 'No' END AS es_personaje_historico
FROM contracts c
JOIN niches n ON c.niche_id = n.id
JOIN niche_types nt ON n.niche_type_id = nt.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN people pr ON c.responsible_cui = pr.cui
JOIN contract_statuses ct ON c.contract_status_id = ct.id
WHERE ct.name = 'Vigente'
AND DATEDIFF(c.end_date, CURDATE()) BETWEEN 0 AND 90
ORDER BY dias_para_vencer ASC;

-- 4. Distribución de ocupantes por género y edad estimada
SELECT
    g.name AS genero,
    CASE
        WHEN nt.name = 'Niño' THEN 'Menor de edad'
        ELSE 'Adulto'
    END AS categoria_edad,
    COUNT(*) AS cantidad,
    ROUND(COUNT(*) * 100.0 / (
        SELECT COUNT(*) FROM deceased
        WHERE cui IS NOT NULL
    ), 2) AS porcentaje
FROM deceased d
JOIN people p ON d.cui = p.cui
LEFT JOIN genders g ON p.gender_id = g.id
JOIN contracts c ON d.id = c.deceased_id
JOIN niches n ON c.niche_id = n.id
JOIN niche_types nt ON n.niche_type_id = nt.id
GROUP BY g.id, g.name, categoria_edad
ORDER BY g.name, categoria_edad;

-- 5. Cantidad de contratos vigentes y vencidos
SELECT
    cs.name AS estado_contrato,
    COUNT(*) AS cantidad,
    ROUND(COUNT(*) * 100.0 / (
        SELECT COUNT(*) FROM contracts
        WHERE contract_status_id IS NOT NULL
    ), 2) AS porcentaje
FROM contracts c
JOIN contract_statuses cs ON c.contract_status_id = cs.id
GROUP BY cs.id, cs.name
ORDER BY cantidad DESC;

-- 6. Nichos con pagos pendientes
SELECT
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_responsable,
    COALESCE(pr.phone, 'No disponible') AS telefono_responsable,
    COALESCE(pr.email, 'No disponible') AS correo_responsable,
    p.receipt_number AS numero_boleta,
    p.amount AS monto,
    p.issue_date AS fecha_emision,
    DATEDIFF(CURDATE(), p.issue_date) AS dias_pendientes,
    CASE WHEN n.historical_figure_id IS NOT NULL THEN 'Sí' ELSE 'No' END AS es_personaje_historico
FROM payments p
JOIN contracts c ON p.contract_id = c.id
JOIN niches n ON c.niche_id = n.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN people pr ON c.responsible_cui = pr.cui
JOIN payment_statuses ps ON p.payment_status_id = ps.id
WHERE ps.name = 'No Pagado'
ORDER BY dias_pendientes DESC;

-- 7. Registro de exhumaciones con detalles de cada caso
SELECT
    e.id AS id_exhumacion,
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    d.death_date AS fecha_fallecimiento,
    dc.name AS causa_muerte,
    e.request_date AS fecha_solicitud,
    e.exhumation_date AS fecha_exhumacion,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_solicitante,
    pr.cui AS cui_solicitante,
    COALESCE(pr.phone, 'No disponible') AS telefono_solicitante,
    e.reason AS motivo,
    es.name AS estado_exhumacion,
    u.name AS registrado_por,
    CASE WHEN n.historical_figure_id IS NOT NULL THEN 'Sí - No se puede exhumar' ELSE 'No' END AS es_personaje_historico
FROM exhumations e
JOIN contracts c ON e.contract_id = c.id
JOIN niches n ON c.niche_id = n.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN death_causes dc ON d.death_cause_id = dc.id
JOIN people pr ON e.requester_cui = pr.cui
JOIN exhumation_statuses es ON e.exhumation_status_id = es.id
JOIN users u ON e.user_id = u.id
ORDER BY e.request_date DESC;

-- 8. Ingresos mensuales por pagos (año actual)
SELECT
    MONTH(payment_date) AS mes,
    COUNT(*) AS cantidad_pagos,
    COALESCE(SUM(amount), 0) AS ingresos_totales
FROM payments
JOIN payment_statuses ps ON payments.payment_status_id = ps.id
WHERE ps.name = 'Pagado'
AND payment_date IS NOT NULL
AND YEAR(payment_date) = YEAR(CURDATE())
GROUP BY MONTH(payment_date)
ORDER BY mes;

-- 9. Estadísticas de uso de nichos por tipo
SELECT
    nt.name AS tipo_nicho,
    COUNT(*) AS total_nichos,
    SUM(CASE WHEN ns.name = 'Disponible' THEN 1 ELSE 0 END) AS disponibles,
    SUM(CASE WHEN ns.name = 'Ocupado' THEN 1 ELSE 0 END) AS ocupados,
    SUM(CASE WHEN ns.name = 'Proceso de Exhumación' THEN 1 ELSE 0 END) AS en_exhumacion,
    SUM(CASE WHEN n.historical_figure_id IS NOT NULL THEN 1 ELSE 0 END) AS personajes_historicos,
    ROUND(SUM(CASE WHEN ns.name = 'Ocupado' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 2) AS porcentaje_ocupacion
FROM niches n
JOIN niche_types nt ON n.niche_type_id = nt.id
JOIN niche_statuses ns ON n.niche_status_id = ns.id
GROUP BY nt.id, nt.name
ORDER BY total_nichos DESC;

-- 10. Listado de personajes históricos enterrados
SELECT
    hf.id,
    CASE
        WHEN hf.cui IS NOT NULL THEN CONCAT(p.first_name, ' ', p.last_name)
        ELSE CONCAT(COALESCE(hf.historical_first_name, ''), ' ', COALESCE(hf.historical_last_name, ''))
    END AS nombre_personaje_historico,
    hf.cui,
    hf.historical_reason AS motivo_historico,
    hf.declaration_date AS fecha_declaracion,
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    nt.name AS tipo_nicho
FROM historical_figures hf
LEFT JOIN people p ON hf.cui = p.cui
JOIN niches n ON n.historical_figure_id = hf.id
JOIN niche_types nt ON n.niche_type_id = nt.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
ORDER BY hf.declaration_date;

-- 11. Listado de usuarios con actividad reciente
SELECT
    u.id,
    u.name AS nombre_usuario,
    CONCAT(p.first_name, ' ', p.last_name) AS nombre_completo,
    r.name AS rol,
    u.last_login_at AS ultimo_acceso,
    DATEDIFF(CURDATE(), COALESCE(u.last_login_at, CURDATE())) AS dias_desde_ultimo_acceso
FROM users u
JOIN people p ON u.cui = p.cui
JOIN roles r ON u.role_id = r.id
WHERE u.last_login_at IS NOT NULL
ORDER BY u.last_login_at DESC
LIMIT 20;

-- 12. Contratos con período de gracia activo
SELECT
    c.id,
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_responsable,
    COALESCE(pr.phone, 'No disponible') AS telefono_responsable,
    COALESCE(pr.email, 'No disponible') AS correo_responsable,
    c.end_date AS fecha_vencimiento,
    c.grace_date AS fecha_fin_gracia,
    DATEDIFF(c.grace_date, CURDATE()) AS dias_restantes_gracia,
    CASE WHEN n.historical_figure_id IS NOT NULL THEN 'Sí' ELSE 'No' END AS es_personaje_historico
FROM contracts c
JOIN niches n ON c.niche_id = n.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN people pr ON c.responsible_cui = pr.cui
JOIN contract_statuses ct ON c.contract_status_id = ct.id
WHERE ct.name = 'En Gracia'
ORDER BY dias_restantes_gracia ASC;

-- 13. Histórico de cambios en contratos (últimos 30 días)
SELECT
    cl.changed_at AS fecha_cambio,
    cl.table_name AS tabla,
    cl.record_id AS id_registro,
    cl.changed_field AS campo_modificado,
    COALESCE(cl.old_value, 'Sin valor previo') AS valor_anterior,
    COALESCE(cl.new_value, 'Sin valor nuevo') AS valor_nuevo,
    CASE
        WHEN cl.user_id IS NOT NULL AND u.id IS NOT NULL THEN CONCAT(p.first_name, ' ', p.last_name)
        ELSE 'Sistema'
    END AS modificado_por
FROM change_logs cl
LEFT JOIN users u ON cl.user_id = u.id
LEFT JOIN people p ON u.cui = p.cui
WHERE cl.table_name = 'contracts'
AND cl.changed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY cl.changed_at DESC;

-- 24. Reporte mensual de ocupación y financiero (sin usar vista)
SELECT
    YEAR(CURDATE()) AS año,
    MONTH(CURDATE()) AS mes,
    -- Estadísticas de nichos
    (SELECT COUNT(*) FROM niches) AS total_nichos,
    (SELECT COUNT(*) FROM niches n JOIN niche_statuses ns ON n.niche_status_id = ns.id WHERE ns.name = 'Disponible') AS nichos_disponibles,
    (SELECT COUNT(*) FROM niches n JOIN niche_statuses ns ON n.niche_status_id = ns.id WHERE ns.name = 'Ocupado') AS nichos_ocupados,
    -- Estadísticas de contratos
    (SELECT COUNT(*) FROM contracts c JOIN contract_statuses cs ON c.contract_status_id = cs.id WHERE cs.name = 'Vigente') AS contratos_vigentes,
    (SELECT COUNT(*) FROM contracts c JOIN contract_statuses cs ON c.contract_status_id = cs.id WHERE cs.name = 'En Gracia') AS contratos_en_gracia,
    (SELECT COUNT(*) FROM contracts c JOIN contract_statuses cs ON c.contract_status_id = cs.id WHERE cs.name = 'Vencido') AS contratos_vencidos,
    (SELECT COUNT(*) FROM contracts WHERE MONTH(start_date) = MONTH(CURDATE()) AND YEAR(start_date) = YEAR(CURDATE())) AS nuevos_contratos_mes,
    -- Estadísticas financieras
    (SELECT COUNT(*) FROM payments p JOIN payment_statuses ps ON p.payment_status_id = ps.id
     WHERE ps.name = 'Pagado' AND MONTH(p.payment_date) = MONTH(CURDATE()) AND YEAR(p.payment_date) = YEAR(CURDATE())) AS pagos_recibidos_mes,
    (SELECT COALESCE(SUM(p.amount), 0) FROM payments p JOIN payment_statuses ps ON p.payment_status_id = ps.id
     WHERE ps.name = 'Pagado' AND MONTH(p.payment_date) = MONTH(CURDATE()) AND YEAR(p.payment_date) = YEAR(CURDATE())) AS ingresos_mes,
    (SELECT COUNT(*) FROM payments p JOIN payment_statuses ps ON p.payment_status_id = ps.id WHERE ps.name = 'No Pagado') AS pagos_pendientes,
    -- Estadísticas de exhumaciones
    (SELECT COUNT(*) FROM exhumations WHERE MONTH(request_date) = MONTH(CURDATE()) AND YEAR(request_date) = YEAR(CURDATE())) AS solicitudes_exhumacion_mes,
    (SELECT COUNT(*) FROM exhumations e JOIN exhumation_statuses es ON e.exhumation_status_id = es.id
     WHERE es.name = 'Aprobada' AND MONTH(e.request_date) = MONTH(CURDATE()) AND YEAR(e.request_date) = YEAR(CURDATE())) AS exhumaciones_aprobadas_mes,
    (SELECT COUNT(*) FROM exhumations e JOIN exhumation_statuses es ON e.exhumation_status_id = es.id
     WHERE es.name = 'Completada' AND MONTH(e.exhumation_date) = MONTH(CURDATE()) AND YEAR(e.exhumation_date) = YEAR(CURDATE())) AS exhumaciones_realizadas_mes;

-- 25. Búsqueda de difuntos por nombre (parámetro definido)
SELECT
    d.id AS id_difunto,
    p.cui,
    p.first_name AS nombre,
    p.last_name AS apellidos,
    g.name AS genero,
    d.death_date AS fecha_fallecimiento,
    dc.name AS causa_muerte,
    d.origin AS origen,
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name, ', Calle ', st.street_number, ', Avenida ', av.avenue_number) AS ubicacion_completa,
    CONCAT(pr.first_name, ' ', pr.last_name) AS responsable,
    COALESCE(pr.phone, 'No disponible') AS telefono_responsable,
    c.start_date AS fecha_inicio_contrato,
    c.end_date AS fecha_fin_contrato,
    cst.name AS estado_contrato
FROM deceased d
JOIN people p ON d.cui = p.cui
LEFT JOIN genders g ON p.gender_id = g.id
JOIN death_causes dc ON d.death_cause_id = dc.id
JOIN contracts c ON d.id = c.deceased_id
JOIN contract_statuses cst ON c.contract_status_id = cst.id
JOIN niches n ON c.niche_id = n.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN people pr ON c.responsible_cui = pr.cui
WHERE
    p.first_name LIKE CONCAT('%', 'Juan', '%') OR  -- Ejemplo de búsqueda por "Juan"
    p.last_name LIKE CONCAT('%', 'Juan', '%') OR
    CONCAT(p.first_name, ' ', p.last_name) LIKE CONCAT('%', 'Juan', '%')
ORDER BY p.last_name, p.first_name;

-- 14. Listado de nichos de personajes históricos
SELECT
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    nt.name AS tipo_nicho,
    CASE
        WHEN hf.cui IS NOT NULL THEN CONCAT(p.first_name, ' ', p.last_name)
        ELSE CONCAT(COALESCE(hf.historical_first_name, ''), ' ', COALESCE(hf.historical_last_name, ''))
    END AS nombre_personaje_historico,
    hf.historical_reason AS motivo_historico,
    hf.declaration_date AS fecha_declaracion,
    ns.name AS estado_nicho
FROM niches n
JOIN historical_figures hf ON n.historical_figure_id = hf.id
LEFT JOIN people p ON hf.cui = p.cui
JOIN niche_types nt ON n.niche_type_id = nt.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN niche_statuses ns ON n.niche_status_id = ns.id
ORDER BY n.code;

-- 15. Exhumaciones rechazadas y sus motivos
SELECT
    e.id,
    n.code AS codigo_nicho,
    CONCAT(cs.name, ' - ', cb.name) AS ubicacion_cementerio,
    CONCAT('Calle ', st.street_number, ', Avenida ', av.avenue_number) AS direccion_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    e.request_date AS fecha_solicitud,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_solicitante,
    COALESCE(pr.phone, 'No disponible') AS telefono_solicitante,
    e.reason AS motivo_solicitud,
    CASE
        WHEN n.historical_figure_id IS NOT NULL THEN 'Personaje histórico - No se puede exhumar'
        ELSE 'Otro motivo'
    END AS motivo_rechazo
FROM exhumations e
JOIN contracts c ON e.contract_id = c.id
JOIN niches n ON c.niche_id = n.id
JOIN cemetery_streets st ON n.street_id = st.id
JOIN cemetery_avenues av ON n.avenue_id = av.id
JOIN cemetery_blocks cb ON st.block_id = cb.id
JOIN cemetery_sections cs ON cb.section_id = cs.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN people pr ON e.requester_cui = pr.cui
JOIN exhumation_statuses es ON e.exhumation_status_id = es.id
WHERE es.name = 'Rechazada'
ORDER BY e.request_date DESC;

-- 16. Estadísticas de ocupación por sección del cementerio
SELECT
    cs.name AS nombre_seccion,
    COUNT(n.id) AS total_nichos,
    SUM(CASE WHEN ns.name = 'Disponible' THEN 1 ELSE 0 END) AS nichos_disponibles,
    SUM(CASE WHEN ns.name = 'Ocupado' THEN 1 ELSE 0 END) AS nichos_ocupados,
    ROUND(SUM(CASE WHEN ns.name = 'Ocupado' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(n.id), 0), 2) AS porcentaje_ocupacion,
    SUM(CASE WHEN n.historical_figure_id IS NOT NULL THEN 1 ELSE 0 END) AS nichos_personajes_historicos
FROM cemetery_sections cs
JOIN cemetery_blocks cb ON cs.id = cb.section_id
JOIN cemetery_streets st ON cb.id = st.block_id
JOIN cemetery_avenues av ON cb.id = av.block_id
JOIN niches n ON st.id = n.street_id AND av.id = n.avenue_id
JOIN niche_statuses ns ON n.niche_status_id = ns.id
GROUP BY cs.id, cs.name
ORDER BY porcentaje_ocupacion DESC;

-- 17. Consulta para la pantalla de dashboard - Resumen general (sin subconsultas anidadas)
SELECT
    (SELECT COUNT(*) FROM niches) AS total_nichos,
    COUNT(CASE WHEN ns.name = 'Disponible' THEN 1 END) AS nichos_disponibles,
    COUNT(CASE WHEN ns.name = 'Ocupado' THEN 1 END) AS nichos_ocupados,
    (SELECT COUNT(*) FROM contracts c JOIN contract_statuses cs ON c.contract_status_id = cs.id WHERE cs.name = 'Vigente') AS contratos_activos,
    (SELECT COUNT(*) FROM contracts c JOIN contract_statuses cs ON c.contract_status_id = cs.id WHERE cs.name = 'En Gracia') AS contratos_en_gracia,
    (SELECT COUNT(*) FROM contracts c JOIN contract_statuses cs ON c.contract_status_id = cs.id
     WHERE cs.name = 'Vigente' AND DATEDIFF(c.end_date, CURDATE()) BETWEEN 0 AND 30) AS contratos_vencen_30_dias,
    (SELECT COUNT(*) FROM payments p JOIN payment_statuses ps ON p.payment_status_id = ps.id WHERE ps.name = 'No Pagado') AS pagos_pendientes,
    (SELECT COALESCE(SUM(p.amount), 0) FROM payments p JOIN payment_statuses ps ON p.payment_status_id = ps.id
     WHERE ps.name = 'Pagado' AND p.payment_date IS NOT NULL AND YEAR(p.payment_date) = YEAR(CURDATE())) AS ingresos_año_actual,
    (SELECT COUNT(*) FROM exhumations e JOIN exhumation_statuses es ON e.exhumation_status_id = es.id
     WHERE es.name = 'Solicitada' AND e.request_date IS NOT NULL AND e.request_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS solicitudes_exhumacion_recientes
FROM niches n
JOIN niche_statuses ns ON n.niche_status_id = ns.id;

-- 18. Consulta para facturación y pagos mensuales
SELECT
    YEAR(p.payment_date) AS año,
    MONTH(p.payment_date) AS mes,
    COUNT(*) AS total_pagos,
    COALESCE(SUM(p.amount), 0) AS monto_total,
    COALESCE(AVG(p.amount), 0) AS pago_promedio,
    COUNT(DISTINCT c.id) AS contratos_con_pagos
FROM payments p
JOIN contracts c ON p.contract_id = c.id
JOIN payment_statuses ps ON p.payment_status_id = ps.id
WHERE ps.name = 'Pagado'
AND p.payment_date IS NOT NULL
GROUP BY YEAR(p.payment_date), MONTH(p.payment_date)
ORDER BY año DESC, mes DESC
LIMIT 24;

-- 19. Consulta para información de notificaciones pendientes para contratos próximos a vencer (en lugar de vista)
SELECT
    c.id AS contract_id,
    n.code AS codigo_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_responsable,
    COALESCE(pr.email, 'No disponible') AS correo_responsable,
    COALESCE(pr.phone, 'No disponible') AS telefono_responsable,
    c.end_date AS fecha_vencimiento,
    c.grace_date AS fecha_fin_gracia,
    DATEDIFF(c.end_date, CURDATE()) AS dias_para_vencer,
    CASE
        WHEN DATEDIFF(c.end_date, CURDATE()) <= 15 THEN 'Urgente'
        WHEN DATEDIFF(c.end_date, CURDATE()) <= 30 THEN 'Alta'
        WHEN DATEDIFF(c.end_date, CURDATE()) <= 60 THEN 'Media'
        ELSE 'Baja'
    END AS prioridad
FROM contracts c
JOIN niches n ON c.niche_id = n.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN people pr ON c.responsible_cui = pr.cui
JOIN contract_statuses cs ON c.contract_status_id = cs.id
LEFT JOIN (
    SELECT contract_id
    FROM notifications
    WHERE sent_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
) nt ON c.id = nt.contract_id
WHERE cs.name = 'Vigente'
AND DATEDIFF(c.end_date, CURDATE()) BETWEEN 0 AND 90
AND nt.contract_id IS NULL;

-- 20. Consulta para obtener las estadísticas de mortalidad por causa
SELECT
    dc.name AS causa_muerte,
    COUNT(*) AS total_fallecidos,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM deceased), 2) AS porcentaje,
    SUM(CASE WHEN p.gender_id = (SELECT id FROM genders WHERE name = 'Masculino') THEN 1 ELSE 0 END) AS masculinos,
    SUM(CASE WHEN p.gender_id = (SELECT id FROM genders WHERE name = 'Femenino') THEN 1 ELSE 0 END) AS femeninos,
    SUM(CASE WHEN p.gender_id = (SELECT id FROM genders WHERE name = 'Otro') THEN 1 ELSE 0 END) AS otros,
    SUM(CASE WHEN nt.name = 'Niño' THEN 1 ELSE 0 END) AS menores,
    SUM(CASE WHEN nt.name = 'Adulto' THEN 1 ELSE 0 END) AS adultos,
    MIN(d.death_date) AS primera_fecha,
    MAX(d.death_date) AS ultima_fecha
FROM deceased d
JOIN people p ON d.cui = p.cui
JOIN death_causes dc ON d.death_cause_id = dc.id
JOIN contracts c ON d.id = c.deceased_id
JOIN niches n ON c.niche_id = n.id
JOIN niche_types nt ON n.niche_type_id = nt.id
GROUP BY dc.id, dc.name
ORDER BY total_fallecidos DESC;

-- 21. Análisis de causas de muerte por año
SELECT
    YEAR(d.death_date) AS año,
    dc.name AS causa_muerte,
    COUNT(*) AS total_fallecidos,
    ROUND(COUNT(*) * 100.0 / (
        SELECT COUNT(*)
        FROM deceased
        WHERE YEAR(death_date) = YEAR(d.death_date)
    ), 2) AS porcentaje_anual
FROM deceased d
JOIN death_causes dc ON d.death_cause_id = dc.id
GROUP BY YEAR(d.death_date), dc.id, dc.name
ORDER BY año DESC, total_fallecidos DESC;

-- 22. Pagos vencidos por más de 60 días
SELECT
    p.id AS id_pago,
    p.receipt_number AS numero_recibo,
    p.amount AS monto,
    p.issue_date AS fecha_emision,
    DATEDIFF(CURDATE(), p.issue_date) AS dias_vencidos,
    n.code AS codigo_nicho,
    CONCAT(pd.first_name, ' ', pd.last_name) AS nombre_difunto,
    CONCAT(pr.first_name, ' ', pr.last_name) AS nombre_responsable,
    COALESCE(pr.phone, 'No disponible') AS telefono_responsable,
    COALESCE(pr.email, 'No disponible') AS correo_responsable,
    c.id AS id_contrato,
    c.end_date AS fecha_fin_contrato,
    cs.name AS estado_contrato
FROM payments p
JOIN contracts c ON p.contract_id = c.id
JOIN niches n ON c.niche_id = n.id
JOIN deceased d ON c.deceased_id = d.id
JOIN people pd ON d.cui = pd.cui
JOIN people pr ON c.responsible_cui = pr.cui
JOIN payment_statuses ps ON p.payment_status_id = ps.id
JOIN contract_statuses cs ON c.contract_status_id = cs.id
WHERE ps.name = 'No Pagado'
AND DATEDIFF(CURDATE(), p.issue_date) > 60
ORDER BY dias_vencidos DESC;

-- 23. Histórico de cambios en nichos de personajes históricos
SELECT
    cl.changed_at AS fecha_cambio,
    n.code AS codigo_nicho,
    CASE
        WHEN hf.cui IS NOT NULL THEN CONCAT(p.first_name, ' ', p.last_name)
        ELSE CONCAT(COALESCE(hf.historical_first_name, ''), ' ', COALESCE(hf.historical_last_name, ''))
    END AS nombre_personaje_historico,
    cl.changed_field AS campo_modificado,
    COALESCE(cl.old_value, 'Sin valor previo') AS valor_anterior,
    COALESCE(cl.new_value, 'Sin valor nuevo') AS valor_nuevo,
    CASE
        WHEN cl.user_id IS NOT NULL AND u.id IS NOT NULL THEN CONCAT(up.first_name, ' ', up.last_name)
        ELSE 'Sistema'
    END AS modificado_por
FROM change_logs cl
JOIN niches n ON cl.table_name = 'niches' AND cl.record_id = n.id
JOIN historical_figures hf ON n.historical_figure_id = hf.id
LEFT JOIN people p ON hf.cui = p.cui
LEFT JOIN users u ON cl.user_id = u.id
LEFT JOIN people up ON u.cui = up.cui
ORDER BY cl.changed_at DESC;
