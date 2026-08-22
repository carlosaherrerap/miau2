-- ==============================================================================
-- SISTEMA DE TICKETS DE ATENCION ENLA 2026 - ESQUEMA COMPLETO Y DATOS INICIALES
-- ==============================================================================

-- 1. LIMPIEZA DE TABLAS PREVIAS
DROP TABLE IF EXISTS personal_access_tokens CASCADE;
DROP TABLE IF EXISTS publicacion_vista CASCADE;
DROP TABLE IF EXISTS publicacion_credencial CASCADE;
DROP TABLE IF EXISTS publicacion_archivo CASCADE;
DROP TABLE IF EXISTS publicacion CASCADE;
DROP TABLE IF EXISTS incidencia_archivo CASCADE;
DROP TABLE IF EXISTS incidencia_informativa CASCADE;
DROP TABLE IF EXISTS ticket_archivo CASCADE;
DROP TABLE IF EXISTS estado_ticket CASCADE;
DROP TABLE IF EXISTS ticket CASCADE;
DROP TABLE IF EXISTS tipo_atencion CASCADE;
DROP TABLE IF EXISTS categoria_atencion CASCADE;
DROP TABLE IF EXISTS asignacion_monitor CASCADE;
DROP TABLE IF EXISTS usuario CASCADE;
DROP TABLE IF EXISTS sede_juris CASCADE;
DROP TABLE IF EXISTS sede_reg CASCADE;
DROP TABLE IF EXISTS rol CASCADE;

-- 2. TABLA ROLES
CREATE TABLE rol (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT
);

-- 3. TABLA SEDES REGIONALES
CREATE TABLE sede_reg (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL
);

-- 4. TABLA SEDES JURISDICCIONALES
CREATE TABLE sede_juris (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_sedereg INT NOT NULL,
    cod VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    CONSTRAINT fk_sede_juris_sedereg FOREIGN KEY (id_sedereg) REFERENCES sede_reg(id) ON DELETE CASCADE
);

-- 5. TABLA USUARIOS
CREATE TABLE usuario (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_rol INT NOT NULL,
    cod_usuario VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    ape_pat VARCHAR(255) NOT NULL,
    ape_mat VARCHAR(255),
    id_sedereg INT,
    id_sedejuris INT,
    codigo_monitor VARCHAR(50),
    doc VARCHAR(50),
    email VARCHAR(255),
    estado INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol(id),
    CONSTRAINT fk_usuario_sedereg FOREIGN KEY (id_sedereg) REFERENCES sede_reg(id) ON DELETE SET NULL,
    CONSTRAINT fk_usuario_sedejuris FOREIGN KEY (id_sedejuris) REFERENCES sede_juris(id) ON DELETE SET NULL
);

-- 6. ASIGNACION DE SEDES A MONITORES INFORMATICOS
CREATE TABLE asignacion_monitor (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario_monitor INT NOT NULL,
    id_sedereg INT NOT NULL,
    id_sedejuris INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asig_monitor_usuario FOREIGN KEY (id_usuario_monitor) REFERENCES usuario(id) ON DELETE CASCADE,
    CONSTRAINT fk_asig_monitor_sedereg FOREIGN KEY (id_sedereg) REFERENCES sede_reg(id) ON DELETE CASCADE,
    CONSTRAINT fk_asig_monitor_sedejuris FOREIGN KEY (id_sedejuris) REFERENCES sede_juris(id) ON DELETE CASCADE
);

