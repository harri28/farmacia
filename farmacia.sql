--
-- PostgreSQL database dump
--

\restrict 5YglrXJhL1Fhfh5coqHbFzBelMlQjjV80Rtgyvadb0EyIVlebzY54bgYa4p6j6E

-- Dumped from database version 17.9
-- Dumped by pg_dump version 17.9

-- Started on 2026-05-29 23:26:28

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 11 (class 2615 OID 20686)
-- Name: generic_pharma_alonso_de_alvarado_2; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA generic_pharma_alonso_de_alvarado_2;


ALTER SCHEMA generic_pharma_alonso_de_alvarado_2 OWNER TO postgres;

--
-- TOC entry 12 (class 2615 OID 20982)
-- Name: generic_pharma_jr_amorarca_129_mora; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA generic_pharma_jr_amorarca_129_mora;


ALTER SCHEMA generic_pharma_jr_amorarca_129_mora OWNER TO postgres;

--
-- TOC entry 7 (class 2615 OID 19325)
-- Name: generic_pharma_jr_lima; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA generic_pharma_jr_lima;


ALTER SCHEMA generic_pharma_jr_lima OWNER TO postgres;

--
-- TOC entry 10 (class 2615 OID 20389)
-- Name: generic_pharma_jr_lima_tambo_408; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA generic_pharma_jr_lima_tambo_408;


ALTER SCHEMA generic_pharma_jr_lima_tambo_408 OWNER TO postgres;

--
-- TOC entry 13 (class 2615 OID 21764)
-- Name: generic_pharma_sucursal_de_prueba; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA generic_pharma_sucursal_de_prueba;


ALTER SCHEMA generic_pharma_sucursal_de_prueba OWNER TO postgres;

--
-- TOC entry 8 (class 2615 OID 19521)
-- Name: mari_boticas_sac_nueva_cajamarca; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA mari_boticas_sac_nueva_cajamarca;


ALTER SCHEMA mari_boticas_sac_nueva_cajamarca OWNER TO postgres;

--
-- TOC entry 9 (class 2615 OID 19716)
-- Name: mari_boticas_sac_rioja; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA mari_boticas_sac_rioja;


ALTER SCHEMA mari_boticas_sac_rioja OWNER TO postgres;

--
-- TOC entry 2 (class 3079 OID 18998)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 6776 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 386 (class 1259 OID 20848)
-- Name: caja_movimientos; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.caja_movimientos OWNER TO postgres;

--
-- TOC entry 385 (class 1259 OID 20847)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6777 (class 0 OID 0)
-- Dependencies: 385
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.caja_movimientos_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.caja_movimientos.id;


--
-- TOC entry 374 (class 1259 OID 20738)
-- Name: cajas; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.cajas OWNER TO postgres;

--
-- TOC entry 373 (class 1259 OID 20737)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6778 (class 0 OID 0)
-- Dependencies: 373
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.cajas_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.cajas.id;


--
-- TOC entry 368 (class 1259 OID 20688)
-- Name: categorias; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.categorias OWNER TO postgres;

--
-- TOC entry 367 (class 1259 OID 20687)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6779 (class 0 OID 0)
-- Dependencies: 367
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.categorias_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.categorias.id;


--
-- TOC entry 372 (class 1259 OID 20725)
-- Name: clientes; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.clientes OWNER TO postgres;

--
-- TOC entry 371 (class 1259 OID 20724)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6780 (class 0 OID 0)
-- Dependencies: 371
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.clientes_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.clientes.id;


--
-- TOC entry 388 (class 1259 OID 20861)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 387 (class 1259 OID 20860)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6781 (class 0 OID 0)
-- Dependencies: 387
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos.id;


--
-- TOC entry 395 (class 1259 OID 20922)
-- Name: cuentas_por_pagar; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar (
    id integer NOT NULL,
    proveedor_id integer,
    ingreso_id integer,
    orden_compra_id integer,
    numero_doc character varying(50),
    monto_total numeric(10,2) NOT NULL,
    monto_pagado numeric(10,2) DEFAULT 0,
    monto_pendiente numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying,
    fecha_vencimiento date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar OWNER TO postgres;

--
-- TOC entry 394 (class 1259 OID 20921)
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar_id_seq OWNER TO postgres;

--
-- TOC entry 6782 (class 0 OID 0)
-- Dependencies: 394
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar.id;


--
-- TOC entry 399 (class 1259 OID 20969)
-- Name: gastos; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.gastos OWNER TO postgres;

--
-- TOC entry 398 (class 1259 OID 20968)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6783 (class 0 OID 0)
-- Dependencies: 398
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.gastos_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.gastos.id;


--
-- TOC entry 384 (class 1259 OID 20830)
-- Name: ingreso_detalles; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 383 (class 1259 OID 20829)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6784 (class 0 OID 0)
-- Dependencies: 383
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ingreso_detalles_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.ingreso_detalles.id;


--
-- TOC entry 382 (class 1259 OID 20811)
-- Name: ingresos; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    orden_compra_id integer
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.ingresos OWNER TO postgres;

--
-- TOC entry 381 (class 1259 OID 20810)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6785 (class 0 OID 0)
-- Dependencies: 381
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ingresos_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.ingresos.id;


--
-- TOC entry 393 (class 1259 OID 20905)
-- Name: orden_compra_detalles; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.orden_compra_detalles (
    id integer NOT NULL,
    orden_id integer NOT NULL,
    producto_id integer,
    descripcion character varying(200),
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.orden_compra_detalles OWNER TO postgres;

--
-- TOC entry 392 (class 1259 OID 20904)
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.orden_compra_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.orden_compra_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6786 (class 0 OID 0)
-- Dependencies: 392
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.orden_compra_detalles_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.orden_compra_detalles.id;


--
-- TOC entry 391 (class 1259 OID 20882)
-- Name: ordenes_compra; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.ordenes_compra (
    id integer NOT NULL,
    numero_orden character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    estado character varying(20) DEFAULT 'borrador'::character varying,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    dias_credito integer DEFAULT 0,
    subtotal numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) DEFAULT 0,
    observaciones text,
    fecha_entrega date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.ordenes_compra OWNER TO postgres;

--
-- TOC entry 390 (class 1259 OID 20881)
-- Name: ordenes_compra_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.ordenes_compra_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ordenes_compra_id_seq OWNER TO postgres;

--
-- TOC entry 6787 (class 0 OID 0)
-- Dependencies: 390
-- Name: ordenes_compra_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ordenes_compra_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.ordenes_compra.id;


--
-- TOC entry 397 (class 1259 OID 20947)
-- Name: pagos_proveedor; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.pagos_proveedor (
    id integer NOT NULL,
    cuenta_id integer NOT NULL,
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    referencia character varying(100),
    usuario_id integer,
    notas text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.pagos_proveedor OWNER TO postgres;

--
-- TOC entry 396 (class 1259 OID 20946)
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.pagos_proveedor_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.pagos_proveedor_id_seq OWNER TO postgres;

--
-- TOC entry 6788 (class 0 OID 0)
-- Dependencies: 396
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.pagos_proveedor_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.pagos_proveedor.id;


--
-- TOC entry 370 (class 1259 OID 20699)
-- Name: productos; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.productos OWNER TO postgres;

--
-- TOC entry 369 (class 1259 OID 20698)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6789 (class 0 OID 0)
-- Dependencies: 369
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.productos_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.productos.id;


--
-- TOC entry 380 (class 1259 OID 20798)
-- Name: proveedores; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.proveedores OWNER TO postgres;

--
-- TOC entry 379 (class 1259 OID 20797)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6790 (class 0 OID 0)
-- Dependencies: 379
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.proveedores_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.proveedores.id;


--
-- TOC entry 389 (class 1259 OID 20875)
-- Name: series_comprobantes; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.series_comprobantes OWNER TO postgres;

--
-- TOC entry 378 (class 1259 OID 20779)
-- Name: venta_detalles; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.venta_detalles OWNER TO postgres;

--
-- TOC entry 377 (class 1259 OID 20778)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6791 (class 0 OID 0)
-- Dependencies: 377
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.venta_detalles_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.venta_detalles.id;


--
-- TOC entry 376 (class 1259 OID 20750)
-- Name: ventas; Type: TABLE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE TABLE generic_pharma_alonso_de_alvarado_2.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_alonso_de_alvarado_2.ventas OWNER TO postgres;

--
-- TOC entry 375 (class 1259 OID 20749)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

CREATE SEQUENCE generic_pharma_alonso_de_alvarado_2.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6792 (class 0 OID 0)
-- Dependencies: 375
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER SEQUENCE generic_pharma_alonso_de_alvarado_2.ventas_id_seq OWNED BY generic_pharma_alonso_de_alvarado_2.ventas.id;


--
-- TOC entry 419 (class 1259 OID 21144)
-- Name: caja_movimientos; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.caja_movimientos OWNER TO postgres;

--
-- TOC entry 418 (class 1259 OID 21143)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6793 (class 0 OID 0)
-- Dependencies: 418
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.caja_movimientos_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.caja_movimientos.id;


--
-- TOC entry 407 (class 1259 OID 21034)
-- Name: cajas; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.cajas OWNER TO postgres;

--
-- TOC entry 406 (class 1259 OID 21033)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6794 (class 0 OID 0)
-- Dependencies: 406
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.cajas_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.cajas.id;


--
-- TOC entry 401 (class 1259 OID 20984)
-- Name: categorias; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.categorias OWNER TO postgres;

--
-- TOC entry 400 (class 1259 OID 20983)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6795 (class 0 OID 0)
-- Dependencies: 400
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.categorias_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.categorias.id;


--
-- TOC entry 405 (class 1259 OID 21021)
-- Name: clientes; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.clientes OWNER TO postgres;

--
-- TOC entry 404 (class 1259 OID 21020)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6796 (class 0 OID 0)
-- Dependencies: 404
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.clientes_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.clientes.id;


--
-- TOC entry 421 (class 1259 OID 21157)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 420 (class 1259 OID 21156)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6797 (class 0 OID 0)
-- Dependencies: 420
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos.id;


--
-- TOC entry 428 (class 1259 OID 21218)
-- Name: cuentas_por_pagar; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar (
    id integer NOT NULL,
    proveedor_id integer,
    ingreso_id integer,
    orden_compra_id integer,
    numero_doc character varying(50),
    monto_total numeric(10,2) NOT NULL,
    monto_pagado numeric(10,2) DEFAULT 0,
    monto_pendiente numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying,
    fecha_vencimiento date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar OWNER TO postgres;

--
-- TOC entry 427 (class 1259 OID 21217)
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar_id_seq OWNER TO postgres;

--
-- TOC entry 6798 (class 0 OID 0)
-- Dependencies: 427
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar.id;


--
-- TOC entry 432 (class 1259 OID 21265)
-- Name: gastos; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.gastos OWNER TO postgres;

--
-- TOC entry 431 (class 1259 OID 21264)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6799 (class 0 OID 0)
-- Dependencies: 431
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.gastos_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.gastos.id;


--
-- TOC entry 417 (class 1259 OID 21126)
-- Name: ingreso_detalles; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 416 (class 1259 OID 21125)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6800 (class 0 OID 0)
-- Dependencies: 416
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ingreso_detalles_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.ingreso_detalles.id;


--
-- TOC entry 415 (class 1259 OID 21107)
-- Name: ingresos; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    orden_compra_id integer
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.ingresos OWNER TO postgres;

--
-- TOC entry 414 (class 1259 OID 21106)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6801 (class 0 OID 0)
-- Dependencies: 414
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ingresos_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.ingresos.id;


--
-- TOC entry 426 (class 1259 OID 21201)
-- Name: orden_compra_detalles; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.orden_compra_detalles (
    id integer NOT NULL,
    orden_id integer NOT NULL,
    producto_id integer,
    descripcion character varying(200),
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.orden_compra_detalles OWNER TO postgres;

--
-- TOC entry 425 (class 1259 OID 21200)
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.orden_compra_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.orden_compra_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6802 (class 0 OID 0)
-- Dependencies: 425
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.orden_compra_detalles_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.orden_compra_detalles.id;


--
-- TOC entry 424 (class 1259 OID 21178)
-- Name: ordenes_compra; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.ordenes_compra (
    id integer NOT NULL,
    numero_orden character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    estado character varying(20) DEFAULT 'borrador'::character varying,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    dias_credito integer DEFAULT 0,
    subtotal numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) DEFAULT 0,
    observaciones text,
    fecha_entrega date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.ordenes_compra OWNER TO postgres;

--
-- TOC entry 423 (class 1259 OID 21177)
-- Name: ordenes_compra_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.ordenes_compra_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ordenes_compra_id_seq OWNER TO postgres;

--
-- TOC entry 6803 (class 0 OID 0)
-- Dependencies: 423
-- Name: ordenes_compra_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ordenes_compra_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.ordenes_compra.id;


--
-- TOC entry 430 (class 1259 OID 21243)
-- Name: pagos_proveedor; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.pagos_proveedor (
    id integer NOT NULL,
    cuenta_id integer NOT NULL,
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    referencia character varying(100),
    usuario_id integer,
    notas text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.pagos_proveedor OWNER TO postgres;

--
-- TOC entry 429 (class 1259 OID 21242)
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.pagos_proveedor_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.pagos_proveedor_id_seq OWNER TO postgres;

--
-- TOC entry 6804 (class 0 OID 0)
-- Dependencies: 429
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.pagos_proveedor_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.pagos_proveedor.id;


--
-- TOC entry 403 (class 1259 OID 20995)
-- Name: productos; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.productos OWNER TO postgres;

--
-- TOC entry 402 (class 1259 OID 20994)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6805 (class 0 OID 0)
-- Dependencies: 402
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.productos_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.productos.id;


--
-- TOC entry 413 (class 1259 OID 21094)
-- Name: proveedores; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.proveedores OWNER TO postgres;

--
-- TOC entry 412 (class 1259 OID 21093)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6806 (class 0 OID 0)
-- Dependencies: 412
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.proveedores_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.proveedores.id;


--
-- TOC entry 422 (class 1259 OID 21171)
-- Name: series_comprobantes; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.series_comprobantes OWNER TO postgres;

--
-- TOC entry 411 (class 1259 OID 21075)
-- Name: venta_detalles; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.venta_detalles OWNER TO postgres;

--
-- TOC entry 410 (class 1259 OID 21074)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6807 (class 0 OID 0)
-- Dependencies: 410
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.venta_detalles_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.venta_detalles.id;


--
-- TOC entry 409 (class 1259 OID 21046)
-- Name: ventas; Type: TABLE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE TABLE generic_pharma_jr_amorarca_129_mora.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_amorarca_129_mora.ventas OWNER TO postgres;

--
-- TOC entry 408 (class 1259 OID 21045)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_amorarca_129_mora.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6808 (class 0 OID 0)
-- Dependencies: 408
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_amorarca_129_mora.ventas_id_seq OWNED BY generic_pharma_jr_amorarca_129_mora.ventas.id;


--
-- TOC entry 276 (class 1259 OID 19487)
-- Name: caja_movimientos; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.caja_movimientos OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 19486)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6809 (class 0 OID 0)
-- Dependencies: 275
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.caja_movimientos_id_seq OWNED BY generic_pharma_jr_lima.caja_movimientos.id;


--
-- TOC entry 264 (class 1259 OID 19377)
-- Name: cajas; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.cajas OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 19376)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6810 (class 0 OID 0)
-- Dependencies: 263
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.cajas_id_seq OWNED BY generic_pharma_jr_lima.cajas.id;


--
-- TOC entry 258 (class 1259 OID 19327)
-- Name: categorias; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.categorias OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 19326)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6811 (class 0 OID 0)
-- Dependencies: 257
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.categorias_id_seq OWNED BY generic_pharma_jr_lima.categorias.id;


--
-- TOC entry 262 (class 1259 OID 19364)
-- Name: clientes; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.clientes OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 19363)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6812 (class 0 OID 0)
-- Dependencies: 261
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.clientes_id_seq OWNED BY generic_pharma_jr_lima.clientes.id;


--
-- TOC entry 278 (class 1259 OID 19500)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 19499)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6813 (class 0 OID 0)
-- Dependencies: 277
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.comprobantes_electronicos_id_seq OWNED BY generic_pharma_jr_lima.comprobantes_electronicos.id;


--
-- TOC entry 329 (class 1259 OID 19927)
-- Name: gastos; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.gastos OWNER TO postgres;

--
-- TOC entry 328 (class 1259 OID 19926)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6814 (class 0 OID 0)
-- Dependencies: 328
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.gastos_id_seq OWNED BY generic_pharma_jr_lima.gastos.id;


--
-- TOC entry 274 (class 1259 OID 19469)
-- Name: ingreso_detalles; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 19468)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6815 (class 0 OID 0)
-- Dependencies: 273
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.ingreso_detalles_id_seq OWNED BY generic_pharma_jr_lima.ingreso_detalles.id;


--
-- TOC entry 272 (class 1259 OID 19450)
-- Name: ingresos; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.ingresos OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 19449)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6816 (class 0 OID 0)
-- Dependencies: 271
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.ingresos_id_seq OWNED BY generic_pharma_jr_lima.ingresos.id;


--
-- TOC entry 260 (class 1259 OID 19338)
-- Name: productos; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE generic_pharma_jr_lima.productos OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 19337)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6817 (class 0 OID 0)
-- Dependencies: 259
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.productos_id_seq OWNED BY generic_pharma_jr_lima.productos.id;


--
-- TOC entry 270 (class 1259 OID 19437)
-- Name: proveedores; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.proveedores OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 19436)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6818 (class 0 OID 0)
-- Dependencies: 269
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.proveedores_id_seq OWNED BY generic_pharma_jr_lima.proveedores.id;


--
-- TOC entry 279 (class 1259 OID 19514)
-- Name: series_comprobantes; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE generic_pharma_jr_lima.series_comprobantes OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 19418)
-- Name: venta_detalles; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.venta_detalles OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 19417)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6819 (class 0 OID 0)
-- Dependencies: 267
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.venta_detalles_id_seq OWNED BY generic_pharma_jr_lima.venta_detalles.id;


--
-- TOC entry 266 (class 1259 OID 19389)
-- Name: ventas; Type: TABLE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima.ventas OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 19388)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6820 (class 0 OID 0)
-- Dependencies: 265
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima.ventas_id_seq OWNED BY generic_pharma_jr_lima.ventas.id;


--
-- TOC entry 353 (class 1259 OID 20551)
-- Name: caja_movimientos; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.caja_movimientos OWNER TO postgres;

--
-- TOC entry 352 (class 1259 OID 20550)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6821 (class 0 OID 0)
-- Dependencies: 352
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.caja_movimientos_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.caja_movimientos.id;


--
-- TOC entry 341 (class 1259 OID 20441)
-- Name: cajas; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.cajas OWNER TO postgres;

--
-- TOC entry 340 (class 1259 OID 20440)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6822 (class 0 OID 0)
-- Dependencies: 340
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.cajas_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.cajas.id;


--
-- TOC entry 335 (class 1259 OID 20391)
-- Name: categorias; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.categorias OWNER TO postgres;

--
-- TOC entry 334 (class 1259 OID 20390)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6823 (class 0 OID 0)
-- Dependencies: 334
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.categorias_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.categorias.id;


--
-- TOC entry 339 (class 1259 OID 20428)
-- Name: clientes; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.clientes OWNER TO postgres;

--
-- TOC entry 338 (class 1259 OID 20427)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6824 (class 0 OID 0)
-- Dependencies: 338
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.clientes_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.clientes.id;


--
-- TOC entry 355 (class 1259 OID 20564)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 354 (class 1259 OID 20563)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6825 (class 0 OID 0)
-- Dependencies: 354
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.comprobantes_electronicos_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.comprobantes_electronicos.id;


