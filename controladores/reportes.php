<?php
require_once __DIR__ . '/../librerias/fpdf.php';
require_once __DIR__ . '/../modelos/DetalleCompra.php';
require_once __DIR__ . '/../modelos/Producto.php';
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

            //Recorrer los detalles de la compra 
            foreach ($detalles as $detalle) {
                $pdf->Cell(40, 10, 'Usuario: ' . $detalle['nombreUsuario']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Fecha: ' . $detalle['fecha']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Producto: ' . $detalle['producto']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Cantidad: ' . $detalle['cantidad']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Precio Unitario: ' . $detalle['precioUnitario']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Subtotal: ' . $detalle['subtotal']);
                $pdf->Ln(20);
            }
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
            foreach ($productos as $producto) {
                $pdf->Cell(40, 10, 'Nombre del producto: ' . $producto['nombre']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Precio de compra: ' . $producto['precioCompra']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Precio de venta: ' . $producto['precioVenta']);
                $pdf->Ln(10);
                $pdf->Cell(40, 10, 'Stock: ' . $producto['stock']);
                $pdf->Ln(20);
            }
            header('Content-Type: application/pdf');
            $pdf ->Output('I', 'reporte.pdf');
            exit;
        }
        
    }
}
?>