-- ============================================================
-- ESTRUCTURA BASE DE DATOS SALUDUSM
-- MySQL / MariaDB / phpMyAdmin
-- 15 tablas originales
-- ============================================================

CREATE TABLE Sexo (
    Id_sexo INT NOT NULL PRIMARY KEY,
    Descripcion VARCHAR(30) NOT NULL,
    Valores VARCHAR(30) NOT NULL
);

CREATE TABLE Prevision_Salud (
    Id_prevision INT NOT NULL PRIMARY KEY,
    Descripcion VARCHAR(30) NOT NULL,
    Valores VARCHAR(30) NOT NULL
);

CREATE TABLE Estado_cita (
    Id_estados INT NOT NULL PRIMARY KEY,
    Descripcion VARCHAR(30) NOT NULL,
    Valores VARCHAR(30) NOT NULL
);

CREATE TABLE Especialidad (
    Id_especialidad INT NOT NULL PRIMARY KEY,
    Tipo_especialidad VARCHAR(30) NOT NULL
);

CREATE TABLE Paciente (
    Rut_paciente VARCHAR(12) NOT NULL PRIMARY KEY,
    Nombre_completo VARCHAR(120) NOT NULL,
    Fecha_nacimiento DATE NOT NULL,
    Id_sexo INT NOT NULL,
    Id_prevision INT NOT NULL,
    Telefono_contacto VARCHAR(15),
    Comuna_residencia VARCHAR(120) NOT NULL,
    CONSTRAINT fk_paciente_sexo FOREIGN KEY (Id_sexo)
        REFERENCES Sexo(Id_sexo),
    CONSTRAINT fk_paciente_prevision FOREIGN KEY (Id_prevision)
        REFERENCES Prevision_Salud(Id_prevision)
);

CREATE TABLE Medico (
    Rut_medico VARCHAR(12) NOT NULL PRIMARY KEY,
    Nombre_completo VARCHAR(120) NOT NULL,
    E_mail_institucional VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE Centro_medico (
    Codigo_interno INT NOT NULL PRIMARY KEY,
    Nombre VARCHAR(30) NOT NULL,
    Comuna VARCHAR(30) NOT NULL,
    Region VARCHAR(30) NOT NULL
);

CREATE TABLE Medico_Centro (
    Rut_medico VARCHAR(12) NOT NULL,
    Codigo_interno INT NOT NULL,
    PRIMARY KEY (Rut_medico, Codigo_interno),
    CONSTRAINT fk_medico_centro_medico FOREIGN KEY (Rut_medico)
        REFERENCES Medico(Rut_medico)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_medico_centro_centro FOREIGN KEY (Codigo_interno)
        REFERENCES Centro_medico(Codigo_interno)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Medico_Especialidad (
    Rut_medico VARCHAR(12) NOT NULL,
    Id_especialidad INT NOT NULL,
    PRIMARY KEY (Rut_medico, Id_especialidad),
    CONSTRAINT fk_medico_especialidad_medico FOREIGN KEY (Rut_medico)
        REFERENCES Medico(Rut_medico)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_medico_especialidad_especialidad FOREIGN KEY (Id_especialidad)
        REFERENCES Especialidad(Id_especialidad)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Cita (
    Id_cita INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Fecha_hora DATETIME NOT NULL,
    Rut_paciente VARCHAR(12) NOT NULL,
    Rut_medico VARCHAR(12) NOT NULL,
    Codigo_centro INT NOT NULL,
    Id_especialidad INT NOT NULL,
    Estado_cita INT NOT NULL,
    CONSTRAINT fk_cita_paciente FOREIGN KEY (Rut_paciente)
        REFERENCES Paciente(Rut_paciente),
    CONSTRAINT fk_cita_medico FOREIGN KEY (Rut_medico)
        REFERENCES Medico(Rut_medico),
    CONSTRAINT fk_cita_centro FOREIGN KEY (Codigo_centro)
        REFERENCES Centro_medico(Codigo_interno),
    CONSTRAINT fk_cita_especialidad FOREIGN KEY (Id_especialidad)
        REFERENCES Especialidad(Id_especialidad),
    CONSTRAINT fk_cita_estado FOREIGN KEY (Estado_cita)
        REFERENCES Estado_cita(Id_estados),
    UNIQUE (Rut_medico, Codigo_centro, Fecha_hora),
    UNIQUE (Rut_paciente, Fecha_hora)
);

CREATE TABLE Atencion (
    Id_atencion INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Id_cita INT NOT NULL UNIQUE,
    Motivo_consulta VARCHAR(255) NOT NULL,
    Observaciones_clinicas TEXT NOT NULL,
    CONSTRAINT fk_atencion_cita FOREIGN KEY (Id_cita)
        REFERENCES Cita(Id_cita)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Diagnostico (
    Codigo VARCHAR(4) NOT NULL PRIMARY KEY,
    Descripcion VARCHAR(222) NOT NULL
);

CREATE TABLE Atencion_Diagnostico (
    Id_atencion INT NOT NULL,
    Codigo_diagnostico VARCHAR(4) NOT NULL,
    PRIMARY KEY (Id_atencion, Codigo_diagnostico),
    CONSTRAINT fk_atencion_diagnostico_atencion FOREIGN KEY (Id_atencion)
        REFERENCES Atencion(Id_atencion)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_atencion_diagnostico_diagnostico FOREIGN KEY (Codigo_diagnostico)
        REFERENCES Diagnostico(Codigo)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Receta (
    Id_receta INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Id_atencion INT NOT NULL,
    CONSTRAINT fk_receta_atencion FOREIGN KEY (Id_atencion)
        REFERENCES Atencion(Id_atencion)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Detalle_Receta (
    Id_detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Id_receta INT NOT NULL,
    Medicamento VARCHAR(30),
    Dosis VARCHAR(30),
    Cantidad_dias INT,
    CONSTRAINT fk_detalle_receta_receta FOREIGN KEY (Id_receta)
        REFERENCES Receta(Id_receta)
        ON DELETE CASCADE ON UPDATE CASCADE
);
