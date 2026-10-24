CREATE TABLE "asistencia" (
	"id_asistencia" SERIAL NOT NULL,
	"id_empleado" INTEGER NOT NULL,
	"fecha" DATE NOT NULL DEFAULT CURRENT_DATE,
	"hora_ingreso" TIME NULL DEFAULT NULL,
	"hora_salida" TIME NULL DEFAULT NULL
)
;
COMMENT ON COLUMN "asistencia"."id_asistencia" IS '';
COMMENT ON COLUMN "asistencia"."id_empleado" IS '';
COMMENT ON COLUMN "asistencia"."fecha" IS '';
COMMENT ON COLUMN "asistencia"."hora_ingreso" IS '';
COMMENT ON COLUMN "asistencia"."hora_salida" IS '';

CREATE TABLE "detalle_venta" (
	"id_detalle" SERIAL NOT NULL,
	"id_venta" INTEGER NOT NULL,
	"id_producto" INTEGER NOT NULL,
	"cantidad" INTEGER NOT NULL,
	"precio_unitario" NUMERIC(10,2) NOT NULL
)
;
COMMENT ON COLUMN "detalle_venta"."id_detalle" IS '';
COMMENT ON COLUMN "detalle_venta"."id_venta" IS '';
COMMENT ON COLUMN "detalle_venta"."id_producto" IS '';
COMMENT ON COLUMN "detalle_venta"."cantidad" IS '';
COMMENT ON COLUMN "detalle_venta"."precio_unitario" IS '';

CREATE TABLE "empleado" (
	"id_empleado" SERIAL NOT NULL,
	"nombres" VARCHAR(100) NOT NULL,
	"es_eventual" BOOLEAN NULL DEFAULT false,
	"password" VARCHAR(255) NULL DEFAULT NULL::character varying,
	"rol" VARCHAR(255) NOT NULL DEFAULT 'empleado',
	"codigo_verificacion" VARCHAR(255) NULL DEFAULT NULL::character varying,
	"codigo_expira_en" TIMESTAMP NULL DEFAULT NULL,
	"remember_token" VARCHAR(100) NULL DEFAULT NULL::character varying,
	"usuario" VARCHAR(50) NULL DEFAULT NULL::character varying,
	"documento_identidad" VARCHAR(20) NULL DEFAULT NULL::character varying,
	PRIMARY KEY ("id_empleado"),
	UNIQUE "empleado_usuario_unique" ("usuario"),
	CONSTRAINT "empleado_rol_check" CHECK (((rol)::text = ANY (ARRAY[('admin'::character varying)::text, ('empleado'::character varying)::text])))
)
;
COMMENT ON COLUMN "empleado"."id_empleado" IS '';
COMMENT ON COLUMN "empleado"."nombres" IS '';
COMMENT ON COLUMN "empleado"."es_eventual" IS '';
COMMENT ON COLUMN "empleado"."password" IS '';
COMMENT ON COLUMN "empleado"."rol" IS '';
COMMENT ON COLUMN "empleado"."codigo_verificacion" IS '';
COMMENT ON COLUMN "empleado"."codigo_expira_en" IS '';
COMMENT ON COLUMN "empleado"."remember_token" IS '';
COMMENT ON COLUMN "empleado"."usuario" IS '';
COMMENT ON COLUMN "empleado"."documento_identidad" IS '';

CREATE TABLE "empleado_temporal" (
	"id_temporal" SERIAL NOT NULL,
	"id_empleado" INTEGER NULL DEFAULT NULL,
	"nombre_temp" VARCHAR(100) NULL DEFAULT NULL,
	"fecha_inicio" DATE NULL DEFAULT NULL,
	"fecha_fin" DATE NULL DEFAULT NULL,
	"agencia_outsourcing" VARCHAR(100) NULL DEFAULT NULL::character varying,
	"hora_ingreso" TIME NULL DEFAULT NULL,
	"hora_salida" TIME NULL DEFAULT NULL,
	PRIMARY KEY ("id_temporal")
)
;
COMMENT ON COLUMN "empleado_temporal"."id_temporal" IS '';
COMMENT ON COLUMN "empleado_temporal"."id_empleado" IS '';
COMMENT ON COLUMN "empleado_temporal"."nombre_temp" IS '';
COMMENT ON COLUMN "empleado_temporal"."fecha_inicio" IS '';
COMMENT ON COLUMN "empleado_temporal"."fecha_fin" IS '';
COMMENT ON COLUMN "empleado_temporal"."agencia_outsourcing" IS '';
COMMENT ON COLUMN "empleado_temporal"."hora_ingreso" IS '';
COMMENT ON COLUMN "empleado_temporal"."hora_salida" IS '';


