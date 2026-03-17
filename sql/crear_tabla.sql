CREATE TABLE IF NOT EXISTS wp_human_diplomas (
    id                        INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie              VARCHAR(30)  NOT NULL UNIQUE,
    tipo_doc_emitido          VARCHAR(30)  NOT NULL,         -- Diploma / Certificado / Constancia
    nombre_curso              VARCHAR(250) NOT NULL,
    tipo_doc_identificacion   VARCHAR(30)  NOT NULL,         -- DNI / CE / RUC / Pasaporte
    numero_doc_identificacion VARCHAR(30)  NOT NULL,
    nombre_completo           VARCHAR(150) NOT NULL,         -- Persona o razón social
    nombre_empresa            VARCHAR(150) DEFAULT NULL,     -- Solo si el doc. es empresarial
    horas_academicas          INT          NOT NULL,
    modalidad                 VARCHAR(20)  NOT NULL,         -- Virtual / Presencial
    instructor                VARCHAR(100) NOT NULL,
    lugar_emision             VARCHAR(50)  NOT NULL,
    fecha_inicio_actividad    DATE         NOT NULL,
    fecha_fin_actividad       DATE         NOT NULL,
    fecha_emision             DATE         NOT NULL,
    estado                    TINYINT(1)   DEFAULT 1,        -- 1=activo / 0=anulado
    nota_adicional            VARCHAR(200) DEFAULT NULL,     -- Campo libre opcional
    creado_en                 TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);