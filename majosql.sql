-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         PostgreSQL 17.11 on x86_64-windows, compiled by msvc-19.44.35228, 64-bit
-- SO del servidor:              
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla public.asistencia
CREATE TABLE IF NOT EXISTS "asistencia" (
	"id_asistencia" SERIAL NOT NULL,
	"id_empleado" INTEGER NOT NULL,
	"fecha" DATE NOT NULL DEFAULT CURRENT_DATE,
	"hora_ingreso" TIME NULL DEFAULT NULL,
	"hora_salida" TIME NULL DEFAULT NULL,
	PRIMARY KEY ("id_asistencia"),
	CONSTRAINT "fk_empleado" FOREIGN KEY ("id_empleado") REFERENCES "empleado" ("id_empleado") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Volcando datos para la tabla public.asistencia: 0 rows
/*!40000 ALTER TABLE "asistencia" DISABLE KEYS */;
/*!40000 ALTER TABLE "asistencia" ENABLE KEYS */;

-- Volcando estructura para tabla public.detalle_venta
CREATE TABLE IF NOT EXISTS "detalle_venta" (
	"id_detalle" SERIAL NOT NULL,
	"id_venta" INTEGER NOT NULL,
	"id_producto" INTEGER NOT NULL,
	"cantidad" INTEGER NOT NULL,
	"precio_unitario" NUMERIC(10,2) NOT NULL,
	PRIMARY KEY ("id_detalle"),
	CONSTRAINT "fk_dv_producto" FOREIGN KEY ("id_producto") REFERENCES "producto" ("id_producto") ON UPDATE NO ACTION ON DELETE RESTRICT,
	CONSTRAINT "fk_dv_venta" FOREIGN KEY ("id_venta") REFERENCES "venta" ("id_venta") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Volcando datos para la tabla public.detalle_venta: 1 rows
/*!40000 ALTER TABLE "detalle_venta" DISABLE KEYS */;
-- Volcando estructura para tabla public.empleado
CREATE TABLE IF NOT EXISTS "empleado" (
	"id_empleado" SERIAL NOT NULL,
	"nombres" VARCHAR(100) NOT NULL,
	"documento_identidad" VARCHAR(20) NOT NULL,
	"es_eventual" BOOLEAN NULL DEFAULT false,
	"email" VARCHAR(255) NULL DEFAULT NULL,
	"password" VARCHAR(255) NULL DEFAULT NULL,
	"rol" VARCHAR(255) NOT NULL DEFAULT 'empleado',
	"codigo_verificacion" VARCHAR(255) NULL DEFAULT NULL,
	"codigo_expira_en" TIMESTAMP NULL DEFAULT NULL,
	"remember_token" VARCHAR(100) NULL DEFAULT NULL,
	PRIMARY KEY ("id_empleado"),
	UNIQUE "empleado_documento_identidad_key" ("documento_identidad"),
	UNIQUE "empleado_email_unique" ("email"),
	CONSTRAINT "empleado_rol_check" CHECK (((rol)::text = ANY ((ARRAY['admin'::character varying, 'empleado'::character varying])::text[])))
);

-- Volcando datos para la tabla public.empleado: 2 rows
/*!40000 ALTER TABLE "empleado" DISABLE KEYS */;
-- Volcando estructura para tabla public.migrations
CREATE TABLE IF NOT EXISTS "migrations" (
	"id" SERIAL NOT NULL,
	"migration" VARCHAR(255) NOT NULL,
	"batch" INTEGER NOT NULL,
	PRIMARY KEY ("id")
);

-- Volcando datos para la tabla public.migrations: 1 rows
/*!40000 ALTER TABLE "migrations" DISABLE KEYS */;
-- Volcando estructura para tabla public.movimiento_inventario
CREATE TABLE IF NOT EXISTS "movimiento_inventario" (
	"id_movimiento" SERIAL NOT NULL,
	"id_producto" INTEGER NOT NULL,
	"tipo_movimiento" VARCHAR(20) NOT NULL,
	"cantidad" INTEGER NOT NULL,
	"motivo" VARCHAR(200) NULL DEFAULT NULL,
	"fecha_hora" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY ("id_movimiento"),
	CONSTRAINT "fk_movimiento_producto" FOREIGN KEY ("id_producto") REFERENCES "producto" ("id_producto") ON UPDATE NO ACTION ON DELETE RESTRICT,
	CONSTRAINT "chk_tipo_movimiento" CHECK (((tipo_movimiento)::text = ANY ((ARRAY['ENTRADA'::character varying, 'SALIDA'::character varying])::text[]))),
	CONSTRAINT "chk_cantidad_movimiento" CHECK ((cantidad > 0))
);

-- Volcando datos para la tabla public.movimiento_inventario: 1 rows
/*!40000 ALTER TABLE "movimiento_inventario" DISABLE KEYS */;
-- Volcando estructura para tabla public.producto
CREATE TABLE IF NOT EXISTS "producto" (
	"id_producto" SERIAL NOT NULL,
	"nombre_producto" VARCHAR(150) NOT NULL,
	"stock_actual" INTEGER NOT NULL DEFAULT 0,
	"stock_minimo_alerta" INTEGER NOT NULL DEFAULT 10,
	"precio_venta" NUMERIC(10,2) NOT NULL,
	PRIMARY KEY ("id_producto")
);

-- Volcando datos para la tabla public.producto: 1 rows
/*!40000 ALTER TABLE "producto" DISABLE KEYS */;
-- Volcando estructura para tabla public.producto_proveedor
CREATE TABLE IF NOT EXISTS "producto_proveedor" (
	"id_producto" INTEGER NOT NULL,
	"id_proveedor" INTEGER NOT NULL,
	"precio_compra" NUMERIC(10,2) NOT NULL,
	PRIMARY KEY ("id_producto", "id_proveedor"),
	CONSTRAINT "fk_pp_producto" FOREIGN KEY ("id_producto") REFERENCES "producto" ("id_producto") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "fk_pp_proveedor" FOREIGN KEY ("id_proveedor") REFERENCES "proveedor" ("id_proveedor") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Volcando datos para la tabla public.producto_proveedor: 0 rows
/*!40000 ALTER TABLE "producto_proveedor" DISABLE KEYS */;
/*!40000 ALTER TABLE "producto_proveedor" ENABLE KEYS */;

-- Volcando estructura para tabla public.proveedor
CREATE TABLE IF NOT EXISTS "proveedor" (
	"id_proveedor" SERIAL NOT NULL,
	"nombre_empresa" VARCHAR(100) NOT NULL,
	PRIMARY KEY ("id_proveedor")
);

-- Volcando datos para la tabla public.proveedor: -1 rows
/*!40000 ALTER TABLE "proveedor" DISABLE KEYS */;
/*!40000 ALTER TABLE "proveedor" ENABLE KEYS */;

-- Volcando estructura para tabla public.venta
CREATE TABLE IF NOT EXISTS "venta" (
	"id_venta" SERIAL NOT NULL,
	"fecha_hora" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	"cliente_nombre" VARCHAR(100) NULL DEFAULT NULL,
	"cliente_documento" VARCHAR(20) NULL DEFAULT NULL,
	"metodo_pago" VARCHAR(50) NOT NULL,
	"total_venta" NUMERIC(10,2) NOT NULL,
	PRIMARY KEY ("id_venta")
);

-- Volcando datos para la tabla public.venta: -1 rows
/*!40000 ALTER TABLE "venta" DISABLE KEYS */;
/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
