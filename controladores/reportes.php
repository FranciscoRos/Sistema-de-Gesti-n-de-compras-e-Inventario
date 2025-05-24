<?php
require_once __DIR__ . '/../librerias/fpdf.php';
require_once __DIR__ . '/../modelos/DetalleCompra.php';
require_once __DIR__ . '/../modelos/Producto.php';
require_once __DIR__ . '/../modelos/Reporte.php';
require_once __DIR__.'/../controladores/usuarios.php';


class Reportes {
    public static function get($peticion) {
        if (isset($peticion[0]) && trim($peticion[0]) === "ticket") {
            $idUsuario = usuarios::autorizar();

            $detalles = DetalleCompra::obtenerTodos($idUsuario)['datos'];

            //Crear el PDF con uso de FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'Ticket de Compra');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',12);

            $nombreUsuario = count($detalles) > 0 ? $detalles[0]['nombreUsuario'] : '';
            //Recorrer los detalles de la compra 
            $pdf->Cell(40, 10, 'Nombre del cliente: ' . $nombreUsuario);
            $pdf->Ln(10);
            self::addRows($pdf, $detalles, ['idCompra', 'producto', 'cantidad', 'precioUnitario', 'subtotal'], 43, 10);
            header('Content-Type: application/pdf');
            $pdf ->Output('I', 'reporte.pdf');
            exit;

        }
        else if (isset($peticion[0]) && trim($peticion[0]) === "stock") {
            $idUsuario = usuarios::autorizar();
            $productos = Producto::obtenerTodos($idUsuario)['datos'];

            //Crear el PDF con uso de FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'Stock de Productos');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',12);

            //Recorrer el stock
            self::addRows($pdf, $productos, ['nombre', 'precioCompra', 'precioVenta', 'stock'], 50, 10);
            header('Content-Type: application/pdf');
            $pdf ->Output('I', 'reporte.pdf');
            exit;
        }
        else if (isset($peticion[0]) && trim($peticion[0]) === "proveedor") {
            $proveedores = Reporte::getAllSuppliers()['datos'];

            //Crear el PDF con uso de FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'Proveedores');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',12);

            self::addRows($pdf, $proveedores, ['nombre', 'contacto', 'telefono'], 70, 10);
            header('Content-Type: application/pdf');
            $pdf ->Output('I', 'reporte.pdf');
            exit;
        } else if (isset($peticion[0]) && trim($peticion[0]) === "productos") {
            $productos = Reporte::getAllProducts()['datos'];

            //Crear el PDF con uso de FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'inventario general');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',12);
            self::addRows($pdf, $productos, ['idProducto','nombre', 'precioCompra', 'precioVenta', 'stock'], 45, 10);
            header('Content-Type: application/pdf');
            $pdf ->Output('I', 'reporte.pdf');
            exit;
        } else if (isset($peticion[0]) && trim($peticion[0]) === "producto") {
            $idUsuario = usuarios::autorizar();
            $productos = Producto::obtenerTodos($idUsuario)['datos'];

            //Crear el PDF con uso de FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'Listado de tus Productos');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',12);

            //Recorrer los productos
             self::addRows($pdf, $productos, ['idProducto','nombre', 'precioCompra', 'precioVenta', 'stock'], 45, 10);
            header('Content-Type: application/pdf');
            $pdf ->Output('I', 'reporte.pdf');
            exit;
        } else {
            throw new ExcepcionApi(4, "Petición no válida", 400);
        }
    }
    private static function addRows($pdf, $datos, $campos, $ancho, $alto) {
        // Mostrar encabezados
        foreach ($campos as $campo) {
            $pdf->Cell($ancho, $alto, ucfirst($campo));
        }
        $pdf->Ln(10);
    
        // Mostrar los datos
        foreach ($datos as $fila) {
            foreach ($campos as $campo) {
                $pdf->Cell($ancho, $alto, $fila[$campo]);
            }
            $pdf->Ln(10);
        }
    }
}
?>