-- 7. CATALOGO DINAMICO DE CATEGORIAS Y TIPOS DE ATENCION
CREATE TABLE categoria_atencion (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tipo_atencion (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tipo_atencion_categoria FOREIGN KEY (id_categoria) REFERENCES categoria_atencion(id) ON DELETE CASCADE
);

-- 8. TICKETS DE ATENCION
CREATE TABLE ticket (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod_ticket VARCHAR(50) NOT NULL UNIQUE,
    id_usuario_solicitante INT NOT NULL,
    id_sedejuris INT NOT NULL,
    id_monitor_responsable INT,
    id_categoria INT NOT NULL,
    id_tipo_atencion INT NOT NULL,
    prioridad VARCHAR(50) NOT NULL DEFAULT 'Media',
    descripcion_problema TEXT NOT NULL,
    identificador_interno_mi VARCHAR(50),
    correlativo_mi INT,
    estado VARCHAR(50) NOT NULL DEFAULT 'Abierto',
    fecha_emision TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP,
    tiempo_resolucion VARCHAR(50),
    tiempo_resolucion_segundos INT,
    descripcion_resolucion TEXT,
    justificacion_no_procede TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_usuario FOREIGN KEY (id_usuario_solicitante) REFERENCES usuario(id),
    CONSTRAINT fk_ticket_sedejuris FOREIGN KEY (id_sedejuris) REFERENCES sede_juris(id),
    CONSTRAINT fk_ticket_monitor FOREIGN KEY (id_monitor_responsable) REFERENCES usuario(id),
    CONSTRAINT fk_ticket_categoria FOREIGN KEY (id_categoria) REFERENCES categoria_atencion(id),
    CONSTRAINT fk_ticket_tipo FOREIGN KEY (id_tipo_atencion) REFERENCES tipo_atencion(id)
);

-- 9. ARCHIVOS ADJUNTOS DE TICKETS
CREATE TABLE ticket_archivo (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_ticket INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    mime_type VARCHAR(150),
    tamano_bytes BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_archivo_ticket FOREIGN KEY (id_ticket) REFERENCES ticket(id) ON DELETE CASCADE
);

-- 10. HISTORIAL DE ESTADOS DE TICKET
CREATE TABLE estado_ticket (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_usuario INT NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha_estado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    descripcion_solucion TEXT,
    justificacion TEXT,
    CONSTRAINT fk_estado_ticket_ticket FOREIGN KEY (id_ticket) REFERENCES ticket(id) ON DELETE CASCADE,
    CONSTRAINT fk_estado_ticket_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id)
);

-- 11. AVISOS OPERATIVOS E INCIDENCIAS INFORMATIVAS
CREATE TABLE incidencia_informativa (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario_registro INT NOT NULL,
    id_sedejuris INT NOT NULL,
    tipo_incidencia VARCHAR(100) NOT NULL,
    subtipo_corte VARCHAR(50),
    fecha_inicio TIMESTAMP,
    fecha_reanudacion TIMESTAMP,
    tiempo_interrupcion_minutos INT,
    actividades_afectadas TEXT,
    observaciones TEXT,
    marca_modelo VARCHAR(255),
    version_android VARCHAR(100),
    aplicativo_afectado VARCHAR(255),
    proceso_relacionado VARCHAR(255),
    descripcion_problema TEXT,
    descartes_sas TEXT,
    resultado_pruebas TEXT,
    funciona_en_equipo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_incidencia_usuario FOREIGN KEY (id_usuario_registro) REFERENCES usuario(id),
    CONSTRAINT fk_incidencia_sedejuris FOREIGN KEY (id_sedejuris) REFERENCES sede_juris(id)
);

CREATE TABLE incidencia_archivo (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_incidencia INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    mime_type VARCHAR(150),
    tamano_bytes BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_incidencia_archivo_incidencia FOREIGN KEY (id_incidencia) REFERENCES incidencia_informativa(id) ON DELETE CASCADE
);

-- 12. PUBLICACIONES DE APLICATIVOS Y CREDENCIALES
CREATE TABLE publicacion (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario_autor INT NOT NULL,
    nombre_aplicativo VARCHAR(255) NOT NULL,
    version VARCHAR(100) NOT NULL,
    fecha_publicacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    indicaciones TEXT,
    enlaces_generales TEXT,
    estado VARCHAR(50) NOT NULL DEFAULT 'Activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_publicacion_autor FOREIGN KEY (id_usuario_autor) REFERENCES usuario(id)
);