--
-- TOC entry 362 (class 1259 OID 20625)
-- Name: cuentas_por_pagar; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.cuentas_por_pagar (
    id integer NOT NULL,
    proveedor_id integer,
    ingreso_id integer,
    orden_compra_id integer,
    numero_doc character varying(50),
    monto_total numeric(10,2) NOT NULL,
    monto_pagado numeric(10,2) DEFAULT 0,
    monto_pendiente numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying,
    fecha_vencimiento date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.cuentas_por_pagar OWNER TO postgres;

--
-- TOC entry 361 (class 1259 OID 20624)
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.cuentas_por_pagar_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.cuentas_por_pagar_id_seq OWNER TO postgres;

--
-- TOC entry 6826 (class 0 OID 0)
-- Dependencies: 361
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.cuentas_por_pagar_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar.id;


--
-- TOC entry 366 (class 1259 OID 20672)
-- Name: gastos; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.gastos OWNER TO postgres;

--
-- TOC entry 365 (class 1259 OID 20671)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6827 (class 0 OID 0)
-- Dependencies: 365
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.gastos_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.gastos.id;


--
-- TOC entry 351 (class 1259 OID 20533)
-- Name: ingreso_detalles; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 350 (class 1259 OID 20532)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6828 (class 0 OID 0)
-- Dependencies: 350
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ingreso_detalles_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.ingreso_detalles.id;


--
-- TOC entry 349 (class 1259 OID 20514)
-- Name: ingresos; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    orden_compra_id integer
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.ingresos OWNER TO postgres;

--
-- TOC entry 348 (class 1259 OID 20513)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6829 (class 0 OID 0)
-- Dependencies: 348
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ingresos_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.ingresos.id;


--
-- TOC entry 360 (class 1259 OID 20608)
-- Name: orden_compra_detalles; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.orden_compra_detalles (
    id integer NOT NULL,
    orden_id integer NOT NULL,
    producto_id integer,
    descripcion character varying(200),
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.orden_compra_detalles OWNER TO postgres;

--
-- TOC entry 359 (class 1259 OID 20607)
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.orden_compra_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.orden_compra_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6830 (class 0 OID 0)
-- Dependencies: 359
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.orden_compra_detalles_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.orden_compra_detalles.id;


--
-- TOC entry 358 (class 1259 OID 20585)
-- Name: ordenes_compra; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.ordenes_compra (
    id integer NOT NULL,
    numero_orden character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    estado character varying(20) DEFAULT 'borrador'::character varying,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    dias_credito integer DEFAULT 0,
    subtotal numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) DEFAULT 0,
    observaciones text,
    fecha_entrega date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.ordenes_compra OWNER TO postgres;

--
-- TOC entry 357 (class 1259 OID 20584)
-- Name: ordenes_compra_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.ordenes_compra_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ordenes_compra_id_seq OWNER TO postgres;

--
-- TOC entry 6831 (class 0 OID 0)
-- Dependencies: 357
-- Name: ordenes_compra_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ordenes_compra_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.ordenes_compra.id;


--
-- TOC entry 364 (class 1259 OID 20650)
-- Name: pagos_proveedor; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.pagos_proveedor (
    id integer NOT NULL,
    cuenta_id integer NOT NULL,
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    referencia character varying(100),
    usuario_id integer,
    notas text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.pagos_proveedor OWNER TO postgres;

--
-- TOC entry 363 (class 1259 OID 20649)
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.pagos_proveedor_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.pagos_proveedor_id_seq OWNER TO postgres;

--
-- TOC entry 6832 (class 0 OID 0)
-- Dependencies: 363
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.pagos_proveedor_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.pagos_proveedor.id;


--
-- TOC entry 337 (class 1259 OID 20402)
-- Name: productos; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.productos OWNER TO postgres;

--
-- TOC entry 336 (class 1259 OID 20401)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6833 (class 0 OID 0)
-- Dependencies: 336
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.productos_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.productos.id;


--
-- TOC entry 347 (class 1259 OID 20501)
-- Name: proveedores; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.proveedores OWNER TO postgres;

--
-- TOC entry 346 (class 1259 OID 20500)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6834 (class 0 OID 0)
-- Dependencies: 346
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.proveedores_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.proveedores.id;


--
-- TOC entry 356 (class 1259 OID 20578)
-- Name: series_comprobantes; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.series_comprobantes OWNER TO postgres;

--
-- TOC entry 345 (class 1259 OID 20482)
-- Name: venta_detalles; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.venta_detalles OWNER TO postgres;

--
-- TOC entry 344 (class 1259 OID 20481)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6835 (class 0 OID 0)
-- Dependencies: 344
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.venta_detalles_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.venta_detalles.id;


--
-- TOC entry 343 (class 1259 OID 20453)
-- Name: ventas; Type: TABLE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE TABLE generic_pharma_jr_lima_tambo_408.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_jr_lima_tambo_408.ventas OWNER TO postgres;

--
-- TOC entry 342 (class 1259 OID 20452)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

CREATE SEQUENCE generic_pharma_jr_lima_tambo_408.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6836 (class 0 OID 0)
-- Dependencies: 342
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER SEQUENCE generic_pharma_jr_lima_tambo_408.ventas_id_seq OWNED BY generic_pharma_jr_lima_tambo_408.ventas.id;


--
-- TOC entry 456 (class 1259 OID 21926)
-- Name: caja_movimientos; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.caja_movimientos OWNER TO postgres;

--
-- TOC entry 455 (class 1259 OID 21925)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6837 (class 0 OID 0)
-- Dependencies: 455
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.caja_movimientos_id_seq OWNED BY generic_pharma_sucursal_de_prueba.caja_movimientos.id;


--
-- TOC entry 444 (class 1259 OID 21816)
-- Name: cajas; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.cajas OWNER TO postgres;

--
-- TOC entry 443 (class 1259 OID 21815)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6838 (class 0 OID 0)
-- Dependencies: 443
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.cajas_id_seq OWNED BY generic_pharma_sucursal_de_prueba.cajas.id;


--
-- TOC entry 438 (class 1259 OID 21766)
-- Name: categorias; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.categorias OWNER TO postgres;

--
-- TOC entry 437 (class 1259 OID 21765)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6839 (class 0 OID 0)
-- Dependencies: 437
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.categorias_id_seq OWNED BY generic_pharma_sucursal_de_prueba.categorias.id;


--
-- TOC entry 442 (class 1259 OID 21803)
-- Name: clientes; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.clientes OWNER TO postgres;

--
-- TOC entry 441 (class 1259 OID 21802)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6840 (class 0 OID 0)
-- Dependencies: 441
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.clientes_id_seq OWNED BY generic_pharma_sucursal_de_prueba.clientes.id;


--
-- TOC entry 458 (class 1259 OID 21939)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 457 (class 1259 OID 21938)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6841 (class 0 OID 0)
-- Dependencies: 457
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.comprobantes_electronicos_id_seq OWNED BY generic_pharma_sucursal_de_prueba.comprobantes_electronicos.id;


--
-- TOC entry 465 (class 1259 OID 22000)
-- Name: cuentas_por_pagar; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.cuentas_por_pagar (
    id integer NOT NULL,
    proveedor_id integer,
    ingreso_id integer,
    orden_compra_id integer,
    numero_doc character varying(50),
    monto_total numeric(10,2) NOT NULL,
    monto_pagado numeric(10,2) DEFAULT 0,
    monto_pendiente numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying,
    fecha_vencimiento date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.cuentas_por_pagar OWNER TO postgres;

--
-- TOC entry 464 (class 1259 OID 21999)
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.cuentas_por_pagar_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.cuentas_por_pagar_id_seq OWNER TO postgres;

--
-- TOC entry 6842 (class 0 OID 0)
-- Dependencies: 464
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.cuentas_por_pagar_id_seq OWNED BY generic_pharma_sucursal_de_prueba.cuentas_por_pagar.id;


--
-- TOC entry 469 (class 1259 OID 22047)
-- Name: gastos; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.gastos OWNER TO postgres;

--
-- TOC entry 468 (class 1259 OID 22046)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6843 (class 0 OID 0)
-- Dependencies: 468
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.gastos_id_seq OWNED BY generic_pharma_sucursal_de_prueba.gastos.id;


--
-- TOC entry 454 (class 1259 OID 21908)
-- Name: ingreso_detalles; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 453 (class 1259 OID 21907)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6844 (class 0 OID 0)
-- Dependencies: 453
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ingreso_detalles_id_seq OWNED BY generic_pharma_sucursal_de_prueba.ingreso_detalles.id;


--
-- TOC entry 452 (class 1259 OID 21889)
-- Name: ingresos; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    orden_compra_id integer
);


ALTER TABLE generic_pharma_sucursal_de_prueba.ingresos OWNER TO postgres;

--
-- TOC entry 451 (class 1259 OID 21888)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6845 (class 0 OID 0)
-- Dependencies: 451
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ingresos_id_seq OWNED BY generic_pharma_sucursal_de_prueba.ingresos.id;


--
-- TOC entry 463 (class 1259 OID 21983)
-- Name: orden_compra_detalles; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.orden_compra_detalles (
    id integer NOT NULL,
    orden_id integer NOT NULL,
    producto_id integer,
    descripcion character varying(200),
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL
);


ALTER TABLE generic_pharma_sucursal_de_prueba.orden_compra_detalles OWNER TO postgres;

--
-- TOC entry 462 (class 1259 OID 21982)
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.orden_compra_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.orden_compra_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6846 (class 0 OID 0)
-- Dependencies: 462
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.orden_compra_detalles_id_seq OWNED BY generic_pharma_sucursal_de_prueba.orden_compra_detalles.id;


--
-- TOC entry 461 (class 1259 OID 21960)
-- Name: ordenes_compra; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.ordenes_compra (
    id integer NOT NULL,
    numero_orden character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    estado character varying(20) DEFAULT 'borrador'::character varying,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    dias_credito integer DEFAULT 0,
    subtotal numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) DEFAULT 0,
    observaciones text,
    fecha_entrega date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.ordenes_compra OWNER TO postgres;

--
-- TOC entry 460 (class 1259 OID 21959)
-- Name: ordenes_compra_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.ordenes_compra_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ordenes_compra_id_seq OWNER TO postgres;

--
-- TOC entry 6847 (class 0 OID 0)
-- Dependencies: 460
-- Name: ordenes_compra_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ordenes_compra_id_seq OWNED BY generic_pharma_sucursal_de_prueba.ordenes_compra.id;


--
-- TOC entry 467 (class 1259 OID 22025)
-- Name: pagos_proveedor; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.pagos_proveedor (
    id integer NOT NULL,
    cuenta_id integer NOT NULL,
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    referencia character varying(100),
    usuario_id integer,
    notas text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.pagos_proveedor OWNER TO postgres;

--
-- TOC entry 466 (class 1259 OID 22024)
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.pagos_proveedor_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.pagos_proveedor_id_seq OWNER TO postgres;

--
-- TOC entry 6848 (class 0 OID 0)
-- Dependencies: 466
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.pagos_proveedor_id_seq OWNED BY generic_pharma_sucursal_de_prueba.pagos_proveedor.id;


--
-- TOC entry 440 (class 1259 OID 21777)
-- Name: productos; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE generic_pharma_sucursal_de_prueba.productos OWNER TO postgres;

--
-- TOC entry 439 (class 1259 OID 21776)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6849 (class 0 OID 0)
-- Dependencies: 439
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.productos_id_seq OWNED BY generic_pharma_sucursal_de_prueba.productos.id;


--
-- TOC entry 450 (class 1259 OID 21876)
-- Name: proveedores; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.proveedores OWNER TO postgres;

--
-- TOC entry 449 (class 1259 OID 21875)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6850 (class 0 OID 0)
-- Dependencies: 449
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.proveedores_id_seq OWNED BY generic_pharma_sucursal_de_prueba.proveedores.id;


--
-- TOC entry 459 (class 1259 OID 21953)
-- Name: series_comprobantes; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE generic_pharma_sucursal_de_prueba.series_comprobantes OWNER TO postgres;

--
-- TOC entry 448 (class 1259 OID 21857)
-- Name: venta_detalles; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.venta_detalles OWNER TO postgres;

--
-- TOC entry 447 (class 1259 OID 21856)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6851 (class 0 OID 0)
-- Dependencies: 447
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.venta_detalles_id_seq OWNED BY generic_pharma_sucursal_de_prueba.venta_detalles.id;


--
-- TOC entry 446 (class 1259 OID 21828)
-- Name: ventas; Type: TABLE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE TABLE generic_pharma_sucursal_de_prueba.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE generic_pharma_sucursal_de_prueba.ventas OWNER TO postgres;

--
-- TOC entry 445 (class 1259 OID 21827)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

CREATE SEQUENCE generic_pharma_sucursal_de_prueba.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6852 (class 0 OID 0)
-- Dependencies: 445
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER SEQUENCE generic_pharma_sucursal_de_prueba.ventas_id_seq OWNED BY generic_pharma_sucursal_de_prueba.ventas.id;


--
-- TOC entry 299 (class 1259 OID 19683)
-- Name: caja_movimientos; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.caja_movimientos OWNER TO postgres;

--
-- TOC entry 298 (class 1259 OID 19682)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6853 (class 0 OID 0)
-- Dependencies: 298
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.caja_movimientos_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.caja_movimientos.id;


--
-- TOC entry 287 (class 1259 OID 19573)
-- Name: cajas; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.cajas OWNER TO postgres;

--
-- TOC entry 286 (class 1259 OID 19572)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6854 (class 0 OID 0)
-- Dependencies: 286
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.cajas_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.cajas.id;


--
-- TOC entry 281 (class 1259 OID 19523)
-- Name: categorias; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.categorias OWNER TO postgres;

--
-- TOC entry 280 (class 1259 OID 19522)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6855 (class 0 OID 0)
-- Dependencies: 280
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.categorias_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.categorias.id;


--
-- TOC entry 285 (class 1259 OID 19560)
-- Name: clientes; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.clientes OWNER TO postgres;

--
-- TOC entry 284 (class 1259 OID 19559)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6856 (class 0 OID 0)
-- Dependencies: 284
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.clientes_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.clientes.id;


--
-- TOC entry 301 (class 1259 OID 19696)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 300 (class 1259 OID 19695)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6857 (class 0 OID 0)
-- Dependencies: 300
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos.id;


--
-- TOC entry 331 (class 1259 OID 19941)
-- Name: gastos; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.gastos OWNER TO postgres;

--
-- TOC entry 330 (class 1259 OID 19940)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6858 (class 0 OID 0)
-- Dependencies: 330
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.gastos_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.gastos.id;


--
-- TOC entry 297 (class 1259 OID 19665)
-- Name: ingreso_detalles; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 296 (class 1259 OID 19664)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6859 (class 0 OID 0)
-- Dependencies: 296
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.ingreso_detalles_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.ingreso_detalles.id;


--
-- TOC entry 295 (class 1259 OID 19646)
-- Name: ingresos; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.ingresos OWNER TO postgres;

--
-- TOC entry 294 (class 1259 OID 19645)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6860 (class 0 OID 0)
-- Dependencies: 294
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.ingresos_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.ingresos.id;


--
-- TOC entry 283 (class 1259 OID 19534)
-- Name: productos; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.productos OWNER TO postgres;

--
-- TOC entry 282 (class 1259 OID 19533)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6861 (class 0 OID 0)
-- Dependencies: 282
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.productos_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.productos.id;


--
-- TOC entry 293 (class 1259 OID 19633)
-- Name: proveedores; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.proveedores OWNER TO postgres;

--
-- TOC entry 292 (class 1259 OID 19632)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6862 (class 0 OID 0)
-- Dependencies: 292
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.proveedores_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.proveedores.id;


--
-- TOC entry 302 (class 1259 OID 19710)
-- Name: series_comprobantes; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.series_comprobantes OWNER TO postgres;

--
-- TOC entry 291 (class 1259 OID 19614)
-- Name: venta_detalles; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.venta_detalles OWNER TO postgres;

--
-- TOC entry 290 (class 1259 OID 19613)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6863 (class 0 OID 0)
-- Dependencies: 290
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.venta_detalles_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.venta_detalles.id;


--
-- TOC entry 289 (class 1259 OID 19585)
-- Name: ventas; Type: TABLE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE TABLE mari_boticas_sac_nueva_cajamarca.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_nueva_cajamarca.ventas OWNER TO postgres;

--
-- TOC entry 288 (class 1259 OID 19584)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_nueva_cajamarca.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6864 (class 0 OID 0)
-- Dependencies: 288
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_nueva_cajamarca.ventas_id_seq OWNED BY mari_boticas_sac_nueva_cajamarca.ventas.id;


--
-- TOC entry 322 (class 1259 OID 19878)
-- Name: caja_movimientos; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    concepto character varying(200),
    monto numeric(10,2) NOT NULL,
    usuario character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.caja_movimientos OWNER TO postgres;

--
-- TOC entry 321 (class 1259 OID 19877)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6865 (class 0 OID 0)
-- Dependencies: 321
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.caja_movimientos_id_seq OWNED BY mari_boticas_sac_rioja.caja_movimientos.id;


--
-- TOC entry 310 (class 1259 OID 19768)
-- Name: cajas; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.cajas OWNER TO postgres;

--
-- TOC entry 309 (class 1259 OID 19767)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6866 (class 0 OID 0)
-- Dependencies: 309
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.cajas_id_seq OWNED BY mari_boticas_sac_rioja.cajas.id;


--
-- TOC entry 304 (class 1259 OID 19718)
-- Name: categorias; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.categorias OWNER TO postgres;

--
-- TOC entry 303 (class 1259 OID 19717)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6867 (class 0 OID 0)
-- Dependencies: 303
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.categorias_id_seq OWNED BY mari_boticas_sac_rioja.categorias.id;


--
-- TOC entry 308 (class 1259 OID 19755)
-- Name: clientes; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.clientes OWNER TO postgres;

--
-- TOC entry 307 (class 1259 OID 19754)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6868 (class 0 OID 0)
-- Dependencies: 307
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.clientes_id_seq OWNED BY mari_boticas_sac_rioja.clientes.id;


--
-- TOC entry 324 (class 1259 OID 19891)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    numero integer NOT NULL,
    numero_completo character varying(20) NOT NULL,
    estado_sunat character varying(100),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 323 (class 1259 OID 19890)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6869 (class 0 OID 0)
-- Dependencies: 323
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.comprobantes_electronicos_id_seq OWNED BY mari_boticas_sac_rioja.comprobantes_electronicos.id;


--
-- TOC entry 333 (class 1259 OID 19955)
-- Name: gastos; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.gastos OWNER TO postgres;

--
-- TOC entry 332 (class 1259 OID 19954)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6870 (class 0 OID 0)
-- Dependencies: 332
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.gastos_id_seq OWNED BY mari_boticas_sac_rioja.gastos.id;


--
-- TOC entry 320 (class 1259 OID 19860)
-- Name: ingreso_detalles; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 319 (class 1259 OID 19859)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6871 (class 0 OID 0)
-- Dependencies: 319
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.ingreso_detalles_id_seq OWNED BY mari_boticas_sac_rioja.ingreso_detalles.id;


--
-- TOC entry 318 (class 1259 OID 19841)
-- Name: ingresos; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    usuario_id integer,
    total numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.ingresos OWNER TO postgres;

--
-- TOC entry 317 (class 1259 OID 19840)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6872 (class 0 OID 0)
-- Dependencies: 317
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.ingresos_id_seq OWNED BY mari_boticas_sac_rioja.ingresos.id;


--
-- TOC entry 306 (class 1259 OID 19729)
-- Name: productos; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE mari_boticas_sac_rioja.productos OWNER TO postgres;

--
-- TOC entry 305 (class 1259 OID 19728)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6873 (class 0 OID 0)
-- Dependencies: 305
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.productos_id_seq OWNED BY mari_boticas_sac_rioja.productos.id;


--
-- TOC entry 316 (class 1259 OID 19828)
-- Name: proveedores; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.proveedores OWNER TO postgres;

--
-- TOC entry 315 (class 1259 OID 19827)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6874 (class 0 OID 0)
-- Dependencies: 315
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.proveedores_id_seq OWNED BY mari_boticas_sac_rioja.proveedores.id;


--
-- TOC entry 325 (class 1259 OID 19905)
-- Name: series_comprobantes; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.series_comprobantes (
    tipo character varying(20) NOT NULL,
    serie character varying(10) NOT NULL,
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE mari_boticas_sac_rioja.series_comprobantes OWNER TO postgres;

--
-- TOC entry 314 (class 1259 OID 19809)
-- Name: venta_detalles; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.venta_detalles OWNER TO postgres;

--
-- TOC entry 313 (class 1259 OID 19808)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6875 (class 0 OID 0)
-- Dependencies: 313
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.venta_detalles_id_seq OWNED BY mari_boticas_sac_rioja.venta_detalles.id;


--
-- TOC entry 312 (class 1259 OID 19780)
-- Name: ventas; Type: TABLE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE TABLE mari_boticas_sac_rioja.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    usuario_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE mari_boticas_sac_rioja.ventas OWNER TO postgres;

--
-- TOC entry 311 (class 1259 OID 19779)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: mari_boticas_sac_rioja; Owner: postgres
--

CREATE SEQUENCE mari_boticas_sac_rioja.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE mari_boticas_sac_rioja.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6876 (class 0 OID 0)
-- Dependencies: 311
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER SEQUENCE mari_boticas_sac_rioja.ventas_id_seq OWNED BY mari_boticas_sac_rioja.ventas.id;


--
-- TOC entry 244 (class 1259 OID 19173)
-- Name: caja_movimientos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.caja_movimientos (
    id integer NOT NULL,
    caja_id integer,
    tipo character varying(20) NOT NULL,
    monto numeric(10,2) NOT NULL,
    concepto character varying(200),
    usuario character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT caja_movimientos_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['ingreso'::character varying, 'egreso'::character varying])::text[])))
);


ALTER TABLE public.caja_movimientos OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 19172)
-- Name: caja_movimientos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.caja_movimientos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.caja_movimientos_id_seq OWNER TO postgres;

--
-- TOC entry 6877 (class 0 OID 0)
-- Dependencies: 243
-- Name: caja_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.caja_movimientos_id_seq OWNED BY public.caja_movimientos.id;


--
-- TOC entry 232 (class 1259 OID 19060)
-- Name: cajas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cajas (
    id integer NOT NULL,
    nombre character varying(100) DEFAULT 'Caja Principal'::character varying,
    saldo_inicial numeric(10,2) DEFAULT 0,
    saldo_actual numeric(10,2) DEFAULT 0,
    estado character varying(20) DEFAULT 'cerrada'::character varying,
    apertura_at timestamp without time zone,
    cierre_at timestamp without time zone,
    usuario_apertura character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.cajas OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 19059)
-- Name: cajas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cajas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cajas_id_seq OWNER TO postgres;

--
-- TOC entry 6878 (class 0 OID 0)
-- Dependencies: 231
-- Name: cajas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cajas_id_seq OWNED BY public.cajas.id;


--
-- TOC entry 226 (class 1259 OID 19010)
-- Name: categorias; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categorias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.categorias OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 19009)
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categorias_id_seq OWNER TO postgres;

--
-- TOC entry 6879 (class 0 OID 0)
-- Dependencies: 225
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;


