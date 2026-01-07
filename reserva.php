<?php 
    require_once 'src/Database.php';
    $db = Database::getInstance();

    // Obtenemos los servicios activos para que el cliente elija
    $servicios = $db->query("SELECT * FROM servicios WHERE activo = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva tu Cita - BookingPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4" style="background-image: url('public/img/fondo.jpg');">

    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row">
        <div class="md:w-1/3 bg-blue-600 p-8 text-white flex flex-col justify-center">
            <h1 class="text-2xl font-bold mb-4">Reserva en línea</h1>
            <p class="text-blue-100 text-sm">Agenda tu cita en menos de 2 minutos. Recibirás una confirmación inmediata.</p>
            <div class="mt-8 space-y-4">
                <div class="flex items-center gap-3 text-sm">
                    <i class="fas fa-check-circle"></i> Rápido y seguro
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <i class="fas fa-clock"></i> Elige tu horario
                </div>
            </div>
        </div>

        <div class="md:w-2/3 p-8">
            <form id="formReserva" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" name="telefono" required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Correo</label>
                        <input type="tel" name="correo" required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Servicio</label>
                    <select name="servicio_id" required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <option value="">Selecciona...</option>
                        <?php foreach($servicios as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['nombre'] ?> - $<?= number_format($s['precio'], 2) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Fecha</label>
                        <input type="date" name="fecha" id="fecha_reserva" min="<?= date('Y-m-d') ?>" required 
                               class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Hora</label>
                        <select name="hora" id="hora_reserva" required class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                            <option value="">Primero elige fecha</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-lg hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Confirmar Reserva
                </button>
            </form>
        </div>
    </div>

    <script>
        // Lógica para cargar horas disponibles cuando se elige una fecha
        document.getElementById('fecha_reserva').addEventListener('change', function() {
            const fecha = this.value;
            const selectHora = document.getElementById('hora_reserva');
            
            // Aquí llamaríamos a un pequeño script que verifique qué horas están libres
            fetch(`get_horas_disponibles.php?fecha=${fecha}`)
                .then(res => res.json())
                .then(horas => {
                    selectHora.innerHTML = '<option value="">Selecciona hora</option>';
                    horas.forEach(h => {
                        selectHora.innerHTML += `<option value="${h}">${h}</option>`;
                    });
                });
        });

        // Envío del formulario con SweetAlert
        document.getElementById('formReserva').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('procesar_reserva_publica.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cita Agendada!',
                        text: 'Te esperamos el día ' + formData.get('fecha'),
                        confirmButtonColor: '#2563eb'
                    }).then(() => location.reload());
                }
            });
        });
    </script>
</body>
</html>