CREATE TABLE publicacion_archivo (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_publicacion INT NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    mime_type VARCHAR(150),
    tamano_bytes BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pub_archivo_pub FOREIGN KEY (id_publicacion) REFERENCES publicacion(id) ON DELETE CASCADE
);

CREATE TABLE publicacion_credencial (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_publicacion INT NOT NULL,
    id_usuario_sas INT NOT NULL,
    cod_sas VARCHAR(100) NOT NULL,
    datos_personalizados JSONB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pub_cred_pub FOREIGN KEY (id_publicacion) REFERENCES publicacion(id) ON DELETE CASCADE,
    CONSTRAINT fk_pub_cred_usuario FOREIGN KEY (id_usuario_sas) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE publicacion_vista (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_publicacion INT NOT NULL,
    id_usuario_sas INT NOT NULL,
    fecha_primera_vista TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pub_vista_pub FOREIGN KEY (id_publicacion) REFERENCES publicacion(id) ON DELETE CASCADE,
    CONSTRAINT fk_pub_vista_usuario FOREIGN KEY (id_usuario_sas) REFERENCES usuario(id) ON DELETE CASCADE,
    CONSTRAINT uq_pub_vista UNIQUE (id_publicacion, id_usuario_sas)
);

-- 13. TABLA TOKENS SANCTUM
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id);

-- ==============================================================================
-- INSERCION DE DATOS INICIALES CON CONTRASENAS SEGURAS (BCRYPT COSTO 12)
-- ==============================================================================

-- ROLES
INSERT INTO rol (cod, nombre, descripcion) VALUES
('SAS', 'Supervisor de Almacen y Soporte Informatico', 'Registra tickets de su sede jurisdiccional y reporta incidencias'),
('MI', 'Monitor Informatico', 'Gestiona, atiende y resuelve tickets de sus sedes asignadas'),
('EMI', 'Especialista de Monitoreo Informatico', 'Acceso global, administra catalogo de categorias/tipos y gestiona publicaciones'),
('CSMI', 'Coordinador de Sistemas y Monitoreo Informatico', 'Acceso global y seguimiento gerencial (Santiago)'),
('ECC', 'Especialista de Control de Calidad', 'Acceso global y seguimiento de resoluciones y calidad'),
('GUEST', 'Usuario Guest', 'Acceso global de consulta y reportes');

-- SEDES REGIONALES
INSERT INTO sede_reg (cod, nombre) VALUES
('REG-LIM', 'LIMA METROPOLITANA'),
('REG-CAL', 'CALLAO'),
('REG-TRU', 'LA LIBERTAD - TRUJILLO'),
('REG-AQP', 'AREQUIPA'),
('REG-TUM', 'TUMBES'),
('REG-TAC', 'TACNA');

-- SEDES JURISDICCIONALES
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