--
-- TOC entry 230 (class 1259 OID 19047)
-- Name: clientes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.clientes (
    id integer NOT NULL,
    nombres character varying(150) NOT NULL,
    apellidos character varying(150),
    dni character varying(20),
    ruc character varying(20),
    telefono character varying(20),
    email character varying(100),
    direccion text,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.clientes OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 19046)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.clientes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 6880 (class 0 OID 0)
-- Dependencies: 229
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.clientes_id_seq OWNED BY public.clientes.id;


--
-- TOC entry 246 (class 1259 OID 19188)
-- Name: comprobantes_electronicos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comprobantes_electronicos (
    id integer NOT NULL,
    venta_id integer,
    tipo character varying(20),
    serie character varying(10),
    numero integer,
    numero_completo character varying(30),
    estado_sunat character varying(200),
    enlace_del_pdf text,
    enlace_del_xml text,
    enlace_del_cdr text,
    cadena_para_codigo_qr text,
    nubefact_response jsonb,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.comprobantes_electronicos OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 19187)
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.comprobantes_electronicos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.comprobantes_electronicos_id_seq OWNER TO postgres;

--
-- TOC entry 6881 (class 0 OID 0)
-- Dependencies: 245
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.comprobantes_electronicos_id_seq OWNED BY public.comprobantes_electronicos.id;


--
-- TOC entry 327 (class 1259 OID 19913)
-- Name: gastos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gastos (
    id integer NOT NULL,
    caja_id integer,
    descripcion character varying(200) NOT NULL,
    proveedor character varying(150),
    numero_comprobante character varying(50),
    monto numeric(10,2) NOT NULL,
    metodo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    usuario_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gastos OWNER TO postgres;

--
-- TOC entry 326 (class 1259 OID 19912)
-- Name: gastos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gastos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.gastos_id_seq OWNER TO postgres;

--
-- TOC entry 6882 (class 0 OID 0)
-- Dependencies: 326
-- Name: gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gastos_id_seq OWNED BY public.gastos.id;


--
-- TOC entry 242 (class 1259 OID 19155)
-- Name: ingreso_detalles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ingreso_detalles (
    id integer NOT NULL,
    ingreso_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ingreso_detalles OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 19154)
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ingreso_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ingreso_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6883 (class 0 OID 0)
-- Dependencies: 241
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ingreso_detalles_id_seq OWNED BY public.ingreso_detalles.id;


--
-- TOC entry 240 (class 1259 OID 19133)
-- Name: ingresos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ingresos (
    id integer NOT NULL,
    numero_ingreso character varying(20) NOT NULL,
    proveedor_id integer,
    numero_factura character varying(50),
    fecha_factura date,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) DEFAULT 0 NOT NULL,
    estado character varying(20) DEFAULT 'completado'::character varying,
    observaciones text,
    usuario character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ingresos OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 19132)
-- Name: ingresos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ingresos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ingresos_id_seq OWNER TO postgres;

--
-- TOC entry 6884 (class 0 OID 0)
-- Dependencies: 239
-- Name: ingresos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ingresos_id_seq OWNED BY public.ingresos.id;


--
-- TOC entry 434 (class 1259 OID 21731)
-- Name: password_resets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_resets (
    id integer NOT NULL,
    usuario_id integer,
    token character varying(100) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    superadmin_id integer
);


ALTER TABLE public.password_resets OWNER TO postgres;

--
-- TOC entry 433 (class 1259 OID 21730)
-- Name: password_resets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.password_resets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.password_resets_id_seq OWNER TO postgres;

--
-- TOC entry 6885 (class 0 OID 0)
-- Dependencies: 433
-- Name: password_resets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.password_resets_id_seq OWNED BY public.password_resets.id;


--
-- TOC entry 228 (class 1259 OID 19021)
-- Name: productos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.productos (
    id integer NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    descripcion text,
    categoria_id integer,
    precio_compra numeric(10,2) DEFAULT 0,
    precio_venta numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    stock_minimo integer DEFAULT 5,
    unidad character varying(50) DEFAULT 'unidad'::character varying,
    laboratorio character varying(100),
    presentacion character varying(100),
    requiere_receta boolean DEFAULT false,
    activo boolean DEFAULT true,
    favorito boolean DEFAULT false,
    total_vendido integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento date,
    imagen_path text
);


ALTER TABLE public.productos OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 19020)
-- Name: productos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.productos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.productos_id_seq OWNER TO postgres;

--
-- TOC entry 6886 (class 0 OID 0)
-- Dependencies: 227
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.productos_id_seq OWNED BY public.productos.id;


--
-- TOC entry 238 (class 1259 OID 19120)
-- Name: proveedores; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.proveedores (
    id integer NOT NULL,
    ruc character varying(20),
    razon_social character varying(200) NOT NULL,
    nombre_comercial character varying(200),
    telefono character varying(30),
    email character varying(100),
    direccion text,
    contacto_nombre character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.proveedores OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 19119)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.proveedores_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 6887 (class 0 OID 0)
-- Dependencies: 237
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.proveedores_id_seq OWNED BY public.proveedores.id;


--
-- TOC entry 248 (class 1259 OID 19203)
-- Name: series_comprobantes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.series_comprobantes (
    id integer NOT NULL,
    tipo character varying(20),
    serie character varying(10),
    ultimo_numero integer DEFAULT 0
);


ALTER TABLE public.series_comprobantes OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 19202)
-- Name: series_comprobantes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.series_comprobantes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.series_comprobantes_id_seq OWNER TO postgres;

--
-- TOC entry 6888 (class 0 OID 0)
-- Dependencies: 247
-- Name: series_comprobantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.series_comprobantes_id_seq OWNED BY public.series_comprobantes.id;


--
-- TOC entry 252 (class 1259 OID 19262)
-- Name: sucursales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sucursales (
    id integer NOT NULL,
    tenant_id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    schema_name character varying(60) NOT NULL,
    direccion text,
    telefono character varying(20),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    archivada boolean DEFAULT false
);


ALTER TABLE public.sucursales OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 19261)
-- Name: sucursales_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sucursales_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sucursales_id_seq OWNER TO postgres;

--
-- TOC entry 6889 (class 0 OID 0)
-- Dependencies: 251
-- Name: sucursales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sucursales_id_seq OWNED BY public.sucursales.id;


--
-- TOC entry 436 (class 1259 OID 21747)
-- Name: superadmins; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.superadmins (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    apellido character varying(100),
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    email character varying(150),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.superadmins OWNER TO postgres;

--
-- TOC entry 435 (class 1259 OID 21746)
-- Name: superadmins_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.superadmins_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.superadmins_id_seq OWNER TO postgres;

--
-- TOC entry 6890 (class 0 OID 0)
-- Dependencies: 435
-- Name: superadmins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.superadmins_id_seq OWNED BY public.superadmins.id;


--
-- TOC entry 471 (class 1259 OID 49188)
-- Name: tenant_config; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tenant_config (
    id integer NOT NULL,
    tenant_id integer NOT NULL,
    nombre_sistema character varying(100) DEFAULT 'FarmaSystem'::character varying NOT NULL,
    logo_path character varying(255),
    updated_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.tenant_config OWNER TO postgres;

--
-- TOC entry 470 (class 1259 OID 49187)
-- Name: tenant_config_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tenant_config_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tenant_config_id_seq OWNER TO postgres;

--
-- TOC entry 6891 (class 0 OID 0)
-- Dependencies: 470
-- Name: tenant_config_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tenant_config_id_seq OWNED BY public.tenant_config.id;


--
-- TOC entry 250 (class 1259 OID 19250)
-- Name: tenants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tenants (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    slug character varying(30) NOT NULL,
    plan character varying(20) DEFAULT 'basico'::character varying,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    url character varying(200),
    ruc character varying(11),
    telefono character varying(20),
    direccion character varying(300),
    logo character varying(255)
);


ALTER TABLE public.tenants OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 19249)
-- Name: tenants_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tenants_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tenants_id_seq OWNER TO postgres;

--
-- TOC entry 6892 (class 0 OID 0)
-- Dependencies: 249
-- Name: tenants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tenants_id_seq OWNED BY public.tenants.id;


--
-- TOC entry 256 (class 1259 OID 19298)
-- Name: usuario_sucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuario_sucursal (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    sucursal_id integer NOT NULL,
    rol character varying(20) DEFAULT 'cajero'::character varying NOT NULL,
    activo boolean DEFAULT true
);


ALTER TABLE public.usuario_sucursal OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 19297)
-- Name: usuario_sucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_sucursal_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_sucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6893 (class 0 OID 0)
-- Dependencies: 255
-- Name: usuario_sucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_sucursal_id_seq OWNED BY public.usuario_sucursal.id;


--
-- TOC entry 254 (class 1259 OID 19280)
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    tenant_id integer,
    nombre character varying(100) NOT NULL,
    apellido character varying(100),
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    email character varying(150),
    observaciones text
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 19279)
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO postgres;

--
-- TOC entry 6894 (class 0 OID 0)
-- Dependencies: 253
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- TOC entry 236 (class 1259 OID 19101)
-- Name: venta_detalles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.venta_detalles (
    id integer NOT NULL,
    venta_id integer,
    producto_id integer,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.venta_detalles OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 19100)
-- Name: venta_detalles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.venta_detalles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.venta_detalles_id_seq OWNER TO postgres;

--
-- TOC entry 6895 (class 0 OID 0)
-- Dependencies: 235
-- Name: venta_detalles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.venta_detalles_id_seq OWNED BY public.venta_detalles.id;


--
-- TOC entry 234 (class 1259 OID 19072)
-- Name: ventas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ventas (
    id integer NOT NULL,
    numero_venta character varying(20) NOT NULL,
    cliente_id integer,
    caja_id integer,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0,
    igv numeric(10,2) DEFAULT 0,
    total numeric(10,2) NOT NULL,
    tipo_pago character varying(30) DEFAULT 'efectivo'::character varying,
    tipo_comprobante character varying(20) DEFAULT 'boleta'::character varying,
    estado character varying(20) DEFAULT 'completada'::character varying,
    observaciones text,
    vendedor character varying(100) DEFAULT 'Administrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.ventas OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 19071)
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ventas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ventas_id_seq OWNER TO postgres;

--
-- TOC entry 6896 (class 0 OID 0)
-- Dependencies: 233
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ventas_id_seq OWNED BY public.ventas.id;


--
-- TOC entry 5723 (class 2604 OID 20851)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5695 (class 2604 OID 20741)
-- Name: cajas id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cajas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.cajas_id_seq'::regclass);


--
-- TOC entry 5678 (class 2604 OID 20691)
-- Name: categorias id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.categorias ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.categorias_id_seq'::regclass);


--
-- TOC entry 5692 (class 2604 OID 20728)
-- Name: clientes id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.clientes ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.clientes_id_seq'::regclass);


--
-- TOC entry 5725 (class 2604 OID 20864)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5737 (class 2604 OID 20925)
-- Name: cuentas_por_pagar id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar_id_seq'::regclass);


--
-- TOC entry 5744 (class 2604 OID 20972)
-- Name: gastos id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.gastos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.gastos_id_seq'::regclass);


--
-- TOC entry 5721 (class 2604 OID 20833)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5716 (class 2604 OID 20814)
-- Name: ingresos id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingresos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.ingresos_id_seq'::regclass);


--
-- TOC entry 5736 (class 2604 OID 20908)
-- Name: orden_compra_detalles id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.orden_compra_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.orden_compra_detalles_id_seq'::regclass);


--
-- TOC entry 5728 (class 2604 OID 20885)
-- Name: ordenes_compra id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ordenes_compra ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.ordenes_compra_id_seq'::regclass);


--
-- TOC entry 5741 (class 2604 OID 20950)
-- Name: pagos_proveedor id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.pagos_proveedor ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.pagos_proveedor_id_seq'::regclass);


--
-- TOC entry 5681 (class 2604 OID 20702)
-- Name: productos id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.productos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.productos_id_seq'::regclass);


--
-- TOC entry 5713 (class 2604 OID 20801)
-- Name: proveedores id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.proveedores ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.proveedores_id_seq'::regclass);


--
-- TOC entry 5710 (class 2604 OID 20782)
-- Name: venta_detalles id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.venta_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5701 (class 2604 OID 20753)
-- Name: ventas id; Type: DEFAULT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ventas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_alonso_de_alvarado_2.ventas_id_seq'::regclass);


--
-- TOC entry 5792 (class 2604 OID 21147)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5764 (class 2604 OID 21037)
-- Name: cajas id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cajas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.cajas_id_seq'::regclass);


--
-- TOC entry 5747 (class 2604 OID 20987)
-- Name: categorias id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.categorias ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.categorias_id_seq'::regclass);


--
-- TOC entry 5761 (class 2604 OID 21024)
-- Name: clientes id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.clientes ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.clientes_id_seq'::regclass);


--
-- TOC entry 5794 (class 2604 OID 21160)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5806 (class 2604 OID 21221)
-- Name: cuentas_por_pagar id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar_id_seq'::regclass);


--
-- TOC entry 5813 (class 2604 OID 21268)
-- Name: gastos id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.gastos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.gastos_id_seq'::regclass);


--
-- TOC entry 5790 (class 2604 OID 21129)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5785 (class 2604 OID 21110)
-- Name: ingresos id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingresos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.ingresos_id_seq'::regclass);


--
-- TOC entry 5805 (class 2604 OID 21204)
-- Name: orden_compra_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.orden_compra_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.orden_compra_detalles_id_seq'::regclass);


--
-- TOC entry 5797 (class 2604 OID 21181)
-- Name: ordenes_compra id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ordenes_compra ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.ordenes_compra_id_seq'::regclass);


--
-- TOC entry 5810 (class 2604 OID 21246)
-- Name: pagos_proveedor id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.pagos_proveedor ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.pagos_proveedor_id_seq'::regclass);


--
-- TOC entry 5750 (class 2604 OID 20998)
-- Name: productos id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.productos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.productos_id_seq'::regclass);


--
-- TOC entry 5782 (class 2604 OID 21097)
-- Name: proveedores id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.proveedores ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.proveedores_id_seq'::regclass);


--
-- TOC entry 5779 (class 2604 OID 21078)
-- Name: venta_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.venta_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5770 (class 2604 OID 21049)
-- Name: ventas id; Type: DEFAULT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ventas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_amorarca_129_mora.ventas_id_seq'::regclass);


--
-- TOC entry 5494 (class 2604 OID 19490)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5467 (class 2604 OID 19380)
-- Name: cajas id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.cajas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.cajas_id_seq'::regclass);


--
-- TOC entry 5450 (class 2604 OID 19330)
-- Name: categorias id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.categorias ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.categorias_id_seq'::regclass);


--
-- TOC entry 5464 (class 2604 OID 19367)
-- Name: clientes id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.clientes ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.clientes_id_seq'::regclass);


--
-- TOC entry 5496 (class 2604 OID 19503)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5600 (class 2604 OID 19930)
-- Name: gastos id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.gastos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.gastos_id_seq'::regclass);


--
-- TOC entry 5492 (class 2604 OID 19472)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5488 (class 2604 OID 19453)
-- Name: ingresos id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingresos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.ingresos_id_seq'::regclass);


--
-- TOC entry 5453 (class 2604 OID 19341)
-- Name: productos id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.productos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.productos_id_seq'::regclass);


--
-- TOC entry 5485 (class 2604 OID 19440)
-- Name: proveedores id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.proveedores ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.proveedores_id_seq'::regclass);


--
-- TOC entry 5482 (class 2604 OID 19421)
-- Name: venta_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.venta_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5473 (class 2604 OID 19392)
-- Name: ventas id; Type: DEFAULT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ventas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima.ventas_id_seq'::regclass);


--
-- TOC entry 5654 (class 2604 OID 20554)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5626 (class 2604 OID 20444)
-- Name: cajas id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cajas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.cajas_id_seq'::regclass);


--
-- TOC entry 5609 (class 2604 OID 20394)
-- Name: categorias id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.categorias ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.categorias_id_seq'::regclass);


--
-- TOC entry 5623 (class 2604 OID 20431)
-- Name: clientes id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.clientes ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.clientes_id_seq'::regclass);


--
-- TOC entry 5656 (class 2604 OID 20567)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5668 (class 2604 OID 20628)
-- Name: cuentas_por_pagar id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.cuentas_por_pagar_id_seq'::regclass);


--
-- TOC entry 5675 (class 2604 OID 20675)
-- Name: gastos id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.gastos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.gastos_id_seq'::regclass);


--
-- TOC entry 5652 (class 2604 OID 20536)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5647 (class 2604 OID 20517)
-- Name: ingresos id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingresos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.ingresos_id_seq'::regclass);


--
-- TOC entry 5667 (class 2604 OID 20611)
-- Name: orden_compra_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.orden_compra_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.orden_compra_detalles_id_seq'::regclass);


--
-- TOC entry 5659 (class 2604 OID 20588)
-- Name: ordenes_compra id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ordenes_compra ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.ordenes_compra_id_seq'::regclass);


--
-- TOC entry 5672 (class 2604 OID 20653)
-- Name: pagos_proveedor id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.pagos_proveedor ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.pagos_proveedor_id_seq'::regclass);


--
-- TOC entry 5612 (class 2604 OID 20405)
-- Name: productos id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.productos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.productos_id_seq'::regclass);


--
-- TOC entry 5644 (class 2604 OID 20504)
-- Name: proveedores id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.proveedores ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.proveedores_id_seq'::regclass);


--
-- TOC entry 5641 (class 2604 OID 20485)
-- Name: venta_detalles id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.venta_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5632 (class 2604 OID 20456)
-- Name: ventas id; Type: DEFAULT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ventas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_jr_lima_tambo_408.ventas_id_seq'::regclass);


--
-- TOC entry 5867 (class 2604 OID 21929)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5839 (class 2604 OID 21819)
-- Name: cajas id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cajas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.cajas_id_seq'::regclass);


--
-- TOC entry 5822 (class 2604 OID 21769)
-- Name: categorias id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.categorias ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.categorias_id_seq'::regclass);


--
-- TOC entry 5836 (class 2604 OID 21806)
-- Name: clientes id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.clientes ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.clientes_id_seq'::regclass);


--
-- TOC entry 5869 (class 2604 OID 21942)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5881 (class 2604 OID 22003)
-- Name: cuentas_por_pagar id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cuentas_por_pagar ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.cuentas_por_pagar_id_seq'::regclass);


--
-- TOC entry 5888 (class 2604 OID 22050)
-- Name: gastos id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.gastos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.gastos_id_seq'::regclass);


--
-- TOC entry 5865 (class 2604 OID 21911)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5860 (class 2604 OID 21892)
-- Name: ingresos id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingresos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.ingresos_id_seq'::regclass);


--
-- TOC entry 5880 (class 2604 OID 21986)
-- Name: orden_compra_detalles id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.orden_compra_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.orden_compra_detalles_id_seq'::regclass);


--
-- TOC entry 5872 (class 2604 OID 21963)
-- Name: ordenes_compra id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ordenes_compra ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.ordenes_compra_id_seq'::regclass);


--
-- TOC entry 5885 (class 2604 OID 22028)
-- Name: pagos_proveedor id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.pagos_proveedor ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.pagos_proveedor_id_seq'::regclass);


--
-- TOC entry 5825 (class 2604 OID 21780)
-- Name: productos id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.productos ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.productos_id_seq'::regclass);


--
-- TOC entry 5857 (class 2604 OID 21879)
-- Name: proveedores id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.proveedores ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.proveedores_id_seq'::regclass);


--
-- TOC entry 5854 (class 2604 OID 21860)
-- Name: venta_detalles id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.venta_detalles ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5845 (class 2604 OID 21831)
-- Name: ventas id; Type: DEFAULT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ventas ALTER COLUMN id SET DEFAULT nextval('generic_pharma_sucursal_de_prueba.ventas_id_seq'::regclass);


--
-- TOC entry 5543 (class 2604 OID 19686)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5516 (class 2604 OID 19576)
-- Name: cajas id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.cajas ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.cajas_id_seq'::regclass);


--
-- TOC entry 5499 (class 2604 OID 19526)
-- Name: categorias id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.categorias ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.categorias_id_seq'::regclass);


--
-- TOC entry 5513 (class 2604 OID 19563)
-- Name: clientes id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.clientes ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.clientes_id_seq'::regclass);


--
-- TOC entry 5545 (class 2604 OID 19699)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5603 (class 2604 OID 19944)
-- Name: gastos id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.gastos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.gastos_id_seq'::regclass);


--
-- TOC entry 5541 (class 2604 OID 19668)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5537 (class 2604 OID 19649)
-- Name: ingresos id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingresos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.ingresos_id_seq'::regclass);


--
-- TOC entry 5502 (class 2604 OID 19537)
-- Name: productos id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.productos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.productos_id_seq'::regclass);


--
-- TOC entry 5534 (class 2604 OID 19636)
-- Name: proveedores id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.proveedores ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.proveedores_id_seq'::regclass);


--
-- TOC entry 5531 (class 2604 OID 19617)
-- Name: venta_detalles id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.venta_detalles ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5522 (class 2604 OID 19588)
-- Name: ventas id; Type: DEFAULT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ventas ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_nueva_cajamarca.ventas_id_seq'::regclass);


--
-- TOC entry 5592 (class 2604 OID 19881)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5565 (class 2604 OID 19771)
-- Name: cajas id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.cajas ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.cajas_id_seq'::regclass);


--
-- TOC entry 5548 (class 2604 OID 19721)
-- Name: categorias id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.categorias ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.categorias_id_seq'::regclass);


--
-- TOC entry 5562 (class 2604 OID 19758)
-- Name: clientes id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.clientes ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.clientes_id_seq'::regclass);


--
-- TOC entry 5594 (class 2604 OID 19894)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5606 (class 2604 OID 19958)
-- Name: gastos id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.gastos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.gastos_id_seq'::regclass);


--
-- TOC entry 5590 (class 2604 OID 19863)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5586 (class 2604 OID 19844)
-- Name: ingresos id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingresos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.ingresos_id_seq'::regclass);


--
-- TOC entry 5551 (class 2604 OID 19732)
-- Name: productos id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.productos ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.productos_id_seq'::regclass);


--
-- TOC entry 5583 (class 2604 OID 19831)
-- Name: proveedores id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.proveedores ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.proveedores_id_seq'::regclass);


--
-- TOC entry 5580 (class 2604 OID 19812)
-- Name: venta_detalles id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.venta_detalles ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5571 (class 2604 OID 19783)
-- Name: ventas id; Type: DEFAULT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ventas ALTER COLUMN id SET DEFAULT nextval('mari_boticas_sac_rioja.ventas_id_seq'::regclass);


--
-- TOC entry 5430 (class 2604 OID 19176)
-- Name: caja_movimientos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.caja_movimientos ALTER COLUMN id SET DEFAULT nextval('public.caja_movimientos_id_seq'::regclass);


--
-- TOC entry 5400 (class 2604 OID 19063)
-- Name: cajas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cajas ALTER COLUMN id SET DEFAULT nextval('public.cajas_id_seq'::regclass);


--
-- TOC entry 5383 (class 2604 OID 19013)
-- Name: categorias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);


