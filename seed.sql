-- ==============================================================================
-- SISTEMA DE TICKETS DE ATENCION ENLA 2026 - SEED DATA
-- ==============================================================================

-- 1. ROLES
INSERT INTO rol (cod, nombre, descripcion) VALUES
('SAS', 'Supervisor de Almacen y Soporte Informatico', 'Registra tickets de su sede jurisdiccional y reporta incidencias'),
('MI', 'Monitor Informatico', 'Gestiona, atiende y resuelve tickets de sus sedes asignadas'),
('EMI', 'Especialista de Monitoreo Informatico', 'Acceso global, administra catalogo de categorias/tipos y gestiona publicaciones'),
('CSMI', 'Coordinador de Sistemas y Monitoreo Informatico', 'Acceso global y seguimiento gerencial (Santiago)'),
('ECC', 'Especialista de Control de Calidad', 'Acceso global y seguimiento de resoluciones y calidad'),
('GUEST', 'Usuario Guest', 'Acceso global de consulta y reportes');

-- 2. SEDES REGIONALES
INSERT INTO sede_reg (cod, nombre) VALUES
('REG-LIM', 'LIMA METROPOLITANA'),
('REG-CAL', 'CALLAO'),
('REG-TRU', 'LA LIBERTAD - TRUJILLO'),
('REG-AQP', 'AREQUIPA'),
('REG-TUM', 'TUMBES'),
('REG-TAC', 'TACNA');

-- 3. SEDES JURISDICCIONALES
INSERT INTO sede_juris (id_sedereg, cod, nombre) VALUES
(1, 'JUR-LIM-1', 'LIMA MET 1'),
(1, 'JUR-LIM-2', 'LIMA MET 2'),
(1, 'JUR-LIM-3', 'LIMA MET 3'),
(2, 'JUR-CAL-1', 'CALLAO CENTRO'),
(2, 'JUR-CAL-2', 'VENTANILLA'),
(3, 'JUR-TRU-1', 'TRUJILLO NORTE'),
(3, 'JUR-TRU-2', 'TRUJILLO SUR'),
(4, 'JUR-AQP-1', 'AREQUIPA CERCADO'),
(5, 'JUR-TUM-1', 'TUMBES 1'),
(5, 'JUR-TUM-2', 'TUMBES 2'),
(6, 'JUR-TAC-1', 'TACNA 1'),
(6, 'JUR-TAC-2', 'TACNA 2');

