<!DOCTYPE html>
<html lang="es">
<body style="font-family: Arial, sans-serif; color: #2b1d14;">
    <h2>Recuperación de contraseña</h2>
    <p>Hola, {{ $usuario->nombre }}:</p>
    <p>Usa este código para continuar con la recuperación de tu contraseña de CaféSync:</p>
    <p style="font-size: 32px; letter-spacing: 6px; font-weight: bold;">{{ $codigo }}</p>
    <p>El código vence en 10 minutos y solo funciona una vez. Si no lo solicitaste, ignora este mensaje y avisa a tu gerente.</p>
</body>
</html>
