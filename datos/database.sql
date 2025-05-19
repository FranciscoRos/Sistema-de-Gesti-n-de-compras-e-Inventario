CREATE DATABASE IF NOT EXISTS sistema_compras;
USE sistema_compras;

CREATE TABLE usuarios (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    claveApi VARCHAR(64) NOT NULL UNIQUE
);

CREATE TABLE productos (
    idProducto INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    precioCompra DECIMAL(10,2) NOT NULL,
    precioVenta DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0
    
);

CREATE TABLE proveedores (
    idProveedor INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20)

);

CREATE TABLE compras (
    idCompra INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    idProveedor INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL

);

CREATE TABLE detalle_compra (
    idDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idCompra INT NOT NULL,
    idProducto INT NOT NULL,
    cantidad INT NOT NULL,
    precioUnitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL
);



-- productos.idUsuario
ALTER TABLE productos DROP FOREIGN KEY productos_ibfk_1;
ALTER TABLE productos
ADD CONSTRAINT fk_productos_usuario
FOREIGN KEY (idUsuario)
REFERENCES usuarios(idUsuario)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- proveedores.idUsuario
ALTER TABLE proveedores DROP FOREIGN KEY proveedores_ibfk_1;
ALTER TABLE proveedores
ADD CONSTRAINT fk_proveedores_usuario
FOREIGN KEY (idUsuario)
REFERENCES usuarios(idUsuario)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- compras.idUsuario
ALTER TABLE compras DROP FOREIGN KEY compras_ibfk_1;
ALTER TABLE compras
ADD CONSTRAINT fk_compras_usuario
FOREIGN KEY (idUsuario)
REFERENCES usuarios(idUsuario)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- compras.idProveedor
ALTER TABLE compras DROP FOREIGN KEY compras_ibfk_2;
ALTER TABLE compras
ADD CONSTRAINT fk_compras_proveedor
FOREIGN KEY (idProveedor)
REFERENCES proveedores(idProveedor)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- detalle_compra.idCompra
ALTER TABLE detalle_compra DROP FOREIGN KEY detalle_compra_ibfk_1;
ALTER TABLE detalle_compra
ADD CONSTRAINT fk_detalle_compra_compra
FOREIGN KEY (idCompra)
REFERENCES compras(idCompra)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- detalle_compra.idProducto
ALTER TABLE detalle_compra DROP FOREIGN KEY detalle_compra_ibfk_2;
ALTER TABLE detalle_compra
ADD CONSTRAINT fk_detalle_compra_producto
FOREIGN KEY (idProducto)
REFERENCES productos(idProducto)
ON DELETE RESTRICT
ON UPDATE CASCADE;
