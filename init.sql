-- ==============================================================================
-- SISTEMA DE TICKETS DE ATENCION ENLA 2026 - ESQUEMA COMPLETO DE BASE DE DATOS
-- ==============================================================================

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

-- 1. ROLES
CREATE TABLE rol (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT
);

-- 2. SEDES REGIONALES
CREATE TABLE sede_reg (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL
);

-- 3. SEDES JURISDICCIONALES
CREATE TABLE sede_juris (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_sedereg INT NOT NULL,
    cod VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    CONSTRAINT fk_sede_juris_sedereg FOREIGN KEY (id_sedereg) REFERENCES sede_reg(id) ON DELETE CASCADE
);

-- 4. USUARIOS
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
    codigo_monitor VARCHAR(50), -- MI1, MI2, MI3, MI4 para monitores informaticos
    doc VARCHAR(50),
    email VARCHAR(255),
    estado INT DEFAULT 1, -- 1 activo, 0 inactivo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol(id),
    CONSTRAINT fk_usuario_sedereg FOREIGN KEY (id_sedereg) REFERENCES sede_reg(id) ON DELETE SET NULL,
    CONSTRAINT fk_usuario_sedejuris FOREIGN KEY (id_sedejuris) REFERENCES sede_juris(id) ON DELETE SET NULL
);

-- 5. ASIGNACION DE SEDES A MONITORES INFORMATICOS
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

-- 6. CATALOGO DINAMICO DE CATEGORIAS Y TIPOS DE ATENCION
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

-- 7. TICKETS DE ATENCION
CREATE TABLE ticket (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod_ticket VARCHAR(50) NOT NULL UNIQUE, -- Ej: 000001
    id_usuario_solicitante INT NOT NULL, -- Supervisor SAS
    id_sedejuris INT NOT NULL,
    id_monitor_responsable INT, -- Monitor Informatico asignado
    id_categoria INT NOT NULL,
    id_tipo_atencion INT NOT NULL,
    prioridad VARCHAR(50) NOT NULL DEFAULT 'Media', -- Alta, Media, Baja
    descripcion_problema TEXT NOT NULL,
    identificador_interno_mi VARCHAR(50), -- Ej: MI1-001
    correlativo_mi INT,
    estado VARCHAR(50) NOT NULL DEFAULT 'Abierto', -- Abierto, Cerrado, No procede
    fecha_emision TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP,
    tiempo_resolucion VARCHAR(50), -- Formato hh:mm:ss
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

-- 8. ARCHIVOS ADJUNTOS DE TICKETS
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

-- 9. HISTORIAL DE ESTADOS DE TICKET
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

-- 10. AVISOS OPERATIVOS E INCIDENCIAS INFORMATIVAS
CREATE TABLE incidencia_informativa (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario_registro INT NOT NULL,
    id_sedejuris INT NOT NULL,
    tipo_incidencia VARCHAR(100) NOT NULL, -- corte_energia, problema_conectividad, aplicativo_movil
    subtipo_corte VARCHAR(50), -- programado, accidental
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

-- 11. PUBLICACION DE APLICATIVOS Y CREDENCIALES
CREATE TABLE publicacion (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario_autor INT NOT NULL,
    nombre_aplicativo VARCHAR(255) NOT NULL,
    version VARCHAR(100) NOT NULL,
    fecha_publicacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    indicaciones TEXT,
    enlaces_generales TEXT,
    estado VARCHAR(50) NOT NULL DEFAULT 'Activa', -- Activa, Desactivada
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

-- 12. TABLA PARA TOKENS DE LARAVEL SANCTUM
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