--
-- TOC entry 5397 (class 2604 OID 19050)
-- Name: clientes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes ALTER COLUMN id SET DEFAULT nextval('public.clientes_id_seq'::regclass);


--
-- TOC entry 5432 (class 2604 OID 19191)
-- Name: comprobantes_electronicos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobantes_electronicos ALTER COLUMN id SET DEFAULT nextval('public.comprobantes_electronicos_id_seq'::regclass);


--
-- TOC entry 5597 (class 2604 OID 19916)
-- Name: gastos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gastos ALTER COLUMN id SET DEFAULT nextval('public.gastos_id_seq'::regclass);


--
-- TOC entry 5428 (class 2604 OID 19158)
-- Name: ingreso_detalles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingreso_detalles ALTER COLUMN id SET DEFAULT nextval('public.ingreso_detalles_id_seq'::regclass);


--
-- TOC entry 5421 (class 2604 OID 19136)
-- Name: ingresos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingresos ALTER COLUMN id SET DEFAULT nextval('public.ingresos_id_seq'::regclass);


--
-- TOC entry 5816 (class 2604 OID 21734)
-- Name: password_resets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets ALTER COLUMN id SET DEFAULT nextval('public.password_resets_id_seq'::regclass);


--
-- TOC entry 5386 (class 2604 OID 19024)
-- Name: productos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.productos ALTER COLUMN id SET DEFAULT nextval('public.productos_id_seq'::regclass);


--
-- TOC entry 5418 (class 2604 OID 19123)
-- Name: proveedores id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedores ALTER COLUMN id SET DEFAULT nextval('public.proveedores_id_seq'::regclass);


--
-- TOC entry 5434 (class 2604 OID 19206)
-- Name: series_comprobantes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.series_comprobantes ALTER COLUMN id SET DEFAULT nextval('public.series_comprobantes_id_seq'::regclass);


--
-- TOC entry 5440 (class 2604 OID 19265)
-- Name: sucursales id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursales ALTER COLUMN id SET DEFAULT nextval('public.sucursales_id_seq'::regclass);


--
-- TOC entry 5819 (class 2604 OID 21750)
-- Name: superadmins id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.superadmins ALTER COLUMN id SET DEFAULT nextval('public.superadmins_id_seq'::regclass);


--
-- TOC entry 5891 (class 2604 OID 49191)
-- Name: tenant_config id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tenant_config ALTER COLUMN id SET DEFAULT nextval('public.tenant_config_id_seq'::regclass);


--
-- TOC entry 5436 (class 2604 OID 19253)
-- Name: tenants id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tenants ALTER COLUMN id SET DEFAULT nextval('public.tenants_id_seq'::regclass);


--
-- TOC entry 5447 (class 2604 OID 19301)
-- Name: usuario_sucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sucursal ALTER COLUMN id SET DEFAULT nextval('public.usuario_sucursal_id_seq'::regclass);


--
-- TOC entry 5444 (class 2604 OID 19283)
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- TOC entry 5415 (class 2604 OID 19104)
-- Name: venta_detalles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.venta_detalles ALTER COLUMN id SET DEFAULT nextval('public.venta_detalles_id_seq'::regclass);


--
-- TOC entry 5406 (class 2604 OID 19075)
-- Name: ventas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas ALTER COLUMN id SET DEFAULT nextval('public.ventas_id_seq'::regclass);


--
-- TOC entry 6685 (class 0 OID 20848)
-- Dependencies: 386
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
1	1	egreso	[Gasto] Escoba	100.00	\N	7	2026-04-25 11:58:03.116492
2	1	egreso	[Gasto] Escoba	5.00	\N	7	2026-05-25 20:12:41.737642
\.


--
-- TOC entry 6673 (class 0 OID 20738)
-- Dependencies: 374
-- Data for Name: cajas; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
1	Caja Principal	150.00	250.70	abierta	2026-04-12 23:36:43.521472	\N	Gloria	\N	2026-04-12 23:36:43.521472
\.


--
-- TOC entry 6667 (class 0 OID 20688)
-- Dependencies: 368
-- Data for Name: categorias; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
1	Medicamentos	Fármacos y medicamentos en general	t	2026-03-29 03:04:29.913412
2	Vitaminas y Suplementos	Vitaminas, minerales y suplementos nutricionales	t	2026-03-29 03:04:29.913412
3	Cuidado Personal	Productos de higiene y cuidado personal	t	2026-03-29 03:04:29.913412
4	Primeros Auxilios	Materiales de curación y primeros auxilios	t	2026-03-29 03:04:29.913412
5	Bebés y Niños	Productos para bebés y niños	t	2026-03-29 03:04:29.913412
6	Genéricos	Medicamentos genéricos	t	2026-03-29 03:04:29.913412
\.


--
-- TOC entry 6671 (class 0 OID 20725)
-- Dependencies: 372
-- Data for Name: clientes; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
1	Cliente	General	00000000	\N	000000000	\N	\N	t	2026-03-29 03:04:29.914515
\.


--
-- TOC entry 6687 (class 0 OID 20861)
-- Dependencies: 388
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
1	6	boleta	B001	1	B001-00000001	S					{"errors":"Serie No puedes emitir comprobantes con esta serie', Fecha de emisión La fecha del documento debe ser la fecha de HOY","codigo":21}	2026-04-12 23:38:07.154663
2	8	boleta	B001	2	B001-00000002	S					{"errors":"Serie No puedes emitir comprobantes con esta serie'","codigo":21}	2026-04-16 00:20:03.030328
3	9	boleta	B001	3	B001-00000003	S					{"errors":"Serie No puedes emitir comprobantes con esta serie'","codigo":21}	2026-04-16 01:42:23.88545
4	11	boleta	B001	4	B001-00000004	Error de conexión	\N	\N	\N	\N	\N	2026-05-23 20:34:09.460308
5	12	boleta	B001	5	B001-00000005	S					{"errors":"Serie No puedes emitir comprobantes con esta serie', Fecha de emisión La fecha del documento debe ser la fecha de HOY","codigo":21}	2026-05-25 19:56:37.579348
6	13	boleta	B001	6	B001-00000006	S					{"errors":"Serie No puedes emitir comprobantes con esta serie', Fecha de emisión La fecha del documento debe ser la fecha de HOY","codigo":21}	2026-05-25 19:56:48.856826
7	14	boleta	B001	7	B001-00000007	S					{"errors":"Serie No puedes emitir comprobantes con esta serie', Fecha de emisión La fecha del documento debe ser la fecha de HOY","codigo":21}	2026-05-25 20:03:17.063413
8	15	boleta	B001	8	B001-00000008	S					{"errors":"Serie No puedes emitir comprobantes con esta serie', Fecha de emisión La fecha del documento debe ser la fecha de HOY","codigo":21}	2026-05-29 21:57:01.117539
\.


--
-- TOC entry 6694 (class 0 OID 20922)
-- Dependencies: 395
-- Data for Name: cuentas_por_pagar; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar (id, proveedor_id, ingreso_id, orden_compra_id, numero_doc, monto_total, monto_pagado, monto_pendiente, estado, fecha_vencimiento, created_at) FROM stdin;
\.


--
-- TOC entry 6698 (class 0 OID 20969)
-- Dependencies: 399
-- Data for Name: gastos; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
1	1	Escoba	Precio Uno		100.00	efectivo	7	2026-04-25 11:58:03.116492
2	1	Escoba	23456789		5.00	efectivo	7	2026-05-25 20:12:41.737642
\.


--
-- TOC entry 6683 (class 0 OID 20830)
-- Dependencies: 384
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6681 (class 0 OID 20811)
-- Dependencies: 382
-- Data for Name: ingresos; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at, tipo_pago, orden_compra_id) FROM stdin;
\.


--
-- TOC entry 6692 (class 0 OID 20905)
-- Dependencies: 393
-- Data for Name: orden_compra_detalles; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.orden_compra_detalles (id, orden_id, producto_id, descripcion, cantidad, precio_unitario, subtotal) FROM stdin;
1	1	6		100	0.90	90.00
\.


--
-- TOC entry 6690 (class 0 OID 20882)
-- Dependencies: 391
-- Data for Name: ordenes_compra; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.ordenes_compra (id, numero_orden, proveedor_id, usuario_id, estado, tipo_pago, dias_credito, subtotal, igv, total, observaciones, fecha_entrega, created_at) FROM stdin;
1	OC20260524-0001	1	7	pendiente	transferencia	30	90.00	0.00	90.00		2026-05-23	2026-05-23 20:31:23.362813
\.


--
-- TOC entry 6696 (class 0 OID 20947)
-- Dependencies: 397
-- Data for Name: pagos_proveedor; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.pagos_proveedor (id, cuenta_id, monto, metodo_pago, referencia, usuario_id, notas, created_at) FROM stdin;
\.


--
-- TOC entry 6669 (class 0 OID 20699)
-- Dependencies: 370
-- Data for Name: productos; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
10	MED009	Azitromicina 500mg	Antibiótico macrólido	1	5.00	10.50	33	8	caja	Lafrancol	Tab x 3	t	t	f	2	2026-04-02 23:21:00.806768	2026-05-25 20:03:17.063413	\N	\N
18	CUI002	Agua Oxigenada 10vol	Antiséptico y desinfectante	3	1.50	3.20	75	20	frasco	Farmacias	120 ml	f	t	f	10	2026-04-02 23:21:00.806768	2026-05-29 21:57:01.117539	\N	\N
1	4006381157896	plumon verde	\N	\N	1.50	2.80	46	5	unidad	stabilo	plumones	f	t	f	4	2026-03-29 03:12:11.137421	2026-03-29 03:23:43.419275	\N	\N
2	MED001	Paracetamol 500mg	Analgésico y antipirético	1	0.80	1.80	120	20	unidad	Genfarma	Tab x 100	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
3	MED002	Ibuprofeno 400mg	Antiinflamatorio no esteroideo	1	1.20	2.50	80	15	unidad	Medrock	Tab x 100	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
5	MED004	Omeprazol 20mg	Inhibidor de bomba de protones	1	1.50	3.20	90	15	caja	Genfarma	Cap x 14	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
6	MED005	Loratadina 10mg	Antihistamínico	1	0.90	2.00	75	10	unidad	Genfar	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
7	MED006	Metformina 850mg	Antidiabético oral	1	1.80	3.80	45	10	unidad	Medrock	Tab x 30	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
11	MED010	Ciprofloxacino 500mg	Antibiótico quinolona	1	2.80	5.80	50	10	caja	Genfar	Tab x 10	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
12	VIT001	Vitamina C 1000mg	Ácido ascórbico efervescente	2	2.50	5.50	100	15	unidad	Bayer	Tab Ef x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
13	VIT002	Complejo B	Vitaminas del grupo B	2	3.20	7.00	80	12	frasco	Genfarma	Tab x 60	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
14	VIT003	Vitamina D3 2000 UI	Colecalciferol para huesos	2	4.50	9.50	55	10	frasco	Nature Made	Cap x 30	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
15	VIT004	Omega 3 1000mg	Ácidos grasos esenciales	2	6.00	13.00	45	8	frasco	Omegavit	Cap x 30	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
16	VIT005	Calcio + Vitamina D	Suplemento óseo	2	5.50	11.00	60	10	frasco	Centrum	Tab x 60	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
19	CUI003	Crema Hidratante Corporal	Hidratación profunda piel seca	3	7.00	15.00	40	8	tubo	Eucerin	250 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
20	CUI004	Jabón Antibacterial	Limpieza y protección bacteriana	3	2.80	5.50	60	10	unidad	Palmolive	110 g	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
21	CUI005	Protector Solar SPF 50	Fotoprotección UVA/UVB	3	12.00	25.00	25	5	tubo	Isdin	50 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
24	PAU003	Termómetro Digital	Medición de temperatura corporal	4	12.00	25.00	20	5	unidad	Omron	Digital	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
25	PAU004	Vendaje Elástico 10cm	Compresión y soporte articular	4	2.50	5.00	40	8	rollo	Curaplex	10cm x 4m	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
26	BEB001	Paracetamol Infantil Jarabe	Analgésico pediátrico 120mg/5ml	5	4.00	8.50	50	10	frasco	Genfarma	120 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
27	BEB002	Vitamina A + D Gotas	Suplemento vitamínico pediátrico	5	5.50	11.00	35	8	frasco	Biomont	20 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
28	BEB003	Sales de Rehidratación	Tratamiento de deshidratación	5	1.20	2.80	80	20	sobre	ORS	x 4 sobres	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
29	GEN001	Ranitidina 150mg	Antiulceroso genérico	6	0.70	1.50	70	15	unidad	Genfarma	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
30	GEN002	Clonazepam 0.5mg	Ansiolítico benzodiazepínico	6	3.00	6.50	30	5	caja	Roche Gen.	Tab x 30	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
31	GEN003	Captopril 25mg	Antihipertensivo genérico	6	1.00	2.20	55	10	unidad	Genfar	Tab x 20	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
17	CUI001	Alcohol 70° 500ml	Antiséptico para piel	3	3.00	6.00	60	15	frasco	Clorox	500 ml	f	t	f	10	2026-04-02 23:21:00.806768	2026-05-29 21:57:01.117539	\N	\N
8	MED007	Atorvastatina 20mg	Reductor de colesterol	1	4.20	9.00	36	8	caja	Pfizer	Tab x 14	t	t	f	4	2026-04-02 23:21:00.806768	2026-05-29 21:57:01.117539	\N	\N
4	MED003	Amoxicilina 500mg	Antibiótico de amplio espectro	1	3.50	7.00	53	10	caja	Lafrancol	Cap x 12	t	t	f	7	2026-04-02 23:21:00.806768	2026-05-25 19:56:37.579348	\N	\N
22	PAU001	Gasa Estéril 10x10cm	Apósito para heridas	4	0.60	1.50	149	30	unidad	Curaplex	x 10 unid	f	t	f	1	2026-04-02 23:21:00.806768	2026-05-25 19:56:48.856826	\N	\N
23	PAU002	Esparadrapo 5cm	Fijación de apósitos	4	3.50	7.00	49	10	rollo	3M	5cm x 4.5m	f	t	f	1	2026-04-02 23:21:00.806768	2026-05-25 19:56:48.856826	\N	\N
9	MED008	Diclofenaco 50mg	Antiinflamatorio y analgésico	1	1.10	2.40	64	12	unidad	Genfarma	Tab x 10	f	t	f	1	2026-04-02 23:21:00.806768	2026-05-25 19:56:48.856826	\N	\N
\.


--
-- TOC entry 6679 (class 0 OID 20798)
-- Dependencies: 380
-- Data for Name: proveedores; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
1	12345678	AGROTECH SAC	AGROTECH SAC	12345678	agrotech@gmailc.om	Sn. 123	Felimon Lopez	t	2026-05-23 20:30:51.321985
\.


--
-- TOC entry 6688 (class 0 OID 20875)
-- Dependencies: 389
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
factura	F001	0
boleta	B001	8
\.


--
-- TOC entry 6677 (class 0 OID 20779)
-- Dependencies: 378
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
1	1	1	1	2.80	0.00	2.80	2026-03-29 03:12:37.930742
2	2	1	1	2.80	0.00	2.80	2026-03-29 03:17:56.267223
3	3	1	1	2.80	0.00	2.80	2026-03-29 03:21:17.27814
4	4	1	1	2.80	0.00	2.80	2026-03-29 03:23:43.419275
5	5	18	1	3.20	0.00	3.20	2026-04-02 23:22:48.542517
6	6	18	1	3.20	0.00	3.20	2026-04-12 23:38:07.154663
7	6	17	1	6.00	0.00	6.00	2026-04-12 23:38:07.154663
8	6	4	1	7.00	0.00	7.00	2026-04-12 23:38:07.154663
9	6	8	1	9.00	0.00	9.00	2026-04-12 23:38:07.154663
10	6	10	1	10.50	0.00	10.50	2026-04-12 23:38:07.154663
11	7	18	1	3.20	0.00	3.20	2026-04-15 10:59:22.355109
12	7	17	1	6.00	0.00	6.00	2026-04-15 10:59:22.355109
13	7	4	1	7.00	0.00	7.00	2026-04-15 10:59:22.355109
14	8	18	1	3.20	0.00	3.20	2026-04-16 00:20:03.030328
15	8	17	1	6.00	0.00	6.00	2026-04-16 00:20:03.030328
16	9	18	1	3.20	0.00	3.20	2026-04-16 01:42:23.88545
17	9	17	1	6.00	0.00	6.00	2026-04-16 01:42:23.88545
18	9	4	1	7.00	0.00	7.00	2026-04-16 01:42:23.88545
19	10	17	1	6.00	0.00	6.00	2026-05-15 22:20:57.115852
20	11	18	1	3.20	0.00	3.20	2026-05-23 20:34:09.460308
21	11	17	1	6.00	0.00	6.00	2026-05-23 20:34:09.460308
22	11	4	1	7.00	0.00	7.00	2026-05-23 20:34:09.460308
23	11	8	1	9.00	0.00	9.00	2026-05-23 20:34:09.460308
24	12	18	2	3.20	0.00	6.40	2026-05-25 19:56:37.579348
25	12	17	2	6.00	0.00	12.00	2026-05-25 19:56:37.579348
26	12	4	3	7.00	0.00	21.00	2026-05-25 19:56:37.579348
27	13	22	1	1.50	0.00	1.50	2026-05-25 19:56:48.856826
28	13	23	1	7.00	0.00	7.00	2026-05-25 19:56:48.856826
29	13	9	1	2.40	0.00	2.40	2026-05-25 19:56:48.856826
30	14	18	1	3.20	0.00	3.20	2026-05-25 20:03:17.063413
31	14	10	1	10.50	0.00	10.50	2026-05-25 20:03:17.063413
32	14	8	1	9.00	0.00	9.00	2026-05-25 20:03:17.063413
33	15	18	1	3.20	0.00	3.20	2026-05-29 21:57:01.117539
34	15	17	2	6.00	0.00	12.00	2026-05-29 21:57:01.117539
35	15	8	1	9.00	0.00	9.00	2026-05-29 21:57:01.117539
\.


--
-- TOC entry 6675 (class 0 OID 20750)
-- Dependencies: 376
-- Data for Name: ventas; Type: TABLE DATA; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

COPY generic_pharma_alonso_de_alvarado_2.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
1	V20260329-0001	\N	\N	\N	2.80	0.00	0.50	2.80	efectivo	ticket	completada	\N	Administrador	2026-03-29 03:12:37.930742
2	V20260329-0002	\N	\N	\N	2.80	0.00	0.50	2.80	efectivo	ticket	completada	\N	Administrador	2026-03-29 03:17:56.267223
3	V20260329-0003	\N	\N	\N	2.80	0.00	0.50	2.80	efectivo	ticket	completada	\N	Administrador	2026-03-29 03:21:17.27814
4	V20260329-0004	\N	\N	\N	2.80	0.00	0.50	2.80	efectivo	ticket	completada	\N	Administrador	2026-03-29 03:23:43.419275
5	V20260403-0001	\N	\N	\N	3.20	0.00	0.58	3.20	efectivo	ticket	completada	\N	Administrador	2026-04-02 23:22:48.542517
6	V20260413-0001	\N	1	\N	35.70	0.00	6.43	35.70	efectivo	boleta	completada	\N	Administrador	2026-04-12 23:38:07.154663
7	V20260415-0001	\N	1	\N	16.20	0.00	2.92	16.20	efectivo	ticket	completada	\N	Administrador	2026-04-15 10:59:22.355109
8	V20260416-0001	\N	1	\N	9.20	0.00	1.66	9.20	efectivo	boleta	completada	\N	Administrador	2026-04-16 00:20:03.030328
9	V20260416-0002	\N	1	\N	16.20	0.00	2.92	16.20	efectivo	boleta	completada	\N	Administrador	2026-04-16 01:42:23.88545
10	V20260516-0001	\N	1	\N	6.00	0.00	1.08	6.00	yape	ticket	completada	\N	Administrador	2026-05-15 22:20:57.115852
11	V20260524-0001	\N	1	\N	25.20	0.00	4.54	25.20	efectivo	boleta	completada	\N	Administrador	2026-05-23 20:34:09.460308
12	V20260526-0001	\N	1	\N	39.40	0.00	7.09	39.40	efectivo	boleta	completada	\N	Administrador	2026-05-25 19:56:37.579348
13	V20260526-0002	\N	1	\N	10.90	0.00	1.96	10.90	efectivo	boleta	completada	\N	Administrador	2026-05-25 19:56:48.856826
14	V20260526-0003	\N	1	\N	22.70	0.00	4.09	22.70	efectivo	boleta	completada	\N	Administrador	2026-05-25 20:03:17.063413
15	V20260530-0001	\N	1	\N	24.20	0.00	4.36	24.20	efectivo	boleta	completada	\N	Administrador	2026-05-29 21:57:01.117539
\.