-- USUARIOS CON NUEVAS CONTRASENAS SEGURAS
INSERT INTO usuario (id_rol, cod_usuario, username, clave, nombres, ape_pat, ape_mat, id_sedereg, id_sedejuris, codigo_monitor, doc, email, estado) VALUES
(3, 'EMI-001', 'especialista', '$2y$12$V92c3yuPq4RPBHmapxKj7OPqmG2J2cmqdOC.RZVNFV60HujA8EU5q', 'Ricardo', 'Palma', 'Mendoza', NULL, NULL, NULL, '45892011', 'especialista@enla2026.gob.pe', 1),
(4, 'CSMI-001', 'santiago', '$2y$12$aVpudkoRtoNnoskf.GnrI.AXykjFm14lriLcIMZzJjofpRli0q1sq', 'Santiago', 'Alvarez', 'Vargas', NULL, NULL, NULL, '40129845', 'santiago@enla2026.gob.pe', 1),
(5, 'ECC-001', 'calidad', '$2y$12$wzgwNNd8DtsiXg6VUucGvuvXyVGLkXpq.9XDzVmOzKU3CmdM1nCK2', 'Elena', 'Torres', 'Castro', NULL, NULL, NULL, '42981734', 'calidad@enla2026.gob.pe', 1),
(6, 'GUEST-001', 'guest', '$2y$12$wqVx9htStzLffbe2WwNr9uUD5SB/4XcaMM2.Ka6Ul1/mTsodEGfnS', 'Observador', 'Invitado', 'General', NULL, NULL, NULL, '00000000', 'guest@enla2026.gob.pe', 1),
(2, 'MI-001', 'monitor1', '$2y$12$a3lMkeCzbbuifiA4iU0kTOQnmm8xQQmkLfpJaELIbPxn/X2sCHtRS', 'Carlos', 'Rojas', 'Salazar', 1, NULL, 'MI1', '46781290', 'monitor1@enla2026.gob.pe', 1),
(2, 'MI-002', 'monitor2', '$2y$12$qDbbdrEQZ8V2H0TiuoqCEeJiMgA.9bHx1lF/JOvULmPHaGh1EdrvK', 'Ana', 'Gutierrez', 'Paz', 2, NULL, 'MI2', '47891234', 'monitor2@enla2026.gob.pe', 1),
(2, 'MI-003', 'monitor3', '$2y$12$9hOwokWjOICpK4hln8ItA.Lkq1GSoVOgRuGpROPcKQ9Fs4rNQqYiG', 'Jorge', 'Navarro', 'Ruiz', 3, NULL, 'MI3', '41238901', 'monitor3@enla2026.gob.pe', 1),
(2, 'MI-004', 'monitor4', '$2y$12$S9FWupF9Jqe7Behs4iF2UeZy/FzC2zpPgq/1w6Ynr/bFS0ude.7Ge', 'Patricia', 'Quispe', 'Luna', 4, NULL, 'MI4', '43890123', 'monitor4@enla2026.gob.pe', 1),
(1, 'SAS022', 'sas022', '$2y$12$LMjzDGErYuz6iyTVewv5HeCBrJaZ9Zef.SGwG88rOlXGf1KUHSzWK', 'Manuel', 'Villar', 'Soto', 2, 5, NULL, '71239845', 'sas022@enla2026.gob.pe', 1),
(1, 'SAS154', 'sas154', '$2y$12$677Nahkl.qOwqdU.bol8NuFPd8LLwDfNKJpjRk3CYfzhrs9/vkgPq', 'Rosa', 'Sanchez', 'Perez', 1, 3, NULL, '72340912', 'sas154@enla2026.gob.pe', 1),
(1, 'SAS043', 'sas043', '$2y$12$najpgJH2VZWDbzGHeuOgGOfVvMw.ym2ZAeLzfmFBGWmdAJeaHfexy', 'Daniel', 'Caceres', 'Mora', 1, 1, NULL, '73451023', 'sas043@enla2026.gob.pe', 1),
(1, 'SAS044', 'sas044', '$2y$12$moGGppD92mdihIp1aGypaO26hak.qufHnsKhdPioi./4CM2VrNJtu', 'Lucia', 'Bravo', 'Campos', 5, 10, NULL, '74562134', 'sas044@enla2026.gob.pe', 1),
(1, 'SAS096', 'sas096', '$2y$12$XrUHMmvjYO8b8fYxS.RrD.T0Z2aKRcbYOLHKRYfQ9ybgjxqzDxz1a', 'Hugo', 'Paredes', 'Leiva', 6, 12, NULL, '75673245', 'sas096@enla2026.gob.pe', 1),
(1, 'SAS050', 'sas050', '$2y$12$qlszJK5f/7HO3agYVTkO8OpCNw8LzMS3/7yZ.Mm6bWr2Y92.y1Oki', 'Valeria', 'Flores', 'Vega', 3, 6, NULL, '76784356', 'sas050@enla2026.gob.pe', 1);

