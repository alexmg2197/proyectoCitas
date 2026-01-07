<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

$db = Database::getInstance();

// Obtener mes y año actual o de la URL
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

// Cálculos para el calendario
$primerDiaMes = mktime(0, 0, 0, $mes, 1, $anio);
$numeroDias = date('t', $primerDiaMes);
$diaSemana = date('w', $primerDiaMes); // 0 (dom) a 6 (sab)
?>

<?php include 'templates/sidebar.php'; ?>
<?php include 'templates/header.php'; ?>
<?php include 'templates/modal_detalle_dia.php'; ?>
<?php include 'templates/modal_form_cita.php'; ?>

<main class="p-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Calendario de Citas</h2>
        <div class="flex gap-2">
            <a href="?mes=<?php echo $mes-1; ?>&anio=<?php echo $anio; ?>" class="p-2 bg-white rounded shadow hover:bg-gray-100"><i class="fas fa-chevron-left"></i></a>
            <span class="px-4 py-2 font-bold bg-white rounded shadow capitalize">
                <?php 
                    $formatter = new IntlDateFormatter(
                        'es_ES', 
                        IntlDateFormatter::NONE, 
                        IntlDateFormatter::NONE, 
                        null, 
                        null, 
                        "MMMM yyyy"
                    );
                    echo ucfirst($formatter->format($primerDiaMes)); 
                    ?>
            </span>
            <a href="?mes=<?php echo $mes+1; ?>&anio=<?php echo $anio; ?>" class="p-2 bg-white rounded shadow hover:bg-gray-100"><i class="fas fa-chevron-right"></i></a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-7 bg-gray-50 border-b">
            <?php foreach(['Dom','Lun','Mar','Mie','Jue','Vie','Sab'] as $d): ?>
                <div class="py-3 text-center text-xs font-bold text-gray-500 uppercase"><?php echo $d; ?></div>
            <?php endforeach; ?>
        </div>
        
        <div class="grid grid-cols-7">
            <?php
            // Espacios en blanco hasta el primer día
            for($i=0; $i<$diaSemana; $i++) echo '<div class="h-32 border-b border-r bg-gray-50/50"></div>';
            
            // Días del mes
            for($dia=1; $dia<=$numeroDias; $dia++):
                $fechaActual = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                // Aquí podrías hacer una consulta rápida para ver si hay citas este día
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM citas WHERE fecha = ?");
                $stmt->execute([$fechaActual]);
                $totalCitas = $stmt->fetch()['total'];
            ?>
                <div onclick="verDetalleDia('<?php echo $fechaActual; ?>')" 
                     class="h-32 border-b border-r p-2 hover:bg-blue-50 cursor-pointer transition relative">
                    <span class="text-sm font-semibold <?php echo ($fechaActual == date('Y-m-d')) ? 'bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded-full' : 'text-gray-700'; ?>">
                        <?php echo $dia; ?>
                    </span>
                    <?php if($totalCitas > 0): ?>
                        <div class="mt-2 bg-blue-100 text-blue-700 text-[10px] px-2 py-1 rounded-full font-bold">
                            <?php echo $totalCitas; ?> cita(s)
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    
    <script>
        // Dentro de tu bloque de scripts al final de citas.php
        <?php if(isset($_GET['status']) && $_GET['status'] == 'error_overlap'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Horario Ocupado',
                text: 'Ya existe una cita que se cruza con este horario. Por favor, elige otra hora.',
                confirmButtonColor: '#3b82f6'
            });
        <?php endif; ?>
        let fechaSeleccionadaGlobal = '';

        function verDetalleDia(fecha) {
            fechaSeleccionadaGlobal = fecha;
            document.getElementById('tituloFecha').innerText = fecha;
            document.getElementById('modalDia').classList.remove('hidden');
            cargarCitasDelDia(fecha);
        }

        function cargarCitasDelDia(fecha) {
            // Llamada AJAX a un archivo que crearemos: citas_del_dia.php
            fetch(`citas_del_dia.php?fecha=${fecha}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('contenidoCitas').innerHTML = html;
                });
        }

        function navegarDia(offset) {
            let date = new Date(fechaSeleccionadaGlobal + 'T00:00:00');
            date.setDate(date.getDate() + offset);
            let nuevaFecha = date.toISOString().split('T')[0];
            verDetalleDia(nuevaFecha);
        }

        // Función para cerrar el modal de detalles del día
        function cerrarModalDia() {
            const modal = document.getElementById('modalDia');
            modal.classList.add('hidden');
            // Limpiamos el contenido para que no parpadee la info anterior al reabrir
            document.getElementById('contenidoCitas').innerHTML = '<p class="text-center text-gray-500">Cargando...</p>';
        }

        // Cerrar modal si el usuario hace clic fuera del recuadro blanco (en el overlay)
        window.onclick = function(event) {
            const modalDia = document.getElementById('modalDia');
            const modalCita = document.getElementById('modalCita');
            
            if (event.target == modalDia) {
                cerrarModalDia();
            }
            if (event.target == modalCita) {
                cerrarModalCita(); // La función que ya tenías para el otro modal
            }
        }

        function abrirModalNuevaCitaDesdeDia() {
            // Cerramos el modal de detalles
            cerrarModalDia();
            // Abrimos el modal de registro que creamos antes
            abrirModalCita(); 
            // Seteamos la fecha automáticamente en el input de fecha del modal
            document.querySelector('input[name="fecha"]').value = fechaSeleccionadaGlobal;
        }

        // Función para eliminar con SweetAlert2 dentro del modal
        function eliminarCita(id) {
            Swal.fire({
                title: '¿Eliminar cita?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, esperar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `citas_eliminar.php?id=${id}`;
                }
            });
        }

        function actualizarEstadoCita(id, nuevoEstado) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('estado', nuevoEstado);

            fetch('citas_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Usamos Toastify para una notificación no invasiva
                   const Toast = Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    Toast.fire({
                        icon: nuevoEstado === "completada" ? "success" : (nuevoEstado === "pendiente" ? "question" : ((nuevoEstado === "confirmada" ? "info" : "error"))),
                        title: `Cita ${nuevoEstado}`
                    });
                    
                    // Refrescamos solo el contenido del modal para ver los nuevos colores
                    cargarCitasDelDia(fechaSeleccionadaGlobal);
                }
            });
        }

        function abrirModalNuevaCitaDesdeDia() {
            // Usamos la fecha que seleccionamos al hacer clic en el calendario
            document.getElementById('cita_id').value = '';
            document.getElementById('f_fecha').value = fechaSeleccionadaGlobal;
            document.getElementById('formCitaTitle').innerText = 'Nueva Cita';
            document.getElementById('modalFormCita').classList.remove('hidden');
        }

        // Para editar, primero obtenemos los datos (podemos enviarlos desde citas_del_dia.php)
        function abrirModalEditarCita(datos) {
            document.getElementById('cita_id').value = datos.id;
            document.getElementById('f_cliente_id').value = datos.cliente_id;
            document.getElementById('f_servicio_id').value = datos.servicio_id;
            document.getElementById('f_fecha').value = datos.fecha;
            document.getElementById('f_hora').value = datos.hora;
            document.getElementById('formCitaTitle').innerText = 'Editar Cita';
            document.getElementById('modalFormCita').classList.remove('hidden');
        }

        function cerrarModalFormCita() {
            document.getElementById('modalFormCita').classList.add('hidden');
        }
</script>
</main>