--
-- TOC entry 6718 (class 0 OID 21144)
-- Dependencies: 419
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6706 (class 0 OID 21034)
-- Dependencies: 407
-- Data for Name: cajas; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6700 (class 0 OID 20984)
-- Dependencies: 401
-- Data for Name: categorias; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
1	Medicamentos	Fármacos y medicamentos en general	t	2026-03-29 03:05:05.774655
2	Vitaminas y Suplementos	Vitaminas, minerales y suplementos nutricionales	t	2026-03-29 03:05:05.774655
3	Cuidado Personal	Productos de higiene y cuidado personal	t	2026-03-29 03:05:05.774655
4	Primeros Auxilios	Materiales de curación y primeros auxilios	t	2026-03-29 03:05:05.774655
5	Bebés y Niños	Productos para bebés y niños	t	2026-03-29 03:05:05.774655
6	Genéricos	Medicamentos genéricos	t	2026-03-29 03:05:05.774655
\.


--
-- TOC entry 6704 (class 0 OID 21021)
-- Dependencies: 405
-- Data for Name: clientes; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
1	Cliente	General	00000000	\N	000000000	\N	\N	t	2026-03-29 03:05:05.775648
\.


--
-- TOC entry 6720 (class 0 OID 21157)
-- Dependencies: 421
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6727 (class 0 OID 21218)
-- Dependencies: 428
-- Data for Name: cuentas_por_pagar; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar (id, proveedor_id, ingreso_id, orden_compra_id, numero_doc, monto_total, monto_pagado, monto_pendiente, estado, fecha_vencimiento, created_at) FROM stdin;
\.


--
-- TOC entry 6731 (class 0 OID 21265)
-- Dependencies: 432
-- Data for Name: gastos; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6716 (class 0 OID 21126)
-- Dependencies: 417
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6714 (class 0 OID 21107)
-- Dependencies: 415
-- Data for Name: ingresos; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at, tipo_pago, orden_compra_id) FROM stdin;
\.


--
-- TOC entry 6725 (class 0 OID 21201)
-- Dependencies: 426
-- Data for Name: orden_compra_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.orden_compra_detalles (id, orden_id, producto_id, descripcion, cantidad, precio_unitario, subtotal) FROM stdin;
\.


--
-- TOC entry 6723 (class 0 OID 21178)
-- Dependencies: 424
-- Data for Name: ordenes_compra; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.ordenes_compra (id, numero_orden, proveedor_id, usuario_id, estado, tipo_pago, dias_credito, subtotal, igv, total, observaciones, fecha_entrega, created_at) FROM stdin;
\.


--
-- TOC entry 6729 (class 0 OID 21243)
-- Dependencies: 430
-- Data for Name: pagos_proveedor; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.pagos_proveedor (id, cuenta_id, monto, metodo_pago, referencia, usuario_id, notas, created_at) FROM stdin;
\.


--
-- TOC entry 6702 (class 0 OID 20995)
-- Dependencies: 403
-- Data for Name: productos; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
1	MED001	Paracetamol 500mg	Analgésico y antipirético	1	0.80	1.80	120	20	unidad	Genfarma	Tab x 100	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
2	MED002	Ibuprofeno 400mg	Antiinflamatorio no esteroideo	1	1.20	2.50	80	15	unidad	Medrock	Tab x 100	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
3	MED003	Amoxicilina 500mg	Antibiótico de amplio espectro	1	3.50	7.00	60	10	caja	Lafrancol	Cap x 12	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
4	MED004	Omeprazol 20mg	Inhibidor de bomba de protones	1	1.50	3.20	90	15	caja	Genfarma	Cap x 14	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
5	MED005	Loratadina 10mg	Antihistamínico	1	0.90	2.00	75	10	unidad	Genfar	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
6	MED006	Metformina 850mg	Antidiabético oral	1	1.80	3.80	45	10	unidad	Medrock	Tab x 30	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
7	MED007	Atorvastatina 20mg	Reductor de colesterol	1	4.20	9.00	40	8	caja	Pfizer	Tab x 14	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
8	MED008	Diclofenaco 50mg	Antiinflamatorio y analgésico	1	1.10	2.40	65	12	unidad	Genfarma	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
9	MED009	Azitromicina 500mg	Antibiótico macrólido	1	5.00	10.50	35	8	caja	Lafrancol	Tab x 3	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
10	MED010	Ciprofloxacino 500mg	Antibiótico quinolona	1	2.80	5.80	50	10	caja	Genfar	Tab x 10	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
11	VIT001	Vitamina C 1000mg	Ácido ascórbico efervescente	2	2.50	5.50	100	15	unidad	Bayer	Tab Ef x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
12	VIT002	Complejo B	Vitaminas del grupo B	2	3.20	7.00	80	12	frasco	Genfarma	Tab x 60	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
13	VIT003	Vitamina D3 2000 UI	Colecalciferol para huesos	2	4.50	9.50	55	10	frasco	Nature Made	Cap x 30	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
14	VIT004	Omega 3 1000mg	Ácidos grasos esenciales	2	6.00	13.00	45	8	frasco	Omegavit	Cap x 30	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
15	VIT005	Calcio + Vitamina D	Suplemento óseo	2	5.50	11.00	60	10	frasco	Centrum	Tab x 60	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
16	CUI001	Alcohol 70° 500ml	Antiséptico para piel	3	3.00	6.00	70	15	frasco	Clorox	500 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
17	CUI002	Agua Oxigenada 10vol	Antiséptico y desinfectante	3	1.50	3.20	85	20	frasco	Farmacias	120 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
18	CUI003	Crema Hidratante Corporal	Hidratación profunda piel seca	3	7.00	15.00	40	8	tubo	Eucerin	250 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
19	CUI004	Jabón Antibacterial	Limpieza y protección bacteriana	3	2.80	5.50	60	10	unidad	Palmolive	110 g	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
20	CUI005	Protector Solar SPF 50	Fotoprotección UVA/UVB	3	12.00	25.00	25	5	tubo	Isdin	50 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
21	PAU001	Gasa Estéril 10x10cm	Apósito para heridas	4	0.60	1.50	150	30	unidad	Curaplex	x 10 unid	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
22	PAU002	Esparadrapo 5cm	Fijación de apósitos	4	3.50	7.00	50	10	rollo	3M	5cm x 4.5m	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
23	PAU003	Termómetro Digital	Medición de temperatura corporal	4	12.00	25.00	20	5	unidad	Omron	Digital	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
24	PAU004	Vendaje Elástico 10cm	Compresión y soporte articular	4	2.50	5.00	40	8	rollo	Curaplex	10cm x 4m	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
25	BEB001	Paracetamol Infantil Jarabe	Analgésico pediátrico 120mg/5ml	5	4.00	8.50	50	10	frasco	Genfarma	120 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
26	BEB002	Vitamina A + D Gotas	Suplemento vitamínico pediátrico	5	5.50	11.00	35	8	frasco	Biomont	20 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
27	BEB003	Sales de Rehidratación	Tratamiento de deshidratación	5	1.20	2.80	80	20	sobre	ORS	x 4 sobres	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
28	GEN001	Ranitidina 150mg	Antiulceroso genérico	6	0.70	1.50	70	15	unidad	Genfarma	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
29	GEN002	Clonazepam 0.5mg	Ansiolítico benzodiazepínico	6	3.00	6.50	30	5	caja	Roche Gen.	Tab x 30	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
30	GEN003	Captopril 25mg	Antihipertensivo genérico	6	1.00	2.20	55	10	unidad	Genfar	Tab x 20	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
\.


--
-- TOC entry 6712 (class 0 OID 21094)
-- Dependencies: 413
-- Data for Name: proveedores; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6721 (class 0 OID 21171)
-- Dependencies: 422
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
boleta	B001	0
factura	F001	0
\.


--
-- TOC entry 6710 (class 0 OID 21075)
-- Dependencies: 411
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6708 (class 0 OID 21046)
-- Dependencies: 409
-- Data for Name: ventas; Type: TABLE DATA; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

COPY generic_pharma_jr_amorarca_129_mora.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
\.


--
-- TOC entry 6575 (class 0 OID 19487)
-- Dependencies: 276
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6563 (class 0 OID 19377)
-- Dependencies: 264
-- Data for Name: cajas; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6557 (class 0 OID 19327)
-- Dependencies: 258
-- Data for Name: categorias; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6561 (class 0 OID 19364)
-- Dependencies: 262
-- Data for Name: clientes; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6577 (class 0 OID 19500)
-- Dependencies: 278
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6628 (class 0 OID 19927)
-- Dependencies: 329
-- Data for Name: gastos; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6573 (class 0 OID 19469)
-- Dependencies: 274
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6571 (class 0 OID 19450)
-- Dependencies: 272
-- Data for Name: ingresos; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at) FROM stdin;
\.


--
-- TOC entry 6559 (class 0 OID 19338)
-- Dependencies: 260
-- Data for Name: productos; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
\.


--
-- TOC entry 6569 (class 0 OID 19437)
-- Dependencies: 270
-- Data for Name: proveedores; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6578 (class 0 OID 19514)
-- Dependencies: 279
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
\.


--
-- TOC entry 6567 (class 0 OID 19418)
-- Dependencies: 268
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6565 (class 0 OID 19389)
-- Dependencies: 266
-- Data for Name: ventas; Type: TABLE DATA; Schema: generic_pharma_jr_lima; Owner: postgres
--

COPY generic_pharma_jr_lima.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
\.


--
-- TOC entry 6652 (class 0 OID 20551)
-- Dependencies: 353
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6640 (class 0 OID 20441)
-- Dependencies: 341
-- Data for Name: cajas; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6634 (class 0 OID 20391)
-- Dependencies: 335
-- Data for Name: categorias; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
1	Medicamentos	Fármacos y medicamentos en general	t	2026-03-29 03:03:52.95336
2	Vitaminas y Suplementos	Vitaminas, minerales y suplementos nutricionales	t	2026-03-29 03:03:52.95336
3	Cuidado Personal	Productos de higiene y cuidado personal	t	2026-03-29 03:03:52.95336
4	Primeros Auxilios	Materiales de curación y primeros auxilios	t	2026-03-29 03:03:52.95336
5	Bebés y Niños	Productos para bebés y niños	t	2026-03-29 03:03:52.95336
6	Genéricos	Medicamentos genéricos	t	2026-03-29 03:03:52.95336
\.


--
-- TOC entry 6638 (class 0 OID 20428)
-- Dependencies: 339
-- Data for Name: clientes; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
1	Cliente	General	00000000	\N	000000000	\N	\N	t	2026-03-29 03:03:52.954206
\.


--
-- TOC entry 6654 (class 0 OID 20564)
-- Dependencies: 355
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6661 (class 0 OID 20625)
-- Dependencies: 362
-- Data for Name: cuentas_por_pagar; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar (id, proveedor_id, ingreso_id, orden_compra_id, numero_doc, monto_total, monto_pagado, monto_pendiente, estado, fecha_vencimiento, created_at) FROM stdin;
\.


--
-- TOC entry 6665 (class 0 OID 20672)
-- Dependencies: 366
-- Data for Name: gastos; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6650 (class 0 OID 20533)
-- Dependencies: 351
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6648 (class 0 OID 20514)
-- Dependencies: 349
-- Data for Name: ingresos; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at, tipo_pago, orden_compra_id) FROM stdin;
\.


--
-- TOC entry 6659 (class 0 OID 20608)
-- Dependencies: 360
-- Data for Name: orden_compra_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.orden_compra_detalles (id, orden_id, producto_id, descripcion, cantidad, precio_unitario, subtotal) FROM stdin;
\.


--
-- TOC entry 6657 (class 0 OID 20585)
-- Dependencies: 358
-- Data for Name: ordenes_compra; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.ordenes_compra (id, numero_orden, proveedor_id, usuario_id, estado, tipo_pago, dias_credito, subtotal, igv, total, observaciones, fecha_entrega, created_at) FROM stdin;
\.


--
-- TOC entry 6663 (class 0 OID 20650)
-- Dependencies: 364
-- Data for Name: pagos_proveedor; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.pagos_proveedor (id, cuenta_id, monto, metodo_pago, referencia, usuario_id, notas, created_at) FROM stdin;
\.


--
-- TOC entry 6636 (class 0 OID 20402)
-- Dependencies: 337
-- Data for Name: productos; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
1	MED001	Paracetamol 500mg	Analgésico y antipirético	1	0.80	1.80	120	20	unidad	Genfarma	Tab x 100	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
2	MED002	Ibuprofeno 400mg	Antiinflamatorio no esteroideo	1	1.20	2.50	80	15	unidad	Medrock	Tab x 100	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
3	MED003	Amoxicilina 500mg	Antibiótico de amplio espectro	1	3.50	7.00	60	10	caja	Lafrancol	Cap x 12	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
4	MED004	Omeprazol 20mg	Inhibidor de bomba de protones	1	1.50	3.20	90	15	caja	Genfarma	Cap x 14	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
5	MED005	Loratadina 10mg	Antihistamínico	1	0.90	2.00	75	10	unidad	Genfar	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
6	MED006	Metformina 850mg	Antidiabético oral	1	1.80	3.80	45	10	unidad	Medrock	Tab x 30	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
7	MED007	Atorvastatina 20mg	Reductor de colesterol	1	4.20	9.00	40	8	caja	Pfizer	Tab x 14	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
8	MED008	Diclofenaco 50mg	Antiinflamatorio y analgésico	1	1.10	2.40	65	12	unidad	Genfarma	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
9	MED009	Azitromicina 500mg	Antibiótico macrólido	1	5.00	10.50	35	8	caja	Lafrancol	Tab x 3	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
10	MED010	Ciprofloxacino 500mg	Antibiótico quinolona	1	2.80	5.80	50	10	caja	Genfar	Tab x 10	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
11	VIT001	Vitamina C 1000mg	Ácido ascórbico efervescente	2	2.50	5.50	100	15	unidad	Bayer	Tab Ef x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
12	VIT002	Complejo B	Vitaminas del grupo B	2	3.20	7.00	80	12	frasco	Genfarma	Tab x 60	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
13	VIT003	Vitamina D3 2000 UI	Colecalciferol para huesos	2	4.50	9.50	55	10	frasco	Nature Made	Cap x 30	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
14	VIT004	Omega 3 1000mg	Ácidos grasos esenciales	2	6.00	13.00	45	8	frasco	Omegavit	Cap x 30	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
15	VIT005	Calcio + Vitamina D	Suplemento óseo	2	5.50	11.00	60	10	frasco	Centrum	Tab x 60	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
16	CUI001	Alcohol 70° 500ml	Antiséptico para piel	3	3.00	6.00	70	15	frasco	Clorox	500 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
17	CUI002	Agua Oxigenada 10vol	Antiséptico y desinfectante	3	1.50	3.20	85	20	frasco	Farmacias	120 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
18	CUI003	Crema Hidratante Corporal	Hidratación profunda piel seca	3	7.00	15.00	40	8	tubo	Eucerin	250 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
19	CUI004	Jabón Antibacterial	Limpieza y protección bacteriana	3	2.80	5.50	60	10	unidad	Palmolive	110 g	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
20	CUI005	Protector Solar SPF 50	Fotoprotección UVA/UVB	3	12.00	25.00	25	5	tubo	Isdin	50 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
21	PAU001	Gasa Estéril 10x10cm	Apósito para heridas	4	0.60	1.50	150	30	unidad	Curaplex	x 10 unid	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
22	PAU002	Esparadrapo 5cm	Fijación de apósitos	4	3.50	7.00	50	10	rollo	3M	5cm x 4.5m	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
23	PAU003	Termómetro Digital	Medición de temperatura corporal	4	12.00	25.00	20	5	unidad	Omron	Digital	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
24	PAU004	Vendaje Elástico 10cm	Compresión y soporte articular	4	2.50	5.00	40	8	rollo	Curaplex	10cm x 4m	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
25	BEB001	Paracetamol Infantil Jarabe	Analgésico pediátrico 120mg/5ml	5	4.00	8.50	50	10	frasco	Genfarma	120 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
26	BEB002	Vitamina A + D Gotas	Suplemento vitamínico pediátrico	5	5.50	11.00	35	8	frasco	Biomont	20 ml	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
27	BEB003	Sales de Rehidratación	Tratamiento de deshidratación	5	1.20	2.80	80	20	sobre	ORS	x 4 sobres	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
28	GEN001	Ranitidina 150mg	Antiulceroso genérico	6	0.70	1.50	70	15	unidad	Genfarma	Tab x 10	f	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
29	GEN002	Clonazepam 0.5mg	Ansiolítico benzodiazepínico	6	3.00	6.50	30	5	caja	Roche Gen.	Tab x 30	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
30	GEN003	Captopril 25mg	Antihipertensivo genérico	6	1.00	2.20	55	10	unidad	Genfar	Tab x 20	t	t	f	0	2026-04-02 23:21:00.806768	2026-04-02 23:21:00.806768	\N	\N
\.


--
-- TOC entry 6646 (class 0 OID 20501)
-- Dependencies: 347
-- Data for Name: proveedores; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6655 (class 0 OID 20578)
-- Dependencies: 356
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
boleta	B001	0
factura	F001	0
\.


--
-- TOC entry 6644 (class 0 OID 20482)
-- Dependencies: 345
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6642 (class 0 OID 20453)
-- Dependencies: 343
-- Data for Name: ventas; Type: TABLE DATA; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

COPY generic_pharma_jr_lima_tambo_408.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
\.


--
-- TOC entry 6755 (class 0 OID 21926)
-- Dependencies: 456
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6743 (class 0 OID 21816)
-- Dependencies: 444
-- Data for Name: cajas; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6737 (class 0 OID 21766)
-- Dependencies: 438
-- Data for Name: categorias; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
1	Medicamentos	Fármacos y medicamentos en general	t	2026-04-02 22:59:12.096454
2	Vitaminas y Suplementos	Vitaminas, minerales y suplementos nutricionales	t	2026-04-02 22:59:12.096454
3	Cuidado Personal	Productos de higiene y cuidado personal	t	2026-04-02 22:59:12.096454
4	Primeros Auxilios	Materiales de curación y primeros auxilios	t	2026-04-02 22:59:12.096454
5	Bebés y Niños	Productos para bebés y niños	t	2026-04-02 22:59:12.096454
6	Genéricos	Medicamentos genéricos	t	2026-04-02 22:59:12.096454
\.


--
-- TOC entry 6741 (class 0 OID 21803)
-- Dependencies: 442
-- Data for Name: clientes; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
1	Cliente	General	00000000	\N	000000000	\N	\N	t	2026-04-02 22:59:12.099564
\.


--
-- TOC entry 6757 (class 0 OID 21939)
-- Dependencies: 458
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6764 (class 0 OID 22000)
-- Dependencies: 465
-- Data for Name: cuentas_por_pagar; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.cuentas_por_pagar (id, proveedor_id, ingreso_id, orden_compra_id, numero_doc, monto_total, monto_pagado, monto_pendiente, estado, fecha_vencimiento, created_at) FROM stdin;
\.


--
-- TOC entry 6768 (class 0 OID 22047)
-- Dependencies: 469
-- Data for Name: gastos; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6753 (class 0 OID 21908)
-- Dependencies: 454
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6751 (class 0 OID 21889)
-- Dependencies: 452
-- Data for Name: ingresos; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at, tipo_pago, orden_compra_id) FROM stdin;
\.


--
-- TOC entry 6762 (class 0 OID 21983)
-- Dependencies: 463
-- Data for Name: orden_compra_detalles; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.orden_compra_detalles (id, orden_id, producto_id, descripcion, cantidad, precio_unitario, subtotal) FROM stdin;
\.


--
-- TOC entry 6760 (class 0 OID 21960)
-- Dependencies: 461
-- Data for Name: ordenes_compra; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.ordenes_compra (id, numero_orden, proveedor_id, usuario_id, estado, tipo_pago, dias_credito, subtotal, igv, total, observaciones, fecha_entrega, created_at) FROM stdin;
\.


--
-- TOC entry 6766 (class 0 OID 22025)
-- Dependencies: 467
-- Data for Name: pagos_proveedor; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.pagos_proveedor (id, cuenta_id, monto, metodo_pago, referencia, usuario_id, notas, created_at) FROM stdin;
\.


--
-- TOC entry 6739 (class 0 OID 21777)
-- Dependencies: 440
-- Data for Name: productos; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
\.


--
-- TOC entry 6749 (class 0 OID 21876)
-- Dependencies: 450
-- Data for Name: proveedores; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6758 (class 0 OID 21953)
-- Dependencies: 459
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
boleta	B001	0
factura	F001	0
\.