-- 4. USUARIOS (Password de todos: password123)
-- Hash bcrypt: $2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC
INSERT INTO usuario (id_rol, cod_usuario, username, clave, nombres, ape_pat, ape_mat, id_sedereg, id_sedejuris, codigo_monitor, doc, email, estado) VALUES
-- Especialista de Monitoreo Informatico (ID: 1)
(3, 'EMI-001', 'especialista', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Ricardo', 'Palma', 'Mendoza', NULL, NULL, NULL, '45892011', 'especialista@enla2026.gob.pe', 1),
-- Coordinador de Sistemas y Monitoreo (Santiago) (ID: 2)
(4, 'CSMI-001', 'santiago', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Santiago', 'Alvarez', 'Vargas', NULL, NULL, NULL, '40129845', 'santiago@enla2026.gob.pe', 1),
-- Especialista Control de Calidad (ID: 3)
(5, 'ECC-001', 'calidad', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Elena', 'Torres', 'Castro', NULL, NULL, NULL, '42981734', 'calidad@enla2026.gob.pe', 1),
-- Usuario Guest (ID: 4)
(6, 'GUEST-001', 'guest', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Observador', 'Invitado', 'General', NULL, NULL, NULL, '00000000', 'guest@enla2026.gob.pe', 1),

-- Monitores Informaticos (MI1 a MI4) (IDs: 5, 6, 7, 8)
(2, 'MI-001', 'monitor1', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Carlos', 'Rojas', 'Salazar', 1, NULL, 'MI1', '46781290', 'monitor1@enla2026.gob.pe', 1),
(2, 'MI-002', 'monitor2', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Ana', 'Gutierrez', 'Paz', 2, NULL, 'MI2', '47891234', 'monitor2@enla2026.gob.pe', 1),
(2, 'MI-003', 'monitor3', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Jorge', 'Navarro', 'Ruiz', 3, NULL, 'MI3', '41238901', 'monitor3@enla2026.gob.pe', 1),
(2, 'MI-004', 'monitor4', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Patricia', 'Quispe', 'Luna', 4, NULL, 'MI4', '43890123', 'monitor4@enla2026.gob.pe', 1),

-- Usuarios SAS (Supervisores de Almacen y Soporte) (IDs: 9 a 14)
(1, 'SAS022', 'sas022', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Manuel', 'Villar', 'Soto', 2, 5, NULL, '71239845', 'sas022@enla2026.gob.pe', 1),
(1, 'SAS154', 'sas154', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Rosa', 'Sanchez', 'Perez', 1, 3, NULL, '72340912', 'sas154@enla2026.gob.pe', 1),
(1, 'SAS043', 'sas043', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Daniel', 'Caceres', 'Mora', 1, 1, NULL, '73451023', 'sas043@enla2026.gob.pe', 1),
(1, 'SAS044', 'sas044', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Lucia', 'Bravo', 'Campos', 5, 10, NULL, '74562134', 'sas044@enla2026.gob.pe', 1),
(1, 'SAS096', 'sas096', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Hugo', 'Paredes', 'Leiva', 6, 12, NULL, '75673245', 'sas096@enla2026.gob.pe', 1),
(1, 'SAS050', 'sas050', '$2y$12$o52efratgdZmL3PTdvP.mOqA0n3V25ZX0qEDcqIfbwlTWf53ZY7EC', 'Valeria', 'Flores', 'Vega', 3, 6, NULL, '76784356', 'sas050@enla2026.gob.pe', 1);

-- 5. ASIGNACIONES DE SEDES A MONITORES INFORMATICOS
-- Monitor 1 (MI1 - ID 5): Lima (Sedes 1, 2, 3)
INSERT INTO asignacion_monitor (id_usuario_monitor, id_sedereg, id_sedejuris) VALUES
(5, 1, 1),
(5, 1, 2),
(5, 1, 3);

-- Monitor 2 (MI2 - ID 6): Callao (Sedes 4, 5) y Tumbes (Sedes 9, 10)
INSERT INTO asignacion_monitor (id_usuario_monitor, id_sedereg, id_sedejuris) VALUES
(6, 2, 4),
(6, 2, 5),
(6, 5, 9),
(6, 5, 10);

-- Monitor 3 (MI3 - ID 7): La Libertad (Sedes 6, 7) y Tacna (Sedes 11, 12)
INSERT INTO asignacion_monitor (id_usuario_monitor, id_sedereg, id_sedejuris) VALUES
(7, 3, 6),
(7, 3, 7),
(7, 6, 11),
(7, 6, 12);

-- Monitor 4 (MI4 - ID 8): Arequipa (Sede 8)
INSERT INTO asignacion_monitor (id_usuario_monitor, id_sedereg, id_sedejuris) VALUES
(8, 4, 8);

-- 6. CATALOGO INICIAL DE CATEGORIAS Y TIPOS DE ATENCION (segun tabla del PDF)
INSERT INTO categoria_atencion (nombre, activo) VALUES
('Sistema Integrado', TRUE),
('APK Geolocalización', TRUE),
('APK Asistencia', TRUE),
('Equipo Informático', TRUE);

INSERT INTO tipo_atencion (id_categoria, nombre, activo) VALUES
-- Sistema Integrado (1)
(1, 'Aplicadores - Preselección', TRUE),
(1, 'Aplicadores - Capacitación', TRUE),
-- APK Geolocalizacion (2)
(2, 'APA', TRUE),
(2, 'Aplicadores', TRUE),
-- APK Asistencia (3)
(3, 'APA', TRUE),
(3, 'Aplicadores', TRUE),
-- Equipo Informatico (4)
(4, 'Laptop', TRUE),
(4, 'Impresora', TRUE),
(4, 'Lectora de código de barras', TRUE);

-- 7. TICKETS DE ATENCION DE PRUEBA
INSERT INTO ticket (cod_ticket, id_usuario_solicitante, id_sedejuris, id_monitor_responsable, id_categoria, id_tipo_atencion, prioridad, descripcion_problema, identificador_interno_mi, correlativo_mi, estado, fecha_emision, fecha_cierre, tiempo_resolucion, tiempo_resolucion_segundos, descripcion_resolucion, justificacion_no_procede) VALUES
('000001', 11, 1, 5, 1, 1, 'Alta', 'Error al sincronizar lista de preseleccion en Lima Met 1', 'MI1-001', 1, 'Cerrado', '2026-08-02 07:12:00', '2026-08-02 09:55:00', '02:43:00', 9780, 'Se reinicio el servicio de sincronizacion en base de datos central.', NULL),
('000002', 9, 5, 6, 2, 3, 'Media', 'Falla de conexion de geolocalizacion en Ventanilla', 'MI2-001', 1, 'Cerrado', '2026-08-02 09:45:00', '2026-08-02 15:12:00', '05:27:00', 19620, 'Se ajustaron permisos de ubicacion GPS y APN de datos.', NULL),
('000003', 10, 3, 5, 4, 8, 'Alta', 'Impresora no reconoce consumible en Lima Met 3', 'MI1-002', 2, 'Cerrado', '2026-08-02 14:32:00', '2026-08-04 09:13:00', '42:41:00', 153660, 'Se reemplazo cartucho de toner por repuesto en stock.', NULL),
('000004', 14, 6, 7, 3, 5, 'Media', 'APK de Asistencia arroja error de validacion de horario', 'MI3-001', 1, 'Cerrado', '2026-08-02 10:47:00', '2026-08-05 19:03:00', '80:16:00', 288960, 'Se actualizo rango de tolerancia de horario en el servidor.', NULL),
('000005', 11, 1, 5, 4, 7, 'Alta', 'Laptop principal no enciende tras corte de energia', 'MI1-003', 3, 'Abierto', '2026-08-16 08:30:00', NULL, NULL, NULL, NULL, NULL),
('000006', 12, 10, 6, 2, 4, 'Baja', 'Duda sobre exportacion de coordenadas en Tumbes 2', 'MI2-002', 2, 'No procede', '2026-08-15 11:00:00', '2026-08-15 11:30:00', '00:30:00', 1800, NULL, 'La consulta corresponde a capacitacion funcional, no a incidencia tecnica.');

-- 8. AVISOS OPERATIVOS E INCIDENCIAS INFORMATIVAS DE PRUEBA
INSERT INTO incidencia_informativa (id_usuario_registro, id_sedejuris, tipo_incidencia, subtipo_corte, fecha_inicio, fecha_reanudacion, tiempo_interrupcion_minutos, actividades_afectadas, observaciones) VALUES
(11, 1, 'corte_energia', 'accidental', '2026-08-10 14:00:00', '2026-08-10 16:30:00', 150, 'Atencion en ventanilla y registro de postulantes', 'Corte general en la manzana por mantenimiento de red publica'),
(9, 5, 'problema_conectividad', 'programado', '2026-08-12 08:00:00', '2026-08-12 10:00:00', 120, 'Transmision de datos de evaluacion', 'Mantenimiento preventivo de enlace de fibra optica');

INSERT INTO incidencia_informativa (id_usuario_registro, id_sedejuris, tipo_incidencia, marca_modelo, version_android, aplicativo_afectado, proceso_relacionado, descripcion_problema, descartes_sas, resultado_pruebas, funciona_en_equipo, observaciones) VALUES
(11, 1, 'aplicativo_movil', 'Samsung Galaxy A03', 'Android 11 Go Edition', 'APK Geolocalización v3.0', 'Toma de punto GPS en campo', 'La app se cierra inesperadamente al iniciar el servicio de camara', 'Se reinstalo APK, se limpiaron datos y cache', 'Fallo persistente por memoria RAM insuficiente (2GB)', FALSE, 'Se determina que el equipo no cumple con requerimientos minimos para el APK.');

-- 9. PUBLICACIONES DE APLICATIVOS Y CREDENCIALES
INSERT INTO publicacion (id_usuario_autor, nombre_aplicativo, version, fecha_publicacion, indicaciones, enlaces_generales, estado) VALUES
(1, 'APK Geolocalización', 'Versión 3.0', '2026-08-01 08:00:00', 'Actualizacion critica para el proceso de preseleccion. Todos los supervisores SAS deben verificar credenciales y descargar la ultima version.', 'https://drive.google.com/drive/folders/apk-geoloc-v3', 'Activa'),
(1, 'APK Asistencia', 'Versión 2.1', '2026-08-05 09:00:00', 'Modulo para marcacion de asistencia de personal aplicador y coordinadores.', 'https://drive.google.com/drive/folders/apk-asistencia-v2', 'Activa'),
(1, 'Manual de Operaciones Sistema Integrado', 'Versión 1.0', '2026-07-20 10:00:00', 'Guia metodologica y tecnica para atencion en sedes jurisdiccionales.', 'https://drive.google.com/drive/folders/manual-ops-2026', 'Desactivada');

-- 10. CREDENCIALES PERSONALIZADAS POR USUARIO SAS (DATOS DE EJEMPLO DE LA PAGINA 12 DEL PDF)
INSERT INTO publicacion_credencial (id_publicacion, id_usuario_sas, cod_sas, datos_personalizados) VALUES
(1, 11, 'SAS043', '[{"label": "Administrador Clave", "value": "3345"}, {"label": "Administrador Token", "value": "4456"}, {"label": "Enlace marco", "value": "https://drive.google.com/xxxxx"}]'),
(1, 12, 'SAS044', '[{"label": "Administrador Clave", "value": "1234"}, {"label": "Administrador Clave", "value": "8846"}, {"label": "Enlace marco", "value": "https://drive.google.com/yyyyy"}]'),
(1, 9, 'SAS022', '[{"label": "Administrador Clave", "value": "5512"}, {"label": "Administrador Token", "value": "9981"}, {"label": "Enlace marco", "value": "https://drive.google.com/zzzzz"}]'),
(1, 10, 'SAS154', '[{"label": "Administrador Clave", "value": "6623"}, {"label": "Administrador Token", "value": "7712"}, {"label": "Enlace marco", "value": "https://drive.google.com/wwwww"}]'),
(1, 13, 'SAS096', '[{"label": "Administrador Clave", "value": "4411"}, {"label": "Administrador Token", "value": "3322"}, {"label": "Enlace marco", "value": "https://drive.google.com/qqqqq"}]');

-- 11. SEGUIMIENTO DE VISUALIZACION (VISTAS DE PUBLICACION)
INSERT INTO publicacion_vista (id_publicacion, id_usuario_sas, fecha_primera_vista) VALUES
(1, 12, '2026-08-05 10:32:00'),
(1, 13, '2026-08-05 10:32:00');