CREATE TABLE "migrations" (
	"id" SERIAL NOT NULL,
	"migration" VARCHAR(255) NOT NULL,
	"batch" INTEGER NOT NULL
)
;
COMMENT ON COLUMN "migrations"."id" IS '';
COMMENT ON COLUMN "migrations"."migration" IS '';
COMMENT ON COLUMN "migrations"."batch" IS '';


CREATE TABLE "movimiento_inventario" (
	"id_movimiento" SERIAL NOT NULL,
	"id_producto" INTEGER NOT NULL,
	"tipo_movimiento" VARCHAR(20) NOT NULL,
	"cantidad" INTEGER NOT NULL,
	"motivo" VARCHAR(200) NULL DEFAULT NULL::character varying,
	"fecha_hora" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT "chk_cantidad_movimiento" CHECK ((cantidad > 0)),
	CONSTRAINT "chk_tipo_movimiento" CHECK (((tipo_movimiento)::text = ANY (ARRAY[('ENTRADA'::character varying)::text, ('SALIDA'::character varying)::text])))
)
;
COMMENT ON COLUMN "movimiento_inventario"."id_movimiento" IS '';
COMMENT ON COLUMN "movimiento_inventario"."id_producto" IS '';
COMMENT ON COLUMN "movimiento_inventario"."tipo_movimiento" IS '';
COMMENT ON COLUMN "movimiento_inventario"."cantidad" IS '';
COMMENT ON COLUMN "movimiento_inventario"."motivo" IS '';
COMMENT ON COLUMN "movimiento_inventario"."fecha_hora" IS '';


CREATE TABLE "producto" (
	"id_producto" SERIAL NOT NULL,
	"nombre_producto" VARCHAR(150) NOT NULL,
	"stock_actual" INTEGER NOT NULL DEFAULT 0,
	"stock_minimo_alerta" INTEGER NOT NULL DEFAULT 10,
	"precio_venta" NUMERIC(10,2) NOT NULL,
	"imagen" VARCHAR(255) NULL DEFAULT 'default.png',
	"descripcion" TEXT NULL DEFAULT NULL,
	"deleted_at" TIMESTAMP NULL DEFAULT NULL,
	"imagen_path" VARCHAR(255) NULL DEFAULT NULL
)
;
COMMENT ON COLUMN "producto"."id_producto" IS '';
COMMENT ON COLUMN "producto"."nombre_producto" IS '';
COMMENT ON COLUMN "producto"."stock_actual" IS '';
COMMENT ON COLUMN "producto"."stock_minimo_alerta" IS '';
COMMENT ON COLUMN "producto"."precio_venta" IS '';
COMMENT ON COLUMN "producto"."imagen" IS '';
COMMENT ON COLUMN "producto"."descripcion" IS '';
COMMENT ON COLUMN "producto"."deleted_at" IS '';
COMMENT ON COLUMN "producto"."imagen_path" IS '';

CREATE TABLE "producto_proveedor" (
	"id_producto" INTEGER NOT NULL,
	"id_proveedor" INTEGER NOT NULL,
	"precio_compra" NUMERIC(10,2) NOT NULL
)
;
COMMENT ON COLUMN "producto_proveedor"."id_producto" IS '';
COMMENT ON COLUMN "producto_proveedor"."id_proveedor" IS '';
COMMENT ON COLUMN "producto_proveedor"."precio_compra" IS '';

CREATE TABLE "proveedor" (
	"id_proveedor" SERIAL NOT NULL,
	"nombre_empresa" VARCHAR(150) NOT NULL,
	"contacto" VARCHAR(150) NULL DEFAULT NULL,
	"telefono" VARCHAR(20) NULL DEFAULT NULL,
	"direccion" VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY ("id_proveedor")
)
;
COMMENT ON COLUMN "proveedor"."id_proveedor" IS '';
COMMENT ON COLUMN "proveedor"."nombre_empresa" IS '';
COMMENT ON COLUMN "proveedor"."contacto" IS '';
COMMENT ON COLUMN "proveedor"."telefono" IS '';
COMMENT ON COLUMN "proveedor"."direccion" IS '';

CREATE TABLE "venta" (
	"id_venta" SERIAL NOT NULL,
	"fecha_hora" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	"cliente_nombre" VARCHAR(100) NULL DEFAULT NULL::character varying,
	"cliente_documento" VARCHAR(20) NULL DEFAULT NULL::character varying,
	"metodo_pago" VARCHAR(50) NOT NULL,
	"total_venta" NUMERIC(10,2) NOT NULL
)
;
COMMENT ON COLUMN "venta"."id_venta" IS '';
COMMENT ON COLUMN "venta"."fecha_hora" IS '';
COMMENT ON COLUMN "venta"."cliente_nombre" IS '';
COMMENT ON COLUMN "venta"."cliente_documento" IS '';
COMMENT ON COLUMN "venta"."metodo_pago" IS '';
COMMENT ON COLUMN "venta"."total_venta" IS '';