--
-- TOC entry 6747 (class 0 OID 21857)
-- Dependencies: 448
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6745 (class 0 OID 21828)
-- Dependencies: 446
-- Data for Name: ventas; Type: TABLE DATA; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

COPY generic_pharma_sucursal_de_prueba.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
\.


--
-- TOC entry 6598 (class 0 OID 19683)
-- Dependencies: 299
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6586 (class 0 OID 19573)
-- Dependencies: 287
-- Data for Name: cajas; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6580 (class 0 OID 19523)
-- Dependencies: 281
-- Data for Name: categorias; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6584 (class 0 OID 19560)
-- Dependencies: 285
-- Data for Name: clientes; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6600 (class 0 OID 19696)
-- Dependencies: 301
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6630 (class 0 OID 19941)
-- Dependencies: 331
-- Data for Name: gastos; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6596 (class 0 OID 19665)
-- Dependencies: 297
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6594 (class 0 OID 19646)
-- Dependencies: 295
-- Data for Name: ingresos; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at) FROM stdin;
\.


--
-- TOC entry 6582 (class 0 OID 19534)
-- Dependencies: 283
-- Data for Name: productos; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
\.


--
-- TOC entry 6592 (class 0 OID 19633)
-- Dependencies: 293
-- Data for Name: proveedores; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6601 (class 0 OID 19710)
-- Dependencies: 302
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
\.


--
-- TOC entry 6590 (class 0 OID 19614)
-- Dependencies: 291
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6588 (class 0 OID 19585)
-- Dependencies: 289
-- Data for Name: ventas; Type: TABLE DATA; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

COPY mari_boticas_sac_nueva_cajamarca.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
\.


--
-- TOC entry 6621 (class 0 OID 19878)
-- Dependencies: 322
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.caja_movimientos (id, caja_id, tipo, concepto, monto, usuario, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6609 (class 0 OID 19768)
-- Dependencies: 310
-- Data for Name: cajas; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6603 (class 0 OID 19718)
-- Dependencies: 304
-- Data for Name: categorias; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6607 (class 0 OID 19755)
-- Dependencies: 308
-- Data for Name: clientes; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6623 (class 0 OID 19891)
-- Dependencies: 324
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6632 (class 0 OID 19955)
-- Dependencies: 333
-- Data for Name: gastos; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
\.


--
-- TOC entry 6619 (class 0 OID 19860)
-- Dependencies: 320
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6617 (class 0 OID 19841)
-- Dependencies: 318
-- Data for Name: ingresos; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.ingresos (id, numero_ingreso, proveedor_id, usuario_id, total, estado, observaciones, created_at) FROM stdin;
\.


--
-- TOC entry 6605 (class 0 OID 19729)
-- Dependencies: 306
-- Data for Name: productos; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
\.


--
-- TOC entry 6615 (class 0 OID 19828)
-- Dependencies: 316
-- Data for Name: proveedores; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
\.


--
-- TOC entry 6624 (class 0 OID 19905)
-- Dependencies: 325
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.series_comprobantes (tipo, serie, ultimo_numero) FROM stdin;
\.


--
-- TOC entry 6613 (class 0 OID 19809)
-- Dependencies: 314
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6611 (class 0 OID 19780)
-- Dependencies: 312
-- Data for Name: ventas; Type: TABLE DATA; Schema: mari_boticas_sac_rioja; Owner: postgres
--

COPY mari_boticas_sac_rioja.ventas (id, numero_venta, cliente_id, caja_id, usuario_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
\.


--
-- TOC entry 6543 (class 0 OID 19173)
-- Dependencies: 244
-- Data for Name: caja_movimientos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.caja_movimientos (id, caja_id, tipo, monto, concepto, usuario, created_at) FROM stdin;
\.


--
-- TOC entry 6531 (class 0 OID 19060)
-- Dependencies: 232
-- Data for Name: cajas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cajas (id, nombre, saldo_inicial, saldo_actual, estado, apertura_at, cierre_at, usuario_apertura, created_at) FROM stdin;
1	Caja Principal	200.00	200.00	cerrada	2026-03-23 07:23:13.397449	2026-03-24 00:12:01.468947	Administrador	2026-03-23 07:23:13.397449
2	Caja Principal	200.00	200.00	cerrada	2026-03-28 15:59:58.915125	2026-03-28 16:00:28.254599	Sandy	2026-03-28 15:59:58.915125
3	Caja Principal	200.00	204.50	abierta	2026-03-28 16:00:51.748215	\N	Diana	2026-03-28 16:00:51.748215
\.


--
-- TOC entry 6525 (class 0 OID 19010)
-- Dependencies: 226
-- Data for Name: categorias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categorias (id, nombre, descripcion, activo, created_at) FROM stdin;
1	Medicamentos	Fármacos y medicamentos en general	t	2026-03-23 07:23:13.389688
2	Vitaminas y Suplementos	Vitaminas, minerales y suplementos nutricionales	t	2026-03-23 07:23:13.389688
3	Cuidado Personal	Productos de higiene y cuidado personal	t	2026-03-23 07:23:13.389688
4	Primeros Auxilios	Materiales de curación y primeros auxilios	t	2026-03-23 07:23:13.389688
5	Bebés y Niños	Productos para bebés y niños	t	2026-03-23 07:23:13.389688
6	Genéricos	Medicamentos genéricos	t	2026-03-23 07:23:13.389688
\.


--
-- TOC entry 6529 (class 0 OID 19047)
-- Dependencies: 230
-- Data for Name: clientes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.clientes (id, nombres, apellidos, dni, ruc, telefono, email, direccion, activo, created_at) FROM stdin;
1	Cliente	General	00000000	\N	000000000	\N	\N	t	2026-03-23 07:23:13.392296
2	María	García López	45678901	\N	987654321	\N	\N	t	2026-03-23 07:23:13.392296
3	Carlos	Mendoza Torres	32145678	\N	976543210	\N	\N	t	2026-03-23 07:23:13.392296
\.


--
-- TOC entry 6545 (class 0 OID 19188)
-- Dependencies: 246
-- Data for Name: comprobantes_electronicos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comprobantes_electronicos (id, venta_id, tipo, serie, numero, numero_completo, estado_sunat, enlace_del_pdf, enlace_del_xml, enlace_del_cdr, cadena_para_codigo_qr, nubefact_response, created_at) FROM stdin;
\.


--
-- TOC entry 6626 (class 0 OID 19913)
-- Dependencies: 327
-- Data for Name: gastos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gastos (id, caja_id, descripcion, proveedor, numero_comprobante, monto, metodo_pago, usuario_id, created_at) FROM stdin;
1	1	Pago de internet	Claro	001	50.00	efectivo	5	2026-03-29 02:30:42.248006
2	1	Pago de internet	Claro	001	50.00	efectivo	5	2026-03-29 02:30:43.68098
\.


--
-- TOC entry 6541 (class 0 OID 19155)
-- Dependencies: 242
-- Data for Name: ingreso_detalles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ingreso_detalles (id, ingreso_id, producto_id, cantidad, precio_unitario, subtotal, created_at) FROM stdin;
\.


--
-- TOC entry 6539 (class 0 OID 19133)
-- Dependencies: 240
-- Data for Name: ingresos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ingresos (id, numero_ingreso, proveedor_id, numero_factura, fecha_factura, subtotal, igv, total, estado, observaciones, usuario, created_at) FROM stdin;
\.


--
-- TOC entry 6733 (class 0 OID 21731)
-- Dependencies: 434
-- Data for Name: password_resets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_resets (id, usuario_id, token, expires_at, used, created_at, superadmin_id) FROM stdin;
\.


--
-- TOC entry 6527 (class 0 OID 19021)
-- Dependencies: 228
-- Data for Name: productos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.productos (id, codigo, nombre, descripcion, categoria_id, precio_compra, precio_venta, stock, stock_minimo, unidad, laboratorio, presentacion, requiere_receta, activo, favorito, total_vendido, created_at, updated_at, fecha_vencimiento, imagen_path) FROM stdin;
1	MED001	Paracetamol 500mg x 10 tab	\N	1	1.50	3.50	150	20	unidad	Genfarma	Tabletas x 10	f	t	t	245	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
2	MED002	Ibuprofeno 400mg x 10 tab	\N	1	2.00	4.50	80	15	unidad	Bayer	Tabletas x 10	f	t	t	189	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
3	MED003	Amoxicilina 500mg x 21 cap	\N	1	8.00	18.00	45	10	unidad	GlaxoSmithKline	Cápsulas x 21	f	t	f	67	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
4	VIT001	Vitamina C 500mg x 30 tab	\N	2	5.00	12.00	120	20	unidad	Farmex	Tabletas x 30	f	t	t	312	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
5	VIT002	Complejo B x 30 tab	\N	2	4.50	10.00	90	15	unidad	Farmindustria	Tabletas x 30	f	t	f	145	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
7	CUI002	Algodón 100g	\N	4	1.50	3.50	180	25	unidad	Genérico	Bolsa 100g	f	t	t	380	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
9	PRI002	Gasa estéril 10x10 x5	\N	4	2.50	5.50	100	15	unidad	Medilene	Pack x 5	f	t	f	98	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
10	GEN001	Omeprazol 20mg x 14 cap	\N	6	3.00	7.00	75	10	unidad	Genfarma	Cápsulas x 14	f	t	t	178	2026-03-23 07:23:13.393059	2026-03-23 07:23:13.393059	\N	\N
11	782752506695	ESCÁNER INALÁMBRICO DE CÓDIGO DE BARRAS 2D	\N	\N	75.00	85.00	1	5	unidad	Genérico	Caja	f	t	f	0	2026-03-23 07:38:12.305447	2026-03-23 07:38:12.305447	\N	\N
6	CUI001	Alcohol 70% 250ml	\N	3	2.00	4.50	198	30	unidad	Genérico	Frasco 250ml	f	t	t	422	2026-03-23 07:23:13.393059	2026-03-28 16:01:23.897397	\N	\N
8	PRI001	Agua Oxigenada 120ml	\N	4	1.00	2.50	160	20	unidad	Genérico	Frasco 120ml	f	f	f	220	2026-03-23 07:23:13.393059	2026-03-28 17:21:41.627431	\N	\N
\.


--
-- TOC entry 6537 (class 0 OID 19120)
-- Dependencies: 238
-- Data for Name: proveedores; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.proveedores (id, ruc, razon_social, nombre_comercial, telefono, email, direccion, contacto_nombre, activo, created_at) FROM stdin;
1	20100055858	LABORATORIOS BAYER S.A.	Bayer	01-6285500	ventas@bayer.pe	\N	\N	t	2026-03-24 00:05:05.066251
2	20503840703	FARMINDUSTRIA S.A.	Farmindustria	01-3172800	ventas@farmindustria.com.pe	\N	\N	t	2026-03-24 00:05:05.066251
3	20100372271	GLAXOSMITHKLINE PERU S.A.	GSK	01-6301000	info@gsk.com	\N	\N	t	2026-03-24 00:05:05.066251
4	20100077596	GENFARMA S.A.C.	Genfarma	01-3260000	ventas@genfarma.com.pe	\N	\N	t	2026-03-24 00:05:05.066251
\.


--
-- TOC entry 6547 (class 0 OID 19203)
-- Dependencies: 248
-- Data for Name: series_comprobantes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.series_comprobantes (id, tipo, serie, ultimo_numero) FROM stdin;
1	boleta	B001	0
2	factura	F001	0
\.


--
-- TOC entry 6551 (class 0 OID 19262)
-- Dependencies: 252
-- Data for Name: sucursales; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sucursales (id, tenant_id, nombre, schema_name, direccion, telefono, activo, created_at, archivada) FROM stdin;
1	1	Jr. Lima tambo 408	generic_pharma_jr_lima_tambo_408	Av. Lima	123456789	t	2026-03-29 03:03:52.955715	f
2	1	Alonso de Alvarado 209	generic_pharma_alonso_de_alvarado_2	Esquina con Ramon Castilla	123456789	t	2026-03-29 03:04:29.915986	f
3	1	Jr. Amorarca 129 - Morales	generic_pharma_jr_amorarca_129_mora	Plaza vea	123456789	t	2026-03-29 03:05:05.777055	f
4	1	Sucursal de prueba	generic_pharma_sucursal_de_prueba	Av. Lima	12345678	f	2026-04-02 22:59:12.101701	t
\.


--
-- TOC entry 6735 (class 0 OID 21747)
-- Dependencies: 436
-- Data for Name: superadmins; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.superadmins (id, nombre, apellido, username, password_hash, email, activo, created_at) FROM stdin;
2	Administrador	Sistema	admin	$2y$10$SGdfdAaIsBaPt6AgEIzkqeT6DExqhTYPRXD0mn7Y9s2Yzh1WMc6NW	\N	t	2026-04-02 22:26:36.266467
\.


--
-- TOC entry 6770 (class 0 OID 49188)
-- Dependencies: 471
-- Data for Name: tenant_config; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tenant_config (id, tenant_id, nombre_sistema, logo_path, updated_at) FROM stdin;
1	1	_	assets/img/logos/logo_1.png	2026-05-25 14:42:27.906043-05
\.


--
-- TOC entry 6549 (class 0 OID 19250)
-- Dependencies: 250
-- Data for Name: tenants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tenants (id, nombre, slug, plan, activo, created_at, url, ruc, telefono, direccion, logo) FROM stdin;
3	Generic Pharmca	generic_pharmca	enterprise	f	2026-04-02 22:42:00.044251		20345384739	979448821	Alonso de Alvarado	\N
2	Botica Mary	botica_mary	pro	t	2026-04-02 22:38:59.884589	http://localhost/farmacia/modules/auth/login.php	20123456945	932269582	Av. cajarma sur	assets/img/logos/logo_botica_mary.png
1	Generic Pharma	generic_pharma	pro	t	2026-03-29 03:03:00.554877					assets/img/logos/logo_generic_pharma.jpg
\.


--
-- TOC entry 6555 (class 0 OID 19298)
-- Dependencies: 256
-- Data for Name: usuario_sucursal; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario_sucursal (id, usuario_id, sucursal_id, rol, activo) FROM stdin;
5	8	1	cajero	t
6	7	2	admin	t
\.


--
-- TOC entry 6553 (class 0 OID 19280)
-- Dependencies: 254
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, tenant_id, nombre, apellido, username, password_hash, activo, created_at, email, observaciones) FROM stdin;
8	1	Administrador	Generic Pharma	admin_gp	$2y$10$zenaMmpu5gLH/pk0LQmUZerng8HwYvmZS4TIqSU8kfA79p72IDss.	t	2026-04-02 22:53:34.744953	\N	Admin principal - actualizado
7	1	Gloria	Muñoz	gloria	$2y$10$1u1Hw0bI6hnc.VhJRpsy1.OWhHiz/2/wmuP6VXcGIbrRv.Co8OhS.	t	2026-04-02 22:44:48.011046	\N	gloria gloria123
\.


--
-- TOC entry 6535 (class 0 OID 19101)
-- Dependencies: 236
-- Data for Name: venta_detalles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.venta_detalles (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal, created_at) FROM stdin;
1	1	6	1	4.50	0.00	4.50	2026-03-24 00:15:23.173734
2	2	6	1	4.50	0.00	4.50	2026-03-28 16:01:23.897397
\.


--
-- TOC entry 6533 (class 0 OID 19072)
-- Dependencies: 234
-- Data for Name: ventas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ventas (id, numero_venta, cliente_id, caja_id, subtotal, descuento, igv, total, tipo_pago, tipo_comprobante, estado, observaciones, vendedor, created_at) FROM stdin;
1	V20260324-0001	\N	\N	4.50	0.00	0.81	4.50	yape	factura	completada	\N	Administrador	2026-03-24 00:15:23.173734
2	V20260328-0001	\N	3	4.50	0.00	0.81	4.50	efectivo	ticket	completada	\N	Administrador	2026-03-28 16:01:23.897397
\.


--
-- TOC entry 6897 (class 0 OID 0)
-- Dependencies: 385
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.caja_movimientos_id_seq', 2, true);


--
-- TOC entry 6898 (class 0 OID 0)
-- Dependencies: 373
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.cajas_id_seq', 1, true);


--
-- TOC entry 6899 (class 0 OID 0)
-- Dependencies: 367
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.categorias_id_seq', 6, true);


--
-- TOC entry 6900 (class 0 OID 0)
-- Dependencies: 371
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.clientes_id_seq', 1, true);


--
-- TOC entry 6901 (class 0 OID 0)
-- Dependencies: 387
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos_id_seq', 8, true);


--
-- TOC entry 6902 (class 0 OID 0)
-- Dependencies: 394
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar_id_seq', 1, false);


--
-- TOC entry 6903 (class 0 OID 0)
-- Dependencies: 398
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.gastos_id_seq', 2, true);


--
-- TOC entry 6904 (class 0 OID 0)
-- Dependencies: 383
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6905 (class 0 OID 0)
-- Dependencies: 381
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.ingresos_id_seq', 1, false);


--
-- TOC entry 6906 (class 0 OID 0)
-- Dependencies: 392
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.orden_compra_detalles_id_seq', 1, true);


--
-- TOC entry 6907 (class 0 OID 0)
-- Dependencies: 390
-- Name: ordenes_compra_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.ordenes_compra_id_seq', 1, true);


--
-- TOC entry 6908 (class 0 OID 0)
-- Dependencies: 396
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.pagos_proveedor_id_seq', 1, false);


--
-- TOC entry 6909 (class 0 OID 0)
-- Dependencies: 369
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.productos_id_seq', 31, true);


--
-- TOC entry 6910 (class 0 OID 0)
-- Dependencies: 379
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.proveedores_id_seq', 1, true);


--
-- TOC entry 6911 (class 0 OID 0)
-- Dependencies: 377
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.venta_detalles_id_seq', 35, true);


--
-- TOC entry 6912 (class 0 OID 0)
-- Dependencies: 375
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_alonso_de_alvarado_2.ventas_id_seq', 15, true);


--
-- TOC entry 6913 (class 0 OID 0)
-- Dependencies: 418
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6914 (class 0 OID 0)
-- Dependencies: 406
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.cajas_id_seq', 1, false);


--
-- TOC entry 6915 (class 0 OID 0)
-- Dependencies: 400
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.categorias_id_seq', 6, true);


--
-- TOC entry 6916 (class 0 OID 0)
-- Dependencies: 404
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.clientes_id_seq', 1, true);


--
-- TOC entry 6917 (class 0 OID 0)
-- Dependencies: 420
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 6918 (class 0 OID 0)
-- Dependencies: 427
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar_id_seq', 1, false);


--
-- TOC entry 6919 (class 0 OID 0)
-- Dependencies: 431
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.gastos_id_seq', 1, false);


--
-- TOC entry 6920 (class 0 OID 0)
-- Dependencies: 416
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6921 (class 0 OID 0)
-- Dependencies: 414
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.ingresos_id_seq', 1, false);


--
-- TOC entry 6922 (class 0 OID 0)
-- Dependencies: 425
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.orden_compra_detalles_id_seq', 1, false);


--
-- TOC entry 6923 (class 0 OID 0)
-- Dependencies: 423
-- Name: ordenes_compra_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.ordenes_compra_id_seq', 1, false);


--
-- TOC entry 6924 (class 0 OID 0)
-- Dependencies: 429
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.pagos_proveedor_id_seq', 1, false);


--
-- TOC entry 6925 (class 0 OID 0)
-- Dependencies: 402
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.productos_id_seq', 30, true);


--
-- TOC entry 6926 (class 0 OID 0)
-- Dependencies: 412
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.proveedores_id_seq', 1, false);


--
-- TOC entry 6927 (class 0 OID 0)
-- Dependencies: 410
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.venta_detalles_id_seq', 1, false);


--
-- TOC entry 6928 (class 0 OID 0)
-- Dependencies: 408
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_amorarca_129_mora.ventas_id_seq', 1, false);


--
-- TOC entry 6929 (class 0 OID 0)
-- Dependencies: 275
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6930 (class 0 OID 0)
-- Dependencies: 263
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.cajas_id_seq', 1, false);


--
-- TOC entry 6931 (class 0 OID 0)
-- Dependencies: 257
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.categorias_id_seq', 1, false);


--
-- TOC entry 6932 (class 0 OID 0)
-- Dependencies: 261
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.clientes_id_seq', 1, false);


--
-- TOC entry 6933 (class 0 OID 0)
-- Dependencies: 277
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 6934 (class 0 OID 0)
-- Dependencies: 328
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.gastos_id_seq', 1, false);


--
-- TOC entry 6935 (class 0 OID 0)
-- Dependencies: 273
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6936 (class 0 OID 0)
-- Dependencies: 271
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.ingresos_id_seq', 1, false);


--
-- TOC entry 6937 (class 0 OID 0)
-- Dependencies: 259
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.productos_id_seq', 1, false);


--
-- TOC entry 6938 (class 0 OID 0)
-- Dependencies: 269
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.proveedores_id_seq', 1, false);


--
-- TOC entry 6939 (class 0 OID 0)
-- Dependencies: 267
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.venta_detalles_id_seq', 1, false);


--
-- TOC entry 6940 (class 0 OID 0)
-- Dependencies: 265
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima.ventas_id_seq', 1, false);


--
-- TOC entry 6941 (class 0 OID 0)
-- Dependencies: 352
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6942 (class 0 OID 0)
-- Dependencies: 340
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.cajas_id_seq', 1, false);


--
-- TOC entry 6943 (class 0 OID 0)
-- Dependencies: 334
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.categorias_id_seq', 6, true);


--
-- TOC entry 6944 (class 0 OID 0)
-- Dependencies: 338
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.clientes_id_seq', 1, true);


--
-- TOC entry 6945 (class 0 OID 0)
-- Dependencies: 354
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 6946 (class 0 OID 0)
-- Dependencies: 361
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.cuentas_por_pagar_id_seq', 1, false);


--
-- TOC entry 6947 (class 0 OID 0)
-- Dependencies: 365
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.gastos_id_seq', 1, false);


--
-- TOC entry 6948 (class 0 OID 0)
-- Dependencies: 350
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6949 (class 0 OID 0)
-- Dependencies: 348
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.ingresos_id_seq', 1, false);


--
-- TOC entry 6950 (class 0 OID 0)
-- Dependencies: 359
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.orden_compra_detalles_id_seq', 1, false);


--
-- TOC entry 6951 (class 0 OID 0)
-- Dependencies: 357
-- Name: ordenes_compra_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.ordenes_compra_id_seq', 1, false);


--
-- TOC entry 6952 (class 0 OID 0)
-- Dependencies: 363
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.pagos_proveedor_id_seq', 1, false);


--
-- TOC entry 6953 (class 0 OID 0)
-- Dependencies: 336
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.productos_id_seq', 30, true);


--
-- TOC entry 6954 (class 0 OID 0)
-- Dependencies: 346
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.proveedores_id_seq', 1, false);


--
-- TOC entry 6955 (class 0 OID 0)
-- Dependencies: 344
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.venta_detalles_id_seq', 1, false);


--
-- TOC entry 6956 (class 0 OID 0)
-- Dependencies: 342
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_jr_lima_tambo_408.ventas_id_seq', 1, false);


--
-- TOC entry 6957 (class 0 OID 0)
-- Dependencies: 455
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6958 (class 0 OID 0)
-- Dependencies: 443
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.cajas_id_seq', 1, false);


--
-- TOC entry 6959 (class 0 OID 0)
-- Dependencies: 437
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.categorias_id_seq', 6, true);


--
-- TOC entry 6960 (class 0 OID 0)
-- Dependencies: 441
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.clientes_id_seq', 1, true);


--
-- TOC entry 6961 (class 0 OID 0)
-- Dependencies: 457
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 6962 (class 0 OID 0)
-- Dependencies: 464
-- Name: cuentas_por_pagar_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.cuentas_por_pagar_id_seq', 1, false);


--
-- TOC entry 6963 (class 0 OID 0)
-- Dependencies: 468
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.gastos_id_seq', 1, false);


--
-- TOC entry 6964 (class 0 OID 0)
-- Dependencies: 453
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6965 (class 0 OID 0)
-- Dependencies: 451
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.ingresos_id_seq', 1, false);


--
-- TOC entry 6966 (class 0 OID 0)
-- Dependencies: 462
-- Name: orden_compra_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.orden_compra_detalles_id_seq', 1, false);


--
-- TOC entry 6967 (class 0 OID 0)
-- Dependencies: 460
-- Name: ordenes_compra_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.ordenes_compra_id_seq', 1, false);


--
-- TOC entry 6968 (class 0 OID 0)
-- Dependencies: 466
-- Name: pagos_proveedor_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.pagos_proveedor_id_seq', 1, false);


--
-- TOC entry 6969 (class 0 OID 0)
-- Dependencies: 439
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.productos_id_seq', 1, false);


--
-- TOC entry 6970 (class 0 OID 0)
-- Dependencies: 449
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.proveedores_id_seq', 1, false);


--
-- TOC entry 6971 (class 0 OID 0)
-- Dependencies: 447
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.venta_detalles_id_seq', 1, false);


--
-- TOC entry 6972 (class 0 OID 0)
-- Dependencies: 445
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

SELECT pg_catalog.setval('generic_pharma_sucursal_de_prueba.ventas_id_seq', 1, false);


--
-- TOC entry 6973 (class 0 OID 0)
-- Dependencies: 298
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6974 (class 0 OID 0)
-- Dependencies: 286
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.cajas_id_seq', 1, false);


--
-- TOC entry 6975 (class 0 OID 0)
-- Dependencies: 280
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.categorias_id_seq', 1, false);


--
-- TOC entry 6976 (class 0 OID 0)
-- Dependencies: 284
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.clientes_id_seq', 1, false);


--
-- TOC entry 6977 (class 0 OID 0)
-- Dependencies: 300
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 6978 (class 0 OID 0)
-- Dependencies: 330
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.gastos_id_seq', 1, false);


--
-- TOC entry 6979 (class 0 OID 0)
-- Dependencies: 296
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6980 (class 0 OID 0)
-- Dependencies: 294
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.ingresos_id_seq', 1, false);


--
-- TOC entry 6981 (class 0 OID 0)
-- Dependencies: 282
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.productos_id_seq', 1, false);


--
-- TOC entry 6982 (class 0 OID 0)
-- Dependencies: 292
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.proveedores_id_seq', 1, false);


--
-- TOC entry 6983 (class 0 OID 0)
-- Dependencies: 290
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.venta_detalles_id_seq', 1, false);


--
-- TOC entry 6984 (class 0 OID 0)
-- Dependencies: 288
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_nueva_cajamarca.ventas_id_seq', 1, false);


--
-- TOC entry 6985 (class 0 OID 0)
-- Dependencies: 321
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6986 (class 0 OID 0)
-- Dependencies: 309
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.cajas_id_seq', 1, false);


--
-- TOC entry 6987 (class 0 OID 0)
-- Dependencies: 303
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.categorias_id_seq', 1, false);


--
-- TOC entry 6988 (class 0 OID 0)
-- Dependencies: 307
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.clientes_id_seq', 1, false);


--
-- TOC entry 6989 (class 0 OID 0)
-- Dependencies: 323
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 6990 (class 0 OID 0)
-- Dependencies: 332
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.gastos_id_seq', 1, false);


--
-- TOC entry 6991 (class 0 OID 0)
-- Dependencies: 319
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 6992 (class 0 OID 0)
-- Dependencies: 317
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.ingresos_id_seq', 1, false);


--
-- TOC entry 6993 (class 0 OID 0)
-- Dependencies: 305
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.productos_id_seq', 1, false);


--
-- TOC entry 6994 (class 0 OID 0)
-- Dependencies: 315
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.proveedores_id_seq', 1, false);


--
-- TOC entry 6995 (class 0 OID 0)
-- Dependencies: 313
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.venta_detalles_id_seq', 1, false);


--
-- TOC entry 6996 (class 0 OID 0)
-- Dependencies: 311
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: mari_boticas_sac_rioja; Owner: postgres
--

SELECT pg_catalog.setval('mari_boticas_sac_rioja.ventas_id_seq', 1, false);


--
-- TOC entry 6997 (class 0 OID 0)
-- Dependencies: 243
-- Name: caja_movimientos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.caja_movimientos_id_seq', 1, false);


--
-- TOC entry 6998 (class 0 OID 0)
-- Dependencies: 231
-- Name: cajas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cajas_id_seq', 3, true);


--
-- TOC entry 6999 (class 0 OID 0)
-- Dependencies: 225
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categorias_id_seq', 6, true);


--
-- TOC entry 7000 (class 0 OID 0)
-- Dependencies: 229
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.clientes_id_seq', 3, true);


--
-- TOC entry 7001 (class 0 OID 0)
-- Dependencies: 245
-- Name: comprobantes_electronicos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comprobantes_electronicos_id_seq', 1, false);


--
-- TOC entry 7002 (class 0 OID 0)
-- Dependencies: 326
-- Name: gastos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gastos_id_seq', 2, true);


--
-- TOC entry 7003 (class 0 OID 0)
-- Dependencies: 241
-- Name: ingreso_detalles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ingreso_detalles_id_seq', 1, false);


--
-- TOC entry 7004 (class 0 OID 0)
-- Dependencies: 239
-- Name: ingresos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ingresos_id_seq', 1, false);


--
-- TOC entry 7005 (class 0 OID 0)
-- Dependencies: 433
-- Name: password_resets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.password_resets_id_seq', 2, true);


--
-- TOC entry 7006 (class 0 OID 0)
-- Dependencies: 227
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.productos_id_seq', 11, true);


--
-- TOC entry 7007 (class 0 OID 0)
-- Dependencies: 237
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.proveedores_id_seq', 4, true);


--
-- TOC entry 7008 (class 0 OID 0)
-- Dependencies: 247
-- Name: series_comprobantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.series_comprobantes_id_seq', 2, true);


--
-- TOC entry 7009 (class 0 OID 0)
-- Dependencies: 251
-- Name: sucursales_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sucursales_id_seq', 4, true);


--
-- TOC entry 7010 (class 0 OID 0)
-- Dependencies: 435
-- Name: superadmins_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.superadmins_id_seq', 2, true);


--
-- TOC entry 7011 (class 0 OID 0)
-- Dependencies: 470
-- Name: tenant_config_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tenant_config_id_seq', 7, true);


--
-- TOC entry 7012 (class 0 OID 0)
-- Dependencies: 249
-- Name: tenants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tenants_id_seq', 3, true);


--
-- TOC entry 7013 (class 0 OID 0)
-- Dependencies: 255
-- Name: usuario_sucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_sucursal_id_seq', 9, true);


--
-- TOC entry 7014 (class 0 OID 0)
-- Dependencies: 253
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 8, true);


