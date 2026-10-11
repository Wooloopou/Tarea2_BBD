Segunda parte de la Tarea 2 de BDD

202430512-3  Christian Melo
202473631-0  Constanza Vargas

El primer paso es exportar la tarea desde el aula

Luego crear la BDD en pgAdmin
1.Abrir el XAMPP
2.Activar apache y Mysql

Entrar a http://localhost:8012/phpmyadmin //8012 puerto coni
1.ingresar con root con contraseña "prueba123"
2.crear salud_usm

Dentro de phpMyAdmin, seleccionar la base salud_usm.
1.Ir a la pestaña Importar.
2.Importar primero el archivo de estructura (estructura_saludusm_15_tablas_mysql.sql).
3.Luego importar el archivo de poblamiento (poblamiento_salud_usm_MYSQL.sql).
4.Finalmente importar el archivo de usuarios (usuario_saludusm_mysql.sql).

Presionar Continuar en cada importación para ejecutar.

Function SQL: lo utilizamos en gestion de pasientes, cuando se necesita mostrar la edad 
en vez de la fecha de nacimiento

Trigger: Lo uilizamos en cita para evitar que un pasiente agende dos citas a la vez

View: En el panel de gestion de citas 

Procedimiento almacenado: Se realizo en modulo de atencion medica, cuando un medico 
registra una consulta

Supuestos:
*Rut paciente y medico estan en formato XXXXXXXX-X