-- ASIGNACIONES DE MONITORES
INSERT INTO asignacion_monitor (id_usuario_monitor, id_sedereg, id_sedejuris) VALUES
(5, 1, 1), (5, 1, 2), (5, 1, 3),
(6, 2, 4), (6, 2, 5), (6, 5, 9), (6, 5, 10),
(7, 3, 6), (7, 3, 7), (7, 6, 11), (7, 6, 12),
(8, 4, 8);

-- CATALOGO DE CATEGORIAS Y TIPOS
INSERT INTO categoria_atencion (nombre, activo) VALUES
('Sistema Integrado', TRUE),
('APK Geolocalización', TRUE),
('APK Asistencia', TRUE),
('Equipo Informático', TRUE);

INSERT INTO tipo_atencion (id_categoria, nombre, activo) VALUES
(1, 'Aplicadores - Preselección', TRUE),
(1, 'Aplicadores - Capacitación', TRUE),
(2, 'APA', TRUE),
(2, 'Aplicadores', TRUE),
(3, 'APA', TRUE),
(3, 'Aplicadores', TRUE),
(4, 'Laptop', TRUE),
(4, 'Impresora', TRUE),
(4, 'Lectora de código de barras', TRUE);

-- TICKETS DE PRUEBA
INSERT INTO ticket (cod_ticket, id_usuario_solicitante, id_sedejuris, id_monitor_responsable, id_categoria, id_tipo_atencion, prioridad, descripcion_problema, identificador_interno_mi, correlativo_mi, estado, fecha_emision, fecha_cierre, tiempo_resolucion, tiempo_resolucion_segundos, descripcion_resolucion, justificacion_no_procede) VALUES
('000001', 11, 1, 5, 1, 1, 'Alta', 'Error al sincronizar lista de preseleccion en Lima Met 1', 'MI1-001', 1, 'Cerrado', '2026-08-02 07:12:00', '2026-08-02 09:55:00', '02:43:00', 9780, 'Se reinicio el servicio de sincronizacion en base de datos central.', NULL),
('000002', 9, 5, 6, 2, 3, 'Media', 'Falla de conexion de geolocalizacion en Ventanilla', 'MI2-001', 1, 'Cerrado', '2026-08-02 09:45:00', '2026-08-02 15:12:00', '05:27:00', 19620, 'Se ajustaron permisos de ubicacion GPS y APN de datos.', NULL),
('000003', 10, 3, 5, 4, 8, 'Alta', 'Impresora no reconoce consumible en Lima Met 3', 'MI1-002', 2, 'Cerrado', '2026-08-02 14:32:00', '2026-08-04 09:13:00', '42:41:00', 153660, 'Se reemplazo cartucho de toner por repuesto en stock.', NULL),
('000004', 14, 6, 7, 3, 5, 'Media', 'APK de Asistencia arroja error de validacion de horario', 'MI3-001', 1, 'Cerrado', '2026-08-02 10:47:00', '2026-08-05 19:03:00', '80:16:00', 288960, 'Se actualizo rango de tolerancia de horario en el servidor.', NULL),
('000005', 11, 1, 5, 4, 7, 'Alta', 'Laptop principal no enciende tras corte de energia', 'MI1-003', 3, 'Abierto', '2026-08-16 08:30:00', NULL, NULL, NULL, NULL, NULL),
('000006', 12, 10, 6, 2, 4, 'Baja', 'Duda sobre exportacion de coordenadas en Tumbes 2', 'MI2-002', 2, 'No procede', '2026-08-15 11:00:00', '2026-08-15 11:30:00', '00:30:00', 1800, NULL, 'La consulta corresponde a capacitacion funcional, no a incidencia tecnica.');