--
-- TOC entry 7015 (class 0 OID 0)
-- Dependencies: 235
-- Name: venta_detalles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.venta_detalles_id_seq', 2, true);


--
-- TOC entry 7016 (class 0 OID 0)
-- Dependencies: 233
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ventas_id_seq', 2, true);


--
-- TOC entry 6132 (class 2606 OID 20854)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 6114 (class 2606 OID 20748)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 6104 (class 2606 OID 20697)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 6110 (class 2606 OID 20736)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 6112 (class 2606 OID 20734)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 6134 (class 2606 OID 20869)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6144 (class 2606 OID 20930)
-- Name: cuentas_por_pagar cuentas_por_pagar_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_pkey PRIMARY KEY (id);


--
-- TOC entry 6148 (class 2606 OID 20976)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 6130 (class 2606 OID 20836)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6126 (class 2606 OID 20823)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 6128 (class 2606 OID 20821)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 6142 (class 2606 OID 20910)
-- Name: orden_compra_detalles orden_compra_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6138 (class 2606 OID 20898)
-- Name: ordenes_compra ordenes_compra_numero_orden_key; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ordenes_compra
    ADD CONSTRAINT ordenes_compra_numero_orden_key UNIQUE (numero_orden);


--
-- TOC entry 6140 (class 2606 OID 20896)
-- Name: ordenes_compra ordenes_compra_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ordenes_compra
    ADD CONSTRAINT ordenes_compra_pkey PRIMARY KEY (id);


--
-- TOC entry 6146 (class 2606 OID 20956)
-- Name: pagos_proveedor pagos_proveedor_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_pkey PRIMARY KEY (id);


--
-- TOC entry 6106 (class 2606 OID 20718)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 6108 (class 2606 OID 20716)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 6122 (class 2606 OID 20807)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 6124 (class 2606 OID 20809)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 6136 (class 2606 OID 20880)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 6120 (class 2606 OID 20786)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6116 (class 2606 OID 20767)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 6118 (class 2606 OID 20765)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 6178 (class 2606 OID 21150)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 6160 (class 2606 OID 21044)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 6150 (class 2606 OID 20993)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 6156 (class 2606 OID 21032)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 6158 (class 2606 OID 21030)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 6180 (class 2606 OID 21165)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6190 (class 2606 OID 21226)
-- Name: cuentas_por_pagar cuentas_por_pagar_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_pkey PRIMARY KEY (id);


--
-- TOC entry 6194 (class 2606 OID 21272)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 6176 (class 2606 OID 21132)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6172 (class 2606 OID 21119)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 6174 (class 2606 OID 21117)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 6188 (class 2606 OID 21206)
-- Name: orden_compra_detalles orden_compra_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6184 (class 2606 OID 21194)
-- Name: ordenes_compra ordenes_compra_numero_orden_key; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ordenes_compra
    ADD CONSTRAINT ordenes_compra_numero_orden_key UNIQUE (numero_orden);


--
-- TOC entry 6186 (class 2606 OID 21192)
-- Name: ordenes_compra ordenes_compra_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ordenes_compra
    ADD CONSTRAINT ordenes_compra_pkey PRIMARY KEY (id);


--
-- TOC entry 6192 (class 2606 OID 21252)
-- Name: pagos_proveedor pagos_proveedor_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_pkey PRIMARY KEY (id);


--
-- TOC entry 6152 (class 2606 OID 21014)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 6154 (class 2606 OID 21012)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 6168 (class 2606 OID 21103)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 6170 (class 2606 OID 21105)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 6182 (class 2606 OID 21176)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 6166 (class 2606 OID 21082)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6162 (class 2606 OID 21063)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 6164 (class 2606 OID 21061)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 5976 (class 2606 OID 19493)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 5958 (class 2606 OID 19387)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 5948 (class 2606 OID 19336)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 5954 (class 2606 OID 19375)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 5956 (class 2606 OID 19373)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 5978 (class 2606 OID 19508)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6052 (class 2606 OID 19934)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 5974 (class 2606 OID 19475)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 5970 (class 2606 OID 19462)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 5972 (class 2606 OID 19460)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 5950 (class 2606 OID 19357)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 5952 (class 2606 OID 19355)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 5966 (class 2606 OID 19446)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 5968 (class 2606 OID 19448)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 5980 (class 2606 OID 19519)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 5964 (class 2606 OID 19425)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 5960 (class 2606 OID 19406)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 5962 (class 2606 OID 19404)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 6086 (class 2606 OID 20557)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 6068 (class 2606 OID 20451)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 6058 (class 2606 OID 20400)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 6064 (class 2606 OID 20439)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 6066 (class 2606 OID 20437)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 6088 (class 2606 OID 20572)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6098 (class 2606 OID 20633)
-- Name: cuentas_por_pagar cuentas_por_pagar_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_pkey PRIMARY KEY (id);


--
-- TOC entry 6102 (class 2606 OID 20679)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 6084 (class 2606 OID 20539)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6080 (class 2606 OID 20526)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 6082 (class 2606 OID 20524)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 6096 (class 2606 OID 20613)
-- Name: orden_compra_detalles orden_compra_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6092 (class 2606 OID 20601)
-- Name: ordenes_compra ordenes_compra_numero_orden_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ordenes_compra
    ADD CONSTRAINT ordenes_compra_numero_orden_key UNIQUE (numero_orden);


--
-- TOC entry 6094 (class 2606 OID 20599)
-- Name: ordenes_compra ordenes_compra_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ordenes_compra
    ADD CONSTRAINT ordenes_compra_pkey PRIMARY KEY (id);


--
-- TOC entry 6100 (class 2606 OID 20659)
-- Name: pagos_proveedor pagos_proveedor_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_pkey PRIMARY KEY (id);


--
-- TOC entry 6060 (class 2606 OID 20421)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 6062 (class 2606 OID 20419)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 6076 (class 2606 OID 20510)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 6078 (class 2606 OID 20512)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 6090 (class 2606 OID 20583)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 6074 (class 2606 OID 20489)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6070 (class 2606 OID 20470)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 6072 (class 2606 OID 20468)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 6232 (class 2606 OID 21932)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 6214 (class 2606 OID 21826)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 6204 (class 2606 OID 21775)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 6210 (class 2606 OID 21814)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 6212 (class 2606 OID 21812)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 6234 (class 2606 OID 21947)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6244 (class 2606 OID 22008)
-- Name: cuentas_por_pagar cuentas_por_pagar_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_pkey PRIMARY KEY (id);


--
-- TOC entry 6248 (class 2606 OID 22054)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 6230 (class 2606 OID 21914)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6226 (class 2606 OID 21901)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 6228 (class 2606 OID 21899)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 6242 (class 2606 OID 21988)
-- Name: orden_compra_detalles orden_compra_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6238 (class 2606 OID 21976)
-- Name: ordenes_compra ordenes_compra_numero_orden_key; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ordenes_compra
    ADD CONSTRAINT ordenes_compra_numero_orden_key UNIQUE (numero_orden);


--
-- TOC entry 6240 (class 2606 OID 21974)
-- Name: ordenes_compra ordenes_compra_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ordenes_compra
    ADD CONSTRAINT ordenes_compra_pkey PRIMARY KEY (id);


--
-- TOC entry 6246 (class 2606 OID 22034)
-- Name: pagos_proveedor pagos_proveedor_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_pkey PRIMARY KEY (id);


--
-- TOC entry 6206 (class 2606 OID 21796)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 6208 (class 2606 OID 21794)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 6222 (class 2606 OID 21885)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 6224 (class 2606 OID 21887)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 6236 (class 2606 OID 21958)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 6220 (class 2606 OID 21864)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6216 (class 2606 OID 21845)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 6218 (class 2606 OID 21843)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 6010 (class 2606 OID 19689)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 5992 (class 2606 OID 19583)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 5982 (class 2606 OID 19532)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 5988 (class 2606 OID 19571)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 5990 (class 2606 OID 19569)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 6012 (class 2606 OID 19704)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6054 (class 2606 OID 19948)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 6008 (class 2606 OID 19671)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6004 (class 2606 OID 19658)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 6006 (class 2606 OID 19656)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 5984 (class 2606 OID 19553)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 5986 (class 2606 OID 19551)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 6000 (class 2606 OID 19642)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 6002 (class 2606 OID 19644)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 6014 (class 2606 OID 19715)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 5998 (class 2606 OID 19621)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 5994 (class 2606 OID 19602)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 5996 (class 2606 OID 19600)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 6044 (class 2606 OID 19884)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 6026 (class 2606 OID 19778)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 6016 (class 2606 OID 19727)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 6022 (class 2606 OID 19766)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 6024 (class 2606 OID 19764)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 6046 (class 2606 OID 19899)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6056 (class 2606 OID 19962)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 6042 (class 2606 OID 19866)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6038 (class 2606 OID 19853)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 6040 (class 2606 OID 19851)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 6018 (class 2606 OID 19748)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 6020 (class 2606 OID 19746)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 6034 (class 2606 OID 19837)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 6036 (class 2606 OID 19839)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 6048 (class 2606 OID 19910)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (tipo);


--
-- TOC entry 6032 (class 2606 OID 19816)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 6028 (class 2606 OID 19797)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 6030 (class 2606 OID 19795)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 5924 (class 2606 OID 19180)
-- Name: caja_movimientos caja_movimientos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.caja_movimientos
    ADD CONSTRAINT caja_movimientos_pkey PRIMARY KEY (id);


--
-- TOC entry 5906 (class 2606 OID 19070)
-- Name: cajas cajas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cajas
    ADD CONSTRAINT cajas_pkey PRIMARY KEY (id);


--
-- TOC entry 5896 (class 2606 OID 19019)
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- TOC entry 5902 (class 2606 OID 19058)
-- Name: clientes clientes_dni_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_dni_key UNIQUE (dni);


--
-- TOC entry 5904 (class 2606 OID 19056)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 5926 (class 2606 OID 19196)
-- Name: comprobantes_electronicos comprobantes_electronicos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_pkey PRIMARY KEY (id);


--
-- TOC entry 6050 (class 2606 OID 19920)
-- Name: gastos gastos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gastos
    ADD CONSTRAINT gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 5922 (class 2606 OID 19161)
-- Name: ingreso_detalles ingreso_detalles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 5918 (class 2606 OID 19148)
-- Name: ingresos ingresos_numero_ingreso_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingresos
    ADD CONSTRAINT ingresos_numero_ingreso_key UNIQUE (numero_ingreso);


--
-- TOC entry 5920 (class 2606 OID 19146)
-- Name: ingresos ingresos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingresos
    ADD CONSTRAINT ingresos_pkey PRIMARY KEY (id);


--
-- TOC entry 6196 (class 2606 OID 21738)
-- Name: password_resets password_resets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_pkey PRIMARY KEY (id);


--
-- TOC entry 6198 (class 2606 OID 21740)
-- Name: password_resets password_resets_token_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_token_key UNIQUE (token);


--
-- TOC entry 5898 (class 2606 OID 19040)
-- Name: productos productos_codigo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_codigo_key UNIQUE (codigo);


--
-- TOC entry 5900 (class 2606 OID 19038)
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- TOC entry 5914 (class 2606 OID 19129)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 5916 (class 2606 OID 19131)
-- Name: proveedores proveedores_ruc_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedores
    ADD CONSTRAINT proveedores_ruc_key UNIQUE (ruc);


--
-- TOC entry 5928 (class 2606 OID 19209)
-- Name: series_comprobantes series_comprobantes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.series_comprobantes
    ADD CONSTRAINT series_comprobantes_pkey PRIMARY KEY (id);


--
-- TOC entry 5930 (class 2606 OID 19211)
-- Name: series_comprobantes series_comprobantes_tipo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.series_comprobantes
    ADD CONSTRAINT series_comprobantes_tipo_key UNIQUE (tipo);


--
-- TOC entry 5936 (class 2606 OID 19271)
-- Name: sucursales sucursales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursales
    ADD CONSTRAINT sucursales_pkey PRIMARY KEY (id);


--
-- TOC entry 5938 (class 2606 OID 19273)
-- Name: sucursales sucursales_schema_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursales
    ADD CONSTRAINT sucursales_schema_name_key UNIQUE (schema_name);


--
-- TOC entry 6200 (class 2606 OID 21756)
-- Name: superadmins superadmins_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.superadmins
    ADD CONSTRAINT superadmins_pkey PRIMARY KEY (id);


