	DROP TABLE IF EXISTS color;
	DROP TABLE IF EXISTS talla;
	DROP TABLE IF EXISTS cliente;
	DROP TABLE IF EXISTS usuario;
	DROP TABLE IF EXISTS producto;
	DROP TABLE IF EXISTS valoraciones;
	DROP TABLE IF EXISTS pedido;
	DROP TABLE IF EXISTS inventario;
	DROP TABLE IF EXISTS detalle_venta;
	DROP TABLE IF EXISTS pedido_producto;
	
	CREATE TABLE color (
	    id SERIAL PRIMARY KEY,
	    rojo VARCHAR(10) NOT NULL,
	    verde VARCHAR(10) NOT NULL,
	    negro VARCHAR(10) NOT NULL,
	    blanco VARCHAR(10) NOT NULL,
	    celeste VARCHAR(10) NOT NULL,
	    marron VARCHAR(10) NOT NULL,
	    naranja VARCHAR(10) NOT NULL,
	    rosa VARCHAR(10) NOT NULL,
	    morado VARCHAR(10) NOT NULL
	);
	
	CREATE TABLE talla (
	    id SERIAL PRIMARY KEY,
	    XS VARCHAR(10) NOT NULL,
	    S VARCHAR(10) NOT NULL,
	    M VARCHAR(10) NOT NULL,
	    L VARCHAR(10) NOT NULL,
	    XL VARCHAR(10) NOT NULL,
	    XXL VARCHAR(10) NOT NULL
	);
	
	CREATE TABLE usuario (
	    id SERIAL PRIMARY KEY,
	    username VARCHAR(100) NOT NULL,
	    password VARCHAR(50) NOT NULL,
	    rol INTEGER NOT NULL -- Enum
	);
	
	CREATE TABLE cliente (
	    id SERIAL PRIMARY KEY,
	    nombre VARCHAR(100) NOT NULL,
	    apellido VARCHAR(100) NOT NULL,
	    email VARCHAR(100) NOT NULL,
	    direccion VARCHAR(150) NOT NULL,
	    telefono INTEGER NOT NULL,
	    id_usuario INTEGER NOT NULL,
	    CONSTRAINT fk_cliente_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id)
	);
	
	CREATE TABLE producto (
	    id SERIAL PRIMARY KEY,
	    nombre VARCHAR(100) NOT NULL,
	    descripcion VARCHAR(200) NOT NULL,
	    tipo INTEGER NOT NULL, -- Enum
	    precio DOUBLE PRECISION NOT NULL,
	    imagen VARCHAR(500) NOT NULL,
	    sexo INTEGER NOT NULL, -- Enum
	    id_color INTEGER NOT NULL,
	    id_talla INTEGER NOT NULL,
	    CONSTRAINT fk_productos_color FOREIGN KEY (id_color) REFERENCES color(id),
	    CONSTRAINT fk_productos_talla FOREIGN KEY (id_talla) REFERENCES talla(id)
	);
	
	CREATE TABLE valoraciones (
	    id SERIAL PRIMARY KEY,
	    valoracion VARCHAR(300) NOT NULL,
	    fecha DATE NOT NULL,
	    id_producto INTEGER NOT NULL,
	    id_cliente INTEGER NOT NULL,
	    CONSTRAINT fk_valoracion_producto FOREIGN KEY (id_producto) REFERENCES producto(id),
	    CONSTRAINT fk_valoraciones_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id)
	);
	
	CREATE TABLE pedido (
	    id SERIAL PRIMARY KEY,
	    total DOUBLE PRECISION NOT NULL,
	    estado INTEGER NOT NULL, -- Enum
	    fecha DATE NOT NULL,
	    id_cliente INTEGER NOT NULL,
	    CONSTRAINT fk_pedido_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id)
	);
	
	CREATE TABLE inventario (
	    id SERIAL PRIMARY KEY,
	    cantidad INTEGER NOT NULL,
	    id_producto INTEGER NOT NULL,
	    CONSTRAINT fk_inventario_producto FOREIGN KEY (id_producto) REFERENCES producto(id)
	);
	
	CREATE TABLE detalle_venta (
	    id SERIAL PRIMARY KEY,
	    cantidad INTEGER NOT NULL,
	    subtotal DOUBLE PRECISION NOT NULL,
	    id_producto INTEGER NOT NULL,
	    id_pedido INTEGER NOT NULL,
	    CONSTRAINT fk_detalle_venta_producto FOREIGN KEY (id_producto) REFERENCES producto(id),
	    CONSTRAINT fk_detalle_venta_pedido FOREIGN KEY (id_pedido) REFERENCES pedido(id)
	);
	
	CREATE TABLE pedido_producto (
	    id SERIAL PRIMARY KEY,
	    id_producto INTEGER NOT NULL,
	    id_pedido INTEGER NOT NULL,
	    id_color INTEGER NOT NULL,
	    id_talla INTEGER NOT NULL,
	    CONSTRAINT fk_pedido_producto_producto FOREIGN KEY (id_producto) REFERENCES producto(id),
	    CONSTRAINT fk_pedido_producto_pedido FOREIGN KEY (id_pedido) REFERENCES pedido(id),
	    CONSTRAINT fk_pedido_producto_color FOREIGN KEY (id_color) REFERENCES color(id),
	    CONSTRAINT fk_pedido_producto_talla FOREIGN KEY (id_talla) REFERENCES talla(id)
	);