-- INCIDENCIAS INFORMATIVAS
INSERT INTO incidencia_informativa (id_usuario_registro, id_sedejuris, tipo_incidencia, subtipo_corte, fecha_inicio, fecha_reanudacion, tiempo_interrupcion_minutos, actividades_afectadas, observaciones) VALUES
(11, 1, 'corte_energia', 'accidental', '2026-08-10 14:00:00', '2026-08-10 16:30:00', 150, 'Atencion en ventanilla y registro de postulantes', 'Corte general en la manzana por mantenimiento de red publica'),
(9, 5, 'problema_conectividad', 'programado', '2026-08-12 08:00:00', '2026-08-12 10:00:00', 120, 'Transmision de datos de evaluacion', 'Mantenimiento preventivo de enlace de fibra optica');

INSERT INTO incidencia_informativa (id_usuario_registro, id_sedejuris, tipo_incidencia, marca_modelo, version_android, aplicativo_afectado, proceso_relacionado, descripcion_problema, descartes_sas, resultado_pruebas, funciona_en_equipo, observaciones) VALUES
(11, 1, 'aplicativo_movil', 'Samsung Galaxy A03', 'Android 11 Go Edition', 'APK Geolocalización v3.0', 'Toma de punto GPS en campo', 'La app se cierra inesperadamente al iniciar el servicio de camara', 'Se reinstalo APK, se limpiaron datos y cache', 'Fallo persistente por memoria RAM insuficiente (2GB)', FALSE, 'Se determina que el equipo no cumple con requerimientos minimos para el APK.');

-- PUBLICACIONES
INSERT INTO publicacion (id_usuario_autor, nombre_aplicativo, version, fecha_publicacion, indicaciones, enlaces_generales, estado) VALUES
(1, 'APK Geolocalización', 'Versión 3.0', '2026-08-01 08:00:00', 'Actualizacion critica para el proceso de preseleccion. Todos los supervisores SAS deben verificar credenciales y descargar la ultima version.', 'https://drive.google.com/drive/folders/apk-geoloc-v3', 'Activa'),
(1, 'APK Asistencia', 'Versión 2.1', '2026-08-05 09:00:00', 'Modulo para marcacion de asistencia de personal aplicador y coordinadores.', 'https://drive.google.com/drive/folders/apk-asistencia-v2', 'Activa'),
(1, 'Manual de Operaciones Sistema Integrado', 'Versión 1.0', '2026-07-20 10:00:00', 'Guia metodologica y tecnica para atencion en sedes jurisdiccionales.', 'https://drive.google.com/drive/folders/manual-ops-2026', 'Desactivada');

-- CREDENCIALES SAS
INSERT INTO publicacion_credencial (id_publicacion, id_usuario_sas, cod_sas, datos_personalizados) VALUES
(1, 11, 'SAS043', '[{"label": "Administrador Clave", "value": "3345"}, {"label": "Administrador Token", "value": "4456"}, {"label": "Enlace marco", "value": "https://drive.google.com/xxxxx"}]'),
(1, 12, 'SAS044', '[{"label": "Administrador Clave", "value": "1234"}, {"label": "Administrador Clave", "value": "8846"}, {"label": "Enlace marco", "value": "https://drive.google.com/yyyyy"}]'),
(1, 9, 'SAS022', '[{"label": "Administrador Clave", "value": "5512"}, {"label": "Administrador Token", "value": "9981"}, {"label": "Enlace marco", "value": "https://drive.google.com/zzzzz"}]'),
(1, 10, 'SAS154', '[{"label": "Administrador Clave", "value": "6623"}, {"label": "Administrador Token", "value": "7712"}, {"label": "Enlace marco", "value": "https://drive.google.com/wwwww"}]'),
(1, 13, 'SAS096', '[{"label": "Administrador Clave", "value": "4411"}, {"label": "Administrador Token", "value": "3322"}, {"label": "Enlace marco", "value": "https://drive.google.com/qqqqq"}]');

-- VISTAS DE PUBLICACION
INSERT INTO publicacion_vista (id_publicacion, id_usuario_sas, fecha_primera_vista) VALUES
(1, 12, '2026-08-05 10:32:00'),
(1, 13, '2026-08-05 10:32:00');