--
-- TOC entry 6202 (class 2606 OID 21758)
-- Name: superadmins superadmins_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.superadmins
    ADD CONSTRAINT superadmins_username_key UNIQUE (username);


--
-- TOC entry 6250 (class 2606 OID 49195)
-- Name: tenant_config tenant_config_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tenant_config
    ADD CONSTRAINT tenant_config_pkey PRIMARY KEY (id);


--
-- TOC entry 6252 (class 2606 OID 49197)
-- Name: tenant_config tenant_config_tenant_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tenant_config
    ADD CONSTRAINT tenant_config_tenant_id_key UNIQUE (tenant_id);


--
-- TOC entry 5932 (class 2606 OID 19258)
-- Name: tenants tenants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_pkey PRIMARY KEY (id);


--
-- TOC entry 5934 (class 2606 OID 19260)
-- Name: tenants tenants_slug_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_slug_key UNIQUE (slug);


--
-- TOC entry 5944 (class 2606 OID 19305)
-- Name: usuario_sucursal usuario_sucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sucursal
    ADD CONSTRAINT usuario_sucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5946 (class 2606 OID 19307)
-- Name: usuario_sucursal usuario_sucursal_usuario_id_sucursal_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sucursal
    ADD CONSTRAINT usuario_sucursal_usuario_id_sucursal_id_key UNIQUE (usuario_id, sucursal_id);


--
-- TOC entry 5940 (class 2606 OID 19289)
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- TOC entry 5942 (class 2606 OID 19291)
-- Name: usuarios usuarios_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_username_key UNIQUE (username);


--
-- TOC entry 5912 (class 2606 OID 19108)
-- Name: venta_detalles venta_detalles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.venta_detalles
    ADD CONSTRAINT venta_detalles_pkey PRIMARY KEY (id);


--
-- TOC entry 5908 (class 2606 OID 19089)
-- Name: ventas ventas_numero_venta_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_numero_venta_key UNIQUE (numero_venta);


--
-- TOC entry 5910 (class 2606 OID 19087)
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- TOC entry 6329 (class 2606 OID 20855)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_alonso_de_alvarado_2.cajas(id);


--
-- TOC entry 6330 (class 2606 OID 20870)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ventas(id);


--
-- TOC entry 6334 (class 2606 OID 20936)
-- Name: cuentas_por_pagar cuentas_por_pagar_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ingresos(id);


--
-- TOC entry 6335 (class 2606 OID 20941)
-- Name: cuentas_por_pagar cuentas_por_pagar_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ordenes_compra(id);


--
-- TOC entry 6336 (class 2606 OID 20931)
-- Name: cuentas_por_pagar cuentas_por_pagar_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_alonso_de_alvarado_2.proveedores(id);


--
-- TOC entry 6338 (class 2606 OID 20977)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_alonso_de_alvarado_2.cajas(id);


--
-- TOC entry 6327 (class 2606 OID 20837)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6328 (class 2606 OID 20842)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_alonso_de_alvarado_2.productos(id);


--
-- TOC entry 6325 (class 2606 OID 20963)
-- Name: ingresos ingresos_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingresos
    ADD CONSTRAINT ingresos_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ordenes_compra(id);


--
-- TOC entry 6326 (class 2606 OID 20824)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_alonso_de_alvarado_2.proveedores(id);


--
-- TOC entry 6332 (class 2606 OID 20911)
-- Name: orden_compra_detalles orden_compra_detalles_orden_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_orden_id_fkey FOREIGN KEY (orden_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ordenes_compra(id) ON DELETE CASCADE;


--
-- TOC entry 6333 (class 2606 OID 20916)
-- Name: orden_compra_detalles orden_compra_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_alonso_de_alvarado_2.productos(id);


--
-- TOC entry 6331 (class 2606 OID 20899)
-- Name: ordenes_compra ordenes_compra_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ordenes_compra
    ADD CONSTRAINT ordenes_compra_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_alonso_de_alvarado_2.proveedores(id);


--
-- TOC entry 6337 (class 2606 OID 20957)
-- Name: pagos_proveedor pagos_proveedor_cuenta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_cuenta_id_fkey FOREIGN KEY (cuenta_id) REFERENCES generic_pharma_alonso_de_alvarado_2.cuentas_por_pagar(id);


--
-- TOC entry 6320 (class 2606 OID 20719)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES generic_pharma_alonso_de_alvarado_2.categorias(id);


--
-- TOC entry 6323 (class 2606 OID 20792)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_alonso_de_alvarado_2.productos(id);


--
-- TOC entry 6324 (class 2606 OID 20787)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_alonso_de_alvarado_2.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6321 (class 2606 OID 20773)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_alonso_de_alvarado_2.cajas(id);


--
-- TOC entry 6322 (class 2606 OID 20768)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_alonso_de_alvarado_2; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_alonso_de_alvarado_2.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES generic_pharma_alonso_de_alvarado_2.clientes(id);


--
-- TOC entry 6348 (class 2606 OID 21151)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_amorarca_129_mora.cajas(id);


--
-- TOC entry 6349 (class 2606 OID 21166)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ventas(id);


--
-- TOC entry 6353 (class 2606 OID 21232)
-- Name: cuentas_por_pagar cuentas_por_pagar_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ingresos(id);


--
-- TOC entry 6354 (class 2606 OID 21237)
-- Name: cuentas_por_pagar cuentas_por_pagar_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ordenes_compra(id);


--
-- TOC entry 6355 (class 2606 OID 21227)
-- Name: cuentas_por_pagar cuentas_por_pagar_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_amorarca_129_mora.proveedores(id);


--
-- TOC entry 6357 (class 2606 OID 21273)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_amorarca_129_mora.cajas(id);


--
-- TOC entry 6346 (class 2606 OID 21133)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6347 (class 2606 OID 21138)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_amorarca_129_mora.productos(id);


--
-- TOC entry 6344 (class 2606 OID 21259)
-- Name: ingresos ingresos_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingresos
    ADD CONSTRAINT ingresos_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ordenes_compra(id);


--
-- TOC entry 6345 (class 2606 OID 21120)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_amorarca_129_mora.proveedores(id);


--
-- TOC entry 6351 (class 2606 OID 21207)
-- Name: orden_compra_detalles orden_compra_detalles_orden_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_orden_id_fkey FOREIGN KEY (orden_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ordenes_compra(id) ON DELETE CASCADE;


--
-- TOC entry 6352 (class 2606 OID 21212)
-- Name: orden_compra_detalles orden_compra_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_amorarca_129_mora.productos(id);


--
-- TOC entry 6350 (class 2606 OID 21195)
-- Name: ordenes_compra ordenes_compra_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ordenes_compra
    ADD CONSTRAINT ordenes_compra_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_amorarca_129_mora.proveedores(id);


--
-- TOC entry 6356 (class 2606 OID 21253)
-- Name: pagos_proveedor pagos_proveedor_cuenta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_cuenta_id_fkey FOREIGN KEY (cuenta_id) REFERENCES generic_pharma_jr_amorarca_129_mora.cuentas_por_pagar(id);


--
-- TOC entry 6339 (class 2606 OID 21015)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES generic_pharma_jr_amorarca_129_mora.categorias(id);


--
-- TOC entry 6342 (class 2606 OID 21088)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_amorarca_129_mora.productos(id);


--
-- TOC entry 6343 (class 2606 OID 21083)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_jr_amorarca_129_mora.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6340 (class 2606 OID 21069)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_amorarca_129_mora.cajas(id);


--
-- TOC entry 6341 (class 2606 OID 21064)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_amorarca_129_mora; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_amorarca_129_mora.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES generic_pharma_jr_amorarca_129_mora.clientes(id);


--
-- TOC entry 6275 (class 2606 OID 19494)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_lima.cajas(id);


--
-- TOC entry 6276 (class 2606 OID 19509)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_jr_lima.ventas(id);


--
-- TOC entry 6298 (class 2606 OID 19935)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_lima.cajas(id);


--
-- TOC entry 6273 (class 2606 OID 19476)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_jr_lima.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6274 (class 2606 OID 19481)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_lima.productos(id);


--
-- TOC entry 6272 (class 2606 OID 19463)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_lima.proveedores(id);


--
-- TOC entry 6267 (class 2606 OID 19358)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES generic_pharma_jr_lima.categorias(id);


--
-- TOC entry 6270 (class 2606 OID 19431)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_lima.productos(id);


--
-- TOC entry 6271 (class 2606 OID 19426)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_jr_lima.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6268 (class 2606 OID 19412)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_lima.cajas(id);


--
-- TOC entry 6269 (class 2606 OID 19407)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES generic_pharma_jr_lima.clientes(id);


--
-- TOC entry 6310 (class 2606 OID 20558)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_lima_tambo_408.cajas(id);


--
-- TOC entry 6311 (class 2606 OID 20573)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_jr_lima_tambo_408.ventas(id);


--
-- TOC entry 6315 (class 2606 OID 20639)
-- Name: cuentas_por_pagar cuentas_por_pagar_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_jr_lima_tambo_408.ingresos(id);


--
-- TOC entry 6316 (class 2606 OID 20644)
-- Name: cuentas_por_pagar cuentas_por_pagar_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_jr_lima_tambo_408.ordenes_compra(id);


--
-- TOC entry 6317 (class 2606 OID 20634)
-- Name: cuentas_por_pagar cuentas_por_pagar_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_lima_tambo_408.proveedores(id);


--
-- TOC entry 6319 (class 2606 OID 20680)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_lima_tambo_408.cajas(id);


--
-- TOC entry 6308 (class 2606 OID 20540)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_jr_lima_tambo_408.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6309 (class 2606 OID 20545)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_lima_tambo_408.productos(id);


--
-- TOC entry 6306 (class 2606 OID 20666)
-- Name: ingresos ingresos_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingresos
    ADD CONSTRAINT ingresos_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_jr_lima_tambo_408.ordenes_compra(id);


--
-- TOC entry 6307 (class 2606 OID 20527)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_lima_tambo_408.proveedores(id);


--
-- TOC entry 6313 (class 2606 OID 20614)
-- Name: orden_compra_detalles orden_compra_detalles_orden_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_orden_id_fkey FOREIGN KEY (orden_id) REFERENCES generic_pharma_jr_lima_tambo_408.ordenes_compra(id) ON DELETE CASCADE;


--
-- TOC entry 6314 (class 2606 OID 20619)
-- Name: orden_compra_detalles orden_compra_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_lima_tambo_408.productos(id);


--
-- TOC entry 6312 (class 2606 OID 20602)
-- Name: ordenes_compra ordenes_compra_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ordenes_compra
    ADD CONSTRAINT ordenes_compra_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_jr_lima_tambo_408.proveedores(id);


--
-- TOC entry 6318 (class 2606 OID 20660)
-- Name: pagos_proveedor pagos_proveedor_cuenta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_cuenta_id_fkey FOREIGN KEY (cuenta_id) REFERENCES generic_pharma_jr_lima_tambo_408.cuentas_por_pagar(id);


--
-- TOC entry 6301 (class 2606 OID 20422)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES generic_pharma_jr_lima_tambo_408.categorias(id);


--
-- TOC entry 6304 (class 2606 OID 20495)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_jr_lima_tambo_408.productos(id);


--
-- TOC entry 6305 (class 2606 OID 20490)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_jr_lima_tambo_408.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6302 (class 2606 OID 20476)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_jr_lima_tambo_408.cajas(id);


--
-- TOC entry 6303 (class 2606 OID 20471)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_jr_lima_tambo_408; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_jr_lima_tambo_408.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES generic_pharma_jr_lima_tambo_408.clientes(id);


--
-- TOC entry 6369 (class 2606 OID 21933)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_sucursal_de_prueba.cajas(id);


--
-- TOC entry 6370 (class 2606 OID 21948)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_sucursal_de_prueba.ventas(id);


--
-- TOC entry 6374 (class 2606 OID 22014)
-- Name: cuentas_por_pagar cuentas_por_pagar_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_sucursal_de_prueba.ingresos(id);


--
-- TOC entry 6375 (class 2606 OID 22019)
-- Name: cuentas_por_pagar cuentas_por_pagar_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_sucursal_de_prueba.ordenes_compra(id);


--
-- TOC entry 6376 (class 2606 OID 22009)
-- Name: cuentas_por_pagar cuentas_por_pagar_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.cuentas_por_pagar
    ADD CONSTRAINT cuentas_por_pagar_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_sucursal_de_prueba.proveedores(id);


--
-- TOC entry 6378 (class 2606 OID 22055)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_sucursal_de_prueba.cajas(id);


--
-- TOC entry 6367 (class 2606 OID 21915)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES generic_pharma_sucursal_de_prueba.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6368 (class 2606 OID 21920)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_sucursal_de_prueba.productos(id);


--
-- TOC entry 6365 (class 2606 OID 22041)
-- Name: ingresos ingresos_orden_compra_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingresos
    ADD CONSTRAINT ingresos_orden_compra_id_fkey FOREIGN KEY (orden_compra_id) REFERENCES generic_pharma_sucursal_de_prueba.ordenes_compra(id);


--
-- TOC entry 6366 (class 2606 OID 21902)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_sucursal_de_prueba.proveedores(id);


--
-- TOC entry 6372 (class 2606 OID 21989)
-- Name: orden_compra_detalles orden_compra_detalles_orden_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_orden_id_fkey FOREIGN KEY (orden_id) REFERENCES generic_pharma_sucursal_de_prueba.ordenes_compra(id) ON DELETE CASCADE;


--
-- TOC entry 6373 (class 2606 OID 21994)
-- Name: orden_compra_detalles orden_compra_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.orden_compra_detalles
    ADD CONSTRAINT orden_compra_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_sucursal_de_prueba.productos(id);


--
-- TOC entry 6371 (class 2606 OID 21977)
-- Name: ordenes_compra ordenes_compra_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ordenes_compra
    ADD CONSTRAINT ordenes_compra_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES generic_pharma_sucursal_de_prueba.proveedores(id);


--
-- TOC entry 6377 (class 2606 OID 22035)
-- Name: pagos_proveedor pagos_proveedor_cuenta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.pagos_proveedor
    ADD CONSTRAINT pagos_proveedor_cuenta_id_fkey FOREIGN KEY (cuenta_id) REFERENCES generic_pharma_sucursal_de_prueba.cuentas_por_pagar(id);


--
-- TOC entry 6360 (class 2606 OID 21797)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES generic_pharma_sucursal_de_prueba.categorias(id);


--
-- TOC entry 6363 (class 2606 OID 21870)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES generic_pharma_sucursal_de_prueba.productos(id);


--
-- TOC entry 6364 (class 2606 OID 21865)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES generic_pharma_sucursal_de_prueba.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6361 (class 2606 OID 21851)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES generic_pharma_sucursal_de_prueba.cajas(id);


--
-- TOC entry 6362 (class 2606 OID 21846)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: generic_pharma_sucursal_de_prueba; Owner: postgres
--

ALTER TABLE ONLY generic_pharma_sucursal_de_prueba.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES generic_pharma_sucursal_de_prueba.clientes(id);


--
-- TOC entry 6285 (class 2606 OID 19690)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES mari_boticas_sac_nueva_cajamarca.cajas(id);


--
-- TOC entry 6286 (class 2606 OID 19705)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES mari_boticas_sac_nueva_cajamarca.ventas(id);


--
-- TOC entry 6299 (class 2606 OID 19949)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES mari_boticas_sac_nueva_cajamarca.cajas(id);


--
-- TOC entry 6283 (class 2606 OID 19672)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES mari_boticas_sac_nueva_cajamarca.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6284 (class 2606 OID 19677)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES mari_boticas_sac_nueva_cajamarca.productos(id);


--
-- TOC entry 6282 (class 2606 OID 19659)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES mari_boticas_sac_nueva_cajamarca.proveedores(id);


--
-- TOC entry 6277 (class 2606 OID 19554)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES mari_boticas_sac_nueva_cajamarca.categorias(id);


--
-- TOC entry 6280 (class 2606 OID 19627)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES mari_boticas_sac_nueva_cajamarca.productos(id);


--
-- TOC entry 6281 (class 2606 OID 19622)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES mari_boticas_sac_nueva_cajamarca.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6278 (class 2606 OID 19608)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES mari_boticas_sac_nueva_cajamarca.cajas(id);


--
-- TOC entry 6279 (class 2606 OID 19603)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_nueva_cajamarca; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_nueva_cajamarca.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES mari_boticas_sac_nueva_cajamarca.clientes(id);


--
-- TOC entry 6295 (class 2606 OID 19885)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES mari_boticas_sac_rioja.cajas(id);


--
-- TOC entry 6296 (class 2606 OID 19900)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES mari_boticas_sac_rioja.ventas(id);


--
-- TOC entry 6300 (class 2606 OID 19963)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES mari_boticas_sac_rioja.cajas(id);


--
-- TOC entry 6293 (class 2606 OID 19867)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES mari_boticas_sac_rioja.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6294 (class 2606 OID 19872)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES mari_boticas_sac_rioja.productos(id);


--
-- TOC entry 6292 (class 2606 OID 19854)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES mari_boticas_sac_rioja.proveedores(id);


--
-- TOC entry 6287 (class 2606 OID 19749)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES mari_boticas_sac_rioja.categorias(id);


--
-- TOC entry 6290 (class 2606 OID 19822)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES mari_boticas_sac_rioja.productos(id);


--
-- TOC entry 6291 (class 2606 OID 19817)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES mari_boticas_sac_rioja.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6288 (class 2606 OID 19803)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES mari_boticas_sac_rioja.cajas(id);


--
-- TOC entry 6289 (class 2606 OID 19798)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: mari_boticas_sac_rioja; Owner: postgres
--

ALTER TABLE ONLY mari_boticas_sac_rioja.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES mari_boticas_sac_rioja.clientes(id);


--
-- TOC entry 6261 (class 2606 OID 19181)
-- Name: caja_movimientos caja_movimientos_caja_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.caja_movimientos
    ADD CONSTRAINT caja_movimientos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES public.cajas(id);


--
-- TOC entry 6262 (class 2606 OID 19197)
-- Name: comprobantes_electronicos comprobantes_electronicos_venta_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comprobantes_electronicos
    ADD CONSTRAINT comprobantes_electronicos_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES public.ventas(id);


--
-- TOC entry 6297 (class 2606 OID 19921)
-- Name: gastos gastos_caja_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gastos
    ADD CONSTRAINT gastos_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES public.cajas(id);


--
-- TOC entry 6259 (class 2606 OID 19162)
-- Name: ingreso_detalles ingreso_detalles_ingreso_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_ingreso_id_fkey FOREIGN KEY (ingreso_id) REFERENCES public.ingresos(id) ON DELETE CASCADE;


--
-- TOC entry 6260 (class 2606 OID 19167)
-- Name: ingreso_detalles ingreso_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingreso_detalles
    ADD CONSTRAINT ingreso_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id);


--
-- TOC entry 6258 (class 2606 OID 19149)
-- Name: ingresos ingresos_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ingresos
    ADD CONSTRAINT ingresos_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES public.proveedores(id);


--
-- TOC entry 6358 (class 2606 OID 21759)
-- Name: password_resets password_resets_superadmin_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_superadmin_id_fkey FOREIGN KEY (superadmin_id) REFERENCES public.superadmins(id) ON DELETE CASCADE;


--
-- TOC entry 6359 (class 2606 OID 21741)
-- Name: password_resets password_resets_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- TOC entry 6253 (class 2606 OID 19041)
-- Name: productos productos_categoria_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES public.categorias(id);


--
-- TOC entry 6263 (class 2606 OID 19274)
-- Name: sucursales sucursales_tenant_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursales
    ADD CONSTRAINT sucursales_tenant_id_fkey FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- TOC entry 6265 (class 2606 OID 19313)
-- Name: usuario_sucursal usuario_sucursal_sucursal_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sucursal
    ADD CONSTRAINT usuario_sucursal_sucursal_id_fkey FOREIGN KEY (sucursal_id) REFERENCES public.sucursales(id) ON DELETE CASCADE;


--
-- TOC entry 6266 (class 2606 OID 19308)
-- Name: usuario_sucursal usuario_sucursal_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario_sucursal
    ADD CONSTRAINT usuario_sucursal_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- TOC entry 6264 (class 2606 OID 19292)
-- Name: usuarios usuarios_tenant_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_tenant_id_fkey FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- TOC entry 6256 (class 2606 OID 19114)
-- Name: venta_detalles venta_detalles_producto_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.venta_detalles
    ADD CONSTRAINT venta_detalles_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id);


--
-- TOC entry 6257 (class 2606 OID 19109)
-- Name: venta_detalles venta_detalles_venta_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.venta_detalles
    ADD CONSTRAINT venta_detalles_venta_id_fkey FOREIGN KEY (venta_id) REFERENCES public.ventas(id) ON DELETE CASCADE;


--
-- TOC entry 6254 (class 2606 OID 19095)
-- Name: ventas ventas_caja_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_caja_id_fkey FOREIGN KEY (caja_id) REFERENCES public.cajas(id);


--
-- TOC entry 6255 (class 2606 OID 19090)
-- Name: ventas ventas_cliente_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_cliente_id_fkey FOREIGN KEY (cliente_id) REFERENCES public.clientes(id);


-- Completed on 2026-05-29 23:26:29

--
-- PostgreSQL database dump complete
--

\unrestrict 5YglrXJhL1Fhfh5coqHbFzBelMlQjjV80Rtgyvadb0EyIVlebzY54bgYa4p6j6E

