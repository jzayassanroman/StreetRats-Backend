DROP SCHEMA IF EXISTS STREETRATS CASCADE;
CREATE SCHEMA IF NOT EXISTS STREETRATS;


CREATE table streetrats.color (
                                  id SERIAL PRIMARY KEY,
                                  descripcion VARCHAR(20) NOT null
);
CREATE TABLE streetrats.talla (
                                  id SERIAL PRIMARY KEY,
                                  descripcion VARCHAR(10) NOT NULL
);
CREATE TABLE streetrats.usuario (
                                    id SERIAL PRIMARY KEY,
                                    username VARCHAR(100) NOT NULL,
                                    password VARCHAR(50) NOT NULL,
                                    rol INTEGER NOT NULL -- Enum
);
CREATE TABLE streetrats.cliente (
                                    id SERIAL PRIMARY KEY,
                                    nombre VARCHAR(100) NOT NULL,
                                    apellido VARCHAR(100) NOT NULL,
                                    email VARCHAR(100) NOT NULL,
                                    direccion VARCHAR(150) NOT NULL,
                                    telefono INTEGER NOT NULL,
                                    id_usuario INTEGER NOT NULL,
                                    CONSTRAINT fk_cliente_usuario FOREIGN KEY (id_usuario) REFERENCES streetrats.usuario(id)
);
CREATE TABLE streetrats.producto (
                                     id SERIAL PRIMARY KEY,
                                     nombre VARCHAR(100) NOT NULL,
                                     descripcion VARCHAR(200) NOT NULL,
                                     tipo INTEGER NOT NULL, -- Enum
                                     precio DOUBLE PRECISION NOT NULL,
                                     imagen VARCHAR(500) NOT NULL,
                                     sexo INTEGER NOT NULL, -- Enum
                                     id_color INTEGER NOT NULL,
                                     id_talla INTEGER NOT NULL,
                                     CONSTRAINT fk_productos_color FOREIGN KEY (id_color) REFERENCES streetrats.color(id),
                                     CONSTRAINT fk_productos_talla FOREIGN KEY (id_talla) REFERENCES streetrats.talla(id)
);
CREATE TABLE streetrats.valoraciones (
                                         id SERIAL PRIMARY KEY,
                                         valoracion VARCHAR(300) NOT NULL,
                                         fecha DATE NOT NULL,
                                         id_producto INTEGER NOT NULL,
                                         id_cliente INTEGER NOT NULL,
                                         CONSTRAINT fk_valoracion_producto FOREIGN KEY (id_producto) REFERENCES streetrats.producto(id),
                                         CONSTRAINT fk_valoraciones_cliente FOREIGN KEY (id_cliente) REFERENCES streetrats.cliente(id)
);
CREATE TABLE streetrats.pedido (
                                   id SERIAL PRIMARY KEY,
                                   total DOUBLE PRECISION NOT NULL,
                                   estado INTEGER NOT NULL, -- Enum
                                   fecha DATE NOT NULL,
                                   id_cliente INTEGER NOT NULL,
                                   CONSTRAINT fk_pedido_cliente FOREIGN KEY (id_cliente) REFERENCES streetrats.cliente(id)
);
CREATE TABLE streetrats.inventario (
                                       id SERIAL PRIMARY KEY,
                                       cantidad INTEGER NOT NULL,
                                       id_producto INTEGER NOT NULL,
                                       CONSTRAINT fk_inventario_producto FOREIGN KEY (id_producto) REFERENCES streetrats.producto(id)
);
CREATE TABLE streetrats.detalle_venta (
                                          id SERIAL PRIMARY KEY,
                                          cantidad INTEGER NOT NULL,
                                          subtotal DOUBLE PRECISION NOT NULL,
                                          id_producto INTEGER NOT NULL,
                                          id_pedido INTEGER NOT NULL,
                                          CONSTRAINT fk_detalle_venta_producto FOREIGN KEY (id_producto) REFERENCES streetrats.producto(id),
                                          CONSTRAINT fk_detalle_venta_pedido FOREIGN KEY (id_pedido) REFERENCES streetrats.pedido(id)
);
CREATE table streetrats.pedido_producto (
                                            id SERIAL PRIMARY KEY,
                                            id_producto INTEGER NOT NULL,
                                            id_pedido INTEGER NOT NULL,
                                            id_color INTEGER NOT NULL,
                                            id_talla INTEGER NOT NULL,
                                            CONSTRAINT fk_pedido_producto_producto FOREIGN KEY (id_producto) REFERENCES streetrats.producto(id),
                                            CONSTRAINT fk_pedido_producto_pedido FOREIGN KEY (id_pedido) REFERENCES streetrats.pedido(id),
                                            CONSTRAINT fk_pedido_producto_color FOREIGN KEY (id_color) REFERENCES streetrats.color(id),
                                            CONSTRAINT fk_pedido_producto_talla FOREIGN KEY (id_talla) REFERENCES streetrats.talla(id)
);
