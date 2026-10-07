-- ============================================================
-- TABLA USUARIO + USUARIOS DE PRUEBA
-- SaludUSM - MySQL / MariaDB / phpMyAdmin
-- ============================================================

CREATE TABLE Usuario (
    Id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    Usuario VARCHAR(100) NOT NULL UNIQUE,
    Email VARCHAR(150) NULL UNIQUE,
    Password_hash VARCHAR(255) NOT NULL,
    Rol ENUM('Paciente', 'Medico', 'Administrador') NOT NULL,
    Rut_paciente VARCHAR(12) NULL,
    Rut_medico VARCHAR(12) NULL,

    CONSTRAINT fk_usuario_paciente
        FOREIGN KEY (Rut_paciente)
        REFERENCES Paciente(Rut_paciente)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuario_medico
        FOREIGN KEY (Rut_medico)
        REFERENCES Medico(Rut_medico)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- ============================================================
-- USUARIOS DE PRUEBA
-- Contraseña para los tres: prueba123
-- ============================================================

INSERT INTO Usuario
(Usuario, Email, Password_hash, Rol, Rut_paciente, Rut_medico)
VALUES
(
    'paciente1',
    'paciente1@test.cl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw3n8V7fQ6V0K9bY7y',
    'Paciente',
    '15000382-2',
    NULL
),
(
    'medico1',
    'medico1@test.cl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw3n8V7fQ6V0K9bY7y',
    'Medico',
    NULL,
    '12173921-6'
),
(
    'admin',
    'admin@test.cl',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCw3n8V7fQ6V0K9bY7y',
    'Administrador',
    NULL,
    NULL
);

-- ============================================================
-- CREDENCIALES
--
-- paciente1 / prueba123
-- medico1   / prueba123
-- admin     / prueba123
-- ============================================================
