<?php 
    require_once 'src/auth.php'; 
    require_once 'src/Database.php';

    $db = Database::getInstance();

    // Ejemplo: Consultar el total de citas hoy
    $stmt = $db->query("SELECT COUNT(*) as total FROM citas WHERE fecha = CURDATE()");
    $citasHoy = $stmt->fetch()['total'];

    // Consultar las últimas 5 citas
    $stmt = $db->query("SELECT c.id, cl.nombre as cliente, s.nombre as servicio, c.hora, c.estado, c.fecha
                        FROM citas c 
                        JOIN clientes cl ON c.cliente_id = cl.id 
                        JOIN servicios s ON c.servicio_id = s.id 
                        ORDER BY c.fecha DESC, c.hora DESC LIMIT 5");
    $citasRecientes = $stmt->fetchAll();

    //Consultar los servicios activos

    $stmt = $db ->query("SELECT COUNT(*) as totalS FROM servicios WHERE activo = 1");
    $servicios = $stmt->fetch()['totalS'];

    $stmt = $db ->query("SELECT COUNT(*) as totalC FROM clientes");
    $clientes = $stmt->fetch()['totalC'];

    // 1. Ingresos Totales del Mes (Solo citas 'completadas')
    $mesActual = date('m');
    $anioActual = date('Y');
    $stmtIngresos = $db->prepare("SELECT SUM(s.precio) as total 
                                FROM citas c 
                                JOIN servicios s ON c.servicio_id = s.id 
                                WHERE c.estado = 'completada' 
                                AND MONTH(c.fecha) = ? AND YEAR(c.fecha) = ?");
    $stmtIngresos->execute([$mesActual, $anioActual]);
    $ingresosMes = $stmtIngresos->fetch()['total'] ?? 0;

    // 2. Tasa de Asistencia (Citas completadas vs Total del mes)
    $stmtCitas = $db->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
        SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
        FROM citas WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
    $stmtCitas->execute([$mesActual, $anioActual]);
    $statsCitas = $stmtCitas->fetch();

    $totalMes = $statsCitas['total'] ?: 1; // Evitar división por cero
    $porcentajeAsistencia = round(($statsCitas['completadas'] / $totalMes) * 100);

    // 3. Cliente más fiel (El que más ha gastado o asistido)
    $stmtTopCliente = $db->query("SELECT cl.nombre, COUNT(c.id) as visitas 
                                FROM clientes cl 
                                JOIN citas c ON cl.id = c.cliente_id 
                                WHERE c.estado = 'completada' 
                                GROUP BY cl.id ORDER BY visitas DESC LIMIT 1");
    $topCliente = $stmtTopCliente->fetch();

    // 4. Servicio Estrella
    $stmtTopServicio = $db->query("SELECT s.nombre, COUNT(c.id) as cantidad 
                                FROM servicios s 
                                JOIN citas c ON s.id = c.servicio_id 
                                GROUP BY s.id ORDER BY cantidad DESC LIMIT 1");
    $topServicio = $stmtTopServicio->fetch();

    // 2. Ingresos Mes Anterior
    $mesPasado = date('m', strtotime("-1 month"));
    $anioPasado = date('Y', strtotime("-1 month"));

    $stmtPrev = $db->prepare("SELECT SUM(s.precio) as total 
                            FROM citas c 
                            JOIN servicios s ON c.servicio_id = s.id 
                            WHERE c.estado = 'completada' 
                            AND MONTH(c.fecha) = ? AND YEAR(c.fecha) = ?");
    $stmtPrev->execute([$mesPasado, $anioPasado]);
    $ingresosMesAnterior = $stmtPrev->fetch()['total'] ?? 0;

    // 3. Calcular Porcentaje de Crecimiento
    $porcentajeCrecimiento = 0;
    $claseColor = "text-gray-500 bg-gray-50"; // Default
    $icono = "fa-minus";

    if ($ingresosMesAnterior > 0) {
        $porcentajeCrecimiento = (($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior) * 100;
    } elseif ($ingresosMes > 0) {
        $porcentajeCrecimiento = 100; // Si el mes pasado fue 0 y este hay algo, es 100% crecimiento
    }

    // Determinar color e icono según el resultado
    if ($porcentajeCrecimiento > 0) {
        $claseColor = "text-green-500 bg-green-50";
        $icono = "fa-arrow-up";
    } elseif ($porcentajeCrecimiento < 0) {
        $claseColor = "text-red-500 bg-red-50";
        $icono = "fa-arrow-down";
    }

    $ventasSemanales = [];
    for ($i = 6; $i >= 0; $i--) {
        $fechaBusqueda = date('Y-m-d', strtotime("-$i days"));
        $stmt = $db->prepare("SELECT SUM(s.precio) as total 
                            FROM citas c 
                            JOIN servicios s ON c.servicio_id = s.id 
                            WHERE c.fecha = ? AND c.estado = 'completada'");
        $stmt->execute([$fechaBusqueda]);
        $res = $stmt->fetch();
        
        $ventasSemanales[] = [
            'dia' => date('d M', strtotime($fechaBusqueda)),
            'monto' => $res['total'] ?? 0
        ];
    }

    // Convertimos a JSON para que JavaScript lo entienda
    $labelsVentas = json_encode(array_column($ventasSemanales, 'dia'));
    $datosVentas = json_encode(array_column($ventasSemanales, 'monto'));

    
?>

<?php include 'templates/sidebar.php'; ?>

<?php include 'templates/header.php'; ?>

<main class="flex-1 overflow-y-auto p-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                <p class="text-gray-500 text-sm uppercase font-bold">Servicios</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $servicios; ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm uppercase font-bold">Citas para Hoy</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $citasHoy; ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm uppercase font-bold">Clientes</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $clientes; ?></p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-100 text-green-600 rounded-xl">
                        <i class="fas fa-dollar-sign fa-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full"><?= abs(round($porcentajeCrecimiento, 1)) ?>% del mes anterior</span>
                </div>
                <p class="text-gray-500 text-sm font-medium">Ingresos (Mes Actual)</p>
                <h3 class="text-2xl font-bold text-gray-800">$<?= number_format($ingresosMes, 2) ?></h3>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400">Tasa de éxito</span>
                </div>
                <p class="text-gray-500 text-sm font-medium">Asistencia Real</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= $porcentajeAsistencia ?>%</h3>
                <div class="w-full bg-gray-100 h-2 rounded-full mt-3">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $porcentajeAsistencia ?>%"></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-xl">
                        <i class="fas fa-crown fa-lg"></i>
                    </div>
                </div>
                <p class="text-gray-500 text-sm font-medium">Cliente VIP</p>
                <h3 class="text-lg font-bold text-gray-800 truncate"><?= $topCliente['nombre'] ?? 'Sin datos' ?></h3>
                <p class="text-xs text-purple-500 font-bold"><?= $topCliente['visitas'] ?? 0 ?> visitas completadas</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-100 text-orange-600 rounded-xl">
                        <i class="fas fa-fire fa-lg"></i>
                    </div>
                </div>
                <p class="text-gray-500 text-sm font-medium">Servicio Popular</p>
                <h3 class="text-lg font-bold text-gray-800 truncate"><?= $topServicio['nombre'] ?? 'Sin datos' ?></h3>
                <p class="text-xs text-orange-500 font-bold"><?= $topServicio['cantidad'] ?? 0 ?> reservas</p>
            </div>

        </div>

        <div class="grid grid-cols-1 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Rendimiento Semanal</h3>
                        <p class="text-sm text-gray-500">Ingresos obtenidos por citas completadas</p>
                    </div>
                    <i class="fas fa-chart-line text-blue-500 bg-blue-50 p-3 rounded-lg"></i>
                </div>
                
                <div class="relative h-[300px]">
                    <canvas id="graficaVentas"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold text-gray-700">Citas Recientes</h3>
            </div>
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">Servicio</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Hora</th>
                        <th class="px-6 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-600">
                    <?php foreach($citasRecientes as $cita): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($cita['cliente']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($cita['servicio']); ?></td>
                        <td class="px-6 py-4"><?php echo $cita['fecha']; ?></td>
                        <td class="px-6 py-4"><?php echo $cita['hora']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                <?php echo $cita['estado'] === 'confirmada' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo ucfirst($cita['estado']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        const ctx = document.getElementById('graficaVentas').getContext('2d');

        // Creamos un degradado para el área de la gráfica
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= $labelsVentas ?>,
                datasets: [{
                    label: 'Ventas ($)',
                    data: <?= $datosVentas ?>,
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4, // Curva suave
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { callback: value => '$' + value }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>

<?php include 'templates/footer.php'; ?>